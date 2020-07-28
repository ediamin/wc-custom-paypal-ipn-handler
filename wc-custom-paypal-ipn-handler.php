<?php
/**
 * Plugin Name: WooCommerce Custom Paypal IPN Handler
 * Description: Plugin to override WC_Gateway_Paypal_IPN_Handler and use your own handler
 * Author: Edi Amin
 * Author URI: https://github.com/ediamin
 */

 /**
 * Function from Dokan Lite
 */
function custom_remove_hook_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
    global $wp_filter;

    // Take only filters on right hook name and priority
    if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
        return false;
    }

    // Loop on filters registered
    foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
        // Test if filter is an array ! (always for class/method)
        if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
            // Test if object is a class, class and method is equal to param !
            if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
                // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                    unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                } else {
                    unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                }
            }
        }
    }

    return false;
}

// First, Prevent execution of WC_Gateway_Paypal_IPN_Handler::valid_response.
// Then hook our own overridden class that extends WC_Gateway_Paypal_IPN_Handler
add_action( 'valid-paypal-standard-ipn-request', function () {
    custom_remove_hook_for_anonymous_class( 'valid-paypal-standard-ipn-request', 'WC_Gateway_Paypal_IPN_Handler', 'valid_response', 10 );

    require_once dirname( __FILE__ ) . '/class-wc-gateway-paypal-ipn-handler-override.php';

    $gatways        = wc()->payment_gateways()->payment_gateways();
    $paypal         = $gatways['paypal'];
    $testmode       = 'yes' === $paypal->get_option( 'testmode', 'no' );
    $receiver_email = $paypal->get_option( 'receiver_email', $paypal->email );

    new WC_Gateway_Paypal_IPN_Handler_Override( $testmode, $receiver_email );
}, 9 );
