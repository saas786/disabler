<?php

/**
 * Pest configuration.
 *
 * The plugin's own autoloader is the one Composer built at the repo root
 * against PHP 8.2. This suite has its own manifest and its own vendor
 * directory, pinned to 8.4, so the test tooling's platform requirement never
 * enters the plugin's dependency resolution.
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\Update\FlattenedSettings;
use PestWP\Config;
use PestWP\Database\TransactionManager;

/**
 * A path inside the plugin being tested.
 *
 * This suite is its own Composer project, one directory down from the plugin,
 * so nothing here can use __DIR__ to reach the plugin without counting levels.
 * Counting them once, here, means a file moving inside this suite does not
 * silently start reading the wrong config.
 */
function plugin_path( string $path = '' ): string {
    return dirname( __DIR__, 2 ) . ( '' === $path ? '' : '/' . ltrim( $path, '/' ) );
}

// The plugin's own autoloader, built at its 8.2 floor. Separate from this
// suite's, which lives in qa/vendor and holds the 8.4 test tooling.
require_once plugin_path( 'vendor/autoload.php' );

Config::plugins( plugin_path( 'disabler.php' ) );

// Not `static fn` -- Pest binds every hook closure to the test case, and
// Closure::bind() cannot rebind a static closure. It fails with
// "Could not bind closure", which surfaces on every test in the suite rather
// than pointing back here.
uses()
    ->beforeEach( function (): void {
        TransactionManager::beginTransaction();
        snapshot_hooks();
        snapshot_query_vars();

        // CI installs a bare WordPress with no theme, so nothing ever calls
        // add_theme_support( 'automatic-feed-links' ) -- and feed_links(),
        // which is hooked unconditionally in default-filters.php, returns
        // without printing. Locally a block theme is present and declares the
        // support, so the same head came back with feed links on one machine
        // and without them on the other. Declaring it here makes the two
        // agree. add_action() dedupes, so doing it when a theme already has
        // is a no-op.
        add_theme_support( 'automatic-feed-links' );
    } )
    ->afterEach( function (): void {
        // wp_reset_postdata() restores $post out of $wp_query -- it does not
        // touch $wp_query itself. A test that made the request singular left
        // it singular, and the rollback then took the post out from under it,
        // so the next file's wp_head() reached wp_get_shortlink() with a
        // queried id that no longer resolved: "Attempt to read property
        // post_type on null", which failOnWarning turns into a failure in a
        // file that did nothing wrong.
        reset_query();
        reset_screen();

        TransactionManager::rollback();
        restore_hooks();
        restore_query_vars();
        reset_scripts();
        reset_styles();
    } )
    ->in( 'Integration' );

/**
 * The plugin's declared settings, read from config rather than listed here.
 *
 * This is the whole point of the suite. There are a few hundred settings and
 * the set changes whenever a control is added, so a hand-written fixture is
 * both unmaintainable and, worse, quietly incomplete: the key someone forgot
 * to add is exactly the key whose migration nobody tested.
 *
 * The defaults files are the source. Every control must have a default -- an
 * invariant this suite also asserts -- so the defaults are the complete list,
 * and unlike the control declarations they are plain arrays that need no
 * WordPress to read.
 *
 * @return array<string, array<string, mixed>> section => key => default
 */
function declared_sections(): array {
    static $sections;

    if ( null !== $sections ) {
        return $sections;
    }

    $sections = [];

    // Config files that are not settings sections.
    $skip = [ 'app', 'logging', 'tabs', 'sections' ];

    foreach ( glob( plugin_path( 'config/*.php' ) ) as $file ) {
        $section = basename( $file, '.php' );

        if ( in_array( $section, $skip, true ) ) {
            continue;
        }

        $sections[ $section ] = require $file;
    }

    return $sections;
}

/**
 * Every declared setting as [ dotted key, legacy flat key ].
 *
 * The flat key is how 4.0.2 and earlier stored it: the section prefix, an
 * underscore, then the control name.
 *
 * @return array<string, array{string, string}>
 */
function declared_settings(): array {
    $settings = [];

    foreach ( declared_sections() as $section => $defaults ) {
        foreach ( array_keys( $defaults ) as $key ) {
            $settings[ "{$section}.{$key}" ] = [ "{$section}.{$key}", "{$section}_{$key}" ];
        }
    }

    return $settings;
}

