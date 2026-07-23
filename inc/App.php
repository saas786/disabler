<?php

namespace HBP\Disabler;

use Hybrid\Action\Scheduler\Provider as ActionSchedulerServiceProvider;
use Hybrid\Assets\Provider as AssetsServiceProvider;
use Hybrid\Contracts\Bootable;
use Hybrid\Core\Application;
use Hybrid\Log\LogServiceProvider;
use Hybrid\View\ViewServiceProvider;
use function Hybrid\app;
use function Hybrid\booted;

/**
 * App class.
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
     */
    public function __construct() {
        // Register default bindings and service providers.
        $this->register();
    }

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
