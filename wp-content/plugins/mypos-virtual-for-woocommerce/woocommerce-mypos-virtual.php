<?php
/**
 * Plugin Name: Woocommerce myPOS Checkout
 * Plugin URI:
 * Description: myPOS Checkout.
 * Version: 1.1.21
 * Author: myPOS Europe LTD
 * Author URI: https://www.mypos.eu
 * Developer: Intercard Finance
 * Developer URI:
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Makes sure the plugin is defined before trying to use it
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

$activate = false;

if (is_multisite()) {
	if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
		$activate = true;
	} elseif ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$activate = true;
	}
} else {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$activate = true;
	}
}

if ($activate) {
	add_action( 'plugins_loaded', 'init_your_gateway_class' );
	add_filter( 'woocommerce_payment_gateways', 'add_mypos_virtual_class' );
}


function add_mypos_virtual_class( $methods ) {
	$methods[] = 'WC_Gateway_IPC';
	return $methods;
}

function init_your_gateway_class() {
	include_once( 'includes/class-wc-gateway-ipc.php' );
}