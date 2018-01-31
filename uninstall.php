<?php

// This is the uninstall script.
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// clean up..
delete_option( 'disabler_options' );
delete_option( 'disabler_plugin_version' );
