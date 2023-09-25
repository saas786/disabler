<?php

namespace HBP\Disabler;

use Hybrid\Contracts\Bootable;

/**
 * Plugin class.
 */
class Plugin implements Bootable {

    /**
     * The current version of the plugin.
     */
    const VERSION = '3.1.0-alpha.1';

    /**
     * The current db version of the plugin.
     */
    const DB_VERSION = '3.1.0-alpha.1';

    /**
     * The current release date of the plugin.
     */
    const RELEASE_DATE = '1 October, 2023 1:00PM (GMT + 5)';

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        /*
        $message = 'Disabler %s emergency: ' . time();
        Log::emergency( sprintf( $message, 'emergency' ) );
        Log::alert( sprintf( $message, 'alert' ) );
        Log::critical( sprintf( $message, 'critical' ) );
        Log::error( sprintf( $message, 'error' ) );
        Log::warning( sprintf( $message, 'warning' ) );
        Log::notice( sprintf( $message, 'notice' ) );
        Log::info( sprintf( $message, 'info' ) );
        Log::debug( sprintf( $message, 'debug' ) );

        Log::stack( [ 'single', 'daily' ] )->info( 'Something happened!' );

        Log::build([
            'driver' => 'single',
            'path'   => storage_path( 'logs/custom.log' ),
        ])->info( 'Something happened!' );
        */
    }

}