/**
 * The section prefixes the migration knows about.
 *
 * Read by reflection because the list is private, and it is private for a
 * good reason -- nothing outside the migration should be branching on it.
 * A test asserting an internal invariant is a fair exception.
 *
 * @return array<int, string>
 */
function migration_sections(): array {
    return ( new ReflectionClass( FlattenedSettings::class ) )
        ->getReflectionConstant( 'SECTIONS' )
        ->getValue();
}

/**
 * A value unique to its key, so a conversion that crosses two settings' wires
 * fails instead of passing on two keys that happen to share a value.
 */
function sentinel( string $key ): string {
    return "sentinel:{$key}";
}

/**
 * The stored option as a pre-4.0.5 install would have it: every declared
 * setting, flat, holding its own sentinel.
 *
 * @return array<string, mixed>
 */
function legacy_option(): array {
    $flat = [];

    foreach ( declared_settings() as [ $dotted, $legacy ] ) {
        $flat[ $legacy ] = sentinel( $dotted );
    }

    return $flat;
}

/**
 * Boot one feature class with the settings currently stored.
 *
 * Every Optimize class registers its work on `init` at priority 0, which
 * fired long before any test body runs. Storing an option in a test therefore
 * changes nothing on its own -- the feature already made its decisions
 * against whatever was stored at boot.
 *
 * So the option is written first and the feature is then booted a second
 * time, in isolation. `initHooks` is private and stays private: nothing
 * outside the class should be calling it, and adding a public entry point
 * purely to let tests in would be production surface serving the suite. This
 * one reflection call, in one helper, is the deliberate exception -- the same
 * one made above for FlattenedSettings::SECTIONS.
 *
 * Re-firing `init` instead would re-run every other feature and all of core's
 * own handlers, which is a blast radius no assertion is worth.
 *
 * Nothing is snapshotted here. The whole registry is saved before every test,
 * so whatever this boot -- or core -- does to hooks is undone either way.
 *
 * The method name is a parameter because the features do not agree on one:
 * most call it initHooks, Editor calls it initializeHooks. Hard-coding the
 * common name meant the odd one out could not be booted at all, which is a
 * poor reason for a feature to go untested. Renaming the method in
 * production to suit the suite would be the tail wagging the dog.
 *
 * @param class-string $class Feature class under test.
 * @param string       $method Its private init method.
 */
function boot_feature( string $class, string $method = 'initHooks' ): object {
    $feature = new $class;

    ( new ReflectionMethod( $class, $method ) )->invoke( $feature );

    return $feature;
}

/**
 * The whole filter registry as it stood before the current test.
 *
 * TransactionManager rolls the database back, not $wp_filter. A callback a
 * feature added -- or one core removed -- survives into the next test, where
 * it silently changes the answer.
 *
 * This used to save only a hand-listed set of hooks per feature. That list
 * was wrong twice. The second time cost a day: core's
 * wp_enqueue_emoji_styles() returns early unless print_emoji_styles is still
 * on wp_print_styles, and unhooks it on its first run. So the first test in
 * the suite to render a head burned that callback for every test after it,
 * and no feature's list named wp_print_styles except Performance's -- which
 * meant emoji styles could never be enqueued again, however the control was
 * set, and the disabled case went green for a feature that did nothing.
 *
 * Saving everything deletes the list, and with it the class of bug. It costs
 * one shallow clone per registered hook per test.
 *
 * @var array<string, WP_Hook>|null
 */
$GLOBALS['hbp_saved_hooks'] = null;

function snapshot_hooks(): void {
    global $wp_filter;

    $GLOBALS['hbp_saved_hooks'] = array_map(
        static fn( $hook ) => clone $hook,
        $wp_filter
    );
}

function restore_hooks(): void {
    global $wp_filter;

    if ( null === $GLOBALS['hbp_saved_hooks'] ) {
        return;
    }

    $wp_filter = $GLOBALS['hbp_saved_hooks'];

    $GLOBALS['hbp_saved_hooks'] = null;
}

/**
 * How many callbacks are registered on a hook, across every priority.
 *
 * has_filter( $hook ) answers "does anything at all hook this", which is
 * almost never the question a test wants: core and the test tooling hook
 * plenty of things, so a bare false is only available on hooks nobody else
 * has touched. Counting before and after a boot asks the question that
 * actually matters -- did this feature add anything -- without needing to
 * name a callback the AccessiblePrivateMethods proxy has already wrapped.
 */
