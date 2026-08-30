<?php

/**
 * backend.disable_self_ping.
 *
 * The callback takes its links by reference and mutates them in place, so the
 * outcome is the array core is left holding after the hook -- fired the same
 * way core fires it, with do_action_ref_array.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Backend;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Backend::class );

/**
 * @param array<string, mixed> $values
 *
 * @return array<int, string> the links core is left with
 */
function ping_links( array $values, array $links ): array {
    $defaults = require plugin_path( 'config/backend.php' );

    store_settings( 'backend', array_merge( $defaults, $values ) );

    boot_feature( Backend::class );

    do_action_ref_array( 'pre_ping', [ &$links ] );

    return array_values( $links );
}

it( 'leaves internal links alone when the control is off', function (): void {
    $links = ping_links(
        [ 'disable_self_ping' => 0 ],
        [ home_url( '/a-post/' ), 'https://elsewhere.example/page' ]
    );

    expect( $links )->toHaveCount( 2 );
} );

it( 'drops links pointing at this site', function (): void {
    $links = ping_links(
        [ 'disable_self_ping' => 1 ],
        [ home_url( '/a-post/' ), 'https://elsewhere.example/page' ]
    );

    // The external link surviving is the load-bearing half: a bug that
    // emptied the array would satisfy "the self link is gone" while silently
    // stopping every legitimate outgoing pingback.
    expect( $links )->toBe( [ 'https://elsewhere.example/page' ] );
} );

it( 'drops links matching an extra url the user listed', function (): void {
    $links = ping_links(
        [
            'disable_self_ping'      => 1,
            'disable_self_ping_urls' => "https://cdn.example\nhttps://old.example",
        ],
        [ 'https://cdn.example/img', 'https://old.example/x', 'https://elsewhere.example/page' ]
    );

    expect( $links )->toBe( [ 'https://elsewhere.example/page' ] );
} );

it( 'matches on prefix, not on substring', function (): void {
    // strpos( $link, $url ) === 0 is a prefix test. A url that merely appears
    // inside the link must not match, or an unrelated site linking to a page
    // whose query string mentions this one would stop being pinged.
    $links = ping_links(
        [ 'disable_self_ping' => 1 ],
        [ 'https://elsewhere.example/?ref=' . rawurlencode( home_url() ) ]
    );

    expect( $links )->toHaveCount( 1 );
} );
