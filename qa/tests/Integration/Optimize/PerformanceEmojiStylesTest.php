<?php

/**
 * The emoji styles half of performance.disable_emojis.
 *
 * Its own file because these two cases fire do_action( 'wp_enqueue_scripts' ),
 * and the AccessiblePrivateMethods proxy resolves its callbacks against the
 * current filter. Left in the main emoji file, that do_action leaked into a
 * later test, which then received the string "wp_enqueue_scripts" where its
 * array of resource hints should have been. Separating the file keeps the
 * action out of the other tests' way.
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
function boot_emoji_styles( array $values ): void {
    $defaults = require plugin_path( 'config/performance.php' );

    store_settings( 'performance', array_merge( $defaults, $values ) );

    boot_feature( Performance::class );
}

it( 'stops core enqueuing emoji styles when emojis are disabled', function (): void {
    // The bug this file found. Core enqueues its emoji CSS through
    // wp_enqueue_emoji_styles, and default-filters.php notes that the same
    // function unhooks print_emoji_styles -- so removing only the latter, as
    // this feature used to, left <style id="wp-emoji-styles-inline-css"> in
    // the head of every page with the control switched on.
    boot_emoji_styles( [ 'disable_emojis' => 1 ] );

    do_action( 'wp_enqueue_scripts' );

    // The second half is what keeps the first honest. wp_enqueue_emoji_styles
    // unhooks print_emoji_styles on its first run and early-returns ever
    // after, so a suite that leaked hooks made the handle un-enqueueable and
    // this case green for a feature that did nothing. The hook check says the
    // feature acted; the handle check says the action had its effect.
    expect( wp_style_is( 'wp-emoji-styles', 'enqueued' ) )->toBeFalse()
        ->and( has_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' ) )->toBeFalse();
} );

it( 'enqueues emoji styles when the control is off', function (): void {
    boot_emoji_styles( [ 'disable_emojis' => 0 ] );

    do_action( 'wp_enqueue_scripts' );

    expect( wp_style_is( 'wp-emoji-styles', 'enqueued' ) )->toBeTrue();
} );
