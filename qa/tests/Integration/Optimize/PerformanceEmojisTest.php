<?php

/**
 * performance.disable_emojis, asserted through what core actually prints.
 *
 * A different shape from the lazy loading slice. That one added filters, so
 * the effect was visible in a value passing through a pipeline. This one
 * mostly calls remove_action() against core's own handlers, so the effect is
 * visible only in a value that stops being transformed -- and an assertion that
 * something is absent is worthless without its pair proving it was ever
 * there. Every case below is paired for that reason.
 *
 * Removals also make the hook restore load-bearing in a way the previous
 * slice did not: this feature strips core's callbacks off shared hooks like
 * wp_head and the_content_feed. Without restore_hooks() putting them back,
 * the first emoji test to run would leave every later test in the suite
 * looking at a WordPress with pieces missing.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Performance;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Performance::class );

/**
 * Boot Performance with one control changed and every sibling at its declared
 * default.
 *
 * initHooks() runs the whole section -- emojis, heartbeat, embeds, dashboard
 * widgets -- so storing only the key under test would leave the siblings
 * reading from whatever the previous test stored. Merging over the declared
 * defaults keeps each case to a single variable.
 *
 * @param array<string, mixed> $values
 */
function boot_performance( array $values ): void {
    $defaults = require plugin_path( 'config/performance.php' );

    store_settings( 'performance', array_merge( $defaults, $values ) );

    boot_feature( Performance::class );
}

/**
 * A string carrying an emoji, as a feed item would.
 *
 * Core converts it to an <img> pointing at its own emoji CDN, which is the
 * behaviour disable_emojis removes.
 */
function content_with_emoji(): string {
    return '<p>Shipped ' . "\u{1F600}" . '</p>';
}

it( 'staticizes emoji in feed content when the control is off', function (): void {
    // The baseline. wp_staticize_emoji is a plain filter with no per-request
    // state, unlike the head printer -- which guards itself with a static
    // flag, so only whichever test calls wp_head() first in a process can
    // ever observe it. That made it useless as a seam here.
    boot_performance( [ 'disable_emojis' => 0 ] );

    expect( apply_filters( 'the_content_feed', content_with_emoji() ) )
        ->toContain( '<img' );
} );

it( 'leaves feed content alone when emojis are disabled', function (): void {
    boot_performance( [ 'disable_emojis' => 1 ] );

    $filtered = apply_filters( 'the_content_feed', content_with_emoji() );

    expect( $filtered )->not->toContain( '<img' )
        ->and( $filtered )->toBe( content_with_emoji() );
} );

it( 'staticizes emoji in comment feeds when the control is off', function (): void {
    // A second hook carrying the same callback. Covering only the_content_feed
    // would let comment_text_rss be dropped without a failure.
    boot_performance( [ 'disable_emojis' => 0 ] );

    expect( apply_filters( 'comment_text_rss', content_with_emoji() ) )
        ->toContain( '<img' );
} );

it( 'leaves comment feed content alone when emojis are disabled', function (): void {
    boot_performance( [ 'disable_emojis' => 1 ] );

    expect( apply_filters( 'comment_text_rss', content_with_emoji() ) )
        ->not->toContain( '<img' );
} );

it( 'leaves the tinymce emoji plugin registered when the control is off', function (): void {
    boot_performance( [ 'disable_emojis' => 0 ] );

    expect( apply_filters( 'tiny_mce_plugins', [ 'wpemoji', 'wplink' ] ) )
        ->toContain( 'wpemoji' );
} );

it( 'strips the tinymce emoji plugin when emojis are disabled', function (): void {
    boot_performance( [ 'disable_emojis' => 1 ] );

    $plugins = apply_filters( 'tiny_mce_plugins', [ 'wpemoji', 'wplink' ] );

    // wplink is the control: the callback uses array_diff, and a bug that
    // returned an empty array would satisfy a bare "wpemoji is gone" check
    // while quietly disabling every editor plugin on the site.
    expect( $plugins )->not->toContain( 'wpemoji' )
        ->and( $plugins )->toContain( 'wplink' );
} );

it( 'drops the s.w.org resource hint when emojis are disabled', function (): void {
    boot_performance( [ 'disable_emojis' => 1 ] );

    $hints = apply_filters(
        'wp_resource_hints',
        [
            '//s.w.org',
            [ 'href' => '//s.w.org' ],
            'https://fonts.example.org',
            [ 'href' => 'https://cdn.example.org' ],
        ],
        'dns-prefetch'
    );

    // Serialised rather than walked: something else on this filter returns a
    // string rather than the array it was handed, and the shape of the value
    // is not what this test is about. Both the string and the array form of a
    // hint carry s.w.org, and the callback handles them on separate branches,
    // so both still have to disappear while the unrelated hosts survive.
    $flat = wp_json_encode( $hints );

    expect( $flat )->not->toContain( 's.w.org' )
        ->and( $flat )->toContain( 'fonts.example.org' )
        ->and( $flat )->toContain( 'cdn.example.org' );
} );

it( 'leaves resource hints untouched when the control is off', function (): void {
    boot_performance( [ 'disable_emojis' => 0 ] );

    $hints = apply_filters( 'wp_resource_hints', [ '//s.w.org' ], 'dns-prefetch' );

    expect( $hints )->toContain( '//s.w.org' );
} );

it( 'restores core callbacks between tests', function (): void {
    // Not a test of the plugin -- a test of the harness the rest of this file
    // depends on. If restore_hooks() ever stops putting core's callbacks
    // back, the failures show up as unrelated tests behaving strangely in a
    // particular order. Better to fail here, where the cause is named.
    boot_performance( [ 'disable_emojis' => 1 ] );

    expect( has_filter( 'the_content_feed', 'wp_staticize_emoji' ) )->toBeFalse();

    restore_hooks();

    expect( has_filter( 'the_content_feed', 'wp_staticize_emoji' ) )->not->toBeFalse();
} );
