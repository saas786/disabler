<?php

namespace HBP\Disabler\Optimize;

use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use function HBP\Disabler\setting;

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
        if ( setting( 'frontend.disable_shortlinks' ) ) {
            // Disable HTML meta tag.
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );

            // Disable HTTP header.
            remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
        }
    }
}
