<?php

namespace Disabler;

/**
 * Disabler Tracker
 *
 * The Disabler tracker class adds functionality to track Disabler usage based on if the customer opted in.
 * No personal information is tracked, only general Disabler settings, general server info, theme and user counts and admin email for discount code.
 *
 * @class 		Tracker
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up and handles the plugin settings screen.
 *
 * @since  3.0.3
 * @access public
 */
final class Tracker {

	/**
	 * URL to the Disabler Tracker API endpoint.
	 * @var string
	 */
	private static $api_url = 'https://tracking.hybopressthemes.com/api/v1/track/';

	/**
	 * Returns the instance.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Sets up the needed actions for tracker.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Loads include and admin files for the plugin.
	 *
	 * @since  3.0.3
	 * @access private
	 * @return void
	 */
	private function includes() {

		require_once( plugin()->dir . 'admin/tracking/tracking-functions.php' );

		require_once( plugin()->dir . 'admin/tracking/class-remote-request.php' );
		require_once( plugin()->dir . 'admin/tracking/interface-collection.php' );
		require_once( plugin()->dir . 'admin/tracking/class-collector.php' );
		require_once( plugin()->dir . 'admin/tracking/class-tracking-default-data.php' );
		require_once( plugin()->dir . 'admin/tracking/class-tracking-server-data.php' );
		require_once( plugin()->dir . 'admin/tracking/class-tracking-theme-data.php' );
		require_once( plugin()->dir . 'admin/tracking/class-tracking-plugin-data.php' );
	}

	/**
	 * Sets up initial actions.
	 *
	 * @since  3.0.3
	 * @access private
	 * @return void
	 */
	private function setup_actions() {
		/**
		 * Hook into cron event.
		 */
		#add_action( 'disabler_tracker_send_event', array( $this, 'send_tracking_data' ) );
		add_action( 'disabler_tracker_send_event', array( __CLASS__, 'send_tracking_data' ) );
	}

	/**
	 * Decide whether to send tracking data or not.
	 *
	 * @param boolean $override
	 */
	public static function send_tracking_data( $override = false ) {

		if ( ! self::should_send_tracking( $override ) ) {
			return;
		}

		// Update time first before sending to ensure it is set
		update_option( 'disabler_tracker_last_send', time() );

		$params   = self::get_tracking_data();

		$request = new Disabler_Remote_Request( self::$api_url ); //self::$endpoint
		$request->set_body( $params );
		$request->send();
	}

	/**
	 * Returns true when last send interval exceeds 1 week (if override is false) or 1 hour if override is true
	 *
	 * @return bool True when tracking data should be send.
	 */
	protected static function should_send_tracking( $override ) {

		// Don't trigger this on AJAX Requests
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		if ( ! apply_filters( 'disabler_tracker_send_override', $override ) ) {
			// Send a maximum of once per week by default.
			$last_send = self::get_last_send_time();
			if ( $last_send && $last_send > apply_filters( 'disabler_tracker_last_send_interval', strtotime( '-1 week' ) ) ) {
				return false;
			}
		} else {
			// Make sure there is at least a 1 hour delay between override sends, we don't want duplicate calls due to double clicking links.
			$last_send = self::get_last_send_time();
			if ( $last_send && $last_send > strtotime( '-1 hours' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the last time tracking data was sent.
	 * @return int|bool
	 */
	private static function get_last_send_time() {
		return apply_filters( 'disabler_tracker_last_send_time', get_option( 'disabler_tracker_last_send', false ) );
	}

	/**
	 * When an admin user logs in, there user agent is tracked in user meta and collected here.
	 * @return array
	 */
	private static function get_admin_user_agents() {
		return array_filter( (array) get_option( 'disabler_tracker_ua', array() ) );
	}

	/**
	 * Get all the tracking data.
	 * @return array
	 */
	private static function get_tracking_data() {

		$collector = self::get_collector();
		$data = $collector->get_as_json();

		$data = apply_filters( 'disabler_tracker_data', $data );

		return $data;
	}

	/**
	 * Returns the collector for collecting the data.
	 *
	 * @return Disabler_Collector The instance of the collector.
	 */
	protected static function get_collector() {
		$collector = new Disabler_Collector();
		$collector->add_collection( new Disabler_Tracking_Default_Data() );
		$collector->add_collection( new Disabler_Tracking_Server_Data() );
		$collector->add_collection( new Disabler_Tracking_Theme_Data() );
		$collector->add_collection( new Disabler_Tracking_Plugin_Data() );

		return $collector;
	}

}

Tracker::get_instance();
