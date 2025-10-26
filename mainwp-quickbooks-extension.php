<?php
/*
  Plugin Name: MainWP QuickBooks Extension
  Plugin URI: https://mainwp.com
  Description: Links QuickBooks Online with MainWP Dashboard for customer and recurring transaction management.
  Version: 1.0
  Author: [Your Name/Company]
  Author URI: [Your Website]
  Documentation URI: [Your Docs Link]
 */


namespace MainWP\Extensions\QuickBooks; // Renamed namespace

if ( ! defined( 'MAINWP_QUICKBOOKS_PLUGIN_FILE' ) ) {
	define( 'MAINWP_QUICKBOOKS_PLUGIN_FILE', __FILE__ );
}
// ... other constants updated

class MainWP_QuickBooks_Extension_Activator { // Renamed class

	protected $plugin_handle    = 'mainwp-quickbooks-extension'; // Renamed plugin handle
	protected $product_id       = 'MainWP QuickBooks Extension';  // Renamed product ID
	protected $software_version = '1.0';

	public function __construct() {
		$this->childFile = __FILE__;

		spl_autoload_register( array( $this, 'autoload' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_filter( 'mainwp_getextensions', array( &$this, 'get_this_extension' ) );
        // Renamed log priority constant
		add_filter( 'mainwp_log_specific_actions', array( $this, 'hook_log_specific' ), 10, 2 );

		// ... activation logic remains the same ...
	}

	public function autoload( $class_name ) {

		if ( 0 === strpos( $class_name, 'MainWP\Extensions\QuickBooks' ) ) { // Updated namespace check
			// trim the namespace prefix: MainWP\Extensions\QuickBooks\.
			$class_name = str_replace( 'MainWP\Extensions\QuickBooks\\', '', $class_name );
		} else {
			return;
		}

		if ( 0 !== strpos( $class_name, 'MainWP_QuickBooks' ) ) { // Updated class prefix check
			return;
		}
		// ... class loading logic remains the same ...
	}

    // ... rest of the class methods with updated class names ...

    public function hook_get_metaboxes( $metaboxes ) {
        // ... (Update to MainWP_QuickBooks_Widget::get_instance()) ...
    }

	public function hook_managesites_subpage( $subPage ) {
		$subPage[] = array(
			'title'            => __( 'QuickBooks Individual', 'mainwp-quickbooks-extension' ),
			'slug'             => 'QuickBooksIndividual',
			'sitetab'          => true,
			'menu_hidden'      => true,
			'callback'         => array( MainWP_QuickBooks_Individual::get_instance(), 'render_individual_page' ),
		);
		return $subPage;
	}
}

global $mainWPQuickBooksExtensionActivator;
$mainWPQuickBooksExtensionActivator = new MainWP_QuickBooks_Extension_Activator();
