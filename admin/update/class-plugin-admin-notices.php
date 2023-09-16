<?php

use function Disabler\plugin;

/**
 * Display notices in admin
 *
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Disabler_Admin_Notices' )) {
	/**
	 * Disabler_Admin_Notices Class.
	 */
	class Disabler_Admin_Notices {

		/**
		 * Stores notices.
		 * @var array
		 */
		private static $notices = array();

		/**
		 * Array of notices - name => callback.
		 * @var array
		 */
		private static $core_notices = array(
			'install'          => 'install_notice',
			'update'           => 'update_notice',
		);

		/**
		 * Constructor.
		 */
		public static function init() {
			self::$notices = get_option( 'disabler_admin_notices', array() );

			add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
			add_action( 'shutdown', array( __CLASS__, 'store_notices' ) );

			if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
				add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
			}
		}

		/**
		 * Store notices to DB
		 */
		public static function store_notices() {
			update_option( 'disabler_admin_notices', self::get_notices() );
		}

		/**
		 * Get notices
		 * @return array
		 */
		public static function get_notices() {
			return self::$notices;
		}

		/**
		 * Remove all notices.
		 */
		public static function remove_all_notices() {
			self::$notices = array();
		}

		/**
		 * Show a notice.
		 * @param string $name
		 */
		public static function add_notice( $name ) {
			self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );
		}

		/**
		 * Remove a notice from being displayed.
		 * @param  string $name
		 */
		public static function remove_notice( $name ) {
			self::$notices = array_diff( self::get_notices(), array( $name ) );
			delete_option( 'disabler_admin_notice_' . $name );
		}

		/**
		 * See if a notice is being shown.
		 * @param  string  $name
		 * @return boolean
		 */
		public static function has_notice( $name ) {
			return in_array( $name, self::get_notices() );
		}

		/**
		 * Hide a notice if the GET variable is set.
		 */
		public static function hide_notices() {
			if ( isset( $_GET['disabler-hide-notice'] ) && isset( $_GET['_disabler_notice_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_disabler_notice_nonce'], 'disabler_hide_notices_nonce' ) ) {
					wp_die( __( 'Action failed. Please refresh the page and retry.', 'disabler' ) );
				}

				if ( ! ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
					wp_die( __( 'Cheatin&#8217; huh?', 'disabler' ) );
				}

				$hide_notice = sanitize_text_field( $_GET['disabler-hide-notice'] );

				self::remove_notice( $hide_notice );

				update_user_meta( get_current_user_id(), 'dismissed_' . $hide_notice . '_notice', true );

				do_action( 'disabler_hide_' . $hide_notice . '_notice' );
			}
		}

		/**
		 * Add notices + styles if needed.
		 */
		public static function add_notices() {
			# Use the .min if SCRIPT_DEBUG is turned off.
			$suffix = disabler_get_min_suffix();

			$notices = self::get_notices();

			if ( ! empty( $notices ) ) {
				wp_enqueue_style( 'disabler-admin-notices', plugin()->assets . 'css/admin/admin-notices' . $suffix . '.css', null, plugin()->asset_version );

				foreach ( $notices as $notice ) {
					if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'disabler_show_admin_notice', true, $notice ) ) {
						add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
					} else {
						add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
					}
				}
			}
		}

		/**
		 * Add a custom notice.
		 * @param string $name
		 * @param string $notice_html
		 */
		public static function add_custom_notice( $name, $notice_html ) {
			self::add_notice( $name );
			update_option( 'disabler_admin_notice_' . $name, wp_kses_post( $notice_html ) );
		}

		/**
		 * Output any stored custom notices.
		 */
		public static function output_custom_notices() {
			$notices = self::get_notices();

			if ( ! empty( $notices ) ) {
				foreach ( $notices as $notice ) {
					if ( empty( self::$core_notices[ $notice ] ) ) {
						$notice_html = get_option( 'disabler_admin_notice_' . $notice );

						if ( $notice_html ) {
							include( plugin()->update_dir . 'views/html-notice-custom.php' );
						}
					}
				}
			}
		}

		/**
		 * If we have just installed, show a welcome message.
		 */
		public static function install_notice() {
			include( plugin()->update_dir . 'views/html-notice-install.php' );
		}

		/**
		 * If we need to update, include a message with the update button.
		 */
		public static function update_notice() {
			if ( version_compare( get_option( 'disabler_db_version' ), plugin()->db_version, '<' ) ) {
				$updater = new Disabler_Background_Updater();

				if ( $updater->is_updating() || ! empty( $_GET['disabler_do_update'] ) ) {
					include( plugin()->update_dir . 'views/html-notice-updating.php' );
				} else {
					include( plugin()->update_dir . 'views/html-notice-update.php' );
				}
			} else {
				include( plugin()->update_dir . 'views/html-notice-updated.php' );
			}
		}

	}
}

Disabler_Admin_Notices::init();
