<?php
/**
 * Plugin Name: Order Redirects for WooCommerce
 * Plugin URI: https://wpsunshine.com/plugins/order-redirects-for-woocommerce
 * Description: Custom redirects after order for WooCommerce. Allows a global redirect URL for all orders or per product/variation redirect URLs with priority options.
 * Version: 1.0.3
 * Author: WP Sunshine
 * Author URI: https://wpsunshine.com
 * Text Domain: order-redirects-for-woocommerce
 */

defined( 'ABSPATH' ) || exit;

class WPSunshine_WC_Order_Redirects {

	public function __construct() {

		add_filter( 'woocommerce_get_sections_advanced', array( $this, 'redirect_settings_section' ) );
		add_filter( 'woocommerce_get_settings_advanced', array( $this, 'redirect_get_settings' ), 10, 2 );
		add_action( 'woocommerce_product_options_advanced', array( $this, 'product_redirect_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_redirect_fields' ) );
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'variation_redirect_fields' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_redirect_fields' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'redirect_after_checkout' ) );

		add_action(
			'before_woocommerce_init',
			function() {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );

				}
			}
		);

	}

	public function redirect_settings_section( $sections ) {
		$sections['wps_wc_redirects'] = __( 'Order Redirects', 'order-redirects-for-woocommerce' );
		return $sections;
	}

	public function redirect_get_settings( $settings, $current_section ) {

		if ( $current_section == 'wps_wc_redirects' ) {
			$settings   = array();
			$settings[] = array(
				'name' => __( 'Purchase Redirects', 'order-redirects-for-woocommerce' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wps_wc_redirects',
			);
			$settings[] = array(
				'name' => __( 'Default Redirect URL', 'order-redirects-for-woocommerce' ),
				'id'   => 'wps_wc_redirect_default',
				'type' => 'url',
				'desc' => __( 'If no redirects are found for any products or variations in the order, this URL will be used. Leave blank for the default receipt page URL.', 'order-redirects-for-woocommerce' ),
			);
			$settings[] = array(
				'name'     => __( 'Append order ID to all redirects', 'order-redirects-for-woocommerce' ),
				'desc_tip' => __( 'This will automatically add order_id=X parameter to all redirect URLs', 'order-redirects-for-woocommerce' ),
				'id'       => 'wps_wc_redirect_append_order_id',
				'type'     => 'checkbox',
				'desc'     => __( 'Enable', 'order-redirects-for-woocommerce' ),
			);
			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'wps_wc_redirects',
			);
		}

		return $settings;
	}


	public function product_redirect_fields() {
		woocommerce_wp_text_input(
			array(
				'id'          => 'redirect_url',
				// Causing issues when saving product, not sure why 'type' => 'url',
				'label'       => __( 'Purchase Redirect URL', 'order-redirects-for-woocommerce' ),
				'description' => '<a href="https://wpsunshine.com/documentation/template-tags/" target="_blank">' . __( 'See available variable tags', 'order-redirects-for-woocommerce' ) . '</a>',
				'desc_tip'    => false,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => 'redirect_url_priority',
				'type'        => 'number',
				'label'       => __( 'Purchase Redirect Priority', 'order-redirects-for-woocommerce' ),
				'description' => __( 'Set a priority on this redirect. Highest priority is used.', 'order-redirects-for-woocommerce' ),
				'desc_tip'    => true,
			)
		);
	}

	public function save_product_redirect_fields( $post_id ) {
		if ( ! empty( $_POST['redirect_url'] ) ) {
			update_post_meta( $post_id, 'redirect_url', $_POST['redirect_url'] );
		}
		if ( ! empty( $_POST['redirect_url_priority'] ) ) {
			update_post_meta( $post_id, 'redirect_url_priority', sanitize_text_field( $_POST['redirect_url_priority'] ) );
		}
	}

	public function variation_redirect_fields( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input(
			array(
				'id'    => 'redirect_url[' . $loop . ']',
				// 'type' => 'url',
				'label' => __( 'Redirect URL', 'order-redirects-for-woocommerce' ),
				'value' => esc_url( get_post_meta( $variation->ID, 'redirect_url', true ) ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'    => 'redirect_url_priority[' . $loop . ']',
				'type'  => 'number',
				'label' => __( 'Redirect Priority', 'order-redirects-for-woocommerce' ),
				'value' => esc_url( get_post_meta( $variation->ID, 'redirect_url_priority', true ) ),
			)
		);
	}

	public function save_variation_redirect_fields( $variation_id, $i ) {
		if ( ! empty( $_POST['redirect_url'][ $i ] ) ) {
			update_post_meta( $variation_id, 'redirect_url', $_POST['redirect_url'][ $i ] );
		}
		if ( ! empty( $_POST['redirect_url_priority'][ $i ] ) ) {
			update_post_meta( $variation_id, 'redirect_url_priority', sanitize_text_field( $_POST['redirect_url_priority'][ $i ] ) );
		}
	}

	public function redirect_after_checkout() {

		// Only run this when on the order received page after an order
		if ( ! is_wc_endpoint_url( 'order-received' ) || empty( $_GET['key'] ) ) {
			return;
		}
		$order_id  = wc_get_order_id_by_order_key( sanitize_text_field( $_GET['key'] ) );
		$order     = wc_get_order( $order_id );
		$items     = $order->get_items();
		$redirects = array();
		foreach ( $items as $item ) {

			$redirect_url = '';

			// See if this is a variable product that has a redirect URL
			if ( ! empty( $item->get_variation_id() ) ) {
				$redirect_url = get_post_meta( $item->get_variation_id(), 'redirect_url', true );
				if ( $redirect_url ) {
					$priority               = get_post_meta( $item->get_variation_id(), 'redirect_url_priority', true );
					$redirects[ $priority ] = $redirect_url;
				}
			}

			// If no redirect from a variation, get it from products
			if ( empty( $redirect_url ) ) {
				$redirect_url = get_post_meta( $item->get_product_id(), 'redirect_url', true );
				if ( $redirect_url ) {
					$priority               = get_post_meta( $item->get_product_id(), 'redirect_url_priority', true );
					$redirects[ $priority ] = $redirect_url;
				}
			}
		}

		// If we have redirects, let's sort by priority and pull the highest
		if ( ! empty( $redirects ) ) {
			ksort( $redirects );
			$redirect_url = end( $redirects );
		}

		// If still empty, get general redirect URL
		if ( empty( $redirect_url ) ) {
			$redirect_url = get_option( 'wps_wc_redirect_default' );
		}

		// Search replace some common things
		$search       = array(
			'{order_id}',
		);
		$replace      = array(
			$order_id,
		);
		$redirect_url = str_replace( $search, $replace, $redirect_url );

		// Search/replace meta variables in the URL
		$start   = '{meta:';
		$end     = '}';
		$matches = array();
		$p1      = explode( $start, $redirect_url );
		for ( $i = 1; $i < count( $p1 ); $i++ ) {
			$p2        = explode( $end, $p1[ $i ] );
			$matches[] = $p2[0];
		}
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $meta_key ) {
				$meta_value   = urlencode( get_post_meta( $order_id, $meta_key, true ) );
				$redirect_url = str_replace( '{meta:' . $meta_key . '}', $meta_value, $redirect_url );
			}
		}

		// Let final value be filtered
		$redirect_url = apply_filters( 'wps_wc_order_redirect_url', $redirect_url );

		if ( ! empty( $redirect_url ) ) {
			$append_order_id = get_option( 'wps_wc_redirect_append_order_id' );
			if ( $append_order_id == 'yes' ) {
				$redirect_url = add_query_arg( 'order_id', $order_id, $redirect_url );
			}
			wp_redirect( $redirect_url );
			exit;
		}

	}

}

$wps_wc_redirects = new WPSunshine_WC_Order_Redirects();
