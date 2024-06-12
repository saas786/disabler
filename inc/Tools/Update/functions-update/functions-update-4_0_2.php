<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_4_0_2_options() {
    $options = get_option( 'hbp_disabler_settings' );

    if ( ! $options ) {
        Log::info(
            'No old Options found',
            [ 'source' => __NAMESPACE__ . '\update_4_0_2_options' ]
        );

        return;
    }

    Log::info(
        sprintf( 'Old Plugin Options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_2_options' ]
    );

    $new_options = [];

    if ( array_key_exists( 'backend_disable_autosave', $options ) ) {
        $new_options['editor_disable_autosave'] = absint( $options['backend_disable_autosave'] ) === 1 ? 'yes' : 'no';
    }

    if ( array_key_exists( 'frontend_disable_texturization', $options ) ) {
        $new_options['editor_disable_texturization'] = absint( $options['frontend_disable_texturization'] ) === 1
            ? 1
            : 0;
    }

    if ( array_key_exists( 'frontend_disable_capital_p', $options ) ) {
        $new_options['editor_disable_capital_p'] = absint( $options['frontend_disable_capital_p'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'frontend_disable_autop', $options ) ) {
        $new_options['editor_disable_autop'] = absint( $options['frontend_disable_autop'] ) === 1 ? 1 : 0;
    }

    Log::info(
        sprintf( 'New plugin options: %s', print_r( $new_options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_2_options' ]
    );

    update_option( 'hbp_disabler_settings', $new_options );
}

function update_4_0_2_db_version() {
    PluginInstall::update_db_version( '4.0.2' );
}
