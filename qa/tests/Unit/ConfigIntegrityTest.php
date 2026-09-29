<?php

/**
 * Invariants between the config files and the migration.
 *
 * These are the tests that earn their keep later. Adding a section is a
 * two-file change -- a defaults file and a controls file -- and nothing at
 * runtime complains if the migration's section list is not the third. The
 * symptom is a settings screen that works perfectly on a fresh install and
 * silently loses that section's values on every upgrade.
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\Update\FlattenedSettings;

it( 'knows about every config section', function ( string $section ): void {
    expect( migration_sections() )->toContain( $section );
} )->with( fn() => array_keys( declared_sections() ) );

it( 'lists no section that no longer exists', function (): void {
    expect( array_diff( migration_sections(), array_keys( declared_sections() ) ) )->toBe( [] );
} );

it( 'orders the section list so no prefix hides a longer section', function (): void {
    $sections = migration_sections();

    // The migration returns the first section that matches, so when one
    // section name is a prefix of another the longer one must come first.
    // `admin` and `admin_bar` are the shape that bites; today only the
    // second exists, but this fails the day the first is added.
    foreach ( $sections as $i => $section ) {
        foreach ( array_slice( $sections, $i + 1 ) as $later ) {
            expect( str_starts_with( $section, $later . '_' ) )->toBeFalse(
                "'{$later}' is listed after '{$section}', which it prefixes"
            );
        }
    }
} );

it( 'declares a control for every default', function ( string $section ): void {
    $controls = require plugin_path( "config/controls/{$section}.php" );

    // Control keys are dotted and may be grouped under a closure that
    // computes them, so only the static ones can be checked without booting
    // WordPress. The integration suite covers the computed ones.
    $declared = array_filter( array_keys( $controls ), static fn( $key ): bool => is_string( $key ) );

    foreach ( array_keys( declared_sections()[ $section ] ) as $key ) {
        $dotted = "{$section}.{$key}";

        // Dynamic keys have no static declaration by definition.
        if ( str_contains( $dotted, 'revisions_limit_' ) ) {
            continue;
        }

        // One needle only. toContain is variadic over needles, so a second
        // argument is asserted as another needle rather than used as a
        // failure message. The dataset name already says which section failed.
        expect( $declared )->toContain( $dotted );
    }
} )->with( fn() => array_keys( declared_sections() ) );

it( 'gives every static control a default', function (): void {
    $missing = [];

    foreach ( glob( plugin_path( 'config/controls/*.php' ) ) as $file ) {
        foreach ( require $file as $key => $declaration ) {
            // A closure computes its keys at runtime; integration covers it.
            if ( $declaration instanceof Closure || ! str_contains( (string) $key, '.' ) ) {
                continue;
            }

            [ $section, $name ] = explode( '.', (string) $key, 2 );

            // `html` controls display text and store nothing.
            if ( 'html' === ( $declaration['type'] ?? '' ) ) {
                continue;
            }

            if ( ! array_key_exists( $name, declared_sections()[ $section ] ?? [] ) ) {
                $missing[] = $key;
            }
        }
    }

    // Without a default a control answers with whatever the caller passed,
    // which differs between call sites and is nobody's declared intent.
    expect( $missing )->toBe( [] );
} );

it( 'has a migration that matches the class it tests', function (): void {
    expect( class_exists( FlattenedSettings::class ) )->toBeTrue();
} );
