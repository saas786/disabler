<?php

/**
 * performance.disable_heartbeat and its frequency companion.
 *
 * The first slice whose outcome depends on the request, not just the option.
 * Two of the four states branch on where the request landed -- $pagenow for
 * the post editor, is_admin() for the dashboard -- so the same stored value
 * legitimately produces different results, and a test that ignores the
 * context would be asserting one arbitrary half of the behaviour.
 *
 * The effect also lands outside $wp_filter: wp_deregister_script() mutates
 * WP_Scripts, which restore_hooks() cannot reach. reset_scripts() in the
 * suite teardown is what keeps a deregistered heartbeat from following the
 * next test around, and reset_screen() does the same for $pagenow and
 * $current_screen -- this file is the only one that writes either, and used
 * to be the only one that put $pagenow back, which left every later file
 * booting as though post.php were the screen loading.
 *
 * $current_screen is the on_dashboard_page half. is_admin() reads it before
 * WP_ADMIN, so setting a screen is how a front-end suite reaches the admin
 * branch, and unsetting it in teardown is what keeps that branch from
 * following the rest of the suite home.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Performance;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Performance::class );

/**
 * Boot Performance with the heartbeat controls set and the rest at their
 * declared defaults, from a given admin page.
 *
 * $pagenow is what WordPress itself reads to know which admin screen is
 * loading, and the feature branches on it directly, so the test sets it the
 * same way an actual request would arrive.
 *
 * @param array<string, mixed> $values
 */
function boot_heartbeat( array $values, ?string $pagenow = null, bool $in_admin = false ): void {
    if ( null !== $pagenow ) {
        $GLOBALS['pagenow'] = $pagenow;
    }

    // Before the boot, not after: the feature reads is_admin() while
    // registering, so a screen set afterwards would arrive too late to change
    // anything and the test would pass or fail for the wrong reason.
    if ( $in_admin ) {
        set_admin_screen();
    }

    $defaults = require plugin_path( 'config/performance.php' );

    store_settings( 'performance', array_merge( $defaults, $values ) );

    boot_feature( Performance::class );
}

/**
 * Whether core still knows about the heartbeat script.
 *
 * wp_script_is() is the API core consults before printing a script tag, so a
 * false here is the same answer a browser gets: no heartbeat on the page.
 */
function heartbeat_registered(): bool {
    return wp_script_is( 'heartbeat', 'registered' );
}

it( 'leaves the heartbeat registered when the control is off', function (): void {
    boot_heartbeat( [ 'disable_heartbeat' => 'no' ] );

    expect( heartbeat_registered() )->toBeTrue();
} );

it( 'deregisters the heartbeat everywhere', function (): void {
    boot_heartbeat( [ 'disable_heartbeat' => 'everywhere' ] );

    expect( heartbeat_registered() )->toBeFalse();
} );

it( 'keeps the heartbeat on post edit screens when only those are allowed', function (): void {
    boot_heartbeat( [ 'disable_heartbeat' => 'allow_only_on_post_edit_pages' ], 'post.php' );

    expect( heartbeat_registered() )->toBeTrue();
} );

it( 'keeps the heartbeat on the new post screen too', function (): void {
    // post-new.php is the second arm of the same condition. Covering only
    // post.php would let the editor lose its autosave heartbeat on every new
    // draft without a test noticing.
    boot_heartbeat( [ 'disable_heartbeat' => 'allow_only_on_post_edit_pages' ], 'post-new.php' );

    expect( heartbeat_registered() )->toBeTrue();
} );

it( 'deregisters the heartbeat away from post edit screens', function (): void {
    boot_heartbeat( [ 'disable_heartbeat' => 'allow_only_on_post_edit_pages' ], 'index.php' );

    expect( heartbeat_registered() )->toBeFalse();
} );

it( 'leaves the heartbeat alone on the front end when disabled on the dashboard', function (): void {
    // No screen is set, so is_admin() is false. This and the test below are
    // the same stored value in the two contexts it distinguishes; neither is
    // worth much without the other.
    boot_heartbeat( [ 'disable_heartbeat' => 'on_dashboard_page' ] );

    expect( heartbeat_registered() )->toBeTrue();
} );

it( 'deregisters the heartbeat inside the admin when set to on_dashboard_page', function (): void {
    boot_heartbeat( [ 'disable_heartbeat' => 'on_dashboard_page' ], null, true );

    expect( heartbeat_registered() )->toBeFalse();
} );

it( 'applies the configured heartbeat frequency', function (): void {
    boot_heartbeat( [
        'disable_heartbeat'   => 'no',
        'heartbeat_frequency' => '60',
    ] );

    $settings = apply_filters( 'heartbeat_settings', [ 'interval' => 15 ] );

    expect( $settings['interval'] )->toBe( 60 );
} );

it( 'ignores the frequency when the heartbeat is disabled everywhere', function (): void {
    // Documented behaviour, not an accident: the control's own description
    // tells the user frequency has no effect under 'everywhere'.
    boot_heartbeat( [
        'disable_heartbeat'   => 'everywhere',
        'heartbeat_frequency' => '60',
    ] );

    $settings = apply_filters( 'heartbeat_settings', [ 'interval' => 15 ] );

    expect( $settings['interval'] )->toBe( 15 );
} );

it( 'leaves the interval untouched when no frequency is set', function (): void {
    $default = ( require plugin_path( 'config/performance.php' ) )['heartbeat_frequency'];

    expect( $default )->toBe( '' );

    boot_heartbeat( [ 'disable_heartbeat' => 'no' ] );

    expect( apply_filters( 'heartbeat_settings', [ 'interval' => 15 ] )['interval'] )->toBe( 15 );
} );

it( 'leaves the interval untouched when the frequency is not numeric', function (): void {
    // A text control, so anything can arrive. absint() on a non-numeric
    // string yields 0, and an interval of zero is a browser hammering
    // admin-ajax -- the guard against that is worth a test.
    boot_heartbeat( [
        'disable_heartbeat'   => 'no',
        'heartbeat_frequency' => 'sixty',
    ] );

    expect( apply_filters( 'heartbeat_settings', [ 'interval' => 15 ] )['interval'] )->toBe( 15 );
} );
