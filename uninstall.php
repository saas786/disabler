<?php

// If uninstall.php is not called by WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete transient data.
delete_transient( '_hbp_disabler_activation_redirect' );

// Delete plugin options.
$options_to_delete = [
    'hbp_disabler_admin_install_timestamp',
    'hbp_disabler_admin_notices',
    'hbp_disabler_db_version',
    'hbp_disabler_newly_installed',
    'hbp_disabler_version',
    'hbp_disabler_usage_tracker_last_request',
    'hbp_disabler_settings',
];
foreach ( $options_to_delete as $option ) {
    delete_option( $option );
}

// Clear scheduled hooks.
wp_clear_scheduled_hook( 'hbp_disabler_cleanup_logs' );

// Unschedule all ActionScheduler jobs for Disabler.
if ( function_exists( 'as_unschedule_all_actions' ) ) {
    as_unschedule_all_actions( null, null, 'hbp-disabler-db-updates' );
}
