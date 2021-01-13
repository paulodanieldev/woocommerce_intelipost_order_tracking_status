<?php

/**
 * Plugin Name: InCuca Tech - Intelipost Order Tracking Status for Woocommerce
 * Plugin URI: https://github.com/InCuca/woocommerce-intelipost-order-tracking-status
 * Description: Show order tracking status using the intelipost API in my account's order table.
 * Author: InCuca Tech
 * Author URI: https://incuca.net
 * Version: 1.0.0
 * Tested up to: 5.5.6
 * License: GNU General Public License v3.0
 *
 * @package Iots_For_WooCommerce
 */

defined('ABSPATH') or exit;

define( 'WC_IOTS_VERSION', '1.0.0' );
define( 'WC_IOTS_PLUGIN_FILE', __FILE__ );

if ( ! class_exists( 'WC_IOTS' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-iots.php';
	add_action( 'plugins_loaded', array( 'WC_IOTS', 'init' ) );
}