<?php

/**
 * The xmlrpc section.
 *
 * Three-state control -- no, completely, selective -- and almost every
 * callback branches on all three, so a case is only meaningful next to the
 * other two. 'completely' ignores every companion setting; 'selective' reads
 * them; 'no' never registers anything at all, because initHooks() returns
 * before it hooks a single filter.
 *
 * That early return is also the seam for the IP allowlist: an allowed
 * request is indistinguishable from the control being off, which is the
 * intended behaviour and the reason the allowlist case asserts on
 * registration rather than on a filtered value.
 *
 * wlwmanifest_link is deliberately absent below. Core removed it from
 * default-filters in 6.3, so on any supported WordPress there is nothing on
 * wp_head to remove and a paired test would have no baseline to assert. The
 * production code still calls remove_action() for it, harmlessly.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\XMLRPC;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( XMLRPC::class );

/**
 * @param array<string, mixed> $values
 */
function boot_xmlrpc( array $values ): void {
    $defaults = require plugin_path( 'config/xmlrpc.php' );

    store_settings( 'xmlrpc', array_merge( $defaults, $values ) );

    boot_feature( XMLRPC::class );
}

/**
 * The methods core would offer, as xmlrpc_methods hands them over.
 *
 * @return array<string, string>
 */
function core_methods(): array {
    return [
        'wp.getUsersBlogs' => 'this:getUsersBlogs',
        'wp.newPost'       => 'this:newPost',
        'pingback.ping'    => 'this:ping',
        'demo.sayHello'    => 'this:sayHello',
    ];
}

it( 'registers nothing when the control is off', function (): void {
    // The whole feature hangs off one early return, so counting across its
    // three main hooks covers every callback in the class at once.
    //
    // Counted rather than asked with has_filter(): core hooks wp_headers and
    // xmlrpc_methods itself, so a bare has_filter() is true before the plugin
    // does anything and the assertion never had a chance.
    $hooks = [ 'xmlrpc_enabled', 'xmlrpc_methods', 'wp_headers' ];

    $before = array_map( 'hook_callback_count', $hooks );

    boot_xmlrpc( [ 'disable_xmlrpc' => 'no' ] );

    expect( array_map( 'hook_callback_count', $hooks ) )->toBe( $before );
} );

it( 'reports xmlrpc as disabled when switched off completely', function (): void {
    boot_xmlrpc( [ 'disable_xmlrpc' => 'completely' ] );

    expect( apply_filters( 'xmlrpc_enabled', true ) )->toBeFalse();
} );

it( 'leaves xmlrpc_enabled alone under selective', function (): void {
    // The half that proves the callback branches. Selective is about
    // individual methods, so the endpoint itself must stay reachable.
    boot_xmlrpc( [ 'disable_xmlrpc' => 'selective' ] );

    expect( apply_filters( 'xmlrpc_enabled', true ) )->toBeTrue();
} );

it( 'empties the method list completely', function (): void {
    boot_xmlrpc( [ 'disable_xmlrpc' => 'completely' ] );

    expect( apply_filters( 'xmlrpc_methods', core_methods() ) )->toBe( [] );
} );

it( 'removes only the selected methods', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc' => 'selective',
        'xmlrpc_methods' => [ 'pingback.ping' ],
    ] );

    $methods = apply_filters( 'xmlrpc_methods', core_methods() );

    // The survivors are the point: 'selective' emptying the list would be a
    // second spelling of 'completely', and nothing else would notice.
    expect( $methods )->not->toHaveKey( 'pingback.ping' )
        ->and( $methods )->toHaveKey( 'wp.newPost' )
        ->and( $methods )->toHaveCount( 3 );
} );

it( 'removes methods listed in the custom text box', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc'        => 'selective',
        'custom_xmlrpc_methods' => "wp.newPost\ndemo.sayHello",
    ] );

    $methods = apply_filters( 'xmlrpc_methods', core_methods() );

    expect( $methods )->not->toHaveKey( 'wp.newPost' )
        ->and( $methods )->not->toHaveKey( 'demo.sayHello' )
        ->and( $methods )->toHaveKey( 'pingback.ping' );
} );

it( 'merges the checkbox list with the custom text box', function (): void {
    // The two sources are array_merge'd, and a bug dropping either one would
    // still pass a test that only used the other.
    boot_xmlrpc( [
        'disable_xmlrpc'        => 'selective',
        'xmlrpc_methods'        => [ 'pingback.ping' ],
        'custom_xmlrpc_methods' => 'wp.newPost',
    ] );

    $methods = apply_filters( 'xmlrpc_methods', core_methods() );

    expect( $methods )->toBe( [
        'wp.getUsersBlogs' => 'this:getUsersBlogs',
        'demo.sayHello'    => 'this:sayHello',
    ] );
} );

it( 'tolerates blank lines and stray spacing in the custom list', function (): void {
    // A textarea, so this is what arrives from real people. prepareMultilineText
    // squishes the whitespace and drops the empties -- without that, an empty
    // string would be looked up as a method name.
    boot_xmlrpc( [
        'disable_xmlrpc'        => 'selective',
        'custom_xmlrpc_methods' => "  wp.newPost \n\n\n   pingback.ping  \n",
    ] );

    $methods = apply_filters( 'xmlrpc_methods', core_methods() );

    expect( $methods )->not->toHaveKey( 'wp.newPost' )
        ->and( $methods )->not->toHaveKey( 'pingback.ping' )
        ->and( $methods )->toHaveKey( 'demo.sayHello' );
} );

