<?php

/**
 * performance.disable_embeds.
 *
 * The widest control in the section: one setting drives ten separate
 * removals, spread across the head, the rewrite rules, the oEmbed pipeline
 * and the REST routes. That breadth is the reason to cover it -- a control
 * this size can lose most of its work and still look like it is doing
 * something, because whichever part is checked by hand still works.
 *
 * Each case picks a seam core itself reads, not a hook name. The discovery
 * link is asserted through the rendered head; the rewrite rules through the
 * filter core passes them to; the query var through $wp, which is where core
 * looks when parsing a request.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Performance;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Performance::class );

/**
 * @param array<string, mixed> $values
 */
function boot_embeds( array $values ): Performance {
    $defaults = require plugin_path( 'config/performance.php' );

    store_settings( 'performance', array_merge( $defaults, $values ) );

    return boot_feature( Performance::class );
}

/**
 * A published post, so wp_oembed_add_discovery_links() has something to
 * point at. Away from a singular request it prints nothing, and both the on
 * and off cases would come back with an empty head.
 */
function embeddable_post(): int {
    return wp_insert_post( [
        'post_title'  => 'Embed subject',
        'post_status' => 'publish',
        'post_type'   => 'post',
    ] );
}

it( 'prints the oembed discovery links when the control is off', function (): void {
    boot_embeds( [ 'disable_embeds' => 0 ] );

    query_singular( embeddable_post() );

    // The baseline. If core ever stops printing these, the removal case below
    // starts passing for a reason that has nothing to do with the plugin.
    expect( rendered_head() )->toContain( 'application/json+oembed' );
} );

it( 'removes the oembed discovery links', function (): void {
    boot_embeds( [ 'disable_embeds' => 1 ] );

    query_singular( embeddable_post() );

    $head = rendered_head();

    // Canonical says the head rendered at all -- this feature does not touch
    // it, so its absence would mean the assertion above proved nothing.
    expect( $head )->not->toContain( 'application/json+oembed' )
        ->and( $head )->not->toContain( 'text/xml+oembed' )
        ->and( $head )->toContain( 'rel="canonical"' );
} );

it( 'leaves oembed discovery on when the control is off', function (): void {
    boot_embeds( [ 'disable_embeds' => 0 ] );

    expect( apply_filters( 'embed_oembed_discover', true ) )->toBeTrue();
} );

it( 'turns off oembed auto discovery', function (): void {
    // This is what stops WordPress making an outbound HTTP request to any
    // pasted URL, which is the half of the control with a network cost.
    boot_embeds( [ 'disable_embeds' => 1 ] );

    expect( apply_filters( 'embed_oembed_discover', true ) )->toBeFalse();
} );

it( 'drops only the embed rewrite rules', function (): void {
    boot_embeds( [ 'disable_embeds' => 1 ] );

    $rules = apply_filters( 'rewrite_rules_array', [
        'feed/(feed|rdf|rss)/?$' => 'index.php?&feed=$matches[1]',
        '(.?.+?)/embed/?$'       => 'index.php?pagename=$matches[1]&embed=true',
        '([0-9]{4})/embed/?$'    => 'index.php?year=$matches[1]&embed=true',
    ] );

    // The surviving feed rule is the load-bearing half: the callback matches
    // on 'embed=true' appearing anywhere in the rewrite target, and a bug
    // that emptied the array would satisfy "the embed rules are gone" while
    // taking every permalink on the site with it.
    expect( $rules )->toHaveCount( 1 )
        ->and( $rules )->toHaveKey( 'feed/(feed|rdf|rss)/?$' );
} );

it( 'leaves the rewrite rules alone when the control is off', function (): void {
    boot_embeds( [ 'disable_embeds' => 0 ] );

    $rules = apply_filters( 'rewrite_rules_array', [
        '(.?.+?)/embed/?$' => 'index.php?pagename=$matches[1]&embed=true',
    ] );

    expect( $rules )->toHaveCount( 1 );
} );

it( 'survives an empty rewrite rules array', function (): void {
    // Reachable on a site with plain permalinks, where core hands the filter
    // an empty array. The guard exists; without a test it can be removed by
    // anyone tidying the method.
    boot_embeds( [ 'disable_embeds' => 1 ] );

    expect( apply_filters( 'rewrite_rules_array', [] ) )->toBe( [] );
} );

it( 'removes the embed query var', function (): void {
    global $wp;
    // The precondition is as much the point of this test as the assertion
    // under it. $wp is mutated in place at boot and is not $wp_filter, so the
    // hook restore does not put 'embed' back -- restore_query_vars() in the
    // suite teardown is what does, and this is where its absence shows up
    // first. Six tests above this one boot the control on.
    expect( $wp->public_query_vars )->toContain( 'embed' );

    boot_embeds( [ 'disable_embeds' => 1 ] );

    // 'p' surviving is the load-bearing half: an array_diff against the wrong
    // list would empty the property and stop WordPress resolving any request
    // at all.
    expect( $wp->public_query_vars )->not->toContain( 'embed' )
        ->and( $wp->public_query_vars )->toContain( 'p' );
} );

it( 'unregisters the oembed rest route', function (): void {
    // This and the case below cover the two callbacks that used to be
    // unreachable: initHooks() called remove_filter() on them rather than
    // add_filter(), so it removed callbacks nothing had added and the methods
    // were dead code. The endpoint stayed registered with the control on.
    boot_embeds( [ 'disable_embeds' => 1 ] );

    $endpoints = apply_filters( 'rest_endpoints', [
        '/oembed/1.0/embed' => [ 'callback' ],
        '/wp/v2/posts'      => [ 'callback' ],
    ] );

    // The posts route surviving is the load-bearing half: this callback
    // unsets one key, and a wrong list would take the whole REST API down.
    expect( $endpoints )->not->toHaveKey( '/oembed/1.0/embed' )
        ->and( $endpoints )->toHaveKey( '/wp/v2/posts' );
} );

it( 'leaves the oembed rest route alone when the control is off', function (): void {
    boot_embeds( [ 'disable_embeds' => 0 ] );

    expect( apply_filters( 'rest_endpoints', [ '/oembed/1.0/embed' => [ 'callback' ] ] ) )
        ->toHaveKey( '/oembed/1.0/embed' );
} );

it( 'passes oembed response data through outside a rest request', function (): void {
    // REST_REQUEST is not defined in this process, so this is the branch a
    // normal page load takes: the data goes back untouched. The other branch
    // returns false and cannot be reached without defining the constant,
    // which is process-wide and irreversible -- the same wall as WP_ADMIN,
    // and not worth a second bootstrap for one line.
    boot_embeds( [ 'disable_embeds' => 1 ] );

    // A real post and real dimensions, not nulls. Core's own callbacks are on
    // this filter too and they read $post->ID directly, so a null second
    // argument raises "Attempt to read property ID on null" -- a warning the
    // suite is configured to fail on, and rightly, since it would mean the
    // test was exercising a call core never makes.
    $post_id = embeddable_post();

    $filtered = apply_filters( 'oembed_response_data', [ 'title' => 'A post' ], get_post( $post_id ), 600, 400 );

    // Not an identity check: those same core callbacks fill in width, height,
    // type and html, so the array coming back is legitimately larger than the
    // one going in. What this branch promises is narrower -- that it does not
    // answer false, which is what the REST branch does and what would blank
    // the response.
    expect( $filtered )->toBeArray()
        ->and( $filtered['title'] )->toBe( 'A post' );
} );
