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
        // Texturization.
        if ( Options::get( 'frontend_disable_texturization' ) ) {
            $filters_wptexturize = [
                'comment_author',
                'term_name',
                'link_name',
                'link_description',
                'link_notes',
                'bloginfo',
                'wp_title',
                'widget_title',

                'single_post_title',
                'single_cat_title',
                'single_tag_title',
                'single_month_title',
                'nav_menu_attr_title',
                'nav_menu_description',

                'term_description',
                'get_the_post_type_description',

                'the_title',
                'the_content',
                'the_excerpt',
                'the_post_thumbnail_caption',
                'comment_text',
                'list_cats',
                'widget_text_content',
                'the_excerpt_embed',

                // 'the_content_feed',
                // 'category_description',
            ];

            array_walk( $filters_wptexturize, static function ( $filter ) {
                remove_filter( $filter, 'wptexturize' );
            } );
        }

        // Disable Capital P in WordPress auto-correct.
        if ( Options::get( 'frontend_disable_capital_p' ) ) {
            remove_filter( 'the_title', 'capital_P_dangit', 11 );
            remove_filter( 'the_content', 'capital_P_dangit', 11 );
            remove_filter( 'comment_text', 'capital_P_dangit', 31 );
        }

        // Remove the <p> from being automagically added in posts.
        if ( Options::get( 'frontend_disable_autop' ) ) {
            $filters_autop = [
                [
                    'name'     => 'the_content',
                    'priority' => 10,
                ],
                // [ 'name' => 'term_description', 'priority' => 10 ],
                // [ 'name' => 'get_the_post_type_description', 'priority' => 10 ],
                // [ 'name' => 'the_excerpt', 'priority' => 10 ],
                // [ 'name' => 'widget_text_content', 'priority' => 10 ],
                // [ 'name' => 'the_excerpt_embed', 'priority' => 10 ],
                // [ 'name' => 'comment_text', 'priority' => 30 ],
            ];

            array_walk( $filters_autop, static function ( $filter ) {
                remove_filter( $filter['name'], 'wpautop', $filter['priority'] );
            } );
        }

        // Disable shortlinks.
        if ( Options::get( 'frontend_disable_shortlinks' ) ) {
            // Disable HTML meta tag.
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );

            // Disable HTTP header.
            remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
        }
    }

}
