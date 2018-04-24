<?php

add_action( 'init', 'disabler_init' );

function disabler_init() {

	/**
	 * FRONT END SETTINGS
	 */

	# Texturization
	if ( disabler_texturization_disabled() ) {
		foreach ( array( 'comment_author', 'term_name', 'link_name', 'link_description', 'link_notes', 'bloginfo', 'wp_title', 'widget_title' ) as $filter ) {
			remove_filter( $filter, 'wptexturize' );
		}

		foreach ( array( 'single_post_title', 'single_cat_title', 'single_tag_title', 'single_month_title', 'nav_menu_attr_title', 'nav_menu_description' ) as $filter ) {
			remove_filter( $filter, 'wptexturize' );
		}

		foreach ( array( 'term_description', 'get_the_post_type_description' ) as $filter ) {
			remove_filter( $filter, 'wptexturize' );
		}

		remove_filter( 'the_title', 'wptexturize' );
		remove_filter( 'the_content', 'wptexturize' );
		remove_filter( 'the_excerpt', 'wptexturize' );
		remove_filter( 'the_post_thumbnail_caption', 'wptexturize' );
		remove_filter( 'comment_text', 'wptexturize' );
		remove_filter( 'list_cats', 'wptexturize' );
		remove_filter( 'widget_text_content', 'wptexturize' );
		remove_filter( 'the_excerpt_embed', 'wptexturize' );

		/*
		remove_filter( 'the_content_feed', 'wptexturize' );
		remove_filter( 'category_description', 'wptexturize' );
		*/
	}

	# Disable Capital P in WordPress auto-correct
	if ( disabler_capital_p_disabled() ) {
		remove_filter( 'the_title', 'capital_P_dangit', 11 );
		remove_filter( 'the_content', 'capital_P_dangit', 11 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );
	}

	# Remove the <p> from being automagically added in posts
	if ( disabler_autop_disabled() ) {
		foreach ( array( 'term_description', 'get_the_post_type_description' ) as $filter ) {
			#remove_filter( $filter, 'wpautop' );
		}
		remove_filter( 'the_content', 'wpautop' );
		#remove_filter( 'the_excerpt', 'wpautop' );
		#remove_filter( 'comment_text', 'wpautop', 30 );
		#remove_filter( 'widget_text_content', 'wpautop' );
		#remove_filter( 'the_excerpt_embed', 'wpautop' );
	}

	/**
	 * BACK END SETTINGS
	 */

	# Disable Self Pings
	if ( disabler_selfping_disabled() ) {
		add_action( 'pre_ping', 'disabler_no_self_ping' );
	}

	# No RSS
	if ( disabler_rss_feed_disabled() ) {
		add_action( 'do_feed', 'disabler_kill_rss', 1 );
		add_action( 'do_feed_rdf', 'disabler_kill_rss', 1 );
		add_action( 'do_feed_rss', 'disabler_kill_rss', 1 );
		add_action( 'do_feed_rss2', 'disabler_kill_rss', 1 );
		add_action( 'do_feed_atom', 'disabler_kill_rss', 1 );
	}

	# XML RPC
	if ( disabler_xmlrpc_disabled() ) {
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}

	# Post Auto Saves
	if ( disabler_autosave_disabled() ) {
		add_action( 'wp_print_scripts', 'disabler_kill_autosave' );
	}

	# Post Revisions
	if ( disabler_revisions_disabled() ) {

		# Method old: No longer works as of v4.9
		#remove_action( 'pre_post_update', 'wp_save_post_revision' );

		# Method 1: Disables revisions increment only
		remove_action( 'post_updated', 'wp_save_post_revision', 10, 1 );

		# Method 2: Disables revisions increment, and also hides revisions panel
		#add_filter( 'wp_revisions_to_keep', '__return_false' );

	}

	/**
	 * PRIVACY SETTINGS
	 */

	# Remove WordPress version from header
	if ( disabler_hide_wp_version() ) {
		remove_action( 'wp_head', 'wp_generator' );
	}

	# Hide blog URL from Wordpress 'phone home'
	if ( disabler_fake_user_agent_value() ) {
		add_filter( 'http_headers_useragent', 'disabler_remove_url' );
	}
}

function disabler_no_self_ping( &$links ) {
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

/**
 * Helper function for getting the script/style `.min` suffix for minified files.
 *
 * @since  3.0.3
 * @access public
 * @return string
 */
function disabler_get_min_suffix() {

	return disabler_is_script_debug() ? '' : '.min';
}

/**
 * Conditional check to determine if we are in script debug mode.  This is generally used
 * to decide whether to load development versions of scripts/styles.
 *
 * @since  3.0.3
 * @access public
 * @return bool
 */
function disabler_is_script_debug() {

	return apply_filters( 'disabler_is_script_debug', defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
}

/**
 * Define a constant if it is not already defined.
 *
 * @since 3.0.3
 * @param string $name  Constant name.
 * @param string $value Value.
 */
function disabler_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Runs a deprecated action with notice only if used.
 *
 * @since  3.0.0
 * @param  string $action
 * @param  array $args
 * @param  string $deprecated_in
 * @param  string $replacement
 */
function disabler_do_deprecated_action( $action, $args, $deprecated_in, $replacement ) {
	if ( has_action( $action ) ) {
		disabler_deprecated_function( 'Action: ' . $action, $deprecated_in, $replacement );
		do_action_ref_array( $action, $args );
	}
}

/**
 * Wrapper for deprecated functions so we can apply some extra logic.
 *
 * @since  3.0.0
 * @param  string $function
 * @param  string $version
 * @param  string $replacement
 */
function disabler_deprecated_function( $function, $version, $replacement = null ) {
	if ( disabler_is_ajax() ) {
		do_action( 'deprecated_function_run', $function, $replacement, $version );
		$log_string  = "The {$function} function is deprecated since version {$version}.";
		$log_string .= $replacement ? " Replace with {$replacement}." : '';
		error_log( $log_string );
	} else {
		_deprecated_function( $function, $version, $replacement );
	}
}

/**
 * Wrapper for disabler_doing_it_wrong.
 *
 * @param  string $function
 * @param  string $version
 * @param  string $replacement
 */
function disabler_doing_it_wrong( $function, $message, $version ) {
	if ( disabler_is_ajax() ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
}

/**
 * disabler_is_ajax - Returns true when the page is loaded via ajax.
 *
 * @return bool
 */
function disabler_is_ajax() {
	return defined( 'DOING_AJAX' );
}
