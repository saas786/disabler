<?php

/**
 * The read and write paths against the real option.
 *
 * Both bugs these cover were invisible in normal use. The read path answered
 * every key, just from the config tier instead of the store; the write path
 * saved a value the screen then displayed back correctly. Nothing looked
 * wrong until a setting stopped taking effect.
 */

declare(strict_types = 1);

use HBP\Disabler\Plugin;
use HBP\Settings\SettingsFactory;
use HBP\Settings\Ui\PanelFactory;
use function HBP\Disabler\container;
use function HBP\Disabler\setting;

/**
 * The panel the settings screen uses.
 */
function panel(): HBP\Settings\Ui\Panel {
    return container()->resolve( PanelFactory::class )
        ->make( 'disabler', Plugin::SETTINGS_OPTION );
}

it( 'reads the option the settings screen writes', function (): void {
    update_option( Plugin::SETTINGS_OPTION, [ 'backend' => [ 'disable_self_ping' => 1 ] ] );

    expect( setting( 'backend.disable_self_ping' ) )->toBe( 1 );
} );

it( 'does not read the option name derived from the namespace', function (): void {
    // Left to default, the settings factory derives `disabler_settings` from
    // the namespace. That is the pre-4.0 option, which the 4.0.0-RC.2 routine
    // deletes -- so every read would miss and answer from config, and this
    // plugin's config defaults mostly mean "not disabled".
    //
    // Plant a value there that differs from the declared default, then assert
    // the default is what comes back. A caller default cannot be used as the
    // sentinel here: config sits above it in the resolution order, so a key
    // with a declared default never reaches the caller's -- and every control
    // has one, which the unit suite asserts.
    $default = ( require plugin_path( 'config/backend.php' ) )['disable_self_ping'];

    expect( $default )->not->toBe( 1 );

    delete_option( Plugin::SETTINGS_OPTION );
    update_option( 'disabler_settings', [ 'backend' => [ 'disable_self_ping' => 1 ] ] );

    expect( setting( 'backend.disable_self_ping' ) )->toBe( $default );
} );

it( 'falls through to the declared default when nothing is stored', function (): void {
    delete_option( Plugin::SETTINGS_OPTION );

    $defaults = require plugin_path( 'config/backend.php' );

    expect( setting( 'backend.disable_self_ping' ) )->toBe( $defaults['disable_self_ping'] );
} );

it( 'saves a submission nested, not as a literal dotted key', function (): void {
    $stored = panel()->sanitize( [ 'backend.disable_self_ping' => '1' ] );

    expect( $stored )->toHaveKey( 'backend' )
        ->and( $stored['backend']['disable_self_ping'] )->toBeTrue()
        ->and( array_keys( $stored ) )->not->toContain( 'backend.disable_self_ping' );
} );

it( 'leaves a key absent from the submission exactly as stored', function (): void {
    // One option backs every tab, and a tab posts only its own controls.
    // A save that treated absent as empty would wipe the other tabs.
    update_option( Plugin::SETTINGS_OPTION, [
        'backend' => [ 'disable_self_ping' => 1 ],
        'xmlrpc'  => [ 'disable_xmlrpc' => 'selective' ],
    ] );

    $stored = panel()->sanitize( [ 'backend.disable_self_ping' => '0' ] );

    expect( $stored['xmlrpc']['disable_xmlrpc'] )->toBe( 'selective' );
} );

it( 'ignores a posted key that is not a declared control', function (): void {
    $stored = panel()->sanitize( [ 'backend.disable_self_ping' => '1', 'evil' => 'x' ] );

    expect( $stored )->not->toHaveKey( 'evil' );
} );

it( 'does not let a saved value shadow a later programmatic write', function (): void {
    // The failure this pins: a save that writes a literal `backend.x` key
    // sits in front of the nested one, because Arr::get checks the flat key
    // first. Settings::set() then writes the nested copy and the read never
    // sees it -- a write that returns true and changes nothing.
    update_option( Plugin::SETTINGS_OPTION, panel()->sanitize( [ 'backend.disable_self_ping' => '1' ] ) );

    $settings = container()->resolve( SettingsFactory::class )
        ->make( 'disabler', Plugin::SETTINGS_OPTION );
    $settings->flush();
    $settings->set( 'backend.disable_self_ping', false );

    expect( setting( 'backend.disable_self_ping' ) )->toBeFalse();
} );
