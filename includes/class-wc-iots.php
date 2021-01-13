<?php
/**
 * Plugin's main class
 *
 * @package Iots_For_WooCommerce
 */

/**
 * WooCommerce bootstrap class.
 */
class WC_IOTS {

	/**
	 * Initialize the plugin public actions.
	 */
	public static function init() {
		// Checks if WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			self::includes();

			//add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wc_iots_load_scripts'));
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'wc_iots_load_scripts'), 999);
			
			add_filter( 'plugin_action_links_' . plugin_basename( WC_IOTS_PLUGIN_FILE ), array( __CLASS__, 'plugin_action_links' ) );

			// Register the integration.
			add_filter( 'woocommerce_integrations', array( __CLASS__, 'add_integration' ) );

			// Create the new order column			
			add_filter( 'woocommerce_account_orders_columns', array( __CLASS__, 'add_account_orders_column'), 10, 1 );
			add_filter( 'woocommerce_account_orders_columns', array( __CLASS__, 'rename_order_status_column'), 11, 1 );
			add_filter( 'woocommerce_account_orders_columns', array( __CLASS__, 'reorder_account_orders_column'), 999, 1 );
			add_action( 'woocommerce_my_account_my_orders_column_iots-status-column', array( __CLASS__, 'add_account_orders_tracking_status_column_rows') );
			add_action( 'woocommerce_my_account_my_orders_column_iots-est-date-column', array( __CLASS__, 'add_account_orders_tracking_est_date_column_rows') );
		} else {
			add_action( 'admin_notices', array( __CLASS__, 'woocommerce_missing_notice' ) );
		}
	}


	/**
     * Add a new integration to WooCommerce.
     */
    public static function add_integration( $integrations ) {
		$integrations[] = 'WC_Integration_IOTS';
		return $integrations;
	}


	/**
	 * Set Script files.
	 */
	public static function wc_iots_load_scripts(){
		// load the main css scripts file
		wp_enqueue_style( 'wc-iots-styles-css', plugins_url( '/css/styles.css', __FILE__ ) );
		
		// load the main js scripts file
		wp_enqueue_script( 'wc-iots-main-js', plugins_url( '/js/main.js', __FILE__ ), array(), '1.0.0', true );
	}

	/**
	 * Action links.
	 *
	 * @param array $links Action links.
	 *
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$plugin_links   = array();
		$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=integration&section=iots' ) ) . '">Configuração</a>';

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Includes.
	 */
	private static function includes() {
		include_once dirname( __FILE__ ) . '/class-wc-iots-integration.php';
		include_once dirname( __FILE__ ) . '/class-wc-iots-methods.php';
	}

	/**
	 * WooCommerce missing notice.
	 */
	public static function woocommerce_missing_notice() {
		include dirname( __FILE__ ) . '/admin/views/html-notice-missing-woocommerce.php';
	}

	/**
	 * Add a new column in order table
	 */
	public static function add_account_orders_column( $columns ){

		$iots_methods = new WC_Methods_IOTS();
		//coluna status do rastreio
		$iots_status_title = $iots_methods->get_iots_status_column_title();
		$col_status_title = !empty($iots_status_title) ? $iots_status_title : 'Status do Rastreio';
		//coluna data estimada de entrega
		$iots_est_date_title = $iots_methods->get_iots_est_date_column_title();
		$col_est_date_title = !empty($iots_est_date_title) ? $iots_est_date_title : 'Data Estimada de Entrega';

		$columns['iots-status-column'] = __( $col_status_title, 'woocommerce' );
		$columns['iots-est-date-column'] = __( $col_est_date_title, 'woocommerce' );
		return $columns;

	}

	/**
	 * Rename column order status 
	 */
	public static function rename_order_status_column( $columns ){

		$iots_methods = new WC_Methods_IOTS();
		$iots_alt_status_title = $iots_methods->get_iots_alt_status_title();
		if (!empty($iots_alt_status_title)){
			$col_alt_status_title = $iots_alt_status_title;
			$new_columns = [];
			foreach ( $columns as $key => $value ) {
			  if ($key == 'order-status'){
				$new_columns['order-status'] = __( $col_alt_status_title, 'woocommerce' );
			  }else{
				$new_columns[$key] = $value;
			  }
			}
		}else{
			$new_columns = $columns;
		}
		return $new_columns;
	}

	/**
	 * Add the intelipost status from api in the new column
	 */
	public static function add_account_orders_tracking_status_column_rows( $order ) {
		$iots_methods = new WC_Methods_IOTS();
		$iots_status = $iots_methods->get_iots_tracking_status($order);
		echo esc_html( $iots_status );
	}

	public static function add_account_orders_tracking_est_date_column_rows( $order ) {
		$iots_methods = new WC_Methods_IOTS();
		$iots_est_date = $iots_methods->get_iots_tracking_est_date($order);
		echo esc_html( $iots_est_date );
	}

	/**
	 * Reorder a new column in order table
	 */
	public static function reorder_account_orders_column( $columns ){
		$new_columns = [];
		$position = 0;

		$iots_methods = new WC_Methods_IOTS();
		$iots_position = $iots_methods->get_iots_column_position();
		$col_position = !empty($iots_position) && $iots_position > 0 && $iots_position <= count( $columns)  ? $iots_position : count($columns);
		

		foreach ( $columns as $key => $value ) {

			if ($position == ($col_position -1)){
				$new_columns['iots-status-column'] = $columns['iots-status-column'];
				$new_columns['iots-est-date-column'] = $columns['iots-est-date-column'];
			}

			if ($key != 'iots-status-column' && $key != 'iots-est-date-column'){
				$new_columns[$key] = $value;
			}
	
			$position ++;
		}

		return $new_columns;
	}
	
}
