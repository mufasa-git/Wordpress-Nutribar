<?php
/*
Plugin Name: WPC Product Bundles for WooCommerce
Plugin URI: https://wpclever.net/
Description: WPC Product Bundles is a plugin help you bundle a few products, offer them at a discount and watch the sales go up!
Version: 3.6.7
Author: WPclever.net
Author URI: https://wpclever.net
Text Domain: woo-product-bundle
Domain Path: /languages/
WC requires at least: 3.0
WC tested up to: 3.6.5
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WOOSB_VERSION' ) && define( 'WOOSB_VERSION', '3.6.7' );
! defined( 'WOOSB_URI' ) && define( 'WOOSB_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOSB_REVIEWS' ) && define( 'WOOSB_REVIEWS', 'https://wordpress.org/support/plugin/woo-product-bundle/reviews/?filter=5' );
! defined( 'WOOSB_CHANGELOG' ) && define( 'WOOSB_CHANGELOG', 'https://wordpress.org/plugins/woo-product-bundle/#developers' );
! defined( 'WOOSB_DISCUSSION' ) && define( 'WOOSB_DISCUSSION', 'https://wordpress.org/support/plugin/woo-product-bundle' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOSB_URI );

include 'includes/wpc-menu.php';
include 'includes/wpc-dashboard.php';

if ( ! function_exists( 'woosb_init' ) ) {
	add_action( 'plugins_loaded', 'woosb_init', 11 );

	function woosb_init() {
		// load text-domain
		load_plugin_textdomain( 'woo-product-bundle', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0.0', '>=' ) ) {
			add_action( 'admin_notices', 'woosb_notice_wc' );

			return;
		}

		if ( ! class_exists( 'WC_Product_Woosb' ) && class_exists( 'WC_Product' ) ) {
			class WC_Product_Woosb extends WC_Product {
				public function __construct( $product = 0 ) {
					$this->supports[] = 'ajax_add_to_cart';
					parent::__construct( $product );
				}

				public function get_type() {
					return 'woosb';
				}

				public function add_to_cart_url() {
					$product_id = $this->id;
					if ( $this->is_purchasable() && $this->is_in_stock() && ! $this->has_variables() && ! $this->is_optional() ) {
						$url = remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $product_id ) );
					} else {
						$url = get_permalink( $product_id );
					}

					return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
				}

				public function add_to_cart_text() {
					if ( $this->is_purchasable() && $this->is_in_stock() ) {
						if ( ! $this->has_variables() && ! $this->is_optional() ) {
							$text = get_option( '_woosb_archive_button_add' );
							if ( empty( $text ) ) {
								$text = esc_html__( 'Add to cart', 'woo-product-bundle' );
							}
						} else {
							$text = get_option( '_woosb_archive_button_select' );
							if ( empty( $text ) ) {
								$text = esc_html__( 'Select options', 'woo-product-bundle' );
							}
						}
					} else {
						$text = get_option( '_woosb_archive_button_read' );
						if ( empty( $text ) ) {
							$text = esc_html__( 'Read more', 'woo-product-bundle' );
						}
					}

					return apply_filters( 'woosb_product_add_to_cart_text', $text, $this );
				}

				public function single_add_to_cart_text() {
					$text = get_option( '_woosb_single_button_add' );
					if ( empty( $text ) ) {
						$text = esc_html__( 'Add to cart', 'woo-product-bundle' );
					}

					return apply_filters( 'woosb_product_single_add_to_cart_text', $text, $this );
				}

				public function get_stock_quantity( $context = 'view' ) {
					if ( ( $woosb_items = $this->get_items() ) && ! $this->is_optional() && ! $this->is_manage_stock() ) {
						$available_qty = array();
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_product = wc_get_product( $woosb_item['id'] );
							if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) || ( $woosb_product->get_stock_quantity() === null ) ) {
								continue;
							}
							$available_qty[] = floor( $woosb_product->get_stock_quantity() / $woosb_item['qty'] );
						}
						if ( count( $available_qty ) > 0 ) {
							sort( $available_qty );

							return (int) $available_qty[0];
						}

						return parent::get_stock_quantity( $context );
					}

					return parent::get_stock_quantity( $context );
				}

				public function get_manage_stock( $context = 'view' ) {
					if ( ( $woosb_items = $this->get_items() ) && ! $this->is_optional() && ! $this->is_manage_stock() ) {
						$manage_stock = false;
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_product = wc_get_product( $woosb_item['id'] );
							if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) ) {
								continue;
							}
							if ( $woosb_product->get_manage_stock( $context ) === true ) {
								return true;
							}
						}

						return $manage_stock;
					}

					return parent::get_manage_stock( $context );
				}

				public function get_backorders( $context = 'view' ) {
					if ( ( $woosb_items = $this->get_items() ) && ! $this->is_optional() && ! $this->is_manage_stock() ) {
						$backorders = 'yes';
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_product = wc_get_product( $woosb_item['id'] );
							if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) || ! $woosb_product->get_manage_stock() ) {
								continue;
							}
							if ( $woosb_product->get_backorders( $context ) === 'no' ) {
								return 'no';
							}
							if ( $woosb_product->get_backorders( $context ) === 'notify' ) {
								$backorders = 'notify';
							}
						}

						return $backorders;
					}

					return parent::get_backorders( $context );
				}

				public function get_stock_status( $context = 'view' ) {
					if ( ( $woosb_items = $this->get_items() ) && ! $this->is_optional() && ! $this->is_manage_stock() ) {
						$stock_status = 'instock';
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_product_id = $woosb_item['id'];
							$woosb_product    = wc_get_product( $woosb_product_id );
							if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) || ! $woosb_product->get_manage_stock() ) {
								continue;
							}
							$woosb_product_qty     = $woosb_item['qty'];
							$woosb_product_qty_min = absint( get_post_meta( $woosb_product_id, 'woosb_limit_each_min', true ) ?: 0 );
							$woosb_product_qty_max = absint( get_post_meta( $woosb_product_id, 'woosb_limit_each_max', true ) ?: 1000 );
							if ( $woosb_product_qty < $woosb_product_qty_min ) {
								$woosb_product_qty = $woosb_product_qty_min;
							}
							if ( ( $woosb_product_qty_max > $woosb_product_qty_min ) && ( $woosb_product_qty > $woosb_product_qty_max ) ) {
								$woosb_product_qty = $woosb_product_qty_max;
							}
							if ( ( $woosb_product->get_stock_status( $context ) === 'outofstock' ) || ( ! $woosb_product->has_enough_stock( $woosb_product_qty ) ) ) {
								return 'outofstock';
							}
							if ( $woosb_product->get_stock_status( $context ) === 'onbackorder' ) {
								$stock_status = 'onbackorder';
							}
						}

						return $stock_status;
					}

					return parent::get_stock_status( $context );
				}

				public function get_sold_individually( $context = 'view' ) {
					if ( ( $woosb_items = $this->get_items() ) && ! $this->is_optional() ) {
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_product_id = $woosb_item['id'];
							$woosb_product    = wc_get_product( $woosb_product_id );
							if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) ) {
								continue;
							}
							if ( $woosb_product->is_sold_individually() ) {
								return true;
							}
						}
					}

					return parent::get_sold_individually( $context );
				}

				public function is_on_sale( $context = 'view' ) {
					if ( ! $this->is_fixed_price() && ( ( $this->get_discount() > 0 ) || ( $this->get_discount_amount() > 0 ) ) ) {
						return true;
					}

					return parent::is_on_sale( $context );
				}

				public function get_sale_price( $context = 'view' ) {
					if ( ! $this->is_fixed_price() ) {
						if ( ( ( $discount_amount = $this->get_discount_amount() ) > 0 ) && ( $discount_amount < $this->get_regular_price() ) ) {
							return (float) $this->get_regular_price() - $discount_amount;
						}

						if ( $this->get_discount() > 0 ) {
							return (float) $this->get_regular_price() * ( 100 - $this->get_discount() ) / 100;
						}
					}

					return parent::get_sale_price( $context );
				}

				// extra functions

				public function has_variables() {
					if ( $woosb_items = $this->get_items() ) {
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_item_product = wc_get_product( $woosb_item['id'] );
							if ( $woosb_item_product && $woosb_item_product->is_type( 'variable' ) ) {
								return true;
							}
						}
					}

					return false;
				}

				public function is_optional() {
					$product_id = $this->id;

					return get_post_meta( $product_id, 'woosb_optional_products', true ) === 'on';
				}

				public function is_manage_stock() {
					$product_id = $this->id;

					return get_post_meta( $product_id, 'woosb_manage_stock', true ) === 'on';
				}

				public function is_fixed_price() {
					$product_id = $this->id;

					return get_post_meta( $product_id, 'woosb_disable_auto_price', true ) === 'on';
				}

				public function get_discount() {
					$product_id = $this->id;

					$discount = 0;

					if ( ( $woosb_discount = get_post_meta( $product_id, 'woosb_discount', true ) ) && is_numeric( $woosb_discount ) && ( (float) $woosb_discount < 100 ) && ( (float) $woosb_discount > 0 ) ) {
						$discount = (float) $woosb_discount;
					}

					return $discount;
				}

				public function get_discount_amount() {
					$product_id = $this->id;

					return (float) get_post_meta( $product_id, 'woosb_discount_amount', true );
				}

				public function get_items() {
					$product_id = $this->id;
					$woosb_arr  = array();
					if ( $woosb_ids = get_post_meta( $product_id, 'woosb_ids', true ) ) {
						$woosb_items = explode( ',', $woosb_ids );
						if ( is_array( $woosb_items ) && count( $woosb_items ) > 0 ) {
							foreach ( $woosb_items as $woosb_item ) {
								$woosb_item_arr = explode( '/', $woosb_item );
								$woosb_arr[]    = array(
									'id'  => absint( isset( $woosb_item_arr[0] ) ? $woosb_item_arr[0] : 0 ),
									'qty' => absint( isset( $woosb_item_arr[1] ) ? $woosb_item_arr[1] : 1 )
								);
							}
						}
					}
					if ( count( $woosb_arr ) > 0 ) {
						return $woosb_arr;
					}

					return false;
				}
			}
		}

		if ( ! class_exists( 'WPcleverWoosb' ) ) {
			class WPcleverWoosb {
				function __construct() {
					// Cron jobs auto sync price
					if ( get_option( '_woosb_price_sync', 'no' ) === 'yes' ) {
						add_action( 'wp', array( $this, 'woosb_wp' ) );
						add_filter( 'cron_schedules', array( $this, 'woosb_cron_add_time' ) );
						add_action( 'woosb_cron_jobs', array( $this, 'woosb_cron_jobs_event' ) );
					}
					register_deactivation_hook( __FILE__, array( $this, 'woosb_deactivation' ) );

					// Menu
					add_action( 'admin_menu', array( $this, 'woosb_admin_menu' ) );

					// Enqueue frontend scripts
					add_action( 'wp_enqueue_scripts', array( $this, 'woosb_wp_enqueue_scripts' ), 99 );

					// Enqueue backend scripts
					add_action( 'admin_enqueue_scripts', array( $this, 'woosb_admin_enqueue_scripts' ) );

					// Backend AJAX search
					add_action( 'wp_ajax_woosb_get_search_results', array( $this, 'woosb_get_search_results' ) );

					// Backend AJAX update price
					add_action( 'wp_ajax_woosb_update_price', array( $this, 'woosb_update_price_ajax' ) );

					// Add to selector
					add_filter( 'product_type_selector', array( $this, 'woosb_product_type_selector' ) );

					// Product data tabs
					add_filter( 'woocommerce_product_data_tabs', array( $this, 'woosb_product_data_tabs' ), 10, 1 );

					// Product tab
					if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'tab' ) || ( get_option( '_woosb_bundles_position', 'no' ) === 'tab' ) ) {
						add_filter( 'woocommerce_product_tabs', array( $this, 'woosb_product_tabs' ) );
					}

					// Product summary
					if ( get_option( '_woosb_bundles_position', 'no' ) === 'above' ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'woosb_single_product_summary'
						), 29 );
					} elseif ( get_option( '_woosb_bundles_position', 'no' ) === 'below' ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'woosb_single_product_summary'
						), 31 );
					}

					// Product filters
					add_filter( 'woocommerce_product_filters', array( $this, 'woosb_product_filters' ) );

					// Product data panels
					add_action( 'woocommerce_product_data_panels', array( $this, 'woosb_product_data_panels' ) );
					add_action( 'woocommerce_process_product_meta', array( $this, 'woosb_delete_option_fields' ) );
					add_action( 'woocommerce_process_product_meta_woosb', array( $this, 'woosb_save_option_fields' ) );

					// Add to cart form & button
					add_action( 'woocommerce_woosb_add_to_cart', array( $this, 'woosb_add_to_cart_form' ) );
					add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'woosb_add_to_cart_button' ) );

					// Add to cart
					add_filter( 'woocommerce_add_to_cart_validation', array(
						$this,
						'woosb_add_to_cart_validation'
					), 10, 2 );
					add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woosb_add_cart_item_data' ), 10, 2 );
					add_action( 'woocommerce_add_to_cart', array( $this, 'woosb_add_to_cart' ), 10, 6 );
					add_filter( 'woocommerce_get_cart_item_from_session', array(
						$this,
						'woosb_get_cart_item_from_session'
					), 10, 2 );

					// Cart item
					add_filter( 'woocommerce_cart_item_name', array( $this, 'woosb_cart_item_name' ), 10, 2 );
					add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woosb_cart_item_quantity' ), 10, 3 );
					add_filter( 'woocommerce_cart_item_remove_link', array(
						$this,
						'woosb_cart_item_remove_link'
					), 10, 2 );
					add_filter( 'woocommerce_cart_contents_count', array( $this, 'woosb_cart_contents_count' ) );
					add_action( 'woocommerce_after_cart_item_quantity_update', array(
						$this,
						'woosb_update_cart_item_quantity'
					), 10, 2 );
					add_action( 'woocommerce_before_cart_item_quantity_zero', array(
						$this,
						'woosb_update_cart_item_quantity'
					), 10 );
					add_action( 'woocommerce_cart_item_removed', array( $this, 'woosb_cart_item_removed' ), 10, 2 );
					add_filter( 'woocommerce_cart_item_price', array( $this, 'woosb_cart_item_price' ), 10, 2 );
					add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'woosb_cart_item_subtotal' ), 10, 2 );

					// Hide on cart & checkout page
					if ( get_option( '_woosb_hide_bundled', 'no' ) !== 'no' ) {
						add_filter( 'woocommerce_cart_item_visible', array( $this, 'woosb_item_visible' ), 10, 2 );
						add_filter( 'woocommerce_order_item_visible', array( $this, 'woosb_item_visible' ), 10, 2 );
						add_filter( 'woocommerce_checkout_cart_item_visible', array(
							$this,
							'woosb_item_visible'
						), 10, 2 );
					}

					// Hide on mini-cart
					if ( get_option( '_woosb_hide_bundled_mini_cart', 'no' ) === 'yes' ) {
						add_filter( 'woocommerce_widget_cart_item_visible', array(
							$this,
							'woosb_item_visible'
						), 10, 2 );
					}

					// Item class
					if ( get_option( '_woosb_hide_bundled', 'no' ) !== 'yes' ) {
						add_filter( 'woocommerce_cart_item_class', array( $this, 'woosb_item_class' ), 10, 2 );
						add_filter( 'woocommerce_mini_cart_item_class', array( $this, 'woosb_item_class' ), 10, 2 );
						add_filter( 'woocommerce_order_item_class', array( $this, 'woosb_item_class' ), 10, 2 );
					}

					// Get item data
					if ( get_option( '_woosb_hide_bundled', 'no' ) === 'yes_text' ) {
						add_filter( 'woocommerce_get_item_data', array(
							$this,
							'woosb_get_item_data'
						), 10, 2 );
						add_action( 'woocommerce_checkout_create_order_line_item', array(
							$this,
							'woosb_checkout_create_order_line_item'
						), 10, 4 );
					}

					// Order item
					add_action( 'woocommerce_checkout_create_order_line_item', array(
						$this,
						'woosb_add_order_item_meta'
					), 10, 3 );
					add_filter( 'woocommerce_order_item_name', array( $this, 'woosb_cart_item_name' ), 10, 2 );
					add_filter( 'woocommerce_order_formatted_line_subtotal', array(
						$this,
						'woosb_order_formatted_line_subtotal'
					), 10, 2 );

					// Admin order
					add_filter( 'woocommerce_hidden_order_itemmeta', array(
						$this,
						'woosb_hidden_order_item_meta'
					), 10, 1 );
					add_action( 'woocommerce_before_order_itemmeta', array(
						$this,
						'woosb_before_order_item_meta'
					), 10, 1 );

					// Undo remove
					add_action( 'woocommerce_restore_cart_item', array( $this, 'woosb_restore_cart_item' ), 10, 1 );

					// Add settings link
					add_filter( 'plugin_action_links', array( $this, 'woosb_action_links' ), 10, 2 );
					add_filter( 'plugin_row_meta', array( $this, 'woosb_row_meta' ), 10, 2 );

					// Loop add-to-cart
					add_filter( 'woocommerce_loop_add_to_cart_link', array(
						$this,
						'woosb_loop_add_to_cart_link'
					), 10, 2 );

					// Calculate totals
					add_action( 'woocommerce_before_calculate_totals', array(
						$this,
						'woosb_before_calculate_totals'
					), 10, 1 );
					add_action( 'woocommerce_after_calculate_totals', array(
						$this,
						'woosb_after_calculate_totals'
					), 10, 1 );
					add_filter( 'woocommerce_cart_get_subtotal', array( $this, 'woosb_cart_get_subtotal' ), 10, 1 );
					add_filter( 'woocommerce_calculated_total', array( $this, 'woosb_calculated_total' ), 10, 2 );

					// Shipping
					add_filter( 'woocommerce_cart_shipping_packages', array(
						$this,
						'woosb_cart_shipping_packages'
					) );

					// Price html
					add_filter( 'woocommerce_get_price_html', array( $this, 'woosb_get_price_html' ), 10, 2 );

					// Order again
					add_filter( 'woocommerce_order_again_cart_item_data', array(
						$this,
						'woosb_order_again_cart_item_data'
					), 10, 2 );
					add_action( 'woocommerce_cart_loaded_from_session', array(
						$this,
						'woosb_cart_loaded_from_session'
					) );

					// Metabox
					if ( get_option( '_woosb_price_update', 'no' ) === 'yes' ) {
						add_action( 'add_meta_boxes', array( $this, 'woosb_meta_boxes' ) );
						add_action( 'wp_ajax_woosb_metabox_update_price', array(
							$this,
							'woosb_metabox_update_price_ajax'
						) );
					}

					// Admin order
					add_action( 'woocommerce_ajax_add_order_item_meta', array(
						$this,
						'woosb_ajax_add_order_item_meta'
					), 10, 3 );

					// Emails
					add_action( 'woocommerce_no_stock_notification', array( $this, 'woosb_no_stock' ), 99 );
					add_action( 'woocommerce_low_stock_notification', array( $this, 'woosb_low_stock' ), 99 );

					// Search filters
					if ( get_option( '_woosb_search_sku', 'no' ) === 'yes' ) {
						add_filter( 'pre_get_posts', array( $this, 'woosb_search_sku' ), 99 );
					}
					if ( get_option( '_woosb_search_exact', 'no' ) === 'yes' ) {
						add_action( 'pre_get_posts', array( $this, 'woosb_search_exact' ), 99 );
					}
					if ( get_option( '_woosb_search_sentence', 'no' ) === 'yes' ) {
						add_action( 'pre_get_posts', array( $this, 'woosb_search_sentence' ), 99 );
					}
				}

				function woosb_wp() {
					if ( ! wp_next_scheduled( 'woosb_cron_jobs' ) ) {
						wp_schedule_event( time(), 'woosb_time', 'woosb_cron_jobs' );
					}
				}

				function woosb_cron_add_time( $schedules ) {
					$schedules['woosb_time'] = array(
						'interval' => 300,
						'display'  => esc_html__( 'Once Every 5 Minutes', 'woo-product-bundle' )
					);

					return $schedules;
				}

				function woosb_cron_jobs_event() {
					$this->woosb_update_price();
				}

				function woosb_update_price( $all = false, $num = - 1, $ajax = false ) {
					$count = 0;
					$time  = time() - 300;
					if ( $all ) {
						$woosb_query_args = array(
							'post_type'      => 'product',
							'post_status'    => 'publish',
							'posts_per_page' => $num,
							'tax_query'      => array(
								array(
									'taxonomy' => 'product_type',
									'field'    => 'slug',
									'terms'    => array( 'woosb' ),
									'operator' => 'IN',
								)
							)
						);
					} else {
						$woosb_query_args = array(
							'post_type'      => 'product',
							'post_status'    => 'publish',
							'posts_per_page' => $num,
							'tax_query'      => array(
								array(
									'taxonomy' => 'product_type',
									'field'    => 'slug',
									'terms'    => array( 'woosb' ),
									'operator' => 'IN',
								)
							),
							'meta_query'     => array(
								'relation' => 'OR',
								array(
									'key'     => 'woosb_update_price',
									'value'   => '',
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => 'woosb_update_price',
									'value'   => $time,
									'compare' => '<=',
								)
							)
						);
					}
					$woosb_query = new WP_Query( $woosb_query_args );
					if ( $woosb_query->have_posts() ) {
						while ( $woosb_query->have_posts() ) {
							$woosb_query->the_post();
							$product_id = get_the_ID();

							// update time
							update_post_meta( $product_id, 'woosb_update_price', time() );
							$this->woosb_update_price_for_id( $product_id );

							$count ++;
						}
						wp_reset_postdata();
					}
					if ( $ajax ) {
						echo $count;
					}
				}

				function woosb_update_price_for_id( $product_id ) {
					$product = wc_get_product( $product_id );
					if ( $product && $product->is_type( 'woosb' ) && ! $product->is_fixed_price() ) {
						// only update for auto price
						$regular_price = 0;
						$sale_price    = 0;

						$woosb_items = $this->woosb_get_items( $product_id );

						// calc regular price
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_product = wc_get_product( $woosb_item['id'] );
							if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) ) {
								continue;
							}
							$regular_price += $woosb_product->get_price() * $woosb_item['qty'];
						}

						// calc sale price
						if ( ( ( $discount_amount = $product->get_discount_amount() ) > 0 ) && ( $discount_amount < $regular_price ) ) {
							$sale_price = $regular_price - $discount_amount;
						} elseif ( ( $discount = $product->get_discount() ) > 0 ) {
							$sale_price = $regular_price * ( 100 - $discount ) / 100;
						}

						// update prices
						update_post_meta( $product_id, '_regular_price', $regular_price );
						if ( ( $sale_price > 0 ) && ( $sale_price < $regular_price ) ) {
							update_post_meta( $product_id, '_sale_price', $sale_price );
							update_post_meta( $product_id, '_price', $sale_price );
						} else {
							update_post_meta( $product_id, '_sale_price', '' );
							update_post_meta( $product_id, '_price', $regular_price );
						}
					}
				}

				function woosb_update_price_ajax() {
					$this->woosb_update_price( false, 100, true );
					die();
				}

				function woosb_metabox_update_price_ajax() {
					$count      = isset( $_POST['count'] ) ? (int) $_POST['count'] : 0;
					$product_id = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;

					if ( $bundles = $this->woosb_get_bundles( $product_id, 1, $count ) ) {
						foreach ( $bundles as $bundle ) {
							$this->woosb_update_price_for_id( $bundle->get_id() );
							echo '<li><a href="' . $bundle->get_permalink() . '" target="_blank">' . $bundle->get_name() . '</a></li>';
						}
					} else {
						echo '0';
					}
					die();
				}

				function woosb_admin_menu() {
					add_submenu_page( 'wpclever', esc_html__( 'Product Bundles', 'woo-product-bundle' ), esc_html__( 'Product Bundles', 'woo-product-bundle' ), 'manage_options', 'wpclever-woosb', array(
						&$this,
						'woosb_admin_menu_content'
					) );
				}

				function woosb_admin_menu_content() {
					add_thickbox();
					$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
					?>
                    <div class="wpclever_settings_page wrap">
                        <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Product Bundles', 'woo-product-bundle' ) . ' ' . WOOSB_VERSION; ?></h1>
                        <div class="wpclever_settings_page_desc about-text">
                            <p>
								<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'woo-product-bundle' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                                <br/>
                                <a href="<?php echo esc_url( WOOSB_REVIEWS ); ?>"
                                   target="_blank"><?php esc_html_e( 'Reviews', 'woo-product-bundle' ); ?></a> | <a
                                        href="<?php echo esc_url( WOOSB_CHANGELOG ); ?>"
                                        target="_blank"><?php esc_html_e( 'Changelog', 'woo-product-bundle' ); ?></a>
                                | <a href="<?php echo esc_url( WOOSB_DISCUSSION ); ?>"
                                     target="_blank"><?php esc_html_e( 'Discussion', 'woo-product-bundle' ); ?></a>
                            </p>
                        </div>
                        <div class="wpclever_settings_page_nav">
                            <h2 class="nav-tab-wrapper">
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=how' ); ?>"
                                   class="<?php echo $active_tab === 'how' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'How to use?', 'woo-product-bundle' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=settings' ); ?>"
                                   class="<?php echo $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Settings', 'woo-product-bundle' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=tools' ); ?>"
                                   class="<?php echo $active_tab === 'tools' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Tools', 'woo-product-bundle' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=premium' ); ?>"
                                   class="<?php echo $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Premium Version', 'woo-product-bundle' ); ?>
                                </a>
                            </h2>
                        </div>
                        <div class="wpclever_settings_page_content">
							<?php if ( $active_tab === 'how' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>
										<?php esc_html_e( 'When creating the product, please choose product data is "Smart Bundle" then you can see the search field to start search and add products to the bundle.', 'woo-product-bundle' ); ?>
                                    </p>
                                    <p>
                                        <img src="<?php echo WOOSB_URI; ?>assets/images/how-01.jpg"/>
                                    </p>
                                </div>
							<?php } elseif ( $active_tab === 'settings' ) { ?>
                                <form method="post" action="options.php">
									<?php wp_nonce_field( 'update-options' ) ?>
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'General', 'woo-product-bundle' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Price format', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_price_format">
                                                    <option value="from_min" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'from_min' ? 'selected' : '' ); ?>><?php esc_html_e( 'From min price', 'woo-product-bundle' ); ?></option>
                                                    <option value="min_only" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'min_only' ? 'selected' : '' ); ?>><?php esc_html_e( 'Min price only', 'woo-product-bundle' ); ?></option>
                                                    <option value="min_max" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'min_max' ? 'selected' : '' ); ?>><?php esc_html_e( 'Min - max', 'woo-product-bundle' ); ?></option>
                                                    <option value="normal" <?php echo( get_option( '_woosb_price_format', 'from_min' ) === 'normal' ? 'selected' : '' ); ?>><?php esc_html_e( 'Regular and sale price', 'woo-product-bundle' ); ?></option>
                                                </select>
                                                <span class="description">
                                                    <?php esc_html_e( 'Choose the price format for bundle on the shop page.', 'woo-product-bundle' ); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Bundled products', 'woo-product-bundle' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Position', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_bundled_position">
                                                    <option
                                                            value="above" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'above' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Above add to cart button', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="below" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'below' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Below add to cart button', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="tab" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'tab' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'In a new tab', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_bundled_position', 'above' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No (hide it)', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span class="description">
                                                    <?php esc_html_e( 'Choose the position to show the bundled products list.', 'woo-product-bundle' ); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show thumbnail', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_bundled_thumb">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_bundled_thumb', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_bundled_thumb', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show quantity', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_bundled_qty">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_bundled_qty', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_bundled_qty', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show short description', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_bundled_description">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_bundled_description', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show price', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_bundled_price">
                                                    <option
                                                            value="price" <?php echo( get_option( '_woosb_bundled_price', 'html' ) === 'price' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Price', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="html" <?php echo( get_option( '_woosb_bundled_price', 'html' ) === 'html' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Price HTML', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="subtotal" <?php echo( get_option( '_woosb_bundled_price', 'html' ) === 'subtotal' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Subtotal', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_bundled_price', 'html' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Link to individual product', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_bundled_link">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the same tab', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="yes_blank" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_blank' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the new tab', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="yes_popup" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_popup' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open quick view popup', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_bundled_link', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span class="description">If you choose "Open quick view popup", please install <a
                                                            href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woo-smart-quick-view&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                            class="thickbox" title="Install WPC Smart Quick View">WPC Smart Quick View</a> to make it work.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change image', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_change_image">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_change_image', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_change_image', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Change the main product image when choosing the variation of bundled products.', 'woo-product-bundle' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change price', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_change_price">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_change_price', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_change_price', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Change the main product price when choosing the variation of bundled products. It uses JavaScript to change product price so it is very dependent on theme’s HTML. If it cannot find and update the product price, please contact us and we can help you adjust the JS file.', 'woo-product-bundle' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Total text', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <input type="text" name="_woosb_bundle_price_text"
                                                       value="<?php echo get_option( '_woosb_bundle_price_text', esc_html__( 'Bundle price:', 'woo-product-bundle' ) ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Saved text', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <input type="text" name="_woosb_bundle_saved_text"
                                                       value="<?php echo get_option( '_woosb_bundle_saved_text', esc_html__( '(saved [d])', 'woo-product-bundle' ) ); ?>"/>
                                                <span class="description">
											<?php esc_html_e( 'Use [d] to show the saved percentage or amount.', 'woo-product-bundle' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Bundles', 'woo-product-bundle' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for bundles on the single product page.', 'woo-product-bundle' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Position', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_bundles_position">
                                                    <option
                                                            value="above" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'above' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Above add to cart button', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="below" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'below' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Below add to cart button', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="tab" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'tab' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'In a new tab', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_bundles_position', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No (hide it)', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span class="description">
                                                    <?php esc_html_e( 'Choose the position to show the bundles list.', 'woo-product-bundle' ); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
												<?php esc_html_e( 'Before text', 'woo-product-bundle' ); ?>
                                            </th>
                                            <td>
                                                <textarea name="_woosb_bundles_before_text" class="large-text"
                                                          placeholder="<?php esc_html_e( 'The text before bundles', 'woo-product-bundle' ); ?>"><?php echo stripslashes( get_option( '_woosb_bundles_before_text' ) ); ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
												<?php esc_html_e( 'After text', 'woo-product-bundle' ); ?>
                                            </th>
                                            <td>
                                                <textarea name="_woosb_bundles_after_text" class="large-text"
                                                          placeholder="<?php esc_html_e( 'The text after bundles', 'woo-product-bundle' ); ?>"><?php echo stripslashes( get_option( '_woosb_bundles_after_text' ) ); ?></textarea>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( '"Add to Cart" button labels', 'woo-product-bundle' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Leave blank if you want to use the default text and can be translated.', 'woo-product-bundle' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Archive/shop page', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <input type="text" name="_woosb_archive_button_add"
                                                       value="<?php echo get_option( '_woosb_archive_button_add' ); ?>"
                                                       placeholder="<?php esc_html_e( 'Add to cart', 'woo-product-bundle' ); ?>"/>
                                                <span class="description">
											<?php esc_html_e( 'For purchasable bundle.', 'woo-product-bundle' ); ?>
										</span><br/>
                                                <input type="text" name="_woosb_archive_button_select"
                                                       value="<?php echo get_option( '_woosb_archive_button_select' ); ?>"
                                                       placeholder="<?php esc_html_e( 'Select options', 'woo-product-bundle' ); ?>"/>
                                                <span class="description">
											<?php esc_html_e( 'For purchasable bundle and has variable product(s).', 'woo-product-bundle' ); ?>
										</span><br/>
                                                <input type="text" name="_woosb_archive_button_read"
                                                       value="<?php echo get_option( '_woosb_archive_button_read' ); ?>"
                                                       placeholder="<?php esc_html_e( 'Read more', 'woo-product-bundle' ); ?>"/>
                                                <span class="description">
											<?php esc_html_e( 'For un-purchasable bundle.', 'woo-product-bundle' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Single product page', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <input type="text" name="_woosb_single_button_add"
                                                       value="<?php echo get_option( '_woosb_single_button_add' ); ?>"
                                                       placeholder="<?php esc_html_e( 'Add to cart', 'woo-product-bundle' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Cart & Checkout', 'woo-product-bundle' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Cart contents count', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_cart_contents_count">
                                                    <option
                                                            value="bundle" <?php echo( get_option( '_woosb_cart_contents_count', 'bundle' ) === 'bundle' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Bundle only', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="bundled_products" <?php echo( get_option( '_woosb_cart_contents_count', 'bundle' ) === 'bundled_products' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Bundled products only', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="both" <?php echo( get_option( '_woosb_cart_contents_count', 'bundle' ) === 'both' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Both bundle and bundled products', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Hide bundle name before bundled products', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_hide_bundle_name">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_hide_bundle_name', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_hide_bundle_name', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Hide bundled products on cart & checkout page', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_hide_bundled">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_hide_bundled', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, just show the main product', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="yes_text" <?php echo( get_option( '_woosb_hide_bundled', 'no' ) === 'yes_text' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, but show bundled product names under the main product', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_hide_bundled', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Hide bundled products on mini-cart', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_hide_bundled_mini_cart">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_hide_bundled_mini_cart', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_hide_bundled_mini_cart', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Hide bundled products, just show the main product on mini-cart.', 'woo-product-bundle' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Search', 'woo-product-bundle' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search limit', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <input name="_woosb_search_limit" type="number" min="1"
                                                       max="500"
                                                       value="<?php echo get_option( '_woosb_search_limit', '5' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search by SKU', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_search_sku">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_search_sku', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_search_sku', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search by ID', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_search_id">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_search_id', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_search_id', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Search by ID when only entered the numeric.', 'woo-product-bundle' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search exact', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_search_exact">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_search_exact', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_search_exact', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Match whole product title or content?', 'woo-product-bundle' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search sentence', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_search_sentence">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_search_sentence', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_search_sentence', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Do a phrase search?', 'woo-product-bundle' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Accept same products', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_search_same">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_search_same', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_search_same', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'If yes, a product can be added many times.', 'woo-product-bundle' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Advance', 'woo-product-bundle' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Price sync', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_price_sync">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_price_sync', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_price_sync', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span class="description">
                                                    <?php esc_html_e( 'Enable this option to change the bundle price automatically when changing the price of the bundled product. You also can do it by manually on the Tools tab.', 'woo-product-bundle' ); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Price update', 'woo-product-bundle' ); ?></th>
                                            <td>
                                                <select name="_woosb_price_update">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woosb_price_update', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-product-bundle' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woosb_price_update', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-product-bundle' ); ?>
                                                    </option>
                                                </select> <span class="description">
                                                    <?php esc_html_e( 'Enable this option to show the update price tool in each product. Use this tool to update the price for all bundles contain the current product.', 'woo-product-bundle' ); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_html_e( 'Update Options', 'woo-product-bundle' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options"
                                                       value="_woosb_price_format,_woosb_price_sync,_woosb_price_update,_woosb_bundled_position,_woosb_bundled_thumb,_woosb_bundled_qty,_woosb_bundled_description,_woosb_bundled_price,_woosb_bundled_link,_woosb_change_image,_woosb_change_price,_woosb_cart_contents_count,_woosb_hide_bundle_name,_woosb_hide_bundled,_woosb_hide_bundled_mini_cart,_woosb_bundle_price_text,_woosb_bundle_saved_text,_woosb_bundles_position,_woosb_bundles_before_text,_woosb_bundles_after_text,_woosb_archive_button_add,_woosb_archive_button_select,_woosb_archive_button_read,_woosb_single_button_add,_woosb_search_limit,_woosb_search_sku,_woosb_search_id,_woosb_search_exact,_woosb_search_sentence,_woosb_search_same"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'tools' ) { ?>
                                <table class="form-table">
                                    <tr>
                                        <th>
                                            <strong
                                                    class="name"><?php esc_html_e( 'Product bundles', 'woo-product-bundle' ); ?></strong>
                                        </th>
                                        <td>
                                            <a class="button button-large"
                                               href="<?php echo admin_url( 'edit.php?s&post_type=product&product_type=woosb' ); ?>">
												<?php esc_html_e( 'View all product bundles', 'woo-product-bundle' ); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <strong class="name"><?php esc_html_e( 'Price sync', 'woo-product-bundle' ); ?></strong>
                                        </th>
                                        <td>
                                            <p class="woosb_updated_price_ajax" style="color: green;"></p>
                                            <a class="button button-large woosb-update-price-btn" href="#">
												<?php esc_html_e( 'Update price for all bundles', 'woo-product-bundle' ); ?>
                                            </a>
                                            <p class="description">
												<?php esc_html_e( 'The bundle price will be updated every 5 minutes automatically, you can click to update immediately.', 'woo-product-bundle' ); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
							<?php } elseif ( $active_tab === 'premium' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>
                                        Get the Premium Version just $29! <a
                                                href="https://wpclever.net/downloads/woocommerce-product-bundle"
                                                target="_blank">https://wpclever.net/downloads/woocommerce-product-bundle</a>
                                    </p>
                                    <p><strong>Extra features for Premium Version</strong></p>
                                    <ul style="margin-bottom: 0">
                                        <li>- Add more than 3 products to the bundle</li>
                                        <li>- Get the lifetime update & premium support</li>
                                    </ul>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}

				function woosb_wp_enqueue_scripts() {
					wp_enqueue_style( 'woosb-frontend', WOOSB_URI . 'assets/css/frontend.css' );
					wp_enqueue_script( 'woosb-frontend', WOOSB_URI . 'assets/js/frontend.js', array( 'jquery' ), WOOSB_VERSION, true );
					wp_localize_script( 'woosb-frontend', 'woosb_vars', array(
							'alert_selection'          => esc_html__( 'Please select some product options before adding this bundle to the cart.', 'woo-product-bundle' ),
							'alert_empty'              => esc_html__( 'Please choose at least one product before adding this bundle to the cart.', 'woo-product-bundle' ),
							'alert_min'                => esc_html__( 'Please choose at least [min] in the whole products before adding this bundle to the cart.', 'woo-product-bundle' ),
							'alert_max'                => esc_html__( 'Please choose maximum [max] in the whole products before adding this bundle to the cart.', 'woo-product-bundle' ),
							'price_text'               => get_option( '_woosb_bundle_price_text', '' ),
							'saved_text'               => get_option( '_woosb_bundle_saved_text', esc_html__( '(saved [d])', 'woo-product-bundle' ) ),
							'change_image'             => get_option( '_woosb_change_image', 'yes' ),
							'change_price'             => get_option( '_woosb_change_price', 'yes' ),
							'price_format'             => get_woocommerce_price_format(),
							'price_decimals'           => wc_get_price_decimals(),
							'price_thousand_separator' => wc_get_price_thousand_separator(),
							'price_decimal_separator'  => wc_get_price_decimal_separator(),
							'currency_symbol'          => get_woocommerce_currency_symbol(),
							'ver'                      => WOOSB_VERSION
						)
					);
				}

				function woosb_admin_enqueue_scripts() {
					wp_enqueue_style( 'hint', WOOSB_URI . 'assets/css/hint.css' );
					wp_enqueue_style( 'woosb-backend', WOOSB_URI . 'assets/css/backend.css' );
					wp_enqueue_script( 'dragarrange', WOOSB_URI . 'assets/js/drag-arrange.js', array( 'jquery' ), WOOSB_VERSION, true );
					wp_enqueue_script( 'accounting', WOOSB_URI . 'assets/js/accounting.js', array( 'jquery' ), WOOSB_VERSION, true );
					wp_enqueue_script( 'woosb-backend', WOOSB_URI . 'assets/js/backend.js', array( 'jquery' ), WOOSB_VERSION, true );
					wp_localize_script( 'woosb-backend', 'woosb_vars', array(
							'price_decimals'           => wc_get_price_decimals(),
							'price_thousand_separator' => wc_get_price_thousand_separator(),
							'price_decimal_separator'  => wc_get_price_decimal_separator()
						)
					);
				}

				function woosb_action_links( $links, $file ) {
					static $plugin;
					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}
					if ( $plugin === $file ) {
						$settings_link = '<a href="' . admin_url( 'admin.php?page=wpclever-woosb&tab=settings' ) . '">' . esc_html__( 'Settings', 'woo-product-bundle' ) . '</a>';
						$links[]       = '<a href="' . admin_url( 'admin.php?page=wpclever-woosb&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'woo-product-bundle' ) . '</a>';
						array_unshift( $links, $settings_link );
					}

					return (array) $links;
				}

				function woosb_row_meta( $links, $file ) {
					static $plugin;
					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}
					if ( $plugin === $file ) {
						$row_meta = array(
							'support' => '<a href="https://wpclever.net/contact" target="_blank">' . esc_html__( 'Premium support', 'woo-product-bundle' ) . '</a>',
						);

						return array_merge( $links, $row_meta );
					}

					return (array) $links;
				}

				function woosb_cart_contents_count( $count ) {
					$cart_contents_count = get_option( '_woosb_cart_contents_count', 'bundle' );

					if ( $cart_contents_count !== 'both' ) {
						$cart_contents = WC()->cart->cart_contents;
						foreach ( $cart_contents as $cart_item_key => $cart_item ) {
							if ( ( $cart_contents_count === 'bundled_products' ) && ! empty( $cart_item['woosb_ids'] ) ) {
								$count -= $cart_item['quantity'];
							}
							if ( ( $cart_contents_count === 'bundle' ) && ! empty( $cart_item['woosb_parent_id'] ) ) {
								$count -= $cart_item['quantity'];
							}
						}
					}

					return $count;
				}

				function woosb_cart_item_name( $name, $item ) {
					if ( isset( $item['woosb_parent_id'] ) && ! empty( $item['woosb_parent_id'] ) && ( get_option( '_woosb_hide_bundle_name', 'no' ) === 'no' ) ) {
						if ( ( strpos( $name, '</a>' ) !== false ) && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
							return '<a href="' . get_permalink( $item['woosb_parent_id'] ) . '">' . get_the_title( $item['woosb_parent_id'] ) . '</a> &rarr; ' . $name;
						}

						return get_the_title( $item['woosb_parent_id'] ) . ' &rarr; ' . strip_tags( $name );
					}

					return $name;
				}

				function woosb_update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] ) ) {
						foreach ( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] as $woosb_key ) {
							if ( isset( WC()->cart->cart_contents[ $woosb_key ] ) ) {
								if ( $quantity <= 0 ) {
									$woosb_qty = 0;
								} else {
									$woosb_qty = $quantity * ( WC()->cart->cart_contents[ $woosb_key ]['woosb_qty'] ?: 1 );
								}
								WC()->cart->set_quantity( $woosb_key, $woosb_qty, false );
							}
						}
					}
				}

				function woosb_cart_item_removed( $cart_item_key, $cart ) {
					if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['woosb_keys'] ) ) {
						$woosb_keys = $cart->removed_cart_contents[ $cart_item_key ]['woosb_keys'];
						foreach ( $woosb_keys as $woosb_key ) {
							unset( $cart->cart_contents[ $woosb_key ] );
						}
					}
				}

				function woosb_check_in_cart( $product_id ) {
					foreach ( WC()->cart->get_cart() as $cart_item ) {
						if ( $cart_item['product_id'] === $product_id ) {
							return true;
						}
					}

					return false;
				}

				function woosb_add_to_cart_validation( $passed, $product_id ) {
					if ( $woosb_ids = get_post_meta( $product_id, 'woosb_ids', true ) ) {
						if ( isset( $_POST['woosb_ids'] ) ) {
							$woosb_ids = $this->woosb_clean_ids( $_POST['woosb_ids'] );
						}
						$woosb_qty = isset( $_POST['quantity'] ) ? (int) $_POST['quantity'] : 1;

						$woosb_ids = $this->woosb_clean_ids( $woosb_ids );
						if ( ! empty( $woosb_ids ) ) {
							$woosb_items = explode( ',', $woosb_ids );

							foreach ( $woosb_items as $woosb_item ) {
								$woosb_item_data = explode( '/', $woosb_item );
								$woosb_item_id   = absint( $woosb_item_data[0] ?: 0 );
								$woosb_item_qty  = absint( $woosb_item_data[1] ?: 1 );
								$woosb_product   = wc_get_product( $woosb_item_id );

								if ( ! $woosb_product || $woosb_product->is_type( 'variable' ) || ! $woosb_product->is_in_stock() || ! $woosb_product->is_purchasable() ) {
									wc_add_notice( esc_html__( 'Have an error when adding this bundle to the cart.', 'woo-product-bundle' ), 'error' );

									return false;
								}

								if ( ! $woosb_product->has_enough_stock( $woosb_item_qty * $woosb_qty ) ) {
									wc_add_notice( sprintf( esc_html__( '"%s" has not enough stock.', 'woo-product-bundle' ), esc_html( $woosb_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

									return false;
								}

								if ( $woosb_product->is_sold_individually() && $this->woosb_check_in_cart( $woosb_item_id ) ) {
									wc_add_notice( sprintf( esc_html__( 'You cannot add another "%s" to your cart.', 'woo-product-bundle' ), esc_html( $woosb_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

									return false;
								}

								if ( $woosb_product->managing_stock() ) {
									$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

									if ( isset( $products_qty_in_cart[ $woosb_product->get_stock_managed_by_id() ] ) && ! $woosb_product->has_enough_stock( $products_qty_in_cart[ $woosb_product->get_stock_managed_by_id() ] + $woosb_item_qty * $woosb_qty ) ) {
										wc_add_notice( sprintf( esc_html__( '"%s" has not enough stock.', 'woo-product-bundle' ), esc_html( $woosb_product->get_name() ) ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

										return false;
									}
								}

								if ( post_password_required( $woosb_item_id ) ) {
									wc_add_notice( sprintf( esc_html__( '"%s" is protected and cannot be purchased.', 'woo-product-bundle' ), esc_html( $woosb_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this bundle to the cart.', 'woo-product-bundle' ), 'error' );

									return false;
								}
							}
						}
					}

					return $passed;
				}

				function woosb_add_cart_item_data( $cart_item_data, $product_id ) {
					if ( $woosb_ids = get_post_meta( $product_id, 'woosb_ids', true ) ) {
						// make sure that is bundle
						if ( isset( $_POST['woosb_ids'] ) ) {
							$woosb_ids = $this->woosb_clean_ids( $_POST['woosb_ids'] );
							unset( $_POST['woosb_ids'] );
						}

						$woosb_ids = $this->woosb_clean_ids( $woosb_ids );
						if ( ! empty( $woosb_ids ) ) {
							$cart_item_data['woosb_ids'] = $woosb_ids;
						}
					}

					return $cart_item_data;
				}

				function woosb_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
					if ( ! empty( $cart_item_data['woosb_ids'] ) && method_exists( WC()->cart->cart_contents[ $cart_item_key ]['data'], 'is_fixed_price' ) && method_exists( WC()->cart->cart_contents[ $cart_item_key ]['data'], 'get_discount' ) ) {
						$items = explode( ',', $cart_item_data['woosb_ids'] );

						$this->woosb_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
					}
				}

				function woosb_restore_cart_item( $cart_item_key ) {
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_ids'] ) ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] );

						$product_id = WC()->cart->cart_contents[ $cart_item_key ]['product_id'];
						$quantity   = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
						$items      = explode( ',', WC()->cart->cart_contents[ $cart_item_key ]['woosb_ids'] );

						$this->woosb_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
					}
				}

				function woosb_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity ) {
					$woosb_fixed_price         = WC()->cart->cart_contents[ $cart_item_key ]['data']->is_fixed_price();
					$woosb_get_discount        = WC()->cart->cart_contents[ $cart_item_key ]['data']->get_discount();
					$woosb_get_discount_amount = WC()->cart->cart_contents[ $cart_item_key ]['data']->get_discount_amount();

					WC()->cart->cart_contents[ $cart_item_key ]['woosb_fixed_price']         = $woosb_fixed_price;
					WC()->cart->cart_contents[ $cart_item_key ]['woosb_get_discount']        = $woosb_get_discount;
					WC()->cart->cart_contents[ $cart_item_key ]['woosb_get_discount_amount'] = $woosb_get_discount_amount;

					if ( is_array( $items ) && ( count( $items ) > 0 ) ) {
						$woosb_i = 0; // for same bundled product
						foreach ( $items as $item ) {
							$woosb_i ++;
							$woosb_item     = explode( '/', $item );
							$woosb_item_id  = absint( isset( $woosb_item[0] ) ? $woosb_item[0] : 0 );
							$woosb_item_qty = absint( isset( $woosb_item[1] ) ? $woosb_item[1] : 1 );

							$woosb_item_product = wc_get_product( $woosb_item_id );

							if ( ! $woosb_item_product || ( $woosb_item_qty <= 0 ) ) {
								continue;
							}

							$woosb_item_price = $woosb_item_product->get_price();

							$woosb_item_variation_id = 0;
							$woosb_item_variation    = array();

							if ( 'product_variation' === get_post_type( $woosb_item_id ) ) {
								// ensure we don't add a variation to the cart directly by variation ID
								$woosb_item_variation_id = $woosb_item_id;
								$woosb_item_id           = wp_get_post_parent_id( $woosb_item_variation_id );
								$woosb_item_variation    = $woosb_item_product->get_attributes();
							}

							if ( ! $woosb_fixed_price && ( $woosb_get_discount_amount <= 0 ) && ( $woosb_get_discount > 0 ) ) {
								$woosb_item_price *= (float) ( 100 - $woosb_get_discount ) / 100;
							}

							// add to cart
							$woosb_product_qty = $woosb_item_qty * $quantity;
							$woosb_item_data   = array(
								'woosb_pos'                 => $woosb_i,
								'woosb_qty'                 => $woosb_item_qty,
								'woosb_price'               => $woosb_item_price,
								'woosb_parent_id'           => $product_id,
								'woosb_parent_key'          => $cart_item_key,
								'woosb_fixed_price'         => $woosb_fixed_price,
								'woosb_get_discount'        => $woosb_get_discount,
								'woosb_get_discount_amount' => $woosb_get_discount_amount
							);

							$woosb_cart_id  = WC()->cart->generate_cart_id( $woosb_item_id, $woosb_item_variation_id, $woosb_item_variation, $woosb_item_data );
							$woosb_item_key = WC()->cart->find_product_in_cart( $woosb_cart_id );

							if ( empty( $woosb_item_key ) ) {
								$woosb_item_key = WC()->cart->add_to_cart( $woosb_item_id, $woosb_product_qty, $woosb_item_variation_id, $woosb_item_variation, $woosb_item_data );
							}

							if ( empty( $woosb_item_key ) ) {
								// can't add the bundled product
								if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] ) ) {
									$woosb_keys = WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'];
									foreach ( $woosb_keys as $woosb_key ) {
										// remove all bundled products
										WC()->cart->remove_cart_item( $woosb_key );
									}
									// remove the bundle
									WC()->cart->remove_cart_item( $cart_item_key );
								}
							} elseif ( ! isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'] ) || ! in_array( $woosb_item_key, WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'], true ) ) {
								// add keys
								WC()->cart->cart_contents[ $cart_item_key ]['woosb_keys'][] = $woosb_item_key;
							}
						} // end foreach
					}
				}

				function woosb_get_cart_item_from_session( $cart_item, $item_session_values ) {
					if ( isset( $item_session_values['woosb_ids'] ) && ! empty( $item_session_values['woosb_ids'] ) ) {
						$cart_item['woosb_ids'] = $item_session_values['woosb_ids'];
					}
					if ( isset( $item_session_values['woosb_parent_id'] ) ) {
						$cart_item['woosb_parent_id']  = $item_session_values['woosb_parent_id'];
						$cart_item['woosb_parent_key'] = $item_session_values['woosb_parent_key'];
						$cart_item['woosb_qty']        = $item_session_values['woosb_qty'];
					}

					return $cart_item;
				}

				function woosb_before_calculate_totals( $cart_object ) {
					if ( ! defined( 'DOING_AJAX' ) && is_admin() ) {
						// This is necessary for WC 3.0+
						return;
					}

					foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
						// bundled product price
						if ( ! empty( $cart_item['woosb_parent_id'] ) ) {
							if ( isset( $cart_item['woosb_fixed_price'] ) && $cart_item['woosb_fixed_price'] ) {
								$cart_item['data']->set_price( 0 );
							} elseif ( isset( $cart_item['woosb_price'], $cart_item['woosb_get_discount'], $cart_item['woosb_get_discount_amount'] ) && ( $cart_item['woosb_get_discount_amount'] <= 0 ) && ( $cart_item['woosb_get_discount'] > 0 ) ) {
								$cart_item['data']->set_price( $cart_item['woosb_price'] );
							}
						}

						// bundle price
						if ( ! empty( $cart_item['woosb_ids'] ) && isset( $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
							// set price zero, calculate after
							$cart_item['data']->set_price( 0 );
						}
					}
				}

				function woosb_after_calculate_totals( $cart_object ) {
					$cart_items = $cart_object->get_cart();
					foreach ( $cart_items as $cart_item_key => $cart_item ) {
						if ( ! empty( $cart_item['woosb_ids'] ) && ! empty( $cart_item['woosb_keys'] ) && isset( $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
							// only calculate for auto price
							$bundle_price = 0;

							foreach ( $cart_item['woosb_keys'] as $woosb_key ) {
								if ( isset( $cart_items[ $woosb_key ] ) ) {
									if ( $cart_object->tax_display_cart === 'incl' ) {
										$bundle_item_price = $cart_items[ $woosb_key ]['line_subtotal'] + $cart_items[ $woosb_key ]['line_subtotal_tax'];
									} else {
										$bundle_item_price = $cart_items[ $woosb_key ]['line_subtotal'];
									}

									$bundle_price += $bundle_item_price;
								}
							}

							if ( ! empty( $cart_item['woosb_get_discount_amount'] ) ) {
								$bundle_price -= $cart_item['woosb_get_discount_amount'];
							}

							WC()->cart->cart_contents[ $cart_item_key ]['woosb_price'] = $bundle_price / $cart_item['quantity'];
						}
					}
				}

				function woosb_cart_get_subtotal( $subtotal ) {
					foreach ( WC()->cart->get_cart() as $cart_item ) {
						if ( ! empty( $cart_item['woosb_ids'] ) && ! empty( $cart_item['woosb_get_discount_amount'] ) ) {
							$subtotal -= $cart_item['woosb_get_discount_amount'];
						}
					}

					return $subtotal;
				}

				function woosb_calculated_total( $total, $cart_object ) {
					$cart_items = $cart_object->get_cart();

					foreach ( $cart_items as $cart_item_key => $cart_item ) {
						if ( ! empty( $cart_item['woosb_ids'] ) && ! empty( $cart_item['woosb_get_discount_amount'] ) ) {
							$total -= $cart_item['woosb_get_discount_amount'];
						}
					}

					return $total;
				}

				function woosb_cart_item_price( $price, $cart_item ) {
					if ( isset( $cart_item['woosb_ids'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
						return wc_price( $cart_item['woosb_price'] );
					}

					if ( isset( $cart_item['woosb_parent_id'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && $cart_item['woosb_fixed_price'] ) {
						return wc_price( $cart_item['woosb_price'] );
					}

					return $price;
				}

				function woosb_cart_item_subtotal( $subtotal, $cart_item = null ) {
					if ( isset( $cart_item['woosb_ids'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && ! $cart_item['woosb_fixed_price'] ) {
						return wc_price( $cart_item['woosb_price'] * $cart_item['quantity'] );
					}

					if ( isset( $cart_item['woosb_parent_id'], $cart_item['woosb_price'], $cart_item['woosb_fixed_price'] ) && $cart_item['woosb_fixed_price'] ) {
						return wc_price( $cart_item['woosb_price'] * $cart_item['quantity'] );
					}

					return $subtotal;
				}

				function woosb_item_visible( $visible, $item ) {
					if ( isset( $item['woosb_parent_id'] ) ) {
						return false;
					}

					return $visible;
				}

				function woosb_item_class( $class, $item ) {
					if ( isset( $item['woosb_parent_id'] ) ) {
						$class .= ' woosb-cart-item woosb-cart-child woosb-item-child';
					} elseif ( isset( $item['woosb_ids'] ) ) {
						$class .= ' woosb-cart-item woosb-cart-parent woosb-item-parent';
					}

					return $class;
				}

				function woosb_get_item_data( $item_data, $cart_item ) {
					if ( empty( $cart_item['woosb_ids'] ) ) {
						return $item_data;
					}

					$woosb_items     = explode( ',', $cart_item['woosb_ids'] );
					$woosb_items_str = '';
					if ( is_array( $woosb_items ) && count( $woosb_items ) > 0 ) {
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_item_arr  = explode( '/', $woosb_item );
							$woosb_item_id   = absint( isset( $woosb_item_arr[0] ) ? $woosb_item_arr[0] : 0 );
							$woosb_item_qty  = absint( isset( $woosb_item_arr[1] ) ? $woosb_item_arr[1] : 1 );
							$woosb_items_str .= $woosb_item_qty . ' × ' . get_the_title( $woosb_item_id ) . '; ';
						}
					}
					$woosb_items_str = trim( $woosb_items_str, '; ' );
					$item_data[]     = array(
						'key'     => esc_html__( 'Bundled products', 'woo-product-bundle' ),
						'value'   => $woosb_items_str,
						'display' => '',
					);

					return $item_data;
				}

				function woosb_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
					if ( empty( $values['woosb_ids'] ) ) {
						return;
					}
					$woosb_items     = explode( ',', $values['woosb_ids'] );
					$woosb_items_str = '';
					if ( is_array( $woosb_items ) && count( $woosb_items ) > 0 ) {
						foreach ( $woosb_items as $woosb_item ) {
							$woosb_item_arr  = explode( '/', $woosb_item );
							$woosb_item_id   = absint( isset( $woosb_item_arr[0] ) ? $woosb_item_arr[0] : 0 );
							$woosb_item_qty  = absint( isset( $woosb_item_arr[1] ) ? $woosb_item_arr[1] : 1 );
							$woosb_items_str .= $woosb_item_qty . ' × ' . get_the_title( $woosb_item_id ) . '; ';
						}
					}
					$woosb_items_str = trim( $woosb_items_str, '; ' );
					$item->add_meta_data( esc_html__( 'Bundled products', 'woo-product-bundle' ), $woosb_items_str );
				}

				function woosb_add_order_item_meta( $item, $cart_item_key, $values ) {
					if ( isset( $values['woosb_parent_id'] ) ) {
						// use _ to hide the data
						$item->update_meta_data( '_woosb_parent_id', $values['woosb_parent_id'] );
					}
					if ( isset( $values['woosb_ids'] ) ) {
						// use _ to hide the data
						$item->update_meta_data( '_woosb_ids', $values['woosb_ids'] );
					}
					if ( isset( $values['woosb_price'] ) ) {
						// use _ to hide the data
						$item->update_meta_data( '_woosb_price', $values['woosb_price'] );
					}
				}

				function woosb_hidden_order_item_meta( $hidden ) {
					return array_merge( $hidden, array(
						'_woosb_parent_id',
						'_woosb_ids',
						'_woosb_price',
						'woosb_parent_id',
						'woosb_ids',
						'woosb_price'
					) );
				}

				function woosb_before_order_item_meta( $item_id ) {
					if ( $woosb_parent_id = wc_get_order_item_meta( $item_id, '_woosb_parent_id', true ) ) {
						echo sprintf( esc_html__( '(bundled in %s)', 'woo-product-bundle' ), get_the_title( $woosb_parent_id ) );
					}
				}

				function woosb_order_formatted_line_subtotal( $subtotal, $item ) {
					if ( isset( $item['_woosb_parent_id'] ) ) {
						return '';
					} elseif ( isset( $item['_woosb_ids'], $item['_woosb_price'] ) ) {
						return wc_price( $item['_woosb_price'] * $item['quantity'] );
					}

					return $subtotal;
				}

				function woosb_cart_item_remove_link( $link, $cart_item_key ) {
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_parent_key'] ) ) {
						$woosb_parent_key = WC()->cart->cart_contents[ $cart_item_key ]['woosb_parent_key'];
						if ( isset( WC()->cart->cart_contents[ $woosb_parent_key ] ) ) {
							return '';
						}
					}

					return $link;
				}

				function woosb_cart_item_quantity( $quantity, $cart_item_key, $cart_item ) {
					// add qty as text - not input
					if ( isset( $cart_item['woosb_parent_id'] ) ) {
						return $cart_item['quantity'];
					}

					return $quantity;
				}

				function woosb_get_search_results() {
					$keyword   = sanitize_text_field( $_POST['keyword'] );
					$added_ids = explode( ',', $this->woosb_clean_ids( $_POST['ids'] ) );

					if ( is_array( $added_ids ) && count( $added_ids ) > 2 ) {
						echo '<ul><span>Please use the Premium Version to add more than 3 products to the bundle & get the premium support. Click <a href="https://wpclever.net/downloads/woocommerce-product-bundle" target="_blank">here</a> to buy, just $29!</span></ul>';
						die();
					}

					if ( ( get_option( '_woosb_search_id', 'no' ) === 'yes' ) && is_numeric( $keyword ) ) {
						// search by id
						$woosb_query_args = array(
							'p'         => absint( $keyword ),
							'post_type' => 'product'
						);
					} else {
						$woosb_query_args = array(
							'is_woosb'       => true,
							'post_type'      => 'product',
							'post_status'    => array( 'publish', 'private' ),
							's'              => $keyword,
							'posts_per_page' => get_option( '_woosb_search_limit', '5' ),
							'tax_query'      => array(
								array(
									'taxonomy' => 'product_type',
									'field'    => 'slug',
									'terms'    => array( 'woosb', 'composite', 'bundle' ),
									'operator' => 'NOT IN',
								)
							)
						);

						if ( get_option( '_woosb_search_same', 'no' ) !== 'yes' ) {
							$exclude_ids = array();
							if ( is_array( $added_ids ) && count( $added_ids ) > 0 ) {
								foreach ( $added_ids as $added_id ) {
									$added_id_new  = explode( '/', $added_id );
									$exclude_ids[] = absint( isset( $added_id_new[0] ) ? $added_id_new[0] : 0 );
								}
							}
							$woosb_query_args['post__not_in'] = $exclude_ids;
						}
					}

					$woosb_query = new WP_Query( $woosb_query_args );
					if ( $woosb_query->have_posts() ) {
						echo '<ul>';
						while ( $woosb_query->have_posts() ) {
							$woosb_query->the_post();
							$woosb_product = wc_get_product( get_the_ID() );
							if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) ) {
								continue;
							}
							$this->woosb_product_data_li( $woosb_product, 1, true );
							if ( $woosb_product->is_type( 'variable' ) ) {
								// show all childs
								$woosb_childs = $woosb_product->get_children();
								if ( is_array( $woosb_childs ) && count( $woosb_childs ) > 0 ) {
									foreach ( $woosb_childs as $woosb_child ) {
										$woosb_product_child = wc_get_product( $woosb_child );
										$this->woosb_product_data_li( $woosb_product_child, 1, true );
									}
								}
							}
						}
						echo '</ul>';
						wp_reset_postdata();
					} else {
						echo '<ul><span>' . sprintf( esc_html__( 'No results found for "%s"', 'woo-product-bundle' ), $keyword ) . '</span></ul>';
					}
					die();
				}

				function woosb_meta_boxes() {
					add_meta_box( 'woosb_meta_box', esc_html__( 'WPC Product Bundles', 'woo-product-bundle' ), array(
						&$this,
						'woosb_meta_boxes_content'
					), 'product', 'side', 'high' );
				}

				function woosb_meta_boxes_content() {
					$post_id = isset( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;
					$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : $post_id;
					if ( $post_id > 0 ) {
						$woosb_product = wc_get_product( $post_id );
						if ( $woosb_product && ! $woosb_product->is_type( 'woosb' ) ) {
							?>
                            <p><?php esc_html_e( 'Update price for all bundles contains this product. The progress time based on the number of your bundles.', 'woo-product-bundle' ); ?></p>
                            <input id="woosb_meta_box_update_price" type="button" class="button"
                                   data-id="<?php echo esc_attr( $post_id ); ?>"
                                   value="<?php esc_html_e( 'Update Price', 'woo-product-bundle' ); ?>"/>
                            <ul id="woosb_meta_box_update_price_result"></ul>
							<?php
						} else { ?>
                            <p><?php esc_html_e( 'Invalid product to use this tool!', 'woo-product-bundle' ); ?></p>
						<?php }
					} else { ?>
                        <p><?php esc_html_e( 'This box content just appears after you publish the product.', 'woo-product-bundle' ); ?></p>
					<?php }
				}

				function woosb_search_sku( $query ) {
					if ( $query->is_search && isset( $query->query['is_woosb'] ) ) {
						global $wpdb;
						$sku = $query->query['s'];
						$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value = %s;", $sku ) );
						if ( ! $ids ) {
							return;
						}
						unset( $query->query['s'], $query->query_vars['s'] );
						$query->query['post__in'] = array();
						foreach ( $ids as $id ) {
							$post = get_post( $id );
							if ( $post->post_type === 'product_variation' ) {
								$query->query['post__in'][]      = $post->post_parent;
								$query->query_vars['post__in'][] = $post->post_parent;
							} else {
								$query->query_vars['post__in'][] = $post->ID;
							}
						}
					}
				}

				function woosb_search_exact( $query ) {
					if ( $query->is_search && isset( $query->query['is_woosb'] ) ) {
						$query->set( 'exact', true );
					}
				}

				function woosb_search_sentence( $query ) {
					if ( $query->is_search && isset( $query->query['is_woosb'] ) ) {
						$query->set( 'sentence', true );
					}
				}

				function woosb_product_type_selector( $types ) {
					$types['woosb'] = esc_html__( 'Smart bundle', 'woo-product-bundle' );

					return $types;
				}

				function woosb_product_data_tabs( $tabs ) {
					$tabs['woosb'] = array(
						'label'  => esc_html__( 'Bundled Products', 'woo-product-bundle' ),
						'target' => 'woosb_settings',
						'class'  => array( 'show_if_woosb' ),
					);

					return $tabs;
				}

				function woosb_single_product_summary() {
					$this->woosb_show_bundles();
				}

				function woosb_product_tabs( $tabs ) {
					global $product;

					if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'tab' ) && $product->is_type( 'woosb' ) ) {
						$tabs['woosb'] = array(
							'title'    => esc_html__( 'Bundled products', 'woo-product-bundle' ),
							'priority' => 50,
							'callback' => array( $this, 'woosb_product_tab_bundled' )
						);
					}

					if ( ( get_option( '_woosb_bundles_position', 'no' ) === 'tab' ) && ! $product->is_type( 'woosb' ) ) {
						$tabs['woosb'] = array(
							'title'    => esc_html__( 'Bundles', 'woo-product-bundle' ),
							'priority' => 50,
							'callback' => array( $this, 'woosb_product_tab_bundles' )
						);
					}

					return $tabs;
				}

				function woosb_product_tab_bundled() {
					$this->woosb_show_items();
				}

				function woosb_product_tab_bundles() {
					$this->woosb_show_bundles();
				}

				function woosb_product_filters( $filters ) {
					$filters = str_replace( 'Woosb', esc_html__( 'Smart bundle', 'woo-product-bundle' ), $filters );

					return $filters;
				}

				function woosb_product_data_panels() {
					global $post;
					$post_id = $post->ID;
					?>
                    <div id='woosb_settings' class='panel woocommerce_options_panel woosb_table'>
                        <table>
                            <tr>
                                <th><?php esc_html_e( 'Search', 'woo-product-bundle' ); ?> (<a
                                            href="<?php echo admin_url( 'admin.php?page=wpclever-woosb&tab=settings#search' ); ?>"
                                            target="_blank"><?php esc_html_e( 'settings', 'woo-product-bundle' ); ?></a>)
                                </th>
                                <td>
                                    <div class="w100">
								<span class="loading"
                                      id="woosb_loading"><?php esc_html_e( 'searching...', 'woo-product-bundle' ); ?></span>
                                        <input type="search" id="woosb_keyword"
                                               placeholder="<?php esc_html_e( 'Type any keyword to search', 'woo-product-bundle' ); ?>"/>
                                        <div id="woosb_results" class="woosb_results"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'Selected', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <input type="hidden" id="woosb_ids" class="woosb_ids" name="woosb_ids"
                                               value="<?php echo get_post_meta( $post_id, 'woosb_ids', true ); ?>"
                                               readonly/>
                                        <div id="woosb_selected" class="woosb_selected">
                                            <ul>
												<?php
												if ( get_post_meta( $post_id, 'woosb_ids', true ) ) {
													$woosb_items = explode( ',', get_post_meta( $post_id, 'woosb_ids', true ) );
													if ( is_array( $woosb_items ) && count( $woosb_items ) > 0 ) {
														foreach ( $woosb_items as $woosb_item ) {
															$woosb_item_arr = explode( '/', $woosb_item );
															$woosb_item_id  = absint( isset( $woosb_item_arr[0] ) ? $woosb_item_arr[0] : 0 );
															$woosb_item_qty = absint( isset( $woosb_item_arr[1] ) ? $woosb_item_arr[1] : 1 );
															$woosb_product  = wc_get_product( $woosb_item_id );
															if ( ! $woosb_product || $woosb_product->is_type( 'woosb' ) ) {
																continue;
															}
															$this->woosb_product_data_li( $woosb_product, $woosb_item_qty );
														}
													}
												}
												?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php echo esc_html__( 'Regular price', 'woo-product-bundle' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></th>
                                <td>
                                    <span id="woosb_regular_price"></span>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'Auto price', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <input id="woosb_disable_auto_price" name="woosb_disable_auto_price"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_disable_auto_price', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woosb_disable_auto_price"><?php esc_html_e( 'Disable auto calculate price?', 'woo-product-bundle' ); ?></label> <?php echo sprintf( esc_html__( 'If yes, %s click here to set price %s by manually.', 'woo-product-bundle' ), '<a id="woosb_set_regular_price">', '</a>' ); ?>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space woosb_tr_show_if_auto_price">
                                <th><?php esc_html_e( 'Discount', 'woo-product-bundle' ); ?></th>
                                <td style="vertical-align: middle; line-height: 30px;">
									<?php $woosb_discount = (float) get_post_meta( $post_id, 'woosb_discount', true ) ?: 0; ?>
                                    <input id="woosb_discount" name="woosb_discount" type="number"
                                           min="0" step="0.0001"
                                           max="99.9999"
                                           value="<?php echo esc_attr( $woosb_discount ); ?>"
                                           style="width: 80px"/> <?php esc_html_e( '% or amount', 'woo-product-bundle' ); ?>
                                    <input id="woosb_discount_amount"
                                           name="woosb_discount_amount" type="number"
                                           min="0" step="0.0001"
                                           value="<?php echo get_post_meta( $post_id, 'woosb_discount_amount', true ); ?>"
                                           style="width: 80px"/> <?php echo get_woocommerce_currency_symbol(); ?>
                                    . <?php esc_html_e( 'If you fill both, the amount will be used.', 'woo-product-bundle' ); ?>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'Optional products', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <input id="woosb_optional_products" name="woosb_optional_products"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_optional_products', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woosb_optional_products"><?php esc_html_e( 'Buyer can change the quantity of bundled products?', 'woo-product-bundle' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space woosb_tr_show_if_optional_products">
                                <th><?php esc_html_e( 'Limit of each item', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <input id="woosb_limit_each_min_default" name="woosb_limit_each_min_default"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_limit_each_min_default', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woosb_limit_each_min_default"><?php esc_html_e( 'Use default quantity as min?', 'woo-product-bundle' ); ?></label>
                                    <u>or</u> Min <input name="woosb_limit_each_min" type="number"
                                                         min="0"
                                                         value="<?php echo( get_post_meta( $post_id, 'woosb_limit_each_min', true ) ?: '' ); ?>"
                                                         style="width: 60px; float: none"/> Max <input
                                            name="woosb_limit_each_max"
                                            type="number" min="1"
                                            value="<?php echo( get_post_meta( $post_id, 'woosb_limit_each_max', true ) ?: '' ); ?>"
                                            style="width: 60px; float: none"/>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space woosb_tr_show_if_optional_products">
                                <th><?php esc_html_e( 'Limit of whole items', 'woo-product-bundle' ); ?></th>
                                <td>
                                    Min <input name="woosb_limit_whole_min" type="number"
                                               min="1"
                                               value="<?php echo( get_post_meta( $post_id, 'woosb_limit_whole_min', true ) ?: '' ); ?>"
                                               style="width: 60px; float: none"/> Max <input
                                            name="woosb_limit_whole_max"
                                            type="number" min="1"
                                            value="<?php echo( get_post_meta( $post_id, 'woosb_limit_whole_max', true ) ?: '' ); ?>"
                                            style="width: 60px; float: none"/>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'Shipping fee', 'woo-product-bundle' ); ?></th>
                                <td style="font-style: italic">
                                    <select id="woosb_shipping_fee" name="woosb_shipping_fee">
                                        <option value="whole" <?php echo( get_post_meta( $post_id, 'woosb_shipping_fee', true ) === 'whole' ? 'selected' : '' ); ?>><?php esc_html_e( 'Apply to the whole bundle', 'woo-product-bundle' ); ?></option>
                                        <option value="each" <?php echo( get_post_meta( $post_id, 'woosb_shipping_fee', true ) === 'each' ? 'selected' : '' ); ?>><?php esc_html_e( 'Apply to each bundled product', 'woo-product-bundle' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'Manage stock', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <input id="woosb_manage_stock" name="woosb_manage_stock"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woosb_manage_stock', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woosb_manage_stock"><?php esc_html_e( 'Enable stock management at bundle level?', 'woo-product-bundle' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'Custom display price', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <input type="text" name="woosb_custom_price"
                                           value="<?php echo stripslashes( get_post_meta( $post_id, 'woosb_custom_price', true ) ); ?>"/>
                                    E.g: <code>From $10 to $100</code>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'Before text', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <div class="w100">
								<textarea name="woosb_before_text"
                                          placeholder="<?php esc_html_e( 'The text before bundled products', 'woo-product-bundle' ); ?>"><?php echo stripslashes( get_post_meta( $post_id, 'woosb_before_text', true ) ); ?></textarea>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woosb_tr_space">
                                <th><?php esc_html_e( 'After text', 'woo-product-bundle' ); ?></th>
                                <td>
                                    <div class="w100">
								<textarea name="woosb_after_text"
                                          placeholder="<?php esc_html_e( 'The text after bundled products', 'woo-product-bundle' ); ?>"><?php echo stripslashes( get_post_meta( $post_id, 'woosb_after_text', true ) ); ?></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
					<?php
				}

				function woosb_product_data_li( $product, $qty = 1, $search = false ) {
					$product_id = $product->get_id();

					if ( $product->is_sold_individually() ) {
						$qty_input = '<input type="number" value="' . $qty . '" min="0" max="1"/>';
					} else {
						$qty_input = '<input type="number" value="' . $qty . '" min="0"/>';
					}

					if ( $product->is_type( 'variable' ) ) {
						$price     = wc_get_price_to_display( $product, array( 'price' => $product->get_variation_price( 'min' ) ) );
						$price_max = wc_get_price_to_display( $product, array( 'price' => $product->get_variation_price( 'max' ) ) );
					} else {
						$price = $price_max = wc_get_price_to_display( $product );
					}

					if ( $search ) {
						$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Add', 'woo-product-bundle' ) . '">+</span>';
					} else {
						$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Remove', 'woo-product-bundle' ) . '">×</span>';
					}

					echo '<li ' . ( ! $product->is_in_stock() ? 'class="out-of-stock"' : '' ) . ' data-id="' . $product_id . '" data-price="' . $price . '" data-price-max="' . $price_max . '"><span class="move"></span><span class="qty hint--right" aria-label="' . esc_html__( 'Default quantity', 'woo-product-bundle' ) . '">' . $qty_input . '</span> <span class="name">' . $product->get_name() . '</span> <span class="info">' . $product->get_price_html() . '</span> ' . ( $product->is_sold_individually() ? '<span class="info">sold individually</span> ' : '' ) . '<span class="type"><a href="' . get_edit_post_link( $product_id ) . '" target="_blank">' . $product->get_type() . ' #' . $product_id . '</a></span> ' . $remove_btn . '</li>';
				}

				function woosb_delete_option_fields( $post_id ) {
					if ( isset( $_POST['product-type'] ) && ( $_POST['product-type'] !== 'woosb' ) ) {
						delete_post_meta( $post_id, 'woosb_ids' );
					}
				}

				function woosb_save_option_fields( $post_id ) {
					if ( isset( $_POST['woosb_ids'] ) && ! empty( $_POST['woosb_ids'] ) ) {
						update_post_meta( $post_id, 'woosb_ids', $this->woosb_clean_ids( $_POST['woosb_ids'] ) );
					}
					if ( isset( $_POST['woosb_disable_auto_price'] ) ) {
						update_post_meta( $post_id, 'woosb_disable_auto_price', 'on' );
					} else {
						update_post_meta( $post_id, 'woosb_disable_auto_price', 'off' );
					}
					if ( isset( $_POST['woosb_discount'] ) ) {
						update_post_meta( $post_id, 'woosb_discount', sanitize_text_field( $_POST['woosb_discount'] ) );
					} else {
						update_post_meta( $post_id, 'woosb_discount', 0 );
					}
					if ( isset( $_POST['woosb_discount_amount'] ) ) {
						update_post_meta( $post_id, 'woosb_discount_amount', sanitize_text_field( $_POST['woosb_discount_amount'] ) );
					} else {
						update_post_meta( $post_id, 'woosb_discount_amount', 0 );
					}
					if ( isset( $_POST['woosb_shipping_fee'] ) ) {
						update_post_meta( $post_id, 'woosb_shipping_fee', sanitize_text_field( $_POST['woosb_shipping_fee'] ) );
					}
					if ( isset( $_POST['woosb_optional_products'] ) ) {
						update_post_meta( $post_id, 'woosb_optional_products', 'on' );
					} else {
						update_post_meta( $post_id, 'woosb_optional_products', 'off' );
					}
					if ( isset( $_POST['woosb_manage_stock'] ) ) {
						update_post_meta( $post_id, 'woosb_manage_stock', 'on' );
					} else {
						update_post_meta( $post_id, 'woosb_manage_stock', 'off' );
					}
					if ( isset( $_POST['woosb_limit_each_min'] ) ) {
						update_post_meta( $post_id, 'woosb_limit_each_min', sanitize_text_field( $_POST['woosb_limit_each_min'] ) );
					}
					if ( isset( $_POST['woosb_limit_each_max'] ) ) {
						update_post_meta( $post_id, 'woosb_limit_each_max', sanitize_text_field( $_POST['woosb_limit_each_max'] ) );
					}
					if ( isset( $_POST['woosb_limit_each_min_default'] ) ) {
						update_post_meta( $post_id, 'woosb_limit_each_min_default', 'on' );
					} else {
						update_post_meta( $post_id, 'woosb_limit_each_min_default', 'off' );
					}
					if ( isset( $_POST['woosb_limit_whole_min'] ) ) {
						update_post_meta( $post_id, 'woosb_limit_whole_min', sanitize_text_field( $_POST['woosb_limit_whole_min'] ) );
					}
					if ( isset( $_POST['woosb_limit_whole_max'] ) ) {
						update_post_meta( $post_id, 'woosb_limit_whole_max', sanitize_text_field( $_POST['woosb_limit_whole_max'] ) );
					}
					if ( isset( $_POST['woosb_before_text'] ) && ( $_POST['woosb_before_text'] !== '' ) ) {
						update_post_meta( $post_id, 'woosb_before_text', addslashes( $_POST['woosb_before_text'] ) );
					} else {
						delete_post_meta( $post_id, 'woosb_before_text' );
					}
					if ( isset( $_POST['woosb_after_text'] ) && ( $_POST['woosb_after_text'] !== '' ) ) {
						update_post_meta( $post_id, 'woosb_after_text', addslashes( $_POST['woosb_after_text'] ) );
					} else {
						delete_post_meta( $post_id, 'woosb_after_text' );
					}
				}

				function woosb_add_to_cart_form() {
					global $product;
					if ( $product->has_variables() ) {
						wp_enqueue_script( 'wc-add-to-cart-variation' );
					}

					if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'above' ) && apply_filters( 'woosb_show_items', true, $product->get_id() ) ) {
						$this->woosb_show_items();
					}

					wc_get_template( 'single-product/add-to-cart/simple.php' );

					if ( ( get_option( '_woosb_bundled_position', 'above' ) === 'below' ) && apply_filters( 'woosb_show_items', true, $product->get_id() ) ) {
						$this->woosb_show_items();
					}
				}

				function woosb_add_to_cart_button() {
					global $product;
					if ( $product->is_type( 'woosb' ) ) {
						echo '<input name="woosb_ids" class="woosb_ids woosb-ids" type="hidden" value="' . get_post_meta( $product->get_id(), 'woosb_ids', true ) . '"/>';
					}
				}

				function woosb_loop_add_to_cart_link( $link, $product ) {
					if ( $product->is_type( 'woosb' ) && ( $product->has_variables() || $product->is_optional() ) ) {
						$link = str_replace( 'ajax_add_to_cart', '', $link );
					}

					return $link;
				}

				function woosb_cart_shipping_packages( $packages ) {
					if ( ! empty( $packages ) ) {
						foreach ( $packages as $package_key => $package ) {
							if ( ! empty( $package['contents'] ) ) {
								foreach ( $package['contents'] as $cart_item_key => $cart_item ) {
									if ( isset( $cart_item['woosb_parent_id'] ) && ( $cart_item['woosb_parent_id'] !== '' ) && ( get_post_meta( $cart_item['woosb_parent_id'], 'woosb_shipping_fee', true ) !== 'each' ) ) {
										unset( $packages[ $package_key ]['contents'][ $cart_item_key ] );
									}
									if ( isset( $cart_item['woosb_ids'] ) && ( $cart_item['woosb_ids'] !== '' ) && ( get_post_meta( $cart_item['data']->get_id(), 'woosb_shipping_fee', true ) === 'each' ) ) {
										unset( $packages[ $package_key ]['contents'][ $cart_item_key ] );
									}
								}
							}
						}
					}

					return $packages;
				}

				function woosb_get_price_html( $price, $product ) {
					$product_id = $product->get_id();
					if ( $product->is_type( 'woosb' ) && ( $woosb_items = $product->get_items() ) ) {
						$custom_price = get_post_meta( $product_id, 'woosb_custom_price', true );
						if ( ! empty( $custom_price ) ) {
							return $custom_price;
						}

						if ( ! $product->is_fixed_price() ) {
							if ( $product->is_optional() ) {
								// min price
								$prices = array();
								foreach ( $woosb_items as $woosb_item ) {
									$woosb_product = wc_get_product( $woosb_item['id'] );
									if ( $woosb_product ) {
										if ( $woosb_product->is_type( 'variable' ) ) {
											$prices[] = wc_get_price_to_display( $woosb_product, array(
												'price' => $woosb_product->get_variation_price( 'min' )
											) );
										} else {
											$prices[] = wc_get_price_to_display( $woosb_product );
										}
									}
								}
								if ( count( $prices ) > 0 ) {
									$min_price = min( $prices );
								} else {
									$min_price = 0;
								}

								// min whole
								$min_qty_whole = absint( get_post_meta( $product_id, 'woosb_limit_whole_min', true ) ?: 1 );
								if ( $min_qty_whole > 1 ) {
									$min_price *= $min_qty_whole;
								}

								// min each
								$min_qty_each = absint( get_post_meta( $product_id, 'woosb_limit_each_min', true ) ?: 0 );
								if ( $min_qty_each > 0 ) {
									$min_price = 0;
									foreach ( $prices as $pr ) {
										$min_price += absint( $pr );
									}
									$min_price *= $min_qty_each;
								}

								if ( ( $discount = $product->get_discount() ) > 0 ) {
									$min_price *= (float) ( 100 - $discount ) / 100;
								}

								switch ( get_option( '_woosb_price_format', 'from_min' ) ) {
									case 'min_only':
										return wc_price( $min_price );
										break;
									case 'from_min':
										return esc_html__( 'From', 'woo-product-bundle' ) . ' ' . wc_price( $min_price );
										break;
								}
							} elseif ( $product->has_variables() ) {
								$min_price = $max_price = 0;
								foreach ( $woosb_items as $woosb_item ) {
									$woosb_product = wc_get_product( $woosb_item['id'] );
									if ( $woosb_product ) {
										if ( $woosb_product->is_type( 'variable' ) ) {
											$min_price += wc_get_price_to_display( $woosb_product, array(
												'qty'   => $woosb_item['qty'],
												'price' => $woosb_product->get_variation_price( 'min' )
											) );
											$max_price += wc_get_price_to_display( $woosb_product, array(
												'qty'   => $woosb_item['qty'],
												'price' => $woosb_product->get_variation_price( 'max' )
											) );
										} else {
											$min_price += wc_get_price_to_display( $woosb_product, array( 'qty' => $woosb_item['qty'] ) );
											$max_price += wc_get_price_to_display( $woosb_product, array( 'qty' => $woosb_item['qty'] ) );
										}
									}
								}
								if ( ( $discount = $product->get_discount() ) > 0 ) {
									$min_price *= (float) ( 100 - $discount ) / 100;
									$max_price *= (float) ( 100 - $discount ) / 100;
								}

								switch ( get_option( '_woosb_price_format', 'from_min' ) ) {
									case 'min_only':
										return wc_price( $min_price );
										break;
									case 'min_max':
										return wc_price( $min_price ) . ' - ' . wc_price( $max_price );
										break;
									case 'from_min':
										return esc_html__( 'From', 'woo-product-bundle' ) . ' ' . wc_price( $min_price );
										break;
								}
							}
						}
					}

					return $price;
				}

				function woosb_order_again_cart_item_data( $item_data, $item ) {
					if ( isset( $item['woosb_ids'] ) ) {
						$item_data['woosb_order_again'] = 'yes';
					}

					return $item_data;
				}

				function woosb_cart_loaded_from_session() {
					foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
						if ( isset( $cart_item['woosb_order_again'] ) ) {
							WC()->cart->remove_cart_item( $cart_item_key );
							wc_add_notice( sprintf( esc_html__( 'The bundle "%s" could not be added to your cart from order again button. Please buy it directly.', 'woo-product-bundle' ), $cart_item['data']->get_name() ), 'error' );
						}
					}
				}

				function woosb_show_items( $product = null ) {
					if ( ! $product ) {
						global $product;
					}
					$product_id = $product->get_id();
					if ( $woosb_items = $product->get_items() ) {
						echo '<div class="woosb-wrap">';
						if ( $woosb_before_text = apply_filters( 'woosb_before_text', get_post_meta( $product_id, 'woosb_before_text', true ), $product_id ) ) {
							echo '<div class="woosb-before-text woosb-text">' . do_shortcode( stripslashes( $woosb_before_text ) ) . '</div>';
						}
						do_action( 'woosb_before_table', $product );
						?>
                        <div class="woosb-products"
                             data-discount="<?php echo $product->get_discount(); ?>"
                             data-discount-amount="<?php echo $product->get_discount_amount(); ?>"
                             data-fixed-price="<?php echo esc_attr( $product->is_fixed_price() ? 'yes' : 'no' ); ?>"
                             data-variables="<?php echo esc_attr( $product->has_variables() ? 'yes' : 'no' ); ?>"
                             data-optional="<?php echo esc_attr( $product->is_optional() ? 'yes' : 'no' ); ?>"
                             data-min="<?php echo esc_attr( get_post_meta( $product_id, 'woosb_limit_whole_min', true ) ?: 1 ); ?>"
                             data-max="<?php echo esc_attr( get_post_meta( $product_id, 'woosb_limit_whole_max', true ) ?: '' ); ?>">
							<?php foreach ( $woosb_items as $woosb_item ) {
								$woosb_product = wc_get_product( $woosb_item['id'] );
								if ( ! $woosb_product ) {
									continue;
								}

								$woosb_product_qty = $woosb_item['qty'];

								if ( get_post_meta( $product_id, 'woosb_limit_each_min_default', true ) === 'on' ) {
									$woosb_product_qty_min = $woosb_product_qty;
								} else {
									$woosb_product_qty_min = absint( get_post_meta( $product_id, 'woosb_limit_each_min', true ) ?: 0 );
								}

								$woosb_product_qty_max = absint( get_post_meta( $product_id, 'woosb_limit_each_max', true ) ?: 1000 );
								if ( $woosb_product_qty < $woosb_product_qty_min ) {
									$woosb_product_qty = $woosb_product_qty_min;
								}
								if ( ( $woosb_product_qty_max > $woosb_product_qty_min ) && ( $woosb_product_qty > $woosb_product_qty_max ) ) {
									$woosb_product_qty = $woosb_product_qty_max;
								}
								if ( ! $woosb_product->is_in_stock() || ! $woosb_product->has_enough_stock( $woosb_product_qty ) ) {
									$woosb_product_qty = 0;
								}
								?>
                                <div class="woosb-product"
                                     data-id="<?php echo esc_attr( $woosb_product->is_type( 'variable' ) ? 0 : $woosb_item['id'] ); ?>"
                                     data-price="<?php echo esc_attr( wc_get_price_to_display( $woosb_product ) ); ?>"
                                     data-qty="<?php echo esc_attr( $woosb_product_qty ); ?>">
									<?php if ( get_option( '_woosb_bundled_thumb', 'yes' ) !== 'no' ) { ?>
                                        <div class="woosb-thumb">
                                            <div class="woosb-thumb-ori">
												<?php echo apply_filters( 'woosb_item_thumbnail', $woosb_product->get_image(), $woosb_product ); ?>
                                            </div>
                                            <div class="woosb-thumb-new"></div>
                                        </div>
									<?php } ?>
                                    <div class="woosb-title">
										<?php
										do_action( 'woosb_before_item_name', $woosb_product );
										echo '<div class="woosb-title-inner">';
										if ( ( get_option( '_woosb_bundled_qty', 'yes' ) === 'yes' ) && ( get_post_meta( $product_id, 'woosb_optional_products', true ) !== 'on' ) ) {
											echo apply_filters( 'woosb_item_qty', $woosb_item['qty'] . ' × ', $woosb_item['qty'], $woosb_product );
										}
										$woosb_item_name = '';
										if ( $woosb_product->is_visible() && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
											$woosb_item_name .= '<a ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-btn" data-id="' . $woosb_item['id'] . '"' : '' ) . ' href="' . get_permalink( $woosb_item['id'] ) . '" ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
										}
										if ( $woosb_product->is_in_stock() && $woosb_product->has_enough_stock( $woosb_product_qty ) ) {
											$woosb_item_name .= $woosb_product->get_name();
										} else {
											$woosb_item_name .= '<s>' . $woosb_product->get_name() . '</s>';
										}
										if ( $woosb_product->is_visible() && ( get_option( '_woosb_bundled_link', 'yes' ) !== 'no' ) ) {
											$woosb_item_name .= '</a>';
										}
										echo apply_filters( 'woosb_item_name', $woosb_item_name, $woosb_product );
										echo '</div>';
										do_action( 'woosb_after_item_name', $woosb_product );
										if ( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ) {
											echo '<div class="woosb-description">' . apply_filters( 'woosb_item_description', $woosb_product->get_short_description(), $woosb_product ) . '</div>';
										}
										if ( $woosb_product->is_type( 'variable' ) ) {
											$attributes           = $woosb_product->get_variation_attributes();
											$available_variations = $woosb_product->get_available_variations();
											$variations_json      = wp_json_encode( $available_variations );
											$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
											if ( is_array( $attributes ) && ( count( $attributes ) > 0 ) ) {
												echo '<form class="variations_form" data-product_id="' . absint( $woosb_product->get_id() ) . '" data-product_variations="' . $variations_attr . '">';
												echo '<div class="variations">';
												foreach ( $attributes as $attribute_name => $options ) { ?>
                                                    <div class="variation">
                                                        <div class="label">
															<?php echo wc_attribute_label( $attribute_name ); ?>
                                                        </div>
                                                        <div class="select">
															<?php
															$attr     = 'attribute_' . sanitize_title( $attribute_name );
															$selected = isset( $_REQUEST[ $attr ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ $attr ] ) ) ) : $woosb_product->get_variation_default_attribute( $attribute_name );
															wc_dropdown_variation_attribute_options( array(
																'options'          => $options,
																'attribute'        => $attribute_name,
																'product'          => $woosb_product,
																'selected'         => $selected,
																'show_option_none' => esc_html__( 'Choose', 'woo-product-bundle' ) . ' ' . wc_attribute_label( $attribute_name )
															) );
															?>
                                                        </div>
                                                    </div>
												<?php }
												echo '<div class="reset">' . apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woo-product-bundle' ) . '</a>' ) . '</div>';
												echo '</div>';
												echo '</form>';
												if ( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ) {
													echo '<div class="woosb-variation-description"></div>';
												}
											}
											do_action( 'woosb_after_item_variations', $woosb_product );
										} else {
											echo wc_get_stock_html( $woosb_product );
										}
										?>
                                    </div>
									<?php if ( get_post_meta( $product_id, 'woosb_optional_products', true ) === 'on' ) {
										if ( ( $woosb_product->get_backorders() === 'no' ) && ( $woosb_product->get_stock_status() !== 'onbackorder' ) && is_int( $woosb_product->get_stock_quantity() ) && ( $woosb_product->get_stock_quantity() < $woosb_product_qty_max ) ) {
											$woosb_product_qty_max = $woosb_product->get_stock_quantity();
										}
										if ( $woosb_product->is_sold_individually() ) {
											$woosb_product_qty_max = 1;
										}
										if ( $woosb_product->is_in_stock() ) {
											?>
                                            <div class="woosb-qty">
                                                <input type="number" class="input-text qty text"
                                                       value="<?php echo esc_attr( $woosb_product_qty ); ?>"
                                                       min="<?php echo esc_attr( $woosb_product_qty_min ); ?>"
                                                       max="<?php echo esc_attr( $woosb_product_qty_max ); ?>"/>
                                            </div>
											<?php
										} else { ?>
                                            <div class="woosb-qty">
                                                <input type="number" class="input-text qty text" value="0" disabled/>
                                            </div>
										<?php }
									} ?>
									<?php if ( get_option( '_woosb_bundled_price', 'html' ) !== 'no' ) { ?>
                                        <div class="woosb-price">
                                            <div class="woosb-price-ori">
												<?php
												$woosb_price = '';
												switch ( get_option( '_woosb_bundled_price', 'html' ) ) {
													case 'price':
														$woosb_price = wc_price( wc_get_price_to_display( $woosb_product ) );
														break;
													case 'html':
														$woosb_price = $woosb_product->get_price_html();
														break;
													case 'subtotal':
														$woosb_price = wc_price( wc_get_price_to_display( $woosb_product, array( 'qty' => $woosb_item['qty'] ) ) );
														break;
												}
												echo apply_filters( 'woosb_item_price', $woosb_price, $woosb_product );
												?>
                                            </div>
                                            <div class="woosb-price-new"></div>
											<?php do_action( 'woosb_after_item_price', $woosb_product ); ?>
                                        </div>
									<?php } ?>
                                </div>
								<?php
							} ?>
                        </div>
						<?php
						if ( ! $product->is_fixed_price() && ( $product->has_variables() || $product->is_optional() ) ) {
							echo '<div class="woosb-total woosb-text"></div>';
						}
						do_action( 'woosb_after_table', $product );
						if ( $woosb_after_text = apply_filters( 'woosb_after_text', get_post_meta( $product_id, 'woosb_after_text', true ), $product_id ) ) {
							echo '<div class="woosb-after-text woosb-text">' . do_shortcode( stripslashes( $woosb_after_text ) ) . '</div>';
						}
						echo '</div>';
					}
				}

				function woosb_show_bundles( $product = null ) {
					if ( ! $product ) {
						global $product;
					}

					if ( $product->is_type( 'woosb' ) ) {
						return;
					}

					$product_id = $product->get_id();

					if ( $bundles = $this->woosb_get_bundles( $product_id ) ) {
						echo '<div class="woosb-bundles">';
						if ( $woosb_bundles_before_text = apply_filters( 'woosb_bundles_before_text', get_option( '_woosb_bundles_before_text' ) ) ) {
							echo '<div class="woosb_before_text woosb-before-text woosb-text">' . do_shortcode( stripslashes( $woosb_bundles_before_text ) ) . '</div>';
						}
						echo '<div class="woosb-products">';
						foreach ( $bundles as $bundle ) {
							echo '<div class="woosb-product">';
							echo '<div class="woosb-thumb">' . $bundle->get_image() . '</div>';
							echo '<div class="woosb-title"><a ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-btn" data-id="' . $bundle->get_id() . '"' : '' ) . ' href="' . $bundle->get_permalink() . '" ' . ( get_option( '_woosb_bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . $bundle->get_name() . '</a></div>';
							echo '<div class="woosb-price">';
							switch ( get_option( '_woosb_bundled_price', 'html' ) ) {
								case 'price':
									echo wc_price( wc_get_price_to_display( $bundle ) );
									break;
								default:
									echo $bundle->get_price_html();
									break;
							}
							echo '</div><!-- woosb-price -->';
							echo '</div><!-- woosb-product -->';
						}
						echo '</div><!-- woosb-products -->';
						wp_reset_postdata();
						if ( $woosb_bundles_after_text = apply_filters( 'woosb_bundles_after_text', get_option( '_woosb_bundles_after_text' ) ) ) {
							echo '<div class="woosb_after_text woosb-after-text woosb-text">' . do_shortcode( stripslashes( $woosb_bundles_after_text ) ) . '</div>';
						}
						echo '</div><!-- woosb-bundles -->';
					}
				}

				function woosb_get_items( $product_id ) {
					$woosb_arr = array();
					if ( $woosb_ids = get_post_meta( $product_id, 'woosb_ids', true ) ) {
						$woosb_items = explode( ',', $woosb_ids );
						if ( is_array( $woosb_items ) && count( $woosb_items ) > 0 ) {
							foreach ( $woosb_items as $woosb_item ) {
								$woosb_item_arr = explode( '/', $woosb_item );
								$woosb_arr[]    = array(
									'id'  => absint( isset( $woosb_item_arr[0] ) ? $woosb_item_arr[0] : 0 ),
									'qty' => absint( isset( $woosb_item_arr[1] ) ? $woosb_item_arr[1] : 1 )
								);
							}
						}
					}
					if ( count( $woosb_arr ) > 0 ) {
						return $woosb_arr;
					}

					return false;
				}

				function woosb_get_bundles( $product_id, $per_page = 500, $offset = 0 ) {
					$bundles          = array();
					$product_id_str   = $product_id . '/';
					$woosb_query_args = array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => $per_page,
						'offset'         => $offset,
						'tax_query'      => array(
							array(
								'taxonomy' => 'product_type',
								'field'    => 'slug',
								'terms'    => array( 'woosb' ),
								'operator' => 'IN',
							)
						),
						'meta_query'     => array(
							array(
								'key'     => 'woosb_ids',
								'value'   => $product_id_str,
								'compare' => 'LIKE',
							)
						)
					);
					$woosb_query      = new WP_Query( $woosb_query_args );
					if ( $woosb_query->have_posts() ) {
						while ( $woosb_query->have_posts() ) {
							$woosb_query->the_post();
							$woosb_product = wc_get_product( get_the_ID() );
							if ( ! $woosb_product ) {
								continue;
							}
							$bundles[] = $woosb_product;
						}
						wp_reset_query();
					}

					return ! empty( $bundles ) ? $bundles : false;
				}

				function woosb_ajax_add_order_item_meta( $item_id, $item, $order ) {
					$quantity = $item->get_quantity();
					if ( 'line_item' === $item->get_type() ) {
						$product = $item->get_product();
						if ( $product && $product->is_type( 'woosb' ) && ( $woosb_items = $product->get_items() ) ) {
							foreach ( $woosb_items as $woosb_item ) {
								$woosb_item_product              = wc_get_product( $woosb_item['id'] );
								$woosb_item_args['subtotal']     = 0;
								$woosb_item_args['total']        = 0;
								$woosb_item_args['subtotal_tax'] = 0;
								$woosb_item_args['total_tax']    = 0;
								if ( $woosb_item_product ) {
									$order->add_product( $woosb_item_product, $woosb_item['qty'] * $quantity, $woosb_item_args );
								}
							}
						}
						$order->save();
					}
				}

				function woosb_low_stock( $product ) {
					if ( 'no' === get_option( 'woocommerce_notify_low_stock', 'yes' ) ) {
						return;
					}

					$message = '';
					$subject = sprintf( '[%s] %s', WC_Email::get_blogname(), esc_html__( 'Bundle(s) low in stock', 'woo-product-bundle' ) );

					$product_id = $product->get_id();
					if ( $bundles = $this->woosb_get_bundles( $product_id ) ) {
						foreach ( $bundles as $bundle ) {
							$message .= sprintf( esc_html__( '%s is low in stock.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $bundle->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ) . ' <a href="' . get_edit_post_link( $bundle->get_id() ) . '" target="_blank">#' . $bundle->get_id() . '</a><br/>';
						}
						$message .= sprintf( esc_html__( '%1$s is low in stock. There are %2$d left.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $product->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ), html_entity_decode( strip_tags( $product->get_stock_quantity() ) ) ) . ' <a href="' . get_edit_post_link( $product_id ) . '" target="_blank">#' . $product_id . '</a>';

						wp_mail(
							apply_filters( 'woocommerce_email_recipient_low_stock', get_option( 'woocommerce_stock_email_recipient' ), $bundle ),
							apply_filters( 'woocommerce_email_subject_low_stock', $subject, $bundle ),
							apply_filters( 'woocommerce_email_content_low_stock', $message, $bundle ),
							apply_filters( 'woocommerce_email_headers', 'Content-Type: text/html; charset=UTF-8', 'low_stock', $bundle ),
							apply_filters( 'woocommerce_email_attachments', array(), 'low_stock', $bundle )
						);
					}
				}

				function woosb_no_stock( $product ) {
					if ( 'no' === get_option( 'woocommerce_notify_no_stock', 'yes' ) ) {
						return;
					}

					$message = '';
					$subject = sprintf( '[%s] %s', WC_Email::get_blogname(), esc_html__( 'Bundle(s) out of stock', 'woo-product-bundle' ) );

					$product_id = $product->get_id();
					if ( $bundles = $this->woosb_get_bundles( $product_id ) ) {
						foreach ( $bundles as $bundle ) {
							$message .= sprintf( esc_html__( '%s is out of stock.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $bundle->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ) . ' <a href="' . get_edit_post_link( $bundle->get_id() ) . '" target="_blank">#' . $bundle->get_id() . '</a><br/>';
						}
						$message .= sprintf( esc_html__( '%s is out of stock.', 'woo-product-bundle' ), html_entity_decode( strip_tags( $product->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ) . ' <a href="' . get_edit_post_link( $product_id ) . '" target="_blank">#' . $product_id . '</a>';

						wp_mail(
							apply_filters( 'woocommerce_email_recipient_no_stock', get_option( 'woocommerce_stock_email_recipient' ), $bundle ),
							apply_filters( 'woocommerce_email_subject_no_stock', $subject, $bundle ),
							apply_filters( 'woocommerce_email_content_no_stock', $message, $bundle ),
							apply_filters( 'woocommerce_email_headers', 'Content-Type: text/html; charset=UTF-8', 'no_stock', $bundle ),
							apply_filters( 'woocommerce_email_attachments', array(), 'no_stock', $bundle )
						);
					}
				}

				function woosb_clean_ids( $ids ) {
					$ids = preg_replace( '/[^,\/0-9]/', '', $ids );

					return $ids;
				}

				function woosb_deactivation() {
					wp_clear_scheduled_hook( 'woosb_cron_jobs' );
				}
			}

			new WPcleverWoosb();
		}
	}
} else {
	add_action( 'admin_notices', 'woosb_notice_premium' );
}

if ( ! function_exists( 'woosb_notice_wc' ) ) {
	function woosb_notice_wc() {
		?>
        <div class="error">
            <p><strong>WPC Product Bundles</strong> requires WooCommerce version 3.0.0 or greater.</p>
        </div>
		<?php
	}
}

if ( ! function_exists( 'woosb_notice_premium' ) ) {
	function woosb_notice_premium() {
		?>
        <div class="error">
            <p>Seems you're using both free and premium version of <strong>WPC Product Bundles</strong>. Please
                deactivate the free version when using the premium version.</p>
        </div>
		<?php
	}
}