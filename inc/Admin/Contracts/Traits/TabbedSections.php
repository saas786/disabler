<?php

/**
 * Until core lands this patch, utilize this code.
 *
 * @see https://core.trac.wordpress.org/ticket/51086
 * @see https://github.com/stevegrunwell/wp-admin-tabbed-settings-pages
 */

namespace HBP\Disabler\Admin\Contracts\Traits;

use HBP\Disabler\Facades\Assets;
use HBP\Disabler\Tools\SVG;
use function Hybrid\Tools\value;

trait TabbedSections {

    public static function bootTabbedSections(): void {
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'registerAssets' ], 1 );
    }

    /**
     * Register assets.
     */
    public static function registerAssets(): void {
        /*
         * Register (but do not enqueue) the tab scripting within WP-Admin.
         *
         * Note that the Trac version uses "settings-tabs" as the hook to prevent conflict.
         */
        wp_register_script(
            'hbp-disabler-wp-admin-tabs',
            Assets::assetUrl( 'js/admin/tabs.js' ),
            [],
            null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
            true
        );
    }

    /**
     * Render settings sections for a particular page using a tabbed interface.
     *
     * This function operates the same as do_settings_sections() as part of the Settings API.
     *
     * @global array $wp_settings_fields Storage array of settings fields and info about their pages/sections.
     * @global array $wp_settings_sections Storage array of all settings sections added to admin pages.
     * @param string $page The slug name of the page whose settings sections you want to output.
     */
    public function renderTabbedSections( $page, $args = [] ) {
        global $wp_settings_sections, $wp_settings_fields;

        if ( ! isset( $wp_settings_sections[ $page ] ) ) {
            return;
        }

        $sections       = (array) $wp_settings_sections[ $page ];
        $sections_order = $args['sections-order'];

        // Sort the array by the specified key order.
        usort( $sections, static function ( $a, $b ) use ( $sections_order ) {
            $posA = array_search( $a['id'], $sections_order );
            $posB = array_search( $b['id'], $sections_order );

            return $posA - $posB;
        } );

        // If there's only one section, don't bother rendering tabs.
        if ( 1 >= count( $sections ) ) {
            $this->renderSections( $page );

            return;
        }

        echo '<div class="nav-tabs nav-tabs-orientation-' . $args['tab-orientation'] . '">';

        echo '<div class="nav-tabs-container">';
        echo '<button class="scroll-arrow prev-arrow" aria-label="Previous" hidden>';
        echo SVG::render( 'arrow-prev-small' );
        echo '</button>';
        echo '<div class="tabs-wrapper">';
        echo '<nav class="nav-tab-wrapper hide-if-no-js" role="tablist" aria-orientation="' . $args['tab-orientation'] . '">';

        foreach ( $sections as $section ) {
            printf(
                '<a href="#%1$s" id="nav-tab-%1$s" class="nav-tab" role="tab">%2$s</a>',
                esc_attr( $section['id'] ),
                esc_html( value( $section['title'] ) )
            );
        }

        echo '</nav><!-- .nav-tab-wrapper -->';
        echo '</div><!-- .tabs-wrapper -->';
        echo '<button class="scroll-arrow next-arrow" aria-label="Next" hidden>';
        echo SVG::render( 'arrow-next-small' );
        echo '</button>';
        echo '</div><!-- .nav-tabs-container -->';

        echo '<div class="nav-tab-content">';

        foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
            printf( '<section id="tab-%1$s" class="hide-if-js" role="tabpanel" aria-labelledby="nav-tab-%1$s">', esc_attr( $section['id'] ) );

            $section_title = value( $section['title'] );
            if ( $section_title ) {
                printf( '<h2 class="tabbed-section-heading">%1$s</h2>%2$s', esc_html( $section_title ), PHP_EOL );
            }

            if ( is_callable( $section['callback'] ) ) {
                call_user_func( $section['callback'], $section );
            }

            if ( isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
                echo '<table class="form-table" role="presentation">';
                do_settings_fields( $page, $section['id'] );
                echo '</table>';
            }

            echo '</section>';
        }

        echo '</div><!-- .nav-tab-content -->';

        echo '</div><!-- .nav-tabs -->';

        // Finally, ensure the necessary scripts are enqueued.
        wp_enqueue_script( 'hbp-disabler-wp-admin-tabs' );
    }

    /**
     * Use WP Default sections rendering.
     */
    public function renderSections( $page ): void {
        do_settings_sections( $page );
    }

}
