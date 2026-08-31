<?php

namespace HBP\Disabler\Optimize;

use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use function HBP\Disabler\setting;

class Privacy implements Bootable {

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
        // Remove WordPress version from header.
        if ( setting( 'privacy.disable_wp_generator' ) ) {
            remove_action( 'wp_head', 'wp_generator' );
        }

        if ( setting( 'privacy.fake_user_agent_value' ) ) {
            add_filter( 'http_headers_useragent', [ $this, 'removeUrlFromUserAgent' ] );
        }
    }

    /**
     *  Removes the site url from the header user-agent string.
     */
    public function removeUrlFromUserAgent() {
        global $wp_version;

        return 'WordPress/' . $wp_version;
    }
}
