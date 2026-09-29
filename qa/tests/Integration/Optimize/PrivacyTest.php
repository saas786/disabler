<?php

/**
 * The privacy section: the generator meta tag and the outgoing user agent.
 *
 * Both are small, both are pure, and neither needs a request context -- which
 * is why this section is covered in full while larger ones are not.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Privacy;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( Privacy::class );

/**
 * @param array<string, mixed> $values
 */
function boot_privacy( array $values ): void {
    $defaults = require plugin_path( 'config/privacy.php' );

    store_settings( 'privacy', array_merge( $defaults, $values ) );

    boot_feature( Privacy::class );
}

it( 'prints the generator meta tag when the control is off', function (): void {
    boot_privacy( [ 'disable_wp_generator' => 0 ] );

    expect( rendered_head() )->toContain( 'name="generator"' );
} );

it( 'removes the generator meta tag', function (): void {
    boot_privacy( [ 'disable_wp_generator' => 1 ] );

    expect( rendered_head() )->not->toContain( 'name="generator"' );
} );

it( 'leaves the user agent alone when the control is off', function (): void {
    boot_privacy( [ 'fake_user_agent_value' => 0 ] );

    $agent = apply_filters( 'http_headers_useragent', 'WordPress/7.1; http://example.org', '' );

    expect( $agent )->toContain( 'example.org' );
} );

it( 'strips the site url out of the outgoing user agent', function (): void {
    // The point of the control: core's default agent carries the site's URL
    // to every host it talks to.
    boot_privacy( [ 'fake_user_agent_value' => 1 ] );

    $agent = apply_filters( 'http_headers_useragent', 'WordPress/7.1; http://example.org', '' );

    expect( $agent )->not->toContain( 'example.org' )
        ->and( $agent )->toStartWith( 'WordPress/' );
} );
