<?php
/**
 * MainWP QuickBooks DB
 *
 * This class handles the DB process for QuickBooks Extension.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\QuickBooks;

 /**
  * Class MainWP_QuickBooks_DB
  */
class MainWP_QuickBooks_DB {

	private static $instance = null;
	private $wpdb;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
	}

	/**
	 * Install Extension.
	 */
	public function install() {
		$this->install_connection_table();
        $this->install_mapping_table();
	}

    /**
     * Creates the table to store QuickBooks Connection/Token data.
     */
    private function install_connection_table() {
        $table = $this->wpdb->prefix . 'mainwp_quickbooks_connection';

        // Check if the table already exists.
        if ( $this->wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
            $sql = 'CREATE TABLE ' . $table . ' (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                realm_id VARCHAR(255) NOT NULL,
                access_token TEXT NOT NULL,
                refresh_token TEXT NOT NULL,
                token_expiry DATETIME NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
    }

    /**
     * Creates the table to store MainWP Site to QuickBooks Customer mapping.
     *
     * Fulfills:
     * 1. Ability to map a child site to a customer in Quickbooks.
     * 2. Ability to exclude sites from checking for the match.
     */
    private function install_mapping_table() {
        $table = $this->wpdb->prefix . 'mainwp_quickbooks_mapping';
        $collate = $this->wpdb->get_charset_collate();

        // Check if the table already exists.
        if ( $this->wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
            $sql = 'CREATE TABLE ' . $table . ' (
                site_id INT(11) UNSIGNED NOT NULL,
                qbo_customer_id VARCHAR(255) NOT NULL,
                is_excluded TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                has_recurring_txn TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (site_id)
            ) ' . $collate . ';';

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
    }
}
