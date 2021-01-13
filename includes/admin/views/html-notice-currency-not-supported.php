<?php
/**
 * Admin View: Notice - Currency not supported.
 *
 * @package Iots_For_WooCommerce/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'Incuca Tech - Intelipost Tracking Status Disabled', 'woocommerce-iots' ); ?></strong>: <?php printf( __( 'Moeda <code>%s</code> não suportada. É aceito apenas BRL', 'woocommerce-iots' ), get_woocommerce_currency() ); ?>
	</p>
</div>
