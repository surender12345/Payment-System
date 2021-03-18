<?php
/**
* Plugin Name: Payment System
* Description: This is the payment system plugin.
* Version: 1.00
**/


add_action('admin_menu', 'my_menu_pages');
function my_menu_pages(){
    add_menu_page('Payment System', 'Payment System', 'manage_options', 'payment-system', 'addPluginOptions' );
}


//////////////////////////////////////////////////////////


function myplugin_activate() {

  	wp_insert_term( 'Payment System', 'product_cat', array(
	    'description' => 'This category use for payment system calculator', // optional
	    'parent' => 0, // optional
	    'slug' => 'payment-system' // optional
	) );
}

//////////////////////////////////////////////////////////
register_activation_hook( __FILE__, 'myplugin_activate' );

function add_plugin_scripts() {

	  wp_enqueue_style( 'payment-style',  plugin_dir_url( __FILE__ ) . 'css/style.css?37'.time(), array(), '1.1', 'all');
	  wp_enqueue_script( 'jQuery-cdn', 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
	  wp_enqueue_script( 'payment-script', '/wp-content/plugins/payment-system/js/script.js', array (), 1.1, 'all');
}
add_action( 'wp_enqueue_scripts', 'add_plugin_scripts' );

//////////////////////////////////////////////////////////

function addPluginOptions(){
    require( plugin_dir_path( __FILE__ ) . 'admin/admin_setting.php');
}

//////////////////////////////////////////////////////////

// function that runs when shortcode is called
function payment_system() { 
	// Things that you want to do. 
	ob_start();
	require( plugin_dir_path( __FILE__ ) . 'front-end/calculator_view.php');
	return ob_get_clean();   
	
} 
// register shortcode
add_shortcode('payment_system', 'payment_system'); 


//////////////////////////////////////////////////////////



add_action( 'woocommerce_before_add_to_cart_button', 'enfold_customization_extra_product_content', 15 );
function enfold_customization_extra_product_content() {
	global $post;
	$terms = get_the_terms( $post->ID, 'product_cat' );
	foreach ($terms as $term) {
		if($term->slug == 'payment-system'){ 
			add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );


			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery(".single_add_to_cart_button").prop("disabled", true);
					jQuery(".cst-jobvalue").on('keyup change',function(){
						var inputdata = jQuery(this).val();
						var multiply = jQuery('#percentage').val();
					  	var totalsurcharge = inputdata * multiply;
					  	var totalvalue = + inputdata + totalsurcharge;
					  	jQuery('#Surcharge').val(totalsurcharge);
					  	jQuery('#Total').val(totalvalue);
					  	if(totalvalue == 0){
					  		jQuery(".single_add_to_cart_button").prop("disabled", true);
					  	}else{
					  		jQuery(".single_add_to_cart_button").prop("disabled", false);
					  	}
					});
				});
			</script>
			<style type="text/css">
				h1.product_title.entry-title {
				    display: none !important;
				}
				.single-product form.cart input {
				    width: 50% !important;
				}
				.woocommerce-product-gallery {
				    display: none !important;
				}
				.summary.entry-summary {
				    float: left !important;
				   
				}
			</style>
			<?php 
			echo do_shortcode("[payment_system]");
		}
	}

    
}

//////////////////////////////////////////////////////////

add_action('woocommerce_single_product_summary', 'customizing_single_product_summary_hooks', 2  );
function customizing_single_product_summary_hooks(){
	global $post;
	$terms = get_the_terms( $post->ID, 'product_cat' );
	foreach ($terms as $term) {
		if($term->slug == 'payment-system'){
        remove_action('woocommerce_single_product_summary','woocommerce_template_single_price',10  );
    	}
	}

}

//////////////////////////////////////////////////////////

function custom_remove_all_quantity_fields( $return, $product ) {
	global $post;
	$terms = get_the_terms( $post->ID, 'product_cat' );
	foreach ($terms as $term) {
		if($term->slug == 'payment-system'){
			return true;
		}
	}

}
add_filter( 'woocommerce_is_sold_individually','custom_remove_all_quantity_fields', 10, 2 );

//////////////////////////////////////////////////////////

function save_gift_wrap_fee( $cart_item_data, $product_id ) {
     
    if( isset( $_POST['invoice'] )) {
        $cart_item_data[ "custom_invoice" ] = $_POST['invoice'];     
    }
    if( isset( $_POST['jobvalue'] )) {
        $cart_item_data[ "custom_jobvalue" ] = $_POST['jobvalue'];     
    }
    if( isset( $_POST['surcharge'] )) {
        $cart_item_data[ "custom_surcharge" ] = $_POST['surcharge'];     
    }
    if( isset( $_POST['custom_price'] )) {
        $cart_item_data[ "custom_price" ] = $_POST['custom_price'];     
    }
    return $cart_item_data;
     
}
add_filter( 'woocommerce_add_cart_item_data', 'save_gift_wrap_fee', 99, 2 );


