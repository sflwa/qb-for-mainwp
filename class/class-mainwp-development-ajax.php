<?php
/**
 * MainWP Development
 *
 * This class handles the extension process.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\Development;

 /**
  * Class MainWP_Development
  *
  * @package MainWP/Extensions
  */
class MainWP_Development_Ajax {

	/**
	 * @var string The update version.
	 */
	public $update_version = '1.0';

	/**
	 * @var self|null The singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self|null
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * MainWP_Development_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
     * Admin init.
     *
	 * @return void
	 */
	public function admin_init() {
		do_action( 'mainwp_ajax_add_action', 'mainwp_development_reload_sites_data', array( &$this, 'ajax_reload_data' ) );
		do_action( 'mainwp_ajax_add_action', 'mainwp_development_enable_site', array( &$this, 'ajax_enable_site' ) );
		do_action( 'mainwp_ajax_add_action', 'mainwp_development_delete_site', array( &$this, 'ajax_delete_site' ) );
		do_action( 'mainwp_ajax_add_action', 'mainwp_development_convert_live_staging', array( &$this, 'ajax_convert_live_staging' ) );
		do_action( 'mainwp_ajax_add_action', 'mainwp_development_toggle_cdn', array( &$this, 'ajax_toggle_cdn' ) );
		do_action( 'mainwp_ajax_add_action', 'mainwp_development_add_new_site', array( &$this, 'ajax_add_new_site' ) );
		do_action( 'mainwp_ajax_add_action', 'mainwp_development_restore_site', array( &$this, 'ajax_restore_site' ) );
	}

	/**
     * Check for error response.
     *
	 * @param $result
	 *
	 * @return void
	 */
	public function check_error_response( $result ) {
		if ( is_array( $result ) && isset( $result['error'] ) && ! empty( $result['error'] ) && is_string( $result['error'] ) ) {
			wp_send_json( array( 'error' => esc_html( $result['error'] ) ) );
		}
	}

    /**
     * Ajax reload data.
     *
     * @return void
     */
	public function ajax_reload_data() {

		do_action( 'mainwp_secure_request', 'mainwp_development_reload_sites_data' );

		$development_id = intval( $_POST['development_id'] );

		$error = '';

		if ( empty( $development_id ) ) {
			$error = esc_html__( 'Empty development site id', 'mainwp-development-extension' );
		}

		if ( ! empty( $error ) ) {
			wp_send_json( array( 'error' => $error ) );
		}

		$success = false;
		$result  = MainWP_Development_App_Api::instance()->get_development_site( $development_id );

		$this->check_error_response( $result );

		if ( is_array( $result ) && isset( $result['data'] ) ) {
			$success = MainWP_Development_API_Handle::instance()->update_development_site( $result['data'] ); // more details data.
		}
		wp_send_json( array( 'success' => $success ? 'SUCCESS' : 'FAILED' ) );
	}

	/**
     * Ajax enable site.
     *
	 * @return void
	 */
	public function ajax_enable_site() {

		do_action( 'mainwp_secure_request', 'mainwp_development_enable_site' );

		$development_id = intval( $_POST['development_id'] );
		$enable       = intval( $_POST['enable'] );

		$error = '';

		if ( empty( $development_id ) ) {
			$error = esc_html__( 'Empty development site id', 'mainwp-development-extension' );
		}

		if ( ! empty( $error ) ) {
			wp_send_json( array( 'error' => $error ) );
		}

		$success = false;
		$result  = MainWP_Development_App_Api::instance()->enable_site( $development_id, $enable );

		$this->check_error_response( $result );

		if ( is_array( $result ) && isset( $result['data'] ) && is_array( $result['data'] ) && isset( $result['data']['state'] ) ) {
			$update = array(
				'development_id' => $development_id,
				'state'        => $result['data']['state'],
			);
			MainWP_Development_DB::get_instance()->update_development( $update );
			$success = true;
		}
		wp_send_json( array( 'success' => $success ? 'SUCCESS' : 'FAILED' ) );
	}

