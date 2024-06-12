<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use HBP\Disabler\Contracts\Traits\Utils;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

/**
 * Class Editor
 * Handles various editor optimizations and configurations.
 */
class Editor implements Bootable {

    use AccessiblePrivateMethods;
    use Utils;

    /**
     * Boot the editor optimization hooks.
     */
    public function boot(): void {
        self::add_action( 'init', [ $this, 'initializeHooks' ], 0 );
        self::add_action( 'plugins_loaded', [ $this, 'onPluginsLoaded' ], 0 );
    }

    /**
     * Initialize hooks for the editor optimization.
     */
    private function initializeHooks(): void {
        $this->disableClassicThemeStyles();
        $this->disableTexturization();
        $this->disableCapitalPFilter();
        $this->disableAutoParagraph();
    }

    /**
     * Disable classic theme styles if the option is enabled.
     */
    private function disableClassicThemeStyles(): void {
        if ( Options::get( 'editor_disable_classic_theme_styles' ) ) {
            remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
            remove_filter( 'block_editor_settings_all', 'wp_add_editor_classic_theme_styles' );
        }
    }

    /**
     * Disable texturization if the option is enabled.
     */
    private function disableTexturization(): void {
        if ( Options::get( 'editor_disable_texturization' ) ) {
            $texturizeFilters = [
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
            ];

            foreach ( $texturizeFilters as $filter ) {
                remove_filter( $filter, 'wptexturize' );
            }
        }
    }

    /**
     * Disable the capital P filter if the option is enabled.
     */
    private function disableCapitalPFilter(): void {
        if ( Options::get( 'editor_disable_capital_p' ) ) {
            $capitalPFilters = [ 'the_title', 'the_content', 'comment_text' ];
            foreach ( $capitalPFilters as $filter ) {
                remove_filter( $filter, 'capital_P_dangit', 'comment_text' === $filter ? 31 : 11 );
            }
        }
    }

    /**
     * Disable automatic paragraph creation if the option is enabled.
     */
    private function disableAutoParagraph(): void {
        if ( Options::get( 'editor_disable_autop' ) ) {
            remove_filter( 'the_content', 'wpautop', 10 );
        }
    }

    /**
     * Handle disabling of the autosave feature for the classic editor.
     */
    public function handleClassicEditorAutosave(): void {
        $disableAutosave  = Options::get( 'editor_disable_autosave' );
        $autosaveInterval = Options::get( 'editor_autosave_interval' );

        if ( 'yes' === $disableAutosave ) {
            wp_deregister_script( 'autosave' );
        } elseif ( 'no' === $disableAutosave && ! empty( $autosaveInterval ) && is_numeric( $autosaveInterval ) ) {
            if ( defined( 'AUTOSAVE_INTERVAL' ) ) {
                $inlineJs = "
                    if (typeof autosaveL10n !== 'undefined') {
                        autosaveL10n.autosaveInterval = " . absint( $autosaveInterval ) . ';
                    }
                ';
                wp_add_inline_script( 'autosave', $inlineJs, 'before' );
            }
        }
    }

    /**
     * Initialize actions once plugins are loaded.
     */
    private function onPluginsLoaded(): void {
        $this->initializeClassicEditorAutosave();
        $this->initializeBlockEditorAutosave();
    }

    /**
     * Initialize autosave settings for the classic editor.
     */
    private function initializeClassicEditorAutosave(): void {
        add_action( 'wp_print_scripts', [ $this, 'handleClassicEditorAutosave' ] );
        $this->setClassicEditorAutosaveInterval();
    }

    /**
     * Set autosave interval for the classic editor.
     */
    private function setClassicEditorAutosaveInterval(): void {
        $autosaveInterval = Options::get( 'editor_autosave_interval' );

        if ( ! empty( $autosaveInterval ) && is_numeric( $autosaveInterval ) ) {
            if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
                define( 'AUTOSAVE_INTERVAL', absint( $autosaveInterval ) );
            }
        }
    }

    /**
     * Initialize autosave settings for the block editor.
     */
    private function initializeBlockEditorAutosave(): void {
        if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
            $disableAutosave  = Options::get( 'editor_disable_autosave' );
            $autosaveInterval = Options::get( 'editor_autosave_interval' );

            if ( 'yes' === $disableAutosave ) {
                define( 'AUTOSAVE_INTERVAL', HOUR_IN_SECONDS * 24 );
            } elseif ( 'no' === $disableAutosave && ! empty( $autosaveInterval ) && is_numeric( $autosaveInterval ) ) {
                define( 'AUTOSAVE_INTERVAL', absint( $autosaveInterval ) );
            }
        } else {
            add_filter( 'block_editor_settings_all', [ $this, 'setBlockEditorAutosaveInterval' ], \PHP_INT_MAX );
        }
    }

    /**
     * Set autosave interval for the block editor.
     */
    public function setBlockEditorAutosaveInterval( array $editorSettings ): array {
        $disableAutosave  = Options::get( 'editor_disable_autosave' );
        $autosaveInterval = Options::get( 'editor_autosave_interval' );

        if ( 'yes' === $disableAutosave ) {
            $editorSettings['autosaveInterval'] = HOUR_IN_SECONDS * 24;
        } elseif ( 'no' === $disableAutosave && ! empty( $autosaveInterval ) && is_numeric( $autosaveInterval ) ) {
            $editorSettings['autosaveInterval'] = absint( $autosaveInterval );
        }

        return $editorSettings;
    }

}
