<?php

class WC_Gateway_Paypal_IPN_Handler_Override extends WC_Gateway_Paypal_IPN_Handler {

	public function __construct( $sandbox = false, $receiver_email = '' ) {
		add_action( 'valid-paypal-standard-ipn-request', array( $this, 'valid_response' ) );

		$this->receiver_email = $receiver_email;
		$this->sandbox        = $sandbox;
    }

	protected function save_paypal_meta_data( $order, $posted ) {
        // Write your code here
	}
}