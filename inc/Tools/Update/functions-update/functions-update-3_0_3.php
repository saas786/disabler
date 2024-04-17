<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_3_0_3_options() {
    $options = get_option( 'disabler_settings' );

    if ( ! $options ) {
        Log::info(
            'Bail early, as newer settings detected',
            [ 'source' => __NAMESPACE__ . '\update_3_0_3_options' ]
        );

        return;
    }

    $options = get_option( 'disabler_options' );

    if ( ! $options ) {
        Log::info(
            'No old options found',
            [ 'source' => __NAMESPACE__ . '\update_3_0_3_options' ]
        );

        return;
    }

    Log::info(
        sprintf( 'Old plugin options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_0_3_options' ]
    );

    $new_options                           = [];
    $new_options['autop_disabled']         = array_key_exists( 'disabler_autop', $options ) && absint( $options['disabler_autop'] ) === 1
        ? 1
        : 0;
    $new_options['hide_wp_version']        = array_key_exists( 'disabler_version', $options ) && absint( $options['disabler_version'] ) === 1
        ? 1
        : 0;
    $new_options['xmlrpc_disabled']        = array_key_exists( 'disabler_xmlrpc', $options ) && absint( $options['disabler_xmlrpc'] ) === 1
        ? 1
        : 0;
    $new_options['autosave_disabled']      = array_key_exists( 'disabler_autosave', $options ) && absint( $options['disabler_autosave'] ) === 1
        ? 1
        : 0;
    $new_options['selfping_disabled']      = array_key_exists( 'disabler_selfping', $options ) && absint( $options['disabler_selfping'] ) === 1
        ? 1
        : 0;
    $new_options['rss_feed_disabled']      = array_key_exists( 'disabler_norss', $options ) && absint( $options['disabler_norss'] ) === 1
        ? 1
        : 0;
    $new_options['capital_p_disabled']     = array_key_exists( 'disabler_capitalp', $options ) && absint( $options['disabler_capitalp'] ) === 1
        ? 1
        : 0;
    $new_options['revisions_disabled']     = array_key_exists( 'disabler_revisions', $options ) && absint( $options['disabler_revisions'] ) === 1
        ? 1
        : 0;
    $new_options['fake_user_agent_value']  = array_key_exists( 'disabler_nourl', $options ) && absint( $options['disabler_nourl'] ) === 1
        ? 1
        : 0;
    $new_options['texturization_disabled'] = array_key_exists( 'disabler_smartquotes', $options ) && absint( $options['disabler_smartquotes'] ) === 1
        ? 1
        : 0;

    Log::info(
        sprintf( 'New plugin options: %s', print_r( $new_options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_0_3_options' ]
    );

    update_option( 'disabler_settings', $new_options );

    delete_option( 'disabler_options' );
    delete_option( 'disabler_plugin_version' );
}
