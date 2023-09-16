<?php

#namespace Disabler;
use function Disabler\plugin;

/**
 * Installation related functions and actions.
 *
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disabler_Install Class.
 */
class Disabler_Install {

	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array(
		'3.0.0' => array(
			'disabler_update_300_options',
			'disabler_update_300_db_version',
		),
		'3.0.3' => array(
			'disabler_update_303_options',
			'disabler_update_303_db_version',
		),
	);

	/** @var object Background update class */
	private static $background_updater;

	private $logger;

	/**
	 * Hook in tabs.
	 */
	public static function init() {

		add_action( 'plugins_loaded', array( __CLASS__, 'init_version' ), 0 );

		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
	}

	/**
	 *	Set initial version, so we can do update
	 * if its installed on old version
	 */
	public static function init_version() {

		$current_db_version = get_option( 'disabler_db_version', null );
		$init_db_ver = '2.3.1';

		if ( is_null( $current_db_version ) ) {
			# if installed verion is v3+, base db version on that
			if ( get_option( 'disabler_plugin_version' ) ) {
				$init_db_ver = '3.0.0';
			}

			update_option( 'disabler_db_version', $init_db_ver );
		}
	}

	/**
	 * Check Plugin version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {

		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'disabler_db_version' ) !== plugin()->db_version ) {
			self::install();
			do_action( 'disabler_updated' );
		}
	}

	/**
	 * Install Plugin updates.
	 */
	public static function install() {
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'disabler_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'disabler_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		disabler_maybe_define_constant( 'DISABLER_INSTALLING', true );

		# Ensure needed classes are loaded
		require_once( plugin()->update_dir . 'class-plugin-admin-notices.php' );

		self::remove_admin_notices();
		self::create_cron_jobs();
		self::create_files();
		self::update_disabler_version();
		self::maybe_update_db_version();

		delete_transient( 'disabler_installing' );

		// Flush rules after install
		flush_rewrite_rules();

		// Trigger action
		do_action( 'disabler_installed' );
	}

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 3.0.3
	 */
	private static function remove_admin_notices() {
		Disabler_Admin_Notices::remove_all_notices();
	}

	/**
	 * Is a DB update needed?
	 *
	 * @since 3.0.3
	 * @return boolean
	 */
	private static function needs_db_update() {
		$current_db_version = get_option( 'disabler_db_version', null );
		$updates            = self::get_db_update_callbacks();

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string|null $version New Disabler DB version or null.
	 */
	public static function update_db_version( $version = null ) {
		$version = is_null( $version ) ? plugin()->db_version : $version;

		$logger             = disabler_get_logger();
		$logger->info(
			sprintf( 'Updating %s', $version ),
			array( 'source' => 'update_db_version' )
		);
		//return;

		delete_option( 'disabler_db_version' );
		add_option( 'disabler_db_version', $version );
	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 3.2.0
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			if ( apply_filters( 'disabler_enable_auto_update_db', false ) ) {
				self::init_background_updater();
				self::update();
			} else {
				Disabler_Admin_Notices::add_notice( 'update' );
			}
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Update Plugin version to current.
	 */
	private static function update_disabler_version() {
		$plugin_version = plugin()->version;

		$logger             = disabler_get_logger();
		$logger->info(
			sprintf( 'Updating %s', $plugin_version ),
			array( 'source' => 'update_disabler_version' )
		);
		//return;

		delete_option( 'disabler_version' );
		add_option( 'disabler_version', $plugin_version );
	}

	/**
	 * Init background updates
	 */
	public static function init_background_updater() {
		require_once( plugin()->update_dir . 'class-plugin-background-updater.php' );

		self::$background_updater = new Disabler_Background_Updater();
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['disabler_do_update'] ) ) {
			self::update();
			Disabler_Admin_Notices::add_notice( 'update' );
		}

		if ( ! empty( $_GET['disabler_force_update'] ) ) {
			do_action( 'wp_' . get_current_blog_id() . '_disabler_updater_cron' );
			wp_safe_redirect( admin_url( 'index.php' ) );
			exit;
		}
	}

	/**
	 * Add more cron schedules
	 *
	 * @param  array $schedules List of WP scheduled cron jobs.
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display'  => __( 'Monthly', 'disabler' ),
		);

		return $schedules;
	}

	/**
	 * Create cron jobs (clear them first).
	 */
	private static function create_cron_jobs() {
		wp_clear_scheduled_hook( 'disabler_tracker_send_event' );

		wp_schedule_event( time(), apply_filters( 'disabler_tracker_event_recurrence', 'daily' ), 'disabler_tracker_send_event' );
	}

	/**
	 * Create files/directories.
	 */
	private static function create_files() {
		// Bypass if filesystem is read-only and/or non-standard upload system is used.
		if ( apply_filters( 'disabler_install_skip_create_files', false ) ) {
			return;
		}

		// Install files and folders for uploading files and prevent hotlinking.
		$upload_dir      = wp_upload_dir();

		$files = array(
			array(
				'base' 		=> DISABLER_LOG_DIR,
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all',
			),
			array(
				'base' 		=> DISABLER_LOG_DIR,
				'file' 		=> 'index.html',
				'content' 	=> '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' );
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  3.0.3
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {
		$current_db_version = get_option( 'disabler_db_version' );
		$logger             = disabler_get_logger();
		$update_queued      = false;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$logger->info(
						sprintf( 'Queuing %s - %s', $version, $update_callback ),
						array( 'source' => 'disabler_db_updates' )
					);
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}
		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}
}

Disabler_Install::init();
