<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

class Frontend implements Bootable {

    use AccessiblePrivateMethods;

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
    }

    private function initHooks(): void {
        // Disable shortlinks.
        if ( Options::get( 'frontend_disable_shortlinks' ) ) {
            // Disable HTML meta tag.
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );

            // Disable HTTP header.
            remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
        }
    }

}
