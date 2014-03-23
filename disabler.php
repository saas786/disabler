<?php
/*
Plugin Name: Disabler
Plugin URI: http://jesin.tk/wordpress-plugins/disabler/
Description: Instead of installing a million plugins to turn off features you don't want, why not use ONE plugin?
Version: 3.1.0
Author: ipstenu
Author URI: http://ipstenu.org/
*/

class Disabler_Base_Class {

	var $options;

	function __construct() {

		$this->options = get_option( 'disabler_options' );

		add_action( 'init', array( $this, 'upgrade' ) );
		add_action( 'init', array( $this, 'internationalization' ) );
		add_action( 'init', array( $this, 'plugin_init' ) );
	}

	function upgrade() {

		if ( ! get_option( 'disabler_plugin_version' ) ) {

			$options_array = Array();

			if ( FALSE !== get_option( 'disabler_smartquotes' ) ) {
				$options_array['disabler_smartquotes'] = get_option( 'disabler_smartquotes' );
				delete_option( 'disabler_smartquotes' );
			}
			else {
				$options_array['disabler_smartquotes'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_capitalp' ) ) {
				$options_array['disabler_capitalp'] = get_option( 'disabler_capitalp' );
				delete_option( 'disabler_capitalp' );
			}
			else {
				$options_array['disabler_capitalp'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_autop' ) ) {
				$options_array['disabler_autop'] = get_option( 'disabler_autop' );
				delete_option( 'disabler_autop' );
			}
			else {
				$options_array['disabler_autop'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_selfping' ) ) {
				$options_array['disabler_selfping'] = get_option( 'disabler_selfping' );
				delete_option( 'disabler_selfping' );
			}
			else {
				$options_array['disabler_selfping'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_norss' ) ) {
				$options_array['disabler_norss'] = get_option( 'disabler_norss' );
				delete_option( 'disabler_norss' );
			}
			else {
				$options_array['disabler_norss'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_xmlrpc' ) ) {
				$options_array['disabler_xmlrpc'] = get_option( 'disabler_xmlrpc' );
				delete_option( 'disabler_xmlrpc' );
			}
			else {
				$options_array['disabler_xmlrpc'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_revisions' ) ) {
				$options_array['disabler_revisions'] = get_option( 'disabler_revisions' );
				delete_option( 'disabler_revisions' );
			}
			else {
				$options_array['disabler_revisions'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_autosave' ) ) {
				$options_array['disabler_autosave'] = get_option( 'disabler_autosave' );
				delete_option( 'disabler_autosave' );
			}
			else {
				$options_array['disabler_autosave'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_version' ) ) {
				$options_array['disabler_version'] = get_option( 'disabler_version' );
				delete_option( 'disabler_version' );
			}
			else {
				$options_array['disabler_version'] = '0';
			}

			if ( FALSE !== get_option( 'disabler_nourl' ) ) {
				$options_array['disabler_nourl'] = get_option( 'disabler_nourl' );
				delete_option( 'disabler_nourl' );
			}
			else {
				$options_array['disabler_nourl'] = '0';
			}

			add_option( 'disabler_options', $options_array );
			add_option( 'disabler_plugin_version', '3.1.0', '', 'no' );
		}
		elseif ( '3.1.0' == get_option( 'disabler_plugin_version' ) ) {
			update_option( 'disabler_plugin_version', '3.1.0' );
		}
	}

	function internationalization() {
		load_plugin_textdomain( 'ippy_dis', FALSE, 'disabler/languages' );
	}

	function plugin_init() {

		/* FRONT END SETTINGS */
		if( isset( $this->options['disabler_frontend_gfonts'] ) && '0' != $this->options['disabler_frontend_gfonts'] ) {
			add_action( 'parse_request', array( $this, 'dummy_css' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'nullify_frontend_gfonts' ), PHP_INT_MAX );
		}

		/* Texturization */
		if ( isset( $this->options['disabler_smartquotes'] ) && '0' != $this->options['disabler_smartquotes'] ) {
			remove_filter( 'comment_text',		'wptexturize' );
			remove_filter( 'the_content',		'wptexturize' );
			remove_filter( 'the_excerpt',		'wptexturize' );
			remove_filter( 'the_title',		'wptexturize' );
			remove_filter( 'the_content_feed',	'wptexturize' );
		}

		/* Disable Capital P in WordPress auto-correct */
		if ( isset( $this->options['disabler_capitalp'] ) && '0' != $this->options['disabler_capitalp'] ) {
			remove_filter( 'the_content',	'capital_P_dangit' );
			remove_filter( 'the_title',	'capital_P_dangit' );
			remove_filter( 'comment_text',	'capital_P_dangit' );
		}

		/* Remove the <p> from being automagically added in posts */
		if ( isset( $this->options['disabler_autop'] ) && '0' != $this->options['disabler_autop'] ) {
			remove_filter( 'the_content', 'wpautop' );
			add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_options' ) );
		}

		/* BACK END SETTINGS */
		/* Disable Self Pings */
		if ( isset( $this->options['disabler_selfping'] ) && '0' != $this->options['disabler_selfping'] ) {
			add_action( 'pre_ping', array( $this, 'no_self_ping' ) );
		}

		/* No RSS */
		if ( isset( $this->options['disabler_norss'] ) && '0' != $this->options['disabler_norss'] ) {
			remove_action( 'wp_head', 'feed_links_extra',	3 );
			remove_action( 'wp_head', 'feed_links',			2 );

			add_action( 'do_feed',		array( $this, 'kill_rss' ), 1 );
			add_action( 'do_feed_rdf',	array( $this, 'kill_rss' ), 1 );
			add_action( 'do_feed_rss',	array( $this, 'kill_rss' ), 1 );
			add_action( 'do_feed_rss2',	array( $this, 'kill_rss' ), 1 );
			add_action( 'do_feed_atom',	array( $this, 'kill_rss' ), 1 );
		}

		/* XML RPC */
		if ( isset( $this->options['disabler_xmlrpc'] ) && '0' != $this->options['disabler_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled',	'__return_false'	);
			add_filter( 'wp_headers',	'remove_x_pingback' );
		}

		/* Post Auto Saves */
		if ( isset( $this->options['disabler_autosave'] ) && '0' != $this->options['disabler_autosave'] ) {
			add_action( 'init', array( $this, 'kill_autosave' ), 999 );
		}

		/* Post Revisions */
		if ( isset( $this->options['disabler_revisions'] ) && '0' != $this->options['disabler_revisions'] ) {
			remove_action( 'post_updated', 'wp_save_post_revision' );
		}

		/* Google Fonts */
		if ( isset( $this->options['disabler_backend_gfonts'] ) && '0' != $this->options['disabler_backend_gfonts'] ) {
			add_action( 'parse_request', array( $this, 'dummy_css' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'remove_gfonts' ) );
		}

		/* WordPress update notification */
		if ( isset( $this->options['disabler_update_nag'] ) && '0' != $this->options['disabler_update_nag'] ) {
			add_action( 'admin_menu', array( $this, 'wp_update_notice' ) );
		}

		/* PRIVACY SETTINGS */	
		/* Remove WordPress version from header */
		if ( isset( $this->options['disabler_version'] ) && '0' != $this->options['disabler_version'] ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		/* Hide blog URL from Wordpress 'phone home' */
		if ( isset( $this->options['disabler_nourl'] ) && $this->options['disabler_nourl'] ) {
			add_filter( 'http_headers_useragent', array( $this, 'remove_url' ) );
		}
	}

	function no_self_ping( &$links ) {
		$home = get_option( 'home' );
		foreach ( $links as $l => $link ) {
			if ( 0 === strpos( $link, $home ) ) {
				unset( $links[$l] );
			}
		}
	}

	function kill_rss() {
		wp_die( __( 'No feeds available.', 'ippy_dis') );
	}

	function remove_x_pingback( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	function kill_autosave() {
		wp_deregister_script( 'autosave' );
	}

	function remove_gfonts() {
		wp_deregister_style( 'open-sans' );
		wp_register_style( 'open-sans', home_url( '?gfont_dummy=1' ) , array(), null );
	}

	function nullify_frontend_gfonts() {
		global $wp_styles;
		$google_fonts = array();
		if ( empty( $wp_styles ) ) {
			return;
		}
		foreach ( $wp_styles->queue as $handle ) {
			if ( stristr( $wp_styles->registered[$handle]->src, "fonts.googleapis.com" ) ) {
				wp_deregister_style( $handle );
				wp_register_style( $handle, home_url( '?gfont_dummy=1' ) , array(), null );
			}
			if ( !empty( $wp_styles->registered[$handle]->deps ) ) {
				foreach( $wp_styles->registered[$handle]->deps as $deps ) {
					if ( stristr( $wp_styles->registered[$deps]->src, "fonts.googleapis.com" ) ) {
						wp_deregister_style( $deps );
						wp_register_style( $deps, home_url( '?gfont_dummy=1' ) , array(), null );
					}
				}
			}
		}
	}

	function wp_update_notice() {
		remove_action( 'admin_notices', 'update_nag', 3 );
	}

	function remove_url( $default ) {
		global $wp_version;
  		return 'WordPress/' . $wp_version;
	}

	function add_query_vars_filter( $vars ) {
		$vars[] = 'gfont_dummy';
		return $vars;
	}

	function dummy_css( &$wp ) {
		if ( isset( $wp->query_vars['gfont_dummy'] ) && '1' == $wp->query_vars['gfont_dummy'] ) {
			header( 'Content-Type: text/css' );
			die();
		}
	}

	//Prevent TinyMCE from stripping manually entered <p> tags - Issue #2
	function tiny_mce_options( $mceInit ) {
		$mceInit['wpautop'] = false;
		$mceInit['apply_source_formatting'] = true;
		$mceInit['forced_root_block'] = false;
		return $mceInit;
	}
}

$disabler_base_class = new Disabler_Base_Class();

if( is_admin() )
	require_once dirname( __FILE__ ) . '/disabler_options.php';
