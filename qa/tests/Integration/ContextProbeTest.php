<?php

/**
 * What this environment offers for request context. A probe, not coverage.
 *
 * Six features are untested because they branch on where the request landed:
 * is_admin() for the dashboard, $wp_query for feeds and shortlinks, and an
 * exit() inside Feeds::redirectFeedOrDie(). Building a harness for those needs
 * facts about PestWP and this WordPress, so this file asks the environment
 * and prints the answers.
 *
 * These tests pass. They report rather than assert -- run them, read the
 * output, delete the file once the harness exists.
 */

declare(strict_types = 1);

/**
 * Print a block of facts to stderr, where both the terminal and the CI log
 * pick it up.
 *
 * @param array<string, string> $facts
 */
function report( string $title, array $facts ): void {
    $out = "\n  [probe] {$title}\n";

    foreach ( $facts as $label => $value ) {
        $out .= sprintf( "    %-24s %s\n", $label, $value );
    }

    fwrite( STDERR, $out );
}

function yes_no( bool $value ): string {
    return $value ? 'yes' : 'no';
}

it( 'reports what request context tooling exists', function (): void {
    report( 'environment', [
        'WP_ADMIN defined' => defined( 'WP_ADMIN' ) ? yes_no( (bool) WP_ADMIN ) . ' (defined)' : 'not defined',
        'is_admin()'       => yes_no( is_admin() ),
        'go_to() function' => yes_no( function_exists( 'go_to' ) ),
        'go_to() method'   => yes_no( method_exists( $this, 'go_to' ) ),
        'WP_UnitTestCase'  => yes_no( class_exists( 'WP_UnitTestCase' ) ),
        'test case class'  => get_class( $this ),
        'permalinks'       => get_option( 'permalink_structure' ) ?: '(plain)',
        'wp() available'   => yes_no( function_exists( 'wp' ) ),
        'wp version'       => get_bloginfo( 'version' ),
    ] );

    expect( true )->toBeTrue();
} );

it( 'reports whether a main query can be re-run in process', function (): void {
    // The biggest question for the harness: if this works, feeds and
    // shortlinks become testable without a second bootstrap.
    $post_id = wp_insert_post( [
        'post_title'  => 'Context probe',
        'post_status' => 'publish',
        'post_type'   => 'post',
    ] );

    $GLOBALS['wp_query'] = new WP_Query( [ 'p' => $post_id ] );
    $GLOBALS['post']     = get_post( $post_id );

    setup_postdata( $GLOBALS['post'] );

    report( 'singular query', [
        'is_singular()' => yes_no( is_singular() ),
        'is_feed()'     => yes_no( is_feed() ),
        'queried id'    => (string) get_queried_object_id(),
        'inserted id'   => (string) $post_id,
    ] );

    expect( true )->toBeTrue();
} );

it( 'reports whether a feed query can be simulated', function (): void {
    // maybeRedirectFeeds() compares $wp_query->query against exact arrays
    // like [ 'feed' => 'feed' ], so the printed query matters as much as the
    // conditionals.
    $GLOBALS['wp_query'] = new WP_Query( [ 'feed' => 'feed' ] );

    report( 'feed query', [
        'is_feed()'         => yes_no( is_feed() ),
        'is_comment_feed()' => yes_no( is_comment_feed() ),
        'query var feed'    => (string) get_query_var( 'feed' ),
        'wp_query->query'   => (string) wp_json_encode( $GLOBALS['wp_query']->query ),
    ] );

    expect( true )->toBeTrue();
} );
