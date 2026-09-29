<?php

namespace HBP\Disabler\Admin;

use HBP\Settings\Ui\PanelFactory;
use Hybrid\Core\ServiceProvider;

/**
 * Admin service provider.
 */
class AdminServiceProvider extends ServiceProvider {
    /**
     * Register.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function register() {
        $this->app->singleton(
            OptionsPage::class,
            static fn( $app ) => new OptionsPage( $app->make( PanelFactory::class ) )
        );
        $this->app->singleton( PluginsPage::class );
    }

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        $page = $this->app->resolve( OptionsPage::class );

        $page->boot();

        // Registered once for the whole option. register_setting() adds a
        // sanitize_option_* filter per call and those chain, so one call per
        // tab would hand every later callback the previous one's merged array
        // and let it treat that as posted input.
        add_action( 'admin_init', [ $page, 'registerSetting' ] );
        $this->app->resolve( PluginsPage::class )->boot();
    }
}
