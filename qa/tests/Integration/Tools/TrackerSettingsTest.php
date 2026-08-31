<?php

/**
 * What the usage tracker actually collects.
 *
 * TrackerTest covers whether anything is sent. This covers what would be in
 * it, which is the other half of the same promise: an opt-in to "usage
 * statistics" is not an opt-in to whatever happens to be in the options row.
 *
 * The collector reports the *effective* value of every declared control --
 * stored value where one exists, default otherwise. That is deliberate and
 * documented in the class: reading only the stored option would under-report
 * every install that never opened the settings screen, making the defaults
 * look unused.
 *
 * Nothing here sends anything. get() builds an array and returns it.
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\UsageTracker\Trackers\Settings;

/**
 * @return array<string, mixed>
 */
function collected_settings(): array {
    return ( new Settings )->get()['disabler'];
}

it( 'reports under a single namespaced key', function (): void {
    // The shape the sender expects. A collector returning a bare array would
    // collide with every other collection registered alongside it.
    expect( ( new Settings )->get() )->toHaveKey( 'disabler' );
} );

it( 'reports a stored value', function (): void {
    store_settings( 'revisions', [ 'disable_revisions' => [ 'all' ] ] );

    expect( collected_settings()['revisions.disable_revisions'] )->toBe( [ 'all' ] );
} );

it( 'reports a default for a control that was never touched', function (): void {
    // The behaviour the class docblock argues for. Without it the data says
    // nobody uses the defaults, because nobody who accepted them appears.
    delete_option( 'hbp_disabler_settings' );

    $collected = collected_settings();

    expect( $collected )->toHaveKey( 'privacy.disable_wp_generator' )
        ->and( $collected['privacy.disable_wp_generator'] )->not->toBeNull();
} );

it( 'covers every declared control', function (): void {
    // Driven off declared_sections(), the same helper ConfigIntegrityTest
    // uses, so a control added later is checked without anyone remembering
    // to add it here -- and the list of non-section config files lives in
    // one place rather than two.
    $collected = collected_settings();

    foreach ( declared_sections() as $section => $controls ) {
        foreach ( array_keys( $controls ) as $key ) {
            expect( $collected )->toHaveKey( $section . '.' . $key );
        }
    }
} );

it( 'collects nothing outside the settings the plugin declares', function (): void {
    // The privacy line. Every key reported is a control key -- no site URL,
    // no user data, nothing carried along from a wider options row.
    update_option( 'hbp_disabler_settings', [
        'revisions_disable_revisions' => [ 'all' ],
        'some_unrelated_key'          => 'should not be reported',
    ] );

    expect( collected_settings() )->not->toHaveKey( 'some_unrelated_key' );

    foreach ( array_keys( collected_settings() ) as $key ) {
        // Every key is section.control, which is the only shape the panel
        // definitions produce.
        expect( $key )->toContain( '.' );
    }
} );
