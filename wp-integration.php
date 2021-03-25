<?php
function mna_add_settings_page() {
	add_options_page( 'MeatNight API settings page', 'Meat Night API', 'manage_options', 'meat_night_api', 'mna_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'mna_add_settings_page' );

function mna_render_plugin_settings_page() {
	require_once __DIR__ .'/mna-settings.php';
}

function mna_register_settings() {
	register_setting( 'meat_night_api_options', 'meat_night_api_options', 'meat_night_api_options_validate' );
	add_settings_section( 'api_settings', 'API Settings', 'mna_plugin_section_text', 'meat_night_api' );

	add_settings_field( 'mna_plugin_setting_api_enabled', 'API Plugin', 'mna_plugin_setting_api_enabled', 'meat_night_api', 'api_settings' );
	add_settings_field( 'mna_plugin_setting_api_rest_local_id', 'Rest Local ID', 'mna_plugin_setting_api_rest_local_id', 'meat_night_api', 'api_settings' );
	add_settings_field( 'mna_plugin_setting_api_send_to_printer', 'Send to printer', 'mna_plugin_setting_api_send_to_printer', 'meat_night_api', 'api_settings' );
	add_settings_field( 'mna_plugin_setting_api_show_debug', 'Debug', 'mna_plugin_setting_api_show_debug', 'meat_night_api', 'api_settings' );
}
add_action( 'admin_init', 'mna_register_settings' );

function meat_night_api_options_validate( $input ) {
	//$newinput['api_key'] = trim( $input['api_key'] );
	//if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
	//	$newinput['api_key'] = '';
	//}

	return $input;
}
function mna_plugin_section_text() {
	echo '<p>Here you can set all the options for using the Meat Night API</p>';
}

function mna_plugin_setting_api_enabled() {
	$options = get_option( 'meat_night_api_options' );
	echo "<input id='mna_plugin_setting_api_enabled' name='meat_night_api_options[api_enabled]' type='checkbox' ".checked( esc_attr( $options['api_enabled'] ), 1 )." value='1' />";
}
function mna_plugin_setting_api_rest_local_id() {
	$options = get_option( 'meat_night_api_options' );
	echo "<input id='mna_plugin_setting_api_rest_local_id' name='meat_night_api_options[api_rest_local_id]' type='radio' ".checked( esc_attr( $options['api_rest_local_id'] ), 0 )." value='' />";
	echo "<input id='mna_plugin_setting_api_rest_local_id' name='meat_night_api_options[api_rest_local_id]' type='radio' ".checked( esc_attr( $options['api_rest_local_id'] ), 1 )." value='' />";
}

function mna_plugin_setting_api_show_debug() {
	$options = get_option( 'meat_night_api_options' );
	echo "<input id='mna_plugin_setting_api_show_debug' name='meat_night_api_options[api_show_debug]' type='checkbox' ".checked( esc_attr( $options['api_show_debug'] ), 1 )." value='1' />";
}

function mna_plugin_setting_api_send_to_printer() {
	$options = get_option( 'meat_night_api_options' );
	echo "<input id='mna_plugin_setting_api_send_to_printer' name='meat_night_api_options[api_send_to_printer]' type='checkbox' ".checked( esc_attr( $options['api_send_to_printer'] ), 1 )." value='1' />";
}