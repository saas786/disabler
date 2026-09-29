<?php

/**
 * performance.disable_widgets.
 *
 * A three-way control, and the two disabling arms work by entirely different
 * means: 'all' assigns an empty array over the core priority of both dashboard
 * columns, while 'core' names thirteen boxes through remove_meta_box() and
 * unhooks the welcome panel. So 'all' is not a superset of 'core' -- it leaves
 * the welcome panel alone, and it leaves any box a plugin registered at a
 * priority other than 'core' standing. Each arm needs its own assertions.
 *
 * The effect lands in $wp_meta_boxes, which no rollback reaches. It is
 * snapshotted and restored in Pest.php alongside the hooks and query vars, so
 * a dashboard emptied here does not follow the rest of the suite home.
 *
 * remove_meta_box() runs convert_to_screen(), an admin include, so these tests
 * reach the admin the same way PerformanceHeartbeatTest does -- via
 * set_admin_screen(), never by defining WP_ADMIN.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\Performance;

mutates( Performance::class );

/**
 * A dashboard populated the way core populates it.
 *
 * Not the real wp_dashboard_setup(): that one fires the very action this
 * feature hooks, so calling it would run the feature at a moment the test
 * does not control, and it also reaches for network state. The shape is what
 * matters -- two columns, boxes under the 'core' priority -- because that is
 * what both arms of the feature write to.
 *
 * 'dashboard_php_nag' sits under 'normal' but is deliberately absent from the
 * feature's remove list (it is commented out in the source). It is here so the
 * 'core' arm has something it must leave alone in the same column it is
 * otherwise emptying.
 */
function seed_dashboard_widgets(): void {
    // Core hooks this in wp-admin/includes/dashboard.php, which a front-end
    // bootstrap never loads -- so without seeding it the hook is already
    // absent and "the feature unhooked it" would pass against a dashboard the
    // feature had not touched. Priority 10 is the one core registers at, and
    // the one remove_action() defaults to; a drift in either is exactly what
    // these assertions are here to catch.
    add_action( 'welcome_panel', 'wp_welcome_panel' );

    $box = static fn( string $id ): array => [
        'id'       => $id,
        'title'    => $id,
        'callback' => '__return_empty_string',
        'args'     => null,
    ];

    $GLOBALS['wp_meta_boxes']['dashboard'] = [
        'normal' => [
            'core' => [
                'dashboard_activity'        => $box( 'dashboard_activity' ),
                'dashboard_right_now'       => $box( 'dashboard_right_now' ),
                'dashboard_recent_comments' => $box( 'dashboard_recent_comments' ),
                'dashboard_php_nag'         => $box( 'dashboard_php_nag' ),
            ],
        ],
        'side'   => [
            'core' => [
                'dashboard_primary'       => $box( 'dashboard_primary' ),
                'dashboard_quick_press'   => $box( 'dashboard_quick_press' ),
                'dashboard_recent_drafts' => $box( 'dashboard_recent_drafts' ),
            ],
        ],
    ];
}

/**
 * Boot Performance with the widgets control set, then run a dashboard load.
 *
 * The feature hooks wp_dashboard_setup, so nothing happens at boot -- the
 * do_action is the dashboard being requested, and asserting before it would
 * pass for every stored value alike.
 */
function boot_widgets( string $stored ): void {
    $defaults = require plugin_path( 'config/performance.php' );

    store_settings( 'performance', array_merge( $defaults, [ 'disable_widgets' => $stored ] ) );

    boot_feature( Performance::class );

    // After the boot: remove_meta_box() needs a screen, and the feature reads
    // is_admin() while registering other controls, which this file has no
    // business changing.
    set_admin_screen();

    // remove_meta_box() itself lives here. Production never has to ask: the
    // only caller of wp_dashboard_setup() is wp-admin/index.php, which has
    // loaded admin.php and therefore this file long before the action fires.
    // A front-end test bootstrap has not.
    require_once ABSPATH . 'wp-admin/includes/template.php';

    seed_dashboard_widgets();

    do_action( 'wp_dashboard_setup' );
}

/**
 * Whether a box survived, read the way core reads it.
 *
 * remove_meta_box() does not unset the key, it writes false into all four
 * priorities. A test using isset() would call every removed box present and
 * pass no matter what the feature did.
 */
function dashboard_box_present( string $id, string $context ): bool {
    foreach ( $GLOBALS['wp_meta_boxes']['dashboard'][ $context ] ?? [] as $boxes ) {
        if ( ! empty( $boxes[ $id ] ) ) {
            return true;
        }
    }

    return false;
}

it( 'leaves the dashboard alone when the control is off', function (): void {
    boot_widgets( 'no' );

    expect( dashboard_box_present( 'dashboard_activity', 'normal' ) )->toBeTrue()
        ->and( dashboard_box_present( 'dashboard_primary', 'side' ) )->toBeTrue()
        ->and( has_action( 'welcome_panel', 'wp_welcome_panel' ) )->not->toBeFalse();
} );

it( 'empties both dashboard columns when set to all', function (): void {
    boot_widgets( 'all' );

    expect( $GLOBALS['wp_meta_boxes']['dashboard']['normal']['core'] )->toBe( [] )
        ->and( $GLOBALS['wp_meta_boxes']['dashboard']['side']['core'] )->toBe( [] );
} );

it( 'leaves the welcome panel hooked when set to all', function (): void {
    // The arms differ here, and the difference is easy to erase by accident:
    // 'all' empties the boxes and says nothing about the panel, which is not
    // a meta box at all.
    boot_widgets( 'all' );

    expect( has_action( 'welcome_panel', 'wp_welcome_panel' ) )->not->toBeFalse();
} );

it( 'removes the named core boxes when set to core', function (): void {
    boot_widgets( 'core' );

    expect( dashboard_box_present( 'dashboard_activity', 'normal' ) )->toBeFalse()
        ->and( dashboard_box_present( 'dashboard_right_now', 'normal' ) )->toBeFalse()
        ->and( dashboard_box_present( 'dashboard_recent_comments', 'normal' ) )->toBeFalse()
        ->and( dashboard_box_present( 'dashboard_primary', 'side' ) )->toBeFalse()
        ->and( dashboard_box_present( 'dashboard_quick_press', 'side' ) )->toBeFalse()
        ->and( dashboard_box_present( 'dashboard_recent_drafts', 'side' ) )->toBeFalse();
} );

it( 'leaves a box it does not name standing when set to core', function (): void {
    // dashboard_php_nag is commented out of the removal list. If someone
    // uncomments it, or swaps the named list for emptying the column, this is
    // what notices -- 'core' would have quietly become 'all'.
    boot_widgets( 'core' );

    expect( dashboard_box_present( 'dashboard_php_nag', 'normal' ) )->toBeTrue();
} );

it( 'unhooks the welcome panel when set to core', function (): void {
    boot_widgets( 'core' );

    expect( has_action( 'welcome_panel', 'wp_welcome_panel' ) )->toBeFalse();
} );
