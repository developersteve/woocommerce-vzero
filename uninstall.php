<?php
/**
 * PayPal All-in-One  Uninstall
 *
 * Uninstalling PayPal All-in-One deletes pages.
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

global $wpdb;

wp_delete_post( get_option( 'woocommerce_review_order_page_id' ), true );
wp_delete_post( get_option( 'woocommerce_thankyou_page_id' ), true );
wp_delete_post( get_option('woocommerce_paypal_express_settings'),true);
