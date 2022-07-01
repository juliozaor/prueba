<?php

if (!defined('ABSPATH')) {
    exit;
}

class wf_dhl_woocommerce_shipping_admin_helper {

    private $service_code;

    public function __construct() {
        $this->id = WF_DHL_ID;
        $this->init();
        $this->auto_return_label = get_option('auto_return_label_generate');
        if($this->auto_return_label){
            $this->debug = false;
        }
    }

    private function init() {
        $this->settings = get_option('woocommerce_' . WF_DHL_ID . '_settings', null);

        $this->add_trackingpin_shipmentid = $this->settings['add_trackingpin_shipmentid'];
        $this->errorMsg = "";

        $this->origin = strtoupper($this->settings['origin']);
        $this->origin_country = isset($this->settings['base_country']) ? $this->settings['base_country'] : WC()->countries->get_base_country();
        $this->account_number = $this->settings['account_number'];

        $this->site_id = $this->settings['site_id'];
        $this->site_password = $this->settings['site_password'];
        $this->region_code = $this->settings['region_code'];

        $this->latin_encoding = isset($this->settings['latin_encoding']) && $this->settings['latin_encoding'] == 'yes' ? true : false;
        $utf8_support = $this->latin_encoding ? '?isUTF8Support=true' : '';

        $_stagingUrl = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet' . $utf8_support;
        $_productionUrl = 'https://xmlpi-ea.dhl.com/XMLShippingServlet' . $utf8_support;

        $this->insure_currency = isset($this->settings['insure_currency']) ? $this->settings['insure_currency'] : '';
        $this->insure_converstion_rate = !empty($this->settings['insure_converstion_rate']) ? $this->settings['insure_converstion_rate'] : '';

        $this->production = (!empty($this->settings['production']) && $this->settings['production'] === 'yes') ? true : false;
        $this->plt = (!empty($this->settings['plt']) && $this->settings['plt'] === 'yes') ? true : false;
        $this->service_url = ($this->production == true) ? $_productionUrl : $_stagingUrl;

        $this->debug = (!empty($this->settings['debug']) && $this->settings['debug'] === 'yes' && !isset($_REQUEST['post'])) ? true : false; //$__REQUEST['post'] to confirm its not coming from bulk action. Bulk action needs to forcefully turn off debug
        $auto_label_generate_add_on = get_option('create_bulk_orders_shipment');
        if($auto_label_generate_add_on){
            $this->debug = false;
        }

        $this->insure_contents = (!empty($this->settings['insure_contents']) && $this->settings['insure_contents'] === 'yes') ? true : false;
        $this->request_type = $this->settings['request_type'];
        $this->packing_method = $this->settings['packing_method'];
        $this->return_label_key = isset($this->settings['return_label_key']) ? $this->settings['return_label_key'] : '';
        $this->return_label_acc_number = isset($this->settings['return_label_acc_number']) ? $this->settings['return_label_acc_number'] : '';

        $this->boxes = isset($this->settings['boxes']) ? $this->settings['boxes'] : '';

        $this->custom_services = $this->settings['services'];
        $this->offer_rates = $this->settings['offer_rates'];

        $this->freight_shipper_person_name = htmlspecialchars($this->settings['shipper_person_name']);
        $this->freight_shipper_company_name = htmlspecialchars($this->settings['shipper_company_name']);
        $this->freight_shipper_phone_number = $this->settings['shipper_phone_number'];
        $this->shipper_email = $this->settings['shipper_email'];

        $this->freight_shipper_street = htmlspecialchars($this->settings['freight_shipper_street']);
        $this->freight_shipper_street_2 = htmlspecialchars($this->settings['shipper_street_2']);
        $this->freight_shipper_city = htmlspecialchars($this->settings['freight_shipper_city']);
        $this->freight_shipper_state = $this->settings['freight_shipper_state'];

        $this->output_format = $this->settings['output_format'];
        $this->image_type = $this->settings['image_type'];

        $this->settings['receiver_duty_payment_type'] = isset($_GET['dutypayment_type']) ? $_GET['dutypayment_type'] : (isset($this->settings['receiver_duty_payment_type']) ? $this->settings['receiver_duty_payment_type'] : '');  

        $this->dutypayment_type = isset($this->settings['dutypayment_type']) ? $this->settings['dutypayment_type'] : '';
        $this->dutyaccount_number = isset($this->settings['dutyaccount_number']) ? $this->settings['dutyaccount_number'] : '';

        $this->invoice_quantity_unit = isset($this->settings['invoice_quantity_unit']) ? $this->settings['invoice_quantity_unit'] : 'PCS';
        $this->invoice_language_code = isset($this->settings['invoice_language_code']) ? $this->settings['invoice_language_code'] : 'en';


        $this->pickupdate = isset($this->settings['pickup_date']) ? $this->settings['pickup_date'] : '0';
        $this->pickupfrom = isset($this->settings['pickup_time_from']) ? $this->settings['pickup_time_from'] : '';
        $this->pickupto = isset($this->settings['pickup_time_to']) ? $this->settings['pickup_time_to'] : '';
        $this->pickupperson = isset($this->settings['pickup_person']) ? htmlspecialchars($this->settings['pickup_person']) : '';
        $this->pickupcontct = isset($this->settings['pickup_contact']) ? $this->settings['pickup_contact'] : '';

        $this->dimension_unit = isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'IN' : 'CM';
        $this->weight_unit = isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'LBS' : 'KG';
        $this->product_weight_unit = isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'L' : 'K';
        $this->shop_weight_unit = get_option('woocommerce_weight_unit');
        $this->shop_dimension_unit = get_option('woocommerce_dimension_unit');

        $this->labelapi_dimension_unit = $this->dimension_unit == 'IN' ? 'I' : 'C';
        $this->labelapi_weight_unit = $this->weight_unit == 'LBS' ? 'L' : 'K';
        $this->conversion_rate = (!empty($this->settings['conversion_rate']) && !(is_plugin_active('woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php'))) ? $this->settings['conversion_rate'] : 1;

        //Time zone adjustment, which was configured in minutes to avoid time diff with server. Convert that in seconds to apply in date() functions.
        $this->timezone_offset = !empty($this->settings['timezone_offset']) ? intval($this->settings['timezone_offset']) * 60 : 0;

        if (class_exists('wf_vendor_addon_setup')) {
            if (isset($this->settings['vendor_check']) && $this->settings['vendor_check'] === 'yes') {
                $this->ship_from_address = 'vendor_address';
            } else {
                $this->ship_from_address = 'origin_address';
            }
        } else {
            $this->ship_from_address = 'origin_address';
        }

        $this->label_contents_text = (isset($_GET['shipment_content']) && $_GET['shipment_content'] != '') ? $_GET['shipment_content'] : ((isset($this->settings['label_contents_text']) && !empty($this->settings['label_contents_text']))? $this->settings['label_contents_text']: 'NA');

        $this->label_comments_text = (isset($_GET['shipment_comments']) && $_GET['shipment_comments'] != '') ? $_GET['shipment_comments'] : 'NA';

        $this->weight_packing_process = !empty($this->settings['weight_packing_process']) ? $this->settings['weight_packing_process'] : 'pack_descending';
        $this->box_max_weight = !empty($this->settings['box_max_weight']) ? $this->settings['box_max_weight'] : '';
        $this->non_plt_commercial_invoice = '';
        $this->local_product_code = '';
        $this->user_settings = get_option('woocommerce_wf_dhl_shipping_settings');
        $this->create_shipment_dhl_response = '';
        $this->special_service_code = array();
        $this->default_special_service_code = (isset($this->settings['default_special_service']) && !empty($this->settings['default_special_service']))? $this->settings['default_special_service'] : 'NA';
        $this->default_special_service_code_array = array();
        $this->special_service_warning = '';
        $this->shipment_un_numbers = array(); // Array for UN numbers for Dangerous Goods
        $this->generate_commercial_invoice_with_awb = '';
        $this->generate_return_commercial_invoice_with_awb = '';
        $this->order = new WC_Order();

        if (!class_exists('wf_dhl_woocommerce_shipping_method')) {
            include_once('class-wf-dhl-woocommerce-shipping.php');
        }

        $this->is_woocommerce_composite_products_installed = (in_array('woocommerce-composite-products/woocommerce-composite-products.php',get_option('active_plugins')))? true: false;

        $this->is_woocommerce_product_bundles_installed = (in_array('woocommerce-product-bundles/woocommerce-product-bundles.php',get_option('active_plugins')))? true: false;

        $this->is_woocommerce_multi_currency_installed = (in_array('woocommerce-multicurrency/woocommerce-multicurrency.php',get_option('active_plugins')))? true: false;

        add_filter('wf_dhl_filter_label_packages', array($this, 'elex_dhl_split_packages'), 10, 4);
    }

    public function debug($message, $type = 'notice') {
        if ($this->debug) {
            echo ($message);
        }
    }

    public function get_dhl_packages($package) {
        switch ($this->packing_method) {
        case 'box_packing':
            return $this->box_shipping($package);
            break;
        case 'weight_based':
            return $this->weight_based_shipping($package);
            break;
        case 'per_item':
        default:
            return $this->per_item_shipping($package);
            break;
        }
    }
    
    private function per_item_shipping($package) {
        global $woocommerce;
        $to_ship = array();
        $group_id = 1;
        $orderid = get_option('current_order_id');
        $order = wc_get_order($orderid);

        // Get weight of order
        if (!empty($package['contents'])) {
            foreach ($package['contents'] as $item_id => $values) {
                try {
                    if (!$values['data']->needs_shipping()) {
                        $this->debug(sprintf(__('Product # is virtual. Skipping.', 'wf-shipping-dhl'), $item_id), 'error');
                        continue;
                    }

                    $skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label', false, $values, $package['contents']);

                    if ($skip_product) {
                        continue;
                    }

                    if (isset($values['measured_weight']) && $values['measured_weight'] != 0) {
                        $weight = $values['measured_weight'];
                    } else {
                        $weight = wc_get_weight((!$values['data']->get_weight() ? 0 : $values['data']->get_weight()), $this->weight_unit, $this->shop_weight_unit);
                    }

                    if (!$weight) {
                        $this->debug(sprintf(__('Product # is missing weight. Aborting.', 'wf-shipping-dhl'), $item_id), 'error');
                        return;
                    }

                    $group = array();

                    //Obtaining discounted prices of the products
                    $items_in_the_order = $order->get_items();
                    $item_value = 0;
                    $item_value = elex_dhl_get_product_total_price($values, $items_in_the_order);

                    $insurance_array = array(
                        'Amount' => wc_format_decimal($item_value, 2, true),
                        'Currency' => get_woocommerce_currency(),
                    );

                    if ($weight < 0.001) {
                        $weight = 0.001;
                    } else {
                        $weight = round($weight, 3);
                    }

                    $group = array(
                        'GroupNumber' => $group_id,
                        'GroupPackageCount' => 1,
                        'Weight' => array(
                            'Value' => round($weight, 3),
                            'Units' => $this->weight_unit,
                        ),
                        'packed_products' => array($values['data'])
                    );

                    if($this->is_woocommerce_composite_products_installed){
                        $group['composite_title'] = isset($values['composite_title'])? $values['composite_title']: '';
                    }

                    if (elex_dhl_get_product_length($values['data']) && elex_dhl_get_product_height($values['data']) && elex_dhl_get_product_width($values['data'])) {

                        $dimensions = array(elex_dhl_get_product_length($values['data']), elex_dhl_get_product_width($values['data']), elex_dhl_get_product_height($values['data']));

                        sort($dimensions);

                        $group['Dimensions'] = array(
                            'Length' => max(1, round(wc_get_dimension($dimensions[2], $this->dimension_unit, $this->shop_dimension_unit ))),
                            'Width' => max(1, round(wc_get_dimension($dimensions[1], $this->dimension_unit, $this->shop_dimension_unit ))),
                            'Height' => max(1, round(wc_get_dimension($dimensions[0], $this->dimension_unit, $this->shop_dimension_unit ))),
                            'Units' => $this->dimension_unit,
                        );
                    }

                    $group['InsuredValue'] = $insurance_array;
                    $group['packtype'] = isset($this->settings['shp_pack_type']) ? $this->settings['shp_pack_type'] : 'OD';
                    
                    $group['quantity'] = $values['quantity'];
                    array_push($to_ship, $group);

                    $group_id++;
                }catch (Exception $e) {
                    $this->debug("Error " . $e);
                }
            }
        }

        return $to_ship;
    }

