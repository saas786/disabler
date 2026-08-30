<?php

namespace HBP\Disabler\Tools\Update;

use HBP\Disabler\Plugin;
use Hybrid\Log\Facades\Log;

function update_4_0_5_options() {
    $options = get_option( Plugin::SETTINGS_OPTION );

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

    $options = backfill_defaults( flatten_settings( $options ) );

    Log::info(
        sprintf( 'New plugin options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_5_options' ]
    );

    update_option( Plugin::SETTINGS_OPTION, $options );
}

/**
 * Fill in settings the stored option has no value for.
 *
 * Some installs saved `null`, or nothing at all, for fields that were never
 * filled in. Backfilling those means a read never returns `null` for a key
 * expected to resolve to something sane.
 *
 * This runs AFTER the conversion, on nested keys, and that ordering is the
 * whole point. Backfilling first, on flat keys, is correct exactly once: on
 * the second run the option is already nested, every flat default looks
 * absent, all of them are re-added at the top level, and the conversion then
 * files them over the real values -- resetting the site's entire
 * configuration to defaults. The update chain queues its callbacks and
 * re-runs a half-finished one, so "exactly once" is not something this can
 * assume.
 *
 * The defaults are frozen at 4.0.5 rather than read live. This upgrades an
 * option FROM 4.0.5, so it must backfill with what 4.0.5 shipped -- live
 * defaults would seed an old install with values invented later, and the flat
 * defaults file no longer exists once the old screen is removed. They are
 * declared flat, so they go through the same conversion as the option.
 *
 * @param array<string, mixed> $options
 *
 * @return array<string, mixed>
 */
function backfill_defaults( array $options ): array {
    $defaults = ( new FlattenedSettings )->convert( require __DIR__ . '/defaults-4_0_5.php' );

    // Key by key rather than a recursive merge. Several settings store lists,
    // and array_replace_recursive merges those element by element: a stored
    // empty list would come back holding the default's first element.
    foreach ( $defaults as $section => $values ) {
        if ( ! is_array( $values ) ) {
            if ( ! array_key_exists( $section, $options ) || is_null( $options[ $section ] ) ) {
                $options[ $section ] = $values;
            }

            continue;
        }

        foreach ( $values as $key => $value ) {
            if ( ! array_key_exists( $key, $options[ $section ] ?? [] ) || is_null( $options[ $section ][ $key ] ) ) {
                $options[ $section ][ $key ] = $value;
            }
        }
    }

    return $options;
}

function update_4_0_5_db_version() {
    PluginInstall::update_db_version( '4.0.5' );
}

/**
 * The stored option in the dotted, nested shape HBP\Settings reads.
 *
 * 4.0.2 and earlier stored one flat key per control, prefixed with its
 * section: `backend_disable_self_ping`. Settings are addressed by dotted key
 * and stored nested, so the same setting now lives at
 * `backend.disable_self_ping`.
 *
 * This runs after the key renames, which still speak flat keys, and before
 * the backfill, which must see nested ones. That second half is the load-
 * bearing one: backfilling first would re-add every flat default on a second
 * run and this would then file them over the real values. See
 * backfill_defaults().
 *
 * @param array<string, mixed> $options
 *
 * @return array<string, mixed>
 */
function flatten_settings( array $options ): array {
    $converted = ( new FlattenedSettings )->convert( $options );

    // Key names and counts, not values. A settings dump names every choice
    // the site has made about its own hardening -- which endpoints answer,
    // which roles keep the admin bar, which IPs reach XML-RPC -- and the
    // debug log is world-readable on plenty of hosts. The names alone are
    // what a failed conversion is actually diagnosed from: a key the rule
    // did not recognise stays at the top level instead of nesting under a
    // section, so listing those says what went wrong without saying what
    // the site is set to.
    $unconverted = array_keys( array_filter(
        $converted,
        static fn( $value ): bool => ! is_array( $value )
    ) );

    Log::info(
        sprintf(
            'Converted %d stored keys into %d sections',
            count( $options ),
            count( $converted ) - count( $unconverted )
        ),
        [ 'source' => __NAMESPACE__ . '\flatten_settings' ]
    );

    if ( [] !== $unconverted ) {
        Log::warning(
            sprintf( 'Left unconverted, no section matched: %s', implode( ', ', $unconverted ) ),
            [ 'source' => __NAMESPACE__ . '\flatten_settings' ]
        );
    }

    return $converted;
}