function hook_callback_count( string $hook ): int {
    global $wp_filter;

    if ( ! isset( $wp_filter[ $hook ] ) ) {
        return 0;
    }

    return array_sum( array_map( 'count', $wp_filter[ $hook ]->callbacks ) );
}

/**
 * A redirect that a feature tried to perform.
 */
final class RedirectAttempt extends RuntimeException {
    public function __construct( public readonly string $url, public readonly int $status ) {
        parent::__construct( "redirect to {$url}" );
    }
}

/**
 * A wp_die() a feature tried to perform.
 */
final class DieAttempt extends RuntimeException {

}

/**
 * Run something that may redirect or die, and report what it tried to do.
 *
 * Both endings are unreachable in process on their own: wp_safe_redirect() is
 * followed by a bare exit(), and wp_die() ends the request. Neither can be
 * turned off by a filter, because the exit sits after the filter returns.
 *
 * Throwing from inside the filter is what makes them testable. The exception
 * unwinds past the exit() before it is reached, so the whole path up to the
 * redirect runs exactly as it would in production and the test still gets its
 * turn back. This is the same trick WordPress' own suite uses, and it is why
 * this file does not need per-test process isolation -- which would have cost
 * a fresh WordPress bootstrap per case.
 *
 * Returns null when nothing was attempted, which is a real outcome and the
 * one every "control is off" case asserts.
 *
 * @return array{url: string, status: int}|string|null
 */
function capture_exit( callable $run ): array|string|null {
    add_filter(
        'wp_redirect',
        static function ( $location, $status ): never {
            throw new RedirectAttempt( (string) $location, (int) $status );
        },
        PHP_INT_MAX,
        2
    );

    // Every wp_die handler filter, not just 'wp_die_handler'. wp_die()
    // chooses which one to apply from the shape of the request -- a feed
    // request takes the xml branch, an ajax request the ajax branch -- and
    // filtering only the default leaves the other branches calling die() for
    // real, which ends the runner with "premature end of PHP process" and no
    // failing assertion to point at.
    $die_handlers = [
        'wp_die_handler',
        'wp_die_xml_handler',
        'wp_die_ajax_handler',
        'wp_die_json_handler',
        'wp_die_jsonp_handler',
        'wp_die_xmlrpc_handler',
    ];

    foreach ( $die_handlers as $handler ) {
        add_filter(
            $handler,
            static fn(): callable => static function ( $message ): never {
                throw new DieAttempt( is_string( $message ) ? $message : 'died' );
            },
            PHP_INT_MAX
        );
    }

    try {
        $run();
    } catch ( RedirectAttempt $redirect ) {
        return [
            'url'    => $redirect->url,
            'status' => $redirect->status,
        ];
    } catch ( DieAttempt $died ) {
        return $died->getMessage();
    }

    return null;
}

/**
 * Store settings for one section, replacing whatever is there.
 *
 * @param array<string, mixed> $values
 */
function store_settings( string $section, array $values ): void {
    $option = get_option( HBP\Disabler\Plugin::SETTINGS_OPTION, [] );

    $option[ $section ] = $values;

    update_option( HBP\Disabler\Plugin::SETTINGS_OPTION, $option );
}

/**
 * The query vars WP will answer to, as they stood before the current test.
 *
 * $wp is a third mutable global, alongside WP_Scripts and WP_Styles, that
 * neither the database rollback nor the hook restore reaches. Performance's
 * embeds control edits public_query_vars in place at boot, so a single test
 * booting it left every later test -- in every later file -- running against
 * a WordPress that could no longer parse an embed request.
 *
 * A plain array copy is enough; the property holds strings.
 *
 * @var array<int, string>|null
 */
$GLOBALS['hbp_saved_query_vars'] = null;

function snapshot_query_vars(): void {
    global $wp;

    $GLOBALS['hbp_saved_query_vars'] = $wp instanceof WP ? $wp->public_query_vars : null;
}

function restore_query_vars(): void {
    global $wp;

    if ( null === $GLOBALS['hbp_saved_query_vars'] || ! $wp instanceof WP ) {
        return;
    }

    $wp->public_query_vars = $GLOBALS['hbp_saved_query_vars'];

    $GLOBALS['hbp_saved_query_vars'] = null;
}

