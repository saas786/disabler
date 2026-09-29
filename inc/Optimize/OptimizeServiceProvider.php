<?php

namespace HBP\Disabler\Optimize;

use Hybrid\Core\ServiceProvider;

/**
 * Plugin service provider.
 */
class OptimizeServiceProvider extends ServiceProvider {
    /**
     * The optimizers, in the order they boot.
     *
     * One list rather than the same twelve class names written out twice,
     * once to bind and once to boot. Kept in order because every boot()
     * registers an `init` callback at priority 0, so this is the order those
     * callbacks run in.
     */
    private const OPTIMIZERS = [
        Editor::class,
        Backend::class,
        Frontend::class,
        Media::class,
        Privacy::class,
        Revisions::class,
        XMLRPC::class,
        Performance::class,
        RestAPI::class,
        Feeds::class,
        Updates::class,
        AdminBar::class,
    ];

    /**
     * Register.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function register() {
        foreach ( self::OPTIMIZERS as $optimizer ) {
            $this->app->singleton( $optimizer );
        }
    }

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        foreach ( self::OPTIMIZERS as $optimizer ) {
            $this->app->resolve( $optimizer )->boot();
        }
    }
}
