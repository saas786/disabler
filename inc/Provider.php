<?php

namespace HBP\Disabler;

use HBP\Disabler\Admin\Notices;
use HBP\Disabler\Tools\Update\PluginInstall;
use HBP\Disabler\Tools\UsageTracker\Tracker;
use Hybrid\Core\ServiceProvider;

/**
 * Plugin service provider.
 */
class Provider extends ServiceProvider {

    /**
     * Register.
     *
     * @return void
     * @throws \Throwable
     */
    public function register() {
        $this->app->singleton( PluginInstall::class );
        $this->app->singleton( Notices::class );
        $this->app->singleton( Plugin::class );

        $this->app->singleton( 'hbp/disabler/assets', static function ( $app ) {
            $plugin = $app->make( \Hybrid\Assets\Plugin::class );
            $plugin->setPluginFile( DISABLER_FILE );

            return $plugin;
        } );

        $this->app->singleton( 'disabler/usage/tracker', static fn( $app ) => new Tracker( $app ) );

        $this->app->singleton( Tracker::class, static fn() => new Tracker(
            'https://tracking.hybopressthemes.com/api/v1/track/',
            WEEK_IN_SECONDS * 2
        ) );

        $this->app->alias( Tracker::class, 'disabler/usage/tracker' );
    }

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        $this->app->resolve( PluginInstall::class )->boot();
        $this->app->resolve( Notices::class )->boot();
        $this->app->resolve( Plugin::class )->boot();

        $this->app->resolve( 'disabler/usage/tracker' )->boot();
    }

}
