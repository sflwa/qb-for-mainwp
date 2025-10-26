<?php
/*
  Plugin Name: MainWP QuickBooks Extension
  Plugin URI: https://mainwp.com
  Description: Links QuickBooks Online with MainWP Dashboard for customer and recurring transaction management.
  Version: 1.1
  Author: [Your Name/Company]
  Author URI: [Your Website]
  Documentation URI: [Your Docs Link]
 */


namespace MainWP\Extensions\QuickBooks; // Updated namespace

if ( ! defined( 'MAINWP_QUICKBOOKS_PLUGIN_FILE' ) ) {
	define( 'MAINWP_QUICKBOOKS_PLUGIN_FILE', __FILE__ ); // Updated constant
}

if ( ! defined( 'MAINWP_QUICKBOOKS_PLUGIN_DIR' ) ) {
	define( 'MAINWP_QUICKBOOKS_PLUGIN_DIR', plugin_dir_path( MAINWP_QUICKBOOKS_PLUGIN_FILE ) ); // Updated constant
}

if ( ! defined( 'MAINWP_QUICKBOOKS_PLUGIN_URL' ) ) {
	define( 'MAINWP_QUICKBOOKS_PLUGIN_URL', plugin_dir_url( MAINWP_QUICKBOOKS_PLUGIN_FILE ) ); // Updated constant
}

if ( ! defined( 'MAINWP_QUICKBOOKS_LOG_PRIORITY' ) ) {
	define( 'MAINWP_QUICKBOOKS_LOG_PRIORITY', 2024011 ); // Updated constant
}

class MainWP_QuickBooks_Extension_Activator { // Updated class name

	protected $mainwpMainActivated = false;
	protected $childEnabled        = false;
	protected $childKey            = false;
	protected $childFile;
	protected $plugin_handle    = 'mainwp-quickbooks-extension'; // Updated plugin handle
	protected $product_id       = 'MainWP QuickBooks Extension'; // Updated product ID
	protected $software_version = '1.0'; // Updated version for new extension

	public function __construct() {
		$this->childFile = __FILE__;

		// Register given function as __autoload() implementation
		spl_autoload_register( array( $this, 'autoload' ) );

		// Register activation and deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_filter( 'mainwp_getextensions', array( &$this, 'get_this_extension' ) );
		add_filter( 'mainwp_log_specific_actions', array( $this, 'hook_log_specific' ), 10, 2 );

		$this->mainwpMainActivated = apply_filters( 'mainwp_activated_check', false );
		if ( $this->mainwpMainActivated !== false ) {
			$this->activate_this_plugin();
		} else {
			add_action( 'mainwp_activated', array( &$this, 'activate_this_plugin' ) );
		}

		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
	}

