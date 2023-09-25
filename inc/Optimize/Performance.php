<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use HBP\Disabler\Facades\Assets;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

class Performance implements Bootable {

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
        $this->disableEmojis();
        $this->disableHearbeat();
        $this->disableEmbeds();
        self::add_action( 'wp_dashboard_setup', [ $this, 'disableWidgets' ] );
    }

    private function disableEmojis() {
        if ( ! Options::get( 'performance_disable_emojis' ) ) {
            return;
        }

        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_action( 'embed_head', 'print_emoji_detection_script' );

        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

        // add_filter( 'emoji_svg_url', '__return_false' );

        self::add_filter( 'tiny_mce_plugins', [ $this, 'removeEmojisTinymce' ] );
        self::add_filter( 'wp_resource_hints', [ $this, 'resourceHintsPlainCleanup' ], 1 );
    }

    private function disableHearbeat() {
        switch ( Options::get( 'performance_disable_heartbeat' ) ) {
            case 'everywhere':
                wp_deregister_script( 'heartbeat' );
                break;
            case 'allow_only_on_post_edit_pages':
                global $pagenow;
                if ( 'post.php' != $pagenow && 'post-new.php' != $pagenow ) {
                    wp_deregister_script( 'heartbeat' );
                }
                break;
            case 'on_dashboard_page':
                if ( is_admin() ) {
                    wp_deregister_script( 'heartbeat' );
                }
                break;
        }

        add_filter( 'heartbeat_settings', [ $this, 'heartbeatFrequency' ], \PHP_INT_MAX );
    }

    /**
     * Filter function used to remove the tinymce emoji plugin.
     *
     * @param  array $plugins
     * @return array
     */
    private function removeEmojisTinymce( $plugins ) {
        return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : [];
    }

    /**
     * Remove the core s.w.org hint as it's only used for emoji stuff we don't use (if disabled).
     *
     * @see https://github.com/Yoast/wordpress-seo/blob/f6179b04ebc359c9975513e1dad1bc0718fef98e/src/integrations/front-end/crawl-cleanup-basic.php#L111
     * @param  array $hints The hints we're adding to.
     * @return array
     */
    private function resourceHintsPlainCleanup( $hints ) {
        foreach ( $hints as $key => $hint ) {
            if ( is_array( $hint ) && isset( $hint['href'] ) ) {
                if ( strpos( $hint['href'], '//s.w.org' ) !== false ) {
                    unset( $hints[ $key ] );
                }
            } elseif ( strpos( $hint, '//s.w.org' ) !== false ) {
                unset( $hints[ $key ] );
            }
        }

        return $hints;
    }

    public function heartbeatFrequency( $settings ) {
        $disable_heartbeat = Options::get( 'performance_disable_heartbeat' );
        if ( 'everywhere' === $disable_heartbeat ) {
            return $settings;
        }

        $heartbeat_frequency = Options::get( 'performance_heartbeat_frequency' );
        if ( empty( $heartbeat_frequency ) || ! is_numeric( $heartbeat_frequency ) ) {
            return $settings;
        }

        $settings['interval'] = absint( $heartbeat_frequency );

        return $settings;
    }

    /**
     * Disable WP Query Vars and hooks relating to embeds.
     *
     *  - Removes the needed query vars.
     *  - Disables oEmbed discovery.
     *  - Completely removes the related JavaScript.
     *  - Disables the core-embed/WordPress block type (WordPress 5.0+)
     *
     * @see https://github.com/wp-media/wp-rocket/blob/84978265d5d81c3eaf6e357bd06a33ff0f13d1ab/inc/deprecated/Engine/Media/Embeds/EmbedsSubscriber.php
     * @see https://github.com/swissspidy/disable-embeds/blob/a394b6fd42bc1d504f7a0909fa72ad6b19e02856/disable-embeds.php
     */
    private function disableEmbeds() {
        global $wp;

        if ( ! Options::get( 'performance_disable_embeds' ) ) {
            return;
        }

        // Remove the embed query var.
        $wp->public_query_vars = array_diff(
            $wp->public_query_vars,
            [
                'embed',
            ]
        );

        remove_filter( 'rest_endpoints', [ $this, 'disableEmbedEndpoint' ] );

        remove_filter( 'oembed_response_data', [ $this, 'emptyOembedResponseData' ] );

        // Turn off oEmbed auto discovery.
        add_filter( 'embed_oembed_discover', '__return_false' );

        self::add_filter( 'rewrite_rules_array', [ $this, 'disableEmbedsRewriteRules' ] );

        self::add_action( 'enqueue_block_editor_assets', [ $this, 'disableEmbedsEnqueueBlockEditorAssets' ] );

        self::add_action( 'wp_default_scripts', [ $this, 'disableWPEmbedDependency' ] );

        // Don't filter oEmbed results.
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result' );

        // Remove oEmbed discovery links.
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

        // Remove oEmbed-specific JavaScript from the front-end and back-end.
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );

        // Remove filter of the oEmbed result before any HTTP requests are made.
        remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result' );
    }

    /**
     * Remove the oembed/1.0/embed REST route.
     *
     * @param  array $endpoints Registered REST API endpoints.
     * @return array Filtered REST API endpoints.
     */
    private function disableEmbedEndpoint( $endpoints ) {
        unset( $endpoints['/oembed/1.0/embed'] );

        return $endpoints;
    }

    /**
     * Disables sending internal oEmbed response data in proxy endpoint.
     *
     * @param  array $data The response data.
     * @return array|false Response data or false if in a REST API context.
     */
    private function emptyOembedResponseData( $data ) {
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return false;
        }

        return $data;
    }

    /**
     * Disable all rewrite rules related to embeds.
     *
     * @param  array $rules WordPress rewrite rules.
     * @return array Rewrite rules without embeds rules.
     */
    private function disableEmbedsRewriteRules( $rules ) {
        if ( empty( $rules ) ) {
            return $rules;
        }

        foreach ( $rules as $rule => $rewrite ) {
            if ( false !== strpos( $rewrite, 'embed=true' ) ) {
                unset( $rules[ $rule ] );
            }
        }

        return $rules;
    }

    /**
     * Disable wp-embed dependency of core packages.
     *
     * @param \WP_Scripts $scripts WP_Scripts instance, passed by reference.
     */
    private function disableWPEmbedDependency( $scripts ) {
        if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
            $scripts->registered['wp-edit-post']->deps = array_diff(
                $scripts->registered['wp-edit-post']->deps,
                [ 'wp-embed' ]
            );
        }
    }

    /**
     * Enqueues JavaScript for the block editor.
     *
     * This is used to unregister the `core-embed/wordpress` block type.
     */
    private function disableEmbedsEnqueueBlockEditorAssets() {
        wp_enqueue_script(
            'hbp-disabler-disable-embeds',
            Assets::assetUrl( 'js/blocks/disable-embeds/index.js' ),
            [ 'wp-blocks', 'wp-dom-ready', 'wp-polyfill' ],
            null,
            true
        );
    }

    private function disableWidgets() {
        global $wp_meta_boxes;

        if ( 'no' === Options::get( 'performance_disable_widgets' ) ) {
            return;
        }

        if ( 'all' === Options::get( 'performance_disable_widgets' ) ) {
            $wp_meta_boxes['dashboard']['normal']['core'] = [];
            $wp_meta_boxes['dashboard']['side']['core']   = [];
        }

        if ( 'core' === Options::get( 'performance_disable_widgets' ) ) {
            remove_action( 'welcome_panel', 'wp_welcome_panel' );

            remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
            remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
            remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
            remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );

            // remove_meta_box( 'dashboard_php_nag', 'dashboard', 'normal' );
            // remove_meta_box( 'dashboard_browser_nag', 'dashboard', 'normal' );
            // remove_meta_box( 'health_check_status', 'dashboard', 'normal' );
            remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
            remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
            remove_meta_box( 'network_dashboard_right_now', 'dashboard', 'normal' );
            remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
            remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
            remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        }
    }

}