	/**
     * Ajax delete site.
     *
	 * @return void
	 */
	public function ajax_delete_site() {

		do_action( 'mainwp_secure_request', 'mainwp_development_delete_site' );

		$development_id = intval( $_POST['development_id'] );

		$error = '';

		if ( empty( $development_id ) ) {
			$error = esc_html__( 'Empty development site id', 'mainwp-development-extension' );
		}

		if ( ! empty( $error ) ) {
			wp_send_json( array( 'error' => $error ) );
		}

		$success = false;
		$result  = MainWP_Development_App_Api::instance()->delete_development_site( $development_id );

		$this->check_error_response( $result );

		if ( is_array( $result ) && isset( $result['message'] ) && empty( $result['data'] ) && empty( $result['error'] ) ) {
			MainWP_Development_DB::get_instance()->delete_development( 'development_id', $development_id );
			$success = true;
		}
		wp_send_json( array( 'success' => $success ? 'SUCCESS' : 'FAILED' ) );
	}

	/**
     * Ajax convert live site to staging.
     *
	 * @return void
	 */
	public function ajax_convert_live_staging() {

		do_action( 'mainwp_secure_request', 'mainwp_development_convert_live_staging' );

		$development_id = intval( $_POST['development_id'] );

		$error = '';

		if ( empty( $development_id ) ) {
			$error = esc_html__( 'Empty development site id', 'mainwp-development-extension' );
		}

		if ( ! empty( $error ) ) {
			wp_send_json( array( 'error' => $error ) );
		}

		$success = false;
		$result  = MainWP_Development_App_Api::instance()->convert_live_staging( $development_id );

		$this->check_error_response( $result );

		if ( is_array( $result ) && isset( $result['data'] ) && is_array( $result['data'] ) && isset( $result['data']['staging'] ) ) {
			$update = array(
				'development_id' => $development_id,
				'staging'      => $result['data']['staging'],
			);
			MainWP_Development_DB::get_instance()->update_development( $update );
			$success = true;
		}
		wp_send_json( array( 'success' => $success ? 'SUCCESS' : 'FAILED' ) );
	}

	/**
     * Ajax toggle cdn on of off.
     *
	 * @return void
	 */
	public function ajax_toggle_cdn() {

		do_action( 'mainwp_secure_request', 'mainwp_development_toggle_cdn' );

		$development_id = intval( $_POST['development_id'] );

		$error = '';

		if ( empty( $development_id ) ) {
			$error = esc_html__( 'Empty development site id', 'mainwp-development-extension' );
		}

		if ( ! empty( $error ) ) {
			wp_send_json( array( 'error' => $error ) );
		}

		$success = false;
		$result  = MainWP_Development_App_Api::instance()->toggle_cdn( $development_id );

		$this->check_error_response( $result );

		if ( is_array( $result ) && isset( $result['data'] ) && is_array( $result['data'] ) && isset( $result['data']['cdnEnabled'] ) ) {
			$update = array(
				'development_id' => $development_id,
				'cdn_enabled'  => $result['data']['cdnEnabled'] ? 1 : 0,
			);
			MainWP_Development_DB::get_instance()->update_development( $update );
			$success = true;
		}
		wp_send_json( array( 'success' => $success ? 'SUCCESS' : 'FAILED' ) );
	}

