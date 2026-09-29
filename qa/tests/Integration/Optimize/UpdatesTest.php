<?php

/**
 * The updates section.
 *
 * The largest feature in the plugin and the one with the worst failure mode:
 * a control that silently over-reaches here stops a site receiving security
 * updates, and nothing on the screen says so.
 *
 * Every case below goes through a filter WordPress itself consults before
 * acting. None of them triggers an update check, so nothing in this file
 * reaches api.wordpress.org -- worth stating explicitly, because the
 * neighbouring code paths (wp_update_plugins and friends) do, and a test that
 * wandered into one would be making a network call from CI.
 *
 * Three states again -- no, all, selective -- crossed with a per-area setting
 * that only 'selective' reads. The pattern throughout is that 'all' answers
 * without consulting anything else, 'selective' consults its companion, and
 * anything else hands back the value it was given untouched. That last case
 * is the one worth guarding: these filters run on every admin request, and a
 * callback that answered instead of passing through would override decisions
 * core and other plugins had already made.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Updates;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Updates::class );

/**
 * @param array<string, mixed> $values
 */
function boot_updates( array $values ): void {
    $defaults = require plugin_path( 'config/updates.php' );

    store_settings( 'updates', array_merge( $defaults, $values ) );

    boot_feature( Updates::class );
}

it( 'registers nothing when the control is off', function (): void {
    // One early return covers the whole class, so counting a few of its hooks
    // is enough. Counted rather than asked with has_filter(), since core is
    // already on some of these.
    $hooks = [ 'auto_update_plugin', 'auto_update_core', 'update_footer' ];

    $before = array_map( 'hook_callback_count', $hooks );

    boot_updates( [ 'disable_updates' => 'no' ] );

    expect( array_map( 'hook_callback_count', $hooks ) )->toBe( $before );
} );

it( 'refuses plugin auto updates when everything is disabled', function (): void {
    boot_updates( [ 'disable_updates' => 'all' ] );

    expect( apply_filters( 'auto_update_plugin', true, null ) )->toBeFalse();
} );

it( 'refuses plugin auto updates when plugins are set to manual', function (): void {
    // 'manual' means a human presses the button -- the plugin still updates,
    // it just does not do it by itself. Conflating this with 'disable' is the
    // mistake worth guarding against, so both are covered.
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'manual',
    ] );

    expect( apply_filters( 'auto_update_plugin', true, null ) )->toBeFalse();
} );

it( 'forces plugin auto updates on when they are set to auto', function (): void {
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'auto',
    ] );

    expect( apply_filters( 'auto_update_plugin', false, null ) )->toBeTrue();
} );

it( 'leaves the plugin auto update decision alone under an unrelated setting', function (): void {
    // 'default' is not one of the branches, so the callback must hand back
    // what it was given. A filter on this hook that answered anyway would
    // override every other plugin's opinion on every admin request.
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'default',
    ] );

    expect( apply_filters( 'auto_update_plugin', true, null ) )->toBeTrue()
        ->and( apply_filters( 'auto_update_plugin', false, null ) )->toBeFalse();
} );

it( 'turns off the plugin auto update ui when plugins are disabled', function (): void {
    // A second hook with the same logic: this one controls whether the
    // toggles appear on the plugins screen at all.
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'disable',
    ] );

    expect( apply_filters( 'plugins_auto_update_enabled', true ) )->toBeFalse();
} );

it( 'refuses core auto updates when everything is disabled', function (): void {
    boot_updates( [ 'disable_updates' => 'all' ] );

    expect( apply_filters( 'auto_update_core', true, null ) )->toBeFalse();
} );

it( 'refuses core auto updates when core auto updates are off', function (): void {
    boot_updates( [
        'disable_updates' => 'selective',
        'core_updates'    => 'disable_core_auto_updates',
    ] );

    expect( apply_filters( 'auto_update_core', true, null ) )->toBeFalse();
} );

it( 'leaves the core auto update decision alone otherwise', function (): void {
    boot_updates( [
        'disable_updates' => 'selective',
        'core_updates'    => 'allow_minor_core_auto_updates',
    ] );

    expect( apply_filters( 'auto_update_core', true, null ) )->toBeTrue();
} );

it( 'allows minor core updates and only minor ones', function (): void {
    // The three allow_* filters read the same setting and answer differently,
    // which is the whole point of the setting: minor security releases in,
    // major version jumps out. Asserting one without the others would let
    // them collapse into each other.
    boot_updates( [
        'disable_updates' => 'selective',
        'core_updates'    => 'allow_minor_core_auto_updates',
    ] );

    expect( apply_filters( 'allow_minor_auto_core_updates', false ) )->toBeTrue()
        ->and( apply_filters( 'allow_major_auto_core_updates', true ) )->toBeFalse()
        ->and( apply_filters( 'allow_dev_auto_core_updates', true ) )->toBeFalse();
} );

it( 'allows major core updates when that is what was chosen', function (): void {
    boot_updates( [
        'disable_updates' => 'selective',
        'core_updates'    => 'allow_major_core_auto_updates',
    ] );

    expect( apply_filters( 'allow_major_auto_core_updates', false ) )->toBeTrue()
        ->and( apply_filters( 'allow_minor_auto_core_updates', true ) )->toBeFalse();
} );

it( 'refuses every core update tier when everything is disabled', function (): void {
    boot_updates( [ 'disable_updates' => 'all' ] );

    expect( apply_filters( 'allow_minor_auto_core_updates', true ) )->toBeFalse()
        ->and( apply_filters( 'allow_major_auto_core_updates', true ) )->toBeFalse()
        ->and( apply_filters( 'allow_dev_auto_core_updates', true ) )->toBeFalse();
} );

