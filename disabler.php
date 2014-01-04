<?php
/*
Plugin Name: Disabler
Plugin URI: http://halfelf.org/plugins/disabler/
Description: Instead of installing a million plugins to turn off features you don't want, why not use ONE plugin?
Version: 2.3.1
Author: Mika Epstein
Author URI: http://ipstenu.org/

Copyright 2010-12 Mika Epstein (email: ipstenu@ipstenu.org)

    This file is part of Disabler, a plugin for WordPress.

    Disabler is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    Disabler is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WordPress.  If not, see <http://www.gnu.org/licenses/>.

*/

global $wp_version;
$exit_msg_ver = 'Sorry, but this plugin is no longer supported on pre-3.0 WordPress installs.';
if (version_compare($wp_version,"2.9","<")) { exit($exit_msg_ver); }

// Internationalization
add_action( 'init', 'ippy_dis_internationalization' );
function ippy_dis_internationalization() {
	load_plugin_textdomain('ippy_dis', false, 'disabler/languages' );
}


/* FRONT END SETTINGS */
/* Texturization */
if (get_option('disabler_smartquotes') != '0' ) {
	remove_filter('comment_text', 'wptexturize');
	remove_filter('the_content', 'wptexturize');
	remove_filter('the_excerpt', 'wptexturize');
	remove_filter('the_title', 'wptexturize');
	remove_filter('the_content_feed', 'wptexturize');
	}
/* Disable Capital P in WordPress auto-correct */
if (get_option('disabler_capitalp') != '0' ) {
	remove_filter('the_content','capital_P_dangit');
	remove_filter('the_title','capital_P_dangit');
	remove_filter('comment_text','capital_P_dangit');
	}
/* Remove the <p> from being automagically added in posts */
if (get_option('disabler_autop') != '0' ) {
	remove_filter('the_content', 'wpautop');
	}
	
/* BACK END SETTINGS */
/* Disable Self Pings */
if (get_option('disabler_selfping') != '0' ) {
	function no_self_ping( &$links ) {
		$home = get_option( 'home' );
		foreach ( $links as $l => $link )
			if ( 0 === strpos( $link, $home ) )
               unset($links[$l]);
		}
	add_action( 'pre_ping', 'no_self_ping' );
	}
/* No RSS */
if (get_option('disabler_norss') != '0' ) {
	function disabler_kill_rss() {
		wp_die( _e("No feeds available.", 'ippy_dis') );
	}
 
	add_action('do_feed', 'disabler_kill_rss', 1);
	add_action('do_feed_rdf', 'disabler_kill_rss', 1);
	add_action('do_feed_rss', 'disabler_kill_rss', 1);
	add_action('do_feed_rss2', 'disabler_kill_rss', 1);
	add_action('do_feed_atom', 'disabler_kill_rss', 1);
	}
/* XML RPC */
if (get_option('disabler_xmlrpc') != '0' ) {
    add_filter( 'xmlrpc_enabled', '__return_false' );
    }
/* Post Auto Saves */
if (get_option('disabler_autosave') != '0' ) {
	
	function disabler_kill_autosave(){
		wp_deregister_script('autosave');
		}
	add_action( 'wp_print_scripts', 'disabler_kill_autosave' );
	}
/* Post Revisions */
if (get_option('disabler_revisions') != '0' ) {
	remove_action ( 'pre_post_update', 'wp_save_post_revision' );
	}

/* PRIVACY SETTINGS */	
/* Remove WordPress version from header */
if (get_option('disabler_version') != '0' ) {
	remove_action('wp_head', 'wp_generator');
	}
/* Hide blog URL from Wordpress 'phone home' */
if (get_option('disabler_nourl') != '0' ) {
	function disabler_remove_url($default)
		{
  		global $wp_version;
  		return 'WordPress/'.$wp_version;
		}
	add_filter('http_headers_useragent', 'disabler_remove_url');
	}
	
// Create the options when turned on

	function disabler_activate() {
        add_option('disabler_smartquotes', '0');
        add_option('disabler_capitalp', '0');
        add_option('disabler_autop', '0');

        add_option('disabler_selfping', '0');
        add_option('disabler_norss', '0');
        add_option('disabler_xmlrpc', '0');
        add_option('disabler_revisions', '0');
        add_option('disabler_autosave', '0');

        add_option('disabler_version', '0');
        add_option('disabler_nourl', '0');
    }

// Load the options page
function disabler_options() {
        if (function_exists('add_submenu_page')) {
          add_submenu_page('options-general.php', 'Disabler', 'Disabler', 'activate_plugins', 'disabler/disabler_options.php');
        }
}

// Hooks
add_action('admin_menu', 'disabler_options');

register_activation_hook( __FILE__, 'disabler_activate' );

// donate link on manage plugin page
add_filter('plugin_row_meta', 'disabler_donate_link', 10, 2);
function disabler_donate_link($links, $file) {
        if ($file == plugin_basename(__FILE__)) {
                $donate_link = '<a href="https://www.wepay.com/donations/halfelf-wp">Donate</a>';
                $links[] = $donate_link;
        }
        return $links;
}

	
?>