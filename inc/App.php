<?php

namespace HBP\Disabler;

use Hybrid\Action\Scheduler\ActionSchedulerServiceProvider;
use Hybrid\Assets\AssetsServiceProvider;
use Hybrid\Contracts\Bootable;
use Hybrid\Core\Application;
use Hybrid\Log\Context\ContextServiceProvider;
use Hybrid\Log\LogServiceProvider;
use Hybrid\Tools\Config\Loader;
use Hybrid\View\ViewServiceProvider;
use function Hybrid\app;
use function Hybrid\booted;

/**
 * App class.
 *
 * Build this through `HBP\Disabler\app()`, never with `new`. The constructor
 * does nothing on purpose: `app()` records the instance and calls register()
 * straight after, and only that order survives a provider reading a setting
 * while registration is still running. A directly constructed App comes back
 * inert rather than failing, so the mistake would surface later and elsewhere.
 */
class App implements Bootable {
    /**
     * The plugin's application / container instance.
     *
     * A wrapper around Hybrid\Core\Application, acting as the central service container and
     * dependency manager. It loads service providers, manages configurations, and bootstraps
     * the plugin within the WordPress ecosystem.
     */
    private Application $plugin;

    /**
     * {@inheritDoc}
     *
     * Deliberately does not register. Registering boots providers, and a
     * provider that reads a setting calls back into `HBP\Disabler\app()`,
     * which cannot have recorded this instance until the constructor has
     * returned. `app()` assigns first and calls register() second, so that
     * re-entrant call finds this same App instead of building another.
     */
    public function __construct() {}

    /**
     * Register.
     */
    public function register(): void {
        // ------------------------------------------------------------------------------
        // Create a new application.
        // ------------------------------------------------------------------------------
        //
        // Creates the one true instance of the Hybrid Core application. You may access
        // this instance via the `\HBP\Disabler\app()`
        // after the application has booted.

        $this->plugin = booted()
            ? app()
            : new Application( WP_CONTENT_DIR . '/.hbp-disabler', false );

        $this->plugin->useConfigPath( DISABLER_DIR . '/config' );
        $this->plugin->useResourcePath( DISABLER_DIR . '/resources' );
        $this->plugin->usePublicPath( DISABLER_DIR . '/public' );
        $this->plugin->useStoragePath( $this->plugin->bootstrapPath() . '/storage' );

        $this->plugin->bootstrap();

        // Nested config → `disabler.*`, always, shared container or not.
        //
        // Root app.php / logging.php are ALSO picked up here and stored a second
        // time as `disabler.app` / `disabler.logging`, alongside the real
        // unprefixed keys that LoadConfiguration set. Harmless duplicates that
        // nothing reads through the prefix; not worth a special case to skip.
        Loader::load( DISABLER_DIR . '/config', 'disabler' );

        do_action( 'hbp/disabler/before/providers/register', $this->plugin );

        // ------------------------------------------------------------------------------
        // Register service providers with the application.
        // ------------------------------------------------------------------------------
        //
        // Before booting the application, add any service providers that are necessary
        // for running the plugin. Service providers are essentially the backbone of the
        // bootstrapping process.

        $this->plugin->register( ActionSchedulerServiceProvider::class );
        $this->plugin->register( AssetsServiceProvider::class );
        $this->plugin->register( LogServiceProvider::class );
        $this->plugin->register( ContextServiceProvider::class );
        $this->plugin->register( ViewServiceProvider::class );
        $this->plugin->register( View\ViewServiceProvider::class );
        $this->plugin->register( Admin\AdminServiceProvider::class );
        $this->plugin->register( Optimize\OptimizeServiceProvider::class );
        $this->plugin->register( PluginServiceProvider::class );

        do_action( 'hbp/disabler/after/providers/register', $this->plugin );
    }

    /**
     * Boot.
     */
    public function boot(): void {
        // ------------------------------------------------------------------------------
        // Perform bootstrap actions.
        // ------------------------------------------------------------------------------
        //
        // Creates an action hook for plugins to hook into the
        // bootstrapping process and add their own bindings before the app is booted by
        // passing the application instance to the action callback.
        do_action( 'hbp/disabler/before/boot', $this->plugin );

        // ------------------------------------------------------------------------------
        // Bootstrap the application.
        // ------------------------------------------------------------------------------
        //
        // Calls the application `boot()` method, which launches the application. Pat
        // yourself on the back for a job well done.

        $this->plugin->boot();

        do_action( 'hbp/disabler/after/boot', $this->plugin );
    }

    /**
     * Get the container instance.
     */
    public function application(): Application {
        return $this->plugin;
    }
}
