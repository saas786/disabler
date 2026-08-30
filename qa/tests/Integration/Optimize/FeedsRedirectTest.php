<?php

/**
 * The half of Feeds that ends the request.
 *
 * maybeRedirectFeeds() either redirects or calls wp_die(), and both endings
 * are followed by code the test runner cannot survive -- a bare exit() in the
 * first case, wp_die()'s own handler in the second. That is why this file did
 * not exist: the note in FeedsHeadLinksTest said it needed per-test process
 * isolation.
 *
 * It does not. capture_exit() throws from inside the 'wp_redirect' and
 * 'wp_die_handler' filters, which unwinds past the exit() before it runs. The
 * whole feature executes exactly as it would in production and the test still
 * gets its turn back, at no bootstrap cost. See the helper for the details.
 *
 * Each redirect is paired with the control off, because "no redirect
 * happened" is the default state of every request and an unpaired assertion
 * cannot tell a working guard from a feature that never ran.
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
function boot_feed_redirects( array $values ): void {
    $defaults = require plugin_path( 'config/feeds.php' );

    store_settings( 'feeds', array_merge( $defaults, $values ) );

    boot_feature( Feeds::class );
}

/**
 * Fire the 'wp' action the way a real request does, and report what the
 * feature tried to do with it.
 *
 * @return array{url: string, status: int}|string|null
 */
function feed_request(): array|string|null {
    return capture_exit( static fn() => do_action( 'wp' ) );
}

it( 'redirects the global feed home', function (): void {
    boot_feed_redirects( [ 'disable_feed_global' => 1 ] );

    query_feed();

    $result = feed_request();

    // 301 is part of the promise: a temporary redirect would leave crawlers
    // coming back to the feed forever, which is the cost this control exists
    // to avoid.
    expect( $result )->toBeArray()
        ->and( $result['url'] )->toBe( home_url() )
        ->and( $result['status'] )->toBe( 301 );
} );

it( 'leaves the global feed alone when the control is off', function (): void {
    boot_feed_redirects( [ 'disable_feed_global' => 0 ] );

    query_feed();

    expect( feed_request() )->toBeNull();
} );

it( 'leaves the global feed alone on a request that is not a feed', function (): void {
    // The is_feed() guard at the top of the method. Without this case it
    // could be deleted and every page on the site would redirect home.
    boot_feed_redirects( [ 'disable_feed_global' => 1 ] );

    expect( feed_request() )->toBeNull();
} );

it( 'redirects an atom feed', function (): void {
    boot_feed_redirects( [ 'disable_atom_rdf_feeds' => 1 ] );

    query_feed( [ 'feed' => 'atom' ] );

    expect( feed_request() )->toBeArray();
} );

it( 'redirects an rdf feed', function (): void {
    // The second arm of the same in_array check. Covering only atom would let
    // rdf be dropped from the list without a failure.
    boot_feed_redirects( [ 'disable_atom_rdf_feeds' => 1 ] );

    query_feed( [ 'feed' => 'rdf' ] );

    expect( feed_request() )->toBeArray();
} );

it( 'leaves an rss feed alone when only atom and rdf are disabled', function (): void {
    // The half that proves the control is about atom and rdf specifically.
    // disable_feed_global stays off, so nothing else can produce a redirect.
    boot_feed_redirects( [ 'disable_atom_rdf_feeds' => 1 ] );

    query_feed();

    expect( feed_request() )->toBeNull();
} );

it( 'dies instead of redirecting when the mode is 404', function (): void {
    // The other ending. Same trigger, different exit -- and a string comes
    // back from capture_exit() rather than an array, which is what tells the
    // two apart.
    boot_feed_redirects( [
        'disable_feed_global' => 1,
        'rss_feed_redirect'   => '404',
    ] );

    query_feed();

    expect( feed_request() )->toBeString();
} );

it( 'redirects a search feed to the search page', function (): void {
    boot_feed_redirects( [ 'disable_feed_search' => 1 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed' => 'feed',
        's'    => 'widgets',
    ] );

    $result = feed_request();

    // The destination matters here in a way it does not for the global feed:
    // this one is built by hand from the search query, so a bug in that
    // string sends visitors somewhere useless.
    expect( $result )->toBeArray()
        ->and( $result['url'] )->toContain( 's=widgets' );
} );

