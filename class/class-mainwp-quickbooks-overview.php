<?php
/**
 * MainWP QuickBooks Overview
 *
 * Handles the MainWP extension page rendering.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\QuickBooks;

 /**
  * Class MainWP_QuickBooks_Overview
  */
class MainWP_QuickBooks_Overview {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// ... existing constructor ...
	}

	/**
	 * Render extension page tabs.
     *
     * Fulfills:
     * 1. Create a page under add-ons which links quickbooks online with mainwp.
     */
	public static function render_tabs() {

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'connection';

		?>

		<div class="ui labeled icon inverted menu mainwp-sub-submenu" id="mainwp-quickbooks-menu">
			<a href="admin.php?page=Extensions-Mainwp-QuickBooks-Extension&tab=connection" class="item <?php echo ( 'connection' == $current_tab || 'oauth_callback' == $current_tab ) ? 'active' : ''; ?>"><i class="cloud download icon"></i> <?php esc_html_e( 'QBO Connection', 'mainwp-quickbooks-extension' ); ?></a>
			<a href="admin.php?page=Extensions-Mainwp-QuickBooks-Extension&tab=mapping" class="item <?php echo ( 'mapping' == $current_tab ) ? 'active' : ''; ?>"><i class="sitemap icon"></i> <?php esc_html_e( 'Site Mapping', 'mainwp-quickbooks-extension' ); ?></a>
		</div>
		<?php

		if ( 'mapping' == $current_tab ) {
			static::render_mapping_page();
		} elseif ( 'oauth_callback' == $current_tab ) { // <-- NEW: Handle the callback
			static::handle_oauth_callback();
		} else {
			static::render_connection_page();
		}
	}
   /**
     * Renders the QuickBooks Online OAuth Connection UI.
     */
    private static function render_connection_page() {

        $utility = MainWP_QuickBooks_Utility::get_instance();
        
        // Fetch current saved credentials and connection status
        $client_id = $utility->get_setting( 'client_id' );
        $client_secret = $utility->get_setting( 'client_secret' );
        $redirect_uri = $utility->get_setting( 'redirect_uri', 'https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl' ); // Use Playground URL as default suggestion
        
        $connection_status = 'Not Connected'; // Placeholder logic

        // This is the full QBO Authorization URL we need to build and use
        $auth_url = MainWP_QuickBooks_Utility::get_qbo_auth_url();

        ?>
        <div class="ui segment">
            <h2 class="ui header">QuickBooks Online Connection</h2>
            <p>Status: **<?php echo esc_html( $connection_status ); ?>**</p>
            
            <form id="mainwp-quickbooks-credentials-form" class="ui form">
                <input type="hidden" name="action" value="mainwp_quickbooks_save_credentials">
                <?php wp_nonce_field( 'mainwp-quickbooks-save-credentials', 'security' ); ?>

                <h4 class="ui dividing header">QuickBooks API Credentials</h4>

                <div class="field">
                    <label>Client ID</label>
                    <input type="text" name="client_id" value="<?php echo esc_attr( $client_id ); ?>" placeholder="Your QBO Client ID">
                </div>
                
                <div class="field">
                    <label>Client Secret</label>
                    <input type="password" name="client_secret" value="<?php echo esc_attr( $client_secret ); ?>" placeholder="Your QBO Client Secret">
                </div>
                
                <div class="field">
                    <label>Redirect URI</label>
                    <input type="text" name="redirect_uri" value="<?php echo esc_attr( $redirect_uri ); ?>" placeholder="Your QBO Redirect URI (e.g., https://yourdomain.com/wp-admin/admin.php?page=Extensions-Mainwp-QuickBooks-Extension&tab=oauth_callback)">
                    <p class="description">**You must register this URI in your Intuit Developer App settings.**</p>
                </div>

                <button class="ui button" type="submit">Save Credentials</button>
            </form>

            <h4 class="ui dividing header" style="margin-top: 2em;">Connection Authorization</h4>
            <div class="ui form">
                <?php if ( 'Not Connected' === $connection_status && ! empty( $auth_url ) ) : ?>
                    <a href="<?php echo esc_url( $auth_url ); ?>" class="ui primary button"><i class="linkify icon"></i> Connect to QuickBooks</a>
                    <p class="ui message">Click to authorize MainWP to connect to your QuickBooks Online account.</p>
                <?php elseif ( empty( $auth_url ) ) : ?>
                    <p class="ui error message">Please enter and save your Client ID and Redirect URI above to enable the connection link.</p>
                <?php else : ?>
                    <button class="ui red button"><i class="unlink icon"></i> Disconnect</button>
                    <p class="ui message success">Successfully connected to QuickBooks Online.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }


    /**
     * Renders the Site Mapping Table.
     *
     * Fulfills:
     * 1. Ability to map a child site in mainwp to a customer in Quickbooks.
     * 2. Ability to link / identify which sites have a recurring transaction in quickbooks and which do not.
     * 3. Ability to exclude sites from checking for the match.
     */
    private static function render_mapping_page() {
        // Fetch all sites and their current QuickBooks mapping status
        $websites = MainWP_QuickBooks_Utility::get_websites(); // Use the Utility to get sites

        ?>
        <h2 class="ui header">QuickBooks Site Mapping and Status</h2>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>MainWP Site Name</th>
                    <th>QBO Customer Mapping</th>
                    <th>Recurring Transaction Status</th>
                    <th>Exclude from Check</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $websites ) : ?>
                    <?php foreach ( $websites as $website ) :
                        // Placeholder values - in reality, you would fetch this from your DB
                        $qbo_customer_id = '12345';
                        $qbo_customer_name = 'Example Customer Co.';
                        $is_excluded = false;
                        $has_recurring_txn = true;

                        // Fetch the actual mapping from the database here.
                        // $mapping = MainWP_QuickBooks_DB::get_instance()->get_site_mapping($website->id);
                        // $qbo_customer_id = $mapping['qbo_customer_id'];
                    ?>
                        <tr>
                            <td><?php echo esc_html( $website->name ); ?></td>
                            <td>
                                <select class="ui dropdown mainwp-qbo-customer-select" data-site-id="<?php echo esc_attr( $website->id ); ?>">
                                    <option value="0">-- Select QBO Customer --</option>
                                    <option value="<?php echo esc_attr( $qbo_customer_id ); ?>" selected>
                                        <?php echo esc_html( $qbo_customer_name ); ?>
                                    </option>
                                </select>
                            </td>
                            <td>
                                <?php if ( $has_recurring_txn ) : ?>
                                    <i class="green check circle icon"></i> **Has Recurring**
                                <?php else : ?>
                                    <i class="red times circle icon"></i> **No Recurring**
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="ui checkbox">
                                    <input type="checkbox" class="mainwp-qbo-exclude" data-site-id="<?php echo esc_attr( $website->id ); ?>" <?php checked( $is_excluded ); ?>>
                                    <label>Exclude</label>
                                </div>
                            </td>
                            <td>
                                <button class="ui mini green button mainwp-qbo-save-mapping"><i class="save icon"></i> Save</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="5">No MainWP Child Sites found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }


