<?php
/**
 * IOTS Integration.
 *
 * @package Iots_For_WooCommerce
 */
if (! class_exists ( 'WC_Methods_IOTS' )):
class WC_Methods_IOTS extends WC_Integration {
	/**
	 * Init and hook in the integration.
	 */
	
	private $iots_api_key               = null;
    private $iots_status_column_title   = null;
    private $iots_est_date_column_title = null;
    private $iots_alt_status_title      = null;
    private $iots_column_position       = null;
    private $iots_requisition_type      = null;
    private $iots_duplicate_id          = null;

	public function __construct() {
		$this->id                 	        = 'iots';
		$this->iots_api_key			        = $this->get_option( 'iots_key' );
        $this->iots_status_column_title	    = $this->get_option( 'iots_status_column_title' );
        $this->iots_est_date_column_title   = $this->get_option( 'iots_est_date_column_title' );
        $this->iots_alt_status_title        = $this->get_option( 'iots_alt_status_title' );
        $this->iots_column_position	        = $this->get_option( 'iots_column_position' );
        $this->iots_requisition_type	    = $this->get_option( 'iots_requisition_type' );
        $this->iots_duplicate_id	        = $this->get_option( 'iots_duplicate_id' );
    }
    
    public function get_iots_api_key(){
        return $this->iots_api_key;
    }

    public function get_iots_status_column_title(){
        return $this->iots_status_column_title;
    }

    public function get_iots_est_date_column_title(){
        return $this->iots_est_date_column_title;
    }

    public function get_iots_alt_status_title(){
        return $this->iots_alt_status_title;
    }

    public function get_iots_column_position(){
        return $this->iots_column_position;
    }

    public function get_iots_requisition_type(){
        return $this->iots_requisition_type;
    }

    public function get_iots_duplicate_id(){
        return $this->iots_duplicate_id;
    }
    
    public function get_iots_tracking_status($order){
        $req_type = $this->iots_requisition_type;
        $result = '';
        if ($req_type == 'order_id'){
            $number = $this->iots_duplicate_id == 'yes' ? $order->get_id( '_iots_field' ) . "_" .$order->get_id( '_iots_field' ) : $order->get_id( '_iots_field' );
            $endpoint = "sales_order_number";
        }else if($req_type == 'order_nf'){
            $number = $this->get_nf_number_by_order_notes($order);
            $endpoint = "invoice_key";
        }

        $result = $this->get_tracking_status_by_order_id($number, $endpoint);
        // id de teste '144866_144866'
        // key de teste '35210129107762000254550010000193291730200640'

        return $result;
    }

    public function get_iots_tracking_est_date($order){
        $req_type = $this->iots_requisition_type;
        $result = '';
        if ($req_type == 'order_id'){
            $number = $this->iots_duplicate_id == 'yes' ? $order->get_id( '_iots_field' ) . "_" .$order->get_id( '_iots_field' ) : $order->get_id( '_iots_field' );
            $endpoint = "sales_order_number";
            
        }else if($req_type == 'order_nf'){
            $number = $this->get_nf_number_by_order_notes($order);
            $endpoint = "invoice_key";
        }

        $result = $this->get_tracking_est_date_by_order_id($number, $endpoint);
        // id de teste '144866_144866'
        // key de teste '35210129107762000254550010000193291730200640'

        return $result;
    }

    private function get_tracking_status_by_order_id($order_number, $api_endpoint) {
        $result = '';
        
        if (!(empty($order_number) || empty($api_endpoint))){

            $url = "https://api.intelipost.com.br/api/v1/shipment_order/";
            $header_var = [
                "Content-Type" => "application/json",
                "api-key" => $this->iots_api_key
            ];

            $url_final = $url . $api_endpoint . "/" . $order_number;
            
            $response = wp_remote_post( $url_final, array(
                'method'      => 'GET',
                'timeout'     => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => $header_var,
                )
            );

            if ( $response["response"]["code"] == 200 ) {
                $response_data = json_decode($response["body"]) ;
                if ($api_endpoint == "sales_order_number"){
                    //$result = $response_data->content->shipment_orders[0]->shipment_order_volume_state;
                    $tracking_array = $response_data->content->shipment_orders[0]->shipment_order_volume_array[0]->shipment_order_volume_state_history_array;
                    $result = $this->get_last_tracking_state($tracking_array);
                }else if ($api_endpoint == "invoice_key"){
                    $tracking_array = $response_data->content[0]->shipment_order_volume_array[0]->shipment_order_volume_state_history_array;
                    $result = $this->get_last_tracking_state($tracking_array);
                }
            }
        }
        return $result;
    }

    private function get_last_tracking_state($tracking_array){

        $result = '';
        $int_date = 0;
        for ($i=0;$i<count($tracking_array);$i++){
            if ($tracking_array[$i]->created > $int_date){
                $int_date = $tracking_array[$i]->created;
                $result = $tracking_array[$i]->shipment_volume_micro_state->shipment_volume_state_localized;
            }
        }
        
        return $result;

    }

    private function get_tracking_est_date_by_order_id($order_number, $api_endpoint) {
        $url = "https://api.intelipost.com.br/api/v1/shipment_order/";
        $header_var = [
            "Content-Type" => "application/json",
            "api-key" => $this->iots_api_key
        ];

        $url_final = $url . $api_endpoint . "/" . $order_number;

        $response = wp_remote_post( $url_final, array(
            'method'      => 'GET',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => $header_var,
            )
        );
        
        if ( $response["response"]["code"] == 200 ) {
            $response_data = json_decode($response["body"]) ;
            if ($api_endpoint == "sales_order_number"){
                $result = date("d/m/Y", strtotime($response_data->content->shipment_orders[0]->estimated_delivery_date_iso));
            }else if ($api_endpoint == "invoice_key"){
                $result = date("d/m/Y", strtotime($response_data->content[0]->estimated_delivery_date_iso));
            }
            
        } else {
            $result = '';
        }

        return $result;
    }

    private function get_nf_number_by_order_notes($order){

        $order_notes = wc_get_order_notes(['order_id' => $order->get_id('_iots_field'),'type' => 'customer',]);
        $result = '';
        for ($i=0;$i<count($order_notes);$i++){
            $note_pieces = explode(" ", $order_notes[$i]->content);
            
            if (in_array("(NFe):", $note_pieces)) { 
                $result = $note_pieces[count($note_pieces) -1];
                break;
            }
        }
        return $result;
    }

}
endif ;