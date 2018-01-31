<?php

/**
 * Plugin Name: Disabler
 * Plugin URI: https://wordpress.org/plugins/disabler/
 * Description: Instead of installing million plugins to turn off features you don't want, why not use just ONE plugin?
 * Version: 3.0.2
 * Author: saas
 * Author URI: https://wordpress.org/plugins/disabler/
 * Requires at least: 3.1
 * Tested up to: 4.8
 *
 * Text Domain: disabler
 * Domain Path: /lang
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not,
 * write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @link      https://wordpress.org/plugins/disabler
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

class Disabler_Base_Class {
	var $options;

	function __construct() {
		$this->options = get_option( 'disabler_options' );

		add_action( 'init', array( $this, 'upgrade' ) );
		add_action( 'init', array( $this, 'i18n' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	function upgrade() {
		if ( ! get_option( 'disabler_plugin_version' ) ) {
			$options_array = array();

			if ( false !== get_option( 'disabler_smartquotes' ) ) {
				$options_array['disabler_smartquotes'] = get_option( 'disabler_smartquotes' );
				delete_option( 'disabler_smartquotes' );
			} else {
				$options_array['disabler_smartquotes'] = '0';
			}

			if ( false !== get_option( 'disabler_capitalp' ) ) {
				$options_array['disabler_capitalp'] = get_option( 'disabler_capitalp' );
				delete_option( 'disabler_capitalp' );
			} else {
				$options_array['disabler_capitalp'] = '0';
			}

			if ( false !== get_option( 'disabler_autop' ) ) {
				$options_array['disabler_autop'] = get_option( 'disabler_autop' );
				delete_option( 'disabler_autop' );
			} else {
				$options_array['disabler_autop'] = '0';
			}

			if ( false !== get_option( 'disabler_selfping' ) ) {
				$options_array['disabler_selfping'] = get_option( 'disabler_selfping' );
				delete_option( 'disabler_selfping' );
			} else {
				$options_array['disabler_selfping'] = '0';
			}

			if ( false !== get_option( 'disabler_norss' ) ) {
				$options_array['disabler_norss'] = get_option( 'disabler_norss' );
				delete_option( 'disabler_norss' );
			} else {
				$options_array['disabler_norss'] = '0';
			}

			if ( false !== get_option( 'disabler_xmlrpc' ) ) {
				$options_array['disabler_xmlrpc'] = get_option( 'disabler_xmlrpc' );
				delete_option( 'disabler_xmlrpc' );
			} else {
				$options_array['disabler_xmlrpc'] = '0';
			}

			if ( false !== get_option( 'disabler_revisions' ) ) {
				$options_array['disabler_revisions'] = get_option( 'disabler_revisions' );
				delete_option( 'disabler_revisions' );
			} else {
				$options_array['disabler_revisions'] = '0';
			}

			if ( false !== get_option( 'disabler_autosave' ) ) {
				$options_array['disabler_autosave'] = get_option( 'disabler_autosave' );
				delete_option( 'disabler_autosave' );
			} else {
				$options_array['disabler_autosave'] = '0';
			}

			if ( false !== get_option( 'disabler_version' ) ) {
				$options_array['disabler_version'] = get_option( 'disabler_version' );
				delete_option( 'disabler_version' );
			} else {
				$options_array['disabler_version'] = '0';
			}

			if ( false !== get_option( 'disabler_nourl' ) ) {
				$options_array['disabler_nourl'] = get_option( 'disabler_nourl' );
				delete_option( 'disabler_nourl' );
			} else {
				$options_array['disabler_nourl'] = '0';
			}

			add_option( 'disabler_options', $options_array );
			add_option( 'disabler_plugin_version', '3.0.0', '', 'no' );
		} // End if().
	}

	function i18n() {
		load_plugin_textdomain( 'disabler', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	function init() {
		/* FRONT END SETTINGS */
		/* Texturization */
		if ( '0' != $this->options['disabler_smartquotes'] ) {
			remove_filter( 'comment_text', 'wptexturize' );
			remove_filter( 'the_content', 'wptexturize' );
			remove_filter( 'the_excerpt', 'wptexturize' );
			remove_filter( 'the_title', 'wptexturize' );
			remove_filter( 'the_content_feed', 'wptexturize' );
		}

		/* Disable Capital P in WordPress auto-correct */
		if ( '0' != $this->options['disabler_capitalp'] ) {
			remove_filter( 'the_content', 'capital_P_dangit' );
			remove_filter( 'the_title', 'capital_P_dangit' );
			remove_filter( 'comment_text', 'capital_P_dangit' );
		}

		/* Remove the <p> from being automagically added in posts */
		if ( '0' != $this->options['disabler_autop'] ) {
			remove_filter( 'the_content', 'wpautop' );
		}

		/* BACK END SETTINGS */
		/* Disable Self Pings */
		if ( '0' != $this->options['disabler_selfping'] ) {
			add_action( 'pre_ping', array( $this, 'no_self_ping' ) );
		}

		/* No RSS */
		if ( '0' != $this->options['disabler_norss'] ) {
			add_action( 'do_feed', array( $this, 'disabler_kill_rss' ), 1 );
			add_action( 'do_feed_rdf', array( $this, 'disabler_kill_rss' ), 1 );
			add_action( 'do_feed_rss', array( $this, 'disabler_kill_rss' ), 1 );
			add_action( 'do_feed_rss2', array( $this, 'disabler_kill_rss' ), 1 );
			add_action( 'do_feed_atom', array( $this, 'disabler_kill_rss' ), 1 );
		}

		/* XML RPC */
		if ( '0' != $this->options['disabler_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		/* Post Auto Saves */
		if ( '0' != $this->options['disabler_autosave'] ) {
			add_action( 'wp_print_scripts', array( $this, 'disabler_kill_autosave' ) );
		}

		/* Post Revisions */
		if ( '0' != $this->options['disabler_revisions'] ) {
			remove_action( 'pre_post_update', 'wp_save_post_revision' );
		}

		/* PRIVACY SETTINGS */
		/* Remove WordPress version from header */
		if ( '0' != $this->options['disabler_version'] ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		/* Hide blog URL from Wordpress 'phone home' */
		if ( '0' != $this->options['disabler_nourl'] ) {
			add_filter( 'http_headers_useragent', array( $this, 'disabler_remove_url' ) );
		}
	}

	function no_self_ping( &$links ) {
		$home = get_option( 'home' );

		foreach ( $links as $l => $link ) {
			if ( 0 === strpos( $link, $home ) ) {
				unset( $links[ $l ] );
			}
		}
	}

	function disabler_kill_rss() {
		wp_die( _e( 'No feeds available.', 'disabler' ) );
	}

	function disabler_kill_autosave() {
		wp_deregister_script( 'autosave' );
	}

	function disabler_remove_url( $default ) {
		global $wp_version;

		return 'WordPress/' . $wp_version;
	}
}

$disabler_base_class = new Disabler_Base_Class();

if ( is_admin() ) {
	require_once dirname( __FILE__ ) . '/inc/options.php';
}
