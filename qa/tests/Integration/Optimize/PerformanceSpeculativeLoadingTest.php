<?php

/**
 * performance.disable_speculative_loading.
 *
 * Core decides the configuration, passes it through
 * `wp_speculation_rules_configuration`, and only sanitises afterwards. So the
 * value reaching this filter is whatever core or another plugin last set, and
 * `auto` is still `auto` at that point rather than the mode it resolves to.
 *
 * Asserted through the filter core actually reads, rather than the rendered
 * footer: the printed rules are also gated on being logged out and on pretty
 * permalinks, and a test that renders nothing passes for the wrong reason.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Performance;

mutates( Performance::class );

/**
 * @param array<string, mixed> $values
 */
function boot_speculative( array $values ): Performance {
    $defaults = require plugin_path( 'config/performance.php' );

    store_settings( 'performance', array_merge( $defaults, $values ) );

    return boot_feature( Performance::class );
}

/**
 * The configuration core would end up with, after this plugin has had its say.
 *
 * @param array<string, string>|null $config
 *
 * @return array<string, string>|null
 */
function speculation_config( $config = [
    'mode'      => 'auto',
    'eagerness' => 'auto',
] ) {
    return apply_filters( 'wp_speculation_rules_configuration', $config );
}

it( 'leaves the configuration alone when the control is off', function (): void {
    boot_speculative( [ 'disable_speculative_loading' => 'no' ] );

    // The baseline. Without it, the disabling cases below could pass because
    // nothing is hooked at all.
    expect( speculation_config() )->toBe( [
        'mode'      => 'auto',
        'eagerness' => 'auto',
    ] );
} );

it( 'switches speculative loading off entirely', function (): void {
    boot_speculative( [ 'disable_speculative_loading' => 'all' ] );

    // Null is core's own signal for "do not speculate", so this is the same
    // state a logged-in request already produces.
    expect( speculation_config() )->toBeNull();
} );

it( 'downgrades prerender to prefetch', function (): void {
    boot_speculative( [ 'disable_speculative_loading' => 'prerender' ] );

    expect( speculation_config( [
        'mode'      => 'prerender',
        'eagerness' => 'moderate',
    ] ) )
        // Eagerness is deliberately untouched: the setting objects to running
        // the page, not to how readily it is fetched.
        ->toBe( [
            'mode'      => 'prefetch',
            'eagerness' => 'moderate',
        ] );
} );

it( 'leaves prefetch alone when downgrading prerender', function (): void {
    boot_speculative( [ 'disable_speculative_loading' => 'prerender' ] );

    expect( speculation_config( [
        'mode'      => 'prefetch',
        'eagerness' => 'conservative',
    ] ) )
        ->toBe( [
            'mode'      => 'prefetch',
            'eagerness' => 'conservative',
        ] );
} );

it( 'passes null through when downgrading prerender', function (): void {
    boot_speculative( [ 'disable_speculative_loading' => 'prerender' ] );

    // Core has already decided not to speculate -- for a logged-in user, or a
    // site without pretty permalinks. Returning an array here would turn
    // speculative loading on for a request that was not going to have it.
    expect( speculation_config( null ) )->toBeNull();
} );

it( 'resolves auto before deciding whether to downgrade', function (): void {
    boot_speculative( [ 'disable_speculative_loading' => 'prerender' ] );

    // `auto` means "core decides", and since 7.1 a host can point that at
    // prerender through WP_SPECULATIVE_LOADING_DEFAULT_MODE. Treating auto as
    // safe would let exactly the mode this setting exists to prevent through
    // on those hosts.
    $default = function_exists( 'wp_get_speculation_rules_default_configuration' )
        ? wp_get_speculation_rules_default_configuration()['mode']
        : 'prefetch';

    $expected = 'prerender' === $default ? 'prefetch' : 'auto';

    expect( speculation_config()['mode'] )->toBe( $expected );
} );
