<?php

/**
 * IP and CIDR matching.
 *
 * This is what decides whether a request is on the xmlrpc allowlist, so a
 * bug here either locks out clients that should be allowed or lets through
 * ones that should not. The second is a security failure, and it is the
 * quiet one -- an over-wide netmask still passes every test that only checks
 * the addresses inside it.
 *
 * A unit test, not an integration one: these are pure static functions with
 * no WordPress in them.
 *
 * The code is adapted from Symfony's IpUtils, which is well travelled, so
 * the point is not to doubt the algorithm. It is that the file was modified
 * on the way in, and nothing here would notice if a later edit broke it.
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\Jetpack\IpUtils;

it( 'matches an exact ipv4 address', function (): void {
    expect( IpUtils::checkIP( '192.168.1.1', '192.168.1.1' ) )->toBeTrue();
} );

it( 'rejects a different ipv4 address', function (): void {
    expect( IpUtils::checkIP( '192.168.1.2', '192.168.1.1' ) )->toBeFalse();
} );

it( 'accepts a list and matches any entry in it', function (): void {
    // The allowlist setting is a list, so this is the shape the feature
    // actually passes in.
    expect( IpUtils::checkIP( '10.0.0.5', [ '192.168.1.1', '10.0.0.5' ] ) )->toBeTrue();
} );

it( 'rejects an address in none of the listed entries', function (): void {
    expect( IpUtils::checkIP( '172.16.0.1', [ '192.168.1.1', '10.0.0.5' ] ) )->toBeFalse();
} );

it( 'matches an address inside a cidr range', function (): void {
    expect( IpUtils::checkIP( '192.168.1.50', '192.168.1.0/24' ) )->toBeTrue();
} );

it( 'rejects an address just outside a cidr range', function (): void {
    // One address past the boundary. An off-by-one in the netmask comparison
    // passes every in-range test and only shows up here.
    expect( IpUtils::checkIP( '192.168.2.1', '192.168.1.0/24' ) )->toBeFalse();
} );

it( 'respects a narrow netmask', function (): void {
    // /31 is two addresses. A netmask that was ignored or clamped would let
    // the whole surrounding block through, which is the failure worth
    // catching: it is invisible from inside the range.
    expect( IpUtils::checkIP( '192.168.1.0', '192.168.1.0/31' ) )->toBeTrue()
        ->and( IpUtils::checkIP( '192.168.1.1', '192.168.1.0/31' ) )->toBeTrue()
        ->and( IpUtils::checkIP( '192.168.1.2', '192.168.1.0/31' ) )->toBeFalse();
} );

it( 'treats a zero netmask as matching everything', function (): void {
    // /0 is special-cased in the source: it returns early rather than going
    // through substr_compare, because comparing zero bits would match
    // anything including malformed input.
    expect( IpUtils::checkIP( '8.8.8.8', '0.0.0.0/0' ) )->toBeTruthy();
} );

it( 'rejects a netmask outside the valid range', function (): void {
    // /33 does not exist for IPv4. Accepting it would mean substr_compare
    // reading past the end of a 32 character string.
    expect( IpUtils::checkIP( '192.168.1.1', '192.168.1.1/33' ) )->toBeFalse();
} );

it( 'rejects a malformed request address', function (): void {
    expect( IpUtils::checkIP( 'not-an-ip', '192.168.1.0/24' ) )->toBeFalse()
        ->and( IpUtils::checkIP( '', '192.168.1.0/24' ) )->toBeFalse();
} );

it( 'rejects an ipv4 address against an ipv6 range', function (): void {
    expect( IpUtils::checkIP( '192.168.1.1', '2001:db8::/32' ) )->toBeFalse();
} );

it( 'matches an exact ipv6 address', function (): void {
    expect( IpUtils::checkIP( '2001:db8::1', '2001:db8::1' ) )->toBeTrue();
} );

it( 'matches an ipv6 address inside a range', function (): void {
    // Routed to checkIP6 by the colon count in checkIP, so this also covers
    // that dispatch.
    expect( IpUtils::checkIP( '2001:db8::1', '2001:db8::/32' ) )->toBeTrue();
} );

it( 'rejects an ipv6 address outside a range', function (): void {
    expect( IpUtils::checkIP( '2001:db9::1', '2001:db8::/32' ) )->toBeFalse();
} );

it( 'rejects a malformed ipv6 address', function (): void {
    expect( IpUtils::checkIP( '2001:db8:::1', '2001:db8::/32' ) )->toBeFalse();
} );

it( 'rejects an ipv6 netmask outside the valid range', function (): void {
    expect( IpUtils::checkIP( '2001:db8::1', '2001:db8::/129' ) )->toBeFalse()
        ->and( IpUtils::checkIP( '2001:db8::1', '2001:db8::/0' ) )->toBeFalse();
} );