//////////////////////////////////////////////////////////

function calculate_gift_wrap_fee( $cart_object ) {
    if( !WC()->session->__isset( "reload_checkout" )) {
        /* Gift wrap price */
        foreach ( WC()->cart->get_cart() as $key => $value ) {
            if( isset( $value["custom_price"] ) ) { 
                if( method_exists( $value['data'], "set_price" ) ) {
                    $value['data']->set_price( $value["custom_price"] );
                } else {
                    $value['data']->price = ( $value["custom_price"] );                    
                }           
            }
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'calculate_gift_wrap_fee', 99 );

//////////////////////////////////////////////////////////


add_filter( 'woocommerce_add_to_cart_redirect', '_skip_cart_redirect_checkout' );
 
function _skip_cart_redirect_checkout( $url ) {

    return wc_get_checkout_url();
}



//////////////////////////////////////////////////////////


function render_meta_on_cart_and_checkout( $cart_data, $cart_item = null ) {
    $meta_items = array();
    /* Woo 2.4.2 updates */
    if( !empty( $cart_data ) ) {
        $meta_items = $cart_data;
    }
    if( isset( $cart_item["custom_surcharge"] ) ) {
        $meta_items[] = array( "name" => "Surcharge", "value" => wc_price($cart_item["custom_surcharge"]) );
    }
    return $meta_items;
}
add_filter( 'woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 99, 2 );

//////////////////////////////////////////////////////////

function gift_wrap_order_meta_handler( $item_id, $values, $cart_item_key ) {
    if( isset( $values["custom_surcharge"] ) ) {
        wc_add_order_item_meta( $item_id, "Surcharge", wc_price($values["custom_surcharge"]) );
    }
}
add_action( 'woocommerce_add_order_item_meta', 'gift_wrap_order_meta_handler', 99, 3 );

//////////////////////////////////////////////////////////



add_action('woocommerce_before_cart', 'bbloomer_check_category_in_cart');
 
function bbloomer_check_category_in_cart() {
 
	// Set $cat_in_cart to false
	$cat_in_cart = false;
	// Loop through all products in the Cart        
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	    // If Cart has category "payment-system", set $cat_in_cart to true
	    if ( has_term( 'payment-system', 'product_cat', $cart_item['product_id'] ) ) {
	       add_filter( 'woocommerce_is_sold_individually','custom_remove_all_quantity_fields1', 10, 2 );
	    }
	}
}

//////////////////////////////////////////////////////////

function custom_remove_all_quantity_fields1( $return, $product ) {
	return true;
}

//////////////////////////////////////////////////////////

function woo_remove_product_tabs( $tabs ) {
    unset( $tabs['description'] );      	// Remove the description tab
    unset( $tabs['reviews'] ); 			// Remove the reviews tab
    unset( $tabs['additional_information'] );  	// Remove the additional information tab
    return $tabs;

}




//////////////////////////////////////////////////////////


add_action('woocommerce_checkout_order_processed', 'before_checkout_create_order', 20, 1); 
function before_checkout_create_order( $order_id ) {
    if ( ! $order_id )
        return;
    foreach ( WC()->cart->get_cart() as $key => $value ) {
    	if(isset($value['custom_invoice']) && !empty($value['custom_invoice'])){
    		update_post_meta( $order_id, '_custom_invoice',$value['custom_invoice'] );
    	}
    	if(isset($value['custom_jobvalue']) && !empty($value['custom_jobvalue'])){
    		update_post_meta( $order_id, '_custom_jobvalue',$value['custom_jobvalue'] );
    	}
    }
}

//////////////////////////////////////////////////////////
	add_action( 'action_getTodayOrder', 'getTodayOrder' );
	function getTodayOrder($value='')
	{
		# code...
		if ( !is_admin() ) { 
			$csv_path = plugin_dir_path( __FILE__ ) . 'csv/orders-.csv';
			$rows   = array_map( 'str_getcsv', file( $csv_path ) );
	        global $wpdb;
			$date_from = date("Y-m-d");
			$date_to = date("Y-m-d");
			$post_status = implode("','", array('wc-processing', 'wc-completed') );
			$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts 
			            WHERE post_type = 'shop_order'
			            AND post_status IN ('{$post_status}')
			            AND post_date BETWEEN '{$date_from}  00:00:00' AND '{$date_to} 23:59:59'
			        ");
			$fp = fopen($csv_path, 'a');  //Open file for append
			$fp1 = fopen($csv_path, 'a');  //Open file for append
			if(isset($result) && !empty($result)){
				$array = array("*ContactName","EmailAddress","POAddressLine1","POAddressLine2","POAddressLine3","POAddressLine4","POCity","PORegion","POPostalCode","POCountry","*InvoiceNumber","*InvoiceDate","*DueDate","InventoryItemCode","Description","Quantity","*UnitAmount","*AccountCode","*TaxType","TrackingName1","TrackingOption1","TrackingName2","TrackingOption2","Currency");
				fputcsv($fp, $array); //@Optimist
				fclose($fp); //Close the file to free memory.

				foreach ($result as $key => $value) {
					$order = new WC_Order($value->ID);
					$billing_first_name = $order->get_billing_first_name();
					$get_billing_last_name = $order->get_billing_last_name();
					$get_billing_email = $order->get_billing_email();
					$get_billing_address_1 = $order->get_billing_address_1();
					$get_billing_address_2 = $order->get_billing_address_2();
					$get_billing_city = $order->get_billing_city();
					$get_billing_state = $order->get_billing_state();
					$get_billing_postcode = $order->get_billing_postcode();
					$get_billing_country = $order->get_billing_country();
					$_custom_invoice = get_post_meta($value->ID, '_custom_invoice', true );
					$get_date_created = $order->get_date_created();
					// Get and Loop Over Order Items
					
					foreach ( $order->get_items() as $item_id => $item ) {
					   $product_id = $item->get_product_id();
					   $variation_id = $item->get_variation_id();
					   $product = $item->get_product();
					   $name = $item->get_name();
					   $quantity = $item->get_quantity();
					   $subtotal = $item->get_subtotal();
					   $total = $item->get_total();
					   $tax = $item->get_subtotal_tax();
					   $taxclass = $item->get_tax_class();
					   $taxstat = $item->get_tax_status();
					   $allmeta = $item->get_meta_data();
					   $somemeta = $item->get_meta( '_whatever', true );
					   $type = $item->get_type();
					}
					

					 $array1 = array($billing_first_name.'  '.$get_billing_last_name,$get_billing_email,$get_billing_address_1,$get_billing_address_2,'','',$get_billing_city,$get_billing_state,$get_billing_postcode,$get_billing_country,$_custom_invoice,$get_date_created,'2 Days Later','',$_custom_invoice,$quantity,$subtotal,'cc','GST','','','','','');
					fputcsv($fp1, $array1); //@Optimist
				}
				fclose($fp1); //Close the file to free memory.

				// Recipient 
				$to = 'tester.webperfection@gmail.com'; 
				// Sender 
				$from = 'tester.webperfection@gmail.com'; 
				$fromName = 'Payment System'; 
				// Email subject 
				$subject = 'Order export';  
				// Attachment file 
				$file = $csv_path; 
				// Email body content 
				$htmlContent = ' 
				    <h3>Order export Email by Payment System</h3> 
				'; 
				// Header for sender info 
				$headers = "From: $fromName"." <".$from.">"; 
				// Boundary  
				$semi_rand = md5(time());  
				$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";  
				// Headers for attachment  
				$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 
				// Multipart boundary  
				$message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . 
				"Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";  
				 
				// Preparing attachment 
				if(!empty($file) > 0){ 
				    if(is_file($file)){ 
				        $message .= "--{$mime_boundary}\n"; 
				        $fp =    @fopen($file,"rb"); 
				        $data =  @fread($fp,filesize($file)); 
				 
				        @fclose($fp); 
				        $data = chunk_split(base64_encode($data)); 
				        $message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\n" .  
				        "Content-Description: ".basename($file)."\n" . 
				        "Content-Disposition: attachment;\n" . " filename=\"".basename($file)."\"; size=".filesize($file).";\n" .  
				        "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n"; 
				    } 
				} 
				$message .= "--{$mime_boundary}--"; 
				$returnpath = "-f" . $from; 
				// Send email 
				$mail = @mail($to, $subject, $message, $headers, $returnpath);  
				if($mail){
					$file_handle = fopen($csv_path, "w+");
				    $myCsv = array();
				    while (!feof($file_handle) ) {
				        $line_of_text = fgetcsv($file_handle, 1024); 
				        if ($id != $line_of_text[0]) {
				            fputcsv($file_handle, $line_of_text);
				        }
				    }
				    fclose($file_handle);
				}
			}
		    
	    }
	}