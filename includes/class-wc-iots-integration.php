<?php
/**
 * IOTS Integration.
 *
 * @package Iots_For_WooCommerce
 */
if (! class_exists ( 'WC_Integration_IOTS' )):
class WC_Integration_IOTS extends WC_Integration {
	/**
	 * Init and hook in the integration.
	 */
	
	public function __construct() {
		global $woocommerce;
		$this->id                 = 'iots';
		$this->method_title       = __( 'Intelipost - Order Tracking Status', 'iots-integration' );
		$this->method_description = __( 'The following options are used to record the api key', 'iots-integration' );
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		// Define user set variables.
		$this->iots_key						= $this->get_option( 'iots_key' );
		$this->iots_status_column_title		= $this->get_option( 'iots_status_column_title' );
		$this->iots_est_date_column_title	= $this->get_option( 'iots_est_date_column_title' );
		$this->iots_alt_status_title		= $this->get_option( 'iots_alt_status_title' );
		$this->iots_column_position			= $this->get_option( 'iots_column_position' );
		$this->iots_requisition_type		= $this->get_option( 'iots_requisition_type' );
		$this->iots_duplicate_id			= $this->get_option( 'iots_duplicate_id' );

		// Actions.
		add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
	}
	/**
	 * Initialize integration settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'iots_key' => array(
				'title'             => __( 'Chave da API', 'iots-integration' ),
				'type'              => 'text',
				'label'             => __( 'Chave da API', 'iots-integration' ),
				'default'           => '',
				'description'       => __( 'Entre com sua chave de API disponibilizada pela Intelipost.', 'iots-integration' ),
				'desc_tip'          => true
			),
			'iots_status_column_title' => array(
				'title'             => __( 'Titulo da coluna Status de Rastreamento', 'iots-integration' ),
				'type'              => 'text',
				'label'             => __( 'Titulo da coluna Status de Rastreamento', 'iots-integration' ),
				'default'           => '',
				'description'       => __( 'Titulo da coluna Status de Rastreamento que é adiciona na lista de pedidos na área do cliente.', 'iots-integration' ),
				'desc_tip'          => true
			),
			'iots_est_date_column_title' => array(
				'title'             => __( 'Titulo da coluna Data Estimada de Entrega', 'iots-integration' ),
				'type'              => 'text',
				'label'             => __( 'Titulo da Coluna Data Estimada de Entrega', 'iots-integration' ),
				'default'           => '',
				'description'       => __( 'Titulo da Coluna Data Estimada de Entrega que é adiciona na lista de pedidos na área do cliente.', 'iots-integration' ),
				'desc_tip'          => true
			),	
			'iots_alt_status_title' => array(
				'title'             => __( 'Titulo alternativo para a coluna Status', 'iots-integration' ),
				'type'              => 'text',
				'label'             => __( 'Titulo alternativo para a coluna Status', 'iots-integration' ),
				'default'           => '',
				'description'       => __( 'Titulo alternativo para a coluna Status original do woocommerce.', 'iots-integration' ),
				'desc_tip'          => true
			),
			'iots_column_position' => array(
				'title'             => __( 'Posição das colunas', 'iots-integration' ),
				'type'              => 'number',
				'label'             => __( 'Posição das colunas', 'iots-integration' ),
				'default'           => '',
				'description'       => __( 'Número da posição das colunas que são adicionadas na lista de pedidos na área do cliente.', 'iots-integration' ),
				'desc_tip'          => true
			),
			'iots_requisition_type' => array(
				'title'             => __( 'Tipo de Requisição', 'iots-integration' ),
				'type'              => 'select',
				'label'             => __( 'Tipo de Requisição', 'iots-integration' ),
				'class'				=> 'ic_iots_requisition_type',
				'default'           => 'order_id',
				'description'       => __( 'Selecione o tipo de requisição que o sistema deverá fazer a API da Intelipost.', 'iots-integration' ),
				'desc_tip'          => true,
				'options' => array(
					'order_id' => __( 'Numero do Pedido', 'iots-integration' ),
					'order_nf' => __( 'Chave da Nota Fiscal', 'iots-integration' )
			   	)
			),
			'iots_duplicate_id' => array(
				'title'             => __( 'Duplicar Número do Pedido', 'iots-integration' ),
				'type'              => 'checkbox',
				'label'             => __( 'sim', 'iots-integration' ),
				'class'				=> 'ic_iots_duplicate_id',
				'default'           => '',
				'description'       => __( 'Duplica o valor do id (Ex.: 123) deixando com a seguinte aparência: 123_123.', 'iots-integration' ),
				'desc_tip'          => true
			),
		);
	}

}
endif ;