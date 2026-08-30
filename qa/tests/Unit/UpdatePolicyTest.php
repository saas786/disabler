<?php

/**
 * The update policy, as a table.
 *
 * No WordPress. The point of pulling the decision out of the callbacks is
 * that it can be checked without booting anything, over every combination
 * rather than the handful an integration test can afford.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\UpdatePolicy;
use HBP\Disabler\Optimize\Verdict;

/**
 * A policy built from a settings array rather than the option store.
 *
 * fromSettings() reads through setting(), which needs a container. Everything
 * worth testing here is the resolution, so the test drives the same mapping
 * with a stubbed reader.
 */
function policy( array $stored ): UpdatePolicy {
    return UpdatePolicy::fromArray( $stored + [
        'disable_updates'             => 'no',
        'core_updates'                => 'allow_minor_core_auto_updates',
        'plugin_updates'              => 'manual',
        'theme_updates'               => 'manual',
        'translation_updates'         => 'default',
        'enable_update_vcs'           => 'default',
        'updates_nags_only_for_admin' => 0,
    ] );
}

it( 'has no opinion when the plugin is off', function (): void {
    $policy = policy( [ 'disable_updates' => 'no' ] );

    // Inherit, not Auto. A plugin that is switched off must return the value
    // WordPress passed in -- returning a decision would mean this plugin
    // silently overrides every other plugin's filter while disabled.
    expect( $policy->core )->toBe( Verdict::Inherit )
        ->and( $policy->plugins )->toBe( Verdict::Inherit )
        ->and( $policy->plugins->autoUpdate( true ) )->toBeTrue()
        ->and( $policy->plugins->autoUpdate( false ) )->toBeFalse();
} );

it( 'switches everything off in all mode, whatever the per-kind settings say', function (): void {
    $policy = policy( [
        'disable_updates' => 'all',
        // Deliberately contradictory: 'all' must win over these.
        'plugin_updates'  => 'auto',
        'theme_updates'   => 'auto',
        'core_updates'    => 'allow_major_core_auto_updates',
    ] );

    expect( $policy->everything )->toBeTrue()
        ->and( $policy->core )->toBe( Verdict::Off )
        ->and( $policy->plugins )->toBe( Verdict::Off )
        ->and( $policy->themes )->toBe( Verdict::Off )
        ->and( $policy->translations )->toBe( Verdict::Off )
        ->and( $policy->plugins->autoUpdate( true ) )->toBeFalse();
} );

it( 'distinguishes manual from disabled', function (): void {
    $manual   = policy( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'manual',
    ] );
    $disabled = policy( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'disable',
    ] );

    // Both stop automatic updates ...
    expect( $manual->plugins->autoUpdate( true ) )->toBeFalse()
        ->and( $disabled->plugins->autoUpdate( true ) )->toBeFalse();

    // ... and differ in whether the update button survives, which is the
    // distinction the old duplicated callbacks kept losing.
    expect( $manual->plugins->isOff() )->toBeFalse()
        ->and( $disabled->plugins->isOff() )->toBeTrue()
        ->and( $manual->strippedBulkActions( $manual->plugins ) )
        ->not->toContain( 'update' )
        ->and( $disabled->strippedBulkActions( $disabled->plugins ) )
        ->toContain( 'update' );
} );

it( 'allows core auto-updates only at the level the setting names', function (
    string $stored,
    string $allowed
): void {
    $policy = policy( [
        'disable_updates' => 'selective',
        'core_updates'    => $stored,
    ] );

    foreach ( [ 'minor', 'major', 'dev' ] as $level ) {
        expect( $policy->allowsCoreAuto( $level, false ) )->toBe( $level === $allowed );
    }
} )->with( [
    [ 'allow_minor_core_auto_updates', 'minor' ],
    [ 'allow_major_core_auto_updates', 'major' ],
    [ 'allow_dev_core_auto_updates', 'dev' ],
] );

it( 'never allows core auto-updates when core updates are off or manual', function ( string $stored ): void {
    $policy = policy( [
        'disable_updates' => 'selective',
        'core_updates'    => $stored,
    ] );

    foreach ( [ 'minor', 'major', 'dev' ] as $level ) {
        // Passing true as the inherited value: an Off or Manual verdict must
        // override what WordPress wanted, not fall through to it.
        expect( $policy->allowsCoreAuto( $level, true ) )->toBeFalse();
    }
} )->with( [ 'disable_core_updates', 'disable_core_auto_updates' ] );