    /**
     * weight_based_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return void
     */
    private function weight_based_shipping($package) {
        if (!class_exists('WeightPack')) {
            include_once ('weight_pack/class-wf-weight-packing.php');
        }

        $weight_pack = new WeightPack($this->weight_packing_process);
        $weight_pack->set_max_weight($this->box_max_weight);
        $current_order_id = get_option("current_order_id");
        $current_order = wc_get_order($current_order_id);

        $package_total_weight = 0;

        $ctr = 0;
        foreach ($package['contents'] as $item => $values) {
            $ctr++;

            $skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label', false, $values, $package['contents']);
            if ($skip_product) {
                continue;
            }

            if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
                $this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'wf-shipping-dhl'), $ctr));
                continue;
            }

            if (isset($values['measured_weight']) && $values['measured_weight'] != 0) {
                $weight = $values['measured_weight'];
            } else {
                $weight = wc_get_weight((!$values['data']->get_weight() ? 0 : $values['data']->get_weight()), $this->weight_unit);
            }

            if (!$weight) {
                $this->debug(sprintf(__('Product #%d is missing weight.', 'wf-shipping-dhl'), $ctr), 'error');
                return;
            }

            $weight_pack->add_item($weight, $values['data'], $values['quantity']);
        }

        $pack = $weight_pack->pack_items();
        $errors = $pack->get_errors();
        $to_ship = array();
        if (!empty($errors)) {
            //do nothing
            return;
        } else {
            $boxes = $pack->get_packed_boxes();
            $unpacked_items = $pack->get_unpacked_items();

            $parcels = array_merge($boxes, $unpacked_items); // merge items if unpacked are allowed
            $package_count = sizeof($parcels);
            // get all items to pass if item info in box is not distinguished
            $packable_items = $weight_pack->get_packable_items();
            $all_items = array();
            if (is_array($packable_items)) {
                foreach ($packable_items as $packable_item) {
                    $all_items[] = $packable_item['data'];
                }
            }

            $order_total = '';
            if (isset($this->order)) {
                $order_total = $this->order->get_total();
            }
            $items_in_the_order = $current_order->get_items();

            $group_id = 1;

            foreach ($parcels as $parcel) {
                $insured_value = 0;
                $packed_products = array();
                $item_value = 0;

                //Obtaining discounted prices of the products
                if (!empty($parcel['items'])) {
                    $item_value = 0;
                    foreach ($parcel['items'] as $parcel_item) {
                        $item_value = elex_dhl_get_product_total_price($parcel_item, $items_in_the_order);
                        $insured_value = $insured_value + $item_value;
                    }
                } else {
                    if (isset($order_total) && $package_count) {
                        $insured_value = $order_total / $package_count;
                    }
                }

                $packed_products = isset($parcel['items']) ? $parcel['items'] : $all_items;

                // Creating package request
                $package_total_weight = round($parcel['weight'], 3);

                $insurance_array = array(
                    'Amount' => wc_format_decimal($insured_value, 2, true),
                    'Currency' => get_woocommerce_currency(),
                );

                $group = array(
                    // 'Name' => 'Weight Pack '.$group_id,
                    'GroupNumber' => $group_id,
                    'GroupPackageCount' => 1,
                    'Weight' => array(
                        'Value' => round($package_total_weight, 3),
                        'Units' => $this->weight_unit,
                    ),
                    'packed_products' => $packed_products,
                );
                $group['InsuredValue'] = $insurance_array;
                $group['packtype'] = isset($this->settings['shp_pack_type']) ? $this->settings['shp_pack_type'] : 'OD';

                $to_ship[] = $group;
                $group_id++;
            }
        }
        return $to_ship;
    }

    private function box_shipping($package) {
        if (!class_exists('WF_Boxpack')) {
            include_once ('class-wf-packing.php');
        }

        $boxpack = new WF_Boxpack();
        $current_order_id = get_option('current_order_id');
        $current_order = wc_get_order($current_order_id);
        update_option('current_order_data', $current_order);

        // Define boxes
        foreach ($this->boxes as $key => $box) {
            if (!$box['enabled']) {
                continue;
            }

            $box['pack_type'] = !empty($box['pack_type']) ? $box['pack_type'] : 'BOX';

            $newbox = $boxpack->add_box($box['length'], $box['width'], $box['height'], $box['box_weight'], $box['pack_type']);

            if (isset($box['id'])) {
                $newbox->set_id(current(explode(':', $box['id'])));
            }

            if (isset($box['name'])) {
                $newbox->set_name($box['name']);
            }

            if ($box['max_weight']) {
                $newbox->set_max_weight($box['max_weight']);
            }

            if ($box['pack_type']) {
                $newbox->set_packtype($box['pack_type']);
            }

        }

        // Add items
        foreach ($package['contents'] as $item_id => $values) {

            if (!$values['data']->needs_shipping()) {
                $this->debug(sprintf(__('Product # is virtual. Skipping.', 'wf-shipping-dhl'), $item_id), 'error');
                continue;
            }

            $skip_product = apply_filters('wf_shipping_skip_product_from_dhl_rate', false, $values, $package['contents']);

            if ($skip_product) {
                continue;
            }

            if (elex_dhl_get_product_length($values['data']) && elex_dhl_get_product_height($values['data']) && elex_dhl_get_product_width($values['data']) && elex_dhl_get_product_weight($values['data'])) {

                $dimensions = array(elex_dhl_get_product_length($values['data']), elex_dhl_get_product_height($values['data']), elex_dhl_get_product_width($values['data']));

                for ($i = 0; $i < $values['quantity']; $i++) {
                    $boxpack->add_item(
                        wc_get_dimension($dimensions[2], $this->dimension_unit), wc_get_dimension($dimensions[1], $this->dimension_unit), wc_get_dimension($dimensions[0], $this->dimension_unit), wc_get_weight($values['data']->get_weight(), $this->weight_unit), $values['data']->get_price(), array(
                            'data' => $values['data'],
                        )
                    );
                }
            } else {
                $this->debug(sprintf(__('Product #%s is missing dimensions. Aborting.', 'wf-shipping-dhl'), $item_id), 'error');
                return;
            }
        }

        // Pack it
        $boxpack->pack();
        $packages = $boxpack->get_packages();
        $not_packed_items = $boxpack->get_not_packed_items();
        $to_ship = array();
        $group_id = 1;

        foreach ($not_packed_items as $not_packed_item) {
            $dimensions = array($not_packed_item->length, $not_packed_item->width, $not_packed_item->height);
            sort($dimensions);
            $insurance_array = array(
                'Amount' => wc_format_decimal($not_packed_item->value, 2, true),
                'Currency' => get_woocommerce_currency(),
            );

            $package_value = $insurance_array;

            $not_packed_item_meta_data = $not_packed_item->get_meta('data');
            $not_packed_item_data = $not_packed_item_meta_data->get_data();
            $this->debug('<b><font color="red">Unpacked Item</font><font color="blue"> ' . $not_packed_item_data['name'] . '</font></b><br>');

            $group = array(
                'Name' => (!empty($not_packed_item_data['name'])) ? $not_packed_item_data['name'] : '',
                'GroupNumber' => $group_id,
                'GroupPackageCount' => 1,
                'Weight' => array(
                    'Value' => round($not_packed_item->weight, 3),
                    'Units' => $this->weight_unit,
                ),
                'Dimensions' => array(
                    'Length' => max(1, round($dimensions[2])),
                    'Width' => max(1, round($dimensions[1])),
                    'Height' => max(1, round($dimensions[0])),
                    'Units' => $this->dimension_unit,
                ),
                'InsuredValue' => $insurance_array,
                'packageValue' => $package_value,
                'unpacked' => 'yes'
            );

            $to_ship[] = $group;

            $group_id++;
        }

        foreach ($packages as $package) {
            if (property_exists($package, 'packed')) {
                $this->debug('<b><font color="green">Packed in </font>' . $package->id . "</b><br>");
                $dimensions = array($package->length, $package->width, $package->height);
                sort($dimensions);

                $insurance_array = array(
                    'Amount' => round($package->value),
                    'Currency' => get_woocommerce_currency(),
                );

                $package_value = $insurance_array;

                $group = array(
                    'Name' => (!empty($package->name)) ? $package->name : '',
                    'GroupNumber' => $group_id,
                    'GroupPackageCount' => 1,
                    'Weight' => array(
                        'Value' => round($package->weight, 3),
                        'Units' => $this->weight_unit,
                    ),
                    'Dimensions' => array(
                        'Length' => max(1, $dimensions[2]),
                        'Width' => max(1, $dimensions[1]),
                        'Height' => max(1, $dimensions[0]),
                        'Units' => $this->dimension_unit,
                    ),
                    'InsuredValue' => $insurance_array,
                    'packageValue' => $package_value,
                    'packed_products' => array(),
                    'package_id' => $package->id,
                    'packtype' => isset($package->packtype) ? $package->packtype : 'BOX',
                );

                if (!empty($package->packed) && is_array($package->packed)) {
                    foreach ($package->packed as $packed) {
                        $group['packed_products'][] = $packed->get_meta('data');
                    }
                }

                $to_ship[] = $group;

                $group_id++;
            }
        }

        return $to_ship;
    }

    /* Function to get custom product properties set by user in the product settings */
    private function get_product_custom_properties_dhl($post_id){
        $wf_hs_code = get_post_meta($post_id, '_wf_hs_code', 1);
        $manufacturing_country = get_post_meta($post_id, '_wf_manufacture_country', 1);
        if(empty($wf_hs_code) || empty($manufacturing_country)){
            $product_provided = wc_get_product($post_id);
            if($product_provided->get_type() == 'variation'){
                $post_parent_id = $product_provided->get_parent_id();
                $wf_hs_code = get_post_meta($post_parent_id, '_wf_hs_code', 1);
                $manufacturing_country = get_post_meta($post_parent_id, '_wf_manufacture_country', 1);
            }
        }

        $product_custom_properties = array( 'wf_hs_code' => $wf_hs_code, 'manufacturing_country' => $manufacturing_country );
        return $product_custom_properties;
    }

    /** 
    * Function set product details for commercial invoice generation
    * @access private
    * @param wc_product
    * @return mixed array
    */
    private function set_product_properties_dhl_commercial_invoice($product_id, $order_item_data, $is_item_bundled_item = false){
        $product = wc_get_product($product_id);
        $product_details['product_id'] =  $product_id;
        $product_details['price'] = 0;
        $product_details['total'] = 0;
        $product_details['tax'] = 0;

        if(isset($order_item_data['subtotal'])){
            $product_details['price'] = wc_format_decimal(($order_item_data['subtotal']/ $order_item_data['quantity']));
            $product_details['total'] = wc_format_decimal($order_item_data['subtotal']);
        }

        if(isset($order_item_data['subtotal_tax'])){
            $tax_on_product = wc_format_decimal(($order_item_data['subtotal_tax']/ $order_item_data['quantity']));
            $product_details['total'] += wc_format_decimal($order_item_data['subtotal_tax']);
            $product_details['tax'] = $tax_on_product;
        }

        $product_details['quantity'] = $order_item_data['quantity'];
        $product_details['weight'] = wc_get_weight($product->get_weight(), $this->weight_unit, $this->shop_weight_unit);

        $product_custom_properties = $this->get_product_custom_properties_dhl($product_id);

        $wf_hs_code = $product_custom_properties['wf_hs_code'];
        $manufacture = $product_custom_properties['manufacturing_country'];

        $product_details['description'] = html_entity_decode($product->get_name());
        $product_details['hs'] = $wf_hs_code;
        $product_details['weight_unit'] = $this->weight_unit;
        $product_details['manufacture'] = $manufacture;
        if($this->is_woocommerce_composite_products_installed){
            $composite_product_component = get_option('composite_product_components_dhl_elex');
            if($composite_product_component){
                $product_details['composite_product_component'] = true;
            }
        }
        if($is_item_bundled_item) {
            $product_details['composite_product_component'] = true;
        }

        return $product_details;
    }

    /**
    * function to return product details removing duplicates
    * @access private
    * @param array product details
    * @return array $product_details
    */
    private function get_product_details_unique($products_details){
        $products_unique = array();
        foreach($products_details as $product){
            if(empty($products_unique)){
                $products_unique[$product['product_id']] = $product;
            }else{
                $add_product = false;
                foreach($products_unique as $products_unique_element){
                    if($this->is_woocommerce_composite_products_installed){
                        if(isset($product['composite_product_component'])){
                            $products_unique[$product['product_id'].'_c'] = $product;// component product
                        }else{
                            $products_unique[$product['product_id']] = $product;
                        }
                    }else if((isset($product['package_type']) && $product['package_type'] == 'custom_package') || $products_unique_element['product_id'] != $product['product_id']){
                        $add_product = true;
                    }

                    if($add_product){
                        if(isset($product['product_id'])){
                            $products_unique[$product['product_id']] = $product;
                        }else{
                            $products_unique[] = $product;
                        }
                    }   
                }
            }
        }

        $products_unique = array_values($products_unique);
        $products_unique['no_package'] = count($products_unique);

        return $products_unique;
    }

    private function is_product_bundled_item($order_item){
        $order_item_metadata = $order_item->get_meta_data();
        $is_item_bundled_item = false;
        foreach($order_item_metadata as $order_item_metadatum){
            $metadatum_data = $order_item_metadatum->get_data();
            if($metadatum_data['key'] == '_bundled_by'){
                $is_item_bundled_item = true;
            }
        }
        return $is_item_bundled_item;
    }

    //shipper as parameter, because if multiventor plug-in is there, it couldn't take origin address.
    public function generate_commercial_invoice($order_id, $packages, $shipper, $toaddress, $document_type = 'commercial', $archive_ref = array(), $pickup_confirmation_number = '') {
        include_once ("fpdf/wf-dhl-commercial-invoice-template.php");
        $commercial_invoice = new wf_dhl_commercial_invoice();

        $order = wc_get_order($order_id);
        if(!$order)
            return;

        $document_title = 'Commercial Invoice';
        if($document_type == 'proforma'){
            $document_title = 'Proforma Invoice';
        }

        update_post_meta($order_id, 'shipment_comments_express_dhl_elex', $this->label_comments_text);
        $dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();

        if($this->is_woocommerce_multi_currency_installed){
            $custom_currency_data = $dhl_shipping_obj->get_exchange_rate_multicurrency_woocommerce($order->get_currency());
        }

        $shipping_method_id_order_placement = '';
        $shipping_methods = $order->get_shipping_methods();
        $shipping_methods = array_shift($shipping_methods);
        $shipping_method_title = '';
        if(!empty($shipping_methods)){
            $shipping_methods_data = $shipping_methods->get_data();
            $shipping_method_id_order_placement = $shipping_methods_data['method_id'];
            $shipping_method_title = $shipping_methods_data['method_title'];
        }

        $total_shipping_tax_on_order = 0;
        if (isset($this->settings['include_woocommerce_tax']) && ($this->settings['include_woocommerce_tax'] == 'yes')) {
            $total_shipping_tax_on_order = $order->get_shipping_tax();
        }

        $avaialble_dhl_express_services = get_post_meta($order->get_order_number(),'_wf_dhl_available_services',true);
        $shipment_service = get_option('service_selected_create_shipment_express_dhl_elex');
        $shipment_service_cost = 0;
        $shipment_service_type = '';
        $shipment_estimated_delivery_time = '';

        if(!empty($avaialble_dhl_express_services)){
            foreach($avaialble_dhl_express_services as $service_code => $service_data){
                if($document_type == 'proforma'){
                    if($service_data['label'] == $shipping_method_title){
                        $shipment_service_type = $service_data['label'];
                        $shipment_service_cost = (float)$service_data['cost'] * (float)$this->conversion_rate; 
                        $shipment_estimated_delivery_time = substr($service_data['meta_data']['dhl_delivery_time'], 0, 10);   
                    }
                }else if($shipment_service == $service_code){
                    $shipment_service_cost = (float)$service_data['cost'] * (float)$this->conversion_rate;
                    $shipment_service_type = $service_data['label'];
                    break;
                }
            }
        }

        $fromaddress = array();
        $fromaddress['sender_name'] = html_entity_decode($shipper['contact_person_name']);
        $fromaddress['sender_address_line1'] = $shipper['address_line'];
        $fromaddress['sender_address_line2'] = $shipper['address_line2'];
        $fromaddress['sender_city'] = $shipper['city'];
        $fromaddress['sender_country'] = $shipper['country_name'];
        $fromaddress['sender_postalcode'] = $shipper['postal_code'];
        $fromaddress['phone_number'] = $shipper['contact_phone_number'];
        $fromaddress['sender_company'] = $shipper['company_name'];
        $fromaddress['sender_state_code'] = $shipper['division_code'];
        $fromaddress['sender_email'] = $shipper['contact_email'];

        $products_details = array();
        if (!empty($packages)) {
            $total_weight = 0;
            $total_value = 0;
            $sub_total = 0;

            $total_units = 0;
            $i = 0;
            $pre_product_id = '';
            $net_weight = 0;
            $pre_package = 0;

            $order_items = $order->get_items();
            $order_data = $order->get_data();
            $currency = $order_data['currency'];
            $products_details = array();

            foreach($order_items as $order_item){
                $order_item_data = $order_item->get_data();
                $product_id = '';
                if($order_item_data['variation_id'] != 0 && $order_item_data['variation_id']){
                    $product_id = $order_item_data['variation_id'];
                }else if($order_item_data['product_id'] != 0 && $order_item_data['product_id']){
                    $product_id = $order_item_data['product_id'];
                }
                
                $product = wc_get_product($product_id);
                $is_item_bundled_item = false;
                if($this->is_woocommerce_product_bundles_installed){
                    $is_item_bundled_item = $this->is_product_bundled_item($order_item);
                }

                if($this->is_woocommerce_composite_products_installed){
                    $is_product_composite_parent = false;
                    $is_product_composite_child = false;
                    $product_composite_data = array();
                    $order_item_metadata = $order_item->get_meta_data();
                    foreach($order_item_metadata as $order_item_metadatum){
                        $data = $order_item_metadatum->get_data();
                        if($data['key'] == '_composite_children'){
                            $is_product_composite_parent = true;
                        }

                        if($data['key'] == '_composite_parent'){
                            $is_product_composite_child = true;
                        }

                        if($is_product_composite_parent){
                           if($data['key'] == '_composite_data'){
                                $product_composite_data = $data['value'];
                            } 
                        }
                    }
                    $product_details = array();
                    if($is_product_composite_parent){
                        $products_details[] = $this->set_product_properties_dhl_commercial_invoice($product_id, $order_item_data);
                        if(empty($product->get_weight()) && empty($product->get_weight()) && empty($product->get_weight()) && empty($product->get_weight())){
                            $product_weight = $product->get_weight();
                            $product_components = array();
                            foreach($product_composite_data as $product_composite_datum){
                                $component_product_id = isset($product_composite_datum['variation_id'])? $product_composite_datum['variation_id']: $product_composite_datum['product_id'];
                                update_option("composite_product_components_dhl_elex", true);
                                $product_components[] = $this->set_product_properties_dhl_commercial_invoice($component_product_id, $product_composite_datum);
                                delete_option("composite_product_components_dhl_elex");
                            }

                            if(!empty($product_components)){
                                if(empty($product_weight)){
                                    foreach ($product_components as $component) {
                                        if(!empty($component['weight'])){
                                            $product_weight = (int)$product_weight + (int)$component['weight'];
                                        }
                                    }
                                }
                                $products_details[count($products_details)-1]['weight'] = $product_weight;
                                foreach($product_components as $product_component){
                                    $products_details[] = $product_component;
                                }
                            }
                        }               
                    }else if(!$is_product_composite_child){
                        $products_details[] = $this->set_product_properties_dhl_commercial_invoice($product_id, $order_item_data);
                    }
                }else{
                    $products_details[] = $this->set_product_properties_dhl_commercial_invoice($product_id, $order_item_data, $is_item_bundled_item);
                }
            }
        }

        foreach($packages[0] as $packages_element){
            if(isset($packages_element['PackageType']) && ($packages_element['PackageType'] == 'custom_package')){
                $additional_package = array(
                    'price' => 0,
                    'total' => 0,
                    'quantity' => 1,
                    'weight' => $packages_element['Weight']['Value'],
                    'description' => 'Additional Package',
                    'hs' => '',
                    'weight_unit' => 'KG',
                    'manufacture' => '',
                    'package_type' => 'custom_package'
                );

                array_push($products_details, $additional_package);
            }
        }

        if (!empty($products_details)) {
            if($this->packing_method == 'box_shipping' || $this->packing_method == 'per_item'){
                foreach ($packages as $package) {
                    foreach ($package as $item) {
                        if(!isset($item['quantity'])){
                            $item['quantity'] = 1;
                        }

                        $total_weight += (float)$item['Weight']['Value'] * $item['quantity']; // Adding package's weight as net weight
                    }
                }
            }else{
                foreach ($packages as $package) {
                    foreach ($package as $item) {
                        $total_weight += (float)$item['Weight']['Value']; // Adding package's weight as net weight
                    }
                }
            }
            
            $tax_on_products = 0;

            foreach ($products_details as $product) {
                $product_woocommerce_data = '';
                if(isset($product['product_id'])){
                    $product_woocommerce_data = wc_get_product($product['product_id']);
                }

                if(isset($product['weight'])){
                    $product_net_weight = (float)$product['weight'] * (float)$product['quantity'];
                }else{
                    $product_net_weight = (float)$product_woocommerce_data->get_weight() * (float)$product['quantity'];
                }

                
                if(!isset($product['composite_product_component'])) {
                    $total_units += $product['quantity'];
                    $net_weight += $product_net_weight;
                }
                $sub_total += $product['quantity'] * ($product['price'] + $product['tax']);
                $tax_on_products += $product['quantity'] * $product['tax'];
            }
        }

        $products_details_quantity_updated = $this->get_product_details_unique($products_details);

        $from_address_extra_data = array("archive_ref" => $archive_ref, "order_number" => $order->get_order_number());
        if($this->settings['include_shipper_vat_number'] == 'yes'){
            $from_address_extra_data['vat_number'] = $this->settings['shipper_vat_number'];
        }

        if($pickup_confirmation_number != ''){
            $from_address_extra_data['pickup_booking_no'] = $pickup_confirmation_number;   
        }

        $insured_amount = 0;
        if (isset($archive_ref['Insured Amount']) && !empty($archive_ref['Insured Amount'])) {
            $insured_amount = (int) $archive_ref['Insured Amount'];
            $insured_amount = wc_format_decimal($insured_amount, 2, true);
        }

        $total_value = $sub_total;

        $shipment_service_cost = $this->is_woocommerce_multi_currency_installed ? $custom_currency_data['exchange_rate'] * $shipment_service_cost: $shipment_service_cost;
        if($dhl_shipping_obj->aelia_activated){
            $shipment_service_cost = apply_filters('wc_aelia_cs_convert', $shipment_service_cost, $dhl_shipping_obj->shop_currency, $order->get_currency());
        }
        $total_weight = round($total_weight, 3);

        $shipping_cost_details = array('shipping_cost' => $shipment_service_cost, 'shipping_tax' => $total_shipping_tax_on_order);

        $custom_shipment_details = apply_filters('get_order_shipping_cost_elex_dhl_express', $shipping_cost_details, $this->order);
        $shipment_service_cost = $custom_shipment_details['shipping_cost'];
        $total_shipping_tax_on_order = $custom_shipment_details['shipping_tax'];
        $total_value += $shipment_service_cost;
        if(isset($this->settings['include_woocommerce_tax']) && $this->settings['include_woocommerce_tax'] == 'yes'){
            $total_value += $total_shipping_tax_on_order;
        }
        else {
            $sub_total -= $tax_on_products;
            $total_value -= $tax_on_products;
        }

        $total_value -= $order->get_total_discount();

        $package_details = array(
            'value' => wc_format_decimal($sub_total, 2, true), //total product price sum
            'shipping_cost' => wc_format_decimal($shipment_service_cost, 2, true),
            'insurance' => wc_format_decimal($insured_amount, 2, true),
            'discount' => wc_format_decimal($order->get_total_discount(), 2, true),
            'shipping_tax' => wc_format_decimal($total_shipping_tax_on_order, 2, true),
            'total' => wc_format_decimal($total_value, 2, true),
            'net_weight' => round($net_weight, 3),
            'gross_weight' => round($total_weight, 3),
            'currency' => $currency,
            'weight_unit' => $this->weight_unit,
            'total_unit' => $total_units,
            'total_package' => count($packages[0]),
            'originator' => html_entity_decode($shipper['company_name']),
            'shipment_content' => !empty($this->label_contents_text)? $this->label_contents_text: (isset($this->settings['label_contents_text'])? $this->settings['label_contents_text']: ''),
            'shipment_comments' => !empty($this->label_comments_text)? $this->label_comments_text: ''
        );
        if(!(isset($this->settings['include_woocommerce_tax']) && $this->settings['include_woocommerce_tax'] == 'yes')){
            unset($package_details['shipping_tax']);
        }
        $package_details = apply_filters('disable_shipping_cost_commercial_invoice_elex_dhl_express', $package_details);

        $extra_details = array(
            'Terms Of Trade' => ($this->dutypayment_type == 'S') ? 'DDP' : (($this->dutypayment_type == 'R') ? $this->settings['receiver_duty_payment_type'] : ''),
            'Terms Of Payment' => '',
            'Contract number' => '',
            'Contract Date' => '',
        );

        if($shipment_service_type != ''){
            if(isset($this->settings['include_shipping_service_type']) && $this->settings['include_shipping_service_type'] == 'yes'){
                $extra_details['Shipment Service Type'] = $shipment_service_type;
            }

            if($document_type == 'proforma'){
                $extra_details['Shipment Estimated Delivery Date'] = $shipment_estimated_delivery_time;
            }
        }

        $designated_broker = array(
            'dutypayment_type' => isset($this->settings['dutypayment_type']) ? $this->settings['dutypayment_type'] : '',
            'dutyaccount_number' => isset($this->settings['dutyaccount_number']) ? $this->settings['dutyaccount_number'] : '',
        );

        $commercial_invoice->get_package_total($products_details_quantity_updated['no_package']);
        $commercial_invoice->init(2, $document_title);
        $commercial_invoice->addShippingToAddress(apply_filters('wf_dhl_commecial_invoice_destination_address', $toaddress, $packages, $order));
        $commercial_invoice->addShippingFromAddress(apply_filters('wf_dhl_commecial_invoice_source_address', $fromaddress, $packages, $order), $from_address_extra_data);
        $commercial_invoice->designated_broker(apply_filters('wf_dhl_commecial_invoice_designated_broker', $designated_broker, $packages, $order));
        $commercial_invoice->addExtraDetails(apply_filters('wf_dhl_commecial_invoice_exta_details', $extra_details, $packages, $order));
        $commercial_invoice->addProductDetails($products_details_quantity_updated);
        $commercial_invoice->addPackageDetails(apply_filters('wf_dhl_commecial_invoice_package_details', $package_details, $packages, $order));
        return base64_encode($commercial_invoice->Output('', 'S'));
    }

    private function generate_return_commercial_invoice($packages, $shipper, $toaddress, $selected_items , $archive_ref) {
        include_once ("fpdf/wf-dhl-commercial-invoice-template.php");
        $commercial_invoice = new wf_dhl_commercial_invoice();

        $this_order_data = $this->order->get_data();
        $order_id = $this->order->get_id();
        $total_shipping_tax_on_order = 0;
        update_post_meta($order_id, 'shipment_comments_express_dhl_elex', $this->label_comments_text);

        $toaddress = array(
            'first_name' => html_entity_decode(isset($toaddress['person_name'])? $toaddress['person_name']: $toaddress['contact_person_name']),
            'last_name' => '',
            'company' => $toaddress['company_name'],
            'address_1' => html_entity_decode(isset($toaddress['address_line'])? $toaddress['address_line']: $toaddress['address_1']),
            'address_2' => html_entity_decode(isset($toaddress['address_line2'])? $toaddress['address_line2']: $toaddress['address_2']),
            'city' => $toaddress['city'],
            'postcode' => (isset($toaddress['division'])? $toaddress['division']: $toaddress['state']).' '.(isset($toaddress['postal_code'])? $toaddress['postal_code']: $toaddress['postcode']),
            'country' => $toaddress['country_name'],
            'email' => isset($toaddress['contact_email'])? $toaddress['contact_email']: $toaddress['email'],
            'phone' => isset($toaddress['contact_phone_number'])? $toaddress['contact_phone_number']: $toaddress['phone'],
        );

        $fromaddress = array();
        $fromaddress['sender_name'] = isset($shipper['person_name'])? $shipper['person_name']: $shipper['first_name'].' '.$shipper['last_name'];
        $fromaddress['sender_address_line1'] = $shipper['address_1'];
        $fromaddress['sender_address_line2'] = $shipper['address_2'];
        $fromaddress['sender_city'] = $shipper['city'];
        $fromaddress['sender_country'] = $shipper['country_code'];
        $fromaddress['sender_postalcode'] = $shipper['postcode'];
        $fromaddress['phone_number'] = $shipper['phone'];
        $fromaddress['sender_company'] = $shipper['company_name'];
        $fromaddress['sender_email'] = $shipper['email'];
        $fromaddress['sender_state_code'] = '';

        $products_details = array();
        if (!empty($packages)) {
            $total_weight = 0;
            $total_value = 0;
            $sub_total = 0;

            $total_units = 0;
            $i = 0;
            $pre_product_id = '';
            $net_weight = 0;
            $pre_package = 0;

            $order_items = $this->order->get_items();
            $order_data = $this->order->get_data();
            $currency = $order_data['currency'];
            $products_details = array();

            foreach($order_items as $order_item){           
                $order_item_data = $order_item->get_data();
                $product_id = '';
                if($order_item_data['variation_id'] != 0 && $order_item_data['variation_id']){
                    $product_id = $order_item_data['variation_id'];
                }else if($order_item_data['product_id'] != 0 && $order_item_data['product_id']){
                    $product_id = $order_item_data['product_id'];
                }

                $is_marked_as_return_item = empty($selected_items) ? true : in_array($product_id, $selected_items);                
                if($is_marked_as_return_item){
                $product = wc_get_product($product_id);
                if($this->is_woocommerce_product_bundles_installed){
                    $is_item_bundled_item = $this->is_product_bundled_item($order_item);
                    if($is_item_bundled_item){
                        continue;
                    }
                }

                if($this->is_woocommerce_composite_products_installed){
                    $is_product_composite_parent = false;
                    $is_product_composite_child = false;
                    $product_composite_data = array();
                    $order_item_metadata = $order_item->get_meta_data();
                    foreach($order_item_metadata as $order_item_metadatum){
                        $data = $order_item_metadatum->get_data();
                        if($data['key'] == '_composite_children'){
                            $is_product_composite_parent = true;
                        }

                        if($data['key'] == '_composite_parent'){
                            $is_product_composite_child = true;
                        }

                        if($is_product_composite_parent){
                           if($data['key'] == '_composite_data'){
                                $product_composite_data = $data['value'];
                            } 
                        }
                    }
                    $product_details = array();
                    if($is_product_composite_parent){
                        $products_details[] = $this->set_product_properties_dhl_commercial_invoice($product_id, $order_item_data);
                        $product_weight = $product->get_weight();
                        $product_components = array();
                        foreach($product_composite_data as $product_composite_datum){
                            $component_product_id = isset($product_composite_datum['variation_id'])? $product_composite_datum['variation_id']: $product_composite_datum['product_id'];
                            update_option("composite_product_components_dhl_elex", true);
                            $product_components[] = $this->set_product_properties_dhl_commercial_invoice($component_product_id, $product_composite_datum);
                            delete_option("composite_product_components_dhl_elex");
                        }

                        if(!empty($product_components)){
                            if(empty($product_weight)){
                                foreach ($product_components as $component) {
                                    if(!empty($component['weight'])){
                                        $product_weight = (int)$product_weight + (int)$component['weight'];
                                    }
                                }
                            }
                            $products_details[count($products_details)-1]['weight'] = $product_weight;
                            foreach($product_components as $product_component){
                                $products_details[] = $product_component;
                            }
                        }
                    }else if(!$is_product_composite_child){
                        $products_details[] = $this->set_product_properties_dhl_commercial_invoice($product_id, $order_item_data);
                    }
                }else{
                    $products_details[] = $this->set_product_properties_dhl_commercial_invoice($product_id, $order_item_data);
                }
                }
            }
        }

        $sub_total = 0;

        if (!empty($products_details)) {
            if($this->packing_method == 'box_shipping' || $this->packing_method == 'per_item'){
                foreach ($packages as $package) {
                    foreach ($package as $item) {
                        if(!isset($item['quantity'])){
                            $item['quantity'] = 1;
                        }

                        $total_weight += (float)$item['Weight']['Value'] * $item['quantity']; // Adding package's weight as net weight
                    }
                }
            }else{
                foreach ($packages as $package) {
                    foreach ($package as $item) {
                        $total_weight += (float)$item['Weight']['Value']; // Adding package's weight as net weight
                    }
                }
            }

            foreach ($products_details as $product) {
                $product_woocommerce_data = '';
                if(isset($product['product_id'])){
                    $product_woocommerce_data = wc_get_product($product['product_id']);
                }

                if(isset($product['weight'])){
                    $product_net_weight = (float)$product['weight'] * (float)$product['quantity'];
                }else{
                    $product_net_weight = (float)$product_woocommerce_data->get_weight() * (float)$product['quantity'];
                }

                if(!empty($product['quantity'])){
                    $total_units += $product['quantity'];
                }

                $net_weight += wc_get_weight($product_net_weight, $this->weight_unit, $this->shop_weight_unit);
                $sub_total += $product['quantity'] * $product['price'];
            }
        }

        $total_value = $sub_total;

        $products_details_quantity_updated = $this->get_product_details_unique($products_details);
        $from_address_extra_data = array("archive_ref" => $archive_ref, "order_number" => $this->order->get_order_number());

        $insured_amount = 0.00;
        if (isset($archive_ref['Insured Amount']) && !empty($archive_ref['Insured Amount'])) {
            $insured_amount = (int) $archive_ref['Insured Amount'];
            $insured_amount = wc_format_decimal($insured_amount, 2, true);
        }

        if($this->shop_weight_unit != $this->weight_unit){
            $total_weight = wc_get_weight($total_weight, $this->weight_unit, $this->shop_weight_unit);
        }

        $total_value -= $this->order->get_total_discount();

        $package_details = array(
            'value' => wc_format_decimal($sub_total, 2, true), //total product price sum
            'insurance' => wc_format_decimal($insured_amount, 2, true),
            'discount' => wc_format_decimal($this->order->get_total_discount(), 2, true),
            'tax' => wc_format_decimal($total_shipping_tax_on_order, 2, true),
            'total' => wc_format_decimal($total_value, 2, true),
            'net_weight' => round($net_weight, 3),
            'gross_weight' => round($total_weight, 3),
            'currency' => $currency,
            'weight_unit' => $this->weight_unit,
            'total_unit' => $total_units,
            'total_package' => count($packages),
            'originator' => $shipper['company_name'],
            'shipment_content' => !empty($this->label_contents_text)? $this->label_contents_text: (isset($this->settings['label_contents_text'])? $this->settings['label_contents_text']: ''),
            'shipment_comments' => !empty($this->label_comments_text)? $this->label_comments_text: ''
        );

        $extra_details = array(
            'Terms Of Trade' => ($this->dutypayment_type == 'S') ? 'DDP' : (($this->dutypayment_type == 'R') ? $this->settings['receiver_duty_payment_type'] : ''),
            'Terms Of Payment' => '',
            'Contract number' => '',
            'Contract Date' => '',
        );

        $designated_broker = array(
            'dutypayment_type' => $this->settings['dutypayment_type'],
            'dutyaccount_number' => $this->settings['dutyaccount_number'],
        );

        $commercial_invoice->get_package_total($total_units);
        $commercial_invoice->init(2);
        $commercial_invoice->addShippingToAddress(apply_filters('wf_dhl_commecial_invoice_destination_address', $toaddress, $packages, $this->order));
        $commercial_invoice->addShippingFromAddress(apply_filters('wf_dhl_commecial_invoice_source_address', $fromaddress, $packages, $this->order), $from_address_extra_data);
        $commercial_invoice->designated_broker(apply_filters('wf_dhl_commecial_invoice_designated_broker', $designated_broker, $packages, $this->order));
        $commercial_invoice->addProductDetails($products_details_quantity_updated);
        $commercial_invoice->addPackageDetails(apply_filters('wf_dhl_commecial_invoice_package_details', $package_details, $packages, $this->order));
        $commercial_invoice->addExtraDetails(apply_filters('wf_dhl_commecial_invoice_exta_details', $extra_details, $packages, $this->order));
        return base64_encode($commercial_invoice->Output('', 'S'));
    }

    public function get_package_signature($products, $orderid) {
        $signature_priority_array = array(
            0 => 'SX',
            1 => 'SB',
            2 => 'SC',
            3 => 'SD',
            4 => 'SE',
            5 => 'SW',
        );

        $higher_signature_option = 0;
        foreach ($products as $key => $product) {
            $par_id = wp_get_post_parent_id(elex_dhl_get_product_id($product['data']));
            $post_id = $par_id ? $par_id : elex_dhl_get_product_id($product['data']);

            $wf_dcis_type = get_post_meta($post_id, '_wf_dhl_signature', true);
            if (empty($wf_dcis_type)) {
                $wf_dcis_type = 0;
            }

            if ($wf_dcis_type > $higher_signature_option) {
                $higher_signature_option = $wf_dcis_type;
            }
        }     

        return $signature_priority_array[$higher_signature_option];
    }

    /*
        * Function to provide actual special service type code for selected type in the product settings
        * If one of the product in an order is a dangerous or a restricted commodity, the shipment content message
        * of that order will be filled by one of these messages based on the type of product
    */
    public function find_special_service_products($products) {
        $special_service_codes_messages = array(
            'N' => array(
                'code' => '',
                'message' => '',
            ),
            'NA' => array(
                'code' => '',
                'message' => '',
            ),
            'HECAOI1A' => array(
                'code' => 'HE',
                'message' => 'Lithium ion batteries  - Dangerous Goods as per attached DGD - CAO',
            ),
            'HECAOI1B' => array(
                'code' => 'HE',
                'message' => 'Lithium ion batteries  - Dangerous Goods as per attached DGD - CAO',
            ),
            'HEDGDI966' => array(
                'code' => 'HE',
                'message' => 'Lithium ion batteries  - Dangerous Goods as per attached DGD',
            ),
            'HEDGDI967' => array(
                'code' => 'HE',
                'message' => 'Lithium ion batteries  - Dangerous Goods as per attached DGD',
            ),
            'HB' => array(
                'code' => 'HB',
                'message' => 'Lithium ion batteries in compliance with Section II of PI965  - CAO',
            ),
            'HD' => array(
                'code' => 'HD',
                'message' => 'Lithium ion batteries in compliance with Section II of PI966',
            ),
            'HV' => array(
                'code' => 'HV',
                'message' => 'Lithium ion batteries in compliance with Section II of PI 967',
            ),
            'HECAOM1A' => array(
                'code' => 'HE',
                'message' => 'Lithium Metal Batteries - Dangerous Goods as per attached DGD-CAO',
            ),
            'HECAOM1B' => array(
                'code' => 'HE',
                'message' => 'Lithium Metal Batteries - Dangerous Goods as per attached DGD-CAO',
            ),
            'HEDGDM969' => array(
                'code' => 'HE',
                'message' => 'Lithium Metal Batteries - Dangerous Goods as per attached DGD',
            ),
            'HEDGDM970' => array(
                'code' => 'HE',
                'message' => 'Lithium Metal Batteries - Dangerous Goods as per attached DGD',
            ),
            'HM' => array(
                'code' => 'HM',
                'message' => 'Lithium metal batteries in compliance with Section II of PI969',
            ),
            'HW' => array(
                'code' => 'HW',
                'message' => 'Lithium metal batteries in compliance with Section II of PI970',
            ),
            'HVHW' => array(
                array(
                    'code' => 'HV',
                    'message' => 'Lithium ion &amp; metal batteries in compliance with Section II of PI 967 &amp; PI970',
                ),
                array(
                    'code' => 'HW',
                    'message' => 'Lithium ion &amp; metal batteries in compliance with Section II of PI 967 &amp; PI970',
                ),
            ),
            'HH' => array(
                'code' => 'HH',
                'message' => 'Dangerous Goods in Excepted Quantities X-package(s)',
            ),
            'HK' => array(
                'code' => 'HK',
                'message' => 'ID8000 Consumer commodity, Dangerous Goods as per attached DGD',
            ),
            'HY' => array(
                'code' => 'HY',
                'message' => 'UN3373 Biological substances - Category B X-package(s)',
            ),
            'HEFG' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HENFG' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HEFL' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HEFS' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HESCS' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HESDWW' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HEO' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HEOPO' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HETS' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HEC' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'HEM' => array(
                'code' => 'HE',
                'message' => 'Dangerous Goods as per attached DGD',
            ),
            'IUP' => array(
                'code' => '',
                'message' => 'Lithium ion batteries in compliance with Section II of PI967(4 cells/2 batteries or less)',
            ),
            'MUP' => array(
                'code' => '',
                'message' => 'Lithium metal batteries in compliance with Section II of PI970 (4cells/2batteries or less)',
            ),
        );

        foreach ($products as $key => $product) {
            $product_parent_id = wp_get_post_parent_id(elex_dhl_get_product_id($product['data']));
            $product_id = elex_dhl_get_product_id($product['data']);
            $post_id = $product_parent_id ? $product_parent_id : $product_id;

            $special_service_convention_code = get_post_meta($post_id, '_wf_product_special_service', true);

            if (isset($special_service_convention_code) && !empty($special_service_convention_code) && ($special_service_convention_code != 'N')) {
                $this->shipment_un_numbers[] = get_post_meta($post_id, '_wf_product_un_number', true);// Retrieving Shipment UN number of the product
                foreach ($special_service_codes_messages as $convention_key_code => $special_service_code_message) {
                    if ($convention_key_code === $special_service_convention_code) {
                        if (isset($special_service_code_message['code']) && isset($special_service_code_message['message'])) {
                            $this->special_service_warning = $special_service_code_message['message'];

                            $this->special_service_code[] = array(
                                'product_id' => $post_id,
                                'code' => $special_service_code_message['code'],
                                'warning' => $special_service_code_message['message'],
                                'default' => false,
                            );

                        } else {
                            foreach ($special_service_code_message as $code_message) {
                                if (is_array($code_message)) {
                                    $this->special_service_warning = $code_message['message'];

                                    $this->special_service_code[] = array(
                                        'product_id' => $post_id,
                                        'code' => $code_message['code'],
                                        'warning' => $code_message['message'],
                                        'default' => false,
                                    );
                                }
                            }
                        }
                    }
                }
            } else {
                if (isset($this->default_special_service_code) && !empty($this->default_special_service_code) && ($this->default_special_service_code != 'N')) {
                    $this->shipment_un_numbers[] = get_post_meta($post_id, '_wf_product_un_number', true);// Retrieving Shipment UN number of the product
                    foreach ($special_service_codes_messages as $convention_key_code => $special_service_code_message) {
                        if ($convention_key_code === $this->default_special_service_code) {
                            if (isset($special_service_code_message['code']) && isset($special_service_code_message['message'])) {
                                $this->special_service_warning = $special_service_code_message['message'];

                                $this->default_special_service_code_array[] = array(
                                    'product_id' => $post_id,
                                    'code' => $special_service_code_message['code'],
                                    'warning' => $special_service_code_message['message'],
                                    'default' => true,
                                );

                            } else {
                                foreach ($special_service_code_message as $code_message) {
                                    if (is_array($code_message)) {
                                        $this->special_service_warning = $code_message['message'];

                                        $this->default_special_service_code_array[] = array(
                                            'product_id' => $post_id,
                                            'code' => $code_message['code'],
                                            'warning' => $code_message['message'],
                                            'default' => true,
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return;
    }

    /* Function to get total of prices of order items  */
    public function get_order_items_total($package_contents){
        $order_items_total = 0;
        foreach ($package_contents as $item_key => $item_value) {
            $item_data = $item_value['data'];
            $item_price = 0;
            if($item_data) {
                 $item_price = $item_data->get_price();
            }
            if($item_price) {
                $order_items_total += $item_price;
            }
        }
        return $order_items_total;
    }

    public function get_shipper_address($package){

        $dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();
        $origin_country_name = isset(WC()->countries->countries[$this->origin_country]) ? WC()->countries->countries[$this->origin_country] : $this->origin_country;
        $shipper = array(
            'shipper_id' => $this->account_number,
            'company_name' => $this->freight_shipper_company_name,
            'registered_account' => $this->account_number,
            'address_line' => $this->freight_shipper_street,
            'address_line2' => $this->freight_shipper_street_2,
            'city' => $this->freight_shipper_city,
            'division' => $this->freight_shipper_state,
            'division_code' => $this->freight_shipper_state,
            'postal_code' => $this->origin,
            'country_code' => $this->origin_country,
            'country_name' => $origin_country_name,
            'contact_person_name' => $this->freight_shipper_person_name,
            'contact_phone_number' => $this->freight_shipper_phone_number,
            'contact_email' => $this->shipper_email,
        );

        /*There are different country codes for same country from WooCommerce and DHL. Here we are obtaining country code which is mapped to DHL for both source and destination countries*/
        $shipping_country_code = $dhl_shipping_obj->get_country_codes_mapped_for_dhl(!empty($package['origin'])? $package['origin']['country']: $this->origin_country);
        $shipping_country_name = isset(WC()->countries->countries[$shipping_country_code]) ? WC()->countries->countries[$shipping_country_code] : $shipping_country_code;

        // If package have different origin, use it instead of admin settings
        if(isset($this->settings['vendor_check']) && ($this->settings['vendor_check'] === 'yes')){
            if (isset($package['origin']) && !empty($package['origin'])) {
                // Check if vendor have atleast provided origin address
                if (isset($package['origin']['country']) && !empty($package['origin']['country'])) {
                    $shipper['company_name'] = str_replace("&", '&amp;', $package['origin']['company']);
                    $shipper['address_line'] = $package['origin']['address_1'];
                    $shipper['address_line2'] = $package['origin']['address_2'];
                    $shipper['city'] = $package['origin']['city'];
                    $shipper['division'] = $package['origin']['state'];
                    $shipper['division_code'] = $package['origin']['state'];
                    $shipper['postal_code'] = $package['origin']['postcode'];
                    $shipper['country_code'] = $shipping_country_code;
                    $shipper['country_name'] = $shipping_country_name;
                    $shipper['contact_person_name'] = $package['origin']['first_name'] . ' ' . $package['origin']['last_name'];
                    $shipper['contact_phone_number'] = $package['origin']['phone'];
                    $shipper['contact_email'] = $package['origin']['email'];
                }
            }
        }

        return $shipper;
    }

    public function get_to_address($order, $package, $destination_info = array()){
        if(empty($destination_info)){
            $destination_info = $this->get_destination_specific_data($package);
        }
        return $toaddress = array(
            'first_name' => elex_dhl_get_order_shipping_first_name($order),
            'last_name' => elex_dhl_get_order_shipping_last_name($order),
            'company' => str_replace("&", "&amp;", elex_dhl_get_order_shipping_company($order)),
            'address_1' => elex_dhl_get_order_shipping_address_1($order),
            'address_2' => elex_dhl_get_order_shipping_address_2($order),
            'city' => $destination_info['city'],
            'postcode' => $destination_info['postcode'],
            'country' => $destination_info['country_code'],
            'email' => elex_dhl_get_order_billing_email($order),
            'phone' => elex_dhl_get_order_billing_phone($order),
        );
    }

    public function get_destination_specific_data($package){
        $destination_city = htmlspecialchars(strtoupper($package['destination']['city']));
        $destination_postcode = strtoupper($package['destination']['postcode']);
        $destination_country_name = '';
        $dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();

        /* For the Sweden, DHL accepted postcode format is 999 99 */
        if($package['destination']['country'] == 'SE'){
            $postcode_part_1 = substr($package['destination']['postcode'], 0, 3);
            $postcode_part_2 = substr($package['destination']['postcode'], 3, strlen($package['destination']['country']));
            if($postcode_part_2[0] != ' '){
                $destination_postcode = $postcode_part_1.' '.$postcode_part_2;
            }
        }

        /*  According to WooCommrce The Canary Islands is a country, but according to DHL it is a part of Spain.
            If the postcodes belong to Canary Islands, we are providing country code as 'ES'
        */
        $canary_islands_postcodes = array( 35100, 35500, 35240, 35220, 35570, 35520, 35560, 35561, 35571, 35628, 35640, 35629, 35600, 35637, 35290, 35018, 35011, 35017, 35508, 35510, 35572, 35530 );

        $dhl_conflict_countries = $dhl_shipping_obj->dhl_country_codes_with_conflicts();

        foreach($dhl_conflict_countries as $dhl_conflict_country_name => $dhl_conflict_country_codes){
            if($package['destination']['country'] == $dhl_conflict_country_codes['Woocommerce_country_code']){
                $destination_country_name = htmlentities($dhl_conflict_country_name, ENT_COMPAT, 'UTF-8');;
                break;
            }

            if(empty($destination_country_name)){
                if($package['destination']['country'] == $dhl_conflict_country_codes['dhl_country_code']){
                    $destination_country_name = htmlentities($dhl_conflict_country_name, ENT_COMPAT, 'UTF-8');
                    break;
                }
            }
        }

        if(empty($destination_country_name)){
            if($this->settings['latin_encoding'] == 'yes'){
                $destination_country_name = isset(WC()->countries->countries[$package['destination']['country']]) ? WC()->countries->countries[$package['destination']['country']] : $package['destination']['country'];
            }else{
                $destination_country_name = htmlentities((isset(WC()->countries->countries[$package['destination']['country']]) ? WC()->countries->countries[$package['destination']['country']] : $package['destination']['country']), ENT_COMPAT, 'UTF-8');   
            }
        }

        $destination_country_code = $dhl_shipping_obj->get_country_codes_mapped_for_dhl($package['destination']['country']);
        $destination_country_code = in_array($destination_postcode, $canary_islands_postcodes)? 'IC': $destination_country_code;

        return array('city' => $destination_city, 'postcode' => $destination_postcode, 'country_name' => $destination_country_name, 'country_code' => $destination_country_code);
    }

    private function get_dhl_requests($dhl_packages, $package, $reference_id='') {
        $order = $this->order;

        if (!$order) {
            return;
        }

        $orderid = elex_dhl_get_order_id($order);

        update_post_meta($orderid, 'shipment_content_express_dhl_elex', $this->label_contents_text);

        $dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();

        if($this->is_woocommerce_multi_currency_installed){
            $custom_currency_data = $dhl_shipping_obj->get_exchange_rate_multicurrency_woocommerce($order->get_currency());
        }

        // Time is modified to avoid date diff with server.
        $mailingDate = date('Y-m-d', current_time('timestamp')) . 'T' . date('H:i:s', current_time('timestamp'));
        $shipping_country_name = '';

        $dhl_conflict_countries = $dhl_shipping_obj->dhl_country_codes_with_conflicts();
        foreach($dhl_conflict_countries as $dhl_conflict_country_name => $dhl_conflict_country_codes){
            if(!empty($package['origin'])){
                if($package['origin']['country'] == $dhl_conflict_country_codes['Woocommerce_country_code']){
                    $shipping_country_name = $dhl_conflict_country_name;
                    break;
                }

                if(empty($shipping_country_name)){
                    if($package['origin']['country'] == $dhl_conflict_country_codes['dhl_country_code']){
                        $shipping_country_name = $dhl_conflict_country_name;
                        break;
                    }
                }
            }else{
                if($this->origin_country == $dhl_conflict_country_codes['Woocommerce_country_code']){
                    $shipping_country_name = $dhl_conflict_country_name;
                    break;
                }
            }
        }

        if(empty($shipping_country_name)){
            if(!empty($package['origin'])){
                $shipping_country_name = htmlentities((isset(WC()->countries->countries[$package['origin']['country']]) ? WC()->countries->countries[$package['origin']['country']] : $package['origin']['country']), ENT_COMPAT, 'UTF-8');
            }else{
                $shipping_country_name = htmlentities((isset(WC()->countries->countries[$this->origin_country]) ? WC()->countries->countries[$this->origin_country] : $this->origin_country), ENT_COMPAT, 'UTF-8');
            }
        }

        $consignee_name = htmlspecialchars(elex_dhl_get_order_shipping_first_name($this->order) . ' ' . elex_dhl_get_order_shipping_last_name($this->order));
        $total_after_discount = $this->order->get_subtotal() - $this->order->get_total_discount();
        $order_subtotal =  $total_after_discount;
        $order_currency = elex_dhl_get_order_currency($this->order);
        $cod_order_total = wc_format_decimal($this->order->get_total(), 2, true);

        //If vendor country set, then use vendor address
        if(isset($this->settings['vendor_check']) && ($this->settings['vendor_check'] === 'yes')){
            if(isset($package['origin']) && isset($package['origin']['country'])){
                $this->origin_country_1         =   $package['origin']['country'];
                $this->origin                   =   $package['origin']['postcode'];
            }
        }

        // For multi-vendor cases
        if(isset($this->settings['vendor_check']) && $this->settings['vendor_check'] === 'yes'){
            $is_dutiable = ($package['destination']['country'] == $this->origin_country_1 || wf_dhl_is_eu_country($this->origin_country_1, $package['destination']['country'])) ? "N" : "Y";
        }else{
            $is_dutiable = ($package['destination']['country'] == $this->origin_country || wf_dhl_is_eu_country($this->origin_country, $package['destination']['country'])) ? "N" : "Y";
        }
        if(isset($this->settings['dutypayment_type']) && $this->settings['dutypayment_type'] == '') {
            $is_dutiable = 'N';
        }
       
        $shipping_service_rates = get_post_meta($orderid,'_wf_dhl_available_services',true);
        $shipping_monetary_value = $this->is_woocommerce_multi_currency_installed? ($custom_currency_data['exchange_rate'] * ($shipping_service_rates[$this->service_code]['cost'] * $this->conversion_rate)): $shipping_service_rates[$this->service_code]['cost'] * $this->conversion_rate;
        if($dhl_shipping_obj->aelia_activated){
            $shipping_monetary_value = apply_filters('wc_aelia_cs_convert', $shipping_monetary_value, $dhl_shipping_obj->shop_currency, $order->get_currency());
        }
        $add_shipping_cost = apply_filters('disable_shipping_cost_shipping_label_elex_dhl_express', true);

        $bulk_create_shipment = get_option('create_bulk_orders_shipment');
        $orders_with_no_default_shipment_service = '';

        if($bulk_create_shipment){
            if($package['destination']['country'] == $this->origin_country){
                if(get_option('dhl_shipping_service_selected') == "no"){
                    if($this->settings['default_domestic_service'] != 'none'){
                        $this->service_code = $this->settings['default_domestic_service'];
                        update_option("default_shipment_service", 'yes');
                    }
                    else{
                        update_option("default_shipment_service", 'no');
                        $orders_with_no_default_shipment_service = $orderid;
                    }
                }
            }else{
                if(get_option('dhl_shipping_service_selected') == "no"){
                    if($this->settings['default_international_service'] != 'none'){
                        $this->service_code = $this->settings['default_international_service'];
                        update_option('default_shipment_service', 'yes');
                    }
                    else{
                        update_option("default_shipment_service", 'no');
                        $orders_with_no_default_shipment_service = $orderid;
                    }
                }
            }
            delete_option('dhl_shipping_service_selected');
            
            $stored_ordered_ids_with_no_default_shipment_service = get_option('orders_with_no_default_shipment_service_exp_dhl_elex');
            if(!empty($orders_with_no_default_shipment_service)){
                $stored_ordered_ids_with_no_default_shipment_service .= $orders_with_no_default_shipment_service. ',';
            }
            update_option("orders_with_no_default_shipment_service_exp_dhl_elex", $stored_ordered_ids_with_no_default_shipment_service);
        }
        

        $this->find_special_service_products($package['contents']);

        $shipment_details = $this->wf_get_shipment_details($dhl_packages, $is_dutiable, 'shipment');
        $origin_country_name = isset(WC()->countries->countries[$this->origin_country]) ? WC()->countries->countries[$this->origin_country] : $this->origin_country;

        $special_service = "";

        //signature option
        $signature_option = $this->get_package_signature($package['contents'], $orderid);

        if(!isset($this->settings['receiver_duty_payment_type']) || empty($this->settings['receiver_duty_payment_type'])){
            $this->settings['receiver_duty_payment_type'] = 'DAP';
        }

        if ($signature_option != 'SX') {
            // SX for no signature required.
            $special_service .= "<SpecialService><SpecialServiceType>" . $signature_option . "</SpecialServiceType></SpecialService>";
        }

        if (isset($_GET['cash_on_delivery']) && $_GET['cash_on_delivery'] === 'true') {
            $special_service .= "<SpecialService><SpecialServiceType>KB</SpecialServiceType><ChargeValue>" . $cod_order_total . "</ChargeValue><CurrencyCode>" . $order_currency . "</CurrencyCode></SpecialService>";
        }

        $customer_insurance = get_post_meta($orderid, 'wf_dhl_insurance', true);

        if ($customer_insurance == 'yes') {
            $special_service .= "<SpecialService><SpecialServiceType>II</SpecialServiceType></SpecialService>";
        }

        if ($is_dutiable == "Y" && $this->dutypayment_type == "S") {
            $special_service .= "<SpecialService><SpecialServiceType>DD</SpecialServiceType></SpecialService>";
        } elseif ($is_dutiable == "Y" && $this->dutypayment_type == "R") {
            if( ! $this->settings['receiver_duty_payment_type'] == 'DAP') {
                $special_service .= "<SpecialService><SpecialServiceType>DS</SpecialServiceType></SpecialService>";
            }
        }

        /* Sending special service code if the product belongs to restricted commodities or dangerous goods*/
        if (!empty($this->special_service_code)) {
            $special_service_codes = $this->special_service_codes_unique($this->special_service_code);
            foreach ($special_service_codes as $service_code) {
                if ($service_code['code'] != '') {
                    $special_service .= "<SpecialService><SpecialServiceType>" . $service_code['code'] . "</SpecialServiceType></SpecialService>";
                }
            }
        }

        /* Sending default special service code if the product belongs to restricted commodities or dangerous goods with default type*/
        if (!empty($this->default_special_service_code_array)) {
            $default_special_service_codes = $this->special_service_codes_unique($this->default_special_service_code_array);
            if(is_array($default_special_service_codes) && !empty($default_special_service_codes)){
                foreach ($default_special_service_codes as $service_code) {
                    if ($service_code['code'] != '') {
                        $special_service .= "<SpecialService><SpecialServiceType>" . $service_code['code'] . "</SpecialServiceType></SpecialService>";
                    }
                }
            }
        }

        if (isset($_GET['sat_delivery']) && $_GET['sat_delivery'] === 'true') {
            $sat_delivery_val = ($is_dutiable != 'Y') ? 'AG' : 'AA';
            $special_service .= "<SpecialService><SpecialServiceType>$sat_delivery_val</SpecialServiceType></SpecialService>";
        }

        $shipping_company = elex_dhl_get_order_shipping_company($this->order);
        $consignee_companyname = substr(htmlspecialchars(!empty($shipping_company) ? $shipping_company : $consignee_name), 0, 35);

        $dutypayment_type_accountnumber = "";
        if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
            $dutypayment_type_accountnumber = "<DutyPaymentType>{$this->dutypayment_type}</DutyPaymentType>";
            if (!empty($this->dutyaccount_number) && $this->dutypayment_type == 'T') {
                $dutypayment_type_accountnumber .= "<DutyAccountNumber>{$this->dutyaccount_number}</DutyAccountNumber>";
            }
        }

        $shipper = $this->get_shipper_address( $package );

        $destination_info = $this->get_destination_specific_data($package);

        $toaddress = $this->get_to_address( $order, $package, $destination_info );

        $this->packages_dhl = $dhl_packages;
        $this->dhl_package_shipper = $shipper;
        $this->dhl_package_to_address = $toaddress;
        $archive_ref = array(
            'airway bill number' => '',
        );

        $RequestArchiveDoc = '';
        $docImage = '';
        
        if ($this->plt && $is_dutiable == 'Y') {
            update_option('PLT_enabled_express_dhl_elex', true);
            $sample_base64_encoded_pdf = $this->generate_commercial_invoice($orderid, $dhl_packages, $shipper, $toaddress, $document_type = 'commercial');
            $special_service .= "<SpecialService><SpecialServiceType>WY</SpecialServiceType></SpecialService>";
                           
            if (isset($sample_base64_encoded_pdf) && !empty($sample_base64_encoded_pdf)) {
                $docImage = "<DocImages>
                                <DocImage>
                                    <Type>CIN</Type>
                                    <Image>$sample_base64_encoded_pdf</Image>
                                    <ImageFormat>PDF</ImageFormat>
                                </DocImage>
                            </DocImages>";
            }
        }

        $address = $this->get_valid_address(elex_dhl_get_order_shipping_address_1($this->order), elex_dhl_get_order_shipping_address_2($this->order));

        $destination_address = '<AddressLine>' . htmlspecialchars($address['valid_line1']) . '</AddressLine>';
        if (!empty($address['valid_line2'])) {
            $destination_address .= '<AddressLine>' . htmlspecialchars($address['valid_line2']) . '</AddressLine>';
        }

        if (!empty($address['valid_line3'])) {
            $destination_address .= '<AddressLine>' . htmlspecialchars($address['valid_line3']) . '</AddressLine>';
        }

        $current_order_items = $order->get_items();

        $export_declaration = '';
         {

            $export_line_item = '';
            $order_dutiable_amount = 0;
            //Obtaining discounted prices of the products
            foreach ($package['contents'] as $i => $item) {
                $item_value = 0;
                foreach($current_order_items as $current_order_item){
                    if(WC()->version < '2.7.0'){
                        if($current_order_item['variation_id'] != 0){
                            if($item['data']->get_id() == $current_order_item['variation_id']){
                                $item_value = $current_order_item['line_total'];
                            }
                        }else if($item['data']->get_id() == $current_order_item['product_id']){
                                $item_value = $current_order_item['line_total'];   
                        }
                    }else{
                        $current_order_item_data = $current_order_item->get_data();
                        if($current_order_item_data['variation_id'] != 0){
                            if($item['data']->get_id() == $current_order_item_data['variation_id']){
                                $item_value = $current_order_item_data['total'];
                            }
                        }else if($item['data']->get_id() == $current_order_item_data['product_id']){
                                $item_value = $current_order_item_data['total'];   
                        }
                    }
                }
                
                $item_unit_value = wc_format_decimal( $item_value/$item['quantity'], 2, false);
                $order_dutiable_amount += ($item_unit_value * $item['quantity']);

                $export_line_item .= '<ExportLineItem>';
                $export_line_item .= '  <LineNumber>' . ++$i . '</LineNumber>';
                $export_line_item .= '  <Quantity>' . $item['quantity'] . '</Quantity>';
                $export_line_item .= '  <QuantityUnit>'. $this->invoice_quantity_unit .'</QuantityUnit>'; //not sure about this value
                $export_line_item .= '  <Description>' . substr(htmlspecialchars($item['data']->get_title()), 0, 75) . '</Description>';
                $export_line_item .= '  <Value>' . $item_unit_value . '</Value>';

                $par_id = wp_get_post_parent_id(elex_dhl_get_product_id($item['data']));
                $post_id = $par_id ? $par_id : elex_dhl_get_product_id($item['data']);

                $wf_hs_code = get_post_meta($post_id, '_wf_hs_code', 1); //this works for variable product also
                $manufacturing_country = get_post_meta($post_id, '_wf_manufacture_country', 1);

                if (!empty($wf_hs_code)) {
                    $export_line_item .= '  <CommodityCode>' . $wf_hs_code . '</CommodityCode>';
                }

                $xa_send_dhl_weight = $item['data']->get_weight();
                if ($this->weight_unit == 'LBS') {
                    if ($xa_send_dhl_weight < 0.12) {
                        $xa_send_dhl_weight = 0.12; // 0.12 lbs, minimum product for DHL
                    } else {
                        $xa_send_dhl_weight = (float) $xa_send_dhl_weight;
                    }
                } else {
                    if ($xa_send_dhl_weight < 0.01) {
                        $xa_send_dhl_weight = 0.01; // 0.12 lbs, minimum product for DHL
                    } else {
                        $xa_send_dhl_weight = (float) $xa_send_dhl_weight;
                    }
                }

                if($this->shop_weight_unit != $this->weight_unit){
                    $xa_send_dhl_weight = round(wc_get_weight($xa_send_dhl_weight, $this->weight_unit, $this->shop_weight_unit), 3);
                }

                $xa_send_dhl_weight = (string) $xa_send_dhl_weight;
                $xa_send_dhl_weight = str_replace(',', '.', $xa_send_dhl_weight);
                $export_line_item .= '  <Weight><Weight>' . round($xa_send_dhl_weight, 3) . '</Weight><WeightUnit>' . $this->product_weight_unit . '</WeightUnit></Weight><GrossWeight><Weight>' . round($xa_send_dhl_weight * $item['quantity'], 3) . '</Weight><WeightUnit>' . $this->product_weight_unit . '</WeightUnit></GrossWeight>';

                $export_line_item .= '<ManufactureCountryName>'.$manufacturing_country.'</ManufactureCountryName></ExportLineItem>';
            }
            $order_dutiable_amount = wc_format_decimal( $order_dutiable_amount, 2, false);
            $dutiable_content = "<Dutiable><DeclaredValue>{$order_dutiable_amount}</DeclaredValue><DeclaredCurrency>{$order_currency}</DeclaredCurrency>";


            if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
                if ($this->dutypayment_type == "S") {
                    $dutiable_content .= "<TermsOfTrade>DDP</TermsOfTrade>";
                } elseif ($this->dutypayment_type == "R") {
                    $dutiable_content .= "<TermsOfTrade>".$this->settings['receiver_duty_payment_type']."</TermsOfTrade>";
                }
            }
            $dutiable_content .= "</Dutiable>";

            $export_declaration = '<ExportDeclaration>';
            $exporter_id_code = '';
            $use_dhl_invoice = '';
            $freight_insurance_charges = '';
            if(isset($this->settings['classic_commercial_invoice']) && $this->settings['classic_commercial_invoice'] == 'default') {
                 $use_dhl_invoice .= '<UseDHLInvoice>Y</UseDHLInvoice>
                <DHLInvoiceLanguageCode>'. $this->invoice_language_code .'</DHLInvoiceLanguageCode>
                <DHLInvoiceType>'. $this->settings['invoice_type'] .'</DHLInvoiceType>';
                if($this->settings['export_reason']) {
                    $export_declaration .= '<ExportReason>'.$this->settings['export_reason'].'</ExportReason>';
                }
                if($this->settings['type_of_export']) {
                    $export_declaration .= '<ExportReasonCode>'.$this->settings['type_of_export'].'</ExportReasonCode>';
                }
                $export_declaration .= '<InvoiceNumber>'. $this->order_id .'</InvoiceNumber>
                <InvoiceDate>'. date('Y-m-d', current_time('timestamp')) .'</InvoiceDate>';

                $exporter_id_code = '<ExporterId>'.$this->settings['exporter_id'].'</ExporterId>
                <ExporterCode>'.$this->settings['exporter_code'].'</ExporterCode>';

                $shipping_methods = $order->get_shipping_methods();
                $insurance_cost = 0;
                $extra_charge = 0;
                foreach ($shipping_methods as $key1 => $shipping_methods_details) {
                    foreach ($shipping_methods_details->get_meta_data() as $key2 => $shipping_meta_details) {
                       if($shipping_meta_details->key == 'Insurance Charge') {
                            $insurance_cost = $shipping_meta_details->value;
                       }
                       if($shipping_meta_details->key == 'Extra Charge') {
                            $extra_charge = $shipping_meta_details->value;
                       }
                    }
                    
                }
                if($this->settings['include_freight_cost'] == 'yes') {
                    $freight_insurance_charges .= '<OtherCharges2>'.$shipping_monetary_value.'</OtherCharges2>';
                }

                if($this->settings['include_insurance_cost'] == 'yes') {
                    $freight_insurance_charges .= '<OtherCharges3>'.$insurance_cost.'</OtherCharges3>';
                }

            }
            else {
                $export_declaration .= '<InvoiceNumber>'. $this->order_id .'</InvoiceNumber>
                <InvoiceDate>'. date('Y-m-d', current_time('timestamp')) .'</InvoiceDate>';
            }
            
            $billing_country_name = isset(WC()->countries->countries[elex_dhl_get_order_billing_country( $order )]) ? WC()->countries->countries[elex_dhl_get_order_billing_country( $order )] : elex_dhl_get_order_billing_country( $order );

            $export_declaration .= '<BillToCompanyName>'.elex_dhl_get_order_billing_company( $order ).'</BillToCompanyName>
            <BillToContanctName>'.elex_dhl_get_order_billing_first_name( $order ).' '.elex_dhl_get_order_billing_last_name( $order ).'</BillToContanctName>
            <BillToAddressLine>'.elex_dhl_get_order_billing_address_1( $order ).' '.elex_dhl_get_order_billing_address_2( $order ).'</BillToAddressLine>
            <BillToCity>'.elex_dhl_get_order_billing_city( $order ).'</BillToCity>
            <BillToPostcode>'.elex_dhl_get_order_billing_postcode( $order ).'</BillToPostcode>
            <BillToSuburb></BillToSuburb>
            <BillToState>'.elex_dhl_get_order_billing_state( $order ).'</BillToState>
            <BillToCountryName>'.$billing_country_name.'</BillToCountryName>
            <BillToPhoneNumber>'.elex_dhl_get_order_billing_phone( $order ).'</BillToPhoneNumber>
            <Remarks>'.$this->label_comments_text.'</Remarks>
            '. $freight_insurance_charges . $exporter_id_code . $export_line_item . '</ExportDeclaration>';
            }

        $billing_phone = elex_dhl_get_order_billing_phone($this->order);
        $billing_email = elex_dhl_get_order_billing_email($this->order);

        $archive_bill_settings = isset($this->settings['request_archive_airway_label']) ? $this->settings['request_archive_airway_label'] : '';
        $number_of_bills = isset($this->settings['no_of_archive_bills']) ? $this->settings['no_of_archive_bills'] : '';
        $number_of_bills_xml = '';
        $dhl_email_enable = isset($this->settings['dhl_email_notification_service']) ? $this->settings['dhl_email_notification_service'] : '';
        $dhl_email_message = isset($this->settings['dhl_email_notification_message']) ? $this->settings['dhl_email_notification_message'] : '';
        $dhl_notification = '';

        $customer_logo_url = isset($this->settings['customer_logo_url']) ? $this->settings['customer_logo_url'] : '';
        $customer_logo_xml = '';

        if (!empty($customer_logo_url) && @file_get_contents($customer_logo_url)) {
            $type = pathinfo($customer_logo_url, PATHINFO_EXTENSION);
            $data = file_get_contents($customer_logo_url);
            $base64 = base64_encode($data);
            $customer_logo_xml = '<CustomerLogo><LogoImage>' . $base64 . '</LogoImage><LogoImageFormat>' . strtoupper($type) . '</LogoImageFormat></CustomerLogo>';
        }

        if ($this->add_trackingpin_shipmentid == 'yes' && !empty($dhl_email_enable) && $dhl_email_enable === 'yes') {
            $dhl_notification = '<Notification><EmailAddress>' . $toaddress['email'] . '</EmailAddress><Message>' . $dhl_email_message . '</Message></Notification>';
        }

        if (!empty($archive_bill_settings) && $archive_bill_settings === 'yes') {
            $request_archive_airway_bill = 'Y';
        } else {
            $request_archive_airway_bill = 'N';
        }

        if (empty($number_of_bills) && $request_archive_airway_bill === 'Y') {
            $number_of_bills_xml = '<NumberOfArchiveDoc>1</NumberOfArchiveDoc>';
        }

        if (!empty($number_of_bills) && $request_archive_airway_bill === 'Y') {
            $number_of_bills_xml = '<NumberOfArchiveDoc>' . $number_of_bills . '</NumberOfArchiveDoc>';
        }

        $shipping_reference = apply_filters("add_shipping_reference_express_dhl_elex", $this->order->get_order_number(), $consignee_companyname);//Sending consignee's company name as per customers' requirement
        if($reference_id) {
            $shipping_reference .= '-'.$reference_id; //For switzerland(splitted packages)
        }
        $switch_account_number_action_input = array('account_number' => $this->settings['account_number'], 'source_country_code' => $shipper['country_code']);
        $switch_account_number_action_result = apply_filters('switch_account_number_action', $switch_account_number_action_input, $this->settings['dutypayment_country']);
        $this->account_number = isset($switch_account_number_action_result['payment_account_number'])? $switch_account_number_action_result['payment_account_number']: $switch_account_number_action_result['account_number'];
        $message_reference_num = elex_dhl_generate_random_message_reference();

        $shipper_address_enhance = "<AddressLine>{$shipper['address_line']}</AddressLine>
        <AddressLine>{$shipper['address_line2']}</AddressLine>";

        $vat_number = '';
        if($this->settings['include_shipper_vat_number'] == 'yes'){
            $vat_number = $this->settings['shipper_vat_number'];
        }
        $eori_no = '';
        if(isset($this->settings['eori_no'])){
            $eori_no = $this->settings['eori_no'];
        }

        $elex_dhl_version = ELEX_DHL_SOFTWARE_VERSION;
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req-6.2.xsd" schemaVersion="6.2">
    <Request>
        <ServiceHeader>
            <MessageTime>{$mailingDate}</MessageTime>
            <MessageReference>{$message_reference_num}</MessageReference>
            <SiteID>{$this->site_id}</SiteID>
            <Password>{$this->site_password}</Password>
        </ServiceHeader>
        <MetaData>
            <SoftwareName>WooCommerce DHL Express Plugin</SoftwareName>
            <SoftwareVersion>{$elex_dhl_version}</SoftwareVersion>
        </MetaData>
    </Request>
    <RegionCode>{$this->region_code}</RegionCode>
    <RequestedPickupTime>Y</RequestedPickupTime>
    <LanguageCode>en</LanguageCode>
    <PiecesEnabled>Y</PiecesEnabled>
    <Billing>
        <ShipperAccountNumber>{$this->account_number}</ShipperAccountNumber>
        <ShippingPaymentType>S</ShippingPaymentType>
        <BillingAccountNumber>{$this->account_number}</BillingAccountNumber>
        {$dutypayment_type_accountnumber}
    </Billing>
    <Consignee>
        <CompanyName>{$consignee_companyname}</CompanyName>
        {$destination_address}
        <City>{$destination_info['city']}</City>
        <PostalCode>{$destination_info['postcode']}</PostalCode>
        <CountryCode>{$destination_info['country_code']}</CountryCode>
        <CountryName>{$destination_info['country_name']}</CountryName>
        <Contact>
            <PersonName>{$consignee_name}</PersonName>
            <PhoneNumber>{$billing_phone}</PhoneNumber>
            <Email>{$billing_email}</Email>
        </Contact>
    </Consignee>
    <Commodity>
        <CommodityCode>cc</CommodityCode>
        <CommodityName>cn</CommodityName>
    </Commodity>
    {$dutiable_content}
    {$use_dhl_invoice}
    {$export_declaration}
    <Reference>
        <ReferenceID>{$shipping_reference}</ReferenceID>
    </Reference>
    {$shipment_details}
    <Shipper>
        <ShipperID>{$shipper['shipper_id']}</ShipperID>
        <CompanyName>{$shipper['company_name']}</CompanyName>
        <RegisteredAccount>{$shipper['registered_account']}</RegisteredAccount>
        {$shipper_address_enhance}
        <City>{$shipper['city']}</City>
        <Division>{$shipper['division']}</Division>
        <PostalCode>{$shipper['postal_code']}</PostalCode>
        <CountryCode>{$shipper['country_code']}</CountryCode>
        <CountryName>{$shipper['country_name']}</CountryName>
        <FederalTaxId>{$vat_number}</FederalTaxId>
        <EORI_No>{$eori_no}</EORI_No>
        <Contact>
            <PersonName>{$shipper['contact_person_name']}</PersonName>
            <PhoneNumber>{$shipper['contact_phone_number']}</PhoneNumber>
            <Email>{$shipper['contact_email']}</Email>
        </Contact>
    </Shipper>
    {$special_service}
    {$dhl_notification}
    {$docImage}
    <LabelImageFormat>{$this->image_type}</LabelImageFormat>
    <RequestArchiveDoc>{$request_archive_airway_bill}</RequestArchiveDoc>
    {$number_of_bills_xml}
    {$RequestArchiveDoc}
    <Label>
        <HideAccount>Y</HideAccount>
        <LabelTemplate>{$this->output_format}</LabelTemplate>
        {$customer_logo_xml}
    </Label>
</req:ShipmentRequest>
XML;
        $xmlRequest = apply_filters('wf_dhl_label_request', $xmlRequest, $this->order_id);

        return $xmlRequest;
    }

    /**
    * function to obtain unique array of Special Service codes the products belong to Dangerous goods
    */
    private function special_service_codes_unique($special_service_code_array){
        $unique_special_codes_array = array();
        foreach($special_service_code_array as $special_service_code_element){
            if(!empty($unique_special_codes_array)){
                foreach($unique_special_codes_array as $unique_special_codes_array_element){
                    if($unique_special_codes_array_element['code'] != $special_service_code_element['code']){
                        $unique_special_codes_array[] = $special_service_code_element;
                    }
                }
            }else{
                $unique_special_codes_array[] = $special_service_code_element;
            }
        }
        return $unique_special_codes_array;
    }

    function wf_pickup_request_handler($order) {
        $order_id = $order->get_id();
        $airwaybill_number = get_post_meta($order_id, 'wf_woo_dhl_shipmentId', '');
        $pickup_number = array();
        foreach ($airwaybill_number as $index => $value) {
            
            $output = $this->wf_pickup_request($order,$index);
            $create_pickup_api = '';

            if ($this->production) {
                $create_pickup_api = 'https://xmlpi-ea.dhl.com/XMLShippingServlet';
            } else {
                $create_pickup_api = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';
            }

            $result = wp_remote_post($create_pickup_api, array(
                'method' => 'POST',
                'timeout' => 70,
                'sslverify' => 0,
                //'headers'       => $this->wf_get_request_header('application/vnd.cpc.shipment-v7+xml','application/vnd.cpc.shipment-v7+xml'),
                'body' => $output,
            )
            );
            //'https://xmlpitest-ea.dhl.com/XMLShippingServlet'


            $this->debug('DHL REQUEST: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($output, true), ENT_IGNORE) . '</pre>');
            $this->debug('DHL RESPONSE: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($result, true), ENT_IGNORE) . '</pre>');

            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
                $this->debug('DHL WP ERROR: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r(htmlspecialchars($error_message), true) . '</pre>');
            } elseif (is_array($result) && !empty($result['body'])) {
                $result = $result['body'];
            } else {
                $result = '';
            }

            $order_id = elex_dhl_get_order_id($order);
            libxml_use_internal_errors(true);
            if(is_string($result)){
                $result = utf8_encode($result);
            }

            $xml = simplexml_load_string($result);
            $ConfirmationNumber = '';
            
            if (isset($xml->Note->ActionNote) && $xml->Note->ActionNote == 'Success') {
                $ConfirmationNumber = isset($xml->ConfirmationNumber) ? (String) $xml->ConfirmationNumber : '';
                $NextPickupDate = isset($xml->NextPickupDate) ? (String) $xml->NextPickupDate : '';
                $ReadyByTime = isset($xml->ReadyByTime) ? (String) $xml->ReadyByTime : '';
                $pickup_number[] = array("pickup_confirmation_number" => $ConfirmationNumber, "next_pickup_date" => $NextPickupDate, "ready_by_time" => $ReadyByTime);
                update_post_meta($order_id, '_wf_dhl_pickup_shipment', $pickup_number);
                update_post_meta($order_id, '_wf_dhl_pickup_shipment_error', '');
            } else if (isset($xml->Response->Status->ActionStatus) && $xml->Response->Status->ActionStatus == 'Error') {
                if ($xml->Response->Status && (string) $xml->Response->Status->Condition->ConditionCode != '') {
                    $error_msg = ((string) $xml->Response->Status->Condition->ConditionCode) . ' : ' . ((string) $xml->Response->Status->Condition->ConditionData);
                    update_post_meta($order_id, '_wf_dhl_pickup_shipment_error', $error_msg);
                    update_post_meta($order_id, '_wf_dhl_pickup_shipment', array());
                }
            }           
        }

        $packages = array();
        $packages = array_values($this->wf_get_package_from_order($order));

        $dhl_packages = array();
        $dhl_packages = get_post_meta( $order_id, '_wf_dhl_stored_packages', true );

        if (!$dhl_packages && !empty($packages)) {
            foreach ($packages as $key => $package) {
                $dhl_packages[] = $this->get_dhl_packages($package);
            }
        }

        $packages_contents = $packages[0];
        $shipper = $this->get_shipper_address($packages_contents);
        $destination_info = $this->get_destination_specific_data($packages_contents);
        $toaddress = $this->get_to_address( $order, $packages_contents, $destination_info );
        $archive_ref = get_post_meta($order_id, 'archive_reference_data_dhl_elex', true);
        $shipmentId = $archive_ref['airway bill number'];
        $commercial_invoice = $this->generate_commercial_invoice($order_id, $dhl_packages, $shipper, $toaddress, $document_type = 'commercial', $archive_ref, $ConfirmationNumber);

        update_post_meta($order_id, 'wf_woo_dhl_shipping_commercialInvoice_'.$shipmentId, $commercial_invoice);

        if ($this->debug) {
            echo '<a href="' . admin_url('/post.php?post=' . $order_id . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>';
            //For the debug information to display in the page
            die();
        }
    }

    private function wf_pickup_request($order, $index) {
        $order_id = elex_dhl_get_order_id($order);
        $airwaybill_number = get_post_meta($order_id, 'wf_woo_dhl_shipmentId', '');
        $dhl_packages = get_post_meta($order_id, 'wf_woo_dhl_package_' . $airwaybill_number[$index], array());
        $package = $this->wf_get_package_from_order($order);
        $keys = array_keys($package);
        $key = $keys[$index];
        //Multi-vendor
        if(isset($package[$key]['origin'])) {
            $this->freight_shipper_person_name  = $package[$key]['origin']['first_name'].' '.$package[$key]['origin']['last_name'];
            $this->freight_shipper_phone_number = $package[$key]['origin']['phone'];
            $this->freight_shipper_company_name = $package[$key]['origin']['company'];
            $this->freight_shipper_street       = $package[$key]['origin']['address_1'];
            $this->freight_shipper_street_2     = $package[$key]['origin']['address_2'];
            $this->freight_shipper_city         = $package[$key]['origin']['city'];
            $this->freight_shipper_state        = $package[$key]['origin']['state'];
            $this->origin_country               = $package[$key]['origin']['country'];
            $this->origin                       = $package[$key]['origin']['postcode'];
        }

        $weight = 0;
        $pieces = 0;
        if ($dhl_packages) {
            foreach ($dhl_packages[0] as $key => $parcel) {
                foreach ($parcel as $key => $value) {
                    if (isset($value['Weight'])) {
                        $weight_value = $weight + $value['Weight']['Value'];
                        $weight = round($weight_value, 3);
                        $pieces = $pieces + 1;
                    }
                    if (isset($value[0]['Weight'])) {
                        $weight_value = $weight + $value[0]['Weight']['Value'];
                        $weight = round($weight_value, 3);
                        $pieces = $pieces + 1;
                    }
                }
            }
        }

        if (!empty($airwaybill_number) && !empty($dhl_packages)) {
            //$mailingDate = date('Y-m-d', time() + $this->timezone_offset) . 'T' . date('H:i:s', time() + $this->timezone_offset);
            $dhl_woo_ship = new wf_dhl_woocommerce_shipping_method();
            $mailingDate = date('Y-m-d', current_time('timestamp')) . 'T' . date('H:i:s', current_time('timestamp'));

            $pickup_date = empty($this->pickupdate)? date('Y-m-d', strtotime("+0 days", current_time('timestamp'))) : date('Y-m-d', strtotime("+" . $this->pickupdate . " days", current_time('timestamp')));
            $pickup_day = date('D', strtotime($pickup_date));
            $pickup_date = $dhl_woo_ship->elex_dhl_get_mailing_date($pickup_date, $pickup_day);
            $message_reference_num = elex_dhl_generate_random_message_reference();

            $xmlRequest = <<<XML
            <req:BookPURequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="http://www.dhl.com book-pickup-global-req.xsd" schemaVersion="1.0">
                <Request>
                    <ServiceHeader>
                        <MessageTime>{$mailingDate}</MessageTime>
                        <MessageReference>{$message_reference_num}</MessageReference>
                        <SiteID>{$this->site_id}</SiteID>
                        <Password>{$this->site_password}</Password>
                    </ServiceHeader>
                </Request>
                <RegionCode>{$this->region_code}</RegionCode>
                <Requestor>
                    <AccountType>D</AccountType>
                    <AccountNumber>{$this->account_number}</AccountNumber>
                    <RequestorContact>
                        <PersonName>{$this->freight_shipper_person_name}</PersonName>
                        <Phone>{$this->freight_shipper_phone_number}</Phone>
                    </RequestorContact>
                    <CompanyName>{$this->freight_shipper_company_name}</CompanyName>
                </Requestor>
                <Place>
                    <LocationType>B</LocationType>
                    <CompanyName>{$this->freight_shipper_company_name}</CompanyName>
                    <Address1>{$this->freight_shipper_street}</Address1>
                    <Address2>{$this->freight_shipper_street_2}</Address2>
                    <PackageLocation>{$this->freight_shipper_city}</PackageLocation>
                    <City>{$this->freight_shipper_city}</City>
                    <DivisionName>{$this->freight_shipper_state}</DivisionName>
                    <CountryCode>{$this->origin_country}</CountryCode>
                    <PostalCode>{$this->origin}</PostalCode>
                    <Suburb>England</Suburb>
                </Place>
                <Pickup>
                    <PickupDate>{$pickup_date}</PickupDate>
                    <ReadyByTime>{$this->pickupfrom}</ReadyByTime>
                    <CloseTime>{$this->pickupto}</CloseTime>
                    <Pieces>{$pieces}</Pieces>
                    <weight>
                        <Weight>{$weight}</Weight>
                        <WeightUnit>{$this->product_weight_unit}</WeightUnit>
                    </weight>
                </Pickup>
                <PickupContact>
                    <PersonName>{$this->pickupperson}</PersonName>
                    <Phone>{$this->pickupcontct}</Phone>
                </PickupContact>
                <ShipmentDetails>
                    <AccountType>D</AccountType>
                    <AccountNumber>{$this->user_settings['account_number']}</AccountNumber>
                    <AWBNumber>{$airwaybill_number[0]}</AWBNumber>
                    <NumberOfPieces>{$pieces}</NumberOfPieces>
                    <Weight>{$weight}</Weight>
                    <WeightUnit>{$this->labelapi_weight_unit}</WeightUnit>
                    <DoorTo>DD</DoorTo>
                    <DimensionUnit>{$this->labelapi_dimension_unit}</DimensionUnit>
                    <Pieces>
                        <Weight>{$weight}</Weight>
                    </Pieces>
                </ShipmentDetails>
            </req:BookPURequest>
XML;

            return $xmlRequest;
        } else {
            return false;
        }
    }

    private function get_dhl_shipping_return_label_requests( $dhl_packages, $package ) {
        global $woocommerce;
        // Time is modified to avoid date diff with server.
        // $mailingDate = date('Y-m-d', time() + $this->timezone_offset) . 'T' . date('H:i:s', time() + $this->timezone_offset);// for reference
        $mailingDate = date('Y-m-d', current_time('timestamp')) . 'T' . date('H:i:s', current_time('timestamp'));
        $destination_city = strtoupper($package['destination']['city']);
        $destination_postcode = strtoupper($package['destination']['postcode']);
        $destination_country = $package['destination']['country'];
        $destination_country_name = isset(WC()->countries->countries[$destination_country]) ? WC()->countries->countries[$destination_country] : $destination_country;
        $total_after_discount = $this->order->get_subtotal() - $this->order->get_total_discount();
        $order_subtotal = $total_after_discount;
        $order_currency = elex_dhl_get_order_currency($this->order);
        $orderid = elex_dhl_get_order_id($this->order);


        $is_dutiable = ($package['destination']['country'] == $this->origin_country || wf_dhl_is_eu_country($this->origin_country, $package['destination']['country'])) ? "N" : "Y";
        if(isset($this->settings['dutypayment_type']) && $this->settings['dutypayment_type'] == '') {
            $is_dutiable = 'N';
        }


        $bulk_create_return_shipment = get_option('$bulk_create_return_shipment');
        $orders_with_no_default_shipment_service = '';

        if($bulk_create_return_shipment){
            if($is_dutiable == 'Y'){
                if(get_option('dhl_return_shipping_service_selected') === 'no'){
                    if($this->settings['default_international_service'] != 'none'){
                        $this->service_code = $this->settings['default_international_service'];
                        update_option('default_shipment_service', 'yes');
                    }
                    else{
                        update_option("default_shipment_service", 'no');
                        $orders_with_no_default_shipment_service .= $orderid. ',';
                    }
                }
            }else{
                if(get_option('dhl_return_shipping_service_selected') === 'no'){
                    if($this->settings['default_domestic_service'] != 'none'){
                        $this->service_code = $this->settings['default_domestic_service'];
                        update_option("default_shipment_service", 'yes');
                    }
                    else{
                        update_option("default_shipment_service", 'no');
                        $orders_with_no_default_shipment_service .= $orderid. ',';
                    }
                }
            }
            update_option("orders_with_no_default_shipment_service_exp_dhl_elex", $orders_with_no_default_shipment_service);
        }



        $archive_bill_settings = isset($this->settings['request_archive_airway_label']) ? $this->settings['request_archive_airway_label'] : '';
        $number_of_bills = isset($this->settings['no_of_archive_bills']) ? $this->settings['no_of_archive_bills'] : '';


        $this->find_special_service_products($package['contents']);

        $shipment_details = $this->wf_get_shipment_details($dhl_packages, $is_dutiable);
        $origin_country_name = isset(WC()->countries->countries[$this->origin_country]) ? WC()->countries->countries[$this->origin_country] : $this->origin_country;

        $special_service = "";
        $shipping_company = elex_dhl_get_order_shipping_company($this->order);

        $dutypayment_type_accountnumber = "";
        if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
            $dutypayment_type_accountnumber = "<DutyPaymentType>{$this->dutypayment_type}</DutyPaymentType>";
            if (!empty($this->dutyaccount_number) && $this->dutypayment_type == 'T') {
                $dutypayment_type_accountnumber .= "<DutyAccountNumber>{$this->dutyaccount_number}</DutyAccountNumber>";
            }
        }

        $shipper = array(
            'shipper_id' => $this->return_label_acc_number,
            'company_name' => $this->freight_shipper_company_name,
            'registered_account' => $this->return_label_acc_number,
            'address_line' => $this->freight_shipper_street,
            'address_line2' => $this->freight_shipper_street_2,
            'city' => $this->freight_shipper_city,
            'division' => $this->freight_shipper_state,
            'division_code' => $this->freight_shipper_state,
            'postal_code' => $this->origin,
            'country_code' => $this->origin_country,
            'country_name' => $origin_country_name,
            'contact_person_name' => $this->freight_shipper_person_name,
            'contact_phone_number' => $this->freight_shipper_phone_number,
            'contact_email' => $this->shipper_email,
        );

        if($package['destination']['country'] == $shipper['country_code']){
            // As per the DHL, if the customer belongs to shipper country we need to use export account number and if the customer not belongs to shipper country we need to use import account number
            $shipper['shipper_id'] = $this->settings['account_number'];
            $shipper['registered_account'] = $this->settings['account_number'];
            $this->return_label_acc_number = $this->settings['account_number'];
        }else if(empty($this->return_label_acc_number)){
            update_post_meta($this->order->get_id(),'wf_woo_dhl_shipmentReturnErrorMessage', ' Return Import Account Number Not Provided');
            wp_redirect( admin_url( '/post.php?post='.$this->order->get_id().'&action=edit') );
            exit;
        }

        // If package have different origin, use it instead of admin settings
        if(isset($this->settings['vendor_check']) && ($this->settings['vendor_check'] === 'yes')){
            if (isset($package['origin']) && !empty($package['origin'])) {
                // Check if vendor have atleast provided origin address
                if (isset($package['origin']['country']) && !empty($package['origin']['country'])) {
                    $shipper['company_name'] = $package['origin']['company'];
                    $shipper['address_line'] = $package['origin']['address_1'];
                    $shipper['address_line2'] = $package['origin']['address_2'];
                    $shipper['city'] = $package['origin']['city'];
                    $shipper['division'] = $package['origin']['state'];
                    $shipper['division_code'] = $package['origin']['state'];
                    $shipper['postal_code'] = $package['origin']['postcode'];
                    $shipper['country_code'] = $package['origin']['country'];
                    $shipper['country_name'] = isset(WC()->countries->countries[$package['origin']['country']]) ? WC()->countries->countries[$package['origin']['country']] : $package['origin']['country'];
                    $shipper['contact_person_name'] = $package['origin']['first_name'] . ' ' . $package['origin']['last_name'];
                    $shipper['contact_phone_number'] = $package['origin']['phone'];
                    $shipper['contact_email'] = $package['origin']['email'];
                }
            }
        }

        $toaddress = array(
            'first_name' => elex_dhl_get_order_shipping_first_name($this->order),
            'last_name' => elex_dhl_get_order_shipping_last_name($this->order),
            'company_name' => elex_dhl_get_order_shipping_company($this->order),
            'address_1' => elex_dhl_get_order_shipping_address_1($this->order),
            'address_2' => elex_dhl_get_order_shipping_address_2($this->order),
            'city' => htmlspecialchars($destination_city),
            'postcode' => $destination_postcode,
            'country_name' => $destination_country_name,
            'country_code' => $destination_country,
            'email' => elex_dhl_get_order_billing_email($this->order),
            'phone' => elex_dhl_get_order_billing_phone($this->order),
        );

        if(!isset($toaddress['country_code'])){
            $woocommerce_countries = $woocommerce->countries->get_countries();        
            $toaddress['country_code'] = array_search($toaddress['country_name'], $woocommerce_countries);
        }

        $this->dhl_package_to_address = $toaddress;
         
        $consignee_companyname = htmlspecialchars($toaddress['company_name']? $toaddress['company_name']: (!empty($shipping_company) ? $shipping_company : '--'));

        $consignee_name = htmlspecialchars(isset($toaddress['person_name'])? $toaddress['person_name']: $toaddress['first_name'].' '.$toaddress['last_name']);

        $order_id = elex_dhl_get_order_id($this->order);;
        $check_items = get_post_meta($order_id, '_wf_dhl_stored_return_products', true);
        
        if (!empty($check_items)) {
            $check_items = explode(',', $check_items);
            $selected_items = array();
            foreach ($check_items as $k => $v) {
                $selected_items[] = explode('|', $v);
            }
        } else {
            $selected_items = '';
        }

        $special_service .= "<SpecialService><SpecialServiceType>PT</SpecialServiceType></SpecialService>";

        /* Sending special service code if the product belongs to restricted commodities or dangerous goods*/
        if (!empty($this->special_service_code)) {
            $special_service_codes = $this->special_service_codes_unique($this->special_service_code);
            foreach ($special_service_codes as $service_code) {
                if ($service_code['code'] != '') {
                    $special_service .= "<SpecialService><SpecialServiceType>" . $service_code['code'] . "</SpecialServiceType></SpecialService>";
                }
            }
        }

        /* Sending default special service code if the product belongs to restricted commodities or dangerous goods with default type*/
        if (!empty($this->default_special_service_code_array)) {
            $default_special_service_codes = $this->special_service_codes_unique($this->default_special_service_code_array);
            foreach ($default_special_service_codes as $service_code) {
                if ($service_code['code'] != '') {
                    $special_service .= "<SpecialService><SpecialServiceType>" . $service_code['code'] . "</SpecialServiceType></SpecialService>";
                }
            }
        }

        $this->packages_dhl = $dhl_packages;

        $return_receiver = $this->settings['return_address_different'] !== 'yes'? array(
            'person_name' => $shipper['contact_person_name'],
            'company_name' => $shipper['company_name'] ? $shipper['company_name'] : '--',
            'phone' => $shipper['contact_phone_number'],
            'email' => $shipper['contact_email'],
            'address_1' => $shipper['address_line'],
            'address_2' => $shipper['address_line2'],
            'city' => $shipper['city'],
            'state' => $shipper['division'],
            'postcode' => $shipper['postal_code'],
            'country_code' => $shipper['country_code'],
            'country_name' => $shipper['country_name']
        ): $this->settings['return_shipment_address'];

        $archive_ref = array(
            'airway bill number' => '',
            'insurance' => ''
        );

        $RequestArchiveDoc = '';
        $docImage = '';
        if ($this->plt && $is_dutiable == 'Y') {
            update_option('PLT_return_enabled_express_dhl_elex', true);
            $sample_base64_encoded_pdf = $this->generate_return_commercial_invoice($dhl_packages, $toaddress, $return_receiver, $selected_items, $archive_ref);
            $special_service .= "<SpecialService><SpecialServiceType>WY</SpecialServiceType></SpecialService>";

            $docImage = "<DocImages>
                            <DocImage>
                                <Type>CIN</Type>
                                <Image>$sample_base64_encoded_pdf</Image>
                                <ImageFormat>PDF</ImageFormat>
                            </DocImage>
                        </DocImages>";
        }

        $address = $this->get_valid_address(elex_dhl_get_order_shipping_address_1($this->order), elex_dhl_get_order_shipping_address_2($this->order));

        $destination_address = '<AddressLine>' . htmlspecialchars($toaddress['address_1']) . '</AddressLine>';
        if (!empty($toaddress['address_2'])) {
            $destination_address .= '<AddressLine>' . htmlspecialchars($toaddress['address_2']) . '</AddressLine>';
        }

        $current_order_items = $this->order->get_items();

        $export_declaration = '';
        {
            $export_declaration = '<ExportDeclaration>';
            $export_line_item = '';
            $order_dutiable_amount = 0;

            //Obtaining discounted prices of the products
            foreach ($package['contents'] as $i => $item) {
                $item_value = 0;
                foreach($current_order_items as $current_order_item){
                    $current_order_item_data = $current_order_item->get_data();
                    if($current_order_item_data['variation_id'] != 0){
                        if($item['data']->get_id() == $current_order_item_data['variation_id']){
                            $item_value = $current_order_item_data['total'];
                        }
                    }else if($item['data']->get_id() == $current_order_item_data['product_id']){
                            $item_value = $current_order_item_data['total'];   
                    }
                }
                $item_unit_value = wc_format_decimal( $item_value/$item['quantity'], 2, false);
                $order_dutiable_amount += ($item_unit_value * $item['quantity']);

                $export_line_item .= '<ExportLineItem>';
                $export_line_item .= '  <LineNumber>' . ++$i . '</LineNumber>';
                $export_line_item .= '  <Quantity>' . $item['quantity'] . '</Quantity>';
                $export_line_item .= '  <QuantityUnit>'. $this->invoice_quantity_unit .'</QuantityUnit>'; //not sure about this value
                $export_line_item .= '  <Description>' . substr(htmlspecialchars($item['data']->get_title()), 0, 75) . '</Description>';
                $export_line_item .= '  <Value>' . $item_unit_value . '</Value>';

                $par_id = wp_get_post_parent_id(elex_dhl_get_product_id($item['data']));
                $post_id = $par_id ? $par_id : elex_dhl_get_product_id($item['data']);

                $wf_hs_code = get_post_meta($post_id, '_wf_hs_code', 1); //this works for variable product also

                if (!empty($wf_hs_code)) {
                    $export_line_item .= '  <CommodityCode>' . $wf_hs_code . '</CommodityCode>';
                }

                $export_line_item .= '  <Weight><Weight>' . round($item['data']->get_weight(), 3) . '</Weight><WeightUnit>' . $this->product_weight_unit . '</WeightUnit></Weight><GrossWeight><Weight>' . round($item['data']->get_weight() * $item['quantity'], 3) . '</Weight><WeightUnit>' . $this->product_weight_unit . '</WeightUnit></GrossWeight>';

                $export_line_item .= '</ExportLineItem>';
            }
            $order_dutiable_amount = wc_format_decimal( $order_dutiable_amount, 2, false);

            $dutiable_content = "<Dutiable><DeclaredValue>{$order_dutiable_amount}</DeclaredValue><DeclaredCurrency>{$order_currency}</DeclaredCurrency>";
            if($is_dutiable == "Y"){
                $this->dutypayment_type = "R";//Here the Store owner becomes recipient and he should pay duty taxes
                $dutiable_content .= "<TermsOfTrade>DAP</TermsOfTrade>";
            }
            $dutiable_content .= "</Dutiable>" ;
            $use_dhl_invoice = '';
        if(isset($this->settings['classic_commercial_invoice']) && $this->settings['classic_commercial_invoice'] == 'default') {
             $use_dhl_invoice .= '<UseDHLInvoice>Y</UseDHLInvoice>
            <DHLInvoiceLanguageCode>'. $this->invoice_language_code .'</DHLInvoiceLanguageCode>';
        }
        $export_declaration .= '<InvoiceNumber>'. $this->order_id .'</InvoiceNumber>
                <InvoiceDate>'. date('Y-m-d', current_time('timestamp')) .'</InvoiceDate>';

            $export_declaration .=  $export_line_item ;

            $export_declaration .= '</ExportDeclaration>';
        }
        $number_of_bills_xml = '';
        $billing_phone = elex_dhl_get_order_billing_phone($this->order);
        $billing_email = elex_dhl_get_order_billing_email($this->order);

        $this->dhl_package_shipper = $return_receiver;
        $this->dhl_package_to_address = $toaddress;
        
        $dhl_email_enable = $this->settings['dhl_email_notification_service'];
        $dhl_email_message = $this->settings['dhl_email_notification_message'];
        $dhl_notification = '';

        $customer_logo_url = $this->settings['customer_logo_url'];
        $customer_logo_xml = '';
        if (!empty($archive_bill_settings) && $archive_bill_settings === 'yes') {
            $request_archive_airway_bill = 'Y';
        } else {
            $request_archive_airway_bill = 'N';
        }

        if (empty($number_of_bills) && $request_archive_airway_bill === 'Y') {
            $number_of_bills_xml = '<NumberOfArchiveDoc>1</NumberOfArchiveDoc>';
        }

        if (!empty($number_of_bills) && $request_archive_airway_bill === 'Y') {
            $number_of_bills_xml = '<NumberOfArchiveDoc>' . $number_of_bills . '</NumberOfArchiveDoc>';
        }
        if (!empty($customer_logo_url) && @file_get_contents($customer_logo_url)) {

            $type = pathinfo($customer_logo_url, PATHINFO_EXTENSION);
            $data = file_get_contents($customer_logo_url);
            $base64 = base64_encode($data);
            $customer_logo_xml = '<CustomerLogo><LogoImage>' . $base64 . '</LogoImage><LogoImageFormat>' . strtoupper($type) . '</LogoImageFormat></CustomerLogo>';
        }

        $return_receiver_enhance= "<AddressLine>{$return_receiver['address_1']}</AddressLine>
                                <AddressLine>{$return_receiver['address_2']}</AddressLine>";
         $elex_dhl_version = ELEX_DHL_SOFTWARE_VERSION;
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req-6.2.xsd" schemaVersion="6.2">
    <Request>
        <ServiceHeader>
            <MessageTime>{$mailingDate}</MessageTime>
            <MessageReference>645897123569741258963214570569</MessageReference>
            <SiteID>{$this->site_id}</SiteID>
            <Password>{$this->site_password}</Password>
        </ServiceHeader>
        <MetaData>
            <SoftwareName>WooCommerce DHL Express Plugin</SoftwareName>
            <SoftwareVersion>{$elex_dhl_version}</SoftwareVersion>
        </MetaData>
    </Request>
    <RegionCode>{$this->region_code}</RegionCode>
    <RequestedPickupTime>Y</RequestedPickupTime>
    <LanguageCode>en</LanguageCode>
    <PiecesEnabled>Y</PiecesEnabled>
    <Billing>
        <ShipperAccountNumber>{$this->return_label_acc_number}</ShipperAccountNumber>
        <ShippingPaymentType>R</ShippingPaymentType>
        <BillingAccountNumber>{$this->return_label_acc_number}</BillingAccountNumber>
        {$dutypayment_type_accountnumber}
    </Billing>
    <Consignee>
        <CompanyName>{$return_receiver['company_name']}</CompanyName>
        {$return_receiver_enhance}
        <City>{$return_receiver['city']}</City>
        <Division>{$return_receiver['state']}</Division>
        <PostalCode>{$return_receiver['postcode']}</PostalCode>
        <CountryCode>{$return_receiver['country_code']}</CountryCode>
        <CountryName>{$return_receiver['country_name']}</CountryName>
        <Contact>
            <PersonName>{$return_receiver['person_name']}</PersonName>
            <PhoneNumber>{$return_receiver['phone']}</PhoneNumber>
            <Email>{$return_receiver['email']}</Email>
        </Contact>
    </Consignee>
    {$dutiable_content}
    {$use_dhl_invoice}
    {$export_declaration}
    {$shipment_details}
    <Shipper>
        <ShipperID>{$shipper['shipper_id']}</ShipperID>
        <CompanyName>{$consignee_companyname}</CompanyName>
        <RegisteredAccount>{$shipper['registered_account']}</RegisteredAccount>
        {$destination_address}
        <City>{$toaddress['city']}</City>
        <PostalCode>{$toaddress['postcode']}</PostalCode>
        <CountryCode>{$toaddress['country_code']}</CountryCode>
        <CountryName>{$toaddress['country_name']}</CountryName>
        <Contact>
            <PersonName>{$consignee_name}</PersonName>
            <PhoneNumber>{$toaddress['phone']}</PhoneNumber>
            <Email>{$toaddress['email']}</Email>
        </Contact>
    </Shipper>
    {$special_service}
    {$docImage}
    <LabelImageFormat>{$this->image_type}</LabelImageFormat>
    <RequestArchiveDoc>{$request_archive_airway_bill}</RequestArchiveDoc>
    {$number_of_bills_xml}
    {$RequestArchiveDoc}
    <Label><HideAccount>Y</HideAccount><LabelTemplate>{$this->output_format}</LabelTemplate>{$customer_logo_xml}</Label>
</req:ShipmentRequest>
XML;
        $xmlRequest = apply_filters('wf_dhl_label_request', $xmlRequest, $this->order_id);
        return $xmlRequest;
    }

    private function get_valid_address($line1, $line2 = '', $line3 = '') {
        $valid_address = array();

        if (strlen($line1) > 35) {
            $valid_address['valid_line1'] = $this->substr_upto_space($line1, 35);
            $line1_rem = trim(str_replace($valid_address['valid_line1'], "", $line1));
            $line2 = $line1_rem . " " . $line2;
        } else {
            $valid_address['valid_line1'] = $line1;
        }

        if (strlen($line2) > 35) {
            $valid_address['valid_line2'] = $this->substr_upto_space($line2, 35);
            $line2_rem = trim(str_replace($valid_address['valid_line2'], "", $line2));
            $line3 = $line2_rem . " " . $line3;
        } else {
            $valid_address['valid_line2'] = $line2;
        }

        // not limiting line3 charecters upto 35, because DHL API handle the case and throws error.
        if (!empty($line3)) {
            $valid_address['valid_line3'] = $line3;
        }
        return $valid_address;
    }

    public function substr_upto_space($str, $l) {
        $pos = strrpos($str, ' ');
        if ($pos > $l) {
            return $this->substr_upto_space(substr($str, 0, $pos), $l);
        } else {
            return substr($str, 0, $pos);
        }
    }

    private function wf_get_shipment_details($dhl_packages, $is_dutiable = 'N', $check_return = 'return') {
        $order = $this->order;
        $order_currency = elex_dhl_get_order_currency($this->order);

        if (!$order) {
            return;
        }

        $orderid = elex_dhl_get_order_id($order);

        $pieces = "";
        $total_packages = 0;
        $total_weight = 0;
        $total_value = 0;
        $order_insurance = get_post_meta($orderid, 'wf_dhl_insurance', true);

        if ($dhl_packages) {
            foreach ($dhl_packages as $group_key => $package_group) {
                $piece_id = 1;
                $package_weight_unit = '';
                foreach ($package_group as $key => $parcel) {
                    if(!isset($parcel['quantity'])){
                        $parcel['quantity'] = 1;
                    }

                    $index = $key + 1;
                    $total_packages += $parcel['quantity'];
                    if(isset($parcel['Weight']['Units'])){
                        $package_weight_unit = $parcel['Weight']['Units'];
                    }

                    $package_weight_unit = !empty($package_weight_unit)? $package_weight_unit: $this->weight_unit;

                    if((isset($parcel['Weight']['Units']) && $parcel['Weight']['Units'] == 'KG') || $package_weight_unit == 'KG'){
                        if ($parcel['Weight']['Value'] < 0.001) {
                            $parcel['Weight']['Value'] = 0.001;
                        } else {
                            $parcel['Weight']['Value'] = (float) $parcel['Weight']['Value'];
                        }
                    }else if((isset($parcel['Weight']['Units']) && $parcel['Weight']['Units'] == 'LBS') || $package_weight_unit == 'LBS'){
                        if ($parcel['Weight']['Value'] < 0.12) {
                            $parcel['Weight']['Value'] = 0.12;
                        } else {
                            $parcel['Weight']['Value'] = (float) $parcel['Weight']['Value'];
                        }
                    }

                    $parcel['GroupPackageCount'] = isset($parcel['GroupPackageCount'])? $parcel['GroupPackageCount']: 1;
                    $parcel['InsuredValue']['Amount'] = isset($parcel['InsuredValue'])? $parcel['InsuredValue']['Amount']: 0;                             

                    $total_value += (float)$parcel['InsuredValue']['Amount'] * (float)$parcel['GroupPackageCount'];
                    if(isset($parcel['packtype'])){
                        $pack_type = $this->wf_get_pack_type($parcel['packtype']);
                    }else{
                        $pack_type = '';
                        $pack_type = $this->wf_get_pack_type($pack_type);
                    }
                    $pack_type = !empty($pack_type) ? $pack_type : '';

                    for($parcel_quantity = 1; $parcel_quantity <= $parcel['quantity']; $parcel_quantity++){
                        $pieces .= '<Piece>';
                        $pieces .= '<PieceID>' . $piece_id . '</PieceID>';
                        $pieces .= '<PackageType>' . $pack_type . '</PackageType>';
                        $piece_weight = ($parcel['Weight']['Value'] == 0)? '0.01':  round($parcel['Weight']['Value'], 3);
                        $pieces .= '<Weight>' . $piece_weight . '</Weight>';

                        if ($this->packing_method != "weight_based") {
                            if (isset($parcel['Dimensions'])) {
                                if (!empty($parcel['Dimensions']['Length']) && !empty($parcel['Dimensions']['Width']) && !empty($parcel['Dimensions']['Height'])) {
                                    $dimensions = array($parcel['Dimensions']['Length'], $parcel['Dimensions']['Width'], $parcel['Dimensions']['Height']);
                                    sort($dimensions);
                                    $pieces .= '<Width>' . round($dimensions[1]) . '</Width>';
                                    $pieces .= '<Height>' . round($dimensions[0]) . '</Height>';
                                    $pieces .= '<Depth>' . round($dimensions[2]) . '</Depth>';
                                }
                            } else {
                                $packed_products = $parcel['packed_products'];
                                foreach ($packed_products as $packed_product) {
                                    $packed_product_data = $packed_product->get_data();
                                    if (!empty($packed_product_data['width']) && !empty($packed_product_data['height']) && !empty($packed_product_data['length'])) {
                                        $dimensions = array($packed_product_data['length'], $packed_product_data['width'], $packed_product_data['height']);
                                        sort($dimensions);
                                        $pieces .= '<Width>' . round($dimensions[1]) . '</Width>';
                                        $pieces .= '<Height>' . round($dimensions[0]) . '</Height>';
                                        $pieces .= '<Depth>' . round($dimensions[2]) . '</Depth>';
                                    }
                                }
                            }
                        }
                        $pieces .= '</Piece>';
                        $piece_id++;
                    }
                    $total_weight += $parcel['Weight']['Value'] * $parcel['quantity'];
                    $total_weight = round($total_weight, 3);
                }
            }
        }

        // Time is modified to avoid date diff with server.
        //$mailingDate = date('Y-m-d', time() + $this->timezone_offset);
        $mailingDate = current_time('Y-m-d');
        $total_value = wc_format_decimal( $total_value, 2, false);
        $special_service_insurance = (($order_insurance == 'yes') && $check_return != 'return' && $total_value != 0) ? "<InsuredAmount>{$total_value}</InsuredAmount>" : "";

        $currency = get_woocommerce_currency();

        $local_product_code = $this->get_local_product_code($this->service_code, $this->origin_country);
        update_option("service_selected_create_shipment_express_dhl_elex", $local_product_code);

        $local_product_code_node = $local_product_code ? "<LocalProductCode>{$local_product_code}</LocalProductCode>" : '';

        if ($total_packages > 99) {
            $this->debug('<br>Because of dhl api limitation , you cannot print label for more than 99 packages <br> <a href="' . admin_url('/post.php?post=' . $this->order_id . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>', 'notice');
            update_option('Error packages more than 99', 'Because of DHL API limitation , you cannot print label for more than 99 packages');
            exit;
        }

        if (isset($this->special_service_warning) && !empty($this->special_service_warning)) {
            if(!empty($this->shipment_un_numbers)){
                $this->label_contents_text = '';
                foreach($this->shipment_un_numbers as $shipment_un_number){
                    $this->label_contents_text .= $shipment_un_number." ";
                }
                $this->label_contents_text .= $this->special_service_warning;
            }else{
                $this->label_contents_text = $this->special_service_warning;
            }

            $this->label_contents_text = rtrim($this->label_contents_text, ' ');
        }

        if(strlen($this->label_contents_text) > 90)
            $this->label_contents_text = substr($this->label_contents_text, 0, 86)."...";

        $shipment_details = <<<XML
    <ShipmentDetails>
        <NumberOfPieces>$total_packages</NumberOfPieces>
        <Pieces>
            {$pieces}
        </Pieces>
        <Weight>{$total_weight}</Weight>
        <WeightUnit>{$this->labelapi_weight_unit}</WeightUnit>
        <GlobalProductCode>{$this->service_code}</GlobalProductCode>
        {$local_product_code_node}
        <Date>{$mailingDate}</Date>
        <Contents>{$this->label_contents_text}</Contents>
        <DoorTo>DD</DoorTo>
        <DimensionUnit>{$this->labelapi_dimension_unit}</DimensionUnit>
        {$special_service_insurance}
        <IsDutiable>{$is_dutiable}</IsDutiable>
        <CurrencyCode>{$order_currency}</CurrencyCode>
    </ShipmentDetails>
XML;
        return $shipment_details;
    }

    private function get_local_product_code($global_product_code, $origin_country = '', $destination_country = '') {
        if (!empty($this->local_product_code)) {
            return $this->local_product_code;
        } else {
            $countrywise_local_product_code = array(
                'SA' => 'global_product_code',
                'ZA' => 'global_product_code',
                'CH' => 'global_product_code',
            );

            if (array_key_exists($origin_country, $countrywise_local_product_code)) {
                return ($countrywise_local_product_code[$this->origin_country] == 'global_product_code') ? $global_product_code : $countrywise_local_product_code[$this->origin_country];
            }
        }
        return $global_product_code;
    }

    public function wf_get_package_from_order($order) {
        $orderItems = $order->get_items();

        $items = array();
        foreach ($orderItems as $orderItem) {
            if($this->is_woocommerce_product_bundles_installed){
                $is_item_bundled_item = $this->is_product_bundled_item($orderItem);
                if($is_item_bundled_item){
                    continue;
                }
            }
            $product_composite_data = null;
            $product_data = null;
            $dhl_shipping_obj = new wf_dhl_woocommerce_shipping_method();

            $order_item_data = $orderItem->get_data();

            $order_product_id = $order_item_data['product_id'];
            $product_in_order = wc_get_product($order_product_id);

            //For Composite products
            $order_item_metadata = $orderItem->get_meta_data();
            $composite_products_ids_quantities = array();
            $is_composite_child = 'no';// For checking is an order item a child component of a composite product
            $is_composite_parent = 'no';
            $is_composite_product_has_properties = 'no';
            if($product_in_order) {
            if($product_in_order->get_type() == 'composite'){
                if($product_in_order->get_length() != '' && $product_in_order->get_width() != '' && $product_in_order->get_height() != '' && $product_in_order->get_weight() != ''){
                    $is_composite_product_has_properties = 'yes';
                }else{
                    foreach($order_item_metadata as $order_item_metadatum_key => $order_item_metadatum){
                        $meta_data_order = $order_item_metadatum->get_data();
                        if($meta_data_order['key'] == '_composite_children'){
                            $is_composite_parent = 'yes';
                        }

                        if($is_composite_parent == 'yes' && $meta_data_order['key'] == '_composite_data'){
                            $meta_data_order_value = $meta_data_order['value'];
                            foreach($meta_data_order_value as $meta_data_order_value_key => $meta_data_order_value_element){
                                $composite_product_id  = isset($meta_data_order_value_element['variation_id'])? $meta_data_order_value_element['variation_id']: $meta_data_order_value_element['product_id'];
                                $composite_product = wc_get_product($meta_data_order_value_element['composite_id']);
                                
                                $composite_products_ids_quantities[] = array(
                                    'product_id' => $composite_product_id,
                                    'quantity' => $meta_data_order_value_element['quantity'],
                                    'composite_id' => $meta_data_order_value_element['composite_id'],
                                    'title' => $composite_product->get_name()." - ". $meta_data_order_value_element['title']
                                );
                            }
                        }
                    }
                }
            }else{
                foreach($order_item_metadata as $order_item_metadatum_key => $order_item_metadatum){
                    $meta_data_order = $order_item_metadatum->get_data();
                    if($meta_data_order['key'] == '_composite_parent'){
                        $is_composite_child = 'yes';
                    }
                }
            }
        }

            $product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id']);
            $data = elex_dhl_get_order_item_meta_data($orderItem);
            $measured_weight = 0;
            if (isset($data[1]->value['weight']['value'])) {
                $measured_weight = (wc_get_weight($data[1]->value['weight']['value'], $this->weight_unit, $data[1]->value['weight']['unit']));
            }

            if(!empty($composite_products_ids_quantities)){
                foreach($composite_products_ids_quantities as $composite_product){
                    $component_product = wc_get_product($composite_product['product_id']);
                    $composite_title = str_replace($component_product->get_name().' - ', '', $composite_product['title']);
                    update_post_meta($composite_product['product_id'], '_composite_'.$composite_title.'_express_dhl_elex', esc_attr($composite_product['title']));
                    $measured_weight = 0;
                    $items[] = array('data' => $component_product, 'quantity' => $composite_product['quantity'], 'measured_weight' => $measured_weight, 'composite_title' => $composite_title);     
                }
            }else if($is_composite_product_has_properties == 'yes' || $is_composite_child == 'no'){
                $items[] = array('data' => $product_data, 'quantity' => $orderItem['qty'], 'measured_weight' => $measured_weight);
            }
        }

        $package = array();
        
        if(!empty($items)){
            $package['contents'] = $items;
            $package['destination']['country'] = elex_dhl_get_order_shipping_country($order);
            $package['destination']['first_name'] = elex_dhl_get_order_shipping_first_name($order);
            $package['destination']['last_name'] = elex_dhl_get_order_shipping_last_name($order);
            $package['destination']['company'] = elex_dhl_get_order_shipping_company($order);
            $package['destination']['address_1'] = elex_dhl_get_order_shipping_address_1($order);
            $package['destination']['address_2'] = elex_dhl_get_order_shipping_address_2($order);
            $package['destination']['city'] = elex_dhl_get_order_shipping_city($order);
            $package['destination']['state'] = elex_dhl_get_order_shipping_state($order);
            $package['destination']['postcode'] = elex_dhl_get_order_shipping_postcode($order);

            $package = apply_filters('wf_dhl_filter_label_packages', array($package), $this->ship_from_address);
        }

        return $package;
    }

    public function wf_get_return_package_return_from_order($order, $selected_items = '') {
        $orderItems = $order->get_items();
        $items = array();

        foreach ($orderItems as $orderItem) {
            $product_id = $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
            if (!is_array($selected_items)) {
                $product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id']);
                $items[] = array('data' => $product_data, 'quantity' => $orderItem['qty']);
            } else {
                foreach ($selected_items as $key => $value) {
                    if (in_array($product_id, $value)) {
                        $product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id']);
                        $items[] = array('data' => $product_data, 'quantity' => $value[1]);
                    }
                }
            }
        }
        $package['contents'] = $items;
        $package['destination']['country'] = elex_dhl_get_order_shipping_country($order);
        $package['destination']['first_name'] = elex_dhl_get_order_shipping_first_name($order);
        $package['destination']['last_name'] = elex_dhl_get_order_shipping_last_name($order);
        $package['destination']['company'] = elex_dhl_get_order_shipping_company($order);
        $package['destination']['address_1'] = elex_dhl_get_order_shipping_address_1($order);
        $package['destination']['address_2'] = elex_dhl_get_order_shipping_address_2($order);
        $package['destination']['city'] = elex_dhl_get_order_shipping_city($order);
        $package['destination']['state'] = elex_dhl_get_order_shipping_state($order);
        $package['destination']['postcode'] = elex_dhl_get_order_shipping_postcode($order);

        $package = apply_filters('wf_dhl_filter_label_packages', array($package), $this->ship_from_address);
        return $package;
    }

    public function wf_get_return_package_from_order($order, $selected_items = '') {
        $orderItems = $order->get_items();
        $items = array();
        foreach ($orderItems as $orderItem) {
            $product_id = $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
            if (!is_array($selected_items) || in_array($product_id, $selected_items)) {
                $product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id']);
                $items[] = array('data' => $product_data, 'quantity' => $orderItem['qty']);
            }
        }
        $package['contents'] = $items;
        $package['destination']['country']      = elex_dhl_get_order_shipping_country($order);
        $package['destination']['first_name']   = elex_dhl_get_order_shipping_first_name($order);
        $package['destination']['last_name']    = elex_dhl_get_order_shipping_last_name($order);
        $package['destination']['company']      = elex_dhl_get_order_shipping_company($order);
        $package['destination']['address_1']    = elex_dhl_get_order_shipping_address_1($order);
        $package['destination']['address_2']    = elex_dhl_get_order_shipping_address_2($order);
        $package['destination']['city']         = elex_dhl_get_order_shipping_city($order);
        $package['destination']['state']        = elex_dhl_get_order_shipping_state($order);
        $package['destination']['postcode']     = elex_dhl_get_order_shipping_postcode($order);

        $package = apply_filters('wf_dhl_filter_label_packages', array($package), $this->ship_from_address);
        return $package;
    }

    private function get_dummy_dhl_package() {
        return array(
            'GroupNumber' => 1,
            'GroupPackageCount' => 1,
            'packtype' => 'BOX',
            'InsuredValue' => 0,
            'packed_products' => array(),
        );
    }

    public function manual_packages($packages) {
        if (!isset($_GET['weight'])) {
            return $packages;
        }
        $length_arr = json_decode(stripslashes(html_entity_decode($_GET["length"])));
        $width_arr = json_decode(stripslashes(html_entity_decode($_GET["width"])));
        $height_arr = json_decode(stripslashes(html_entity_decode($_GET["height"])));
        $weight_arr = json_decode(stripslashes(html_entity_decode($_GET["weight"])));
        $insurance_arr = isset($_GET["insurance"]) ? json_decode(stripslashes(html_entity_decode($_GET["insurance"]))) : array();

        $no_of_package_entered = count($weight_arr);
        $no_of_packages = 0;
        foreach ($packages as $key => $package) {
            $no_of_packages += count($package);
        }
        // Populate extra packages, if entered manual values
        if ($no_of_package_entered > $no_of_packages) {
            $package_clone = is_array($packages[0]) ? current($packages[0]) : $this->get_dummy_dhl_package(); //get first package to clone default data
            for ($i = $no_of_packages; $i < $no_of_package_entered; $i++) {
                $packages[0][$i] = $package_clone;
                $packages[0][$i]['package_type'] = 'custom';
            }
        }
        // Overriding package values
        $index = 0;
        foreach ($packages as $package_num => $stored_package) {
            foreach ($stored_package as $key => $package) {
                if (isset($length_arr[$index])) {
                    // If not available in GET then don't overwrite.
                    $packages[$package_num][$key]['Dimensions']['Length'] = $length_arr[$index];
                }

                if (isset($width_arr[$index])) {
                    // If not available in GET then don't overwrite.
                    $packages[$package_num][$key]['Dimensions']['Width'] = $width_arr[$index];
                }

                if (isset($height_arr[$index])) {
                    // If not available in GET then don't overwrite.
                    $packages[$package_num][$key]['Dimensions']['Height'] = $height_arr[$index];
                }

                if (isset($weight_arr[$index])) {
                    // If not available in GET then don't overwrite.

                    $weight = $weight_arr[$index];
                    $packages[$package_num][$key]['Weight']['Value'] = round($weight, 3);
                }

                if (isset($insurance_arr[$index])) {
                    // If not available in GET then don't overwrite.
                    $packages[$package_num][$key]['InsuredValue']['Amount'] = $insurance_arr[$index];
                }

                if (!isset($length_arr[$index]) && !isset($width_arr[$index]) && !isset($height_arr[$index]) && !isset($weight_arr[$index]) && !isset($insurance_arr[$index])) {
                    unset($packages[$package_num][$key]);
                }
                $index++;
            }
        }

        return $packages;
    }

    //For Switzerland we have to send each package as seperate request
    public function elex_dhl_split_packages( $packages) {
        if(!($packages[0]['destination']['country'] == 'CH' && $this->origin_country == 'CH')) {
            return $packages;
        }
        $new_packages = array();
        foreach ($packages as $package) {
            $flag = 0;
            foreach ($package['contents'] as $key=>$order_details) {
                $new_packages[$flag]['contents'][$key] = $order_details;
                $new_packages[$flag]['destination'] = $package['destination'];
                $flag ++;
            }
        }
        return $new_packages;
    }

    public function print_label( $order, $service_code, $post_plt = '0' ) {
        $this->order = $order;
        $this->order_id = $order->get_id();
        $this->service_code = $service_code;
        $this->plt = ($post_plt != '0') ? false : $this->plt;
        $shipping_methods = $order->get_shipping_methods();
        $shipping_method = array_shift($shipping_methods);
        $shipping_output = explode('|', $shipping_method['method_id']);
        $this->local_product_code = isset($shipping_output[1]) ? $shipping_output[1] : '';
        $packages = array();
        $packages = array_values($this->wf_get_package_from_order($order));

        $stored_packages = array();
        $stored_packages = get_post_meta( $order->get_id(), '_wf_dhl_stored_packages', true );

        if (!$stored_packages && !empty($packages)) {
            foreach ($packages as $key => $package) {
                $stored_packages[] = $this->get_dhl_packages($package);
            }
        }

        $dhl_packages = $this->manual_packages($stored_packages);

        $reference_id = 0;
        foreach ($dhl_packages as $key => $dhl_package) {
            //Since for switzerland we are sending seperate package request
            if($packages[$key]['destination']['country'] == 'CH' && $this->settings['base_country'] == 'CH') {
                foreach ($dhl_package as $pack_index => $value) {
                    $reference_id++; 
                    $package_piece = array();
                    $package_piece[0] = $value;
                    $this->print_label_processor(array($package_piece), $packages[$key],$reference_id);
                }
            }
            else {
                $this->print_label_processor(array($dhl_package), $packages[$key]);
            }
            if (!empty($this->shipmentErrorMessage)) {
                $this->shipmentErrorMessage .= "</br>Some error occurred for package $key: " . $this->shipmentErrorMessage;
            }
        }

        if ($this->debug) {
            echo '<a href="' . admin_url('/post.php?post=' . $_GET['wf_dhl_createshipment'] . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>';
            //For the debug information to display in the page
            wp_die();
        }
    }

    public function print_return_label( $order, $service_code, $post_plt = '0' ) {
        $this->order = $order;
        $this->order_id = $order->get_order_number();

        $order_shipment_id = get_post_meta($this->order_id, 'wf_woo_dhl_shipmentId');

        $this->service_code = $service_code;
        $this->plt = ($post_plt != '0') ? false : $this->plt;

        $packages = array();

        $check_items = get_post_meta($this->order_id, '_wf_dhl_stored_return_products', true);
        if (!empty($check_items)) {
            $selected_items = explode(',', $check_items);
        } else {
            $selected_items = '';
        }

        $packages = array_values($this->wf_get_return_package_from_order($order, $selected_items));

        $stored_packages = array();
        //$stored_packages  =   get_post_meta( $order_id, '_wf_dhl_stored_return_packages', array() );

        if (!$stored_packages) {
            foreach ($packages as $key => $package) {
                $stored_packages[] = $this->get_dhl_packages($package);
            }
        }

        $dhl_packages = $this->manual_packages($stored_packages);

        foreach ($dhl_packages as $key => $dhl_package) {
            $this->print_return_label_processor(array($dhl_package), $packages[$key]);

            if (!empty($this->shipmentErrorMessage)) {
                $this->shipmentErrorMessage .= "</br>Some error occured for package $key: " . $this->shipmentErrorMessage;
            }
        }

        if ($this->debug) {
            echo '<a href="' . admin_url('/post.php?post=' . $_GET['wf_dhl_create_return_shipment'] . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>';
            //For the debug information to display in the page
            die();
        }
    }

    public function print_label_processor($dhl_package, $package, $reference_id='') {
        $this->shipmentErrorMessage = '';
        $this->master_tracking_id = '';

        // Debugging
        $this->debug(__('dhl debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-dhl'));

        // Get requests
        $dhl_requests = $this->get_dhl_requests($dhl_package, $package, $reference_id);
        if ($dhl_requests) {
            $this->run_package_request($dhl_requests, $dhl_package);
        }
        update_post_meta($this->order_id, 'wf_woo_dhl_shipmentErrorMessage', $this->shipmentErrorMessage);
    }

    public function print_return_label_processor( $dhl_package, $package ) {
        $this->shipmentErrorMessage = '';
        $this->master_tracking_id = '';
        // Debugging
        $this->debug(__('dhl debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-dhl'));

        // Get requests
        $dhl_requests = $this->get_dhl_shipping_return_label_requests( $dhl_package, $package );

        if ($dhl_requests) {
            $this->run_package_request($dhl_requests, $dhl_package, 'return_label');
        }
        update_post_meta($this->order_id, 'wf_woo_dhl_shipmentReturnErrorMessage', $this->shipmentErrorMessage);
    }

    public function run_package_request($request, $dhl_packages = null, $return_label = '') {
        if ($return_label != '') {
            update_option("return_create_shipment", true);
            $this->process_result($this->get_result($request), $request, $dhl_packages, 'return');
        } else {
            update_option("return_create_shipment", false);
            $this->process_result($this->get_result($request), $request, $dhl_packages);
        }
    }

    private function get_result($request) {
        $this->debug('<br>DHL REQUEST: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($request, true), ENT_IGNORE) . '</pre>');

        $shipment_type_return = get_option('return_create_shipment');
        $response = array();

        $result = wp_remote_post($this->service_url, array(
                'method' => 'POST',
                'timeout' => 70,
                'sslverify' => 0,
                'body' => $request,
            )
        );

        $this->debug('DHL RESPONSE: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($result, true), ENT_IGNORE) . '</pre>');
        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
            $this->debug('DHL WP ERROR: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r(htmlspecialchars($error_message), true) . '</pre>');
        } elseif (is_array($result) && !empty($result['body'])) {
            $result = $result['body'];
            $this->order = apply_filters('elex_dhl_mark_order_status_completed', $this->order);
        } else {
            $result = '';
        }

        libxml_use_internal_errors(true);
        if (is_string($result) && !empty($result)) {
            $result = utf8_encode($result);
        }

        $xml = is_string($result) && !empty($result)? simplexml_load_string($result): '';

        $archive_ref = array();

        if($xml != '' && isset($xml->AirwayBillNumber)){
            $archive_ref['airway bill number'] = (string)$xml->AirwayBillNumber;
        }else{
            $archive_ref['airway bill number'] = '';
        }

        if($xml != '' && isset($xml->AirwayBillNumber)){
            $archive_ref['Insured Amount'] = (int)$xml->InsuredAmount;
        }else{
            $archive_ref['Insured Amount'] = 0;
        }

        $order_id = $this->order->get_id();
        update_post_meta($order_id, 'archive_reference_data_dhl_elex', $archive_ref);

        $selected_items = '';
        $selected_items_enhance = array();

        $order_id = elex_dhl_get_order_id($this->order);;
        $check_items = get_post_meta($order_id, '_wf_dhl_stored_return_products', true);
        
        if (!empty($check_items)) {
            $check_items = explode(',', $check_items);
            $selected_items = array();
            foreach ($check_items as $k => $v) {
                $selected_items[] = explode('|', $v);
            }
            foreach ($selected_items as $value) {
                array_push($selected_items_enhance, $value[0]);
            }
        }

        if($shipment_type_return){
            $return_shipment_shipper = $this->dhl_package_to_address;
            $return_shipment_reciever = $this->dhl_package_shipper;
            $this->generate_return_commercial_invoice_with_awb = $this->generate_return_commercial_invoice($this->packages_dhl, $return_shipment_shipper, $return_shipment_reciever, $selected_items_enhance, $archive_ref);

        }else{
            $this->generate_commercial_invoice_with_awb = $this->generate_commercial_invoice($order_id, $this->packages_dhl, $this->dhl_package_shipper, $this->dhl_package_to_address, $document_type = 'commercial', $archive_ref);
        }

        delete_option('PLT_enabled_express_dhl_elex');
        delete_option('PLT_return_enabled_express_dhl_elex');

        $shipmentErrorMessage = "";
        if (!$xml) {
            $shipmentErrorMessage .= 'Failed loading XML' . "\n";
            foreach (libxml_get_errors() as $error) {
                $shipmentErrorMessage .= "\t" . $error->message;
            }
            $response = array(
                'ErrorMessage' => $shipmentErrorMessage,
            );
        } else {
            if ($xml->Response->Status && (string) $xml->Response->Status->Condition->ConditionCode != '') {
                if ((string) $xml->Response->Status->Condition->ConditionCode === 'PLT006') {
                    $this->errorMsg .= __(' PLT ( Paperless Trade ) is Not Available. <b>Please print the Commercial Invoice and physically attach them to your shipments.</b> <br>', 'wf-shipping-dhl');
                    if($shipment_type_return){
                        $this->print_return_label($this->order, $this->service_code,'1');
                    }else{
                        $this->print_label($this->order, $this->service_code,'1');
                    }
                } else {
                    $this->errorMsg .= ((string) $xml->Response->Status->Condition->ConditionCode) . ' : ' . ((string) $xml->Response->Status->Condition->ConditionData);
                }
            }

                if(isset($this->settings['classic_commercial_invoice']) && $this->settings['classic_commercial_invoice'] == 'default') {
                    $commercial_invoice = $xml->LabelImage->MultiLabels->MultiLabel->DocImageVal;
                }
                else {
                    if($shipment_type_return) {
                        $commercial_invoice = $this->generate_return_commercial_invoice_with_awb;
                    }
                    else {
                        $commercial_invoice = $this->generate_commercial_invoice_with_awb;
                    }
                }
                $response = array(
                    'ShipmentID' => (string) $xml->AirwayBillNumber,
                    'LabelImage' => (string) $xml->LabelImage->OutputImage,
                    'CommercialInvoice' => (string) $commercial_invoice,
                    'ErrorMessage' => $this->errorMsg,
                );

            $xml_request = simplexml_load_string($request);
        }
        $this->create_shipment_dhl_response = $response;
        return $response;
    }

    private function process_result($result = '', $request, $dhl_packages, $return_label_process = '') {
        if (!empty($result['ShipmentID']) && !empty($result['LabelImage'])) {
            update_option("dhl_shipping_service_selected", "no");
            $shipmentId = $result['ShipmentID'];
            $shippingLabel = $result['LabelImage'];

            if ($return_label_process != '') {
                add_post_meta($this->order_id, 'wf_woo_dhl_return_shipmentId', $shipmentId, false);
                add_post_meta($this->order_id, 'wf_woo_dhl_return_shippingLabel_' . $shipmentId, $shippingLabel, true);
                add_post_meta($this->order_id, 'wf_woo_dhl_return_packageDetails_' . $shipmentId, $this->wf_get_parcel_details($dhl_packages), true);

                // Saving Return shipment Commercial invoice 
                if (isset($result['CommercialInvoice'])) {
                    add_post_meta($this->order_id, 'wf_woo_dhl_shipping_return_commercialInvoice_' . $shipmentId, $result['CommercialInvoice'], true);
                }
                if ($this->non_plt_commercial_invoice != '') {
                    add_post_meta($this->order_id, 'wf_woo_dhl_shipping_return_commercialInvoice_' . $shipmentId, $this->non_plt_commercial_invoice, true);
                }
            } else {
                add_post_meta($this->order_id, 'wf_woo_dhl_shipmentId', $shipmentId, false);
                add_post_meta($this->order_id, 'wf_woo_dhl_shippingLabel_' . $shipmentId, $shippingLabel, true);
                add_post_meta($this->order_id, 'wf_woo_dhl_packageDetails_' . $shipmentId, $this->wf_get_parcel_details($dhl_packages), true);
                add_post_meta($this->order_id, 'wf_woo_dhl_package_' . $shipmentId, $dhl_packages, true);

                //Saving Commercial invoice
                if(isset($result['CommercialInvoice'])){
                    add_post_meta($this->order_id, 'wf_woo_dhl_shipping_commercialInvoice_' . $shipmentId, $result['CommercialInvoice'], true);
                }
            }
            // Shipment Tracking (Auto)
            try {
                $shipment_id_cs = $shipmentId;
                $admin_notice = WfTrackingUtil::update_tracking_data($this->order_id, $shipment_id_cs, 'dhl-express', WF_Tracking_Admin_DHLExpress::SHIPMENT_SOURCE_KEY, WF_Tracking_Admin_DHLExpress::SHIPMENT_RESULT_KEY);
            } catch (Exception $e) {
                $admin_notice = '';
                // Do nothing.
            }

            // Shipment Tracking (Auto)
            if ($admin_notice != '') {
                WF_Tracking_Admin_DHLExpress::display_admin_notification_message($this->order_id, $admin_notice);
            } else {
                //Do your plugin's desired redirect.
                //exit;
            }       

            if (!empty($this->service_code)) {
                add_post_meta($this->order_id, 'wf_woo_dhl_service_code', $this->service_code, true);
            }

            if (!empty($this->service_code) && $return_label_process != '') {
                add_post_meta($this->order_id, 'wf_woo_dhl_return_service_code', $this->service_code, true);
            }

            if ($this->add_trackingpin_shipmentid == 'yes' && !empty($shipmentId)) {
                $this->order->add_order_note(sprintf(__('DHL Tracking-pin #: <a href="http://www.dhl.com/en/express/tracking.html?AWB=%s" target="_blank">%s</a>', 'wf-shipping-dhl'), $shipmentId, $shipmentId), true);
            }
        }

        if (!empty($result['ErrorMessage'])) {
            $this->shipmentErrorMessage .= $result['ErrorMessage'];
        }
    }

    private function wf_load_order($orderId) {
        if (!class_exists('WC_Order')) {
            return false;
        }
        return new WC_Order($orderId);
    }

    private function wf_get_parcel_details($dhl_packages) {
        $orderid = get_option('current_order_id');
        $order_insurance = get_post_meta($orderid, 'wf_dhl_insurance', true);

        $complete_box = array();

        if ($dhl_packages) {
            foreach ($dhl_packages as $key => $parcel) {
                $box_details = "<br/><table class='wf-shipment-package-table' style='border:1px solid lightgray;margin: 5px;margin-top: 5px;box-shadow:.5px .5px 5px lightgrey;'>
                    <tr>
                        <td style='font-weight: bold;
                        padding: 5px;
                    '>BOX/ITEM</td><td style='
                        padding: 5px;
                    font-weight: bold;'>Weight</td><td style='
                        padding: 5px;
                    font-weight: bold;'>Length</td><td style='
                        padding: 5px;
                    font-weight: bold;'>Width</td><td style='
                        padding: 5px;
                    font-weight: bold;'>Height</td>";

                if ($order_insurance == 'yes') {
                    $box_details .= "<td style='padding: 5px;font-weight: bold;'>Insurance </td>";
                }

                $box_details .= "</tr>";
                $product_name = '';
                $product_name_string = '';
                $count = 0;
                foreach ($parcel as $key => $value) {
                    $packed_products = (isset($value['packed_products']) && !empty($value['packed_products']))? $value['packed_products']: array();;
                    $product_name = '';
                    ++$count;
                    if(!isset($value['quantity'])){
                        $value['quantity'] = 1;
                    }

                    if ($this->packing_method == "per_item") {
                        foreach ($packed_products as $packed_product) {
                            if($this->is_woocommerce_composite_products_installed){
                                $product_name_string = isset($value['composite_title'])? $value['composite_title']: '';
                                if(empty($product_name_string)){
                                    $product_name_string = elex_dhl_get_product_name($packed_product);  
                                }    
                            }else{
                                $product_name_string = elex_dhl_get_product_name($packed_product);
                            }
                            $product_name_string .= ' x '.$value['quantity'];
                        }
                    }

                    if ($this->packing_method == "weight_based") {
                        foreach ($packed_products as $packed_product) {
                            $product_name = elex_dhl_get_product_name($packed_product).' , '. $product_name;
                            $product_name = rtrim($product_name, ", ");
                            $product_name_string = "Weight Pack " . $count . "(" . $product_name . ")";
                        }
                    }

                    $box_details .= "<tr>";
                    if (!empty($value['package_id'])) {
                        $box_details .= '<td style="padding: 5px;">' . strtoupper(str_replace('_', ' ', $value['Name'])) . '</td>';
                    } else if(isset($value['Name']) && !empty($value['Name'])){
                        $box_details .= '<td style="padding: 5px;">' . $value['Name'] . '</td>';
                    }else{
                        if($this->packing_method == "box_shipping" && empty($product_name_string)){
                            $product_name_string = 'Custom Box';
                        }
                        $box_details .= '<td style="padding: 5px;">' . $product_name_string . '</td>';
                    }

                    if (isset($value['Weight'])) {
                        $package_weight_shipped = round($value['Weight']['Value'], 3);
                        $value['Weight']['Units'] = isset($value['Weight']['Units'])? $value['Weight']['Units']: $this->weight_unit;
                        $box_details .= '<td style="padding: 5px;">' . $package_weight_shipped . ' ' . $value['Weight']['Units'] . '</td>';
                    } else {
                        $box_details .= '<td style="padding: 5px;">-</td>';
                    }

                    if (isset($value['Dimensions'])) {
                        $value['Dimensions']['Units'] = isset($value['Dimensions']['Units']) ? $value['Dimensions']['Units'] : '';
                        $box_details .= '<td style="padding: 5px;">' . $value['Dimensions']['Length'] . ' ' . $value['Dimensions']['Units'] . '</td>';
                        $box_details .= '<td style="padding: 5px;">' . $value['Dimensions']['Width'] . ' ' . $value['Dimensions']['Units'] . '</td>';
                        $box_details .= '<td style="padding: 5px;">' . $value['Dimensions']['Height'] . ' ' . $value['Dimensions']['Units'] . '</td>';
                    } else {
                        $box_details .= '<td style="padding: 5px;">-</td><td style="padding: 5px;">-</td><td style="padding: 5px;">-</td>';
                    }

                    if (isset($value['InsuredValue'])) {
                        if ($order_insurance == 'yes') {
                            $box_details .= '<td style="padding: 5px;">' . $value['InsuredValue']['Amount'] . ' ' . get_woocommerce_currency() . '</td>';
                        }
                    }
                }
                $box_details .= '</tr></table>';
                $complete_box[] = $box_details;
            }
        }
        return $complete_box;
    }

    // Alter package type as per user selection in settings - for print label API
    private function wf_get_pack_type($selected = '') {
        $pack_type = 'OD';
        if ($selected == 'FLY') {
            $pack_type = 'DF';
        } elseif ($selected == 'BOX') {
            $pack_type = 'OD';
        } elseif ($selected == 'YP') {
            $pack_type = 'YP';
        }
        return $pack_type;
    }

}
