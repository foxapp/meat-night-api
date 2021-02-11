<?php
function cptui_register_my_cpts_api_transaction() {

	/**
	 * Post Type: API Transactions.
	 */

	$labels = [
		"name" => __( "API Transactions", "lafka-child" ),
		"singular_name" => __( "Transaction", "lafka-child" ),
	];

	$args = [
		"label" => __( "API Transactions", "lafka-child" ),
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

add_action( 'init', 'cptui_register_my_cpts_api_transaction' );
function cptui_register_my_taxes_transaction_type() {

	/**
	 * Taxonomy: Return Types.
	 */

	$labels = [
		"name" => __( "Return Types", "lafka-child" ),
		"singular_name" => __( "Return Type", "lafka-child" ),
	];

	$args = [
		"label" => __( "Return Types", "lafka-child" ),
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
add_action( 'init', 'cptui_register_my_taxes_transaction_type' );