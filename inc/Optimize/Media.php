<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use HBP\Disabler\Contracts\Traits\Utils;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

/**
 * Class Media.
 */
class Media implements Bootable {

    use AccessiblePrivateMethods;
    use Utils;

    /**
     * Boot.
     */
    public function boot(): void {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
        self::add_action( 'wp_enqueue_scripts', [ $this, 'wpEnqueueScripts' ] );
    }

    private function initHooks(): void {
        $this->disableWPImgTagAddAutoSizes();
    }

    private function wpEnqueueScripts(): void {
        $this->disableWPImgAutoSizesContain();
    }

    /**
     * Removes the CSS containment inline styles used by WordPress 6.7+
     * for lazy-loaded image auto-sizing.
     *
     * @see https://make.wordpress.org/core/2024/10/18/auto-sizes-for-lazy-loaded-images-in-wordpress-6-7/
     */
    private function disableWPImgAutoSizesContain(): void {
        if ( Options::get( 'editor_disable_wp_img_auto_sizes_contain' ) ) {
            wp_dequeue_style( 'wp-img-auto-sizes-contain' );
        }
    }

    /**
     * Prevents WordPress from automatically adding the `sizes="auto"`
     * attribute to lazy-loaded images.
     *
     * Note: Disabling the `sizes="auto"` attribute also makes the
     * associated CSS containment stylesheet unnecessary.
     *
     * @see https://github.com/WordPress/WordPress/blob/7f13088e924c0437f954e6cd46b7d65da0bd9317/wp-includes/media.php#L2019
     */
    private function disableWPImgTagAddAutoSizes(): void {
        if ( Options::get( 'editor_disable_wp_img_tag_add_auto_sizes' ) ) {
            add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
        }
    }
}
