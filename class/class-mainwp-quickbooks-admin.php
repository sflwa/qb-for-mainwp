<?php
/**
 * MainWP QuickBooks Admin
 *
 * This class handles the extension process.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\QuickBooks; // Updated namespace

 /**
  * Class MainWP_QuickBooks_Admin
  *
  * @package MainWP/Extensions
  */
class MainWP_QuickBooks_Admin { // Updated class name

	public static $instance = null;
	public $version         = '1.0'; // New extension version

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * MainWP_QuickBooks_Admin constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( &$this, 'localization' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'mainwp_delete_site', array( &$this, 'hook_delete_site' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

		// Instantiate all required components using the new class names
		MainWP_QuickBooks_DB::get_instance()->install();
		MainWP_QuickBooks_Ajax::get_instance();
		MainWP_QuickBooks_Overview::get_instance();
		MainWP_QuickBooks_Individual::get_instance();
	}

	/**
	 * Register the /languages folder. This will allow us to translate the extension.
	 *
	 * @return void
	 */
	public function localization() {
		load_plugin_textdomain( 'mainwp-quickbooks-extension', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); // Updated text domain
	}

	/**
	 * Displays the meta data winthin the plugin row on the WP > Plugins > Installed Plugins page.
	 *
	 * @param $plugin_meta
	 * @param $plugin_file
	 *
	 * @return mixed Array of plugin meta data.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( 'mainwp-quickbooks-extension/mainwp-quickbooks-extension.php' != $plugin_file ) {
			return $plugin_meta;
		}
		$slug     = basename( $plugin_file, '.php' );
		$api_data = get_option( $slug . '_APIManAdder' );
		if ( ! is_array( $api_data ) || ! isset( $api_data['activated_key'] ) || $api_data['activated_key'] != 'Activated' || ! isset( $api_data['api_key'] ) || empty( $api_data['api_key'] ) ) {
			return $plugin_meta;
		}
		$plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for Update</a>';
		return $plugin_meta;
	}

	/**
	 * This method is responsible for loading all JS & CSS for the extension.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( isset( $_GET['page'] ) && ( 'Extensions-Mainwp-QuickBooks-Extension' === $_GET['page'] || 'ManageSitesQuickBooksIndividual' === $_GET['page'] || ( 'managesites' === $_GET['page'] && isset( $_GET['dashboard'] ) ) ) ) {
			wp_enqueue_style( 'mainwp-quickbooks-extension', MAINWP_QUICKBOOKS_PLUGIN_URL . 'css/mainwp-quickbooks-extension.css', array(), $this->version );
			wp_enqueue_script( 'mainwp-quickbooks-extension', MAINWP_QUICKBOOKS_PLUGIN_URL . 'js/mainwp-quickbooks-extension.js', array(), $this->version, true );
		}
	}

	/**
	 * Handle MainWP site deletion hook.
	 *
	 * @param object $website The deleted child site object.
	 *
	 * @return void
	 */
	public function hook_delete_site( $website ) {
        // In a real implementation, this is where you would clean up any site-specific data
        // from the mainwp_quickbooks_mapping table.
	}

	/**
	 * Widgets screen options.
	 *
	 * @param array $input Input.
	 *
	 * @return array $input Input.
	 */
	public function widgets_screen_options( $input ) {
		$input['advanced-quickbooks-widget'] = __( 'QuickBooks Widget', 'mainwp-quickbooks-extension' ); // Updated text domain
		return $input;
	}
}
