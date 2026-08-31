<?php

/**
 * The feed links Feeds removes from the document head.
 *
 * This covers the half of the feature that ends in output. The other half --
 * maybeRedirectFeeds(), which ends in exit() -- is covered in
 * FeedsRedirectTest, by throwing from inside the 'wp_redirect' filter so the
 * exit() is never reached. No process isolation needed after all.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Feeds;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Feeds::class );

/**
 * @param array<string, mixed> $values
 */
function boot_feeds( array $values ): void {
    $defaults = require plugin_path( 'config/feeds.php' );

    store_settings( 'feeds', array_merge( $defaults, $values ) );

    boot_feature( Feeds::class );
}

function published_post(): int {
    return wp_insert_post( [
        'post_title'  => 'Feed subject',
        'post_status' => 'publish',
        'post_type'   => 'post',
    ] );
}

it( 'prints the global feed link when the control is off', function (): void {
    boot_feeds( [ 'disable_feed_global' => 0 ] );

    expect( rendered_head() )->toContain( 'type="application/rss+xml"' );
} );

/**
 * The two site-wide feed links, as core titles them.
 *
 * Matching the posts link on 'Feed" href' -- the first thing this file tried
 * -- can never work: 'Comments Feed" href' contains it, so the assertion that
 * the posts link is gone and the assertion that the comments link survives
 * contradict each other and no implementation can satisfy both. The separator
 * in front of the title is the only thing that tells the two apart.
 */
const POSTS_FEED_LINK    = '&raquo; Feed"';
const COMMENTS_FEED_LINK = '&raquo; Comments Feed"';

it( 'removes the global feed link', function (): void {
    boot_feeds( [ 'disable_feed_global' => 1 ] );

    $head = rendered_head();

    // The comments feed link is a separate control and has to survive, or
    // this would pass just as well for a bug that removed every feed link.
    expect( $head )->not->toContain( POSTS_FEED_LINK )
        ->and( $head )->toContain( COMMENTS_FEED_LINK );
} );

it( 'removes the comments feed link', function (): void {
    boot_feeds( [ 'disable_feed_global_comments' => 1 ] );

    $head = rendered_head();

    expect( $head )->not->toContain( COMMENTS_FEED_LINK )
        ->and( $head )->toContain( POSTS_FEED_LINK );
} );

it( 'keeps the extra feed links on a singular request when the control is off', function (): void {
    boot_feeds( [ 'disable_feed_post_comments' => 0 ] );

    query_singular( published_post() );

    do_action( 'wp' );

    expect( has_action( 'wp_head', 'feed_links_extra' ) )->not->toBeFalse();
} );

it( 'removes the extra feed links on a singular request', function (): void {
    // maybeDisableFeeds runs on 'wp', after the query is known -- so the
    // query has to be in place before the action fires, which is the whole
    // reason this feature could not be covered before.
    boot_feeds( [ 'disable_feed_post_comments' => 1 ] );

    query_singular( published_post() );

    do_action( 'wp' );

    expect( has_action( 'wp_head', 'feed_links_extra' ) )->toBeFalse();
} );

it( 'leaves the extra feed links alone away from a singular request', function (): void {
    // The half that proves the is_singular() guard does something: the same
    // setting, a feed query instead, and the links stay.
    boot_feeds( [ 'disable_feed_post_comments' => 1 ] );

    query_feed();

    do_action( 'wp' );

    expect( has_action( 'wp_head', 'feed_links_extra' ) )->not->toBeFalse();
} );
