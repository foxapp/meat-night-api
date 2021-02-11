<?php
/**
 * Post Type: API Transactions.
 */

function mna_register_api_transaction() {
	$labels = [
		"name" => __( "API Transactions", "meat-night-api" ),
		"singular_name" => __( "Transaction", "meat-night-api" ),
	];

	$args = [
		"label" => __( "API Transactions", "meat-night-api" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "api_transaction", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail" ],
	];

	register_post_type( "api_transaction", $args );
}

add_action( 'init', 'mna_register_api_transaction' );



/**
 * Taxonomy: Return Types.
 */
function mna_register_my_taxes_transaction_type() {
	$labels = [
		"name" => __( "Return Types", "meat-night-api" ),
		"singular_name" => __( "Return Type", "meat-night-api" ),
	];

	$args = [
		"label" => __( "Return Types", "meat-night-api" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'transaction_type', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"rest_base" => "transaction_type",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
	];
	register_taxonomy( "transaction_type", [ "api_transaction" ], $args );
}
add_action( 'init', 'mna_register_my_taxes_transaction_type' );