it( 'drops the pingback header completely', function (): void {
    boot_xmlrpc( [ 'disable_xmlrpc' => 'completely' ] );

    $headers = apply_filters( 'wp_headers', [
        'X-Pingback'   => 'https://example.org/xmlrpc.php',
        'Content-Type' => 'text/html',
    ] );

    // Content-Type surviving is load-bearing: array_walk against the wrong
    // list would strip every header the response needs.
    expect( $headers )->not->toHaveKey( 'X-Pingback' )
        ->and( $headers )->toHaveKey( 'Content-Type' );
} );

it( 'leaves headers alone under selective until one is listed', function (): void {
    boot_xmlrpc( [ 'disable_xmlrpc' => 'selective' ] );

    expect( apply_filters( 'wp_headers', [ 'X-Pingback' => 'https://example.org/xmlrpc.php' ] ) )
        ->toHaveKey( 'X-Pingback' );
} );

it( 'drops the headers the user selected', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc'         => 'selective',
        'disable_xmlrpc_headers' => [ 'X-Pingback' ],
    ] );

    expect( apply_filters( 'wp_headers', [ 'X-Pingback' => 'https://example.org/xmlrpc.php' ] ) )
        ->not->toHaveKey( 'X-Pingback' );
} );

it( 'drops headers listed in the custom text box', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc'        => 'selective',
        'custom_xmlrpc_headers' => 'X-Custom-Thing',
    ] );

    $headers = apply_filters( 'wp_headers', [
        'X-Custom-Thing' => 'yes',
        'X-Pingback'     => 'https://example.org/xmlrpc.php',
    ] );

    expect( $headers )->not->toHaveKey( 'X-Custom-Thing' )
        ->and( $headers )->toHaveKey( 'X-Pingback' );
} );

it( 'blanks the pingback url completely', function (): void {
    boot_xmlrpc( [ 'disable_xmlrpc' => 'completely' ] );

    expect( apply_filters( 'bloginfo_url', 'https://example.org/xmlrpc.php', 'pingback_url' ) )
        ->toBe( '' );
} );

it( 'leaves every other bloginfo url alone', function (): void {
    // The callback sits on bloginfo_url for every key core asks about, and
    // its first line is the guard that stops it blanking the site's own home
    // url. Without this case that guard could be deleted silently.
    boot_xmlrpc( [ 'disable_xmlrpc' => 'completely' ] );

    expect( apply_filters( 'bloginfo_url', 'https://example.org', 'url' ) )
        ->toBe( 'https://example.org' );
} );

it( 'keeps the pingback url under selective until the box is ticked', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc'              => 'selective',
        'remove_xmlrpc_pingback_link' => 0,
    ] );

    expect( apply_filters( 'bloginfo_url', 'https://example.org/xmlrpc.php', 'pingback_url' ) )
        ->toBe( 'https://example.org/xmlrpc.php' );
} );

it( 'blanks the pingback url when the box is ticked', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc'              => 'selective',
        'remove_xmlrpc_pingback_link' => 1,
    ] );

    expect( apply_filters( 'bloginfo_url', 'https://example.org/xmlrpc.php', 'pingback_url' ) )
        ->toBe( '' );
} );

it( 'prints the rsd link when the control is off', function (): void {
    boot_xmlrpc( [ 'disable_xmlrpc' => 'no' ] );

    expect( rendered_head() )->toContain( 'rel="EditURI"' );
} );

it( 'removes the rsd link completely', function (): void {
    boot_xmlrpc( [ 'disable_xmlrpc' => 'completely' ] );

    $head = rendered_head();

    expect( $head )->not->toContain( 'rel="EditURI"' )
        ->and( $head )->toContain( 'rel="https://api.w.org/"' );
} );

it( 'keeps the rsd link under selective until the box is ticked', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc'         => 'selective',
        'xmlrpc_remove_rsd_link' => 0,
    ] );

    expect( rendered_head() )->toContain( 'rel="EditURI"' );
} );

it( 'removes the rsd link when the box is ticked', function (): void {
    boot_xmlrpc( [
        'disable_xmlrpc'         => 'selective',
        'xmlrpc_remove_rsd_link' => 1,
    ] );

    expect( rendered_head() )->not->toContain( 'rel="EditURI"' );
} );

it( 'stands aside entirely for an allowlisted ip', function (): void {
    // The allowlist is checked before anything is registered, so an allowed
    // request looks exactly like the control being off -- which is the
    // intent: those clients are meant to keep working.
    $before = $_SERVER['REMOTE_ADDR'] ?? null;

    $_SERVER['REMOTE_ADDR'] = '198.51.100.7';

    try {
        $before = hook_callback_count( 'xmlrpc_enabled' );

        boot_xmlrpc( [
            'disable_xmlrpc'              => 'completely',
            'custom_xmlrpc_whitelist_ips' => '198.51.100.7',
        ] );

        expect( hook_callback_count( 'xmlrpc_enabled' ) )->toBe( $before );
    } finally {
        if ( null === $before ) {
            unset( $_SERVER['REMOTE_ADDR'] );
        } else {
            $_SERVER['REMOTE_ADDR'] = $before;
        }
    }
} );

it( 'still disables xmlrpc for an ip that is not on the list', function (): void {
    $before = $_SERVER['REMOTE_ADDR'] ?? null;

    $_SERVER['REMOTE_ADDR'] = '203.0.113.9';

    try {
        boot_xmlrpc( [
            'disable_xmlrpc'              => 'completely',
            'custom_xmlrpc_whitelist_ips' => '198.51.100.7',
        ] );

        expect( apply_filters( 'xmlrpc_enabled', true ) )->toBeFalse();
    } finally {
        if ( null === $before ) {
            unset( $_SERVER['REMOTE_ADDR'] );
        } else {
            $_SERVER['REMOTE_ADDR'] = $before;
        }
    }
} );