it( 'claims a version control checkout when everything is disabled', function (): void {
    // Reporting a VCS checkout is how core is persuaded to stay out of the
    // filesystem. It is a lie told deliberately, and the setting that tells
    // it needs to be exactly as wide as advertised.
    boot_updates( [ 'disable_updates' => 'all' ] );

    expect( apply_filters( 'automatic_updates_is_vcs_checkout', false, ABSPATH ) )->toBeTrue();
} );

it( 'denies a version control checkout when the user asked it to', function (): void {
    // The opposite answer from the same callback: 'disable' forces false even
    // where core would have detected a real .git directory.
    boot_updates( [
        'disable_updates'   => 'selective',
        'enable_update_vcs' => 'disable',
    ] );

    expect( apply_filters( 'automatic_updates_is_vcs_checkout', true, ABSPATH ) )->toBeFalse();
} );

it( 'leaves the version control answer to core by default', function (): void {
    boot_updates( [
        'disable_updates'   => 'selective',
        'enable_update_vcs' => 'default',
        'core_updates'      => 'allow_minor_core_auto_updates',
    ] );

    expect( apply_filters( 'automatic_updates_is_vcs_checkout', true, ABSPATH ) )->toBeTrue()
        ->and( apply_filters( 'automatic_updates_is_vcs_checkout', false, ABSPATH ) )->toBeFalse();
} );

it( 'empties the admin footer version when everything is disabled', function (): void {
    boot_updates( [ 'disable_updates' => 'all' ] );

    expect( apply_filters( 'update_footer', 'Version 7.1' ) )->toBe( '' );
} );

it( 'leaves the admin footer version alone unless core updates are off', function (): void {
    boot_updates( [
        'disable_updates' => 'selective',
        'core_updates'    => 'allow_minor_core_auto_updates',
    ] );

    expect( apply_filters( 'update_footer', 'Version 7.1' ) )->toBe( 'Version 7.1' );
} );

it( 'strips the update bulk actions from the plugins screen', function (): void {
    boot_updates( [ 'disable_updates' => 'all' ] );

    $actions = apply_filters( 'bulk_actions-plugins', [
        'activate-selected'           => 'Activate',
        'deactivate-selected'         => 'Deactivate',
        'update-selected'             => 'Update',
        'enable-auto-update-selected' => 'Enable auto-updates',
        'delete-selected'             => 'Delete',
    ] );

    // Activate and delete surviving is the load-bearing half: this callback
    // unsets by key, and a wrong list would take the whole plugins screen
    // with it.
    expect( $actions )->not->toHaveKey( 'update-selected' )
        ->and( $actions )->not->toHaveKey( 'enable-auto-update-selected' )
        ->and( $actions )->toHaveKey( 'activate-selected' )
        ->and( $actions )->toHaveKey( 'delete-selected' );
} );

it( 'keeps manual updating available when plugins are only set to manual', function (): void {
    // The distinction the whole 'manual' state exists for: the auto-update
    // toggles go, the update button stays.
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'manual',
    ] );

    $actions = apply_filters( 'bulk_actions-plugins', [
        'update-selected'             => 'Update',
        'enable-auto-update-selected' => 'Enable auto-updates',
    ] );

    expect( $actions )->toHaveKey( 'update-selected' )
        ->and( $actions )->not->toHaveKey( 'enable-auto-update-selected' );
} );

it( 'leaves the bulk actions alone when the plugin setting is unrelated', function (): void {
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'default',
    ] );

    $actions = apply_filters( 'bulk_actions-plugins', [ 'update-selected' => 'Update' ] );

    expect( $actions )->toHaveKey( 'update-selected' );
} );

it( 'reports no plugin updates pending', function (): void {
    // The transient short circuit. Answering here is what stops core going
    // to the network at all, so the shape of the object matters: core reads
    // ->updates and ->last_checked off it directly.
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'disable',
    ] );

    $transient = apply_filters( 'pre_site_transient_update_plugins', false );

    expect( $transient )->toBeObject()
        ->and( $transient->updates )->toBe( [] )
        ->and( $transient->last_checked )->toBeInt();
} );

it( 'empties the stored plugin update list', function (): void {
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'disable',
    ] );

    expect( apply_filters( 'site_transient_update_plugins', (object) [ 'response' => [ 'a/a.php' => 'x' ] ] ) )
        ->toBe( [] );
} );

it( 'leaves the plugin transient alone when plugins are only manual', function (): void {
    // 'manual' must still let core find out that an update exists -- that is
    // the difference between "I will press the button myself" and "do not
    // tell me". Short-circuiting the transient here would hide the update.
    boot_updates( [
        'disable_updates' => 'selective',
        'plugin_updates'  => 'manual',
    ] );

    expect( apply_filters( 'pre_site_transient_update_plugins', false ) )->toBeFalse();
} );

it( 'strips translations out of the core transient', function (): void {
    boot_updates( [
        'disable_updates'     => 'selective',
        'translation_updates' => 'disable',
    ] );

    $transient = apply_filters( 'site_transient_update_core', (object) [
        'translations' => [ 'nl_NL' ],
        'updates'      => [ 'core-update' ],
    ] );

    // 'updates' surviving is the point: this callback shares a transient with
    // core's own update data, and emptying the object would hide a pending
    // WordPress release behind a translation setting.
    expect( $transient->translations )->toBe( [] )
        ->and( $transient->updates )->toBe( [ 'core-update' ] );
} );

it( 'leaves the core transient alone when translations are not disabled', function (): void {
    boot_updates( [
        'disable_updates'     => 'selective',
        'translation_updates' => 'default',
    ] );

    $transient = apply_filters( 'site_transient_update_core', (object) [ 'translations' => [ 'nl_NL' ] ] );

    expect( $transient->translations )->toBe( [ 'nl_NL' ] );
} );
