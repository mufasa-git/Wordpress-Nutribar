<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings for myPOS Checkout
 */
return array(
    'enabled' => array(
        'title'   => __( 'Enable/Disable', 'woocommerce' ),
        'label'   => __( 'Enable myPOS Checkout Payment', 'woocommerce' ),
        'type'    => 'checkbox',
        'default' => 'yes',
    ),
    'title' => array(
        'title'       => __( 'Title', 'woocommerce' ),
        'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
        'default'     => __( 'myPOS Checkout', 'woocommerce' ),
        'type'        => 'text',
        'desc_tip'    => true,
    ),
    'description' => array(
        'title'       => __( 'Description', 'woocommerce' ),
        'type'        => 'text',
        'desc_tip'    => true,
        'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
        'default'     => __( 'Pay via myPOS Checkout.', 'woocommerce' )
    ),
    'test' => array(
        'title'   => __( 'Test Mode', 'woocommerce' ),
        'label'   => __( 'Enable test mode', 'woocommerce' ),
        'type'    => 'checkbox',
        'default' => 'yes',
    ),
    'debug' => array(
        'title'   => __( 'Logging', 'woocommerce' ),
        'label'   => __( 'Enable logging', 'woocommerce' ),
        'type'    => 'checkbox',
        'default' => 'yes',
    ),
    'developer_options' => array(
        'title'       => __( 'Developer options', 'woocommerce' ),
        'type'        => 'title',
        'description' => '',
    ),
    'developer_sid' => array(
        'title'       => __( 'Store ID', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Store ID is given when you add a new online store. It could be reviewed in your online banking at www.mypos.eu > menu Online > Online stores.', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'developer_wallet_number' => array(
        'title'       => __( 'Client Number', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( 'You can view your myPOS Client number in your online banking at www.mypos.eu', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'developer_private_key' => array(
        'title'       => __( 'Private Key', 'woocommerce' ),
        'type'        => 'textarea',
        'description' => __( 'The Private Key for your store is generated in your online banking at www.mypos.eu > menu Online > Online stores > Keys.', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'developer_public_certificate' => array(
        'title'       => __( 'myPOS Public Certificate', 'woocommerce' ),
        'type'        => 'textarea',
        'description' => __( 'The myPOS Public Certificate is available for download in your online banking at www.mypos.eu > menu Online > Online stores > Keys.', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'developer_url' => array(
        'title'       => __( 'Developer URL', 'woocommerce' ),
        'type'        => 'text',
        'default'     => 'https://www.mypos.eu/vmp/checkout-test',
    ),
    'developer_keyindex' => array(
        'title'       => __( 'Developer Key Index', 'woocommerce' ),
        'type'        => 'text',
        'description' => __('The Key Index assigned to the certificate could be reviewed in your online banking at www.mypos.eu > menu Online > Online stores > Keys.', 'woocommerce'),
        'desc_tip'    => true,
    ),

    'developer_payment_method' => array(
        'title'       => __( 'Payment Method', 'woocommerce' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'desc_tip'    => true,
        'default'     => 3,
        'options' => array(
            '1' => __( 'Card Payment', 'woocommerce' ),
            '2' => __( 'iDeal', 'woocommerce' ),
            '3' => __( 'All', 'woocommerce' ),
        ),
    ),

    'developer_ppr' => array(
	    'title'       => __( 'Checkout form view', 'woocommerce' ),
	    'type'        => 'select',
	    'class'       => 'wc-enhanced-select',
	    'description' => __( '<strong>Full payment form</strong><br/>When you choose the "Full payment form", you can collect detailed customer information on checkout - customer names, address, phone number and email. Have in mind, that if your website has a shipping form, customer should double type some of the details. All fields are mandatory. Names and email address are not editable on the payment page.<br/><br/><strong>Simplified payment form</strong><br/>Similar to the "Full payment form". However, customer names and email addresses are editable on the payment page.<br/><br/><strong>Ultra-simplified payment form</strong><br/>The most basic payment form - it requires only card details. Use this only if you collect customer details on a prior page.' ),
	    'desc_tip'    => true,
	    'default'     => 3,
	    'options' => array(
		    '1' => __( 'Full payment form', 'woocommerce' ),
		    '2' => __( 'Simplified payment form', 'woocommerce' ),
		    '3' => __( 'Ultra-simplified payment form', 'woocommerce' ),
	    ),
    ),
    'production_options' => array(
        'title'       => __( 'Production options', 'woocommerce' ),
        'type'        => 'title',
        'description' => '',
    ),
    'production_sid' => array(
        'title'       => __( 'Store ID', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Store ID is given when you add a new online store. It could be reviewed in your online banking at www.mypos.eu > menu Online > Online stores.', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'production_wallet_number' => array(
        'title'       => __( 'Client Number', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( 'You can view your myPOS Client number in your online banking at www.mypos.eu', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'production_private_key' => array(
        'title'       => __( 'Private Key', 'woocommerce' ),
        'type'        => 'textarea',
        'description' => __( 'The Private Key for your store is generated in your online banking at www.mypos.eu > menu Online > Online stores > Keys.', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'production_public_certificate' => array(
        'title'       => __( 'myPOS Public Certificate', 'woocommerce' ),
        'type'        => 'textarea',
        'description' => __( 'The myPOS Public Certificate is available for download in your online banking at www.mypos.eu > menu Online > Online stores > Keys.', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'production_url' => array(
        'title'       => __( 'Production URL', 'woocommerce' ),
        'type'        => 'text',
        'default'     => 'https://www.mypos.eu/vmp/checkout',
    ),
    'production_keyindex' => array(
        'title'       => __( 'Production Key Index', 'woocommerce' ),
        'type'        => 'text',
        'description' => __('The Key Index assigned to the certificate could be reviewed in your online banking at www.mypos.eu > menu Online > Online stores > Keys.', 'woocommerce'),
        'desc_tip'    => true,
    ),

    'production_payment_method' => array(
        'title'       => __( 'Payment Method', 'woocommerce' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'desc_tip'    => true,
        'default'     => 3,
        'options' => array(
            '1' => __( 'Card Payment', 'woocommerce' ),
            '2' => __( 'iDeal', 'woocommerce' ),
            '3' => __( 'All', 'woocommerce' ),
        ),
    ),

    'production_ppr' => array(
	    'title'       => __( 'Checkout form view', 'woocommerce' ),
	    'type'        => 'select',
	    'class'       => 'wc-enhanced-select',
	    'description' => __( '<strong>Full payment form</strong><br/>When you choose the "Full payment form", you can collect detailed customer information on checkout - customer names, address, phone number and email. Have in mind, that if your website has a shipping form, customer should double type some of the details. All fields are mandatory. Names and email address are not editable on the payment page.<br/><br/><strong>Simplified payment form</strong><br/>Similar to the "Full payment form". However, customer names and email addresses are editable on the payment page.<br/><br/><strong>Ultra-simplified payment form</strong><br/>The most basic payment form - it requires only card details. Use this only if you collect customer details on a prior page.' ),
	    'desc_tip'    => true,
	    'default'     => 3,
	    'options' => array(
		    '1' => __( 'Full payment form', 'woocommerce' ),
		    '2' => __( 'Simplified payment form', 'woocommerce' ),
		    '3' => __( 'Ultra-simplified payment form', 'woocommerce' ),
	    ),
    ),
);
