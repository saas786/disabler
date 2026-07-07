<?php

/**
 * Plugin Name:       Disabler
 * Plugin URI:        https://wordpress.org/plugins/disabler/
 * Description:       Why install a million plugins to disable features you don't want when you can use just ONE plugin?
 * Version:           4.0.3
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            HyboPress Themes
 * Author URI:        https://wordpress.org/plugins/disabler/
 * Text Domain:       hbp-disabler
 * Domain Path:       /public/lang
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace HBP\Disabler;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DISABLER_FILE' ) ) {
    define( 'DISABLER_FILE', __FILE__ );
}

if ( ! defined( 'DISABLER_DIR' ) ) {
    define( 'DISABLER_DIR', __DIR__ );
}

if ( ! defined( 'DISABLER_BASENAME' ) ) {
    define( 'DISABLER_BASENAME', plugin_basename( DISABLER_FILE ) );
}

/*
 * Sanity check.
 *
 * Check that the site meets the minimum requirements for the plugin.
 */
if ( version_compare( $GLOBALS['wp_version'], '6.0.0', '<' ) || version_compare( PHP_VERSION, '8.2', '<' ) ) {
    require_once DISABLER_DIR . '/inc/bootstrap-compat.php';

    return;
}

require_once DISABLER_DIR . '/inc/bootstrap-autoload.php';

// Initialize the plugin.
add_action( 'plugins_loaded', static fn() => app(), -999 );

// Boot the plugin.
add_action( 'plugins_loaded', static fn() => app()->boot(), -998 );
