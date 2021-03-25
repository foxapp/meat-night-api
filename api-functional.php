<?php
function mna_send_to_api_order_details( $order_id ) {
	if ( ! $order_id )
		return;
	//$parent_id = 0;
	//$order->parent_id > 0
	//Detect Lafka global addons
	$global_addons = [];
	$query = new WP_Query(array(
		'post_type' => 'lafka_glb_addon',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	));
	while ($query->have_posts()) {
		$query->the_post();
		//$global_addons[] = get_the_ID();
		$product_addons = get_post_meta(get_the_ID(), '_product_addons',true);
		$global_addons[get_the_title()] = $product_addons[0]["options"];

	}
	wp_reset_query();
	if(isset($_GET['debug']) && $_GET['debug']==='all') {
		echo '<pre style="direction: ltr">';
		echo print_r($global_addons);
		echo '</pre>';
	}

	/////////////////////////////////

	// Allow code execution only once
	if( !get_post_meta( $order_id, '_thankyou_action_done', true ) ) { // || 2==2 || isset($_GET['debug'])

		// Get an instance of the WC_Order object
		$order = wc_get_order( $order_id );

		// Get the order key
		$order_key = $order->get_order_key();

		// Get the order number
		$order_key          = $order->get_order_number();
		$get_payment_method = $order->get_payment_method();

		if ( $order->is_paid() ) {
			$paid = __( 'yes' );
		} else {
			$paid = __( 'no' );
		}

		// Loop through order items
		$order_items   = array();
		$item_position = 1;
		foreach ( $order->get_items() as $item_id => $item ) {

			// Get the product object
			$product = $item->get_product();
			$strings = array();

			// Get the product Id
			$product_id = $product->get_id();

			// Get the product name
			$product_name = $item->get_name();

			$item_meta_data = $item->get_meta_data();
			$product_addns  = [];

			foreach ( $item_meta_data as $meta_data_item ) {

				$s    = explode( "(", $meta_data_item->key );
				$addn = trim( $s[0] );

				//We try to get price from key
				preg_match( '#\((.*?)\)#', $meta_data_item->key, $match );
				$price_sub_addon_ = $match[1];
				//$price_meta = explode('₪', $price_meta_);

				//explode by space between moned symbol and price
				$price_sub_addon = explode( '&nbsp;', $price_sub_addon_ );

				//Detect if symbol are from right or from left(for RTL)
				if ( strpos( $price_sub_addon[0], '&' ) !== false ) {
					$price_sub_addon = $price_sub_addon[1];
				} else {
					$price_sub_addon = $price_sub_addon[0];
				}

				//$price_sub_addon = substr($price_sub_addon_, 13);
				$product_addns[$addn][] = array(
					'name'  => $meta_data_item->value,
					'price' => $price_sub_addon,
					'sku'   => ''
				);
				if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'all' ) {
					echo '<pre style="direction: ltr">';
					print_r( $meta_data_item );
					//	print_r($product_addns);
					echo '<pre>';
				}


				/*
				 * NEW VERSION
				 *

                var_dump($meta_data_item['label']);
				$product_addns[] = array(
					'name'=>$meta_data_item['label'],
					'price'=>$meta_data_item['price'],
					'sku'=>$meta_data_item['sku']
				);*/
			}

			//Try to find SKU addons from Global addons

			foreach ( $product_addns as $glbl => $sub_addons_array ) {
				if ( array_key_exists( $glbl, $global_addons ) ) {
					foreach ( $sub_addons_array as $key_subaddon => $sub_addon ) {
						foreach ( $global_addons[ $glbl ] as $global_addons_array ) {
							if ( $global_addons_array['label'] === $sub_addon['name'] ) {
								$product_addns[ $glbl ][ $key_subaddon ]["sku"] = $global_addons_array['sku'];
							}
						}
					}
				}
			}

			//echo '<pre>';
			if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'all' ) {
				echo '<pre style="direction: ltr">';
				echo 'Final:' . PHP_EOL;
				print_r( $product_addns );
				echo '</pre>';
			}

			//
			//product_addon_option_sku
			//foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			//	$value     = $meta->display_value;
			//	$strings[] = $meta->display_key .'/'.$value;
			//}
			//var_dump($strings);
			//echo '</pre>';

			//$product_addons = (array) $product->get_meta( '_product_addons' );
			//$product_addons = get_post_meta(3954, '_product_addons');//
			//echo '<pre>';
			//var_dump($product_addons);
			//echo '</pre>';
			//$text = 'ignore everything except this (text)';
			//preg_match('#\((.*?)\)#', $text, $match);
			//print $match[1];
			/*
			$strings2 = array();
			foreach ( $item->get_formatted_meta_data( '' ) as $meta_id => $meta ) {
				$value     = $meta->display_key;
				preg_match('#\((.*?)\)#', $value, $match);
				$price_meta_ = $match[1];
				//var_dump($meta->get_meta());
				//$price_meta = explode('₪', $price_meta_);
				$price_meta = substr($price_meta_, 13);
				//$price_meta = preg_replace("/[^0-9.]/", "", $price_meta_);
				//var_dump($price_meta);
				//print $match[1];
				$strings2[] =  rtrim(wp_kses($meta->display_value, [])).'/'.$price_meta;
			}
			echo '<pre>';
			echo 'Ce avem la moment:'.PHP_EOL;
			var_dump($strings2);
			//var_dump($allmeta);
			echo '</pre>';
			*/

			$order_items[] = array(
				'item_pozition'  => $item_position,
				'item_level'     => 0,
				'item_type'      => 'product',//$item->get_type()
				'item_source_id' => (string) $product_id,
				'item_pos_id'    => (string) $product->get_sku() ?? 'missing sku',
				//'item_pos_id' => (string) $item->get_variation_id(),
				'item_name'      => $item->get_name(),
				'item_price'     => number_format( (float) $product->get_price(), 2 ) ?? 0.00,
				'item_qty'       => number_format( (float) $item->get_quantity(), 2 ) ?? 0.00,
				'item_discount'  => number_format( (float) $product->get_sale_price(), 2 ) ?? 0.00,//discount_total
				'item_total'     => number_format( (float) $item->get_total(), 2 ) ?? 0.00,//total
				'item_comment'   => '',//???
			);
			$item_position ++;
			//Detect if we have addons for this product
			if ( is_array( $product_addns ) && count( $product_addns ) > 0 ) {
				foreach ( $product_addns as $current_item ) {
					foreach ( $current_item as $addon_item ) {
						//var_dump($addon_item['sku']);
						$order_items[] = array(
							'item_pozition'  => $item_position,
							'item_level'     => 1,
							'item_type'      => 'addon',
							'item_source_id' => (string) $product_id . '.' . $item_position,
							'item_pos_id'    => (string) $addon_item['sku'] ?? 0,
							'item_name'      => $addon_item['name'],
							'item_price'     => number_format( (float) $addon_item['price'], 2 ) ?? 0.00,
							'item_qty'       => number_format( (float) $item->get_quantity(), 2 ) ?? 1.00,
							'item_discount'  => "0.00",//discount_total
							'item_total'     => number_format( (float) $addon_item['price'], 2 ) ?? 0.00,//total
							'item_comment'   => '',//???
						);
						$item_position++;
					}
				}
			}
		}


		$paiment_id = 0;

		if ( $order->get_payment_method() === 'cod' ) {
			$paiment_id = 1;
		} elseif ( $order->get_payment_method() === 'icredit_payment' ) {
			$paiment_id = 3;
			$cc_number  = get_post_meta( $order_id, 'icredit_ccnum', true );
			/*
			switch(get_post_meta( $order_id, 'icredit_cardname', true )){
				case '(ויזה) Cal': $cc_card_type = 1; break;
				case 'יורוקרד/מסטרקרד': $cc_card_type = 2; break;//Eurocard / MasterCard
				case 'Visa': $cc_card_type = 3; break;
				default: $cc_card_type = 0; break;
			}
			*/
			$payments_card_details = array(
				'cc_number'      => '',//$cc_number
				'cc_card_type'   => 2, //$cc_card_type,//substr($cc_number, 0, 4),
				'cc_exp'         => '',
				'cc_cvv'         => '',
				'cc_owner_name'  => '',
				'cc_owner_id'    => '',
				'cc_owner_phone' => '',
			);
		}

		$payments = array(
			array(
				'payment_id'   => $paiment_id,//$order->get_payment_method(),
				'payment_type' => $order->get_payment_method_title(),
				'amount'       => number_format( (float) $order->get_total(), 2 ) ?? 0.00,
			)
		);

		if ( $order->get_payment_method() === 'icredit_payment' ) {
			$payments[0]['payment_card_dets'] = $payments_card_details ?? [];
			if($order->parent_id > 0){
				$payments[1] =
					array(
						'payment_id'   => 1,
						'payment_type'=> 'Cash',
						'amount'       => (isset($_COOKIE['second_amount']))?number_format( $_COOKIE['second_amount'], 2 ) : 0.00,
					);
			}
		}

		if ( isset( $_GET['debug'] ) && $_GET['debug']==='all') {
			echo '<pre style="direction: ltr">';
			print_r( $order );
			echo '</pre>';
		}

		// Output some data
		//echo '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . '</p>';

		// Flag the action as done (to avoid repetitions on reload for example)
		$order->update_meta_data( '_thankyou_action_done', true );

		$parent_order = 0;//Default

		if ($order->get_parent_id() === 0){
			$order->update_status( 'completed' );//Mark as completed if is paid with cache only(full payment) or COD
		}else{
			$parent_order = wc_get_order($order->get_parent_id());
			$parent_order->update_status( 'completed' );//Mark as completed if is paid with cache(partial payment)
			$parent_order->save();
		}
		$order->save();


		// Setup request to send json via POST

		//$store_address     = get_option( 'woocommerce_store_address' );
		//$store_address_2   = get_option( 'woocommerce_store_address_2' );
		//$store_city        = get_option( 'woocommerce_store_city' );
		//$store_postcode    = get_option( 'woocommerce_store_postcode' );

		//var_dump(get_post_meta( $order->get_id()));
		//echo $result;

		$data = array(
			'order_date_delivery' => ( $order->get_date_created()->getTimestamp() ) * 1000,
			'order_rest'          =>
				array(
					'rest_source_id' => '99999',
					'rest_local_id'  => '4700',//4700 real local id Ramas Gan, 4701 tel Aviv, 99999 - Testing
					//'rest_local_id'=> '99999',//4700 real local id Ramas Gan, 4701 tel Aviv, 99999 - Testing
					'rest_name'      => 'Meatnight',
				),
			'num_of_dinners'      => 0,
			'address'             =>
				array(
					'city'           => WC()->countries->get_states( 'IL' )[ $order->get_billing_state() ],
					//$order->get_billing_city()
					'neighborhood'   => '',
					'street'         => trim($order->get_billing_address_1()),
					//$order->get_address()
					'house_number'   => get_post_meta( $order->get_id(), '_billing_address_house_number', true ),
					//$order->get_billing_address_house_number()
					'entrance'       => get_post_meta( $order->get_id(), '_billing_address_entrance', true ),
					//$order->get_billing_address_entrance(),
					'floor_num'      => get_post_meta( $order->get_id(), '_billing_address_floor_number', true ),
					//$order->get_billing_address_floor_number(),
					'appartment'     => get_post_meta( $order->get_id(), '_billing_address_appartment', true ),
					//$order->get_billing_address_appartment(),
					'address_remark' => get_post_meta( $order->get_id(), '_billing_address_remark', true ),
					//$order->get_billing_address_remark(),
					'gps_geo_lat'    => 0.0,
					'gps_geo_lng'    => 0.0
				),
			'order_contact'       =>
				array(
					array(
						'contact_firstname' => $order->get_billing_first_name(),
						'contact_lastname'  => $order->get_billing_last_name(),
						'contact_phone'     => $order->get_billing_phone(),
						'contact_cellular'  => $order->get_billing_phone(),
						'contact_email'     => $order->get_billing_email(),
						'contact_fax'       => '',
						'contact_id'        => (string) $order->get_user_id()//$order->get_customer_id()
					)
				),
			'order_items'         => $order_items,
			'payments'            => $payments,
			'delivery_fee'        =>
				($order->parent_id == 0)?
					number_format( $order->get_shipping_total(), 2 ):
					number_format( $parent_order->get_shipping_total(), 2 ),
			'order_discount'      =>
				($order->parent_id == 0)?
					number_format( $order->get_total_discount(), 2 ):
					"0.00",
			'order_total'         =>
				($order->parent_id == 0)?
					number_format( $order->get_total(), 2 ):
					number_format( $parent_order->get_total(), 2 ),
			'order_source'        =>
				array(
					'source_name'     => site_url(),
					'source_order_id' => (string) $order_id
				),
			'order_remark'        =>
				($order->parent_id == 0)?
					$order->get_customer_note():
					"תשלום חלקי!",
		);

		if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'api' ) {
			echo '<pre style="direction: ltr">';
			print_r( $data );
			echo json_encode( $data, JSON_UNESCAPED_UNICODE );
			echo '</pre>';
		}


		/*
		* Send date to API
		*/

		// API URL
		//if(!isset($_GET['debug']) ) {

		include_once __DIR__ .'/adapter/Beecomm.php';
		$client_id = '';
		$client_secret = '';
		$beecommApi = new BeecommApi("example.com", "XXXXXXXX");
		$url = 'http://2order.org/api/restgenord/SaveOrder';
		//$postdata = json_encode($data, JSON_UNESCAPED_UNICODE);
		$payload = json_encode( $data, JSON_UNESCAPED_UNICODE );
		$ch      = curl_init( $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLINFO_HEADER_OUT, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen( $payload )
			)
		);

		//Disable send to printer
		$result = [];
		if(2==3){
			$result = curl_exec( $ch );
			curl_close( $ch );

			$result_decoded = json_decode( $result );
			//var_dump($result_decoded);
			if ( is_object( $result_decoded ) ) {
				if ( $result_decoded->error ) {
					$post_id = wp_insert_post(
						array(
							'post_name'    => 'error-' . $order_id,
							'post_title'   => ($order->parent_id == 0)?'Transaction Error on order : ' . $order_id:'Transaction Error on parent order : ' . $order->parent_id,
							'post_content' => $result_decoded->error->description . '<br><a href="/wp-admin/post.php?post=' . $order_id . '&action=edit">View Order</a>',
							'post_type'    => 'api_transaction',
							'post_status'  => 'publish'
						),
						false
					);
					wp_set_object_terms( $post_id, 244, 'transaction_type' );
				} elseif ( $result_decoded->value ) {
					$post_id = wp_insert_post(
						array(
							'post_name'    => 'success-' . $order_id,
							'post_title'   => ($order->parent_id == 0)?'Transaction Success on order : ' . $order_id:'Transaction Success on partial order : ' . $order->parent_id,
							'post_content' => 'Transaction Order ID: ' . $result_decoded->value->orderId . '<br><a href="/wp-admin/post.php?post=' . $order_id . '&action=edit">View Order</a>',
							'post_type'    => 'api_transaction',
							'post_status'  => 'publish'
						),
						false
					);
					wp_set_object_terms( $post_id, 245, 'transaction_type' );
				}
				if($post_id){
					$meta_key = 'json_body';
					$meta_value = $payload;
					$unique = true;
					add_post_meta( $post_id, $meta_key, $meta_value, $unique );
				}
			}
		}

	}

	if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'api' ) {
		echo '<pre>Result:<br>';
		echo $result;
		echo '</pre>';
	}
	// Close cURL resource
	//}
	///
}