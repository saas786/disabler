<?php

namespace Disabler;

/**
 * Represents the default data.
 */
class Disabler_Tracking_Default_Data implements Disabler_Collection {

	/**
	 * Returns the collection data.
	 *
	 * @return array The collection data.
	 */
	public function get() {
		return array(
			'site_title'        => get_option( 'blogname' ),
			'@timestamp'        => (int) date( 'Uv' ),
			'home_url'          => home_url(),
			'admin_url'         => admin_url(),
			'site_language'     => get_bloginfo( 'language' ),
			'admin_email'       => apply_filters( 'disabler_tracker_admin_email', get_option( 'admin_email' ) ),
			'settings'          => self::get_all_disabler_options_values(),
			'users'             => self::get_user_counts(),
			'admin_user_agents' => self::get_admin_user_agents(),
			'wp'                => self::get_wordpress_info(),
		);
	}

	/**
	 * Get WordPress related data.
	 * @return array
	 */
	private static function get_wordpress_info() {
		$wp_data = array();

		$memory = disabler_let_to_num( WP_MEMORY_LIMIT );

		if ( function_exists( 'memory_get_usage' ) ) {
			$system_memory = disabler_let_to_num( @ini_get( 'memory_limit' ) );
			$memory        = max( $memory, $system_memory );
		}

		$wp_data['memory_limit'] = size_format( $memory );
		$wp_data['debug_mode']   = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No';
		$wp_data['locale']       = get_locale();
		$wp_data['version']      = self::get_wordpress_version();
		$wp_data['multisite']    = is_multisite() ? 'Yes' : 'No';

		return $wp_data;
	}

	/**
	 * Returns the WordPress version.
	 *
	 * @return string The version.
	 */
	protected static function get_wordpress_version() {
		global $wp_version;

		#get_bloginfo( 'version' );

		return $wp_version;
	}

	/**
	 * Get user totals based on user role.
	 * @return array
	 */
	private static function get_user_counts() {
		$user_count          = array();
		$user_count_data     = count_users();
		$user_count['total'] = $user_count_data['total_users'];

		// Get user count based on user role
		foreach ( $user_count_data['avail_roles'] as $role => $count ) {
			$user_count[ $role ] = $count;
		}

		return $user_count;
	}

	/**
	 * Get all options starting with disabler_ prefix.
	 * @return array
	 */
	private static function get_all_disabler_options_values() {
		$options = get_option( 'disabler_settings', null );
		return array(
			'version' => plugin()->version,
			'options' => $options,
		);
	}

	/**
	 * When an admin user logs in, there user agent is tracked in user meta and collected here.
	 * @return array
	 */
	private static function get_admin_user_agents() {
		return array_filter( (array) get_option( 'disabler_tracker_ua', array() ) );
	}
}
