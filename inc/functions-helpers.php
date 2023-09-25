<?php
/**
 * Helper functions for handling plugin options.
 */

namespace HBP\Disabler;

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param string $value Value.
 */
function maybe_define_constant( $name, $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

/**
 * Smartquotes / Texturization disabled?
 *
 * @return bool
 */
function is_texturization_disabled() {
    return apply_filters( 'hbp/disabler/is_texturization_disabled', disabler_get_setting( 'texturization_disabled' ) );
}

/**
 * Capital P in WordPress auto-correct disabled?
 *
 * @return bool
 */
function is_capital_p_disabled() {
    return apply_filters( 'hbp/disabler/is_capital_p_disabled', disabler_get_setting( 'capital_p_disabled' ) );
}

/**
 * Remove the <p> from being automagically added in posts
 * disable autop?
 *
 * @return bool
 */
function is_autop_disabled() {
    return apply_filters( 'hbp/disabler/is_autop_disabled', disabler_get_setting( 'autop_disabled' ) );
}

/**
 * Self ping Disabled.
 *
 * @return bool
 */
function is_selfping_disabled() {
    return apply_filters( 'hbp/disabler/is_selfping_disabled', disabler_get_setting( 'selfping_disabled' ) );
}

/**
 * RSS Feed Disabled.
 *
 * @return bool
 */
function is_rss_feed_disabled() {
    return apply_filters( 'hbp/disabler/is_rss_feed_disabled', disabler_get_setting( 'rss_feed_disabled' ) );
}

/**
 * xmlrpc Disabled.
 *
 * @return bool
 */
function is_xmlrpc_disabled() {
    return apply_filters( 'hbp/disabler/is_xmlrpc_disabled', disabler_get_setting( 'xmlrpc_disabled' ) );
}

/**
 * autosave Disabled.
 *
 * @return bool
 */
function is_autosave_disabled() {
    return apply_filters( 'hbp/disabler/is_autosave_disabled', disabler_get_setting( 'autosave_disabled' ) );
}

/**
 * Revisions Disabled.
 *
 * @return bool
 */
function is_revisions_disabled() {
    return apply_filters( 'hbp/disabler/is_revisions_disabled', disabler_get_setting( 'revisions_disabled' ) );
}

/**
 * hide WordPress version.
 *
 * @return bool
 */
function is_hide_wp_version() {
    return apply_filters( 'hbp/disabler/is_hide_wp_version', disabler_get_setting( 'hide_wp_version' ) );
}

/**
 * fake user agent value sent with an HTTP request?
 *
 * @return bool
 */
function is_fake_user_agent_value() {
    return apply_filters( 'hbp/disabler/is_fake_user_agent_value', disabler_get_setting( 'fake_user_agent_value' ) );
}

/**
 * Allow usage tracking?
 *
 * @return bool
 */
function is_allow_usage_tracking() {
    return apply_filters( 'hbp/disabler/is_allow_usage_tracking', disabler_get_setting( 'allow_usage_tracking' ) );
}

/**
 * Gets a setting from the plugin settings in the database.
 *
 * @return mixed
 */
function disabler_get_setting( $option = '' ) {
    $defaults = disabler_get_default_settings();

    $settings = wp_parse_args( get_option( 'disabler_settings', $defaults ), $defaults );

    return $settings[ $option ] ?? false;
}

/**
 * Returns an array of the default plugin settings.
 *
 * @return array
 */
function disabler_get_default_settings() {
    return [
        'allow_usage_tracking'   => 0,
        'autop_disabled'         => 0,
        'autosave_disabled'      => 0,
        'capital_p_disabled'     => 0,
        'fake_user_agent_value'  => 0,
        'hide_wp_version'        => 0,
        'revisions_disabled'     => 0,
        'rss_feed_disabled'      => 0,
        'selfping_disabled'      => 0,
        'texturization_disabled' => 0,
        'xmlrpc_disabled'        => 0,
    ];
}

/**
 * Get post types which support revisions.
 *
 * @return array
 */
function get_revision_post_types() {
    $revision_post_types = [];

    foreach ( get_post_types() as $type ) {
        $object = get_post_type_object( $type );
        if ( ! post_type_supports( $type, 'revisions' ) || null === $object ) {
            continue;
        }

        $name = property_exists( $object, 'labels' ) && property_exists( $object->labels, 'name' )
            ? $object->labels->name
            : $object->name;

        $revision_post_types[ $type ] = $name;
    }

    return $revision_post_types;
}
