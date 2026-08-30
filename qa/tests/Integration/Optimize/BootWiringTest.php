<?php

/**
 * How the features get attached to WordPress in the first place.
 *
 * Every other file in this directory reaches initHooks() by reflection, which
 * is deliberate -- it isolates the feature from the boot order. The cost is
 * that boot() itself was never executed by anything, and mutation testing
 * said so plainly: the `self::add_action( 'init', ... )` line in all twelve
 * features escaped every mutant thrown at it. Delete the call, change the
 * priority, corrupt the callback array, and the whole suite still passed.
 *
 * A feature that is never attached does nothing at all, so this is the one
 * line in each class whose failure is total. It is checked here once per
 * feature rather than repeated in each file.
 *
 * has_action() with the exact callback is the seam that matters. A count
 * would notice a deleted call but not a corrupted callback, and comparing
 * against 0 catches a changed priority -- which is not cosmetic here, since
 * priority 0 is what puts these before everything else on init.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\AdminBar;
use HBP\Disabler\Optimize\Backend;
use HBP\Disabler\Optimize\Editor;
use HBP\Disabler\Optimize\Feeds;
use HBP\Disabler\Optimize\Frontend;
use HBP\Disabler\Optimize\Media;
use HBP\Disabler\Optimize\OptimizeServiceProvider;
use HBP\Disabler\Optimize\Performance;
use HBP\Disabler\Optimize\Privacy;
use HBP\Disabler\Optimize\RestAPI;
use HBP\Disabler\Optimize\Revisions;
use HBP\Disabler\Optimize\Updates;
use HBP\Disabler\Optimize\XMLRPC;
use Hybrid\Contracts\Bootable;
use function HBP\Disabler\container;

mutates(
    AdminBar::class,
    Backend::class,
    Editor::class,
    Feeds::class,
    Frontend::class,
    Media::class,
    OptimizeServiceProvider::class,
    Performance::class,
    Privacy::class,
    RestAPI::class,
    Revisions::class,
    Updates::class,
    XMLRPC::class
);

/**
 * Every feature and the init callback its boot() is expected to register.
 *
 * Editor is the odd one out with initializeHooks; the rest use initHooks.
 * That inconsistency is worth pinning rather than smoothing over, since the
 * test harness already has to know about it.
 *
 * @return array<int, array{0: class-string, 1: string}>
 */
dataset( 'bootable features', [
    [ AdminBar::class, 'initHooks' ],
    [ Backend::class, 'initHooks' ],
    [ Editor::class, 'initializeHooks' ],
    [ Feeds::class, 'initHooks' ],
    [ Frontend::class, 'initHooks' ],
    [ Media::class, 'initHooks' ],
    [ Performance::class, 'initHooks' ],
    [ Privacy::class, 'initHooks' ],
    [ RestAPI::class, 'initHooks' ],
    [ Revisions::class, 'initHooks' ],
    [ Updates::class, 'initHooks' ],
    [ XMLRPC::class, 'initHooks' ],
] );

it( 'attaches to init at priority zero', function ( string $class, string $method ): void {
    $feature = new $class;

    // Nothing of this feature is on init before boot() runs. Asserting the
    // absence first is what makes the assertion after it mean something: the
    // suite restores hooks between tests, and a leak from an earlier file
    // would otherwise make this pass without boot() doing anything.
    expect( has_action( 'init', [ $feature, $method ] ) )->toBeFalse();

    $feature->boot();

    // toBe( 0 ), not toBeTrue(). has_action() returns the priority, and 0 is
    // falsy -- a truthiness check here would fail on correct code and pass on
    // a mutant that bumped the priority to 1.
    expect( has_action( 'init', [ $feature, $method ] ) )->toBe( 0 );
} )->with( 'bootable features' );

it( 'attaches media to wp_enqueue_scripts as well', function (): void {
    // Media boots two hooks, not one. The init assertion above would still
    // pass if this second line were deleted, and the containment stylesheet
    // would silently stop being dequeued.
    $feature = new Media;

    $feature->boot();

    expect( has_action( 'wp_enqueue_scripts', [ $feature, 'wpEnqueueScripts' ] ) )->toBe( 10 );
} );

it( 'attaches the editor to plugins_loaded as well', function (): void {
    $feature = new Editor;

    $feature->boot();

    expect( has_action( 'plugins_loaded', [ $feature, 'onPluginsLoaded' ] ) )->toBe( 0 );
} );

it( 'boots every feature through the service provider', function (): void {
    // The provider binds and boots from one list with no branching, and
    // every one of those lines escaped: a feature dropped from the list
    // would simply never run, and nothing said so.
    //
    // Driven through the real container rather than a stub, because what is
    // being checked is that the provider and the container agree -- a stub
    // would only check that this test can call boot() in a loop.
    $provider = new OptimizeServiceProvider( container() );

    $provider->register();
    $provider->boot();

    $expected = [
        [ AdminBar::class, 'initHooks' ],
        [ Backend::class, 'initHooks' ],
        [ Editor::class, 'initializeHooks' ],
        [ Feeds::class, 'initHooks' ],
        [ Frontend::class, 'initHooks' ],
        [ Media::class, 'initHooks' ],
        [ Performance::class, 'initHooks' ],
        [ Privacy::class, 'initHooks' ],
        [ RestAPI::class, 'initHooks' ],
        [ Revisions::class, 'initHooks' ],
        [ Updates::class, 'initHooks' ],
        [ XMLRPC::class, 'initHooks' ],
    ];

    $missing = [];

    foreach ( $expected as [ $class, $method ] ) {
        // Resolved from the container, not constructed here: the provider
        // registers singletons, so this is the same instance it booted. A new
        // instance would not match the registered callback and every feature
        // would look missing.
        if ( false === has_action( 'init', [ container()->resolve( $class ), $method ] ) ) {
            $missing[] = $class;
        }
    }

    // Reported as a list so a failure names which features were dropped
    // rather than just saying one of them was.
    expect( $missing )->toBe( [] );
} );

it( 'registers every optimizer that exists on disk', function (): void {
    // The lists above are written by hand, and so is the provider's. All
    // three agreeing with each other still says nothing about a thirteenth
    // feature added to the directory and wired into none of them -- it would
    // never boot, and every existing assertion would stay green.
    //
    // Derived from the filesystem for that reason: this is the one check
    // here that can fail because of a file it was never told about.
    $onDisk = [];

    foreach ( glob( plugin_path( 'inc/Optimize/*.php' ) ) as $file ) {
        $class = 'HBP\\Disabler\\Optimize\\' . basename( $file, '.php' );

        if ( is_a( $class, Bootable::class, true ) ) {
            $onDisk[] = $class;
        }
    }

    $registered = ( new ReflectionClass( OptimizeServiceProvider::class ) )
        ->getConstant( 'OPTIMIZERS' );

    // Both directions. Missing means a feature never boots; extra means the
    // provider names a class that no longer exists, which fatals on resolve.
    expect( array_diff( $onDisk, $registered ) )->toBe( [] )
        ->and( array_diff( $registered, $onDisk ) )->toBe( [] );
} );
