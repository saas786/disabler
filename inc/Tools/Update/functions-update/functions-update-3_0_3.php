<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_3_0_3_options() {
    $options     = get_option( 'disabler_options' );
    $new_options = [];

    Log::info(
        sprintf( 'Old Plugin Options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_0_3_options' ]
    );

    $new_options['autop_disabled']         = absint( $options['disabler_autop'] ) === 1;
    $new_options['hide_wp_version']        = absint( $options['disabler_version'] ) === 1;
    $new_options['xmlrpc_disabled']        = absint( $options['disabler_xmlrpc'] ) === 1;
    $new_options['autosave_disabled']      = absint( $options['disabler_autosave'] ) === 1;
    $new_options['selfping_disabled']      = absint( $options['disabler_selfping'] ) === 1;
    $new_options['rss_feed_disabled']      = absint( $options['disabler_norss'] ) === 1;
    $new_options['capital_p_disabled']     = absint( $options['disabler_capitalp'] ) === 1;
    $new_options['revisions_disabled']     = absint( $options['disabler_revisions'] ) === 1;
    $new_options['fake_user_agent_value']  = absint( $options['disabler_nourl'] ) === 1;
    $new_options['texturization_disabled'] = absint( $options['disabler_smartquotes'] ) === 1;

    Log::info(
        sprintf( 'New Plugin Options: %s', print_r( $new_options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_0_3_options' ]
    );

    update_option( 'disabler_settings', $new_options );

    delete_option( 'disabler_options' );
    delete_option( 'disabler_plugin_version' );
}

function update_3_0_3_db_version() {
    PluginInstall::update_db_version( '3.0.3' );
}
