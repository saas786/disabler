<?php

/**
 * frontend.disable_shortlinks.
 *
 * The first feature covered through a real query context. Core only prints a
 * shortlink for a singular request, so without one in the main query both
 * cases would come back empty and the "removed" test would pass against a
 * head that never carried the tag.
 *
 * The tag is matched on single quotes because wp_shortlink_wp_head() writes
 * its attributes by hand rather than through a filter that would normalise
 * them. Matching on rel="shortlink" is what this file did first, and it is
 * the worse kind of wrong: the "removed" case stayed green while asserting
 * the absence of a string the head could never contain either way.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Frontend;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Frontend::class );

/**
 * @param array<string, mixed> $values
 */
function shortlink_head( array $values ): string {
    $defaults = require plugin_path( 'config/frontend.php' );

    store_settings( 'frontend', array_merge( $defaults, $values ) );

    boot_feature( Frontend::class );

    query_singular( wp_insert_post( [
        'post_title'  => 'Shortlink subject',
        'post_status' => 'publish',
        'post_type'   => 'post',
    ] ) );

    return rendered_head();
}

const SHORTLINK_TAG = "rel='shortlink'";

it( 'prints the shortlink tag when the control is off', function (): void {
    expect( shortlink_head( [ 'disable_shortlinks' => 0 ] ) )->toContain( SHORTLINK_TAG );
} );

it( 'removes the shortlink tag', function (): void {
    $head = shortlink_head( [ 'disable_shortlinks' => 1 ] );

    // Canonical comes from the same singular request and this feature does
    // not touch it, so it is what says the head was rendered at all.
    expect( $head )->not->toContain( SHORTLINK_TAG )
        ->and( $head )->toContain( 'rel="canonical"' );
} );

it( 'leaves the shortlink header hook in place when the control is off', function (): void {
    shortlink_head( [ 'disable_shortlinks' => 0 ] );

    // The HTTP header half sends its output through headers rather than the
    // document, and PHP cannot read back a header this process never sent.
    // The registration check is second best and flagged as such -- it is the
    // one place in the suite asserting on a hook instead of an outcome.
    expect( has_action( 'template_redirect', 'wp_shortlink_header' ) )->not->toBeFalse();
} );

it( 'removes the shortlink header hook', function (): void {
    shortlink_head( [ 'disable_shortlinks' => 1 ] );

    expect( has_action( 'template_redirect', 'wp_shortlink_header' ) )->toBeFalse();
} );
