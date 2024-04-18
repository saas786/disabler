<?php
/**
 * WP and PHP compatibility.
 */

namespace HBP\Disabler;

/**
 * Outputs an admin notice with the compatibility issue.
 *
 * @return void
 */
add_action( 'admin_notices', static function () {
    echo '<div class="error"><p>';

    if ( version_compare( $GLOBALS['wp_version'], '6.0.0', '<' ) ) {
        /* Translators: %s: Minimum required version. */
        printf( esc_html__( 'Disabler requires WordPress %s or later to function properly. Please upgrade WordPress before activating disabler.', 'hbp-disabler' ), '6.0.0' );
    }

    if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
        /* Translators: 1 is the required PHP version and 2 is the user's current version. */
        printf( esc_html__( 'Disabler requires at least PHP version %1$s. You are running version %2$s. Please upgrade and try again.', 'hbp-disabler' ), '8.0', PHP_VERSION );
    }

    echo '</p></div>';
} );
