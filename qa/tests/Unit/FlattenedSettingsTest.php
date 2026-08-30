<?php

/**
 * The flat -> nested conversion, over every setting the plugin declares.
 *
 * No WordPress. The migration depends on nothing, which is what makes it
 * cheap enough to run over the whole key set on every commit.
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\Update\FlattenedSettings;
use Hybrid\Tools\Arr;

beforeEach( function (): void {
    $this->migration = new FlattenedSettings;
    $this->converted = $this->migration->convert( legacy_option() );
} );

it( 'converts every declared setting to a readable dotted key', function ( string $dotted, string $legacy ): void {
    // Read the way the plugin reads: Arr::get, dotted, against the nested
    // store. Asserting on the array shape instead would pass on a conversion
    // that nests correctly but under a section nothing ever looks up.
    expect( Arr::get( $this->converted, $dotted, '__MISSING__' ) )
        ->toBe( sentinel( $dotted ) );
} )->with( declared_settings() );

it( 'files nothing under the empty string', function (): void {
    // A key equal to its own section name has nothing left after the prefix
    // is stripped. Split anyway, it lands at '' -- unreachable, and nothing
    // will ever clean it up.
    expect( array_keys( $this->converted ) )->not->toContain( '' );

    foreach ( $this->converted as $value ) {
        if ( is_array( $value ) ) {
            expect( array_keys( $value ) )->not->toContain( '' );
        }
    }
} );

it( 'leaves an already converted option alone', function (): void {
    // The routine can be interrupted, and the update chain can re-run it.
    // Both are survivable only if a second pass is a no-op.
    expect( $this->migration->convert( $this->converted ) )->toBe( $this->converted );
} );

it( 'carries across a key no section matches', function (): void {
    $converted = $this->migration->convert( [ 'a_key_from_a_version_we_never_saw' => 'keep me' ] );

    // Silently dropping a user's data is the one failure that cannot be
    // undone, so an unrecognised key is kept rather than discarded.
    expect( $converted )->toBe( [ 'a_key_from_a_version_we_never_saw' => 'keep me' ] );
} );

it( 'splits at the longest matching section', function (): void {
    // `admin_bar` contains an underscore. Matched shortest-first, or against
    // a section list that happened to put `admin` first, this would split in
    // the wrong place and file the value where nothing reads it.
    $converted = $this->migration->convert( [ 'admin_bar_admin_bar_roles' => [ 'subscriber' ] ] );

    expect( $converted )->toBe( [ 'admin_bar' => [ 'admin_bar_roles' => [ 'subscriber' ] ] ] );
} );

it( 'handles a dynamic key whose name contains a section name', function (): void {
    // Revision limits are one key per registered post type, so the tail is
    // arbitrary site data -- including, on someone's install, a post type
    // called `media` or `updates`.
    $converted = $this->migration->convert( [
        'revisions_revisions_limit_media'   => 5,
        'revisions_revisions_limit_updates' => 3,
    ] );

    expect( Arr::get( $converted, 'revisions.revisions_limit_media' ) )->toBe( 5 )
        ->and( Arr::get( $converted, 'revisions.revisions_limit_updates' ) )->toBe( 3 );
} );

it( 'preserves a stored value that is falsy', function (): void {
    // 0, '' and false are stored values, not absent ones. A conversion that
    // treated them as missing would hand the read path a config default --
    // and for this plugin the defaults mostly mean "not disabled".
    $converted = $this->migration->convert( [
        'backend_disable_self_ping'      => 0,
        'backend_disable_self_ping_urls' => '',
    ] );

    expect( $converted['backend'] )->toBe( [
        'disable_self_ping'      => 0,
        'disable_self_ping_urls' => '',
    ] );
} );

it( 'keeps a scalar stored under a bare section name', function (): void {
    // Nothing else claims the slot, so there is no reason to discard it --
    // it is carried across like any other key the rule does not understand.
    expect( $this->migration->convert( [ 'updates' => 'junk' ] ) )
        ->toBe( [ 'updates' => 'junk' ] );
} );

it( 'lets real settings win a bare section name', function ( array $stored ): void {
    // A scalar named after a section collides with the section its prefixed
    // keys nest into, and an array holds one value per key. The result has to
    // be the same either way round, so both orders are asserted against the
    // same expectation: ahead of the prefixed keys the scalar would otherwise
    // be indexed into as if it were an array, behind them it would overwrite
    // the section already built.
    expect( $this->migration->convert( $stored ) )->toBe( [
        'updates' => [
            'core_updates'    => 'allow_minor_core_auto_updates',
            'disable_updates' => 'selective',
        ],
    ] );
} )->with( [
    'scalar last'  => [
        [
            'updates_core_updates'    => 'allow_minor_core_auto_updates',
            'updates_disable_updates' => 'selective',
            'updates'                 => 'junk',
        ],
    ],
    'scalar first' => [
        [
            'updates'                 => 'junk',
            'updates_core_updates'    => 'allow_minor_core_auto_updates',
            'updates_disable_updates' => 'selective',
        ],
    ],
] );

it( 'merges a section left nested by an earlier run', function (): void {
    // The other half of the same branch: an array under a bare section name
    // is not malformed, it is a partially converted option. It merges, in
    // either order.
    expect( $this->migration->convert( [
        'updates'                => [ 'core_updates' => 'x' ],
        'updates_plugin_updates' => 'manual',
    ] ) )->toBe( [
        'updates' => [
            'core_updates'   => 'x',
            'plugin_updates' => 'manual',
        ],
    ] );
} );

it( 'leaves a converted option alone even when one collided', function (): void {
    // The suite's other idempotency check runs on a clean fixture, which
    // never enters this branch. A second pass has to be a no-op here too:
    // the update chain re-runs a half-finished routine.
    $converted = $this->migration->convert( [
        'updates_core_updates' => 'allow_minor_core_auto_updates',
        'updates'              => 'junk',
    ] );

    expect( $this->migration->convert( $converted ) )->toBe( $converted );
} );

it( 'has no section prefix that shadows another', function (): void {
    // The reason SECTIONS can be in any order. If some later edit adds a
    // section that is a prefix of an existing one -- 'admin' alongside
    // 'admin_bar', say -- the first match stops being the only match, and
    // 'admin_bar_roles' starts filing under 'admin' as 'bar_roles'. That is
    // silent, and it is data loss. Fail here instead.
    $sections = ( new ReflectionClass( FlattenedSettings::class ) )
        ->getConstant( 'SECTIONS' );

    foreach ( $sections as $section ) {
        foreach ( $sections as $other ) {
            if ( $section === $other ) {
                continue;
            }

            expect( str_starts_with( $other, $section . '_' ) )->toBeFalse(
                "'{$section}' is a prefix of '{$other}'"
            );
        }
    }
} );

it( 'covers every section the plugin currently ships', function (): void {
    // Not a derivation -- SECTIONS stays frozen at the 4.0.4 shape on
    // purpose. This only catches the opposite mistake: a section added to
    // the plugin after this migration was written, whose keys a 4.0.4 user
    // could plausibly already hold. Removals are expected and ignored.
    $shipped = array_keys( require plugin_path( 'config/tabs.php' ) );
    $known   = ( new ReflectionClass( FlattenedSettings::class ) )
        ->getConstant( 'SECTIONS' );

    expect( array_diff( $shipped, $known ) )->toBe( [] );
} );
