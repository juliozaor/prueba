<div id="my-export-options" class="my-block">
    <div class="wc-oe-header">
		<?php _e( 'Export date range', 'woocommerce-order-export' ) ?>:
    </div>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( !isset( $settings['export_rule'] ) || ( $settings['export_rule'] == 'none' ) ) ? 'checked' : '' ?>
               value="none">
		<?php _e( 'None', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'today' ) ) ? 'checked' : '' ?>
               value="today">
		<?php _e( 'Today', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'last_day' ) ) ? 'checked' : '' ?>
               value="last_day">
		<?php _e( 'Yesterday', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'this_week' ) ) ? 'checked' : '' ?>
               value="this_week">
		<?php _e( 'Current week', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'this_month' ) ) ? 'checked' : '' ?>
               value="this_month">
		<?php _e( 'Current month', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'last_week' ) ) ? 'checked' : '' ?>
               value="last_week">
		<?php _e( 'Last week', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'last_month' ) ) ? 'checked' : '' ?>
               value="last_month">
		<?php _e( 'Last month', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'last_quarter' ) ) ? 'checked' : '' ?>
               value="last_quarter">
		<?php _e( 'Last quarter', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'this_year' ) ) ? 'checked' : '' ?>
               value="this_year">
		<?php _e( 'This year', 'woocommerce-order-export' ) ?>
    </label>
    <br>
    <label>
        <input type="radio" name="settings[export_rule]"
               class="width-100" <?php echo ( isset( $settings['export_rule'] ) && ( $settings['export_rule'] == 'custom' ) ) ? 'checked' : '' ?>
               value="custom">
		<?php
		$input_days = isset( $settings['export_rule_custom'] ) ? $settings['export_rule_custom'] : 3;
		$input_days = '<input class="width-15" name="settings[export_rule_custom]" value="' . $input_days . '">';
		?>
		<?php echo sprintf( __( 'Last %s days', 'woocommerce-order-export' ), $input_days ) ?>
    </label>
</div>
<br>
