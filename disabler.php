<?php
/**
 * Plugin Name: Disabler
 * Plugin URI:  https://wordpress.org/plugins/disabler/
 * Description: Instead of installing million plugins to turn off features you don't want, why not use just ONE plugin?
 * Version:     3.0.3
 * Author:      saas
 * Author URI:  https://wordpress.org/plugins/disabler/
 * Text Domain: disabler
 * Domain Path: /lang
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * license: GPLv2 or later
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

# If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

# Define DISABLER_PLUGIN_FILE.
if ( ! defined( 'DISABLER_PLUGIN_FILE' ) ) {
	define( 'DISABLER_PLUGIN_FILE', __FILE__ );
}

# Include the main Disabler class.
include_once dirname( DISABLER_PLUGIN_FILE ) . '/inc/class-disabler.php';