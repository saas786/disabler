<?php

namespace HBP\Disabler\Tools\Update;

use HBP\Disabler\Admin\Options;
use Hybrid\Log\Facades\Log;

function update_4_0_5_options() {
    $options = get_option( 'hbp_disabler_settings' );

    if ( ! $options ) {
        Log::info(
            'No old Options found',
            [ 'source' => __NAMESPACE__ . '\update_4_0_5_options' ]
        );

        return;
    }

    Log::info(
        sprintf( 'Old Plugin Options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_5_options' ]
    );

    // Map of deprecated setting keys to their new replacements.
    $keys_map = [
        'editor_disable_wp_img_tag_add_auto_sizes' => 'media_disable_wp_img_tag_add_auto_sizes',
        'editor_disable_wp_img_auto_sizes_contain' => 'media_disable_wp_img_auto_sizes_contain',
    ];

    foreach ( $keys_map as $old_key => $new_key ) {
        if ( array_key_exists( $old_key, $options ) ) {
            $options[ $new_key ] = $options[ $old_key ];

            unset( $options[ $old_key ] );
        }
    }

    // Some installs may have saved `null` (or otherwise missing) values for
    // fields that were never actually filled in. Backfill those with the
    // current default values, so `Options::get()` doesn't return `null`
    // for keys that are expected to always resolve to a sane value.
    $defaults = Options::defaults();

    foreach ( $defaults as $key => $default_value ) {
        if ( ! array_key_exists( $key, $options ) || is_null( $options[ $key ] ) ) {
            $options[ $key ] = $default_value;
        }
    }

    Log::info(
        sprintf( 'New plugin options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_5_options' ]
    );

    update_option( 'hbp_disabler_settings', $options );
}

function update_4_0_5_db_version() {
    PluginInstall::update_db_version( '4.0.5' );
}
