<?php
/**
 * Plugin Name:       Disabler
 * Plugin URI:        https://wordpress.org/plugins/disabler/
 * Description:       Why install a million plugins to disable features you don't want when you can use just ONE plugin?
 * Version:           4.0.2
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Hybopress Themes
 * Author URI:        https://wordpress.org/plugins/disabler/
 * Text Domain:       hbp-disabler
 * Domain Path:       /public/lang
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
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
