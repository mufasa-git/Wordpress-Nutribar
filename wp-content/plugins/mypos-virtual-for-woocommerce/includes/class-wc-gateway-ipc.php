<?php

if (!defined('ABSPATH')) {
    exit;
}

function mypos_check_pending_payment_orders_statuses() {
    (new WC_Gateway_IPC)->check_pending_payment_orders_statuses();
}

add_action('mypos_check_payment_status', 'mypos_check_pending_payment_orders_statuses');

/**
 * @property string version
 * @property string test
 * @property string debug
 * @property string sid
 * @property string wallet_number
 * @property string private_key
 * @property string public_certificate
 * @property string url
 * @property string keyindex
 * @property string paymentParametersRequired
 * @property string notify_url
 * @property string test_prefix
 * @property string paymentMethod
 */
class WC_Gateway_IPC extends WC_Payment_Gateway
{
    const PAYMENT_METHOD_CARD = '1';
    const PAYMENT_METHOD_IDEAL = '2';
    const PAYMENT_METHOD_BOTH = '3';

    const PENDING_PAYMENT_DB_TABLE_NAME = 'mypos_pending_payments_schedule';
    const WAITING_CONFIRMATION_PERIOD_HOURS = '8';

    /** @var boolean Whether or not logging is enabled */
    public static $log_enabled = false;

    /** @var WC_Logger Logger instance */
    public static $log = false;

    protected $line_items;

    /**
     * Logging method
     * @param  string $message
     */
    public static function log( $message ) {
        if ( self::$log_enabled ) {
            if ( empty( self::$log ) ) {
                self::$log = new WC_Logger();
            }
            self::$log->add( 'mypos_virtual', $message );
        }
    }

