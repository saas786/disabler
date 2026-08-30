<?php

namespace HBP\Disabler\Optimize;

use Hybrid\Contracts\Bootable;
use Hybrid\Tools\Arr;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use function HBP\Disabler\prepare_multiline_text;
use function HBP\Disabler\setting;

class Backend implements Bootable {

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
        // No self ping.
        if ( setting( 'backend.disable_self_ping' ) ) {
            add_action( 'pre_ping', [ $this, 'noSelfPing' ] );
        }
    }

    public function noSelfPing( &$links ) {
        $urls = prepare_multiline_text( setting( 'backend.disable_self_ping_urls', '' ), 'sanitize_url' );

        // Add home URL to array.
        $urls = Arr::prepend( $urls, esc_url( home_url() ) );

        // Process each link in the content and remove if it matches the current site URL or one of the additional URLs provided.
        foreach ( $links as $key => $link ) {
            foreach ( $urls as $url ) {
                if ( 0 === strpos( $link, $url ) ) {
                    unset( $links[ $key ] );
                }
            }
        }
    }
}
