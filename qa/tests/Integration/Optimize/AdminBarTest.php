<?php

/**
 * admin_bar.disable_admin_bar, including the per-role case.
 *
 * The first feature that reads the current user, so each test sets one before
 * booting -- initHooks() returns early for a logged-out visitor, and a stale
 * user from a previous test would silently pick the branch.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\AdminBar;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( AdminBar::class );

/**
 * Create a fresh user and log in as them.
 *
 * Left on the default role, deliberately -- see the "role that was not
 * selected" case for why nothing here tries to assign one.
 */
function log_in_as(): int {
    $suffix  = wp_generate_password( 6, false );
    $user_id = wp_insert_user( [
        'user_login' => "ab_{$suffix}",
        'user_email' => "ab_{$suffix}@example.org",
        'user_pass'  => wp_generate_password(),
    ] );

    // A WP_Error here would send wp_set_current_user() to the logged out
    // user, which changes the branch under test instead of failing.
    expect( $user_id )->toBeInt();

    wp_set_current_user( $user_id );

    return $user_id;
}

/**
 * @param array<string, mixed> $values
 */
function store_admin_bar( array $values ): void {
    $defaults = require plugin_path( 'config/admin_bar.php' );

    store_settings( 'admin_bar', array_merge( $defaults, $values ) );
}

/**
 * @param array<string, mixed> $values
 */
function admin_bar_shown( array $values ): bool {
    log_in_as();

    store_admin_bar( $values );

    boot_feature( AdminBar::class );

    return (bool) apply_filters( 'show_admin_bar', true );
}

it( 'shows the admin bar when the control is off', function (): void {
    expect( admin_bar_shown( [ 'disable_admin_bar' => 'no' ] ) )->toBeTrue();
} );

it( 'hides the admin bar for everyone', function (): void {
    expect( admin_bar_shown( [ 'disable_admin_bar' => 'all' ] ) )->toBeFalse();
} );

it( 'hides the admin bar for a selected role', function (): void {
    expect( admin_bar_shown( [
        'disable_admin_bar' => 'selective',
        'admin_bar_roles'   => [ 'subscriber' ],
    ] ) )->toBeFalse();
} );

it( 'leaves the admin bar alone for a role that was not selected', function (): void {
    // The half that proves 'selective' is actually selective rather than a
    // second spelling of 'all'.
    //
    // The user stays a subscriber and the *setting* names a different role.
    // Creating an editor was the obvious way to write this and cost three
    // rounds: neither wp_insert_user's role argument nor a later set_role()
    // survived into wp_get_current_user(), even after clean_user_cache(). The
    // branch under test only compares the current user's roles against the
    // stored list, so which side differs makes no difference to what is
    // covered -- and this side works.
    log_in_as();

    store_admin_bar( [
        'disable_admin_bar' => 'selective',
        'admin_bar_roles'   => [ 'editor' ],
    ] );

    expect( wp_get_current_user()->roles )->toBe( [ 'subscriber' ] );

    boot_feature( AdminBar::class );

    expect( (bool) apply_filters( 'show_admin_bar', true ) )->toBeTrue();
} );

it( 'leaves logged out visitors to core', function (): void {
    wp_set_current_user( 0 );

    store_admin_bar( [ 'disable_admin_bar' => 'all' ] );

    boot_feature( AdminBar::class );

    // Nothing registered: core already hides the bar for anonymous users, and
    // the feature deliberately does not get involved.
    expect( has_filter( 'show_admin_bar' ) )->toBeFalse();
} );
