<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_3_0_0_options() {
    $options['disabler_autop']     = get_option( 'disabler_autop' );
    $options['disabler_version']   = get_option( 'disabler_version' );
    $options['disabler_xmlrpc']    = get_option( 'disabler_xmlrpc' );
    $options['disabler_autosave']  = get_option( 'disabler_autosave' );
    $options['disabler_selfping']  = get_option( 'disabler_selfping' );
    $options['disabler_norss']     = get_option( 'disabler_norss' );
    $options['disabler_capitalp']  = get_option( 'disabler_capitalp' );
    $options['disabler_revisions'] = get_option( 'disabler_revisions' );

    $options['disabler_nourl'] = 0;

    if ( ! empty( get_option( 'disabler_nourl' ) ) ) {
        $options['disabler_nourl'] = get_option( 'disabler_nourl' );
    }

    if ( empty( $options['disabler_nourl'] ) && ! empty( get_option( 'new_version' ) ) ) {
        $options['disabler_nourl'] = get_option( 'new_version' );
    }

    $options['disabler_smartquotes'] = get_option( 'disabler_smartquotes' );
    $new_options                     = [];

    Log::info(
        sprintf( 'Old Plugin Options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_0_0_options' ]
    );

    $new_options['disabler_autop']       = absint( $options['disabler_autop'] ) === 1 ? '1' : '0';
    $new_options['disabler_version']     = absint( $options['disabler_version'] ) === 1 ? '1' : '0';
    $new_options['disabler_xmlrpc']      = absint( $options['disabler_xmlrpc'] ) === 1 ? '1' : '0';
    $new_options['disabler_autosave']    = absint( $options['disabler_autosave'] ) === 1 ? '1' : '0';
    $new_options['disabler_selfping']    = absint( $options['disabler_selfping'] ) === 1 ? '1' : '0';
    $new_options['disabler_norss']       = absint( $options['disabler_norss'] ) === 1 ? '1' : '0';
    $new_options['disabler_capitalp']    = absint( $options['disabler_capitalp'] ) === 1 ? '1' : '0';
    $new_options['disabler_revisions']   = absint( $options['disabler_revisions'] ) === 1 ? '1' : '0';
    $new_options['disabler_nourl']       = absint( $options['disabler_nourl'] ) === 1 ? '1' : '0';
    $new_options['disabler_smartquotes'] = absint( $options['disabler_smartquotes'] ) === 1 ? '1' : '0';

    Log::info(
        sprintf( 'New Plugin Options: %s', print_r( $new_options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_0_0_options' ]
    );

    update_option( 'disabler_options', $new_options );

    delete_option( 'disabler_version' );

    delete_option( 'disabler_autop' );
    delete_option( 'disabler_version' );
    delete_option( 'disabler_xmlrpc' );
    delete_option( 'disabler_autosave' );
    delete_option( 'disabler_selfping' );
    delete_option( 'disabler_norss' );
    delete_option( 'disabler_capitalp' );
    delete_option( 'disabler_revisions' );
    delete_option( 'disabler_nourl' );
    delete_option( 'disabler_smartquotes' );
}

function update_3_0_0_db_version() {
    PluginInstall::update_db_version( '3.0.0' );
}
