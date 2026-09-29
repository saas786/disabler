<?php

/**
 * The multiline textarea parser.
 *
 * Shared by every "one per line" setting in the plugin: xmlrpc methods,
 * xmlrpc headers, and the IP allowlist. Whatever this returns is what those
 * features match against, so its edges are their edges.
 *
 * A unit test: no WordPress beyond sanitize_text_field, which the suite has
 * loaded anyway.
 */

declare(strict_types = 1);

use function HBP\Disabler\prepare_multiline_text;

/**
 * array_values() because the helper preserves the keys array_filter() left
 * behind, and every caller only ever iterates the values.
 */
function prepare_text( string $text ): array {
    return array_values( prepare_multiline_text( $text ) );
}

it( 'splits one entry per line', function (): void {
    expect( prepare_text( "wp.newPost\npingback.ping" ) )
        ->toBe( [ 'wp.newPost', 'pingback.ping' ] );
} );

it( 'drops blank lines', function (): void {
    // What a textarea looks like after someone hits enter twice.
    expect( prepare_text( "wp.newPost\n\n\npingback.ping\n" ) )
        ->toBe( [ 'wp.newPost', 'pingback.ping' ] );
} );

it( 'trims surrounding whitespace', function (): void {
    expect( prepare_text( "   wp.newPost   \n\tpingback.ping\t" ) )
        ->toBe( [ 'wp.newPost', 'pingback.ping' ] );
} );

it( 'returns nothing for an empty string', function (): void {
    // The default for every setting that uses this. It must be an empty
    // array, not an array containing an empty string -- a lookup for '' would
    // match nothing useful and could match something unintended.
    expect( prepare_text( '' ) )->toBe( [] )
        ->and( prepare_text( "\n\n  \n" ) )->toBe( [] );
} );

it( 'handles a single entry with no line break', function (): void {
    expect( prepare_text( 'pingback.ping' ) )->toBe( [ 'pingback.ping' ] );
} );

it( 'splits on spaces as well as newlines, which is worth knowing', function (): void {
    // Documenting real behaviour rather than endorsing it. The
    // implementation squishes all whitespace to single spaces and then
    // explodes on ' ', so a space inside an entry splits it in two.
    //
    // Harmless for the current callers: method names, header names and IP
    // addresses never contain spaces. It becomes a bug the moment a setting
    // that allows spaces is added, and it means a comma-and-space separated
    // list silently half-works -- 'a, b' yields 'a,' and 'b'.
    expect( prepare_text( 'wp.newPost pingback.ping' ) )
        ->toBe( [ 'wp.newPost', 'pingback.ping' ] );

    expect( prepare_text( '198.51.100.7, 203.0.113.9' ) )
        ->toBe( [ '198.51.100.7,', '203.0.113.9' ] );
} );

it( 'sanitizes each entry', function (): void {
    // sanitize_text_field is the default, and it strips tags. An entry that
    // survived unsanitized would be compared against a header or method name
    // with markup still in it.
    expect( prepare_text( "<b>wp.newPost</b>\npingback.ping" ) )
        ->toBe( [ 'wp.newPost', 'pingback.ping' ] );
} );

it( 'preserves the order entries were written in', function (): void {
    expect( prepare_text( "c\na\nb" ) )->toBe( [ 'c', 'a', 'b' ] );
} );
