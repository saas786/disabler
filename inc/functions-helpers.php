<?php

/**
 * Helper functions for handling plugin options.
 */

namespace HBP\Disabler;

use HBP\Settings\SettingsFactory;
use Hybrid\Core\Application;
use Hybrid\Tools\Str;

/**
 * Read one effective setting for this plugin.
 *
 * The package deliberately exports no global helper and requires the
 * namespace at every call, because two HBP packages sharing a global name
 * fatal on redeclare. Owning the short name is the consumer's call, so this
 * is where it is made -- the namespace is fixed here and nowhere else.
 *
 * Reads fall through object meta, the stored option, the active preset and
 * then config, so a key with no stored value still answers with its declared
 * default.
 *
 * The option name is passed explicitly. Left off, the factory derives
 * `disabler_settings` from the namespace -- the pre-4.0 option, which the
 * 4.0.0-RC.2 routine deletes. Reads would then miss every stored value and
 * silently answer with config defaults, which for this plugin means nothing
 * is disabled.
 *
 * The factory comes from this plugin's own container rather than through
 * HBP\Settings\settings(), which reaches for Hybrid\app() and therefore
 * Container::getInstance(). That returns `static::$instance ??= new static`:
 * whichever container happened to claim the global slot first, or a bare one
 * with no config bound and no deferred providers, which auto-wires
 * SettingsFactory with a null config and fatals. Resolving from the container
 * this plugin registered its providers with does not depend on load order or
 * on what else on the site is built on Hybrid.
 */
function setting( string $key, mixed $default = null ): mixed {
    return container()->resolve( SettingsFactory::class )
        ->make( 'disabler', Plugin::SETTINGS_OPTION )
        ->get( $key, $default );
}

/**
 * Returns the plugin application instance.
 *
 * Registered after the static is assigned, not from the constructor.
 * Registering a provider boots it when the application is already booted, and
 * a provider that reads a setting calls setting() -> container() -> back into
 * here. With `$app ??= new App` the assignment only happens once the
 * constructor returns, so that re-entrant call would find `$app` still null,
 * build a second App, register it, and recurse until the stack gave out.
 *
 * Handing back the half-built instance is safe because the container has its
 * config bound by then: App::register() calls bootstrap() before it registers
 * a single provider, and config is all setting() needs.
 */
function app(): App {
    static $app;

    if ( ! $app instanceof App ) {
        $app = new App;

        $app->register();
    }

    return $app;
}

/**
 * Helper function for quickly accessing the plugin container. Devs can
 * access any concrete implementation by passing in a reference to its abstract
 * identifier via `container()->resolve($abstract)`.
 */
function container(): Application {
    return app()->application();
}

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name Constant name.
 * @param string $value Value.
 */
function maybe_define_constant( $name, $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

/**
 * Get post types which support revisions.
 *
 * @return array
 */
function get_revision_post_types() {
    $revision_post_types = [];

    foreach ( get_post_types() as $type ) {
        $object = get_post_type_object( $type );
        if ( ! post_type_supports( $type, 'revisions' ) || null === $object ) {
            continue;
        }

        $name = property_exists( $object, 'labels' ) && property_exists( $object->labels, 'name' )
            ? $object->labels->name
            : $object->name;

        $revision_post_types[ $type ] = $name;
    }

    return $revision_post_types;
}

/**
 * Split a textarea's contents into a list of sanitized, non-empty entries.
 *
 * The settings screen offers several free-text lists -- XML-RPC methods,
 * headers, allowed IPs, self-ping URLs -- and people type them one per line,
 * with stray blank lines and trailing spaces. Whitespace of any kind is the
 * separator, so a list pasted space-separated works as well as one typed
 * line by line.
 *
 * Was a trait, `Contracts\Traits\Utils`, mixed into six classes. Two of them
 * called it; the other four carried it unused. A trait also made the method
 * part of the public surface of every class that used it, which is why the
 * unit test reaches for it as `XMLRPC::prepareMultilineText`. It belongs
 * beside the other helpers instead.
 *
 * @param string $text Raw textarea contents.
 * @param string $sanitize Sanitizer applied to each entry. Pass '' to skip it,
 *                         for values a text sanitizer would mangle.
 *
 * @return array<int, string>
 */
function prepare_multiline_text( string $text, string $sanitize = 'sanitize_text_field' ): array {
    $entries = array_map( 'trim', explode( ' ', Str::squish( $text ) ) );

    if ( $sanitize ) {
        $entries = array_map( $sanitize, $entries );
    }

    return array_filter( $entries );
}
