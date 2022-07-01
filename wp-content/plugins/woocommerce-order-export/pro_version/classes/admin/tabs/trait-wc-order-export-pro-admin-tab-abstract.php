<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait WC_Order_Export_Pro_Admin_Tab_Abstract {
	use WC_Order_Export_Pro_Admin_Tab_Abstract_Ajax;

	public function get_template_path() {
		return WOE_PRO_PLUGIN_BASEPATH . '/view/';
	}

	public function add_destinations_to_view( $settings ) {
		$this->render_template( 'destinations', array(
			'settings' => $settings,
			'tab'      => $this,
		), $this->get_template_path() );
	}

	public function get_value( $arr, $name ) {

		$arr_name = explode( ']', $name );
		$arr_name = array_map( function ( $name ) {
			if ( substr( $name, 0, 1 ) == '[' ) {
				$name = substr( $name, 1 );
			}

			return trim( $name );
		}, $arr_name );

		$arr_name = array_filter( $arr_name );

		foreach ( $arr_name as $value ) {
			$arr = isset( $arr[ $value ] ) ? $arr[ $value ] : "";
		}

		return $arr;
	}

	public function tweak_refund_settings() {
		// for refunds -- Allow to export fully refunded items by default
		if( !empty($_GET['woe_post_type']) AND $_GET['woe_post_type'] == 'shop_order_refund') {
			add_filter( 'woe_settings_page_prepare', function($settings) {
				$settings['skip_refunded_items'] = 0;
				return $settings;
			});
		}
	}
}