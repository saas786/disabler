<?php

namespace HBP\Disabler;

/*
 * Sanity check.
 *
 * Check that the site meets the minimum requirements for the plugin.
 */
if ( version_compare( $GLOBALS['wp_version'], '6.0.0', '<' ) || version_compare( PHP_VERSION, '8.0', '<' ) ) {
    require_once dirname( DISABLER_FILE ) . '/inc/bootstrap-compat.php';

    return;
}

require_once dirname( DISABLER_FILE ) . '/inc/bootstrap-autoload.php';
require_once dirname( DISABLER_FILE ) . '/inc/bootstrap-plugin.php';
