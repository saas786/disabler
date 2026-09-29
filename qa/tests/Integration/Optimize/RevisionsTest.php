<?php

/**
 * revisions.disable_revisions and the per-post-type limit.
 *
 * The seam is wp_revisions_to_keep(), which is what core consults before
 * storing a revision, so the number coming back is the behaviour itself.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Revisions;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Revisions::class );

/**
 * @param array<string, mixed> $values
 */
function revisions_kept( array $values, string $post_type = 'post' ): int {
    $defaults = require plugin_path( 'config/revisions.php' );

    store_settings( 'revisions', array_merge( $defaults, $values ) );

    boot_feature( Revisions::class );

    $post_id = wp_insert_post( [
        'post_title'  => 'Revision subject',
        'post_status' => 'publish',
        'post_type'   => $post_type,
    ] );

    return (int) wp_revisions_to_keep( get_post( $post_id ) );
}

it( 'keeps revisions when the control is off', function (): void {
    // -1 is core's own "unlimited". Asserting the feature does not answer 0
    // matters more than the exact number.
    expect( revisions_kept( [ 'disable_revisions' => [ 'no' ] ] ) )->not->toBe( 0 );
} );

it( 'stops revisions for every post type', function (): void {
    expect( revisions_kept( [ 'disable_revisions' => [ 'all' ] ] ) )->toBe( 0 );
} );

it( 'stops revisions only for a selected post type', function (): void {
    expect( revisions_kept( [ 'disable_revisions' => [ 'page' ] ], 'page' ) )->toBe( 0 );
} );

it( 'leaves other post types alone when one is selected', function (): void {
    expect( revisions_kept( [ 'disable_revisions' => [ 'page' ] ], 'post' ) )->not->toBe( 0 );
} );

it( 'lets no override any other selection', function (): void {
    // 'no' wins outright: the code checks for it before anything else, so a
    // stored value holding both must keep revisions.
    expect( revisions_kept( [ 'disable_revisions' => [ 'no', 'all' ] ] ) )->not->toBe( 0 );
} );

it( 'applies a numeric per post type limit', function (): void {
    expect( revisions_kept( [
        'disable_revisions'    => [ 'no' ],
        'revisions_limit_post' => '3',
    ] ) )->toBe( 3 );
} );

it( 'ignores a non numeric limit', function (): void {
    expect( revisions_kept( [
        'disable_revisions'    => [ 'nope' ],
        'revisions_limit_post' => 'three',
    ] ) )->not->toBe( 0 );
} );