	public function ajax_add_new_site() {

		do_action( 'mainwp_secure_request', 'mainwp_development_add_new_site' );

		$args = array(
			'name' => sanitize_text_field(
				wp_unslash( $_POST['new_site']['site_name'] )
			),
		);

		if ( isset( $_POST['new_site']['php_version'] ) && ! empty( $_POST['new_site']['php_version'] ) ) {
			$args['php_version'] = sanitize_text_field( wp_unslash( $_POST['new_site']['php_version'] ) );
		}

		if ( isset( $_POST['new_site']['staging'] ) ) {
			$args['staging'] = true;
		}

		if ( isset( $_POST['new_site']['site_type'] ) ) {
			$args['install'] = sanitize_text_field( wp_unslash( $_POST['new_site']['site_type'] ) );
		}

		if ( isset( $_POST['new_site']['datacenter_code'] ) && 'NA' != $_POST['new_site']['datacenter_code'] ) {
			$args['datacenter_code'] = sanitize_text_field( wp_unslash( $_POST['new_site']['datacenter_code'] ) );
		}

		$success = false;
		$result  = MainWP_Development_App_Api::instance()->create_development_site( $args );
		$this->check_error_response( $result );

		if ( is_array( $result ) && isset( $result['data'] ) && is_array( $result['data'] ) && isset( $result['data']['id'] ) ) {
			MainWP_Development_API_Handle::instance()->update_development_site( $result['data'] );
			$success = true; // created site.
		}
		wp_send_json( array( 'success' => $success ? 'SUCCESS' : 'FAILED' ) );
	}


	public function ajax_restore_site() {

		do_action( 'mainwp_secure_request', 'mainwp_development_restore_site' );

		$development_id       = intval( $_POST['source_development_id'] );
		$file_backup_id     = isset( $_POST['restore_filesystem_id'] ) ? intval( $_POST['restore_filesystem_id'] ) : false;
		$database_backup_id = isset( $_POST['restore_database_id'] ) ? intval( $_POST['restore_database_id'] ) : false;
		$dest_development_id  = isset( $_POST['destination_development_id'] ) && ! empty( $_POST['destination_development_id'] ) ? intval( $_POST['destination_development_id'] ) : false;

		if ( $dest_development_id == $development_id ) {
			$dest_development_id = false;
		}

		$success = false;

		$result = MainWP_Development_App_Api::instance()->restore_site_backup( $development_id, $file_backup_id, $database_backup_id, $dest_development_id );

		$this->check_error_response( $result );

		if ( is_array( $result ) && ! empty( $result['message'] ) && empty( $result['data'] ) && empty( $result['errors'] ) ) {
			$success = true; // restore site.
		}
		wp_send_json( array( 'success' => $success ? 'SUCCESS' : 'FAILED' ) );
	}



	/**
	 * Method load_child_sites_to_prepare().
	 *
	 * Load child sites to prepare.
	 *
	 * @return null
	 */
	public static function load_child_sites_to_prepare() {

		$dbwebsites_list = false;
		$sql_sites       = MainWP_Development_DB::get_instance()->get_sql_websites_ext_data();
		if ( ! empty( $sql_sites ) ) {
			$dbwebsites_list = MainWP_Development_DB::get_instance()->query( $sql_sites );
		}
		if ( $dbwebsites_list ) {
			?>
			<div class="ui modal" id="mainwp-development-sync-modal">
				<div class="header"><?php echo esc_html__( 'Reload Development Data', 'mainwp-development-extension' ); ?></div>
					<div class="scrolling content">
						<div class="ui message" id="mainwp-development-modal-progress-feedback" style="display:none"></div>
					<div class="ui relaxed divided list">
					<?php
					while ( $website = MainWP_Development_DB::fetch_object( $dbwebsites_list ) ) {
						?>
							<div class="item mainwpProccessSitesItem" website-id="<?php echo intval( $website->id ); ?>" development-id="<?php echo intval( $website->development_id ); ?>" status="queue" >
								<a href="admin.php?page=managesites&dashboard=<?php echo $website->id; ?>" data-tooltip="<?php esc_attr_e( 'Go to the site Overview page.', 'mainwp-development-extension' ); ?>" data-inverted="" data-position="right center"><?php echo $website->name; ?></a>
								<span class="right floated status"><span data-tooltip="<?php esc_attr_e( 'Pending.', 'mainwp-development-extension' ); ?>" data-inverted="" data-position="left center"><i class="clock outline icon"></i></span></span>
							</div>
						<?php }; ?>
					</div>
				</div>
				<div class="actions">
					<div class="ui cancel reload button"><?php esc_html_e( 'Close', 'mainwp-development-extension' ); ?></div>
				</div>
			</div>
			<script type="text/javascript">
			  jQuery( document ).ready( function($) {
				jQuery( '#mainwp-development-sync-modal' ).modal( {
					onHide: function () {
						location.href = 'admin.php?page=Extensions-Mainwp-Development-Extension&tab=dashboard';
					}
				} ).modal( 'show' );
				mainwp_development_reload_sites_data_start();
			  } );
			</script>
				<?php
		} else {
			?>
			  <div class="ui yellow message"><?php esc_html_e( 'Sites not found.', 'mainwp-development-extension' ); ?></div>
			<?php
		}
	}

