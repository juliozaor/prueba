<?php
class wf_dhl_woocommerce_shipping_admin{

    public $found_rates = array();
    public function __construct(){
        $this->settings                         = get_option( 'woocommerce_'.WF_DHL_ID.'_settings', null );
        $settings_custom_services               = get_option('custom_services');
        $this->custom_services = !empty($settings_custom_services)? $settings_custom_services: (($this->settings['services'] ) ? $this->settings['services'] : array());
        $this->enable_shipping_label            = isset( $this->settings['enabled_label'] ) ? $this->settings['enabled_label'] : 'yes';
        $this->image_type                       = isset( $this->settings['image_type'] ) ? $this->settings['image_type'] : '';
        $this->services                         = include( 'data-wf-service-codes.php' );
        $this->sat_delivery                     = isset( $this->settings['enable_saturday_delivery'] ) ? $this->settings['enable_saturday_delivery'] : '';
        $this->origin_country                   = isset( $this->settings['base_country']) ? $this->settings['base_country'] : '';
        $this->return_label_key                 = isset( $this->settings['return_label_key'] ) ? $this->settings['return_label_key'] : '';
        $this->cash_on_delivery                 = isset( $this->settings['cash_on_delivery'] ) ? $this->settings['cash_on_delivery'] : '';
        $this->label_contents_text              = isset( $this->settings['label_contents_text'] ) ? $this->settings['label_contents_text'] : 'NA';
        $this->label_comment_text              = isset( $this->settings['label_comment_text'] ) ? $this->settings['label_comment_text'] : 'NA';
        $this->show_front_end_shipping_method   = isset( $this->settings['show_front_end_shipping_method'] ) ? $this->settings['show_front_end_shipping_method'] : '';
        $this->debug                            = ( isset($this->settings[ 'debug' ]) && ($this->settings[ 'debug' ] == 'yes')) ? true : false;
        $this->default_domestic_service         = isset( $this->settings['default_domestic_service'] ) ? $this->settings['default_domestic_service'] : '';
        $this->default_international_service    = isset( $this->settings['default_international_service'] ) ? $this->settings['default_international_service'] : '';
        $this->plt                              = ( !empty($this->settings['plt']) && $this->settings['plt'] === 'yes' ) ? true : false;
        $this->pickup_enable                    = isset( $this->settings['add_pickup'] ) && $this->settings['add_pickup'] =='yes' ? true : false;
        $this->user_settings = get_option('woocommerce_wf_dhl_shipping_settings');
        if ( $this->settings['dimension_weight_unit'] == 'KG_CM' ) {
            $this->weight_unit = 'KGS';
            $this->dim_unit    = 'CM';
        } else {
            $this->weight_unit = 'LBS';
            $this->dim_unit    = 'IN';
        }
        $this->insure_currency          = isset( $this->settings['insure_currency'] ) ?  $this->settings['insure_currency'] : '';
        $this->insure_converstion_rate  = !empty($this->settings['insure_converstion_rate']) ? $this->settings['insure_converstion_rate'] : '';

        $this->account_number  = isset( $this->settings['account_number'] ) ?  $this->settings['account_number'] : '';
        $this->add_trackingpin_shipmentid = isset($this->settings['add_trackingpin_shipmentid']) ? $this->settings['add_trackingpin_shipmentid'] : 'no';
        $this->id = WF_DHL_ID;

        $this->latin_encoding = isset($this->settings['latin_encoding']) && $this->settings['latin_encoding'] == 'yes' ? true : false;
        $utf8_support = $this->latin_encoding ? '?isUTF8Support=true' : '';

        $_stagingUrl           = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet'.$utf8_support;
        $_productionUrl        = 'https://xmlpi-ea.dhl.com/XMLShippingServlet'.$utf8_support;
        
        $this->production      = (!empty($this->settings['production']) && $this->settings['production'] === 'yes') ? true : false;
        $this->service_url     = ($this->production == true) ? $_productionUrl : $_stagingUrl;
        $this->site_id         = !empty($this->settings['site_id']) ? $this->settings['site_id'] : '' ;
        $this->site_password   = !empty($this->settings['site_password']) ? $this->settings['site_password'] : '';
        $this->freight_shipper_city = isset($this->settings['freight_shipper_city']) ? $this->settings['freight_shipper_city'] : '';
        $this->origin          = apply_filters('woocommerce_dhl_origin_postal_code', strtoupper(isset($this->settings['origin']) ? $this->settings['origin'] : ''));
        $this->request_type    = !empty( $this->settings['request_type'] ) ? $this->settings['request_type'] : '';
        $this->dimension_unit  = (isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN') ? 'IN' : 'CM';
        $this->weight_unit     = (isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN') ? 'LBS' : 'KG';
        $this->conversion_rate = (!empty($this->settings['conversion_rate']) && !(is_plugin_active('woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php'))) ? $this->settings['conversion_rate'] : '';
        $this->quoteapi_dimension_unit = $this->dimension_unit;
        $this->quoteapi_weight_unit = $this->weight_unit == 'LBS' ? 'LB' : 'KG';
        $this->insure_contents = ( isset($this->settings['insure_contents']) && ($this->settings['insure_contents'] == 'yes')) ? true : false;
        $this->insure_contents_chk = ( isset($this->settings['insure_contents_chk']) && ($this->settings['insure_contents_chk'] == 'yes')) ? true : false;
        $this->dir_download = ( isset($this->settings['dir_download']) && $this->settings['dir_download'] =='yes' ) ? 'attachment' : 'inline';
        $this->select_service_check_box = (isset($this->settings['services_select']) && $this->settings['services_select'] ==='yes') ? true : false;
        $this->general_settings = get_option('woocommerce_wf_dhl_shipping_settings');
        $this->order = '';
        $this->packing_method = (isset($this->settings['packing_method']) && !empty($this->settings['packing_method']))? $this->settings['packing_method']: 'per_item';
        $this->shop_currency = '';

        if ( ! class_exists( 'wf_dhl_woocommerce_shipping_admin_helper' ) )
        include_once('class-wf-dhl-woocommerce-shipping-admin-helper.php');
        
        add_action('load-edit.php', array( $this, 'wf_orders_bulk_action_dhl_express' ) ); //to handle post id for bulk actions
        add_action('admin_notices', array( $this, 'bulk_label_admin_notices_dhl_express') );


        if (is_admin() && $this->enable_shipping_label === 'yes') {
            add_action('add_meta_boxes', array($this, 'wf_add_dhl_metabox'),15);
        }

        if ( isset( $_GET['wf_dhl_generate_packages'] ) ) {
            add_action( 'init', array( $this, 'wf_dhl_generate_packages' ), 15 );
        }

        if ( isset( $_GET['wf_dhl_generate_return_packages'] ) ) {
            add_action( 'init', array( $this, 'wf_dhl_generate_return_packages' ), 15 );
        }

        if ( isset( $_GET['dhl_product_choose_return_shipment'] ) ) {
            add_action( 'init', array( $this, 'dhl_product_choose_return_shipment' ), 15 );
        }

        if ( isset( $_GET['wf_dhl_process_return_packages'] ) ) {
            add_action( 'init', array( $this, 'wf_dhl_process_return_packages' ), 15 );
        }

        if ( isset( $_GET['wf_dhl_process_pickup_packages'] ) ) {
            add_action( 'init', array( $this, 'wf_dhl_process_pickup_packages' ), 15 );
        }

        if (isset($_GET['generate_proforma_invoice_dhl_elex'])) {
            add_action('init', array($this, 'generate_proforma_invoice'));
        }

        if (isset($_GET['print_proforma_invoice_dhl_elex'])) {
            add_action('init', array($this, 'print_proforma_invoice'));
        }

        if (isset($_GET['delete_proforma_invoice_dhl_elex'])) {
            add_action('init', array($this, 'delete_proforma_invoice'));
        }

        if (isset($_GET['wf_dhl_createshipment'])) {
            if (! isset( $_GET['dhl_india']) || $_GET['dhl_india'] == 'false' ) {
                add_action('init', array($this, 'wf_dhl_createshipment'));
            }
        }

        if (isset($_GET['wf_dhl_create_return_shipment'])) {
            add_action('init', array($this, 'wf_dhl_create_return_shipment'));
        }

        if (isset($_GET['wf_dhl_delete_label'])) {
            add_action('init', array($this, 'wf_dhl_delete_label'));
        }

        if (isset($_GET['wf_dhl_delete_return_label'])) {
            add_action('init', array($this, 'wf_dhl_delete_return_label'));
        }

        if (isset($_GET['wf_dhl_viewlabel'])) {
            add_action('init', array($this, 'wf_dhl_viewlabel'));
        }

        if (isset($_GET['wf_dhl_viewreturnlabel'])) {
            add_action('init', array($this, 'wf_dhl_viewreturnlabel'));
        }

        if (isset($_GET['wf_dhl_view_commercial_invoice'])) {
            add_action('init', array($this, 'wf_dhl_view_commercial_invoice'));
        }

        if (isset($_GET['wf_dhl_view_return_commercial_invoice'])) {
            add_action('init', array($this, 'wf_dhl_view_return_commercial_invoice'));
        }

        if (isset($_GET['wf_dhl_generate_packages_rates'])) {
            add_action('init', array($this, 'wf_dhl_generate_packages_rates'));
        }

        global $wpdb;

        $query = "SELECT ID FROM `".$wpdb->prefix."posts` WHERE post_type = 'shop_order' ORDER BY `ID` DESC LIMIT 1";

        $this->last_order_id = $wpdb->get_results($query);
        $this->last_order_id = array_shift($this->last_order_id);

        $last_order_wf_dhl_insurance = false;

        if(isset($this->settings['enabled']) && ($this->settings['enabled'] === 'yes')){
            $last_order_wf_dhl_insurance = get_option('wf_dhl_insurance');
        }else{
            $last_order_wf_dhl_insurance = get_option('wf_dhl_insurance_enabled_checkout_no_real_time_enabled');
        }

        if(is_object($this->last_order_id)){
            $last_order_post_meta_insurance = get_post_meta($this->last_order_id->ID, 'wf_dhl_insurance', true);
        }

        if(empty($last_order_post_meta_insurance) && is_object($this->last_order_id)){
            update_post_meta($this->last_order_id->ID, 'wf_dhl_insurance', $last_order_wf_dhl_insurance);
        }


        $this->is_woocommerce_composite_products_installed = (in_array('woocommerce-composite-products/woocommerce-composite-products.php',get_option('active_plugins')))? true: false;

        $this->is_woocommerce_multi_currency_installed = (in_array('woocommerce-multicurrency/woocommerce-multicurrency.php',get_option('active_plugins')))? true: false;
    }

    function wf_dhl_delete_return_label()
    {
        $get_id = $_GET['wf_dhl_delete_return_label'];

        $return_shipment_id = get_post_meta($get_id,'wf_woo_dhl_return_shipmentId');
        if(!empty($return_shipment_id))
        {
            foreach ($return_shipment_id as $value) {
                delete_post_meta($get_id,'wf_woo_dhl_return_shippingLabel_'.$value );
                delete_post_meta($get_id,'wf_woo_dhl_return_packageDetails_'.$value );
                delete_post_meta($get_id,'wf_woo_dhl_shipping_return_commercialInvoice_'.$value );
            }
        }       
        delete_post_meta($get_id,'wf_woo_dhl_return_shipmentId');
        delete_post_meta($get_id,'wf_woo_dhl_shipmentReturnErrorMessage');
        delete_post_meta($get_id,'wf_woo_dhl_return_service_code');
        delete_post_meta( $get_id, '_wf_dhl_stored_return_packages');
        delete_post_meta( $get_id, '_wf_dhl_process_return_shipment');
        delete_post_meta( $get_id, '_wf_dhl_stored_return_packages');

        wp_redirect( admin_url( '/post.php?post='.$get_id.'&action=edit') );
        exit;
    }


    function wf_dhl_delete_label()
    {
        $get_id = $_GET['wf_dhl_delete_label'];
        $shipment_id = get_post_meta($get_id,'wf_woo_dhl_shipmentId');

        delete_post_meta($get_id,'wfdhlexpresstrackingmsg');
        foreach ($shipment_id as $value) {
            delete_post_meta($get_id,'wf_woo_dhl_shippingLabel_'.$value );
            delete_post_meta($get_id,'wf_woo_dhl_shipping_commercialInvoice_'.$value );
        }
        $return_shipment_id = get_post_meta($get_id,'wf_woo_dhl_return_shipmentId');

        if(!empty($return_shipment_id))
        {
            foreach ($return_shipment_id as $value) {
                delete_post_meta($get_id,'wf_woo_dhl_return_shippingLabel_'.$value );
                delete_post_meta($get_id,'wf_woo_dhl_return_packageDetails_'.$value );
                delete_post_meta($get_id,'wf_woo_dhl_shipping_return_commercialInvoice_'.$value );
            }
        }       
        delete_post_meta( $get_id,'_wf_dhl_pickup_shipment');
        delete_post_meta( $get_id,'_wf_dhl_pickup_shipment_error');

        delete_post_meta( $get_id,'wf_woo_dhl_shipmentId');
        delete_post_meta( $get_id,'wf_woo_dhl_shipmentErrorMessage');
        delete_post_meta( $get_id,'wf_woo_dhl_service_code');
        delete_post_meta( $get_id,'wf_woo_dhl_return_shipmentId');
        delete_post_meta( $get_id,'wf_woo_dhl_shipmentReturnErrorMessage');
        delete_post_meta( $get_id,'wf_woo_dhl_return_service_code');
        delete_post_meta( $get_id, '_wf_dhl_stored_return_packages');
        delete_post_meta( $get_id, '_wf_dhl_process_return_shipment');
        delete_post_meta( $get_id, '_wf_dhl_stored_return_packages');

        wp_redirect( admin_url( '/post.php?post='.$get_id.'&action=edit') );
        exit;

    }

    function wf_dhl_generate_packages($bulk_order_id){
        if( !$this->wf_user_permission() ) {
            echo "You don't have admin privileges to view this page.";
            exit;
        }

        if(isset($bulk_order_id) && !empty($bulk_order_id)){
            $post_id = $bulk_order_id;    
        }else{
            $post_id    =   base64_decode($_GET['wf_dhl_generate_packages']);
        }

        $order = $this->wf_load_order( $post_id );
        if ( !$order ) return;

        $woo_dhl_wrapper = new wf_dhl_woocommerce_shipping_admin_helper();

        $packages = $woo_dhl_wrapper->wf_get_package_from_order($order);
        $order_items_total = $woo_dhl_wrapper->get_order_items_total($packages[0]['contents']);
        update_option('current_order_items_total_express_dhl_elex', $order_items_total);
        update_post_meta($post_id, 'initial_generated_packages_dhl_elex', $packages);

        $dhl_requests = array();
        foreach ($packages as $key => $package) {
            $package_single = $woo_dhl_wrapper->get_dhl_packages($package);
            $package_data[] = $package_single;
            if($package['destination']['country'] == 'CH' && $this->settings['base_country'] == 'CH') {
                if($this->packing_method == 'per_item') {
                    $total_qty = $package_single[0]['quantity'];
                    $package_single[0]['quantity'] = 1;
                    for($i=0; $i<$total_qty; $i++) {
                        $dhl_requests[] = $this->get_service_request($order,$package_single);
                    }
                    
                }
                else {
                    foreach ($package_single as $key => $value) {
                        $package_piece = array();
                        $package_piece[0] = $value;
                        $dhl_requests[] = $this->get_service_request($order,$package_piece);
                    }
                }
            }
            else {
                $dhl_requests[] = $this->get_service_request($order,$package_single);
            }
        }
        
        update_post_meta( $post_id, '_wf_dhl_stored_packages', $package_data );

        $result = array();
        if ($dhl_requests) {
            try {
                $this->found_rates = array();
                foreach ( $dhl_requests as $key => $request ) {
                    $this->process_result($this->get_result($request));
                }           
            } catch (Exception $e) {
                return false;
            }
            update_post_meta( $post_id, '_wf_dhl_available_services', $this->found_rates );
        }

        //$this->wf_available_services($post_id);   
        if(isset($bulk_order_id) && !empty($bulk_order_id)){
            return;   
        }else{
            wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit#dhl_meta_box') );
            exit;
        }
    }

    function wf_dhl_generate_packages_rates($bulk_order_id)
    {
        if( !$this->wf_user_permission() ) {
            echo "You don't have admin privileges to view this page.";
            exit;
        }

        if(isset($bulk_order_id) && !empty($bulk_order_id)){
            $post_id = $bulk_order_id;    
        }else{
            $post_id    =   base64_decode($_GET['wf_dhl_generate_packages_rates']);
        }

        $dhl_requests = array();

        $lenth_arr = $width_arr = $height_arr = $weight_arr = $insurance_arr = array();
        if(isset($_GET['length'])){
            if($_GET['length'] != '' && $_GET['width'] != '' && $_GET['height'] != '' && $_GET['weight'] != ''){
                $lenth_arr      = explode(',',$_GET['length']);
                $width_arr      = explode(',',$_GET['width']);
                $height_arr     = explode(',',$_GET['height']);
                $weight_arr     = explode(',',$_GET['weight']);
            }
        }

        if(isset($_GET['insurance']) && !empty($_GET['insurance'])){
            $insurance_arr  = explode(',',$_GET['insurance']);
        }

        $order = $this->wf_load_order( $post_id );
        if ( !$order ) return;
        $orderid = elex_dhl_get_order_id($order);

        $get_stored_packages = get_post_meta( $post_id, '_wf_dhl_stored_packages',true );

        $i = 0;
        foreach ($get_stored_packages as $package) {
            if(!empty($package))
            {
                foreach($lenth_arr as $key => $lenth_arr_element){
                    $package_length = isset($package[$key]['Dimensions'])? $package[$key]['Dimensions']['Length']: '';
                    $package_width  = isset($package[$key]['Dimensions'])? $package[$key]['Dimensions']['Width']: '';
                    $package_height = isset($package[$key]['Dimensions'])? $package[$key]['Dimensions']['Height']: '';

                    $package[$key]['Dimensions']['Length'] = isset($lenth_arr[$key]) ? $lenth_arr[$key] : $package_length;
                    $package[$key]['Dimensions']['Width'] = isset($width_arr[$key]) ? $width_arr[$key] : $package_width ;
                    $package[$key]['Dimensions']['Height'] = isset($height_arr[$key]) ? $height_arr[$key] : $package_height;
                    $package[$key]['Weight']['Value'] = isset($weight_arr[$key]) ? round($weight_arr[$key], 3) : round($package[$key]['Weight']['Value'], 3);
                    if($insurance_arr){
                        $package[$key]['InsuredValue']['Amount'] = isset($insurance_arr[$key]) ? $insurance_arr[$key] : $package[$key]['InsuredValue']['Amount'];
                    }
                }

            }
            $package_data[] = $package;
            $dhl_requests[] = $this->get_service_request($order,$package);
        }

        foreach($package_data[0] as $package_data_element_key => $package_data_element_value){
            if(!isset($package_data_element_value['GroupNumber'])){
                $package_data[0][$package_data_element_key]['PackageType'] = 'custom_package';
            }
        }
        
        update_post_meta( $post_id, '_wf_dhl_stored_packages', $package_data );

        $result = array();
        if ($dhl_requests) {
            try {
                $this->found_rates = array();
                foreach ( $dhl_requests as $key => $request ) {
                    $this->process_result($this->get_result($request));
                }            
            } catch (Exception $e) {
                //$this->debug(print_r($e, true), 'error');
                return false;
            }
            update_post_meta( $post_id, '_wf_dhl_available_services', $this->found_rates );
        }

        //$this->wf_available_services($post_id);
        if(isset($bulk_order_id) && !empty($bulk_order_id)){
            return;   
        }else{
            wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit#dhl_meta_box') );
            exit;
        }
    }

    private function process_result($result = '') {
        $processed_ratecode = array();
        $rate_compain = '';
        $rate_cost = '';
        $remore_area_surcharge = 0;
        $insurance_charge = 0;

        $response = json_decode(json_encode($result), true);
        $response_services = isset($response['GetQuoteResponse']['BkgDetails'])? $response['GetQuoteResponse']['BkgDetails']['QtdShp']: array();
        if(isset($response_services['GlobalProductCode'])){
            $response_services_temp = $response_services;
            $response_services = array();
            $response_services[0] = $response_services_temp;
        }

        if ($response && !empty($response['GetQuoteResponse']['BkgDetails']['QtdShp'])) {
            foreach ($response_services as $response_service) {

                $rate_code = strval((string) $response_service['GlobalProductCode']);
                $rate_local_code = strval((string) (isset($response_service['LocalProductCode']) ? $response_service['LocalProductCode'] : ''));
                $remote_area_surcharge = 0;
                $insurance_charge = 0;

                $extra_shipping_charges = isset($response_service['QtdShpExChrg'])? $response_service['QtdShpExChrg']: array();

                if(!empty($extra_shipping_charges)){
                    foreach ($extra_shipping_charges as $extra_charge) {
                        if(isset($extra_charge['GlobalServiceName']) && $extra_charge['GlobalServiceName'] == 'REMOTE AREA DELIVERY'){
                            $remote_area_surcharge = $extra_charge['ChargeValue'];
                        }

                        if(isset($extra_charge['GlobalServiceName']) && ($extra_charge['GlobalServiceName'] == 'SHIPMENT INSURANCE' || $extra_charge['GlobalServiceName'] == 'SHIPMENT VALUE PROTECTION')){
                            $insurance_charge = $extra_charge['ChargeValue'];
                        }
                    }
                }

                if (!in_array($rate_code,$processed_ratecode)) {
                    $shipping_rates_source_currency = apply_filters('wf_dhl_shipping_rates_source_currency', get_woocommerce_currency(), $result, $this);
                    if (isset($response_service['CurrencyCode']) && (string) $response_service['CurrencyCode'] == $shipping_rates_source_currency) {
                        $this->conversion_rate = 1;
                        $rate_cost = floatval((string) $response_service['ShippingCharge']);
                        $rate_cost_weight = floatval((string) $response_service['WeightCharge']);
                    }else{
                        $charge_type = "shipping";
                        $rate_cost = floatval((string) $this->wf_get_cost_based_on_currency($response_service['QtdSInAdCur'], $response_service['ShippingCharge'], $charge_type));
                        $charge_type = "weight";
                        $rate_cost_weight = floatval((string) $this->wf_get_cost_based_on_currency($response_service['QtdSInAdCur'], $response_service['WeightCharge'], $charge_type));
                    }
                    $processed_ratecode[] = $rate_code;
                    $rate_id = $rate_code;
                    
                    $delivery_time = new DateInterval($response_service['DeliveryTime']);
                    $delivery_time = $delivery_time->format('%h:%I');
                    $delivery_date_time = $response_service['DeliveryDate'].' '.$delivery_time;
                    $rate_name = strval( (string) $response_service['ProductShortName'] );
                    if($rate_cost > 0) $this->prepare_rate($rate_code, $rate_id, $rate_name, $rate_cost, $delivery_date_time, $rate_cost_weight, $remote_area_surcharge, $insurance_charge);
                }
            }
            
        }
    }

    private function wf_get_cost_based_on_currency($qtdsinadcur, $default_charge,$charge_type) {

        $dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();
        $base_currency = $dhl_shipping_obj->wf_get_dhl_base_currency();
        
        if (!empty($qtdsinadcur)) {
            foreach ($qtdsinadcur as $multiple_currencies) {
                if($charge_type == "shipping"){
                    if (isset($multiple_currencies['CurrencyCode']) && (string) $multiple_currencies['CurrencyCode'] == $base_currency && !empty($multiple_currencies['TotalAmount']) && ($multiple_currencies['TotalAmount'] != 0)){
                        return  $multiple_currencies['TotalAmount'];   
                     }
                }else{
                    if (isset($multiple_currencies['CurrencyCode']) && (string) $multiple_currencies['CurrencyCode'] == $base_currency && !empty($multiple_currencies['WeightCharge']) && ($multiple_currencies['WeightCharge'] != 0)){
                        return $multiple_currencies['WeightCharge'];   
                     }
                }
            }
        }
        return $default_charge;
    }
    

    private function prepare_rate($rate_code, $rate_id, $rate_name, $rate_cost, $delivery_time, $rate_compain = '0', $remote_area_surcharge = 0, $insurance_charge = 0) {
        // Name adjustment
        if (!empty($this->custom_services[$rate_code]['name'])) {
            $rate_name = $this->custom_services[$rate_code]['name'];
        }

        // Cost adjustment %
        if (!empty($this->custom_services[$rate_code]['adjustment_percent'])) {
            $rate_cost = $rate_cost + ( $rate_cost * ( floatval($this->custom_services[$rate_code]['adjustment_percent']) / 100 ) );
        }
        // Cost adjustment
        if (!empty($this->custom_services[$rate_code]['adjustment'])) {
            $rate_cost = $rate_cost + floatval($this->custom_services[$rate_code]['adjustment']);
        }

        // Enabled check
        if($this->select_service_check_box)
        {
            if (isset($this->custom_services[$rate_code]) && empty($this->custom_services[$rate_code]['enabled'])) {
                return;
            }
        }


        // Merging
        if (isset($this->found_rates[$rate_id])) {
            $rate_cost = $rate_cost + $this->found_rates[$rate_id]['cost'];
            $packages = 1 + $this->found_rates[$rate_id]['packages'];
        } else {
            $packages = 1;
        }
        // Sort
        if (isset($this->custom_services[$rate_code]['order'])) {
            $sort = $this->custom_services[$rate_code]['order'];
        } else {
            $sort = 999;
        }

        $extra_charge = $rate_cost - $rate_compain;

        $this->found_rates[$rate_id] = array(
            'id' => $rate_id,
            'label' => $rate_name,
            'cost' => $rate_cost,
            'sort' => $sort,
            'packages' => $packages,
            'meta_data' => array('dhl_delivery_time'=>$delivery_time,'weight_charge'=>floatval($rate_compain),'extra_charge'=>$extra_charge, 'remote_area_surcharge' => $remote_area_surcharge, 'insurance' => $insurance_charge)
        );
    }

    private function get_result($request) {
        $result = wp_remote_post($this->service_url, array(
            'method' => 'POST',
            'timeout' => 70,
            'sslverify' => 0,
            //'headers'          => $this->wf_get_request_header('application/vnd.cpc.shipment-v7+xml','application/vnd.cpc.shipment-v7+xml'),
            'body' => $request
            )
        );

        if ( is_wp_error( $result ) ) {
            $error_message = $result->get_error_message();

        }
        elseif (is_array($result) && !empty($result['body'])) {
            $result = $result['body'];
        } else {
            $result = '';
        }

        libxml_use_internal_errors(true);
        if(is_string($result)){// if response contains services
            $xml = simplexml_load_string($result);
        }

        $shipmentErrorMessage = "";
        if ($xml) {
            return $xml;
        } else {
            return null;
        }
    }

    function get_service_request($order,$dhl_packages)
    {
        if ( ! class_exists( 'wf_dhl_woocommerce_shipping_admin_helper' ) )
        include_once('class-wf-dhl-woocommerce-shipping-admin-helper.php');

        $woo_dhl_wrapper = new wf_dhl_woocommerce_shipping_admin_helper();

        if (!class_exists('wf_dhl_woocommerce_shipping_method')) {
            include_once('class-wf-dhl-woocommerce-shipping.php');
        }

        if(!empty($this->shop_currency)){
            $this->conversion_rate      = apply_filters('wf_dhl_conversion_rate', $this->conversion_rate, $this->settings['dhl_currency_type'], $this->shop_currency);
        }

        $dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();

        $packages   =   $woo_dhl_wrapper->wf_get_package_from_order($order);
        $package_origin = array();

        $orderid = elex_dhl_get_order_id($order);
        $order_items = $order->get_items();
        $mailing_date = date('Y-m-d', time());
        $mailing_datetime = date('Y-m-d', time()) . 'T' . date('H:i:s', time());

        $origin_postcode_city = $this->wf_get_postcode_city($this->origin_country, $this->freight_shipper_city, $this->origin);
        $fetch_accountrates = $this->request_type == "ACCOUNT" ? "<PaymentAccountNumber>" . $this->account_number . "</PaymentAccountNumber>" : "";

        if(isset($this->settings['vendor_check']) && $this->settings['vendor_check'] === 'yes'){
            foreach($packages as $package_key => $package){
                foreach($package as $sub_package_key => $sub_package){
                    if($sub_package_key == 'origin'){
                        $package_origin = $sub_package;
                        $this->origin_country = $sub_package['country'];
                        $origin_postcode_city = $this->wf_get_postcode_city($this->origin_country, $sub_package['city'], $sub_package['postcode']);
                    }
                }
            }
        }

        $paymentCountryCode = isset($this->general_settings['dutypayment_country']) && !empty($this->general_settings['dutypayment_country'])? $this->general_settings['dutypayment_country']: $this->general_settings['base_country'];// obtaining payment country code from label settings
        $pieces = '';
        $currency = '';

        if ($dhl_packages) {
            foreach ($dhl_packages as $key => $parcel) {
                $pack_type = (isset($parcel['packtype']) && !empty($parcel['packtype']))? $parcel['packtype']: '';
                if($pack_type != 'FLY')
                {
                    $pack_type = 'BOX';
                }
                $index = $key + 1;
                if($this->packing_method == 'per_item'){
                    if(isset($parcel['quantity'])){
                        for($quantity = 0; $quantity < $parcel['quantity']; $quantity++){
                            $pieces .= '<Piece><PieceID>' . $index . '</PieceID>';
                            $pieces .= '<PackageTypeCode>'.$pack_type.'</PackageTypeCode>';
                            if( !empty($parcel['Dimensions']['Height']) && !empty($parcel['Dimensions']['Length']) && !empty($parcel['Dimensions']['Width']) ){
                                $pieces .= '<Height>' . round($parcel['Dimensions']['Height']) . '</Height>';
                                $pieces .= '<Depth>' . round($parcel['Dimensions']['Length']) . '</Depth>';
                                $pieces .= '<Width>' . round($parcel['Dimensions']['Width']) . '</Width>';
                            }
                            $package_total_weight   =(string) $parcel['Weight']['Value'];
                            $package_total_weight   = str_replace(',','.',$package_total_weight);
                            $pieces .= '<Weight>' . round($package_total_weight, 3) . '</Weight></Piece>';
                        }      
                    }   
                }else{
                    $pieces .= '<Piece><PieceID>' . $index . '</PieceID>';
                    $pieces .= '<PackageTypeCode>'.$pack_type.'</PackageTypeCode>';
                    if( !empty($parcel['Dimensions']['Height']) && !empty($parcel['Dimensions']['Length']) && !empty($parcel['Dimensions']['Width']) ){
                        $pieces .= '<Height>' . round($parcel['Dimensions']['Height']) . '</Height>';
                        $pieces .= '<Depth>' . round($parcel['Dimensions']['Length']) . '</Depth>';
                        $pieces .= '<Width>' . round($parcel['Dimensions']['Width']) . '</Width>';
                    }
                    $package_total_weight   =(string) $parcel['Weight']['Value'];
                    $package_total_weight   = str_replace(',','.',$package_total_weight);
                    $pieces .= '<Weight>' . round($package_total_weight, 3) . '</Weight></Piece>';
                }
            }
        }
        $total_value = $this->wf_get_package_total_value($dhl_packages);

        $total_insurance_value = '';

        $is_insurance = get_post_meta($orderid, 'wf_dhl_insurance', true);

        if ($this->settings['insure_contents'] == 'yes' && !empty($this->conversion_rate) && ($is_insurance == 'yes'))
        {
            $currency = $this->settings['dhl_currency_type'];
        }else{
            $currency = get_woocommerce_currency();
        }

        if($is_insurance == 'yes'){
            $total_insurance_value = $total_value * $this->insure_converstion_rate;
        }

        $insurance_details = !empty($total_insurance_value) ? "<InsuredValue>{$total_insurance_value}</InsuredValue><InsuredCurrency>{$this->insure_currency}</InsuredCurrency>" : "";
        $additional_insurance_details = (!empty($total_insurance_value)  && ($this->conversion_rate || $this->insure_converstion_rate)) ? "<QtdShp><QtdShpExChrg><SpecialServiceType>II</SpecialServiceType><LocalSpecialServiceType>XCH</LocalSpecialServiceType></QtdShpExChrg></QtdShp>" : ""; //insurance type

        $destination_country_code = elex_dhl_get_order_shipping_country($order);

        $destination_city = strtoupper(elex_dhl_get_order_shipping_city($order));

        /*  According to WooCommrce The Canary Islands is a country, but according to DHL it is a part of Spain.
            If the postcodes belong to Canary Islands, we are providing country code as 'ES'
        */
        $canary_islands_postcodes = array( 35100, 35500, 35240, 35220, 35570, 35520, 35560, 35561, 35571, 35628, 35640, 35629, 35600, 35637, 35290, 35018, 35011, 35017, 35508, 35510, 35572, 35530 );

        $destination_postcode = elex_dhl_get_order_shipping_postcode($order);

        $is_dutiable = ($destination_country_code == $this->origin_country || wf_dhl_is_eu_country($this->origin_country, $destination_country_code)) ? "N" : "Y";
        if(isset($this->settings['dutypayment_type']) && $this->settings['dutypayment_type'] == '') {
            $is_dutiable = 'N';
        }
        $order_items_total = get_option('current_order_items_total_express_dhl_elex');
        $order_dutiable_amount = $total_value != 0? $total_value : $order_items_total;
        $dutiable_content = $is_dutiable == "Y" ? "<Dutiable><DeclaredCurrency>{$currency}</DeclaredCurrency><DeclaredValue>{$order_dutiable_amount}</DeclaredValue></Dutiable>" : "";
        $destination_postcode_city = $this->wf_get_postcode_city($destination_country_code, $destination_city, $destination_postcode);

        /*There are different country codes for same country from WooCommerce and DHL. Here we are obtaining country code which is mapped to DHL for both source and destination countries*/
        $shipping_country_code = $dhl_shipping_obj->get_country_codes_mapped_for_dhl($this->origin_country);
        $destination_country_code = $dhl_shipping_obj->get_country_codes_mapped_for_dhl($destination_country_code);
        $destination_country_code = in_array($destination_postcode, $canary_islands_postcodes)? 'IC': $destination_country_code;
        $message_reference_num = elex_dhl_generate_random_message_reference();
        
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
    <GetQuote>
        <Request>
            <ServiceHeader>
                <MessageTime>{$mailing_datetime}</MessageTime>
                <MessageReference>{$message_reference_num}</MessageReference>
                <SiteID>{$this->site_id}</SiteID>
                <Password>{$this->site_password}</Password>
            </ServiceHeader>
        </Request>
        <From>
            <CountryCode>{$shipping_country_code}</CountryCode>
            {$origin_postcode_city}
        </From>
        <BkgDetails>
            <PaymentCountryCode>{$paymentCountryCode}</PaymentCountryCode>
            <Date>{$mailing_date}</Date>
            <ReadyTime>PT10H21M</ReadyTime>
            <DimensionUnit>{$this->quoteapi_dimension_unit}</DimensionUnit>
            <WeightUnit>{$this->quoteapi_weight_unit}</WeightUnit>
            <Pieces>
                {$pieces}
            </Pieces>
            {$fetch_accountrates}
            <IsDutiable>{$is_dutiable}</IsDutiable>
            <NetworkTypeCode>AL</NetworkTypeCode>
            {$additional_insurance_details}
            {$insurance_details}
        </BkgDetails>
        <To>
            <CountryCode>{$destination_country_code}</CountryCode>
            {$destination_postcode_city}
        </To>
        {$dutiable_content}
    </GetQuote>
</p:DCTRequest>
XML;
        return $xmlRequest;
    }

    private function wf_get_package_total_value($dhl_packages) {
        $total_value = 0;
        if ($dhl_packages) {
            foreach ($dhl_packages as $key => $parcel) {
                $parcel['GroupPackageCount'] = isset($parcel['GroupPackageCount'])? $parcel['GroupPackageCount']: 1;
                $parcel['packageValue']['Amount'] = isset($parcel['packageValue'])? $parcel['packageValue']['Amount']: 0;
                $total_value += (Int)$parcel['packageValue']['Amount'] * (Int)$parcel['GroupPackageCount'];
            }
        }
        return $total_value;
    }

    private function wf_get_postcode_city($country, $city, $postcode) {
        $no_postcode_country = array('AE', 'AF', 'AG', 'AI', 'AL', 'AN', 'AO', 'AW', 'BB', 'BF', 'BH', 'BI', 'BJ', 'BM', 'BO', 'BS', 'BT', 'BW', 'BZ', 'CD', 'CF', 'CG', 'CI', 'CK',
        'CL', 'CM', 'CR', 'CV', 'DJ', 'DM', 'DO', 'EC', 'EG', 'ER', 'ET', 'FJ', 'FK', 'GA', 'GD', 'GH', 'GI', 'GM', 'GN', 'GQ', 'GT', 'GW', 'GY', 'HK', 'HN', 'HT', 'IE', 'IQ', 'IR',
        'JM', 'JO', 'KE', 'KH', 'KI', 'KM', 'KN', 'KP', 'KW', 'KY', 'LA', 'LB', 'LC', 'LK', 'LR', 'LS', 'LY', 'ML', 'MM', 'MO', 'MR', 'MS', 'MT', 'MU', 'MW', 'MZ', 'NA', 'NE', 'NG', 'NI',
        'NP', 'NR', 'NU', 'OM', 'PA', 'PE', 'PF', 'PY', 'QA', 'RW', 'SA', 'SB', 'SC', 'SD', 'SL', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SY', 'TC', 'TD', 'TG', 'TL', 'TO', 'TT', 'TV', 'TZ',
        'UG', 'UY', 'VC', 'VE', 'VG', 'VN', 'VU', 'WS', 'XA', 'XB', 'XC', 'XE', 'XL', 'XM', 'XN', 'XS', 'YE', 'ZM', 'ZW');

        $postcode_city = !in_array( $country, $no_postcode_country ) ? $postcode_city = "<Postalcode>{$postcode}</Postalcode>" : '';
        if( !empty($city) ){
            $postcode_city .= "<City>".htmlspecialchars($city)."</City>";
        }
        return $postcode_city;
    }

    function wf_dhl_process_pickup_packages()
    {
        if( !$this->wf_user_permission() ) {
            echo "You don't have admin privileges to view this page.";
            exit;
        }

        $post_id    =   $_GET['wf_dhl_process_pickup_packages'];

        $order = $this->wf_load_order( $post_id );
        if ( !$order )
        {
            return;
        }
        if ( ! class_exists( 'wf_dhl_woocommerce_shipping_admin_helper' ) )
            include_once('class-wf-dhl-woocommerce-shipping-admin-helper.php');

            $woo_dhl_wrapper = new wf_dhl_woocommerce_shipping_admin_helper();

            $woo_dhl_wrapper->wf_pickup_request_handler($order);

            wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit') );
        exit;
    }

    function wf_dhl_process_return_packages()
    {
        if( !$this->wf_user_permission() ) {
        echo "You don't have admin privileges to view this page.";
        exit;
        }
        $post_id    =   base64_decode($_GET['wf_dhl_process_return_packages']);

        $order = $this->wf_load_order( $post_id );
        if ( !$order ) return;

        update_post_meta( $post_id, '_wf_dhl_process_return_shipment', 'yes' );

        wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit') );
        exit;
    }

    function dhl_product_choose_return_shipment()
    {
        if( !$this->wf_user_permission() ) {
        echo "You don't have admin privileges to view this page.";
        exit;
        }

        $post_id    =   base64_decode($_GET['dhl_product_choose_return_shipment']);

        $order = $this->wf_load_order( $post_id );
        if ( !$order ) return;

        delete_post_meta($post_id, '_wf_dhl_stored_return_products');
        delete_post_meta( $post_id, '_wf_dhl_stored_return_packages');
        wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit') );
        exit;
    }

    function wf_dhl_generate_return_packages()
    {
        if( !$this->wf_user_permission() ) {
            echo "You don't have admin privileges to view this page.";
            exit;
        }

        $post_id    =   base64_decode($_GET['wf_dhl_generate_return_packages']);

        $order = $this->wf_load_order( $post_id );
        if ( !$order ) return;


        $selected_items = '';

        if(isset($_GET['dhl_express_manual_return_products']) && $_GET['dhl_express_manual_return_products'] !='null')
        {
            $check_item = $_GET['dhl_express_manual_return_products'];
            if(!empty($check_item))
            {
                $data = explode(',',$check_item);
                $selected_items = array();
                if(!empty($data))
                {
                    foreach ( $data as $k => $v )
                    {
                        $selected_items[] = explode( '|', $v );
                    }
                }
                update_post_meta($post_id, '_wf_dhl_stored_return_products', $check_item );
            }else{
                update_post_meta($post_id, '_wf_dhl_stored_return_products', '' );
            }
        }else{
            update_post_meta($post_id, '_wf_dhl_stored_return_products', '' );
        }


        if ( ! class_exists( 'wf_dhl_woocommerce_shipping_admin_helper' ) )
        include_once('class-wf-dhl-woocommerce-shipping-admin-helper.php');

        $woo_dhl_wrapper = new wf_dhl_woocommerce_shipping_admin_helper();
        $packages   =   $woo_dhl_wrapper->wf_get_return_package_return_from_order($order,$selected_items);
        if(!empty($packages))
        {
            foreach ($packages as $key => $package) {
                $package_data[] = $woo_dhl_wrapper->get_dhl_packages($package);
            }
        }
        update_post_meta( $post_id, '_wf_dhl_stored_return_packages', $package_data );
        wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit') );
        exit;
    }

    
    function bulk_label_admin_notices_dhl_express() {
        global $post_type, $pagenow;

        if(!isset($_REQUEST['ids']))
        {
            return;
        }

        if( $pagenow == 'edit.php' && $post_type == 'shop_order' && isset($_REQUEST['bulk_label_dhl_express']) ) {
            if(isset($_REQUEST['ids']) && !empty($_REQUEST['ids'])){
                $order_ids = explode( ",", $_REQUEST['ids'] );
            }

            $failed_ids_str = '';
            $success_ids_str = '';
            $already_exist_arr = array();
            if(isset($_REQUEST['already_exist']) && !empty($_REQUEST['already_exist'])){
                $already_exist_arr = explode( ',', $_REQUEST['already_exist'] );
            }

            if(!empty($order_ids)){
                foreach ($order_ids as $key => $id) {
                    $dhl_shipment_err   = get_post_meta( $id, 'wf_woo_dhl_shipmentErrorMessage',true );
                    $dhl_shipment_err  .= get_post_meta( $id, 'wf_woo_dhl_shipmentReturnErrorMessage',true);
                    if( !empty($dhl_shipment_err) ){
                        $failed_ids_str .= $id.', ';
                    }else if( !in_array( $id, $already_exist_arr ) ){
                       $success_ids_str .= $id.', ';
                    }
                }
            }

            $failed_ids_str = rtrim($failed_ids_str,', ');
            $success_ids_str = rtrim($success_ids_str,', ');

            // Showing notices if the shipment id/s are not there to create return shipment
            if( isset( $_REQUEST['no_normal_shipment'] ) && $_REQUEST['no_normal_shipment'] != '' ){
                $message_string = 'Unable to find Shipment ids for the order(s) '.$_REQUEST['no_normal_shipment'];
                $message_string = rtrim($message_string, ',');
                echo '<div class="notice notice-error"><p>' . __( $message_string, 'wf-shipping-dhl') . '</p></div>';
                return;
            }

            if( isset( $_REQUEST['already_exist'] ) && $_REQUEST['already_exist'] != '' ){
                echo '<div class="notice notice-success"><p>' . __('Shipment already exist for following order(s) '.$_REQUEST['already_exist'] , 'wf-shipping-dhl') . '</p></div>';
            }

            if( $success_ids_str != '' ){
                echo '<div class="updated"><p>' . __('Successfully created shipment for following order(s) '.$success_ids_str, 'wf-shipping-dhl') . '</p></div>';
            }

            // Showing notices if the customer has not set default shipment service
            if(isset($_REQUEST['default_shipment_service']) && !empty($_REQUEST['default_shipment_service'])){
                if(!empty($_REQUEST['default_shipment_service'])){
                    echo '<div class="error"><p>' . __('Default Shipment Service is not set for order/s '.$_REQUEST['default_shipment_service'] , 'wf-shipping-dhl') . '</p></div>';
                    delete_option('default_shipment_service');
                    delete_option('orders_with_no_default_shipment_service_exp_dhl_elex');
                    return;
                }
            }

            if( $failed_ids_str != '' ){
                echo '<div class="error"><p>' . __('Create shipment is failed for following order(s) '.$failed_ids_str, 'wf-shipping-dhl') . '</p></div>';
            }
        }
    }

    public function wf_auto_label_generate_order_dhl_express( $post_id )
    {
        $this->debug = false;
        update_option('create_bulk_orders_shipment', true);
        $order = $this->wf_load_order( $post_id );
        $this->wf_create_shipment($order);
        delete_option('create_bulk_orders_shipment');
        if(isset($this->settings['elex_dhl_auto_return_label'])){
            if($this->settings['elex_dhl_auto_return_label'] == 'enable'){
                update_option('create_bulk_return_orders_shipment', true);
                update_option('auto_return_label_generate',true);
                $this->wf_create_return_shipment($order);
                delete_option('return_create_shipment');
                delete_option('auto_return_label_generate');
            }
        }
    }

    public function wf_orders_bulk_action_dhl_express()
    {
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();
        $sendback = '';

        if ($action == 'create_shipment_dhl') {
            //forcefully turn off debug mode, otherwise it will die and cause to break the loop.
            $this->debug = false;
            $label_exist_for = '';
            if(isset($_REQUEST['post']) && !empty($_REQUEST['post']))
            {
                foreach($_REQUEST['post'] as $post_id) {
                    $order = $this->wf_load_order( $post_id );
                    if (!$order) 
                    return;
                    $orderid = elex_dhl_get_order_id($order);

                    $shipmentIds = get_post_meta($orderid, 'wf_woo_dhl_shipmentId', false);
                    if ( !empty($shipmentIds) ) {
                        $label_exist_for .= $orderid.', ';
                    }
                    else{
                        update_option('create_bulk_orders_shipment', true);
                        $this->wf_create_shipment($order);
                    }
                }
                delete_option('create_bulk_orders_shipment');

                // Checking is default shipment service activated
                if(get_option('default_shipment_service') == 'yes'){
                    $sendback = add_query_arg( array(
                        'bulk_label_dhl_express' => 1, 
                        'ids' => join(',', $_REQUEST['post']),
                        'already_exist' =>rtrim( $label_exist_for, ', ' )
                        ), admin_url( 'edit.php?post_type=shop_order' ) 
                    );
                } 
                else{
                    $orders_ids_with_no_default_shipment_service = get_option('orders_with_no_default_shipment_service_exp_dhl_elex');
                    $orders_ids_with_no_default_shipment_service = rtrim($orders_ids_with_no_default_shipment_service, ',');
                    $sendback = add_query_arg( array(
                        'bulk_label_dhl_express' => 1, 
                        'ids' => join(',', $_REQUEST['post']),
                        'already_exist' =>rtrim( $label_exist_for, ', ' ),
                        'default_shipment_service' => $orders_ids_with_no_default_shipment_service
                        ), admin_url( 'edit.php?post_type=shop_order' ) 
                    );
                }
                
                wp_redirect($sendback);
                exit();
            }else{
                return;
            }
        }

        if ($action == 'create_shipment_return_dhl') {
            //forcefully turn off debug mode, otherwise it will die and cause to break the loop.
            $this->debug = false;
            $label_exist_for = '';
            $no_normal_labels_for = '';
            if(isset($_REQUEST['post']) && !empty($_REQUEST['post']))
            {
                foreach($_REQUEST['post'] as $post_id) {
                    $order = $this->wf_load_order( $post_id );
                    if (!$order) 
                    return;
                    $orderid = elex_dhl_get_order_id($order);

                    $shipmentIds = get_post_meta($orderid, 'wf_woo_dhl_return_shipmentId', false);
                    if ( !empty($shipmentIds) ) {
                        $label_exist_for .= $orderid.', ';
                    }
                    else{
                        $shipmentIds = get_post_meta($orderid, 'wf_woo_dhl_shipmentId', false);
                        if(!empty($shipmentIds)){
                            update_option('create_bulk_return_orders_shipment', true);
                            $this->wf_create_return_shipment($order);
                        }else{
                            $no_normal_labels_for .= $orderid.', ';
                        }
                    }
                }

                update_option('create_bulk_return_orders_shipment', false);
                delete_option('return_create_shipment');

                // Checking is default shipment service activated
                if(get_option('default_shipment_service') == 'yes'){
                    $sendback = add_query_arg( array(
                        'bulk_label_dhl_express' => 1, 
                        'ids' => join(',', $_REQUEST['post']),
                        'already_exist' =>rtrim( $label_exist_for, ', ' ),
                        'no_normal_shipment' => rtrim($no_normal_labels_for, ',')
                        ), admin_url( 'edit.php?post_type=shop_order' ) 
                    );
                } 
                else{
                    $orders_ids_with_no_default_shipment_service = get_option('orders_with_no_default_shipment_service_exp_dhl_elex');
                    $orders_ids_with_no_default_shipment_service = rtrim($orders_ids_with_no_default_shipment_service, ',');
                    $sendback = add_query_arg( array(
                        'bulk_label_dhl_express' => 1, 
                        'ids' => join(',', $_REQUEST['post']),
                        'already_exist' =>rtrim( $label_exist_for, ', ' ),
                        'default_shipment_service' => $orders_ids_with_no_default_shipment_service
                        ), admin_url( 'edit.php?post_type=shop_order' ) 
                    );
                }

                wp_redirect($sendback);
                exit();
            }
            else
            {
                return;
            }
        }
    }

    public function wf_load_order($orderId){
        if (!class_exists('WC_Order')) {
            return false;
        }
        return new WC_Order($orderId);      
    }

    private function wf_user_permission(){

        // Check if user has rights to generate invoices
        $current_user = wp_get_current_user();
        $user_ok = false;
        if ($current_user instanceof WP_User) {
            if (in_array('administrator', $current_user->roles) || in_array('shop_manager', $current_user->roles)) {
                $user_ok = true;
            }
        }
        if(ELEX_DHL_EXPRESS_AUTO_LABEL_GENERATE_ADDON_WOOCOMMERCE_EXTENSION){
            $user_ok = true;
        }
        return $user_ok;
    }

    public function wf_dhl_createshipment(){
        $user_ok = $this->wf_user_permission();
        if (!$user_ok)          
        return;

        $order = $this->wf_load_order($_GET['wf_dhl_createshipment']);
        if (!$order) 
        return;

        $this->wf_create_shipment($order);

        if ( $this->debug ) {
            //dont redirect when debug is printed
            wp_die();
        }
        else{           
            wp_redirect(admin_url('/post.php?post='.$_GET['wf_dhl_createshipment'].'&action=edit&'.WF_Tracking_Admin_DHLExpress::get_admin_notification_message_var()));
            exit;
        }
    }

    public function wf_dhl_create_return_shipment(){
        $user_ok = $this->wf_user_permission();
        if (!$user_ok)          
        return;

        $order = $this->wf_load_order($_GET['wf_dhl_create_return_shipment']);
        if (!$order) 
        return;


        $this->wf_create_return_shipment($order);

        if ( $this->debug ) {
            //dont redirect when debug is printed
            die();
        }
        else{           
            wp_redirect(admin_url('/post.php?post='.$_GET['wf_dhl_create_return_shipment'].'&action=edit&'.WF_Tracking_Admin_DHLExpress::get_admin_notification_message_var()));
            exit;
        }
    }


    public function wf_dhl_viewlabel(){
        $view_label = isset($_GET['wf_dhl_viewlabel']) ? $_GET['wf_dhl_viewlabel'] : ''; 
        $shipmentDetails = explode('|', base64_decode($view_label));

        if (count($shipmentDetails) != 2) {
            exit;
        }

        $is_request_from_cart_side = get_option('cart_side_print_label_request_express_dhl_elex', false);
        if($is_request_from_cart_side){
            $this->dir_download = 'attachment';
        }
        delete_option('cart_side_print_label_request_express_dhl_elex');

        $shipmentId = $shipmentDetails[0]; 
        $post_id = $shipmentDetails[1]; 
        $shipping_label = get_post_meta($post_id, 'wf_woo_dhl_shippingLabel_'.$shipmentId, true);
        header('Content-Type: application/'.$this->image_type);
        header('Content-disposition: '.$this->dir_download.'; filename="ShipmentArtifact-' . $shipmentId . '.'.$this->image_type.'"');
        print(base64_decode($shipping_label));
        exit;
    }

    public function wf_dhl_viewreturnlabel(){
        $view_return_label = isset($_GET['wf_dhl_viewreturnlabel']) ? $_GET['wf_dhl_viewreturnlabel'] : ''; 

        $shipmentDetails = explode('|', base64_decode($view_return_label));

        if (count($shipmentDetails) != 2) {
            exit;
        }

        $shipmentId = $shipmentDetails[0]; 
        $post_id = $shipmentDetails[1]; 
        $shipping_label = get_post_meta($post_id, 'wf_woo_dhl_return_shippingLabel_'.$shipmentId, true);
        header('Content-Type: application/'.$this->image_type);
        header('Content-disposition: '.$this->dir_download.'; filename="ShipmentArtifactReturn-' . $shipmentId . '.'.$this->image_type.'"');
        print(base64_decode($shipping_label)); 
        exit;
    }

    public function generate_proforma_invoice(){
        $order_id = $_GET['generate_proforma_invoice_dhl_elex'];
        if(!empty($order_id)){
            update_option("proforma_invoice_order_id_dhl_elex", $order_id);
            $order = wc_get_order($order_id);

            $woo_dhl_wrapper = new wf_dhl_woocommerce_shipping_admin_helper();

            $packages = array();
            $packages = array_values($woo_dhl_wrapper->wf_get_package_from_order($order));

            $dhl_packages = get_post_meta( $order_id, '_wf_dhl_stored_packages', true );

            if (!$dhl_packages && !empty($packages)) {
                foreach ($packages as $key => $package) {
                    $dhl_packages[] = $this->get_dhl_packages($package);
                }
            }

            $packages_contents = $packages[0];
            $shipper = $woo_dhl_wrapper->get_shipper_address($packages_contents);
            $destination_info = $woo_dhl_wrapper->get_destination_specific_data($packages_contents);
            $toaddress = $woo_dhl_wrapper->get_to_address( $order, $packages_contents, $destination_info );

            $proforma_invoice = $woo_dhl_wrapper->generate_commercial_invoice($order_id, $dhl_packages, $shipper, $toaddress, $document_type = 'proforma');
            if(!empty($proforma_invoice)){
                update_option("is_elex_dhl_express_proforma_invoice_generated_".$order_id, true);
                update_post_meta($order_id, 'wf_woo_dhl_shipping_proformaInvoice', $proforma_invoice);
            }
            wp_redirect( admin_url( '/post.php?post='.$order_id.'&action=edit') );
            exit;
        }
    }

    public function print_proforma_invoice(){
        $order_id = $_GET['print_proforma_invoice_dhl_elex'];
        if(!empty($order_id)){
            $proforma_invoice = get_post_meta($order_id, 'wf_woo_dhl_shipping_proformaInvoice', true);
            if(!empty($proforma_invoice)){
                header('Content-Type: application/pdf');
                header('Content-disposition: inline; filename="ProformaInvoice-' . $order_id . 'pdf"');
                print(base64_decode($proforma_invoice)); 
                exit;
            }else{
                update_option("is_elex_dhl_express_proforma_invoice_generated_".$order_id, false);
                wp_redirect( admin_url( '/post.php?post='.$order_id.'&action=edit') );
                exit;
            }
        }
    }

    public function delete_proforma_invoice(){
        $order_id = $_GET['delete_proforma_invoice_dhl_elex'];
        delete_post_meta($order_id, 'wf_woo_dhl_shipping_proformaInvoice', true);
        update_option("is_elex_dhl_express_proforma_invoice_generated_".$order_id, false);
        wp_redirect( admin_url( '/post.php?post='.$order_id.'&action=edit') );
        exit;
    }

    public function wf_dhl_view_commercial_invoice(){
        $view_invoice = isset($_GET['wf_dhl_view_commercial_invoice']) ? $_GET['wf_dhl_view_commercial_invoice'] : ''; 

        $invoiceDetails = explode('|', base64_decode($view_invoice));

        if (count($invoiceDetails) != 2) {
            exit;
        }

        $image_type =   'pdf'; //commercial invoice generated in pdf only
        $shipmentId = $invoiceDetails[0];
        $post_id = $invoiceDetails[1]; 
        $commercial_invoice = get_post_meta($post_id, 'wf_woo_dhl_shipping_commercialInvoice_'.$shipmentId, true);
        header('Content-Type: application/'.$image_type);
        header('Content-disposition: '.$this->dir_download.'; filename="CommercialInvoice-' . $shipmentId . '.'.$image_type.'"');
        print(base64_decode($commercial_invoice)); 
        exit;
    }

    public function wf_dhl_view_return_commercial_invoice(){
        $view_return_invoice = isset($_GET['wf_dhl_view_return_commercial_invoice']) ? $_GET['wf_dhl_view_return_commercial_invoice'] : ''; 
        $invoiceDetails = explode('|', base64_decode($view_return_invoice));

        if (count($invoiceDetails) != 2) {
            exit;
        }
        $image_type =   'pdf'; //commercial invoice generated in pdf only
        $shipmentId = $invoiceDetails[0]; 
        $post_id = $invoiceDetails[1]; 
        $commercial_invoice = get_post_meta($post_id, 'wf_woo_dhl_shipping_return_commercialInvoice_'.$shipmentId, true);
        header('Content-Type: application/'.$image_type);
        header('Content-disposition: '.$this->dir_download.'; filename="ReturnCommercialInvoice-' . $shipmentId . '.'.$image_type.'"');
        print(base64_decode($commercial_invoice)); 
        exit;
    }

    private function wf_is_service_valid_for_country($order,$service_code){
        return true; 
    }

    private function wf_get_shipping_service($order,$retrive_from_order = false, $for_return_shipment = false){
        if($retrive_from_order == true){
            $orderid = elex_dhl_get_order_id($order);
            if($for_return_shipment){
                $service_code = get_post_meta( $orderid, 'wf_woo_dhl_return_service_code', true);
            }else{
                $service_code = get_post_meta( $orderid, 'wf_woo_dhl_service_code', true);
            }
            if(!empty($service_code)) 
            return $service_code;
        }

        if(!empty($_GET['dhl_express_shipping_service'])){    
            return $_GET['dhl_express_shipping_service'];           
        }

        if(!empty($_GET['dhl_express_return_shipping_service'])){           
            return $_GET['dhl_express_return_shipping_service'];            
        }

        //TODO: Take the first shipping method. It doesnt work if you have item wise shipping method
        $shipping_methods = $order->get_shipping_methods();
        $is_international = ( elex_dhl_get_order_shipping_country($order) == $this->origin_country ) ? false : true;
        $shipping_services = '';

        if (!empty($shipping_methods) ) {
            $shipping_method = array_shift($shipping_methods);

            $shipping_output = elex_dhl_get_order_shipping_service($shipping_method);
            if( strpos( $shipping_output[0], WF_DHL_ID ) !== false )
            {
                $shipping_services = str_replace(WF_DHL_ID.':', '', $shipping_output[0]);
            }else{
                $shipping_services = $shipping_method['method_title'];
            }

            if(empty($shipping_services))
            {
                if( $is_international ){
                    if(!empty( $this->default_international_service) )
                    return $this->default_international_service;
                }
                elseif( !empty($this->default_domestic_service) && $this->default_domestic_service !='none'  ){
                    return $this->default_domestic_service;
                }
            }
        }
        else
        {
            if( $is_international ){
                if(!empty( $this->default_international_service) )
                return $this->default_international_service;
            }
            elseif( !empty($this->default_domestic_service) && $this->default_domestic_service !='none'  ){
                return $this->default_domestic_service;
            }
        }
        return $shipping_services;
    }

    public function wf_create_shipment($order){
        if ( ! class_exists( 'wf_dhl_woocommerce_shipping_admin_helper' ) )
        include_once('class-wf-dhl-woocommerce-shipping-admin-helper.php');

        $woo_dhl_wrapper = new wf_dhl_woocommerce_shipping_admin_helper();
        $serviceCode = $this->wf_get_shipping_service($order,false);
        $orderid = elex_dhl_get_order_id($order);
        $bulk_create_shipment = get_option('create_bulk_orders_shipment');
        add_option("dhl_shipping_service_selected", "no");
        if($bulk_create_shipment){
            $this->wf_dhl_generate_packages($orderid);
            $this->wf_dhl_generate_packages_rates($orderid);

            $available_service_data = get_post_meta($orderid,'_wf_dhl_available_services',true);
            $selected_service = $this->wf_get_shipping_service($order,true);
            foreach($available_service_data as $available_service_datum){
                foreach($this->custom_services as $custom_service_key => $custom_service){
                    if($custom_service_key == $available_service_datum['id']){
                        if($available_service_datum['label'] == $selected_service){
                            $serviceCode = $available_service_datum['id'];
                            update_option("dhl_shipping_service_selected", "yes");
                        }
                    }
                }
            }
        }

        $woo_dhl_wrapper->print_label($order,$serviceCode);
    }

    public function wf_create_return_shipment($order){      
        if ( ! class_exists( 'wf_dhl_woocommerce_shipping_admin_helper' ) )
        include_once('class-wf-dhl-woocommerce-shipping-admin-helper.php');

        $woo_dhl_wrapper = new wf_dhl_woocommerce_shipping_admin_helper();
        if(isset($_GET['dhl_express_return_shipping_service']) && !empty($_GET['dhl_express_return_shipping_service'])){
            $serviceCode = $_GET['dhl_express_return_shipping_service'];
        }else{
            $serviceCode = $this->wf_get_shipping_service($order,false, true);
        }

        $orderid = elex_dhl_get_order_id($order);
        $bulk_create_return_shipment = get_option('create_bulk_return_orders_shipment');
        add_option("dhl_return_shipping_service_selected", "no");
        if($bulk_create_return_shipment){
            $this->wf_dhl_generate_packages($orderid);
            $this->wf_dhl_generate_packages_rates($orderid);

            $available_service_data = get_post_meta($orderid,'_wf_dhl_available_services',true);
            $selected_service = $this->wf_get_shipping_service($order,true, true);
            foreach($available_service_data as $available_service_datum){
                foreach($this->custom_services as $custom_service_key => $custom_service){
                    if($custom_service_key == $selected_service){
                        $serviceCode = $selected_service;
                    }else if($custom_service_key == $available_service_datum['id']){
                            if($available_service_datum['label'] == $selected_service){
                                $serviceCode = $available_service_datum['id'];
                                update_option("dhl_return_shipping_service_selected", "yes");
                            }
                    }
                }
            }
        }

        $woo_dhl_wrapper->print_return_label($order,$serviceCode );
    }

    public function wf_add_dhl_metabox(){
        global $post;

        if (!$post) {
            return;
        }

        if ( in_array( $post->post_type, array('shop_order') )) {
            $order = $this->wf_load_order($post->ID);
            if (!$order) 
            return;

            add_meta_box('wf_dhl_metabox', __('DHL Express', 'wf-shipping-dhl'), array($this, 'wf_dhl_metabox_content'), 'shop_order', 'advanced', 'default');
        }
    }

    public function wf_dhl_metabox_content(){
        
        global $post;
        global $woocommerce;

        if (!$post) {
            return;
        }

        $order = $this->wf_load_order($post->ID);
        $this->order = $order;

        if (!$order) 
        return;

        if (!class_exists('wf_dhl_woocommerce_shipping_method')) {
            include_once('class-wf-dhl-woocommerce-shipping.php');
        }
        
        $woo_dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();
        $items_in_the_order = $order->get_items();

        $orderid = elex_dhl_get_order_id($order);
        update_option("current_order_id", $orderid);

        $this->shop_currency   = $woo_dhl_shipping_obj->wf_get_currency_based_on_country_code(WC()->countries->get_base_country());

        $order_insurance = 'no';
        if($this->insure_contents && $this->insure_contents_chk){
            $order_insurance = get_post_meta($orderid, 'wf_dhl_insurance', true);
            update_post_meta($orderid, 'wf_dhl_insurance', $order_insurance);
        }else if($this->insure_contents && !$this->insure_contents_chk){
            $order_insurance = 'yes';
            update_post_meta($orderid, 'wf_dhl_insurance', $order_insurance);
        }else{
            update_post_meta($orderid, 'wf_dhl_insurance', $order_insurance);
        }

        $customer_insurance = $order_insurance;

        $shipping_method_data = $order->get_shipping_methods();

        if(!empty($shipping_method_data))
        {
            $shipping_method_data = array_shift($shipping_method_data);

            $shipping_method_meta_data = wf_get_order_shipping_method_meta_data($shipping_method_data);

            foreach($shipping_method_meta_data as $each_meta_datum)
            {
                $getting_shipping_data = (array)$each_meta_datum;
                if(isset($getting_shipping_data['key']) && $getting_shipping_data['key'] == 'insurance' && $getting_shipping_data['value'] == 'no')
                {
                    $customer_insurance = 'no';
                }
            }
        }

        $shipmentIds = get_post_meta($orderid, 'wf_woo_dhl_shipmentId', false);
        $return_shipmentIds = get_post_meta($orderid, 'wf_woo_dhl_return_shipmentId', false);

        $shipmentErrorMessage = get_post_meta($orderid, 'wf_woo_dhl_shipmentErrorMessage',true);
        $shipmentReturnErrorMessage = get_post_meta($orderid, 'wf_woo_dhl_shipmentReturnErrorMessage',true);

        //Only Display error message if the process is not complete. If the Invoice link available then Error Message is unnecessary
        if(!empty($shipmentErrorMessage))
        {
            echo '<div class="error"><p>' . sprintf( __( 'DHL Express Create Shipment Error:%s', 'wf-shipping-dhl' ), $shipmentErrorMessage) . '</p></div>';
        }
        if(!empty($shipmentReturnErrorMessage))
        {
            echo '<div class="error"><p>' . sprintf( __( 'DHL Express Return Shipment Error:%s', 'wf-shipping-dhl' ), $shipmentReturnErrorMessage) . '</p></div>';
        }

        $receiver_duty_payment_types = array('DAP' => __('Delivered At Place (DAP)', 'wf-shipping-dhl'), 'DDU' => __('Delivered Duty Unpaid (DDU)', 'wf-shipping-dhl'),'DDP' => __('Delivered Duty Paid (DDP)', 'wf-shipping-dhl'), 'EXW' => __('Ex Works (EXW)', 'wf-shipping-dhl'), 'FCA' => __('Free Carrier (FCA)', 'wf-shipping-dhl'), 'CPT' => __('Carriage Paid To (CPT)', 'wf-shipping-dhl'), 'CIP' => __('Carriage and Insurance Paid to (CIP)', 'wf-shipping-dhl'), 'DAT' => __('Delivered At Terminal (DAT)', 'wf-shipping-dhl'),'DAP' => __('Delivered At Place (DAP)', 'wf-shipping-dhl'), 'FAS' => __('Free Alongside Ship (FAS)', 'wf-shipping-dhl'), 'FOB' => __('Free on Board (FOB)', 'wf-shipping-dhl'), 'CFR' => __('Cost and Freight (CFR)', 'wf-shipping-dhl'), 'CIF' => __('Cost, Insurance & Freight (CIF)', 'wf-shipping-dhl'));

        /*Showing Error notice for packages more than 99 in an order*/
        $error_packages_more_than_99 = get_option('Error packages more than 99');
        if(!empty($error_packages_more_than_99)){
            echo '<div class="error"><p>' . sprintf( __( $error_packages_more_than_99, 'wf-shipping-dhl' ), $shipmentReturnErrorMessage) . '</p></div>';
            update_option('Error packages more than 99','');
        }

        echo '<ul id="dhl_meta_box">';
        $selected_service = $this->wf_get_shipping_service($order,true);
        $dhl_india = 'false';
        if (!empty($shipmentIds)) {

            if(!empty($selected_service) && !empty($this->services[$selected_service]) ){
                echo "<li>Shipping service: <strong>".$this->services[$selected_service]."</strong></li>";
                $xa_order_to_country = elex_dhl_get_order_shipping_country($order);
                $xa_plt_allowed_country = array('AL','AS','AD','AO','AI','AG','AR','AW','AU','CX','NF','AT','BS','BH','BB','BE','BZ','BJ',
                                'BM','BT','BO','AN','BA','BW','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','CD','CN','CO','KM',
                                'CG','CK','HR','CU','AN','CY','CZ','PM','CD','DK','DJ','DM','DO','EC','TL','ER','EE','ET','FK','FO',
                                'FJ','WF','FI','FR','GF','GA','GM','DE','GH','GI','GR','GL','GD','GP','GU','GQ','GY','GN','HT','HU',
                                'HK','IS','IL','IT','IE','VA','JM','JP','JO','JE','KE','KI','KP','KR','LV','LS','LR','LI','LT','LU',
                                'LA','MO','MG','MV','MW','MY','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MC','MN','MS','MZ','MM',
                                'MK','ME','NA','NP','NL','NC','NG','NZ','KN','NE','NU','NO','NR','OM','PA','PW','PG','PY','PN','PL',
                                'PT','PR','RE','RO','RW','WS','SM','ST','SA','SN','SC','SL','SG','SK','SI','SB','SO','ZA','SS','ES',
                                'LK','SD','LC','SR','SJ','SZ','SE','CH','RS','SH','BL','SX','VC','TW','TZ','TH','TG','TK','TO','TT',
                                'TR','TC','TV','UG','AE','US','UK','UY','VU','VE','VG','VI','EH');

                if($this->plt && !in_array($xa_order_to_country, $xa_plt_allowed_country))
                {
                    echo '<li style="background:yellow;float:right;padding:2px;">PLT ( Paperless Trade ) is Not Available for the destination. Please print the Commercial Invoice and physically attach them to your shipments. </li>';
                }
            }

            include_once('class-wf-tracking-admin.php');
            $tracking_obj = new WF_Tracking_Admin_DHLExpress();
            foreach($shipmentIds as $index_num => $shipmentId) {
                echo '<li><strong>Shipment #:</strong> <a href="http://www.dhl.com/en/express/tracking.html?AWB='.$shipmentId.'&brand=DHL" target="_blank">'. $shipmentId.'</a> ';
                if($this->add_trackingpin_shipmentid === 'yes')
                {
                    $tracking_array = $tracking_obj->get_tracking_info($post->ID,$shipmentId);
                    echo '<span style="">';
                    //$last_checkpoint_status = '';
                    $full_check_point_data = '';

                    if($tracking_array['status'] !='success')
                    {
                        //$last_checkpoint_status = ' <small> (No Shipments Found : Test Mode)</small>';
                        $full_check_point_data .='<li>
                                <div class="wf-dhl-direction-r">
                                    <div class="wf-dhl-wf-dhl-flag-wrapper">
                                        <span class="wf-dhl-flag">Test Mode</span>
                                        <span class="wf-dhl-wf-dhl-time-wrapper"><span class="wf-dhl-time">Failure</span></span>
                                    </div>
                                    <div class="wf-dhl-desc">No Shipments Found</div>
                                </div>
                            </li>';
                    }else
                    {
                        if(isset($tracking_array['shipment']))
                        {
                                foreach ($tracking_array['shipment'] as $key => $value) {
                                //  $last_checkpoint_status = empty($value['desc']) ? ' <small>(Shipment information received)</small>' : ' <small>('.$value['desc'].')</small>';
                                $full_check_point_data .='<li>
                                        <div class="wf-dhl-direction-r">
                                            <div class="wf-dhl-wf-dhl-flag-wrapper">
                                                <span class="wf-dhl-flag">'.$value['date'].'</span>
                                                <span class="wf-dhl-wf-dhl-time-wrapper"><span class="wf-dhl-time">'.$value['time'].'</span></span>
                                            </div>
                                            <div class="wf-dhl-desc">'.$value['desc'].'</div>
                                        </div>
                                    </li>';
                                }

                        }else{
                            //$last_checkpoint_status = ' <small>(Shipment information received)</small>';
                            $full_check_point_data .='<li>
                                    <div class="wf-dhl-direction-r">
                                        <div class="wf-dhl-wf-dhl-flag-wrapper">
                                            <span class="wf-dhl-flag">Initial</span>
                                            <span class="wf-dhl-wf-dhl-time-wrapper"><span class="wf-dhl-time">Shipment Received</span></span>
                                        </div>
                                        <div class="wf-dhl-desc">Shipment information received</div>
                                    </div>
                                </li>';
                        }
                    }
                //echo $last_checkpoint_status;
                include_once(WF_DHL_PAKET_EXPRESS_ROOT_PATH. '/dhl_express/resources/css/tracking-back-end.php');
                echo ' <a href="#wf_dhl_metabox1'.$shipmentId.'" style="text-decoration:none;color:#ba0c2f;" id="wf_shipment_details_but'.$shipmentId.'" > <span class="dashicons dashicons-search"></span> </a></span>';

                ?>
                <!-- The wf-dhl-model -->
                <div id="wf_shipment_data_popup" class="wf-dhl-model">
                    <!-- wf-dhl-model content -->
                    <div class="wf-dhl-model-content" style="height:70%;overflow-x: scroll;">
                        <span class="wf-dhl-close">&times;</span>
                        <!-- The wf-dhl-wf-dhl-timeline -->
                        <ul class="wf-dhl-wf-dhl-timeline">
                            <?php echo $full_check_point_data; ?>
                        </ul>
                    </div>
                </div>

                <script>
                // Get the wf-dhl-model
                var model = document.getElementById('wf_shipment_data_popup');

                // Get the button that opens the wf-dhl-model
                var btn = document.getElementById("wf_shipment_details_but<?php echo $shipmentId; ?>");

                // Get the <span> element that wf-dhl-closes the wf-dhl-model
                var span = document.getElementsByClassName("wf-dhl-close")[0];

                // When the user clicks the button, open the wf-dhl-model 
                btn.onclick = function() {
                    model.style.display = "block";
                }

                // When the user clicks on <span> (x), wf-dhl-close the wf-dhl-model
                span.onclick = function() {
                    model.style.display = "none";
                }

                // When the user clicks anywhere outside of the wf-dhl-model, wf-dhl-close it
                window.onclick = function(event) {
                    if (event.target == model) {
                        model.style.display = "none";
                    }
                }
                </script>
                <?php } ?>
                <table style='width:100%'>
                    <tbody>
                        <tr>
                            <td style='width:85%;'>
                    <?php
                        $packageDetailForTheshipment = get_post_meta($orderid, 'wf_woo_dhl_packageDetails_'.$shipmentId, true);
                        if(!empty($packageDetailForTheshipment)){
                            foreach($packageDetailForTheshipment as $dimentionValue){
                                echo $dimentionValue;
                            }
                    ?>
                            </td>
                            <td style='width:15%;text-align:right;vertical-align:bottom;'>
                                <img src='<?php echo WF_DHL_PAKET_PATH.'/dhl_express/resources/images/box.png'; ?>' style='width: 60px;padding-right: 5px;'>
                                <div style='width:50%:float:left;'>

                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php
                }
                $stored_pickup_shipment =   get_post_meta( $post->ID, '_wf_dhl_pickup_shipment', array() );
                $stored_pickup_shipment =   array_shift($stored_pickup_shipment);
                if(isset($stored_pickup_shipment[0])) {
                    $stored_pickup_shipment = $stored_pickup_shipment[$index_num];
                }
                $stored_pickup_shipment_error   =   get_post_meta( $post->ID, '_wf_dhl_pickup_shipment_error', '' );
                if(!empty($stored_pickup_shipment))
                {
                    $confirmation_number = isset($stored_pickup_shipment['pickup_confirmation_number']) ? " <b>Confirmation Number:</b> " . (string) $stored_pickup_shipment['pickup_confirmation_number'] : '';  
                    $pickup_date = ($stored_pickup_shipment['next_pickup_date'] != NULL) ? " <b>Next Possible Pickup Date:</b> " . (string) $stored_pickup_shipment['next_pickup_date'] : '';  
                    echo "<div> <b>Pickup Booked</b> " .$confirmation_number . $pickup_date ."</div>";
                }
                else if(!empty($stored_pickup_shipment_error))
                {
                    echo '<div class="error"><p>DHL Express Pickup Failed : ' . __($stored_pickup_shipment_error[0], 'wf-shipping-dhl') . '</p></div>';
                }
                echo '<hr style="border-color:#c9c9c9"></li>';
                $shipping_label = get_post_meta($post->ID, 'wf_woo_dhl_shippingLabel_'.$shipmentId, true);
                if(!empty($shipping_label)){
                    $download_url = admin_url('/post.php?wf_dhl_viewlabel='.base64_encode($shipmentId.'|'.$post->ID));?>
                    <a class="button tips button-primary" target="_blank"  href="<?php echo $download_url; ?>" data-tip="<?php _e('Shipment Label', 'wf-shipping-dhl'); ?>"><?php _e('Shipment Label', 'wf-shipping-dhl'); ?></a>
                    <a class="button tips"  href="<?php echo admin_url('/post.php?wf_dhl_delete_label='.$post->ID); ?>" onclick="return confirm('Are you sure?');" data-tip="<?php _e('Reset Shipment', 'wf-shipping-dhl'); ?>"><?php _e('Reset Shipment', 'wf-shipping-dhl'); ?></a>
                <?php 
                }
                $commercial_invoice = get_post_meta($post->ID, 'wf_woo_dhl_shipping_commercialInvoice_'.$shipmentId, true);
                if(!empty($commercial_invoice)){
                    $commercial_invoice_download_url = admin_url('/post.php?wf_dhl_view_commercial_invoice='.base64_encode($shipmentId.'|'.$post->ID));?>
                    <a class="button tips button-primary"  href="<?php echo $commercial_invoice_download_url; ?>" target="_blank" data-tip="<?php _e('Commercial Invoice', 'wf-shipping-dhl'); ?>"><?php _e('Commercial Invoice', 'wf-shipping-dhl'); ?></a>
                <?php 
                }

                
            }

            // Shipment Pickup
                if($this->pickup_enable)
                {
                    if(empty($stored_pickup_shipment))
                    {
                    ?>
                         <a class="button tips dhl_pickup_generate_packages button-primary" style="text-align: center;float:right;margin-left: 2px;" href="<?php echo admin_url( '/?wf_dhl_process_pickup_packages='.$post->ID ); ?>" data-tip="<?php _e( 'Create Pickup Request', 'wf-shipping-dhl' ); ?>"><?php _e( 'Create Pickup Request', 'wf-shipping-dhl' ); ?></a> 
                        <script type="text/javascript">
                        jQuery("a.dhl_pickup_generate_packages").on("click", function() {
                            location.href = this.href;
                        });
                        </script>

                    <?php
                    }
                }

            // Return Label New development
            if(!empty($this->return_label_key) && $this->return_label_key === 'yes')
            {
                if(empty($return_shipmentIds))
                {

                    $stored_return_shipment =   get_post_meta( $post->ID, '_wf_dhl_process_return_shipment', true );

                    if(empty($stored_return_shipment))
                    {
                        ?>
                        <a class="button tips dhl_generate_packages button-primary" style="text-align: center;float:right;" href="<?php echo admin_url( '/?wf_dhl_process_return_packages='.base64_encode($post->ID) ); ?>" data-tip="<?php _e( 'Process Return Shipment', 'wf-shipping-dhl' ); ?>"><?php _e( 'Process Return Shipment', 'wf-shipping-dhl' ); ?></a> <hr style="border-color:#0074a2">
                        <script type="text/javascript">
                        jQuery("a.dhl_process_return_packages").on("click", function() {
                            location.href = this.href;
                        });
                        </script>
                        <?php
                    }
                    else
                    {
                        $stored_return_packages =   get_post_meta( $post->ID, '_wf_dhl_stored_return_packages', true );
                        $generate_return_url = admin_url('/post.php?wf_dhl_create_return_shipment='.$post->ID);
                        if(empty($stored_return_packages))
                        {
                            $items = $order->get_items();
                            echo '<hr ><b>Select Products to be Return</b><br/>';
                            echo '<table id="dhl_slect_qty_table" style="margin-top: 5px;margin-bottom: 5px;box-shadow: 1px 1px 5px lightgrey;width:100%;" class="wf-shipment-package-table">';
                                echo '<tr>';
                                echo '<th > </th>';
                                echo '<th style="padding:4px;text-align:left">Product Name</th>';
                                echo '<th style="padding:4px;text-align:left">Qty</th>';
                                echo '</tr>';
                                if(!empty($items))
                                {
                                    foreach ($items as $item_id => $orderItem) {
                                        $item_id        = $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];

                                        $product_name = $orderItem['name'];
                                        echo '<tr><td style="padding-left: 3px"><input type="checkbox" style="padding-left:2px;" name="wf_dhl_item_id" id="wf_dhl_item_id" value="'.$item_id.'"></td><td><small>'.$product_name.'</small></td><td><input type="number" id="dhl_return_product_ids" min="1" max="'.$orderItem['quantity'].'" name="dhl_return_product_ids" style="width:50px;" value='.$orderItem['quantity'].'></td></tr>';
                                    }
                                }
                                echo '</table>';
                            ?>
                            <a class="button tips dhl_generate_return_packages button-primary" id="" style="text-align: center;" href="<?php echo admin_url( '/?wf_dhl_generate_return_packages='.base64_encode($post->ID) ); ?>" data-tip="<?php _e( 'Generate Return Packages', 'wf-shipping-dhl' ); ?>"><?php _e( 'Generate Return Packages', 'wf-shipping-dhl' ); ?></a><hr style="border-color:#0074a2">
                                <!-- <script type="text/javascript">
                                jQuery("a.dhl_generate_return_packages").on("click", function() {
                                    location.href = this.href;
                                });
                            </script> -->
                            <script>
                                jQuery("a.dhl_generate_return_packages").one("click", function() {
                                    var values = new Array();
                                    jQuery(this).click(function () { return false; });

                                        jQuery('#dhl_slect_qty_table').find('tr').each(function () {
                                            var row = jQuery(this);

                                            if (row.find('input[type="checkbox"]').is(':checked')) {
                                                values.push([row.find('input[name="wf_dhl_item_id"]').val()] + '|' +row.find('input[name="dhl_return_product_ids"]').val());
                                            }
                                        });


                                       location.href = this.href 
                                        + '&dhl_express_manual_return_products=' + values.join(",");
                                    return false;           
                                });
                            </script>   
                            <?php
                        }else
                        {

                            echo '<hr><span style="font-weight:bold;">'.__( 'Return Package(s)' , 'wf-shipping-dhl').': </span> ';
                                            $xa_order_to_country = elex_dhl_get_order_shipping_country($order);
                                            $xa_plt_allowed_country = array('AL','AS','AD','AO','AI','AG','AR','AW','AU','CX','NF','AT','BS','BH','BB','BE','BZ','BJ','BM','BT','BO','AN','BA','BW','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','CD','CN','CO','KM','CG','CK','HR','CU','AN','CY','CZ','PM','CD','DK','DJ','DM','DO','EC','TL','ER','EE','ET','FK','FO','FJ','WF','FI','FR','GF','GA','GM','DE','GH','GI','GR','GL','GD','GP','GU','GQ','GY','GN','HT','HU','HK','IS','IL','IT','IE','VA','JM','JP','JO','JE','KE','KI','KP','KR','LV','LS','LR','LI','LT','LU','LA','MO','MG','MV','MW','MY','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MC','MN','MS','MZ','MM','MK','ME','NA','NP','NL','NC','NG','NZ','KN','NE','NU','NO','NR','OM','PA','PW','PG','PY','PN','PL','PT','PR','RE','RO','RW','WS','SM','ST','SA','SN','SC','SL','SG','SK','SI','SB','SO','ZA','SS','ES','LK','SD','LC','SR','SJ','SZ','SE','CH','RS','SH','BL','SX','VC','TW','TZ','TH','TG','TK','TO','TT','TR','TC','TV','UG','AE','UK','US','UY','VU','VE','VG','VI','EH');

                            if($this->plt && in_array($xa_order_to_country, $xa_plt_allowed_country))
                            {
                                echo ' <a href="'.admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_dhl_shipping&subtab=labels' ) .'" target="_blank" style="color:#ba0c2f;"><label style="background:yellow;float:right;padding:2px;">Paper Less Trade (PLT) is enabled.</label></a>';
                            }
                            echo '<table id="wf_dhl_return_package_list" class="wf-shipment-package-table">';                   
                                echo '<tr>';
                                    echo '<th style="padding:6px;text-align:left;">'.__('Item(s)/Package(s)', 'wf-shipping-dhl').' <span class="woocommerce-help-tip" data-tip="The item / Package details will be shown in this column. Each package will be associated with individual items if the packaging option is chosen as Pack items individually.','wf-shipping-dhl"></span></th>';
                                    echo '<th style="padding:6px;text-align:left;">'.__('Weight', 'wf-shipping-dhl').' ('.$this->weight_unit.')</th>';
                                    echo '<th style="text-align:left;padding:left:6px;">'.__('Length', 'wf-shipping-dhl').'</th>';
                                    echo '<th style="text-align:left;padding:left:6px;">'.__('Width', 'wf-shipping-dhl').' </th>';
                                    echo '<th style="text-align:left;padding:left:6px;">'.__('Height', 'wf-shipping-dhl').' </th>';
                                    echo '<th>&nbsp;</th>';
                                echo '</tr>';
                                if( empty($stored_return_packages[0]) ){
                                    $stored_return_packages[0][0] = $this->get_dhl_dummy_package();
                                }
                                if(!empty($stored_return_packages))
                                {
                                    foreach($stored_return_packages as $package_group_key   =>  $package_group){
                                        if( !empty($package_group) && is_array($package_group) ){ //package group may empty if boxpacking and product have no dimensions 
                                            foreach($package_group as $stored_package_key   =>  $stored_package){
                                                $product_details = '';
                                                if(isset($stored_package['packed_products'])) {
                                                        foreach ($stored_package['packed_products'] as $key => $value) {

                                                            $product_id = $value->get_id();
                                                            $product_title = get_the_title($product_id);
                                                            $product_composite_title = get_post_meta($product_id, '_composite_title_express_dhl_elex', true);
                                                            if($this->is_woocommerce_composite_products_installed && !empty($product_composite_title)){
                                                                $product_details .= $product_composite_title.', ';
                                                            }else{
                                                                $product_details .= $product_title.', ';
                                                            }
                                                        }
                                                }
                                                    $package_name = strlen($product_details) > 30 ? substr($product_details,0,30)."..." : $product_details;
                                                    $product_details = rtrim( $product_details,', ');
                                                    $product_details = '<a href="#" title="'.$product_details.'" style="text-decoration: unset;color: black;cursor: default;">'. $package_name .'</a>';                                           
                                                $dimensions =   $this->get_dimension_from_package($stored_package);
                                                if(is_array($dimensions)){
                                                    ?>
                                                    <tr>
                                                        <td style="width:25%;padding:5px;border-radius:5px;margin-left:4px;"><small><?php echo $product_details; ?></small></td>
                                                        <td><input type="text" id="dhl_return_manual_weight" name="dhl_return_manual_weight[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo round($dimensions['Weight'], 3);?>" /> <b><?php echo $this->weight_unit; ?></b></td>     
                                                        <td><input type="text" id="dhl_return_manual_length" name="dhl_return_manual_length[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo round($dimensions['Length']);?>" /> <b><?php echo $this->dim_unit; ?></b></td>
                                                        <td><input type="text" id="dhl_return_manual_width" name="dhl_return_manual_width[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo round($dimensions['Width']);?>" /> <b><?php echo $this->dim_unit; ?></b></td>
                                                        <td><input type="text" id="dhl_return_manual_height" name="dhl_return_manual_height[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo round($dimensions['Height']);?>" /> <b><?php echo $this->dim_unit; ?></b></td>
                                                        <td><a class="wf_dhl_return_package_line_remove tips" data-tip="<?php _e( 'Delete Package', 'wf-shipping-dhl' ); ?>">&#x26D4;</a></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                        }
                                    }
                                }
                        echo '</table>';
                        echo '<a class="wf-action-button wf-add-button" style="font-size: 12px;" id="wf_dhl_add_reurn_package">Add Package</a>';

                        echo '</li>';
                            if($this->show_front_end_shipping_method === 'yes' && !empty($selected_service)){
                                echo '<li style="padding: 5px"><bold>Return Shipping Service:</bold></li>';
                                echo '<li><input value="'.$selected_service.'" id="dhl_express_manual_return_service" style="display: none"><bold style="border:solid 1px grey; padding:5px">'.$this->services[$selected_service].'</bold></li>';
                            }else{
                                echo '<li>choose Return service:<br><select class="wc-enhanced-select" style="width:40%;padding:5px" id="dhl_express_manual_return_service">';
                                foreach($this->custom_services as $service_code => $service){
                                    if(isset($service['enabled']) && $service['enabled'] == true && $this->wf_is_service_valid_for_country($order,$service_code) == true){
                                        echo '<option value="'.$service_code.'" ' . selected($selected_service,$service_code,false) . ' >'.$this->services[$service_code].'</option>';
                                    }
                                }
                                echo '</select></li>';
                            }
                        echo '<li>';
                        echo '<li></br>';
                        ?>
                        <table>
                            <?php if(($this->origin_country != $this->order->get_shipping_country()) && ($this->settings['dutypayment_type'] == 'R')){?>
                            <tr>
                                <td><?php _e('Duty Payment (Recipient)', 'wf-shipping-dhl');?></td>
                                <td>
                                    <select id="shipment_incoterm_express_dhl_elex" name="shipment_incoterm_express_dhl_elex" style="width: 100%"> <?php
                                        foreach($receiver_duty_payment_types as $receiver_duty_payment_type_key => $receiver_duty_payment_type_value){
                                            if($this->settings['receiver_duty_payment_type'] == $receiver_duty_payment_type_key){
                                                echo '<option value="'.$receiver_duty_payment_type_key.'" selected>'.$receiver_duty_payment_type_value.'</option>';    
                                            }else{
                                                echo '<option value="'.$receiver_duty_payment_type_key.'">'.$receiver_duty_payment_type_value.'</option>';
                                            }
                                        }?>
                                    </select>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td><?php _e('Shipment Content', 'wf-shipping-dhl');?></td><td>
                                    <?php $label_contents_text = get_post_meta($orderid, 'shipment_content_express_dhl_elex', true);
                                    if(!empty($label_contents_text)) $this->label_contents_text = $label_contents_text;?>
                                    <input type="text" placeholder="Enter Shipment Contents to ship" id="wf_dhl_shpment_content" name="wf_dhl_shpment_content" style="width: 100%" value="<?php echo $this->label_contents_text; ?>">  
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e('Shipper Comments', 'wf-shipping-dhl');?></td>
                                <td>
                                    <?php $label_comments_text = get_post_meta($post->ID, 'shipment_comments_express_dhl_elex', true);
                                    if(!empty($label_comments_text)) $this->label_comments_text = $label_comments_text;?>
                                    <input type="text" placeholder="Enter Comments for the shipment" id="shipment_comments_express_dhl_elex" name="shipment_comments_express_dhl_elex" style="width: 100%" value="<?php echo $this->label_comments_text; ?>"> 
                                </td>
                            </tr>
                        </table>
                        <?php
                        echo '<hr style="border-color:#c9c9c9"></li>';
                        ?>

                        <li>
                            <a class="button tips onclickdisable dhl_create_return_shipment button-primary" style="text-align: center;" href="<?php echo $generate_return_url; ?>" data-tip="<?php _e('Create Return Shipment', 'wf-shipping-dhl'); ?>"><?php _e('Create Return Shipment', 'wf-shipping-dhl'); ?></a>
                            <a class="button tips onclickdisable dhl_product_choose_return_shipment" style="text-align: center;" href="<?php echo admin_url( '/?dhl_product_choose_return_shipment='.base64_encode($post->ID) ); ?>" data-tip="<?php _e('Back', 'wf-shipping-dhl'); ?>"><?php _e('Back', 'wf-shipping-dhl'); ?></a>
                        </li>

                        <script type="text/javascript">
                            jQuery(document).ready(function(){
                                jQuery('#wf_dhl_add_reurn_package').on("click", function(){
                                    var new_row = '<tr>';
                                        new_row     += '<td></td>';
                                        new_row     += '<td><input type="text" id="dhl_return_manual_weight" name="dhl_return_manual_weight[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->weight_unit; ?></b></td>';
                                        new_row     += '<td><input type="text" id="dhl_return_manual_length" name="dhl_return_manual_length[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->dim_unit; ?></b></td>';                             
                                        new_row     += '<td><input type="text" id="dhl_return_manual_width" name="dhl_return_manual_width[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->dim_unit; ?></b></td>';
                                        new_row     += '<td><input type="text" id="dhl_return_manual_height" name="dhl_return_manual_height[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->dim_unit; ?></b></td>';
                                        new_row     += '<td><a class="wf_dhl_return_package_line_remove tips" data-tip="Delete Package">&#x26D4;</a></td>';
                                    new_row     += '</tr>';

                                    jQuery('#wf_dhl_return_package_list tr:last').after(new_row);
                                });

                                jQuery(document).on('click', '.wf_dhl_return_package_line_remove', function(){
                                    if(confirm('Are you sure you want to remove this package?'))
                                    {
                                        jQuery(this).closest('tr').remove();
                                    }
                                });
                            });

                            jQuery("a.dhl_create_return_shipment").one("click", function() {

                                jQuery(this).click(function () { return false; });
                                    var manual_weight_arr   =   jQuery("input[id='dhl_return_manual_weight']").map(function(){return jQuery(this).val();}).get();
                                    var manual_weight       =   JSON.stringify(manual_weight_arr);

                                    var manual_height_arr   =   jQuery("input[id='dhl_return_manual_height']").map(function(){return jQuery(this).val();}).get();
                                    var manual_height       =   JSON.stringify(manual_height_arr);

                                    var manual_width_arr    =   jQuery("input[id='dhl_return_manual_width']").map(function(){return jQuery(this).val();}).get();
                                    var manual_width        =   JSON.stringify(manual_width_arr);

                                    var manual_length_arr   =   jQuery("input[id='dhl_return_manual_length']").map(function(){return jQuery(this).val();}).get();
                                    var manual_length       =   JSON.stringify(manual_length_arr);

                                   location.href = this.href + '&weight=' + manual_weight +
                                    '&length=' + manual_length
                                    + '&width=' + manual_width
                                    + '&height=' + manual_height
                                    + '&shipment_content=' + jQuery('#wf_dhl_shpment_content').val()
                                    + '&shipment_comments=' + jQuery('#shipment_comments_express_dhl_elex').val()
                                    + '&dhl_express_return_shipping_service=' + jQuery('#dhl_express_manual_return_service').val();
                                return false;           
                            });
                        </script>   
                        <?php
                        }   
                    }

                }else{
                    $selected_return_service_code = get_post_meta($orderid,'wf_woo_dhl_return_service_code',true);
                    if(!empty($selected_return_service_code) && !empty($this->services[$selected_return_service_code]) )
                    echo "<hr><li>Return Shipping service: <strong>".$this->services[$selected_return_service_code]."</strong></li>";       

                    foreach($return_shipmentIds as $shipmentId) {
                        echo '<li><strong>Return Shipment #:</strong> <a href="http://www.dhl.com/en/express/tracking.html?AWB='.$shipmentId.'&brand=DHL" target="_blank" >'. $shipmentId.'</a>';
                        if($this->add_trackingpin_shipmentid === 'yes')
                        {
                            $tracking_array = $tracking_obj->get_tracking_info($post->ID,$shipmentId);
                            echo '<span style="">';
                            //$last_checkpoint_status = '';
                            $full_check_point_data = '';

                            if($tracking_array['status'] !='success')
                            {
                                //$last_checkpoint_status = ' <small> (No Shipments Found : Test Mode)</small>';
                                $full_check_point_data .='<li>
                                            <div class="wf-dhl-direction-r">
                                                <div class="wf-dhl-wf-dhl-flag-wrapper">
                                                    <span class="wf-dhl-flag">Test Mode</span>
                                                    <span class="wf-dhl-wf-dhl-time-wrapper"><span class="wf-dhl-time">Faliure</span></span>
                                                </div>
                                                <div class="wf-dhl-desc">No Shipments Found</div>
                                            </div>
                                        </li>';
                            }else
                            {

                                if(isset($tracking_array['shipment']))
                                {
                                    foreach ($tracking_array['shipment'] as $key => $value) {
                                        //$last_checkpoint_status = empty($value['desc']) ? ' <small>(Shipment information received)</small>' : ' <small>('.$value['desc'].')</small>';
                                        $full_check_point_data .='<li>
                                                <div class="wf-dhl-direction-r">
                                                    <div class="wf-dhl-wf-dhl-flag-wrapper">
                                                        <span class="wf-dhl-flag">'.$value['date'].'</span>
                                                        <span class="wf-dhl-wf-dhl-time-wrapper"><span class="wf-dhl-time">'.$value['time'].'</span></span>
                                                    </div>
                                                    <div class="wf-dhl-desc">'.$value['desc'].'</div>
                                                </div>
                                            </li>';
                                    }

                                }else{
                                    //$last_checkpoint_status = ' <small>(Shipment information received)</small>';
                                    $full_check_point_data .='<li>
                                            <div class="wf-dhl-direction-r">
                                                <div class="wf-dhl-wf-dhl-flag-wrapper">
                                                    <span class="wf-dhl-flag">Initial</span>
                                                    <span class="wf-dhl-wf-dhl-time-wrapper"><span class="wf-dhl-time">Shipment Received</span></span>
                                                </div>
                                                <div class="wf-dhl-desc">Shipment information received</div>
                                            </div>
                                        </li>';
                                }
                            }

                            echo ' <a href="#wf_dhl_metabox1'.$shipmentId.'"  style="text-decoration:none;color:#ba0c2f;"  id="wf_shipment_data_return_but'.$shipmentId.'" > <span class="dashicons dashicons-search"></span> </a></span>';

                            ?>

                            <!-- The wf-dhl-model -->
                            <div id="wf_shipment_data_return_popup" class="wf-dhl-model">

                              <!-- wf-dhl-model content -->
                                <div class="wf-dhl-model-content" style="height:70%;overflow-x: scroll;">
                                    <span class="wf-dhl-return-close">&times;</span>

                                    <!-- The wf-dhl-wf-dhl-timeline -->

                                    <ul class="wf-dhl-wf-dhl-timeline">
                                        <?php echo $full_check_point_data; ?>

                                    </ul>

                                </div>

                            </div>

                            <script>
                                // Get the wf-dhl-model
                                var returnmodel = document.getElementById('wf_shipment_data_return_popup');

                                // Get the button that opens the wf-dhl-model
                                var returnbtn = document.getElementById("wf_shipment_data_return_but<?php echo $shipmentId; ?>");

                                // Get the <span> element that wf-dhl-closes the wf-dhl-model
                                var returnspan = document.getElementsByClassName("wf-dhl-return-close")[0];

                                // When the user clicks the button, open the wf-dhl-model 
                                returnbtn.onclick = function() {
                                    returnmodel.style.display = "block";
                                }

                                // When the user clicks on <span> (x), wf-dhl-close the wf-dhl-model
                                returnspan.onclick = function() {
                                    returnmodel.style.display = "none";
                                }

                                // When the user clicks anywhere outside of the wf-dhl-model, wf-dhl-close it
                                window.onclick = function(event) {
                                    if (event.target == returnmodel) {
                                        returnmodel.style.display = "none";
                                    }
                                }
                            </script>
                <?php   } ?>
                        <table style='width:100%' class=''>
                            <tbody>
                                <tr>
                                    <td style='width:80%;'>
                                        <?php
                                        $packageDetailForTheshipment = get_post_meta($orderid, 'wf_woo_dhl_return_packageDetails_'.$shipmentId, true);
                                        if(!empty($packageDetailForTheshipment)){
                                            foreach($packageDetailForTheshipment as $dimentionValue){
                                                echo $dimentionValue;
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td style='width:15%;text-align:right;vertical-align:bottom;'>
                                        <img src='<?php echo WF_DHL_PAKET_PATH.'/dhl_express/resources/images/box.png'; ?>' style='width: 60px;padding-right: 5px;'>
                                        <div style='width:50%:float:left;'>

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php
                        $shipping_label = get_post_meta($post->ID, 'wf_woo_dhl_return_shippingLabel_'.$shipmentId, true);
                        if(!empty($shipping_label)){
                            echo '<hr style="border-color:#c9c9c9"></li>';
                            $download_return_url = admin_url('/post.php?wf_dhl_viewreturnlabel='.base64_encode($shipmentId.'|'.$post->ID));?>
                            <a class="button tips button-primary" target="_blank"  href="<?php echo $download_return_url; ?>" data-tip="<?php _e('Return Label', 'wf-shipping-dhl'); ?>"><?php _e('Return Label', 'wf-shipping-dhl'); ?></a>
                            <a class="button tips"  href="<?php echo admin_url('/post.php?wf_dhl_delete_return_label='.$post->ID); ?>" onclick="return confirm('Are you sure?');" data-tip="<?php _e('Reset Return Label', 'wf-shipping-dhl'); ?>"><?php _e('Reset Return Label', 'wf-shipping-dhl'); ?></a>
                            <?php 
                        }

                        $commercial_invoice = get_post_meta($post->ID, 'wf_woo_dhl_shipping_return_commercialInvoice_'.$shipmentId, true);
                        if(!empty($commercial_invoice)){
                            $commercial_invoice_download_url = admin_url('/post.php?wf_dhl_view_return_commercial_invoice='.base64_encode($shipmentId.'|'.$post->ID));?>
                            <a class="button tips button-primary"  href="<?php echo $commercial_invoice_download_url; ?>" target="_blank" data-tip="<?php _e('Commercial Invoice', 'wf-shipping-dhl'); ?>"><?php _e('Commercial Invoice', 'wf-shipping-dhl'); ?></a>
                            <?php 
                        }
                        echo '<hr style="border-color:#c9c9c9"></li>';
                    } 
                }   
            }                           
        }
        else {

            $stored_packages = get_post_meta( $post->ID, '_wf_dhl_stored_packages', true );

            if(empty($stored_packages)  &&  !is_array($stored_packages)){
                ?>
                <a class="button tips dhl_generate_packages button-primary"  href="<?php echo admin_url( '/?wf_dhl_generate_packages='.base64_encode($post->ID) ); ?>" data-tip="<?php _e( 'Generate Packages', 'wf-shipping-dhl' ); ?>"><?php _e( 'Generate Packages', 'wf-shipping-dhl' ); ?></a>
                <?php
            }else{
                $generate_url = admin_url('/post.php?wf_dhl_createshipment='.$post->ID);
                $generate_proforma_invoice_url = admin_url('/post.php?generate_proforma_invoice_dhl_elex='.$post->ID);
                $print_proforma_invoice_url = admin_url('/post.php?print_proforma_invoice_dhl_elex='.$post->ID);
                $delete_proforma_invoice_url = admin_url('/post.php?delete_proforma_invoice_dhl_elex='.$post->ID);
                $select_box_value = '';
                $show_insurance = '';
                $insurance_required = $this->user_settings['insure_contents'];
                if($customer_insurance == 'yes'){
                    $show_insurance = 'yes';
                }

                echo '<li>';
                echo '<span style="font-weight:bold;">'.__( 'Package(s)' , 'wf-shipping-dhl').':</span>';
                            $xa_order_to_country = elex_dhl_get_order_shipping_country($order);
                            $xa_plt_allowed_country = array('AL','AS','AD','AO','AI','AG','AR','AW','AU','CX','NF','AT','BS','BH','BB','BE','BZ','BJ','BM','BT','BO','AN','BA','BW','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','CD','CN','CO','KM','CG','CK','HR','CU','AN','CY','CZ','PM','CD','DK','DJ','DM','DO','EC','TL','ER','EE','ET','FK','FO','FJ','WF','FI','FR','GF','GA','GM','DE','GH','GI','GR','GL','GD','GP','GU','GQ','GY','GN','HT','HU','HK','IS','IL','IT','IE','VA','JM','JP','JO','JE','KE','KI','KP','KR','LV','LS','LR','LI','LT','LU','LA','MO','MG','MV','MW','MY','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MC','MN','MS','MZ','MM','MK','ME','NA','NP','NL','NC','NG','NZ','KN','NE','NU','NO','NR','OM','PA','PW','PG','PY','PN','PL','PT','PR','RE','RO','RW','WS','SM','ST','SA','SN','SC','SL','SG','SK','SI','SB','SO','ZA','SS','ES','LK','SD','LC','SR','SJ','SZ','SE','CH','RS','SH','BL','SX','VC','TW','TZ','TH','TG','TK','TO','TT','TR','TC','TV','UG','AE','UK','US','UY','VU','VE','VG','VI','EH');
                if($this->plt && in_array($xa_order_to_country, $xa_plt_allowed_country))
                {
                    echo ' <a href="'.admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_dhl_shipping&subtab=labels' ) .'" target="_blank" style="color:#ba0c2f;"><label style="background:yellow;float:right;padding:2px;">Paper Less Trade (PLT) is enabled.</label></a>';
                }
                echo '<table id="wf_dhl_package_list" class="wf-shipment-package-table" style="margin-bottom: 5px;margin-top: 5px;box-shadow:.5px .5px 5px lightgrey;">';                   
                echo '<tr>';

                echo '<th style="padding:6px;text-align:left;">'.__('Item / Package', 'wf-shipping-dhl').' <span class="woocommerce-help-tip" data-tip="The item / Package details will be shown in this column. Each package will be associated with individual items if the packaging option is chosen as Pack items individually.','wf-shipping-dhl"></span></th>';
                echo '<th style="padding:6px;text-align:left;">'.__('Weight', 'wf-shipping-dhl').' ('.$this->weight_unit.') <span class="woocommerce-help-tip" data-tip="Choose the total weight of the package/item. The weight will be associated with individual items only if the packaging option is chosen as Pack items individually. In this case, changing the weight will reflect on the commercial invoice. In any other case, a commercial invoice will have the weight of the items set on individual product admin page."></span></th>';
                echo '<th style="text-align:left;padding:6px;">'.__('Length', 'wf-shipping-dhl').'</th>';
                echo '<th style="text-align:left;padding:6px;">'.__('Width', 'wf-shipping-dhl').' </th>';
                echo '<th style="text-align:left;padding:6px;">'.__('Height', 'wf-shipping-dhl').' </th>';
                if($show_insurance == 'yes'){
                    echo '<th style="text-align:left;padding:6px;">'.__('Insurance', 'wf-shipping-dhl').'</th>';   
                }
                echo '<th style="text-align:left;padding-right:20px;">&nbsp;</th>';
                echo '</tr>';
                if( empty($stored_packages[0]) ){
                    $stored_packages[0][0] = $this->get_dhl_dummy_package();
                }

                foreach($stored_packages as $package_group_key  =>  $package_group){
                    if( !empty($package_group) && is_array($package_group) ){ //package group may empty if box packing and product have no dimensions 
                        $count = 1;
                        foreach($package_group as $stored_package_key   =>  $stored_package){
                            $order_packages = get_post_meta($this->order->get_id(), 'initial_generated_packages_dhl_elex', true);
                            $product_details = '';
                            $package_details = '';
                            if(!empty($stored_package) && is_array($stored_package))
                            {
                                if($this->packing_method == 'weight_based' || $this->packing_method == 'box_packing'){
                                    if($this->packing_method == 'weight_based'){
                                        $package_details = (isset($stored_package['Name']) && !empty($stored_package['Name']))? $stored_package['Name']: 'Weight Box '.$count++;
                                    }else{
                                        $package_details = (isset($stored_package['Name']) && !empty($stored_package['Name']))? $stored_package['Name']: 'Unnamed Box';
                                    }

                                    $packed_products = isset($stored_package['packed_products'])? $stored_package['packed_products']: array();
                                    $packed_product_details = array();
                                    if(!empty($packed_products)){
                                        foreach($packed_products as $packed_product){
                                            if($this->is_woocommerce_composite_products_installed){
                                                if(!empty($order_packages)){
                                                    foreach($order_packages as $order_package){
                                                        $order_package_contents = $order_package['contents'];
                                                        foreach($order_package_contents as $package_content){
                                                            $package_content_data = $package_content['data'];
                                                            if($package_content_data->get_id() == $packed_product->get_id()){
                                                                if(isset($package_content['composite_title'])){
                                                                    $packed_product_details[] = $package_content['composite_title'];
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }else{
                                                $packed_product_details[] = html_entity_decode($packed_product->get_name());
                                            }
                                        }
                                    }else{
                                        if($this->is_woocommerce_composite_products_installed){
                                            if($order_packages){
                                                foreach($order_packages as $order_package){
                                                    $order_package_contents = $order_package['contents'];
                                                    foreach($order_package_contents as $package_content){
                                                        $package_content_data = $package_content['data'];
                                                        if($package_content_data->get_name() == $stored_package['Name']){
                                                            if(isset($package_content['composite_title'])){
                                                                $package_details = $package_content['composite_title'];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if(!empty($packed_product_details))
                                    $product_details = '( '.implode(',', $packed_product_details).' )';

                                }else{// per_item_packing
                                    $product_name = '';

                                    if($this->is_woocommerce_composite_products_installed)
                                    {
                                        if(isset($stored_package['composite_title']) && !empty($stored_package['composite_title'])){
                                            $product_name = $stored_package['composite_title'];
                                        }else{
                                            $packed_products = $stored_package['packed_products'];
                                            $packed_product = $packed_products[0];
                                            $product_name = $packed_product->get_title();    
                                        }
                                        $product_details .= $product_name.', ';
                                    }else{
                                        if(isset($stored_package['packed_products'])){
                                            $packed_product = $stored_package['packed_products'][0];
                                            $product_name = $packed_product->get_title();
                                        }
                                        $product_details .= $product_name.', ';
                                    }
                                }
                            }
                            $package_name = strlen($product_details) > 30 ? substr($product_details,0,30)."..." : $product_details;
                            $product_details = rtrim( $product_details,', ');
                            $package_name = rtrim( $package_name,', ');
                            if(empty($package_name)){
                                $package_name = 'Custom Package';
                            }
                            $product_details = '<a href="#" title="'.$product_details.'" style="text-decoration: unset;color: black;cursor: default;" >'. $package_name .'</a>';
                            $cus_site_currency = get_woocommerce_currency();

                            $dimensions =   $this->get_dimension_from_package($stored_package);
                            $calc_insurance = 0;

                            if(is_array($dimensions)){   
                                if($customer_insurance == 'yes'){
                                    if(!empty($dimensions['insurance']) && $dimensions['insurance'] !=0 && !empty($this->insure_converstion_rate))
                                    {
                                        $calc_insurance =   $dimensions['insurance'];
                                    }
                                }

                                $package_quantity = isset($stored_package['quantity'])? $stored_package['quantity']: 1;

                                ?>
                                <tr>
                                    <?php if($this->packing_method == 'weight_based'){?>
                                        <td style="width:25%;padding:5px;border-radius:5px;margin-left:4px;"><small><?php echo "<b>".$package_details."</b> ". $product_details; ?></small></td>
                                    <?php   }else if($this->packing_method == 'box_packing'){?>
                                        <td style="width:25%;padding:5px;border-radius:5px;margin-left:4px;"><small><?php echo "<b>".$package_details."</b> ". $product_details; ?></small></td>
                                    <?php   }else { ?>
                                        <td style="width:25%;padding:5px;border-radius:5px;margin-left:4px;"><small><?php echo "<b>".$product_details." &times; ".$package_quantity."</b>"; ?></small></td>
                                    <?php 
                                        }
                                    ?> 
                                    <td><input type="text" id="dhl_manual_weight" name="dhl_manual_weight[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo str_replace(',','.',$dimensions['Weight']);?>" /> <b><?php echo $this->weight_unit; ?></b></td>     
                                    <td><input type="text" id="dhl_manual_length" name="dhl_manual_length[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo round($dimensions['Length']);?>" /> <b><?php echo $this->dim_unit; ?></b></td>
                                    <td><input type="text" id="dhl_manual_width" name="dhl_manual_width[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo round($dimensions['Width']);?>" /> <b><?php echo $this->dim_unit; ?></b></td>
                                    <td><input type="text" id="dhl_manual_height" name="dhl_manual_height[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo round($dimensions['Height']);?>" /> <b><?php echo $this->dim_unit; ?></b></td>
                                    <?php
                                    if($show_insurance == 'yes'){
                                        ?>
                                        <td><input type="text" id="dhl_manual_insurance" name="dhl_manual_insurance[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="<?php echo ($customer_insurance) ? $calc_insurance : '';?>" title="<?php echo $dimensions['insurance'] .' '.get_woocommerce_currency(); ?>" /> <b><?php echo get_woocommerce_currency(); ?> </b> </td>
                                        <?php
                                    }
                                    ?>
                                    <td><a class="tips wf_dhl_package_line_remove"  data-tip="<?php _e( 'Delete Package', 'wf-shipping-dhl' ); ?>" >&#x26D4;</a></td>
                                </tr>
                                <?php
                            }
                        }
                    }
                }
                echo '</table>';
                echo '<a class="wf-action-button wf-add-button button-secondary" style="font-size: 12px;" id="wf_dhl_add_package">Add Package</a>'; ?>
                <a class="button tips dhl_generate_packages button-secondary"  href="<?php echo admin_url( '/?wf_dhl_generate_packages='.base64_encode($post->ID) ); ?>" data-tip="<?php _e( 'Regenerate Packages', 'wf-shipping-dhl' ); ?>"><?php _e( 'Regenerate', 'wf-shipping-dhl' ); ?></a> <?php

                echo '</li>';
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery('#wf_dhl_add_package').on("click", function(){
                            var new_row = '<tr>';
                                new_row     += '<td>Custom Package</td>';
                                new_row     += '<td><input type="text" id="dhl_manual_weight" name="dhl_manual_weight[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->weight_unit; ?></b></td>';
                                new_row     += '<td><input type="text" id="dhl_manual_length" name="dhl_manual_length[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->dim_unit; ?></b></td>';                               
                                new_row     += '<td><input type="text" id="dhl_manual_width" name="dhl_manual_width[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->dim_unit; ?></b></td>';
                                new_row     += '<td><input type="text" id="dhl_manual_height" name="dhl_manual_height[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo $this->dim_unit; ?></b></td>';
                                <?php
                                    if($customer_insurance == 'yes'){
                                ?>
                                new_row     += '<td><input type="text" id="dhl_manual_insurance" name="dhl_manual_insurance[]" style="width:60%;padding:5px;border-radius:5px;margin-left:4px;" value="0"> <b><?php echo get_woocommerce_currency(); ?></b></td>';
                                <?php } ?>
                                new_row     += '<td><a class="wf_dhl_package_line_remove tips" data-tip="Delete Package">&#x26D4;</a></td>';
                                new_row     += '</tr>';

                            jQuery('#wf_dhl_package_list tr:last').after(new_row);
                        });

                        jQuery(document).on('click', '.wf_dhl_package_line_remove', function(){
                            if(confirm('Are you sure you want to remove this package?'))
                            {
                                jQuery(this).closest('tr').remove();
                            }
                        });
                    });
                </script>
                <?php

                $available_services = get_post_meta($post->ID,'_wf_dhl_available_services',true);

                $currency_symbol = get_woocommerce_currency_symbol();
                if($this->is_woocommerce_multi_currency_installed){
                    $custom_currency_data = $woo_dhl_shipping_obj->get_exchange_rate_multicurrency_woocommerce($order->get_currency());;
                    $currency_symbol = $custom_currency_data['currency_symbol'];
                }

                echo '<li>';
                echo '<span style="font-weight:bold;">'.__( 'Choose Service' , 'wf-shipping-dhl').':</span>';
                echo '<table id="wf_dhl_service_select" class="wf-shipment-package-table" style="margin-bottom: 5px;margin-top: 5px;box-shadow:.5px .5px 5px lightgrey;">';                 
                echo '<tr>';

                    echo '<th></th>';
                    echo '<th style="text-align:left;padding:5px;">'.__('Service Name', 'wf-shipping-dhl').'</th>';
                    echo '<th style="text-align:left;">'.__('Delivery Time', 'wf-shipping-dhl').' </th>';
                    echo '<th style="text-align:left;">'.__('Cost ('. $currency_symbol.')', 'wf-shipping-dhl').' </th>';

                echo '</tr>';
                if(!empty($available_services))
                {
                    $order_shipping_method = $this->order->get_shipping_method();
                    $data = $this->order->get_items( 'shipping' );
                    foreach ($data as $meta_id => $shipping_desc){
                      foreach ($shipping_desc->get_meta_data() as $key => $value) {

                           $meta_data = $value->get_data();
                           if($meta_data['key'] == 'Service Label'){
                               $selected_service = $meta_data['value']; 
                           }
                      }
                    }
                    foreach ($available_services as $key => $value) {

                        echo '<tr style="padding:10px;">';

                        if($this->show_front_end_shipping_method === 'yes' && !empty($selected_service))
                        {
                            if( ($selected_service === $value['id']) || ($selected_service === $value['label']) )
                            {
                                echo '<td style="padding-left: 5px;padding-bottom: 3px;"><input name="wf_service_choosing_radio" id="wf_service_choosing_radio" value="'.$value['id'].'" type="radio" checked="true" ></td>';
                                ?>
                                <td><small><?php echo $value['label']; ?></small></td>
                                <td><small><?php echo $value['meta_data']['dhl_delivery_time']; ?></small></td>
                                <td><small><?php 

                                // Rate conversion
                                if ($this->conversion_rate)
                                    $value['cost'] = $value['cost'] * $this->conversion_rate;

                                $value_cost = apply_filters('wc_aelia_cs_convert', $value['cost'], $this->shop_currency, get_woocommerce_currency());

                                /* Handling code for WooCommerce Multi-Currency */
                                if($this->is_woocommerce_multi_currency_installed){
                                    $value_cost *= $custom_currency_data['exchange_rate'];
                                }

                                echo $value_cost;

                                 ?></small></td>
                                </tr>
                            <?php
                            }
                        }
                        else if($this->show_front_end_shipping_method !== 'yes' || ($order_shipping_method === 'Flat rate' || $order_shipping_method === 'Free shipping'))
                        {
                            if(array_key_exists($selected_service,$available_services )){
                                if($selected_service === $value['id'] || $selected_service === $value['label']){
                                    echo '<td style="padding-left: 5px;padding-bottom: 3px;"><input name="wf_service_choosing_radio" id="wf_service_choosing_radio" value="'.$value['id'].'" type="radio" checked="true" ></td>';
                                }else{
                                    echo '<td style="padding-left: 5px;padding-bottom: 3px;" ><input name="wf_service_choosing_radio" id="wf_service_choosing_radio" value="'.$value['id'].'" type="radio"  ></td>';
                                }
                            }
                            else
                            {
                                foreach($this->custom_services as $custom_service_key => $custom_service){
                                    if($custom_service_key == $value['id'] || $custom_service_key == $value['label']){
                                        if((!empty($custom_service['name']) && (($value['label'] == $custom_service['name'] && $custom_service['name'] == $selected_service) || $value['label'] == $custom_service['default_name'])) || $value['label'] == $selected_service){
                                            echo '<td style="padding-left: 5px;padding-bottom: 3px;"><input name="wf_service_choosing_radio" id="wf_service_choosing_radio" value="'.$value['id'].'" type="radio" checked="true" ></td>';
                                        }else{
                                            echo '<td style="padding-left: 5px;padding-bottom: 3px;" ><input name="wf_service_choosing_radio" id="wf_service_choosing_radio" value="'.$value['id'].'" type="radio"  ></td>';
                                        }
                                    }
                                }
                            }
                            ?>

                            <td><small><?php echo $value['label']; ?></small></td>
                            <td><small><?php echo $value['meta_data']['dhl_delivery_time']; ?></small></td>
                            <td><small><?php 

                            // Rate conversion
                            if ($this->conversion_rate)
                                $value['cost'] *= $this->conversion_rate;

                            //Compatibility with WooCommerce Currency Switcher by WooBeWoo Plugin
                            if ( in_array( 'woo-currency/wcu.php', get_option( 'active_plugins' ) ) ) {
                                if( $this->shop_currency != get_woocommerce_currency()) {
                                    $wcu_currencies = get_option('wcu_currencies');
                                    $value['cost'] *= $wcu_currencies[get_woocommerce_currency()]['rate'];
                                }
                            }

                            $value_cost = apply_filters('wc_aelia_cs_convert', $value['cost'], $this->shop_currency, get_woocommerce_currency());

                            /* Handling code for WooCommerce Multi-Currency */
                            if($this->is_woocommerce_multi_currency_installed){
                                $value_cost *= $custom_currency_data['exchange_rate'];
                            }

                            echo $value_cost;   

                             ?></small></td>
                        </tr>
                        <?php
                        }
                    }
                }
                else
                {
                    echo '<tr><td colspan="4"> Not able to get the Services at this moment, Re-Calculate the Shipment </td></tr>';
                }
                echo '</table>';
                ?>
                <a class="button tips wf_dhl_generate_packages_rates button-secondary"  href="<?php echo admin_url( '/?wf_dhl_generate_packages_rates='.base64_encode($post->ID) ); ?>" data-tip="<?php _e( 'Re-Calculate', 'wf-shipping-dhl' ); ?>"><?php _e( 'Re-Calculate', 'wf-shipping-dhl' ); ?></a>
                <?php

                if(!empty($this->sat_delivery) && $this->sat_delivery === 'yes') { ?>
                <li>
                    <label for="wf_dhl_sat_delivery">
                        <input type="checkbox" style="" id="wf_dhl_sat_delivery" name="wf_dhl_sat_delivery" class=""><?php _e('Saturday Delivery', 'wf-shipping-dhl') ?>
                    </label>
                </li>

                <?php } ?>
                <?php if(!empty($this->cash_on_delivery) && $this->cash_on_delivery === 'yes') { ?>
                <li>
                    <label for="wf_dhl_cash_on_delivery">
                        <input type="checkbox" style="" id="wf_dhl_cash_on_delivery" name="wf_dhl_cash_on_delivery" class=""><?php _e('Cash on Delivery', 'wf-shipping-dhl') ?>
                    </label>
                </li>

                <?php }
                ?>
                <table>
                    <?php
                    
                    if( ELEX_DHL_INDIA_ADDON_WOOCOMMERCE_EXTENSION && $this->origin_country =='IN' && $this->order->get_shipping_country() == 'IN') {
                        $dhl_india = 'true';
                    }
                    ?>
                    <?php if(($this->origin_country != $this->order->get_shipping_country()) && ( isset($this->settings['dutypayment_type']) && $this->settings['dutypayment_type'] == 'R')){?>
                    <tr>
                        <td><?php _e('Duty Payment (Recipient)', 'wf-shipping-dhl');?></td>
                        <td>
                            <select id="shipment_incoterm_express_dhl_elex" name="shipment_incoterm_express_dhl_elex" style="width: 100%"> <?php
                                foreach($receiver_duty_payment_types as $receiver_duty_payment_type_key => $receiver_duty_payment_type_value){
                                    if($this->settings['receiver_duty_payment_type'] == $receiver_duty_payment_type_key){
                                        echo '<option value="'.$receiver_duty_payment_type_key.'" selected>'.$receiver_duty_payment_type_value.'</option>';    
                                    }else{
                                        echo '<option value="'.$receiver_duty_payment_type_key.'">'.$receiver_duty_payment_type_value.'</option>';
                                    }
                                }?>
                            </select>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><?php _e('Shipment Content', 'wf-shipping-dhl');?></td><td>
                            <?php $label_contents_text = get_post_meta($orderid, 'shipment_content_express_dhl_elex', true);
                            if(!empty($label_contents_text)) $this->label_contents_text = $label_contents_text;?>
                            <input type="text" placeholder="Enter Shipment Contents to ship" id="wf_dhl_shpment_content" name="wf_dhl_shpment_content" style="width: 100%" value="<?php echo $this->label_contents_text; ?>">  
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Shipper Comments', 'wf-shipping-dhl');?></td>
                        <td>
                            <?php $label_comments_text = get_post_meta($post->ID, 'shipment_comments_express_dhl_elex', true);
                            if(!empty($label_comments_text)) $this->label_comment_text = $label_comments_text;?>
                            <input type="text" placeholder="Enter Comments for the shipment" id="shipment_comments_express_dhl_elex" name="shipment_comments_express_dhl_elex" style="width: 100%" value="<?php echo $this->label_comment_text; ?>"> 
                        </td>
                    </tr>
                </table>

                <?php
                echo '<hr style="border-color:#c9c9c9"></li>';
                ?>
                <li>
                    <?php if(((isset($this->settings['classic_commercial_invoice']) && $this->settings['classic_commercial_invoice'] == 'classic') || !isset($this->settings['classic_commercial_invoice'])) && isset($this->settings['option_generate_proforma_invoice']) && $this->settings['option_generate_proforma_invoice'] == 'yes'):?>
                    <?php $is_proforma_invoice_generated = get_option("is_elex_dhl_express_proforma_invoice_generated_".$orderid, false);?>
                    <?php if($is_proforma_invoice_generated){ ?>
                        <a class="button tips onclickdisable print_proforma_invoice_dhl_elex button-primary" href="<?php echo $print_proforma_invoice_url; ?>" target="_blank" style="position: absolute; right: 6%" data-tip="<?php _e('Print Proforma Invoice.', 'wf-shipping-dhl'); ?>"><?php _e('Proforma Invoice', 'wf-shipping-dhl'); ?></a>  
                        <a class="button tips onclickdisable delete_proforma_invoice_dhl_elex button-primary" href="<?php echo $delete_proforma_invoice_url; ?>" style="position: absolute; right: 1%; padding-top: 0.3% !important;" data-tip="<?php _e('Delete Proforma Invoice.', 'wf-shipping-dhl'); ?>"><span class="dashicons dashicons-trash"></span></a>   
            <?php   }else{ ?>
                        <a class="button tips onclickdisable generate_proforma_invoice_dhl_elex button-primary" href="<?php echo $generate_proforma_invoice_url; ?>" style="position: absolute; right: 1%;" data-tip="<?php _e('Generate Proforma Invoice before creating shipment.', 'wf-shipping-dhl'); ?>"><?php _e('Generate Proforma Invoice', 'wf-shipping-dhl'); ?></a> 
            <?php   }?>
            <?php endif; ?>
                    <a class="button tips onclickdisable dhl_create_shipment button-primary"  href="<?php echo $generate_url; ?>" data-tip="<?php _e('Create Shipment', 'wf-shipping-dhl'); ?>"><?php _e('Create Shipment', 'wf-shipping-dhl'); ?></a> 
                </li>
                </ul><?php
            } ?>
            <script type="text/javascript">
            jQuery("a.dhl_generate_packages").on("click", function() {
            location.href = this.href;
            });
            </script>
<?php   } ?>

        <script>
            jQuery(document).ready(function(){
                jQuery("a.wf_dhl_generate_packages_rates").one("click", function() {

                    jQuery(this).click(function () { return false; });

                    var manual_weight_arr     =     jQuery("input[id='dhl_manual_weight']").map(function(){return jQuery(this).val();}).get();

                    var manual_height_arr     =     jQuery("input[id='dhl_manual_height']").map(function(){return jQuery(this).val();}).get();

                    var manual_width_arr      =     jQuery("input[id='dhl_manual_width']").map(function(){return jQuery(this).val();}).get();

                    var manual_length_arr     =     jQuery("input[id='dhl_manual_length']").map(function(){return jQuery(this).val();}).get();

                    var manual_insurance_arr  =     jQuery("input[id='dhl_manual_insurance']").map(function(){return jQuery(this).val();}).get();

                    location.href = this.href + '&weight=' + manual_weight_arr +
                    '&length=' + manual_length_arr
                    + '&width=' + manual_width_arr
                    + '&height=' + manual_height_arr
                    + '&insurance=' + manual_insurance_arr;
                    return false;        
                });

                jQuery("a.dhl_create_shipment").one("click", function() {

                    jQuery(this).click(function () { return false; });

                    var is_dhl_india = <?php  echo $dhl_india ?>;
                    var manual_weight_arr   =   jQuery("input[id='dhl_manual_weight']").map(function(){return jQuery(this).val();}).get();
                    var manual_weight       =   JSON.stringify(manual_weight_arr);

                    var manual_height_arr   =   jQuery("input[id='dhl_manual_height']").map(function(){return jQuery(this).val();}).get();
                    var manual_height       =   JSON.stringify(manual_height_arr);

                    var manual_width_arr    =   jQuery("input[id='dhl_manual_width']").map(function(){return jQuery(this).val();}).get();
                    var manual_width        =   JSON.stringify(manual_width_arr);

                    var manual_length_arr   =   jQuery("input[id='dhl_manual_length']").map(function(){return jQuery(this).val();}).get();
                    var manual_length       =   JSON.stringify(manual_length_arr);

                    var manual_insurance_arr    =   jQuery("input[id='dhl_manual_insurance']").map(function(){return jQuery(this).val();}).get();
                    var manual_insurance        =   JSON.stringify(manual_insurance_arr);

                    var selectedShippingIncoterm = jQuery('#shipment_incoterm_express_dhl_elex').val();

                    var selectedShippingService = jQuery("input[name='wf_service_choosing_radio']:checked").val();

                    var eligibleShippingServices = <?php echo json_encode(get_post_meta($this->order->get_id(),'_wf_dhl_available_services',true));?>;

                    if(eligibleShippingServices){
                        jQuery.each(eligibleShippingServices, function(key, value){
                            if(key == selectedShippingService){
                                if(value['meta_data']['remote_area_surcharge'] != 0){
                                    alert('This ZIP Code incurs extra remote area surcharge for the selected shipping service');
                                }
                            }
                        });
                    }
                 
                    // Note: If the query length exceeds the limit change the request to POST
                    location.href = this.href 
                    + '&weight=' + manual_weight 
                    + '&length=' + manual_length
                    + '&width=' + manual_width
                    + '&height=' + manual_height
                    + '&insurance=' + manual_insurance
                    + '&sat_delivery=' + jQuery('#wf_dhl_sat_delivery').is(':checked')
                    + '&cash_on_delivery=' + jQuery('#wf_dhl_cash_on_delivery').is(':checked')
                    + '&dutypayment_type=' + selectedShippingIncoterm
                    + '&shipment_content=' + jQuery('#wf_dhl_shpment_content').val()
                    + '&shipment_comments=' + jQuery('#shipment_comments_express_dhl_elex').val()
                    + '&dhl_express_shipping_service=' + selectedShippingService
                    + '&dhl_india='+ is_dhl_india;
                    return false;         
                });
            });
        </script>       
        <?php
    }

    private function get_dhl_dummy_package(){
        return array(
            'Dimensions' => array(
                'Length' => 0,
                'Width' => 0,
                'Height' => 0,
                'Units' => $this->dim_unit
            ),
            'Weight' => array(
                'Value' => 0,
                'Units' => $this->weight_unit
            )
        );
    }

    public function get_dimension_from_package($package){
        $orderid = get_option('current_order_id');
        
        $customer_insurance = get_post_meta($orderid, 'wf_dhl_insurance', true);

        $dimensions =   array(
            'Length'    =>  0,
            'Width'     =>  0,
            'Height'    =>  0,
            'Weight'    =>  0,
            'insurance' =>  0,
        );

        if(!is_array($package)){ // Package is not valid
            return $dimensions;
        }

        if(isset($package['Dimensions'])){
            $dimensions['Length']   =   $package['Dimensions']['Length'];
            $dimensions['Width']    =   $package['Dimensions']['Width'];
            $dimensions['Height']   =   $package['Dimensions']['Height'];
            $dimensions['dim_unit'] =   isset($package['Dimensions']['Units']) ? $package['Dimensions']['Units'] : 0 ;
        }
        $dimensions['Weight']   =   round($package['Weight']['Value'], 3);
        $dimensions['weight_unit']  =   isset($package['Weight']['Units'])? $package['Weight']['Units']: '';
        if($customer_insurance == 'yes')
        {
            $dimensions['insurance']    =   isset($package['InsuredValue']['Amount']) ? $package['InsuredValue']['Amount']: 0;
        }
        else
        {
            $dimensions['insurance'] = 0;
        }

        return $dimensions;
    }   
}
new wf_dhl_woocommerce_shipping_admin();