it( 'claims a VCS checkout when core updates are off or the user asked', function (): void {
    expect( policy( [
        'disable_updates' => 'selective',
        'core_updates'    => 'disable_core_updates',
    ] )->vcs )
        ->toBeTrue()
        ->and( policy( [
            'disable_updates'   => 'selective',
            'enable_update_vcs' => 'enable',
        ] )->vcs )
        ->toBeTrue()
        ->and( policy( [
            'disable_updates'   => 'selective',
            'enable_update_vcs' => 'disable',
        ] )->vcs )
        ->toBeFalse();
} );

it( 'leaves the VCS answer to others when the user expressed no preference', function (): void {
    // Null, not false. `automatic_updates_is_vcs_checkout` is answered by
    // hosts and by core's own .git detection, and a site genuinely under
    // version control must keep saying so. Returning false here would
    // silently re-enable updates on exactly the installs the filter exists
    // to protect.
    expect( policy( [ 'disable_updates' => 'selective' ] )->vcs )->toBeNull()
        ->and( policy( [ 'disable_updates' => 'no' ] )->vcs )->toBeNull();
} );

it( 'resolves every combination without throwing', function (): void {
    // 3 x 5 x 4 x 4 x 4 = 960 states. The old code spread this across thirty
    // callbacks, so no combination was ever exercised as a whole.
    $modes    = [ 'no', 'all', 'selective' ];
    $cores    = [ 'disable_core_updates', 'disable_core_auto_updates', 'allow_minor_core_auto_updates', 'allow_major_core_auto_updates', 'allow_dev_core_auto_updates' ];
    $kinds    = [ 'disable', 'manual', 'auto', 'default' ];
    $resolved = 0;

    foreach ( $modes as $mode ) {
        foreach ( $cores as $core ) {
            foreach ( $kinds as $plugin ) {
                foreach ( $kinds as $theme ) {
                    foreach ( $kinds as $translation ) {
                        $policy = policy( [
                            'disable_updates'     => $mode,
                            'core_updates'        => $core,
                            'plugin_updates'      => $plugin,
                            'theme_updates'       => $theme,
                            'translation_updates' => $translation,
                        ] );

                        // An off plugin must never force a decision.
                        if ( 'no' === $mode ) {
                            expect( $policy->plugins )->toBe( Verdict::Inherit );
                        }

                        $resolved++;
                    }
                }
            }
        }
    }

    expect( $resolved )->toBe( 960 );
} );

it( 'vetoes core auto-updates without ever forcing them', function (): void {
    $off    = policy( [
        'disable_updates' => 'selective',
        'core_updates'    => 'disable_core_updates',
    ] );
    $auto   = policy( [
        'disable_updates' => 'selective',
        'core_updates'    => 'allow_major_core_auto_updates',
    ] );
    $absent = policy( [ 'disable_updates' => 'no' ] );

    // auto_update_core is a veto. The three release-level filters decide
    // whether core updates; this one may only refuse. An Auto verdict must
    // therefore pass the inherited value through rather than return true,
    // or it overrules a level filter that already said no.
    expect( $off->core->blocksAuto() )->toBeTrue()
        ->and( $auto->core->blocksAuto() )->toBeFalse()
        ->and( $absent->core->blocksAuto() )->toBeFalse();
} );

it( 'refuses core auto-updates for a core setting it cannot read', function ( mixed $stored ): void {
    // The screen offers five choices and coreVerdict maps all five, so this
    // is a preset or a hand-edited row rather than anything a user can click.
    // It still has to fail closed: inheriting would hand the decision back to
    // WordPress, which allows minor auto-updates by default, and turn core
    // updates on for a site that installed this plugin to keep them off.
    $policy = policy( [
        'disable_updates' => 'selective',
        'core_updates'    => $stored,
    ] );

    foreach ( [ 'minor', 'major', 'dev' ] as $level ) {
        // Passing true is the point -- it is what WordPress would have done
        // unopposed, and the assertion is that this refuses anyway.
        expect( $policy->allowsCoreAuto( $level, true ) )->toBeFalse();
    }
} )->with( [ 'a_choice_from_a_later_version', '', null ] );

it( 'defers to WordPress on core auto-updates when updates are unmanaged', function (): void {
    // The other meaning of Inherit, and the reason the branch above cannot
    // just return false. With disable_updates at 'no' this plugin has no
    // opinion at all, so whatever WordPress passed in has to survive.
    $policy = policy( [ 'disable_updates' => 'no' ] );

    foreach ( [ 'minor', 'major', 'dev' ] as $level ) {
        expect( $policy->allowsCoreAuto( $level, true ) )->toBeTrue()
            ->and( $policy->allowsCoreAuto( $level, false ) )->toBeFalse();
    }
} );