it( 'leaves a search feed alone when the control is off', function (): void {
    boot_feed_redirects( [ 'disable_feed_search' => 0 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed' => 'feed',
        's'    => 'widgets',
    ] );

    expect( feed_request() )->toBeNull();
} );

it( 'redirects a category feed to the category', function (): void {
    // wp_insert_term() rather than self::factory(): pest-wp does not extend
    // the test case with WordPress' own factories, so the core suite's usual
    // entry point is not available here.
    $term = wp_insert_term( 'Announcements', 'category' );

    expect( $term )->toBeArray();

    boot_feed_redirects( [ 'disable_feed_categories' => 1 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed' => 'feed',
        'cat'  => $term['term_id'],
    ] );

    $result = feed_request();

    // Compared against get_term_link() rather than a slug in the URL. The
    // suite runs on plain permalinks, so the link is a query string and the
    // slug never appears -- asserting on it would fail for a feature that
    // works.
    expect( $result )->toBeArray()
        ->and( $result['url'] )->toBe( get_term_link( (int) $term['term_id'], 'category' ) );
} );

it( 'leaves a category feed alone when the control is off', function (): void {
    $term = wp_insert_term( 'Announcements', 'category' );

    expect( $term )->toBeArray();

    boot_feed_redirects( [ 'disable_feed_categories' => 0 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed' => 'feed',
        'cat'  => $term['term_id'],
    ] );

    expect( feed_request() )->toBeNull();
} );

it( 'redirects an author feed to the author archive', function (): void {
    $user_id = wp_insert_user( [
        'user_login' => 'feeds_' . wp_generate_password( 6, false ),
        'user_email' => 'feeds_' . wp_generate_password( 6, false ) . '@example.org',
        'user_pass'  => wp_generate_password(),
    ] );

    expect( $user_id )->toBeInt();

    boot_feed_redirects( [ 'disable_feed_authors' => 1 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed'   => 'feed',
        'author' => $user_id,
    ] );

    expect( feed_request() )->toBeArray();
} );

it( 'redirects a post comment feed to the post', function (): void {
    $post_id = wp_insert_post( [
        'post_title'  => 'Comment feed subject',
        'post_status' => 'publish',
        'post_type'   => 'post',
    ] );

    boot_feed_redirects( [ 'disable_feed_post_comments' => 1 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed'         => 'comments-feed',
        'withcomments' => 1,
        'p'            => $post_id,
    ] );

    $result = feed_request();

    expect( $result )->toBeArray()
        ->and( $result['url'] )->toBe( get_permalink( $post_id ) );
} );

it( 'builds the search feed url from the search term', function (): void {
    // Six concatenation mutants escaped on this line: the pieces of the URL
    // could be reordered, dropped or emptied and the earlier search test
    // still passed, because it only asserted the query string appeared
    // somewhere in the result. Asserting the whole string is what pins the
    // trailing slash, the '?s=' and the term into one order.
    boot_feed_redirects( [ 'disable_feed_search' => 1 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed' => 'feed',
        's'    => 'widgets',
    ] );

    $result = feed_request();

    expect( $result )->toBeArray()
        ->and( $result['url'] )->toBe( trailingslashit( home_url() ) . '?s=widgets' );
} );

it( 'keeps a multi word search term intact', function (): void {
    // get_search_query() applies esc_attr, so a space arrives encoded. The
    // point is not the encoding itself but that the term survives into the
    // destination at all -- a redirect to a bare '?s=' would look like a
    // working feature and send every visitor to an empty search.
    boot_feed_redirects( [ 'disable_feed_search' => 1 ] );

    $GLOBALS['wp_query'] = new WP_Query( [
        'feed' => 'feed',
        's'    => 'block editor',
    ] );

    $result = feed_request();

    expect( $result )->toBeArray()
        ->and( $result['url'] )->toContain( 'block' )
        ->and( $result['url'] )->toContain( 'editor' );
} );
