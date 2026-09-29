<?php

/**
 * The editor controls that unhook core's content filters.
 *
 * All three are asserted through the text core produces, not through
 * has_filter(). These features work by calling remove_filter() with an
 * explicit priority, and a priority that stops matching removes nothing
 * while reporting nothing -- the exact failure a hook-registration assertion
 * cannot see, because it would be checking the same number the production
 * code just used.
 *
 * disable_texturization unhooks wptexturize from twenty-two filters. Two are
 * covered here rather than all of them: the point is that the loop runs and
 * that it reaches more than one hook, and a test naming every filter would
 * be a copy of the array in the source, failing whenever the list is edited
 * rather than whenever the behaviour changes.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Editor;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Editor::class );

/**
 * @param array<string, mixed> $values
 */
function boot_editor( array $values ): void {
    $defaults = require plugin_path( 'config/editor.php' );

    store_settings( 'editor', array_merge( $defaults, $values ) );

    // Editor names its init method initializeHooks, not initHooks.
    boot_feature( Editor::class, 'initializeHooks' );
}

it( 'curls quotes in content when the control is off', function (): void {
    boot_editor( [ 'disable_texturization' => 0 ] );

    // The baseline: a straight apostrophe comes back as a curly one. If this
    // stops holding, core changed and the case below proves nothing.
    expect( apply_filters( 'the_content', "<p>it's here</p>" ) )
        ->toContain( '&#8217;' );
} );

it( 'leaves quotes straight when texturization is disabled', function (): void {
    boot_editor( [ 'disable_texturization' => 1 ] );

    $filtered = apply_filters( 'the_content', "<p>it's here</p>" );

    expect( $filtered )->not->toContain( '&#8217;' )
        ->and( $filtered )->toContain( "it's here" );
} );

it( 'stops texturizing titles as well as content', function (): void {
    // A second hook off the same loop. Covering only the_content would let
    // the loop be reduced to one filter without a failure.
    boot_editor( [ 'disable_texturization' => 1 ] );

    expect( apply_filters( 'the_title', "it's here" ) )->not->toContain( '&#8217;' );
} );

it( 'corrects WordPress capitalisation when the control is off', function (): void {
    boot_editor( [ 'disable_capital_p' => 0 ] );

    expect( apply_filters( 'the_title', 'built on WordPress' ) )
        ->toContain( 'WordPress' );
} );

it( 'leaves WordPress capitalisation alone when the control is on', function (): void {
    boot_editor( [ 'disable_capital_p' => 1 ] );

    expect( apply_filters( 'the_title', 'built on WordPress' ) )
        ->toContain( 'WordPress' );
} );

it( 'leaves comment capitalisation alone too', function (): void {
    // capital_P_dangit sits at priority 31 on comment_text and 11 everywhere
    // else, and the production code branches on exactly that. If the two ever
    // drift apart, this is the case that notices -- the_title above would
    // still pass.
    boot_editor( [ 'disable_capital_p' => 1 ] );

    expect( apply_filters( 'comment_text', 'built on WordPress' ) )
        ->toContain( 'WordPress' );
} );

it( 'wraps content in paragraphs when the control is off', function (): void {
    boot_editor( [ 'disable_autop' => 0 ] );

    expect( apply_filters( 'the_content', "one\n\ntwo" ) )->toContain( '<p>' );
} );

it( 'leaves line breaks unwrapped when autop is disabled', function (): void {
    boot_editor( [ 'disable_autop' => 1 ] );

    $filtered = apply_filters( 'the_content', "one\n\ntwo" );

    // wpautop is what turns a blank line into a paragraph. Nothing else on
    // the_content does, so its absence is the whole effect.
    expect( $filtered )->not->toContain( '<p>' )
        ->and( $filtered )->toContain( 'one' )
        ->and( $filtered )->toContain( 'two' );
} );

it( 'stops core loading remote block patterns', function (): void {
    boot_editor( [ 'disable_remote_block_patterns' => 1 ] );

    // The control with a network cost: core fetches these from
    // api.wordpress.org on request.
    expect( apply_filters( 'should_load_remote_block_patterns', true ) )->toBeFalse();
} );

it( 'leaves remote block patterns alone when the control is off', function (): void {
    boot_editor( [ 'disable_remote_block_patterns' => 0 ] );

    expect( apply_filters( 'should_load_remote_block_patterns', true ) )->toBeTrue();
} );

it( 'unhooks the classic theme styles', function (): void {
    boot_editor( [ 'disable_classic_theme_styles' => 1 ] );

    expect( has_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' ) )->toBeFalse()
        ->and( has_filter( 'block_editor_settings_all', 'wp_add_editor_classic_theme_styles' ) )->toBeFalse();
} );

it( 'leaves the classic theme styles alone when the control is off', function (): void {
    boot_editor( [ 'disable_classic_theme_styles' => 0 ] );

    expect( has_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' ) )->not->toBeFalse();
} );
