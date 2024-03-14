<?php

namespace HBP\Disabler\Admin;

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
        $this->app->singleton( OptionsPage::class );
        $this->app->singleton( PluginsPage::class );
    }

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        $this->app->resolve( OptionsPage::class )->boot();
        $this->app->resolve( PluginsPage::class )->boot();
    }

}
