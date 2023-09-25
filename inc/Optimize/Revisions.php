<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

class Revisions implements Bootable {

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
        add_filter( 'wp_revisions_to_keep', [ &$this, 'revisionsToKeep' ], \PHP_INT_MAX, 2 );
    }

    /**
     * Filters the number of revisions to save for the given post.
     *
     * Overrides the value of WP_POST_REVISIONS.
     *
     * @param int      $num  Number of revisions to store.
     * @param \WP_Post $post Post object.
     */
    public function revisionsToKeep( $num, $post ) {
        $disable_revisions = Options::get( 'revisions_disable_revisions' );

        if (
            ! in_array( 'no', $disable_revisions )
            && (
                in_array( 'all', $disable_revisions )
                || in_array( $post->post_type, $disable_revisions )
            )
        ) {
            return 0;
        }

        $revisions_limit = Options::get( 'revisions_revisions_limit_' . $post->post_type, '' );

        if ( ! empty( $revisions_limit ) && is_numeric( $revisions_limit ) ) {
            return (int) $revisions_limit;
        }

        return $num;
    }

}
