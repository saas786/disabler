<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_3_0_0_options() {
    $options = get_option( 'disabler_options' );

    if ( ! $options ) {
        Log::info(
            'Bail early, as newer settings detected.',
            [ 'source' => __NAMESPACE__ . '\update_3_0_0_options' ]
        );

        return;
    }

    $options = [];

    if ( ! is_null( get_option( 'disabler_autop', null ) ) ) {
        $options['disabler_autop'] = get_option( 'disabler_autop' );
    }

    if ( ! is_null( get_option( 'disabler_version', null ) ) ) {
        $options['disabler_version'] = get_option( 'disabler_version' );
    }

    if ( ! is_null( get_option( 'disabler_xmlrpc', null ) ) ) {
        $options['disabler_xmlrpc'] = get_option( 'disabler_xmlrpc' );
    }

    if ( ! is_null( get_option( 'disabler_autosave', null ) ) ) {
        $options['disabler_autosave'] = get_option( 'disabler_autosave' );
    }

    if ( ! is_null( get_option( 'disabler_selfping', null ) ) ) {
        $options['disabler_selfping'] = get_option( 'disabler_selfping' );
    }

    if ( ! is_null( get_option( 'disabler_norss', null ) ) ) {
        $options['disabler_norss'] = get_option( 'disabler_norss' );
    }

    if ( ! is_null( get_option( 'disabler_capitalp', null ) ) ) {
        $options['disabler_capitalp'] = get_option( 'disabler_capitalp' );
    }

    if ( ! is_null( get_option( 'disabler_revisions', null ) ) ) {
        $options['disabler_revisions'] = get_option( 'disabler_revisions' );
    }

    if ( ! is_null( get_option( 'disabler_nourl', null ) ) ) {
        $options['disabler_nourl'] = get_option( 'disabler_nourl' );
    }

    if ( array_key_exists( 'disabler_nourl', $options ) && ! is_null( get_option( 'new_version', null ) ) ) {
        $options['disabler_nourl'] = get_option( 'new_version' );
    }

    if ( ! is_null( get_option( 'disabler_smartquotes', null ) ) ) {
        $options['disabler_smartquotes'] = get_option( 'disabler_smartquotes' );
    }

    // $options['disabler_nourl'] = 0;

    if ( ! $options ) {
        Log::info(
            'No old options found',
            [ 'source' => __NAMESPACE__ . '\update_3_0_0_options' ]
        );

        return;
    }

    Log::info(
        sprintf( 'Old plugin options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_0_0_options' ]
    );

    $new_options                         = [];
    $new_options['disabler_autop']       = array_key_exists( 'disabler_autop', $options ) && absint( $options['disabler_autop'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_version']     = array_key_exists( 'disabler_version', $options ) && absint( $options['disabler_version'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_xmlrpc']      = array_key_exists( 'disabler_xmlrpc', $options ) && absint( $options['disabler_xmlrpc'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_autosave']    = array_key_exists( 'disabler_autosave', $options ) && absint( $options['disabler_autosave'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_selfping']    = array_key_exists( 'disabler_selfping', $options ) && absint( $options['disabler_selfping'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_norss']       = array_key_exists( 'disabler_norss', $options ) && absint( $options['disabler_norss'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_capitalp']    = array_key_exists( 'disabler_capitalp', $options ) && absint( $options['disabler_capitalp'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_revisions']   = array_key_exists( 'disabler_revisions', $options ) && absint( $options['disabler_revisions'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_nourl']       = array_key_exists( 'disabler_nourl', $options ) && absint( $options['disabler_nourl'] ) === 1
        ? 1
        : 0;
    $new_options['disabler_smartquotes'] = array_key_exists( 'disabler_smartquotes', $options ) && absint( $options['disabler_smartquotes'] ) === 1
        ? 1
        : 0;

    Log::info(
        sprintf( 'New plugin options: %s', print_r( $new_options, true ) ),
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
