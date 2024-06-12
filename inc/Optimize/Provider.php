<?php

namespace HBP\Disabler\Optimize;

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
        $this->app->singleton( Editor::class );
        $this->app->singleton( Backend::class );
        $this->app->singleton( Frontend::class );
        $this->app->singleton( Privacy::class );
        $this->app->singleton( Revisions::class );
        $this->app->singleton( XMLRPC::class );
        $this->app->singleton( Performance::class );
        $this->app->singleton( RestAPI::class );
        $this->app->singleton( Feeds::class );
        $this->app->singleton( Updates::class );
    }

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        $this->app->resolve( Editor::class )->boot();
        $this->app->resolve( Backend::class )->boot();
        $this->app->resolve( Frontend::class )->boot();
        $this->app->resolve( Privacy::class )->boot();
        $this->app->resolve( Revisions::class )->boot();
        $this->app->resolve( XMLRPC::class )->boot();
        $this->app->resolve( Performance::class )->boot();
        $this->app->resolve( RestAPI::class )->boot();
        $this->app->resolve( Feeds::class )->boot();
        $this->app->resolve( Updates::class )->boot();
    }

}