	/**
	 * Method render_state_color()
	 *
	 * Render state color.
	 *
	 * @return string color.
	 */
	public static function render_state_color( $state, $for_widget = false ) {
		$text = ucfirst( $state );
		if ( $for_widget ) {
			if ( 'live' == $state ) {
				$icon_code = '<i class="check large circle icon green"></i>';
			} elseif ( 'disabled' == $state ) {
				$icon_code = '<i class="times large circle icon grey"></i>';
			} else {
				$icon_code = '<i class="warning large circle icon yellow"></i>';
			}
			return '<span data-tooltip="' . esc_html( $text ) . '" data-inverted="" data-position="left center">' . $icon_code . '</span>';
		} else {

			$v_color = '';
			if ( 'live' == $state ) {
				$v_color = '<div class="ui green fluid center aligned label">' . esc_html( $text ) . '</div>';
			} elseif ( 'disabled' == $state ) {
				$v_color = '<div class="ui grey fluid center aligned label">' . esc_html( $text ) . '</div>';
			} else {
				$v_color = '<div class="ui yellow fluid center aligned label">' . esc_html( $text ) . '</div>';
			}
			echo $v_color;
		}

	}

	/**
	 * Method: show_messages()
	 *
	 * Renders the success or failure message after form submissions.
	 *
	 * @param  string $msg Message text.
	 *
	 * @return void
	 */
	public static function show_messages( $msg ) {
		if ( $msg ) {
			if ( 1 == $msg ) {
				?>
				<div class="ui green message"><i class="close icon"></i><?php esc_html_e( 'Settings saved successfully.', 'mainwp-development-extension' ); ?></div>
					<?php
			} elseif ( 2 == $msg ) {
				?>
				<div class="ui green message"><i class="close icon"></i><?php esc_html_e( 'Access generated successfully.', 'mainwp-development-extension' ); ?></div>
					<?php
			} elseif ( 3 == $msg ) {
				?>
				<div class="ui green message"><i class="close icon"></i><?php esc_html_e( 'Development sites data successfully reload.', 'mainwp-development-extension' ); ?></div>
					<?php
			} elseif ( 4 == $msg ) {
				?>
				<div class="ui yellow message"><i class="close icon"></i><?php esc_html_e( 'Invalid data. Please try again.', 'mainwp-development-extension' ); ?></div>
				<?php
			} elseif ( 5 == $msg ) {
				?>
				<div class="ui yellow message"><i class="close icon"></i><?php esc_html_e( 'Generate access failed. Please try again.', 'mainwp-development-extension' ); ?></div>
				<?php
			} elseif ( is_string( $msg ) ) {
				$msg   = urldecode( $msg );
				$color = 'green';
				if ( 'ERROR:' == substr( $msg, 0, 6 ) ) {
					$msg   = str_replace( 'ERROR:', '', $msg );
					$color = 'yellow';
				}
				?>
				<div class="ui message <?php echo $color; ?>"><i class="close icon"></i><?php echo esc_html( $msg ); ?></div>
				<?php
			}
		}
	}
}