// In class/class-mainwp-quickbooks-overview.php, ADD this new function
    /**
     * Handles the redirect back from QuickBooks Online after authorization.
     */
    private static function handle_oauth_callback() {
        if ( isset( $_GET['code'] ) && isset( $_GET['realmId'] ) && isset( $_GET['state'] ) ) {
            // Success! We received the authorization code.
            
            // In a real scenario, you would call a function here to exchange the code for tokens:
            // MainWP_QuickBooks_Utility::exchange_code_for_tokens( $_GET['code'], $_GET['realmId'], $_GET['state'] );
            
            echo '<div class="ui segment green"><h2 class="ui header">QuickBooks Authorization Successful!</h2><p>Your MainWP Dashboard received the authorization code and is now attempting to exchange it for Access Tokens...</p></div>';
            
            // To prevent the user from seeing the raw code in the URL, redirect them back to the connection tab (optional)
            // echo '<script>setTimeout(function() { window.location.href = "admin.php?page=Extensions-Mainwp-QuickBooks-Extension&tab=connection"; }, 3000);</script>';

        } elseif ( isset( $_GET['error'] ) ) {
            // Failure or user denial
            $error_description = isset( $_GET['error_description'] ) ? sanitize_text_field( wp_unslash( $_GET['error_description'] ) ) : 'Authorization failed or was denied by the user.';
            echo '<div class="ui segment red"><h2 class="ui header">QuickBooks Authorization Failed</h2><p>Error: ' . esc_html( $error_description ) . '</p></div>';
        } else {
            // Direct access without parameters
            echo '<div class="ui segment yellow"><h2 class="ui header">Authorization Callback Page</h2><p>This page is the designated callback handler for QuickBooks Online. Please use the "Connect to QuickBooks" button on the connection tab to initiate the OAuth flow.</p></div>';
        }
    }


	
}
