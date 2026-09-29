<?php

namespace HBP\Disabler\Optimize;

use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use function HBP\Disabler\setting;

/**
 * Class Media.
 */
class Media implements Bootable {

    use AccessiblePrivateMethods;

    /**
     * Boot.
     */
    public function boot(): void {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
        self::add_action( 'wp_enqueue_scripts', [ $this, 'wpEnqueueScripts' ] );
    }

    private function initHooks(): void {
        $this->disableWPImgTagAddAutoSizes();
        $this->handleWPCoreLazyLoading();
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
        if ( setting( 'media.disable_wp_img_auto_sizes_contain' ) ) {
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
        if ( setting( 'media.disable_wp_img_tag_add_auto_sizes' ) ) {
            add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
        }
    }

    /**
     * Disables or overrides WordPress' native lazy loading (added in 5.5) for
     * images and iframes, which can conflict with lazy-load logic added by
     * themes, page builders, or other optimization plugins.
     *
     * - 'yes' fully disables core lazy loading, so no `loading` attribute is
     *   added at all.
     * - 'eager' leaves core lazy loading logic in place, but forces the
     *   `loading` attribute to `eager` instead of `lazy`, for elements where
     *   lazy behavior causes layout shift or LCP issues.
     *
     * @see https://make.wordpress.org/core/2020/07/14/lazy-loading-images-in-5-5/
     * @see https://developer.wordpress.org/reference/hooks/wp_lazy_loading_enabled/
     */
    private function handleWPCoreLazyLoading(): void {
        $mode = setting( 'media.disable_core_lazy_loading', 'no' );

        if ( 'yes' === $mode ) {
            add_filter( 'wp_lazy_loading_enabled', '__return_false' );

            return;
        }

        if ( 'eager' === $mode ) {
            self::add_filter( 'wp_img_tag_add_loading_attr', [ $this, 'forceEagerLoadingAttr' ] );
            self::add_filter( 'wp_iframe_tag_add_loading_attr', [ $this, 'forceEagerLoadingAttr' ] );
        }
    }

    /**
     * Forces the `loading` attribute value to `eager`.
     *
     * @see https://developer.wordpress.org/reference/hooks/wp_img_tag_add_loading_attr/
     * @see https://developer.wordpress.org/reference/hooks/wp_iframe_tag_add_loading_attr/
     */
    private function forceEagerLoadingAttr(): string {
        return 'eager';
    }
}