    public function __construct()
    {
        $this->id = "mypos_virtual";
        $this->icon = "";
        $this->has_fields = false;
        $this->method_title = __('myPOS Checkout', 'woocommerce');
        $this->method_description = __('myPOS Checkout works by sending customers to myPOS Checkout where they can enter their payment information.<br/>To use this payment option you need to <a href="https://mypos.eu/en/register/" target="_blank">sign up</a> for a myPOS account.', 'woocommerce');
        $this->supports = array(
            'products',
            'refunds',
        );
        $this->version = '1.4';

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->test        = 'yes' === $this->get_option( 'test', 'no' );
        $this->debug       = 'yes' === $this->get_option( 'debug', 'no' );

        $test_prefix = $this->get_option('test_prefix');
        if (empty($test_prefix)){
            if ( empty( $this->settings ) ) {
                $this->init_settings();
            }

            $this->settings['test_prefix'] =  uniqid() . '_';
            update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
        }

        $this->test_prefix = $this->get_option('test_prefix');

        if (!$this->test)
        {
            $this->sid                       = $this->get_option( 'production_sid' );
            $this->wallet_number             = $this->get_option( 'production_wallet_number' );
            $this->private_key               = $this->get_option( 'production_private_key' );
            $this->public_certificate        = $this->get_option( 'production_public_certificate' );
            $this->url                       = $this->get_option( 'production_url' );
            $this->keyindex                  = $this->get_option( 'production_keyindex' );
	        $this->paymentParametersRequired = $this->get_option( 'production_ppr' );
	        $this->paymentMethod             = $this->get_option( 'production_payment_method' );
        }
        else
        {
            $this->sid                       = $this->get_option( 'developer_sid' );
            $this->wallet_number             = $this->get_option( 'developer_wallet_number' );
            $this->private_key               = $this->get_option( 'developer_private_key' );
            $this->public_certificate        = $this->get_option( 'developer_public_certificate' );
            $this->url                       = $this->get_option( 'developer_url' );
            $this->keyindex                  = $this->get_option( 'developer_keyindex' );
	        $this->paymentParametersRequired = $this->get_option( 'developer_ppr' );
	        $this->paymentMethod             = $this->get_option( 'developer_payment_method' );
        }

        $this->notify_url         = str_replace( 'http:', 'https:', home_url( '?wc-api=' . strtolower(__CLASS__ ))  );

        self::$log_enabled    = $this->debug;

        $this->init_cron_hook();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        add_action( 'woocommerce_api_' . strtolower(__CLASS__), array( $this, 'check_ipc_response' ) );

        add_action( 'woocommerce_order_item_add_action_buttons', array($this, 'check_payment_status_view'));

        add_action('save_post', array($this, 'check_payment_status_post'));

        if ( ! $this->is_valid_for_use() ) {
            $this->enabled = 'no';
        } else {
            add_action('woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        }

        $this->init_db();
    }

    public function init_cron_hook() {
        $nextScheduledTime = wp_next_scheduled('mypos_check_payment_status');
        if (!$nextScheduledTime) {
            wp_schedule_event(time(), 'hourly', 'mypos_check_payment_status');
        }
    }

    public static function init_db() {
        global $wpdb;
        $pendingPaymentConfirmTable = $wpdb->prefix . self::PENDING_PAYMENT_DB_TABLE_NAME;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        if ($wpdb->get_var("show tables like '$pendingPaymentConfirmTable'") != $pendingPaymentConfirmTable) {
            $sql = "
                CREATE TABLE " . $pendingPaymentConfirmTable . " (
                id int(11) NOT NULL AUTO_INCREMENT,
                order_id varchar(255) NOT NULL,
                last_check int NULL,
                expired_time int NOT NULL,
                test_environment boolean NOT NULL,
                UNIQUE KEY id (id)
            );";
            dbDelta($sql);
        }
    }

    /**
     * Check if this gateway is enabled and available in the user's country
     *
     * @return bool
     */
    public function is_valid_for_use() {
        return in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_ipc_supported_currencies', array( 'BGN', 'USD', 'EUR', 'GBP', 'CHF', 'JPY', 'RON', 'HRK', 'NOK', 'SEK', 'CZK', 'HUF', 'PLN', 'DKK', 'ISK', ) ) );
    }

    /**
     * Check if ideal is available option
     *
     * @return bool
     */
    public function is_valid_for_ideal() {
        return in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_ipc_supported_currencies', array( 'EUR' ) ) );
    }

    public function is_available()
    {
        return $this->paymentMethod == self::PAYMENT_METHOD_IDEAL ? $this->is_valid_for_ideal() && parent::is_available() : $this->is_valid_for_use() && parent::is_available();
    }

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     */
    public function admin_options() {
        if ( $this->is_valid_for_use() ) {
            parent::admin_options();
        } else {
            ?>
            <div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( 'myPOS Checkout does not support your store currency.', 'woocommerce' ); ?></p></div>
            <?php
        }
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = include( 'settings-ipc.php' );
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id){
        $order = new WC_Order($order_id);

        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url( true ),
        );
    }

    /**
     * Receipt Page
     * @param $order
     */
    public function receipt_page($order){
        echo $this->generate_ipc_form($order);
    }

    /**
     * Check for valid ipc response
     **/
    function check_ipc_response(){
        $this->log("Notify url request.");

        /**
         * @var WooCommerce $woocommerce
         */
        global $woocommerce;

        $post = $_POST;

        if($this->is_valid_signature($post)){
            if ($this->test) {
                $post['OrderID'] = str_replace($this->test_prefix, '', $post['OrderID']);
            }
            $order = new WC_Order($post['OrderID']);

            if ($post['IPCmethod'] == 'IPCPurchaseNotify')
            {
                $this->log("IPCPurchaseNotify request for order: " . $order->get_order_number());
                $order->payment_complete($post['IPC_Trnref']);
                $order->add_order_note('Gateway has authorized payment.<br/>Transaction Number: ' . $post['IPC_Trnref']);
                $woocommerce->cart->empty_cart();
                $this->remove_from_pending_payments_schedule($order->get_id());
                ob_get_clean();
                echo 'OK';
                exit;
            }
            else if ($post['IPCmethod'] == 'IPCPurchaseRollback')
            {
                $this->log("IPCPurchaseRollback request for order: " . $order->get_order_number());

                $order->update_status('failed');
                $order->add_order_note('Gateway has declined payment.');
                $woocommerce->cart->empty_cart();
                ob_get_clean();
                echo 'OK';
                exit;
            }
            else if ($post['IPCmethod'] == 'IPCPurchaseCancel')
            {
                $this->log("IPCPurchaseCancel request for order: " . $order->get_order_number());
                $this->remove_from_pending_payments_schedule($order->get_id());
                $order->update_status('cancelled');
                $order->add_order_note('User canceled the order.');

                $redirect_url = $order->get_cancel_order_url();
                wp_redirect( $redirect_url );
                exit;
            }
            else if ($post['IPCmethod'] == 'IPCPurchaseOK')
            {
                $this->log("IPCPurchaseOK request for order: " . $order->get_order_number());
                $woocommerce->cart->empty_cart();

                $redirect_url = $order->get_checkout_order_received_url();
                wp_redirect( $redirect_url );
                exit;
            }
            else
            {
                echo 'INVALID METHOD';
                exit;
            }
        }

        echo 'INVALID SIGNATURE';
        exit;
    }

    /**
     * Generate myPOS Checkout form
     * @param $order_id
     * @return string
     */
    public function generate_ipc_form($order_id){
        $order = new WC_Order($order_id);
        $this->add_to_pending_payments_schedule($order->get_id());

        $post = $this->create_post($order);
        $post_array = array();

        foreach($post as $key => $value){
            $value = htmlspecialchars($value, ENT_QUOTES);
            $post_array[] = "<input type='hidden' name='$key' value='$value'/>";
        }

        $this->log("Show payment form for order: " . $order_id);

        return '<div style="position: fixed; top: 0; left: 0; bottom: 0; right: 0; z-index: 9999; width: 100%; height: 100%; background-color: white;"></div>
                <style>* { display: none; }</style><form action="' . $this->url .'" method="post" name="mypos_virtual">
               ' . implode('', $post_array) . '
                    <button type="submit">' . __( 'Pay', 'woocommerce' ) . '</button>
                </form>
                <script>document.mypos_virtual.submit();</script>';
    }

    public function create_post(WC_Order $order)
    {
        $this->log("Create post data for order: " . $order->get_order_number());

        $post = array();

        $countries = include("countries.php");

        $post['IPCmethod'] = 'IPCPurchase';
        $post['IPCVersion'] = $this->version;
        $post['IPCLanguage'] = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr(get_locale(), 0, 2);
        $post['WalletNumber'] = $this->wallet_number;
        $post['SID'] = $this->sid;
        $post['keyindex'] = $this->keyindex;
        $post['Source'] = 'sc_wp_woocommerce 1.1.17 ' . PHP_VERSION . ' ' . bloginfo('version') ;

        $post['Amount'] = number_format($order->get_total(), 2, '.', '');
        $post['Currency'] = $order->get_currency();
        $post['OrderID'] = ($this->test ? $this->test_prefix : '') . $order->get_id();
        $post['URL_OK'] = $this->notify_url;
        $post['URL_CANCEL'] = $this->notify_url;
        $post['URL_Notify'] = $this->notify_url;
        $post['CustomerIP'] = $_SERVER['REMOTE_ADDR'];
        $post['CustomerEmail'] = $order->get_billing_email();
        $post['CustomerFirstNames'] = $order->get_billing_first_name();
        $post['CustomerFamilyName'] = $order->get_billing_last_name();
        $post['CustomerCountry'] = $countries[$order->get_billing_country()];
        $post['CustomerCity'] = $order->get_billing_city();
        $post['CustomerZIPCode'] = $order->get_billing_postcode();
        $post['CustomerAddress'] = $order->get_billing_address_1();
        $post['CustomerPhone'] = $order->get_billing_phone();
        $post['Note'] = 'myPOS Checkout WooCommerce Extension. Order Number: ' . $order->get_order_number();

	    $post['CardTokenRequest'] = 0;
	    $post['PaymentParametersRequired'] = $this->paymentParametersRequired;
	    $post['PaymentMethod'] = $this->paymentMethod;

        $index = 1;

        $this->line_items = $this->get_line_item_args($order);

        while (true)
        {
            if (isset($this->line_items['item_name_' . $index])) {
                $post['Article_' . $index] = str_replace("\r", "", str_replace("\n", "", do_shortcode($this->line_items['item_name_' . $index])));
                $post['Quantity_' . $index] = $this->line_items['quantity_' . $index];
                $post['Price_' . $index] = $this->line_items['amount_' . $index];
                $post['Amount_' . $index] = $this->number_format($this->line_items['amount_' . $index] * $this->line_items['quantity_' . $index], $order);
                $post['Currency_' . $index] = $post['Currency'];
            } else {
                break;
            }

            $index++;
        }

        if (isset($this->line_items['tax_cart']) && $this->line_items['tax_cart'] != 0) {
            $post['Article_' . $index] = 'Tax';
            $post['Quantity_' . $index] = 1;
            $post['Price_' . $index] = $this->line_items['tax_cart'];
            $post['Amount_' . $index] = $this->line_items['tax_cart'];
            $post['Currency_' . $index] = $post['Currency'];

            $index++;
        }

        if (isset($this->line_items['discount_amount_cart']) && $this->line_items['discount_amount_cart'] != 0) {
            $post['Article_' . $index] = 'Discount';
            $post['Quantity_' . $index] = 1;
            $post['Price_' . $index] = -$this->line_items['discount_amount_cart'];
            $post['Amount_' . $index] = -$this->line_items['discount_amount_cart'];
            $post['Currency_' . $index] = $post['Currency'];

            $index++;
        }

        $post['CartItems'] = $index - 1;

        $post['Signature'] = $this->create_signature($post);

        return $post;
    }

    private function add_to_pending_payments_schedule($order_id) {
        global $wpdb;
        $pay_order_id = ($this->test ? $this->test_prefix : '') . $order_id;

        $exists = $wpdb->get_var("SELECT exists(SELECT * FROM " . $wpdb->prefix . self::PENDING_PAYMENT_DB_TABLE_NAME
            . " WHERE order_id='" . $pay_order_id . "')");

        if ('0' === $exists) {
            $confirmationDate = new DateTime();
            $period = 'PT' . self::WAITING_CONFIRMATION_PERIOD_HOURS . 'H';
            $confirmationDate->add(new DateInterval($period));

            $wpdb->insert(
                $wpdb->prefix . self::PENDING_PAYMENT_DB_TABLE_NAME,
                array(
                    'order_id' => $pay_order_id,
                    'expired_time' => $confirmationDate->getTimestamp(),
                    'test_environment' => $this->test
                )
            );
        }
    }

    private function remove_from_pending_payments_schedule($order_id) {
        global $wpdb;

        $wpdb->delete(
            $wpdb->prefix . self::PENDING_PAYMENT_DB_TABLE_NAME,
            array(
                'order_id' => ($this->test ? $this->test_prefix : '') . $order_id,
            )
        );
    }

    private function create_signature($post)
    {
        $this->log("Create signature for order: " . $post['OrderID']);

        $concData = base64_encode(implode('-', $post));
        $privKeyObj = openssl_get_privatekey($this->private_key);
        openssl_sign($concData, $signature, $privKeyObj, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    public function is_valid_signature($post)
    {
        // Save signature
        $signature = $post['Signature'];

        // Remove signature from POST data array
        unset($post['Signature']);

        // Concatenate all values
        $concData = base64_encode(implode('-', $post));

        // Extract public key from certificate
        $pubKeyId = openssl_get_publickey($this->public_certificate);

        // Verify signature
        $result = openssl_verify($concData, base64_decode($signature), $pubKeyId, OPENSSL_ALGO_SHA256);

        //Free key resource
        openssl_free_key($pubKeyId);

        if ($result == 1) {
            return true;
        } else {
            $this->log('Invalid signature. ' . (isset($post['OrderID']) ? 'Order: ' . $post['OrderID'] : ''));
            return false;
        }
    }

    /**
     * Can the order be refunded via mypos virtual?
     * @param  WC_Order $order
     * @return bool
     */
    public function can_refund_order( $order ) {
        return $order && $order->get_transaction_id();
    }

    /**
     * Process a refund if supported
     * @param  int $order_id
     * @param  float $amount
     * @param  string $reason
     * @return  boolean True or false based on success, or a WP_Error object
     */
    public function process_refund( $order_id, $amount = null, $reason = '' ) {
        $order = wc_get_order( $order_id );

        if ( ! $this->can_refund_order( $order ) ) {
            return false;
        }

        $post = $this->create_refund_data($order, $amount);

        //open connection
        $ch = curl_init($this->url);

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
        curl_setopt($ch, CURLOPT_HEADER ,0); // DO NOT RETURN HTTP HEADERS
        curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); // RETURN THE CONTENTS OF THE CALL
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // Timeout on connect (2 minutes)

        //execute post
        $result = curl_exec($ch);
        curl_close($ch);

        $this->log('Execute IPCRefund for order : ' . $order->get_order_number());

        // Parse xml
        $post = $this->xml_to_post($result);

        if ($this->is_valid_signature($post))
        {
            if ($post['Status'] != 0)
            {
                $this->log('Refund failed for order: ' . $order->get_order_number() . '. Status: ' . $post['Status']);
                $order->add_order_note( sprintf( __( 'Refunded failed. Status: %s', 'woocommerce' ), $post['Status']) );
                return false;
            }
            else
            {
                $this->log('Refund succeeded for order: ' . $order->get_order_number());
                $order->add_order_note( sprintf( __( 'Refunded %s - Refund ID: %s', 'woocommerce' ), $post['Amount'], $post['IPC_Trnref'] . '-' . time() ) );
                return true;
            }
        }
        else
        {
            $this->log('Refund failed for order: ' . $order->get_order_number() . '. Invalid signature.');
            $order->add_order_note( sprintf( __( 'Refunded failed. Invalid signature.', 'woocommerce' )) );
            return false;
        }
    }

    public function check_payment_status_view()
    {
        ?>
            <button type="submit" name="mypos-check-payment-status" class="button mypos-check-payment-status" value="1">
                Check Payment Status
            </button>
        <?php
    }

    public function check_payment_status_post($order_id) {
        if (array_key_exists('mypos-check-payment-status', $_POST) && $_POST['mypos-check-payment-status']) {
            $this->check_payment_status($order_id);
        }
    }

    public function check_payment_status($order_id) {
        $order = wc_get_order( $order_id );

        if (empty($order)) {
            return false;
        }

        $this->line_items = $this->get_line_item_args($order);

        $data = $this->create_check_status_data($order->get_id());

        //open connection
        $ch = curl_init($this->url);

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0); // DO NOT RETURN HTTP HEADERS
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // RETURN THE CONTENTS OF THE CALL
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // Timeout on connect (2 minutes)

        //execute post
        $result = curl_exec($ch);
        curl_close($ch);

        // Parse xml
        $post = $this->xml_to_post($result);

        if ($this->is_valid_signature($post) && array_key_exists('PaymentStatus', $post)
            && array_key_exists('Amount', $post) && $post['Amount'] == number_format($order->get_total(), 2, '.', '')
            && array_key_exists('Currency', $post) && $post['Currency'] == $order->get_currency())
        {
            switch ($post['PaymentStatus']) {
                case 1:
                    if (!$order->is_paid()) {
                        $order->payment_complete($post['IPC_Trnref']);
                        $order->add_order_note('Gateway has authorized payment.<br/>Transaction Number: ' . $post['IPC_Trnref']);
                    }
                    break;
                case 3:
                    if  ($order->get_status() != 'failed') {
                        $order->update_status('failed');
                        $order->add_order_note('Gateway has authorized payment as error.');
                    }
                    break;
            }
        }
    }

    /**
     *
     */
    public function check_pending_payment_orders_statuses() {
        global $wpdb;
        $mypos_table = $wpdb->prefix . self::PENDING_PAYMENT_DB_TABLE_NAME;
        $currentDate = new DateTime();
        $currentTime = $currentDate->getTimestamp();

        $orders = $wpdb->get_results("SELECT order_id FROM " . $mypos_table
            . " WHERE test_environment = " . (int)$this->test . " ORDER BY last_check ASC LIMIT 0, 8");

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $this->check_payment_status($order->order_id);
            }
        }
        $ordersRaw = implode(',', array_map(function ($order) {
            return $order->order_id;
        }, $orders));
        $wpdb->query("UPDATE {$mypos_table} SET last_check = {$currentTime} WHERE order_id IN ({$ordersRaw})");
        $wpdb->query("DELETE FROM {$mypos_table} WHERE expired_time < {$currentTime}");
    }

    private function create_refund_data(WC_Order $order, $amount)
    {
        $this->log('Create refund data for order: ' . $order->get_order_number());

        $post = array();
        $post['IPCmethod'] = 'IPCRefund';
        $post['IPCVersion'] = $this->version;
        $post['IPCLanguage'] = 'en';
        $post['WalletNumber'] = $this->wallet_number;
        $post['SID'] = $this->sid;
        $post['keyindex'] = $this->keyindex;
	    $post['Source'] = 'sc_wp_woocommerce 1.1.17 ' . PHP_VERSION . ' ' . bloginfo('version');

        $post['IPC_Trnref'] = $order->get_transaction_id();
        $post['OrderID'] = $order->get_id();
        $post['Amount'] = number_format($amount, 2, '.', '');
        $post['Currency'] = $order->get_currency();
        $post['OutputFormat'] = 'xml';

        $post['Signature'] = $this->create_signature($post);

        return $post;
    }

    private function create_check_status_data($order_id)
    {

        $post = array();
        $post['IPCmethod'] = 'IPCGetPaymentStatus';
        $post['IPCVersion'] = $this->version;
        $post['IPCLanguage'] = 'en';
        $post['WalletNumber'] = $this->wallet_number;
        $post['SID'] = $this->sid;
        $post['keyindex'] = $this->keyindex;
        $post['Source'] = 'sc_wp_woocommerce 1.1.17 ' . PHP_VERSION . ' ' . bloginfo('version');

        $post['OrderID'] = $order_id;

        $post['OutputFormat'] = 'xml';

        $post['Signature'] = $this->create_signature($post);

        return $post;
    }


    public function xml_to_post($xml)
    {
        $xml = simplexml_load_string($xml);

        $post = array();

        /** @var \SimpleXMLElement $child */
	    foreach ($xml->children() as $child)
        {
            $post[$child->getName()] = (string) $child;
        }

        return $post;
    }

    /**
     * Get gateway icon.
     *
     * @return string
     */
    public function get_icon()
    {
        if ($this->paymentMethod == self::PAYMENT_METHOD_BOTH && $this->is_valid_for_ideal()) {
            $image_name = 'card_schemes_ideal_no_bg.png';
        } elseif ($this->paymentMethod == self::PAYMENT_METHOD_IDEAL && $this->is_valid_for_ideal()) {
            $image_name = 'mypos_ideal_no_bg.png';
        } else {
            $image_name = 'card_schemes_no_bg.png';
        }

        $icon = WC_HTTPS::force_https_url(plugins_url() . '/mypos-virtual-for-woocommerce/assets/images/' . $image_name );
        $icon_html = '<img src="' . $icon . '" alt="mypos_checkout_logo" />';

        return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
    }

    /**
     * Get line item args for mypos request.
     * @param  WC_Order $order
     * @return array
     */
    protected function get_line_item_args( $order ) {
        if (empty($order)) {
            return array();
        }

        /**
         * Try passing a line item per product if supported.
         */
        if ( ( ! wc_tax_enabled() || ! wc_prices_include_tax() ) && $this->prepare_line_items( $order ) ) {

            $line_item_args             = array();
            $line_item_args['tax_cart'] = $this->number_format( $order->get_total_tax(), $order );

            if ( $order->get_total_discount() > 0 ) {
                $line_item_args['discount_amount_cart'] = $this->number_format( $this->round( $order->get_total_discount(), $order ), $order );
            }

            if ( $order->get_shipping_total() > 0 ) {
                $this->add_line_item( sprintf( __( 'Shipping via %s', 'woocommerce' ), $order->get_shipping_method() ), 1, $this->number_format( $order->get_shipping_total(), $order ) );
            }

            $line_item_args = array_merge( $line_item_args, $this->get_line_items() );

            /**
             * Send order as a single item.
             */
        } else {

            $this->delete_line_items();

            $line_item_args = array();
            $all_items_name = $this->get_order_item_names( $order );
            $this->add_line_item( $all_items_name ? $all_items_name : __( 'Order', 'woocommerce' ), 1, $this->number_format( $order->get_total() - $this->round( $order->get_shipping_total() + $order->get_shipping_tax(), $order ), $order ), $order->get_order_number() );

            if ( $order->get_shipping_total() > 0 ) {
                $this->add_line_item( sprintf( __( 'Shipping via %s', 'woocommerce' ), $order->get_shipping_method() ), 1, $this->number_format( $order->get_shipping_total() + $order->get_shipping_tax(), $order ) );
            }

            $line_item_args = array_merge( $line_item_args, $this->get_line_items() );
        }

        return $line_item_args;
    }

    /**
     * Get order item names as a string.
     * @param  WC_Order $order
     * @return string
     */
    protected function get_order_item_names( $order ) {
        if (empty($order)) {
            return '';
        }

        $item_names = array();

        foreach ( $order->get_items() as $item ) {
            $item_names[] = $item['name'] . ' x ' . $item['qty'];
        }

        return implode( ', ', $item_names );
    }

    /**
     * Get order item names as a string.
     * @param  WC_Order $order
     * @param  array $item
     * @return string
     */
    protected function get_order_item_name( $order, $item ) {
        $item_name = $item['name'];
        $item_meta = new WC_Order_Item_Meta( $item );

        if ( $meta = $item_meta->display( true, true ) ) {
            $item_name .= ' ( ' . $meta . ' )';
        }

        return $item_name;
    }

    /**
     * Return all line items.
     */
    protected function get_line_items() {
        return $this->line_items;
    }

    /**
     * Remove all line items.
     */
    protected function delete_line_items() {
        $this->line_items = array();
    }

    /**
     * Get line items to send to mypos virtual.
     * @param  WC_Order $order
     * @return bool
     */
    protected function prepare_line_items( $order ) {
        $this->delete_line_items();
        $calculated_total = 0;

        // Products
        /** @var WC_Order_item $item */
        foreach ( $order->get_items( array( 'line_item', 'fee' ) ) as $item ) {
            if ( 'fee' === $item['type'] ) {
                $item_line_total  = $this->number_format( $item['line_total'], $order );
                $line_item        = $this->add_line_item( $item['name'], 1, $item_line_total );
                $calculated_total += $item_line_total;
            } else {
                /** @var WC_Product|bool $product */
                $product          = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;
                $sku              = $product ? $product->get_sku() : '';
                $item_line_total  = $this->number_format( $order->get_item_subtotal( $item, false ), $order );
                $line_item        = $this->add_line_item( $this->get_order_item_name( $order, $item ), $item['qty'], $item_line_total, $sku );
                $calculated_total += $item_line_total * $item['qty'];
            }

            if ( ! $line_item ) {
                return false;
            }
        }

        // Check for mismatched totals.
        if ( $this->number_format( $calculated_total + $order->get_total_tax() + $this->round( $order->get_shipping_total(), $order ) - $this->round( $order->get_total_discount(), $order ), $order ) != $this->number_format( $order->get_total(), $order ) ) {
            return false;
        }

        return true;
    }

    /**
     * Add Line Item.
     * @param  string  $item_name
     * @param  int     $quantity
     * @param  float   $amount
     * @param  string  $item_number
     * @return bool successfully added or not
     */
    protected function add_line_item( $item_name, $quantity = 1, $amount = 0.0, $item_number = '' ) {
        $index = ( sizeof( $this->line_items ) / 4 ) + 1;

        $this->line_items[ 'item_name_' . $index ]   = html_entity_decode( wc_trim_string( $item_name ? $item_name : __( 'Item', 'woocommerce' ), 127 ), ENT_NOQUOTES, 'UTF-8' );
        $this->line_items[ 'quantity_' . $index ]    = (int) $quantity;
        $this->line_items[ 'amount_' . $index ]      = (float) $amount;
        $this->line_items[ 'item_number_' . $index ] = $item_number;

        return true;
    }

    /**
     * Check if currency has decimals.
     * @param  string $currency
     * @return bool
     */
    protected function currency_has_decimals( $currency ) {
        if ( in_array( $currency, array( 'HUF', 'JPY', 'TWD' ) ) ) {
            return false;
        }

        return true;
    }

    /**
     * Round prices.
     * @param  double $price
     * @param  WC_Order $order
     * @return double
     */
    protected function round( $price, $order ) {
        $precision = 2;

        if ( ! $this->currency_has_decimals( $order->get_currency() ) ) {
            $precision = 0;
        }

        return round( $price, $precision );
    }

    /**
     * Format prices.
     * @param  float|int $price
     * @param  WC_Order $order
     * @return string
     */
    protected function number_format( $price, $order ) {
        $decimals = 2;

        if ( ! $this->currency_has_decimals( $order->get_currency() ) ) {
            $decimals = 0;
        }

        return number_format( $price, $decimals, '.', '' );
    }
}
