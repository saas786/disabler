<?php

namespace Disabler;

/**
 * Represents the plugin data.
 */
class Disabler_Tracking_Plugin_Data implements Disabler_Collection {

	/**
	 * Returns the collection data.
	 *
	 * @return array The collection data.
	 */
	public function get() {
		return array(
			'plugins' => self::get_plugin_data(),
		);
	}

	/**
	 * Returns all plugins.
	 *
	 * @return array The formatted plugins.
	 */
	protected static function get_plugin_data() {

		// Plugin info
		return self::get_all_plugins();
	}

	/**
	 * Get all plugins grouped into activated or not.
	 * @return array
	 */
	private static function get_all_plugins() {

		// Ensure get_plugins function is loaded
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        	 = get_plugins();
		$active_plugins_keys = get_option( 'active_plugins', array() );
		$active_plugins 	 = array();
		
		#$plugins = array_map( array( $this, 'format_plugin' ), $plugins );
		#$plugins = array_map( 'self::format_plugin', $plugins );
		$plugins = array_map( array( __CLASS__, 'format_plugin' ), $plugins );

		foreach ( $plugins as $k => $v ) {

			if ( in_array( $k, $active_plugins_keys ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $k ] );
				$active_plugins[ $k ] = $v ;
			} else {
				$plugins[ $k ] = $v ;
			}

		}

		return array( 'active_plugins' => $active_plugins, 'inactive_plugins' => $plugins );
	}

	/**
	 * Formats the plugin array.
	 *
	 * @param array $plugin The plugin details.
	 *
	 * @return array The formatted array.
	 */
	protected static function format_plugin( array $plugin ) {
		return array(
			'name'    => $plugin['Name'],
			'url'     => $plugin['PluginURI'],
			'version' => $plugin['Version'],
			'network' => $plugin['Network'],
			'author'  => array(
				'name' => wp_strip_all_tags( $plugin['Author'], true ),
				'url'  => $plugin['AuthorURI'],
			),
		);
	}
}
