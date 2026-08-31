<?php

/**
 * Client IP resolution, and the cached Jetpack IP list.
 *
 * getIP() is the other half of the xmlrpc allowlist: IpUtils decides whether
 * an address matches, this decides which address is checked. A bug here is a
 * bypass rather than a lockout.
 *
 * Read the header-precedence tests below as a record of what the code does
 * today, not as an endorsement. HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR are
 * both consulted before REMOTE_ADDR and neither is verified against a list
 * of trusted proxies, so on a site not behind one they are attacker
 * controlled. That is the upstream behaviour this file was adapted from, and
 * changing it is a decision for the maintainer -- but it should be a
 * decision, not an accident, which is why the precedence is pinned here.
 *
 * The Jetpack list is exercised through the 'pre_http_request' filter, so
 * nothing here reaches jetpack.com. The transport is short-circuited before
 * WordPress opens a socket.
 *
 * Not covered: the WP_Error branch of safeWPRemoteRequest(). It calls
 * trigger_error() at E_USER_WARNING, and the suite runs with
 * failOnWarning="true", so exercising it would fail the run for doing
 * exactly what it is supposed to do. Suppressing it needs
 * WPCOM_VIP_DISABLE_REMOTE_REQUEST_ERROR_REPORTING, and defining a constant
 * mid-suite is process-wide and irreversible.
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\Jetpack\IPManager;

/**
 * $_SERVER is request state, not WordPress state, so neither the transaction
 * rollback nor the hook restore reaches it. Every key this file writes is
 * put back, including the ones that were absent to begin with -- leaving a
 * stray HTTP_CLIENT_IP behind would change how every later test resolves an
 * address.
 */
beforeEach( function (): void {
    $this->server = $_SERVER;
} );

afterEach( function (): void {
    $_SERVER = $this->server;
} );

/**
 * Answer one HTTP request with a canned response, without a socket.
 *
 * 'pre_http_request' short circuits WP_Http entirely: returning a non-false
 * value from it means WordPress never opens a connection.
 *
 * @param array<string, mixed>|WP_Error $response
 */
function fake_http( $response ): void {
    add_filter( 'pre_http_request', static fn() => $response );
}

/**
 * A response body as WP_Http would return it.
 */
function http_ok( string $body ): array {
    return [
        'headers'  => [],
        'body'     => $body,
        'response' => [
            'code'    => 200,
            'message' => 'OK',
        ],
    ];
}

it( 'uses the remote address when no proxy headers are present', function (): void {
    unset( $_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'] );

    $_SERVER['REMOTE_ADDR'] = '203.0.113.9';

    expect( IPManager::getIP() )->toBe( '203.0.113.9' );
} );

it( 'falls back to localhost when nothing usable is set', function (): void {
    unset( $_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR'] );

    // Not an empty string. Something downstream will compare this against an
    // allowlist, and an empty needle is a different question than a loopback
    // address.
    expect( IPManager::getIP() )->toBe( '127.0.0.1' );
} );

it( 'falls back to localhost when the remote address is malformed', function (): void {
    unset( $_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'] );

    $_SERVER['REMOTE_ADDR'] = 'not-an-ip';

    expect( IPManager::getIP() )->toBe( '127.0.0.1' );
} );

it( 'prefers the client ip header over the remote address', function (): void {
    // Documenting current behaviour. On a site not behind a proxy that sets
    // this header, its value comes from the client.
    unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );

    $_SERVER['HTTP_CLIENT_IP'] = '198.51.100.7';
    $_SERVER['REMOTE_ADDR']    = '203.0.113.9';

    expect( IPManager::getIP() )->toBe( '198.51.100.7' );
} );

it( 'prefers the forwarded for header over the remote address', function (): void {
    unset( $_SERVER['HTTP_CLIENT_IP'] );

    $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';
    $_SERVER['REMOTE_ADDR']          = '203.0.113.9';

    expect( IPManager::getIP() )->toBe( '198.51.100.7' );
} );

it( 'takes the first address from a forwarded for chain', function (): void {
    // X-Forwarded-For accumulates left to right, so the first entry is the
    // original client. Taking the last would name a proxy instead.
    unset( $_SERVER['HTTP_CLIENT_IP'] );

    $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7, 203.0.113.9, 192.0.2.1';

    expect( IPManager::getIP() )->toBe( '198.51.100.7' );
} );

it( 'ignores a malformed forwarded for header', function (): void {
    unset( $_SERVER['HTTP_CLIENT_IP'] );

    $_SERVER['HTTP_X_FORWARDED_FOR'] = 'garbage';
    $_SERVER['REMOTE_ADDR']          = '203.0.113.9';

    // Note it does not fall through to REMOTE_ADDR: the branch was taken, so
    // an unparseable header produces the loopback fallback rather than the
    // address the connection actually came from.
    expect( IPManager::getIP() )->toBe( '127.0.0.1' );
} );

it( 'lets a filter override the resolved address', function (): void {
    // The escape hatch a site behind a known proxy would use to impose its
    // own trust rules.
    unset( $_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'] );

    $_SERVER['REMOTE_ADDR'] = '203.0.113.9';

    add_filter( 'hbp/disabler/get_ip', static fn(): string => '192.0.2.55' );

    expect( IPManager::getIP() )->toBe( '192.0.2.55' );
} );

it( 'stores the jetpack ip list and an expiry', function (): void {
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( wp_json_encode( [ '192.0.2.0/24', '198.51.100.0/24' ] ) ) );

    $data = IPManager::updateJetpackIPs();

    expect( $data['ips'] )->toBe( [ '192.0.2.0/24', '198.51.100.0/24' ] )
        ->and( $data['exp'] )->toBeGreaterThan( time() );

    // Written through to the option, which is what the next request reads.
    expect( get_option( IPManager::OPTION_NAME )['ips'] )->toBe( $data['ips'] );
} );

it( 'stores nothing when the endpoint answers with an error status', function (): void {
    delete_option( IPManager::OPTION_NAME );

    fake_http( [
        'headers'  => [],
        'body'     => 'nope',
        'response' => [
            'code'    => 500,
            'message' => 'Internal Server Error',
        ],
    ] );

    expect( IPManager::updateJetpackIPs() )->toBe( [] )
        ->and( get_option( IPManager::OPTION_NAME, false ) )->toBeFalse();
} );

it( 'rejects a json object served as a list', function (): void {
    // The regression test for a real defect. The guard used to be
    // `is_array( $ips ) && ! empty( $ips )`, and json_decode with assoc=true
    // turns a JSON *object* into an array too -- so an error payload served
    // with a 200 was stored as the IP list, and whatever read it matched
    // addresses against the string 'nope'. array_is_list() is the fix.
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( '{"error":"nope"}' ) );

    expect( IPManager::updateJetpackIPs() )->toBe( [] )
        ->and( get_option( IPManager::OPTION_NAME, false ) )->toBeFalse();
} );

it( 'rejects an object with no entries', function (): void {
    // json_decode gives [] for both '{}' and '[]', so this one was always
    // rejected -- by the empty check rather than the shape check. Kept so
    // that stays true if the empty check is ever dropped.
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( '{}' ) );

    expect( IPManager::updateJetpackIPs() )->toBe( [] );
} );

