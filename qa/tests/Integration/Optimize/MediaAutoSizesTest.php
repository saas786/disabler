<?php

/**
 * The two media controls the lazy loading slice did not cover.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Media;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Media::class );

/**
 * @param array<string, mixed> $values
 */
function boot_media( array $values ): Media {
    $defaults = require plugin_path( 'config/media.php' );

    store_settings( 'media', array_merge( $defaults, $values ) );

    return boot_feature( Media::class );
}

/**
 * Put core's containment stylesheet in the queue.
 *
 * reset_styles() drops WP_Styles after every test, and core only registers
 * this handle during a real wp_enqueue_scripts pass, so enqueueing a bare
 * handle can leave nothing for wp_style_is() to find. Registering it first
 * makes the starting state explicit instead of inherited.
 */
function enqueue_containment_style(): void {
    if ( ! wp_style_is( 'wp-img-auto-sizes-contain', 'registered' ) ) {
        wp_register_style( 'wp-img-auto-sizes-contain', false, [], null );
    }

    wp_enqueue_style( 'wp-img-auto-sizes-contain' );
}

it( 'adds auto sizes when the control is off', function (): void {
    boot_media( [ 'disable_wp_img_tag_add_auto_sizes' => 0 ] );

    expect( apply_filters( 'wp_img_tag_add_auto_sizes', true ) )->toBeTrue();
} );

it( 'stops core adding sizes auto to images', function (): void {
    boot_media( [ 'disable_wp_img_tag_add_auto_sizes' => 1 ] );

    expect( apply_filters( 'wp_img_tag_add_auto_sizes', true ) )->toBeFalse();
} );

it( 'dequeues the containment stylesheet', function (): void {
    // This one hangs off wp_enqueue_scripts rather than init, so booting
    // initHooks alone would never reach it -- the enqueue hook has to fire.
    $media = boot_media( [ 'disable_wp_img_auto_sizes_contain' => 1 ] );

    enqueue_containment_style();

    ( new ReflectionMethod( Media::class, 'wpEnqueueScripts' ) )->invoke( $media );

    expect( wp_style_is( 'wp-img-auto-sizes-contain', 'enqueued' ) )->toBeFalse();
} );

it( 'leaves the containment stylesheet alone when the control is off', function (): void {
    $media = boot_media( [ 'disable_wp_img_auto_sizes_contain' => 0 ] );

    enqueue_containment_style();

    // Precondition, not decoration: the off case failed on the first run with
    // the style already absent, which would have made the on case above pass
    // for the wrong reason. If registration is what is missing, this is the
    // assertion that says so.
    expect( wp_style_is( 'wp-img-auto-sizes-contain', 'enqueued' ) )->toBeTrue();

    ( new ReflectionMethod( Media::class, 'wpEnqueueScripts' ) )->invoke( $media );

    expect( wp_style_is( 'wp-img-auto-sizes-contain', 'enqueued' ) )->toBeTrue();
} );
