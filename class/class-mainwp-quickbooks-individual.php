<?php
/**
 * MainWP QuickBooks Individual
 *
 * This class handles the Individual site page process.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\QuickBooks; // Updated namespace

 /**
  * Class MainWP_QuickBooks_Individual
  *
  * @package MainWP/Extensions
  */
class MainWP_QuickBooks_Individual // Updated class name
{
	/**
	 * @var self|null The singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self|null
	 */
	public static function get_instance()
	{
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * MainWP_QuickBooks_Individual constructor.
     *
     * @return void
	 */
	public function __construct()
	{
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Admin init.
	 *
	 * @return void
	 */
	public function admin_init()
	{
		// Any specific admin initialization for the individual site page goes here.
	}

	/**
	 * Render individual site page.
     *
     * @return void
	 */
	public function render_individual_page()
	{
        // Get the site ID from the URL parameter for context
        $site_id = isset( $_GET['dashboard'] ) ? intval( $_GET['dashboard'] ) : 0;
        
		do_action( 'mainwp_pageheader_sites', 'QuickBooksIndividual' ); // Updated slug
		?>
        <div class="ui message">
            <h2 class="ui header">QuickBooks Customer Mapping</h2>
            <p>This page allows you to view and manage the QuickBooks mapping for this specific child site (ID: <?php echo esc_html($site_id); ?>).</p>
            
            <div class="ui placeholder segment">
                <div class="ui icon header">
                    <i class="sync icon"></i>
                    Site to Customer Mapping Tools (Coming Soon)
                </div>
            </div>
        </div>
		<?php
		do_action( 'mainwp_pagefooter_sites', 'QuickBooksIndividual' ); // Updated slug
	}
}
