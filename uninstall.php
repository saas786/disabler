<?php

// Make sure we're actually uninstalling the plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	wp_die( sprintf( __( '%s should only be called when uninstalling the plugin.', 'disabler' ), '<code>' . __FILE__ . '</code>' ) );
}

// clean up..
delete_option( 'disabler_settings' );
delete_option( 'disabler_version' );
delete_option( 'disabler_db_version' );
delete_option( 'disabler_admin_notices' );

wp_clear_scheduled_hook( 'disabler_tracker_send_event' );