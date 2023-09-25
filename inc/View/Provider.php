<?php
/**
 * View service provider.
 */

namespace HBP\Disabler\View;

use Hybrid\Core\ServiceProvider;
use Hybrid\View\Facades\View;
use function Hybrid\public_path;

/**
 * View service provider.
 */
class Provider extends ServiceProvider {

    /**
     * Boot.
     */
    public function boot() {
        // Add view paths.
        // View::addLocation( resource_path( 'views' ), 50 );
        View::addLocation( public_path( 'views' ), 50 );
        // $this->loadViewsFrom( public_path( 'views' ), 'HBP/Disabler');
    }

}
