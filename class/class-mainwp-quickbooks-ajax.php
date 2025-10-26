<?php
/**
 * MainWP QuickBooks AJAX
 *
 * Handles AJAX calls for QBO integration.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\QuickBooks;

 /**
  * Class MainWP_QuickBooks_Ajax
  */
class MainWP_QuickBooks_Ajax {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

public function admin_init() {
    do_action( 'mainwp_ajax_add_action', 'mainwp_quickbooks_save_mapping', array( &$this, 'ajax_save_mapping' ) );
    do_action( 'mainwp_ajax_add_action', 'mainwp_quickbooks_save_credentials', array( &$this, 'ajax_save_credentials' ) ); // <-- ADD THIS LINE
    // Add other AJAX actions for QBO connection, token refresh, etc.
}

    /**
     * Ajax Save Site Mapping.
     */
	public function ajax_save_mapping() {

		// Secure the AJAX request
		do_action( 'mainwp_secure_request', 'mainwp_quickbooks_save_mapping' );

        // Sanitize and validate input
		$site_id = isset( $_POST['site_id'] ) ? intval( $_POST['site_id'] ) : 0;
        $qbo_customer_id = isset( $_POST['qbo_customer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['qbo_customer_id'] ) ) : '';
        $is_excluded = isset( $_POST['is_excluded'] ) ? intval( $_POST['is_excluded'] ) : 0;

        if ( empty( $site_id ) ) {
            wp_send_json_error( array( 'message' => 'Invalid site ID.' ) );
            return;
        }

        // 1. Save the mapping and exclusion status to the database (using MainWP_QuickBooks_DB)
        $saved = MainWP_QuickBooks_DB::get_instance()->update_site_mapping( $site_id, $qbo_customer_id, $is_excluded );

        if ( $saved ) {
            wp_send_json_success( array( 'message' => 'Mapping saved successfully.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to save mapping.' ) );
        }
	}

// class/class-mainwp-quickbooks-ajax.php

/**
 * Ajax Save QBO Credentials.
 */
public function ajax_save_credentials() {

    // Secure the AJAX request
    do_action( 'mainwp_secure_request', 'mainwp_quickbooks_save_credentials' );

    // Validate nonce
    if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mainwp-quickbooks-save-credentials' ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        return;
    }

    $utility = MainWP_QuickBooks_Utility::get_instance();

    // Sanitize input
    $client_id     = isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : '';
    $client_secret = isset( $_POST['client_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ) : '';
    $redirect_uri  = isset( $_POST['redirect_uri'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_uri'] ) ) : '';

    if ( empty( $client_id ) || empty( $client_secret ) || empty( $redirect_uri ) ) {
        wp_send_json_error( array( 'message' => 'All credential fields are required.' ) );
        return;
    }

    // Save to settings
    $utility->update_setting( 'client_id', $client_id );
    $utility->update_setting( 'client_secret', $client_secret );
    $utility->update_setting( 'redirect_uri', $redirect_uri );

    wp_send_json_success( array( 'message' => 'QuickBooks Credentials saved successfully. Please refresh the page if the "Connect" button does not update.' ) );
}

	
}
