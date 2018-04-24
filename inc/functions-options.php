<?php
/**
 * Functions for handling plugin options.
 *
 */

/**
 * Smartquotes / Texturization disabled?
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_texturization_disabled() {

	return apply_filters( 'disabler_texturization_disabled', disabler_get_setting( 'texturization_disabled' ) );
}

/**
 * Capital P in WordPress auto-correct disabled?
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_capital_p_disabled() {

	return apply_filters( 'disabler_capital_p_disabled', disabler_get_setting( 'capital_p_disabled' ) );
}

/**
 * Remove the <p> from being automagically added in posts
 * disable autop?
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_autop_disabled() {

	return apply_filters( 'disabler_autop_disabled', disabler_get_setting( 'autop_disabled' ) );
}

/**
 * Seflping Disabled
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_selfping_disabled() {

	return apply_filters( 'disabler_selfping_disabled', disabler_get_setting( 'selfping_disabled' ) );
}

/**
 * RSS Feed Disabled
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_rss_feed_disabled() {

	return apply_filters( 'disabler_rss_feed_disabled', disabler_get_setting( 'rss_feed_disabled' ) );
}

/**
 * xmlrpc Disabled
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_xmlrpc_disabled() {

	return apply_filters( 'disabler_xmlrpc_disabled', disabler_get_setting( 'xmlrpc_disabled' ) );
}

/**
 * autosave Disabled
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_autosave_disabled() {

	return apply_filters( 'disabler_autosave_disabled', disabler_get_setting( 'autosave_disabled' ) );
}

/**
 * Revisions Disabled
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_revisions_disabled() {

	return apply_filters( 'disabler_revisions_disabled', disabler_get_setting( 'revisions_disabled' ) );
}

/**
 * hide WordPress version
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_hide_wp_version() {

	return apply_filters( 'disabler_hide_wp_version', disabler_get_setting( 'hide_wp_version' ) );
}

/**
 * fake user agent value sent with an HTTP request.?
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_fake_user_agent_value() {

	return apply_filters( 'disabler_fake_user_agent_value', disabler_get_setting( 'fake_user_agent_value' ) );
}

/**
 * Allow usage tracking?
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_allow_usage_tracking() {

	return apply_filters( 'disabler_allow_usage_tracking', disabler_get_setting( 'allow_usage_tracking' ) );
}

/**
 * Gets a setting from from the plugin settings in the database.
 *
 * @since  3.0.3
 * @access public
 * @return mixed
 */
function disabler_get_setting( $option = '' ) {

	$defaults = disabler_get_default_settings();

	$settings = wp_parse_args( get_option( 'disabler_settings', $defaults ), $defaults );

	return isset( $settings[ $option ] ) ? $settings[ $option ] : false;
}

/**
 * Returns an array of the default plugin settings.
 *
 * @since  3.0.3
 * @access public
 * @return array
 */
function disabler_get_default_settings() {

	return array(
		'texturization_disabled' => 0,
		'capital_p_disabled'     => 0,
		'autop_disabled'         => 0,
		'selfping_disabled'      => 0,
		'rss_feed_disabled'      => 0,
		'xmlrpc_disabled'        => 0,
		'autosave_disabled'      => 0,
		'revisions_disabled'     => 0,
		'hide_wp_version'        => 0,
		'fake_user_agent_value'  => 0,
		'allow_usage_tracking'   => 0,
	);
}