/**
 * Throw away the registered scripts so the next use rebuilds core's defaults.
 *
 * Features that call wp_deregister_script() change WP_Scripts, not $wp_filter,
 * so restore_hooks() cannot undo them. Nulling the global is enough: the next
 * wp_scripts() call constructs a fresh instance and fires wp_default_scripts
 * again, which is exactly the pre-test state.
 */
function reset_scripts(): void {
    $GLOBALS['wp_scripts'] = null;
}

/**
 * Throw away the registered styles, as reset_scripts() does for scripts.
 */
function reset_styles(): void {
    $GLOBALS['wp_styles'] = null;
}

/**
 * Put a single post in the main query, as a singular request would.
 *
 * The probe confirmed this takes in process: is_singular() and
 * get_queried_object_id() both follow. That is what makes the feed and
 * shortlink features testable without a second bootstrap.
 */
function query_singular( int $post_id ): void {
    $GLOBALS['wp_query'] = new WP_Query( [ 'p' => $post_id ] );
    $GLOBALS['post']     = get_post( $post_id );

    setup_postdata( $GLOBALS['post'] );
}

/**
 * Put $pagenow and the current screen back to a front-end request.
 *
 * Features branch on both directly, and nothing else in the teardown reaches
 * them: reset_scripts() replaces WP_Scripts, which is a different global. A
 * file that set $pagenow to 'post.php' left every later file booting as
 * though the post editor were loading.
 *
 * $current_screen is the same hazard with a wider blast radius. is_admin()
 * consults it before it consults WP_ADMIN, so a screen left standing makes
 * every later test read as an admin request -- including the front-end half
 * of a pair whose whole point is that the two contexts differ. Unset rather
 * than replaced: the absence of a screen is the front-end state, and any
 * WP_Screen this suite could construct to stand in for it would be a screen.
 */
function reset_screen(): void {
    $GLOBALS['pagenow'] = 'index.php';

    // set_current_screen() writes these two as well -- they are the screen's
    // post type and taxonomy, and a stale 'post' in typenow is the same kind
    // of quiet carry-over as a stale screen.
    unset( $GLOBALS['current_screen'], $GLOBALS['typenow'], $GLOBALS['taxnow'] );
}

/**
 * Make is_admin() answer true, the way core's own suite does.
 *
 * WP_ADMIN is the other input, and it is the wrong one to reach for: it is
 * defined during bootstrap by wp-admin/admin.php, long before any test body
 * runs, and defining it late is both process-wide and irreversible. One test
 * doing that would put every later test in a fake admin -- starting with the
 * front-end heartbeat case, which asserts the opposite.
 *
 * set_current_screen() is reversible, which is the whole reason to prefer it.
 * Both files are admin includes, so a front-end bootstrap has not loaded
 * either, and they are plain declaration files with no side effects -- unlike
 * admin.php, which would run auth checks and fire admin_init.
 *
 * Two requires, not one, and the order matters. screen.php declares
 * set_current_screen(); the WP_Screen class it calls on its first line is a
 * separate file. On a real admin request wp-admin/includes/admin.php pulls in
 * both, which is why they are easy to mistake for one unit. Loading only
 * screen.php gets as far as the call and fatals with "Class WP_Screen not
 * found".
 */
function set_admin_screen( string $screen = 'dashboard' ): void {
    require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
    require_once ABSPATH . 'wp-admin/includes/screen.php';

    set_current_screen( $screen );
}

/**
 * Put the request back to a bare, non-singular state.
 *
 * wp_reset_query() is no help here: it copies $wp_the_query back over
 * $wp_query, and $wp_the_query is the bootstrap's query, not one this suite
 * ever wants restored.
 */
function reset_query(): void {
    $GLOBALS['wp_query'] = new WP_Query;
    $GLOBALS['post']     = null;
}

/**
 * Put a feed request in the main query.
 *
 * maybeRedirectFeeds() compares $wp_query->query against exact arrays such as
 * [ 'feed' => 'feed' ], so what goes in here has to match what a real request
 * would produce, not merely satisfy is_feed().
 *
 * @param array<string, mixed> $query
 */
function query_feed( array $query = [ 'feed' => 'feed' ] ): void {
    $GLOBALS['wp_query'] = new WP_Query( $query );
}

/**
 * Everything core prints into the document head.
 */
function rendered_head(): string {
    ob_start();
    wp_head();

    return (string) ob_get_clean();
}
