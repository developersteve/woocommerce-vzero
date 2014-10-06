<?php
/*
Plugin Name: WooCommerce V.Zero plugin
Plugin URI: http://developer.getbraintreepayments.com
Description: A payment gateway for Braintree V.Zero SDK.
Version: 1.0
Author: DeveloperSteve
Author URI: http://developersteve.com/

	License:
	License URI:

*/

/**
 * Required functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Plugin updates
 */

function activate_woocommerce_vzero() {
	global $woocommerce;

	include_once $woocommerce->plugin_path() . '/includes/admin/wc-admin-functions.php';
}

register_activation_hook( __FILE__, 'activate_woocommerce_vzero' );


function woocommerce_gateway_vzero_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	class WC_Gateway_VZero extends WC_Payment_Gateway {
		public function __construct() {
			global $woocommerce;

			$this->id = 'braintree_vzero';
			$this->method_description = "Accept payment via Braintree";
			$this->method_title = __( 'Braintree V.Zero', 'braintree_vzero' );

			$this->init_settings();
			$this->enabled = $this->settings['enabled'];
			$this->title = $this->settings['title'];
			$this->has_fields = true;
			$this->brand_name = $this->settings['brand_name'];

			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable/Disable', 'braintree_vzero' ),
					'type' => 'checkbox',
					'label' => __( 'Enable Braintree V.Zero', 'braintree_vzero' ),
					'default' => 'yes'
				),
				'sandbox' => array(
					'title' => __( 'Braintree Sandbox', 'braintree_vzero' ),
					'type' => 'checkbox',
					'label' => __( 'Enable Sandbox', 'braintree_vzero' ),
					'default' => 'yes',
					'desc_tip' => true,
					'description' => __( 'Enables sandbox for testing.', 'braintree_vzero' ),
				),
				'title' => array(
					'title' => __( 'Title', 'braintree_vzero' ),
					'type' => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'braintree_vzero' ),
					'default' => __( 'Pay using Credit Card or PayPal', 'braintree_vzero' ),
					'desc_tip'      => true,
				),
				'description' => array(
					'title' => __( 'Customer Message', 'braintree_vzero' ),
					'type' => 'textarea',
					'default' => '',
					'desc_tip' => true,
					'description' => __( 'A message to the user on the checkout page', 'braintree_vzero' ),
				),
				'api_merchantId' => array(
					'title' => __( 'merchantId', 'braintree_vzero' ),
					'type' => 'text',
					'description' => __( 'This is for the Merchant ID.', 'braintree_vzero' ),
					'default' => '',
					'desc_tip' => true,
				),
				'api_publicKey' => array(
					'title' => __( 'publicKey', 'braintree_vzero' ),
					'type' => 'text',
					'description' => __( 'This is for the publicKey.', 'braintree_vzero' ),
					'default' => '',
					'desc_tip' => true,
				),
				'api_privateKey' => array(
					'title' => __( 'privateKey', 'braintree_vzero' ),
					'type' => 'text',
					'description' => __( 'This is for the privateKey.', 'braintree_vzero'),
					'default' => '',
					'desc_tip' => true,
				),
			);

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

		}

		public function payment_scripts() {
			if ( ! is_checkout() || ! $this->is_available() ) {
				return;
			}
    		wp_enqueue_script( 'wc-braintree-js', 'https://js.braintreegateway.com/v1/braintree.js', array( 'jquery' ), WC_VERSION, true );
    		wp_enqueue_script( 'wc-vzero-js', plugins_url( 'js/vzero.js' , __FILE__ ), array( 'jquery' ), WC_VERSION, true );
		}

		public function process_payment( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );
			echo "test";
		 //    return array(
			// 	'result'   => 'fail',
			// 	'redirect' => ''
			// );

			// print_r($_POST);
		}

		function payment_fields() {
			global $woocommerce;

			$vzero_settings = get_option('woocommerce_braintree_vzero_settings');

			if(!$vzero_settings['api_merchantId'] && !$vzero_settings['api_publicKey'] && !$vzero_settings['api_privateKey']){
				echo "Please configure the V.Zero API keys.";
			}
			else{
				require_once( 'braintree/braintree.php' );

				if($vzero_settings['title']=="no") $env = "production";
				else $env = "sandbox";

				Braintree_Configuration::environment($env);
				Braintree_Configuration::merchantId($vzero_settings['api_merchantId']);
				Braintree_Configuration::publicKey($vzero_settings['api_publicKey']);
				Braintree_Configuration::privateKey($vzero_settings['api_privateKey']);

				$token = Braintree_ClientToken::generate(array());

			    echo "<div id=\"vzero_checkout\"></div>";
			    echo "<input type='hidden' id='nonce'>";
			    echo "<script src=\"https://js.braintreegateway.com/v2/braintree.js\"></script>";
			    echo "<script>braintree.setup(\"".$token."\", 'dropin', {container: 'vzero_checkout', paymentMethodNonceReceived: function(event, nonce){ jQuery('#nonce').val(nonce); alert(nonce);}  });</script>";
			    echo "<div style=\"clear: both;\"></div>";
			}

		}

	}

	function add_vzero_gateway($methods) {
		$methods[] = 'WC_Gateway_VZero';

		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_vzero_gateway' );
}

add_action( 'plugins_loaded', 'woocommerce_gateway_vzero_init', 0 );
