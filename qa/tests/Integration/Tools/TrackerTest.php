<?php

/**
 * The usage tracker's gate and its post-update scheduling.
 *
 * This is the code that decides whether anything about the site leaves it,
 * so the setting being read correctly is the whole privacy promise. A gate
 * that fails open sends data from sites that opted out, and does it quietly.
 *
 * Nothing here sends anything: the tests exercise tracking_enabled() and the
 * scheduling guard, and never call send().
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\UsageTracker\Tracker;

/**
 * @param array<string, mixed> $values
 */
function boot_tracker( array $values ): Tracker {
    $defaults = require plugin_path( 'config/tracking.php' );

    store_settings( 'tracking', array_merge( $defaults, $values ) );

    // The parent constructor takes an endpoint and a threshold, and the
    // parent's own constructor consults tracking_enabled() -- so the setting
    // has to be stored before the object exists, not after.
    //
    // A test endpoint rather than the production one from
    // PluginServiceProvider. Nothing here calls send(), but a real URL sitting
    // in an object during a test run is one refactor away from being
    // requested, and the tests are about the gate rather than the address.
    return new Tracker( 'https://example.org/track/', MONTH_IN_SECONDS * 3 );
}

it( 'is off when the setting is off', function (): void {
    expect( boot_tracker( [ 'allow_usage_tracking' => 0 ] )->tracking_enabled() )->toBeFalse();
} );

it( 'is on when the setting is on', function (): void {
    expect( boot_tracker( [ 'allow_usage_tracking' => 1 ] )->tracking_enabled() )->toBeTrue();
} );

it( 'registers nothing at all when tracking is off', function (): void {
    // boot() returns before parent::boot(), so the opt-out is not merely a
    // check inside the sender -- nothing is scheduled or hooked in the first
    // place. Counted rather than asked with has_action, since core and other
    // code use upgrader_process_complete too.
    $before = hook_callback_count( 'upgrader_process_complete' );

    boot_tracker( [ 'allow_usage_tracking' => 0 ] )->boot();

    expect( hook_callback_count( 'upgrader_process_complete' ) )->toBe( $before );
} );

it( 'hooks the core update listener when tracking is on', function (): void {
    $tracker = boot_tracker( [ 'allow_usage_tracking' => 1 ] );

    $tracker->boot();

    expect( has_action( 'upgrader_process_complete', [ $tracker, 'schedule_tracking_data_sending' ] ) )->toBe( 10 )
        ->and( has_action( 'hbp_disabler_send_tracking_data_after_core_update', [ $tracker, 'send' ] ) )->toBe( 10 );
} );

it( 'schedules a send after a core update', function (): void {
    $tracker = boot_tracker( [ 'allow_usage_tracking' => 1 ] );

    // The args matter: wp_next_scheduled() only finds the event when given
    // the same arguments it was scheduled with, which is why the production
    // code passes [ true ] to both.
    wp_clear_scheduled_hook( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] );

    $tracker->schedule_tracking_data_sending( true, [ 'type' => 'core' ] );

    expect( wp_next_scheduled( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] ) )
        ->toBeInt();
} );

it( 'ignores a plugin or theme update', function (): void {
    // The guard that keeps this to core updates only. Without it every plugin
    // update on the site would schedule a send.
    $tracker = boot_tracker( [ 'allow_usage_tracking' => 1 ] );

    wp_clear_scheduled_hook( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] );

    $tracker->schedule_tracking_data_sending( true, [ 'type' => 'plugin' ] );

    expect( wp_next_scheduled( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] ) )
        ->toBeFalse();
} );

it( 'ignores a call with no upgrader', function (): void {
    $tracker = boot_tracker( [ 'allow_usage_tracking' => 1 ] );

    wp_clear_scheduled_hook( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] );

    $tracker->schedule_tracking_data_sending( false, [ 'type' => 'core' ] );

    expect( wp_next_scheduled( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] ) )
        ->toBeFalse();
} );

it( 'ignores a call with no type', function (): void {
    $tracker = boot_tracker( [ 'allow_usage_tracking' => 1 ] );

    wp_clear_scheduled_hook( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] );

    $tracker->schedule_tracking_data_sending( true, [] );

    expect( wp_next_scheduled( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] ) )
        ->toBeFalse();
} );

it( 'does not schedule a second send when one is already pending', function (): void {
    // Two core updates in one request, or a retry. The wp_next_scheduled()
    // guard is what stops the queue filling with duplicates.
    $tracker = boot_tracker( [ 'allow_usage_tracking' => 1 ] );

    wp_clear_scheduled_hook( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] );

    $tracker->schedule_tracking_data_sending( true, [ 'type' => 'core' ] );

    $first = wp_next_scheduled( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] );

    $tracker->schedule_tracking_data_sending( true, [ 'type' => 'core' ] );

    // Same timestamp, so the second call scheduled nothing new rather than
    // moving the existing event.
    expect( wp_next_scheduled( 'hbp_disabler_send_tracking_data_after_core_update', [ true ] ) )
        ->toBe( $first );
} );
