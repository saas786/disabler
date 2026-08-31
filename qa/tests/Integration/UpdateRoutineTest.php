<?php

/**
 * The 4.0.5 update routine, end to end, against a real option row.
 *
 * The unit suite proves the conversion is correct for the settings config
 * declares. This proves the routine actually runs it, writes it, and that
 * the plugin then reads what it wrote -- three things that have each been
 * broken independently.
 */

declare(strict_types = 1);

use HBP\Disabler\Plugin;
use HBP\Settings\Ui\PanelFactory;
use function HBP\Disabler\container;
use function HBP\Disabler\setting;
use function HBP\Disabler\Tools\Update\update_4_0_5_options;

// The update functions are not autoloaded. PluginInstall includes this file
// only when ActionScheduler fires a callback, so calling one directly -- which
// is the point of this test -- means loading them first.
require_once plugin_path( 'inc/Tools/Update/bootstrap-autoload.php' );

/**
 * Every control key the settings screen declares, including the ones
 * computed at runtime from the registered post types.
 *
 * This is the list the unit suite cannot see, and the reason this test is
 * worth booting WordPress for.
 *
 * @return array<int, string>
 */
function control_keys(): array {
    return array_keys(
        container()->resolve( PanelFactory::class )
            ->make( 'disabler', Plugin::SETTINGS_OPTION )
            ->definitions()
            ->all()
    );
}

it( 'converts a pre-4.0.5 option so every control reads back', function (): void {
    $flat = [];

    foreach ( control_keys() as $key ) {
        // Rebuild the legacy flat name. Only the first dot separated the
        // section from the control, so replace that one and leave any others
        // alone rather than flattening the whole key.
        $flat[ preg_replace( '/\./', '_', $key, 1 ) ] = sentinel( $key );
    }

    update_option( Plugin::SETTINGS_OPTION, $flat );

    update_4_0_5_options();

    foreach ( control_keys() as $key ) {
        // The sentinel value, not the sentinel default, is what makes this
        // meaningful. Config sits above the caller default in the resolution
        // order, so a read that misses the store answers with the declared
        // default and never reaches '__FELL_THROUGH__' -- it only shows up for
        // a key config has no default for, such as a dynamic revisions limit.
        // Either way the value differs from what was stored, and this fails.
        expect( setting( $key, '__FELL_THROUGH__' ) )->toBe( sentinel( $key ) );
    }
} );

it( 'renames the two media keys that moved out of the editor section', function (): void {
    update_option( Plugin::SETTINGS_OPTION, [
        'editor_disable_wp_img_tag_add_auto_sizes' => 1,
        'editor_disable_wp_img_auto_sizes_contain' => 1,
    ] );

    update_4_0_5_options();

    expect( setting( 'media.disable_wp_img_tag_add_auto_sizes' ) )->toBe( 1 )
        ->and( setting( 'media.disable_wp_img_auto_sizes_contain' ) )->toBe( 1 )
        ->and( setting( 'editor.disable_wp_img_tag_add_auto_sizes', '__GONE__' ) )->toBe( '__GONE__' );
} );

it( 'leaves the option nested, with no literal dotted keys', function (): void {
    update_option( Plugin::SETTINGS_OPTION, legacy_option() );

    update_4_0_5_options();

    $stored = get_option( Plugin::SETTINGS_OPTION );

    // A literal `section.control` key reads fine today, because Arr::get
    // checks the flat key before splitting on dots -- and then shadows every
    // later write made through the nested path. It has to fail here or it
    // will not fail anywhere.
    $literal = array_filter(
        array_keys( $stored ),
        static fn( $key ): bool => str_contains( (string) $key, '.' )
    );

    expect( $literal )->toBe( [] );
} );

it( 'can be run twice', function (): void {
    update_option( Plugin::SETTINGS_OPTION, legacy_option() );

    update_4_0_5_options();
    $once = get_option( Plugin::SETTINGS_OPTION );

    update_4_0_5_options();

    // The chain queues its callbacks and a half-finished run gets repeated.
    expect( get_option( Plugin::SETTINGS_OPTION ) )->toBe( $once );
} );

it( 'does nothing to a fresh install', function (): void {
    delete_option( Plugin::SETTINGS_OPTION );

    update_4_0_5_options();

    expect( get_option( Plugin::SETTINGS_OPTION, null ) )->toBeNull();
} );