	/**
	 * Autoload for QuickBooks Extension classes.
	 *
	 * @param string $class_name The name of the class to load.
	 */
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
		$class_name = str_replace( '_', '-', strtolower( $class_name ) );
		$class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . 'class' . DIRECTORY_SEPARATOR . 'class-' . $class_name . '.php';
		if ( file_exists( $class_file ) ) {
			require_once $class_file;
		}
	}

	/**
	 * Adds the extension to the MainWP extension list.
	 */
	public function get_this_extension( $pArray ) {

		$pArray[] = array(
			'plugin'     => __FILE__,
			'api'        => $this->plugin_handle,
			'mainwp'     => true,
			'callback'   => array( &$this, 'settings' ),
			'apiManager' => true,
		);

		return $pArray;
	}

	/**
	 * Main extension settings page.
	 */
	public function settings() {
		do_action( 'mainwp_pageheader_extensions', __FILE__ );
		MainWP_QuickBooks_Overview::get_instance()->render_tabs(); // Updated class name
		do_action( 'mainwp_pagefooter_extensions', __FILE__ );
	}

	/**
	 * Initializes the extension after MainWP is activated.
	 */
	public function activate_this_plugin() {

		$this->mainwpMainActivated = apply_filters( 'mainwp_activated_check', $this->mainwpMainActivated );
		$this->childEnabled        = apply_filters( 'mainwp_extension_enabled_check', __FILE__ );
		$this->childKey            = $this->childEnabled['key'];

		if ( function_exists( 'mainwp_current_user_can' ) && ! mainwp_current_user_can( 'extension', 'mainwp-quickbooks-extension' ) ) { // Updated handle
			return;
		}

		add_filter( 'mainwp_getsubpages_sites', array( &$this, 'hook_managesites_subpage' ), 10, 1 );
		add_filter( 'mainwp_getmetaboxes', array( &$this, 'hook_get_metaboxes' ) );
		add_filter( 'mainwp_widgets_screen_options', array( MainWP_QuickBooks_Admin::get_instance(), 'widgets_screen_options' ), 10, 1 ); // Updated class name

		MainWP_QuickBooks_Admin::get_instance(); // Updated class name
	}

	/**
	 * Hook hook_log_specific.
	 *
	 * @return mixed $inputs.
	 */
	public function hook_log_specific( $inputs ) {
		$inputs[ MAINWP_QUICKBOOKS_LOG_PRIORITY ] = __( 'QuickBooks logs', 'mainwp-quickbooks-extension' ); // Updated constant and text domain
		return $inputs;
	}
	public function get_child_key() {
		return $this->childKey;
	}

	public function get_child_file() {
		return $this->childFile;
	}

	/**
	 * Displays an admin notice if MainWP is not activated.
	 */
	public function admin_notices() {
		global $current_screen;
		if ( $current_screen->parent_base == 'plugins' && $this->mainwpMainActivated == false ) {
			echo '<div class="error"><p>' . sprintf( esc_html__( 'MainWP QuickBooks Extension requires %1$sMainWP Dashboard Plugin%2$s to be activated in order to work. Please install and activate %3$sMainWP Dashboard Plugin%4$s first.' ), '<a href="http://mainwp.com/" target="_blank">', '</a>', '<a href="http://mainwp.com/" target="_blank">', '</a>' ) . '</p></div>';
		}
	}

	/**
	 * Activates the extension in WordPress.
	 *
	 * @return void
	 */
	public function activate() { // Must be public
		$options = array(
			'product_id'       => $this->product_id,
			'software_version' => $this->software_version,
		);
		do_action( 'mainwp_activate_extention', $this->plugin_handle, $options );
        
        // Ensure the database installation runs on activation
        if ( class_exists( '\MainWP\Extensions\QuickBooks\MainWP_QuickBooks_DB' ) ) {
            MainWP_QuickBooks_DB::get_instance()->install();
        }
	}

	/**
	 * Deactivates the extension in WordPress.
	 *
	 * @return void
	 */
	public function deactivate() { // Must be public
		do_action( 'mainwp_deactivate_extention', $this->plugin_handle );
	}

	/**
	 * Adds metabox (widget) on the MainWP Dashboard overview page.
	 *
	 * @param array $metaboxes Array containing metaboxes data.
	 *
	 * @return array $metaboxes Updated array that contains metaboxes data.
	 */
	public function hook_get_metaboxes( $metaboxes ) {
		if ( ! $this->childEnabled ) {
			return $metaboxes;
		}

		if ( ! is_array( $metaboxes ) ) {
			$metaboxes = array();
		}

		$metaboxes[] = array(
			'id'            => 'quickbooks-widget',
			'plugin'        => $this->childFile,
			'key'           => $this->childKey,
			'metabox_title' => __( 'QuickBooks', 'mainwp-quickbooks-extension' ),
			'callback'      => array( MainWP_QuickBooks_Widget::get_instance(), 'render_metabox' ), // Updated class name
		);


		return $metaboxes;
	}


	/**
	 * Adds the individual site subpage.
	 *
	 * @param array $subPage Input sub pages.
	 *
	 * @return array $subPage Output sub pages.
	 */
	public function hook_managesites_subpage( $subPage ) {
		$subPage[] = array(
			'title'            => __( 'QuickBooks Individual', 'mainwp-quickbooks-extension' ),
			'slug'             => 'QuickBooksIndividual',
			'sitetab'          => true,
			'menu_hidden'      => true,
			'callback'         => array( MainWP_QuickBooks_Individual::get_instance(), 'render_individual_page' ), // Updated class name
		);
		return $subPage;
	}


}

global $mainWPQuickBooksExtensionActivator; // Updated global variable name
$mainWPQuickBooksExtensionActivator = new MainWP_QuickBooks_Extension_Activator();
