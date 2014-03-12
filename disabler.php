<?php
/*
Plugin Name: Disabler
Plugin URI: http://halfelf.org/plugins/disabler/
Description: Instead of installing a million plugins to turn off features you don't want, why not use ONE plugin?
Version: 3.0.0
Author: ipstenu
Author URI: http://ipstenu.org/
*/

class Disabler_Base_Class
{
	var $options;

	function __construct()
	{
		$this->options = get_option( 'disabler_options' );

		add_action( 'init', array( $this, 'disabler_upgrade' ) );
		add_action( 'init', array( $this, 'internationalization' ) );
		add_action( 'init', array( $this, 'plugin_init' ) );
	}

	function disabler_upgrade()
	{
		if( !get_option( 'disabler_plugin_version' ) )
		{
			$options_array = Array();

			if( FALSE !== get_option( 'disabler_smartquotes' ) )
			{
				$options_array['disabler_smartquotes'] = get_option( 'disabler_smartquotes' );
				delete_option( 'disabler_smartquotes' );
			}
			else
				$options_array['disabler_smartquotes'] = '0';

			if( FALSE !== get_option( 'disabler_capitalp' ) )
			{
				$options_array['disabler_capitalp'] = get_option( 'disabler_capitalp' );
				delete_option( 'disabler_capitalp' );
			}
			else
				$options_array['disabler_capitalp'] = '0';

			if( FALSE !== get_option( 'disabler_autop' ) )
			{
				$options_array['disabler_autop'] = get_option( 'disabler_autop' );
				delete_option( 'disabler_autop' );
			}
			else
				$options_array['disabler_autop'] = '0';

			if( FALSE !== get_option( 'disabler_selfping' ) )
			{
				$options_array['disabler_selfping'] = get_option( 'disabler_selfping' );
				delete_option( 'disabler_selfping' );
			}
			else
				$options_array['disabler_selfping'] = '0';

			if( FALSE !== get_option( 'disabler_norss' ) )
			{
				$options_array['disabler_norss'] = get_option( 'disabler_norss' );
				delete_option( 'disabler_norss' );
			}
			else
				$options_array['disabler_norss'] = '0';

			if( FALSE !== get_option( 'disabler_xmlrpc' ) )
			{
				$options_array['disabler_xmlrpc'] = get_option( 'disabler_xmlrpc' );
				delete_option( 'disabler_xmlrpc' );
			}
			else
				$options_array['disabler_xmlrpc'] = '0';

			if( FALSE !== get_option( 'disabler_revisions' ) )
			{
				$options_array['disabler_revisions'] = get_option( 'disabler_revisions' );
				delete_option( 'disabler_revisions' );
			}
			else
				$options_array['disabler_revisions'] = '0';

			if( FALSE !== get_option( 'disabler_autosave' ) )
			{
				$options_array['disabler_autosave'] = get_option( 'disabler_autosave' );
				delete_option( 'disabler_autosave' );
			}
			else
				$options_array['disabler_autosave'] = '0';

			if( FALSE !== get_option( 'disabler_version' ) )
			{
				$options_array['disabler_version'] = get_option( 'disabler_version' );
				delete_option( 'disabler_version' );
			}
			else
				$options_array['disabler_version'] = '0';

			if( FALSE !== get_option( 'disabler_nourl' ) )
			{
				$options_array['disabler_nourl'] = get_option( 'disabler_nourl' );
				delete_option( 'disabler_nourl' );
			}
			else
				$options_array['disabler_nourl'] = '0';

			add_option( 'disabler_options', $options_array );
			add_option( 'disabler_plugin_version', '3.0.0', '', 'no' );
		}
	}

	function internationalization()
	{
		load_plugin_textdomain( 'ippy_dis', FALSE, 'disabler/languages' );
	}

	function plugin_init()
	{
		/* FRONT END SETTINGS */
		/* Texturization */
		if( $this->options['disabler_smartquotes'] != '0' )
		{
			remove_filter('comment_text', 'wptexturize');
			remove_filter('the_content', 'wptexturize');
			remove_filter('the_excerpt', 'wptexturize');
			remove_filter('the_title', 'wptexturize');
			remove_filter('the_content_feed', 'wptexturize');
		}

		/* Disable Capital P in WordPress auto-correct */
		if( $this->options['disabler_capitalp'] != '0' )
		{
			remove_filter('the_content','capital_P_dangit');
			remove_filter('the_title','capital_P_dangit');
			remove_filter('comment_text','capital_P_dangit');
		}

		/* Remove the <p> from being automagically added in posts */
		if( $this->options['disabler_autop'] != '0' )
		{
			remove_filter('the_content', 'wpautop');
		}

		/* BACK END SETTINGS */
		/* Disable Self Pings */
		if( $this->options['disabler_selfping'] != '0' )
		{
			add_action( 'pre_ping', array($this, 'no_self_ping' ) );
		}

		/* No RSS */
		if( $this->options['disabler_norss'] != '0' )
		{
			add_action('do_feed', array( $this, 'disabler_kill_rss' ), 1);
			add_action('do_feed_rdf', array( $this, 'disabler_kill_rss' ), 1);
			add_action('do_feed_rss', array( $this, 'disabler_kill_rss' ), 1);
			add_action('do_feed_rss2', array( $this, 'disabler_kill_rss' ), 1);
			add_action('do_feed_atom', array( $this, 'disabler_kill_rss' ), 1);
		}

		/* XML RPC */
		if ($this->options['disabler_xmlrpc'] != '0' )
		{
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		/* Post Auto Saves */
		if( $this->options['disabler_autosave'] != '0' )
		{
			add_action( 'wp_print_scripts', array( $this, 'disabler_kill_autosave' ) );
		}

		/* Post Revisions */
		if( $this->options['disabler_revisions'] != '0' )
		{
			remove_action ( 'pre_post_update', 'wp_save_post_revision' );
		}

		/* PRIVACY SETTINGS */	
		/* Remove WordPress version from header */
		if( $this->options['disabler_version'] != '0' )
		{
			remove_action('wp_head', 'wp_generator');
		}

		/* Hide blog URL from Wordpress 'phone home' */
		if( $this->options['disabler_nourl'] != '0' )
		{
			add_filter('http_headers_useragent', array( $this, 'disabler_remove_url' ) );
		}
	}

	function no_self_ping( &$links )
	{
		$home = get_option( 'home' );
		foreach ( $links as $l => $link )
			if ( 0 === strpos( $link, $home ) )
				unset( $links[$l] );
	}

	function disabler_kill_rss()
	{
		wp_die( _e("No feeds available.", 'ippy_dis') );
	}

	function disabler_kill_autosave()
	{
		wp_deregister_script('autosave');
	}

	function disabler_remove_url( $default )
	{
		global $wp_version;
  		return 'WordPress/'.$wp_version;
	}
}

$disabler_base_class = new Disabler_Base_Class();

if( is_admin() )
	require_once dirname( __FILE__ ) . '/disabler_options.php';