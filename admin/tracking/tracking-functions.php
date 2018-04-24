<?php

add_action( 'wp_login', 'disabler_maybe_store_user_agent', 10, 2 );

/**
 * Store user agents. Used for tracker.
 *
 * @since 3.0.3
 * @param string     $user_login User login.
 * @param int|object $user       User.
 */
function disabler_maybe_store_user_agent( $user_login, $user ) {
	if ( disabler_allow_usage_tracking() && user_can( $user, 'manage_options' ) ) {
		$admin_user_agents   = array_filter( (array) get_option( 'disabler_tracker_ua', array() ) );
		$admin_user_agents[] = disabler_get_user_agent();
		update_option( 'disabler_tracker_ua', array_unique( $admin_user_agents ) );
	}
}

/**
 * Get user agent string.
 *
 * @since  3.0.3
 * @return string
 */
function disabler_get_user_agent() {
	return isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( disabler_clean( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var Data to sanitize.
 * @return string|array
 */
function disabler_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'disabler_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Notation to numbers.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @param  string $size Size value.
 * @return int
 */
function disabler_let_to_num( $size ) {
	$l   = substr( $size, -1 );
	$ret = substr( $size, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
	}
	
	return $ret;
}