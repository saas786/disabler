<?php

/**
 * What the settings screen puts on the page.
 *
 * These cover regressions that only showed up by looking at the rendered
 * screen next to the old one: choices that had been frozen into config, and a
 * section note that stopped being printed at all. Neither breaks a read, so
 * nothing else in this suite would have caught them.
 */

declare(strict_types = 1);

use HBP\Disabler\Plugin;
use HBP\Settings\Ui\PanelFactory;
use function HBP\Disabler\container;

function screenPanel(): HBP\Settings\Ui\Panel {
    return container()->resolve( PanelFactory::class )
        ->make( 'disabler', Plugin::SETTINGS_OPTION );
}

function definitions(): HBP\Settings\Ui\Definitions {
    return screenPanel()->definitions();
}

it( 'offers every role the site has, not a fixed list', function ( string $key ): void {
    $choices = definitions()->all()[ $key ]->choices();

    // wp_roles() is the only source that knows about the roles a site's own
    // plugins add -- shop managers, SEO editors. A list written into config
    // silently drops them, and the setting then cannot express what the admin
    // wants.
    expect( $choices )->toBe( wp_roles()->get_names() )
        ->and( $choices )->toHaveKey( 'administrator' );
} )->with( [
    'restapi.application_passwords_roles',
    'admin_bar.admin_bar_roles',
] );

it( 'opens the tab with its note', function ( string $tab ): void {
    $controls = definitions()->all();
    $key      = "{$tab}.note";

    expect( $controls )->toHaveKey( $key )
        ->and( $controls[ $key ]->type() )->toBe( 'html' )
        ->and( (string) $controls[ $key ]->get( 'content', '' ) )->not->toBe( '' );

    // First in priority order, so it renders above the controls it explains.
    foreach ( $controls as $other => $definition ) {
        if ( $other === $key || $definition->tab() !== $tab ) {
            continue;
        }

        expect( $controls[ $key ]->priority() )->toBeLessThan( $definition->priority() );
    }
} )->with( [ 'feeds', 'privacy', 'revisions', 'tracking', 'xmlrpc' ] );

it( 'stores nothing through a note', function (): void {
    // An html control is not a setting. It has no default, so the invariant
    // that every control declares one must keep skipping it -- and a posted
    // value carrying its key must not write.
    $stored = screenPanel()->sanitize( [ 'privacy.note' => '<script>x</script>' ] );

    expect( $stored )->not->toHaveKey( 'note' )
        ->and( $stored['privacy']['note'] ?? null )->toBeNull();
} );

it( 'builds a limit control for every post type that supports revisions', function (): void {
    $keys = array_keys( definitions()->all() );

    // Computed at read time from the registered post types, which is why the
    // screen cannot be built before init.
    foreach ( array_keys( HBP\Disabler\get_revision_post_types() ) as $type ) {
        expect( $keys )->toContain( "revisions.revisions_limit_{$type}" );
    }
} );
