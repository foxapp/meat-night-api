<?php

use MeatNightApi\Adapter\Beecomm\BeecommApi;

defined( 'ABSPATH' ) or die( 'No script kiddies please!');
/**
 * Plugin Name: Meat Night API connection to Beecomm API
 * Plugin URI: https://github.com/foxapp/meat-night-api
 * Description: Connection between Meat Night orders and API Server(to printer).
 * Version: 1.0
 * Author: Ion Enache
 * Author URI: https://www.foxapp.net
 */

include_once __DIR__ .'/wp-integration.php';
include_once __DIR__ .'/api-transactions.php';
include_once __DIR__ .'/api-functional.php';

add_action('woocommerce_thankyou', 'mna_connect_api_to_order', 3, 1);

if(!function_exists('mna_connect_api_to_order')){
	function mna_connect_api_to_order(){
		$connection_to_api = new BeecommApi();
	}
}