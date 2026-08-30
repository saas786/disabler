<?php

/**
 * media.disable_core_lazy_loading, asserted through the rendered HTML.
 *
 * The seam is WordPress' own content pipeline, not the plugin's hooks. What
 * the control promises is that an image comes out of `the_content` without a
 * `loading` attribute, or with `loading="eager"` -- so that is what is
 * asserted. Checking `has_filter()` instead would pass just as happily with
 * the filter on the wrong hook, at a priority core overrides, or returning a
 * value core ignores, and would break on a rename that changed no behaviour.
 *
 * Every state is paired with the control off. Without the off case a passing
 * assertion cannot be told apart from one that was already true: core omits
 * the `loading` attribute on its own in several situations, and a test that
 * only ever runs with the control on would happily report success while the
 * plugin did nothing at all.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Media;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Media::class );

/**
 * Content holding three images, each with the dimensions core needs to make
 * its own loading decisions.
 *
 * Three, because core deliberately treats the first contentful image
 * differently -- it is the likely LCP element, so it gets `fetchpriority` and
 * no lazy loading. Asserting on that image would compare the plugin's effect
 * against a case where core already omits the attribute, and the test would
 * pass whether or not the plugin ran.
 */
function content_with_images(): string {
    $img = '<img src="https://example.org/wp-content/uploads/%d.jpg" alt="" width="1200" height="800" />';

    return sprintf( "<p>{$img}</p><p>{$img}</p><p>{$img}</p>", 1, 2, 3 );
}

/**
 * The rendered `loading` attribute of the last image in the content.
 *
 * Returns null when core emitted no `loading` attribute at all, which is a
 * different outcome from `loading="eager"` and the two must not be conflated:
 * 'yes' removes the attribute, 'eager' keeps it and changes its value.
 */
function last_image_loading_attr( string $html ): ?string {
    preg_match_all( '/<img\b[^>]*>/', $html, $tags );

    $last = end( $tags[0] );

    expect( $last )->not->toBeFalse( 'no <img> survived the content filter' );

    return preg_match( '/\bloading=["\']([^"\']+)["\']/', $last, $m ) ? $m[1] : null;
}

/**
 * Store the control, boot Media against it, and render the content.
 */
function render_content_with( string $mode ): string {
    store_settings( 'media', [ 'disable_core_lazy_loading' => $mode ] );

    boot_feature( Media::class );

    return wp_filter_content_tags( content_with_images(), 'the_content' );
}

it( 'lazy loads images when the control is off', function (): void {
    // The baseline every other case is measured against. If this ever stops
    // holding, core changed its own defaults and the cases below are no
    // longer proving what they claim -- so this failing is informative, not
    // noise to be relaxed away.
    expect( last_image_loading_attr( render_content_with( 'no' ) ) )->toBe( 'lazy' );
} );

it( 'emits no loading attribute when core lazy loading is disabled', function (): void {
    expect( last_image_loading_attr( render_content_with( 'yes' ) ) )->toBeNull();
} );

it( 'forces eager loading when the control is set to eager', function (): void {
    expect( last_image_loading_attr( render_content_with( 'eager' ) ) )->toBe( 'eager' );
} );

it( 'leaves iframes lazy loaded when the control is off', function (): void {
    store_settings( 'media', [ 'disable_core_lazy_loading' => 'no' ] );

    boot_feature( Media::class );

    $html = wp_filter_content_tags(
        '<p><iframe src="https://example.org/a" width="640" height="360"></iframe></p>'
        . '<p><iframe src="https://example.org/b" width="640" height="360"></iframe></p>',
        'the_content'
    );

    expect( $html )->toContain( 'loading="lazy"' );
} );

it( 'forces eager loading on iframes too', function (): void {
    // The eager branch registers two filters, one for images and one for
    // iframes. Covering only images would let the iframe filter be dropped
    // entirely without a single test noticing.
    store_settings( 'media', [ 'disable_core_lazy_loading' => 'eager' ] );

    boot_feature( Media::class );

    $html = wp_filter_content_tags(
        '<p><iframe src="https://example.org/a" width="640" height="360"></iframe></p>'
        . '<p><iframe src="https://example.org/b" width="640" height="360"></iframe></p>',
        'the_content'
    );

    expect( $html )->toContain( 'loading="eager"' )
        ->and( $html )->not->toContain( 'loading="lazy"' );
} );

it( 'falls back to the declared default when nothing is stored', function (): void {
    // The declared default is read from config rather than written here, so
    // that changing the default in config makes this test follow rather than
    // quietly contradict it.
    $default = ( require plugin_path( 'config/media.php' ) )['disable_core_lazy_loading'];

    expect( $default )->toBe( 'no' );

    delete_option( HBP\Disabler\Plugin::SETTINGS_OPTION );

    boot_feature( Media::class );

    expect( last_image_loading_attr( wp_filter_content_tags( content_with_images(), 'the_content' ) ) )
        ->toBe( 'lazy' );
} );