it( 'drops blank entries from an otherwise good list', function (): void {
    // One malformed line should not become an allowlist entry. A blank string
    // stored here would be compared against every request address.
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( '["192.0.2.0/24","","  ",null]' ) );

    expect( IPManager::updateJetpackIPs()['ips'] )->toBe( [ '192.0.2.0/24' ] );
} );

it( 'rejects a list of non strings', function (): void {
    // Nested arrays and objects inside the list. Every entry is filtered out,
    // so nothing is left to store.
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( '[["a"],{"b":1}]' ) );

    expect( IPManager::updateJetpackIPs() )->toBe( [] );
} );

it( 'stores nothing when the body is an empty list', function (): void {
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( '[]' ) );

    expect( IPManager::updateJetpackIPs() )->toBe( [] )
        ->and( get_option( IPManager::OPTION_NAME, false ) )->toBeFalse();
} );

it( 'stores nothing when the body is not json at all', function (): void {
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( '<html>maintenance</html>' ) );

    expect( IPManager::updateJetpackIPs() )->toBe( [] );
} );

it( 'reads an unexpired list without making a request', function (): void {
    update_option( IPManager::OPTION_NAME, [
        'ips' => [ '192.0.2.0/24' ],
        'exp' => time() + HOUR_IN_SECONDS,
    ] );

    // Any request at all is the failure. If the cache were ignored, every
    // page load would reach for the network.
    fake_http( http_ok( wp_json_encode( [ 'fresh-value-should-not-be-used' ] ) ) );

    expect( IPManager::getJetpackIPs() )->toBe( [ '192.0.2.0/24' ] );
} );

it( 'refreshes an expired list', function (): void {
    update_option( IPManager::OPTION_NAME, [
        'ips' => [ '192.0.2.0/24' ],
        'exp' => time() - HOUR_IN_SECONDS,
    ] );

    fake_http( http_ok( wp_json_encode( [ '198.51.100.0/24' ] ) ) );

    expect( IPManager::getJetpackIPs() )->toBe( [ '198.51.100.0/24' ] );
} );

it( 'keeps the stale list when the refresh fails', function (): void {
    // The branch that matters on a bad day: the endpoint is down, and the
    // answer is the old list rather than nothing. Returning [] here would
    // fail closed against every Jetpack address at once.
    update_option( IPManager::OPTION_NAME, [
        'ips' => [ '192.0.2.0/24' ],
        'exp' => time() - HOUR_IN_SECONDS,
    ] );

    fake_http( [
        'headers'  => [],
        'body'     => '',
        'response' => [
            'code'    => 503,
            'message' => 'Service Unavailable',
        ],
    ] );

    expect( IPManager::getJetpackIPs() )->toBe( [ '192.0.2.0/24' ] );
} );

it( 'fetches the list when the option has never been written', function (): void {
    delete_option( IPManager::OPTION_NAME );

    fake_http( http_ok( wp_json_encode( [ '198.51.100.0/24' ] ) ) );

    expect( IPManager::getJetpackIPs() )->toBe( [ '198.51.100.0/24' ] );
} );
