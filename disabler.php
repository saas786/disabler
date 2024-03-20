<?php
/**
 * Plugin Name:       Disabler
 * Plugin URI:        https://wordpress.org/plugins/disabler/
 * Description:       Why install a million plugins to disable features you don't want when you can use just ONE plugin?
 * Version:           4.0.0-RC.3
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Hybopress Themes
 * Author URI:        https://wordpress.org/plugins/disabler/
 * Text Domain:       disabler
 * Domain Path:       /public/lang
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'WPINC' ) ) {
    exit;
}

if ( ! defined( 'DISABLER_FILE' ) ) {
    define( 'DISABLER_FILE', __FILE__ );
}

if ( ! defined( 'DISABLER_BASENAME' ) ) {
    define( 'DISABLER_BASENAME', plugin_basename( DISABLER_FILE ) );
}

require_once dirname( DISABLER_FILE ) . '/inc/init.php';
