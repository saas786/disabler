<?php

/**
 * The settings screen as one form with client-side tabs.
 */

namespace HBP\Disabler\Admin;

use HBP\Disabler\Facades\Assets;
use HBP\Settings\Ui\Admin\View;
use HBP\Settings\Ui\Panel;

/**
 * One view holding every tab.
 *
 * hbp/settings-ui ships Page, which draws one tab per View and switches
 * between them with a `?view=` reload. This plugin switched tabs without a
 * reload before, so it keeps doing that: a single View, one form, and every
 * tab rendered into its own panel for tabs.js to reveal.
 *
 * Each tab still registers against its own Settings API page slug. Sharing
 * one slug is what made every tab print every control: do_settings_sections()
 * takes a page, not a tab, so all sections registered to that page come out
 * on all of them.
 */
class TabbedView extends View {
    /**
     * @param array<int, string> $tabs Tab slugs, in display order.
     */
    public function __construct(
        protected readonly Panel $panel,
        protected readonly array $tabs,
        protected readonly string $page
    ) {}

    public function name(): string {
        return 'settings';
    }

    /**
     * Required by View, never displayed.
     *
     * Page draws its tab strip from view labels, but skips the strip entirely
     * below two views -- and this plugin has exactly one, because its tabs are
     * inside the view rather than being views. So the label has nowhere to
     * appear. It stays because View declares it abstract; it stays empty
     * because inventing a string would only make it look like it renders.
     */
    public function label(): string {
        return '';
    }

    /**
     * One Settings API page per tab.
     */
    public function register(): void {
        foreach ( $this->tabs as $tab ) {
            $this->panel->registerTab( $tab, $this->pageFor( $tab ) );
        }
    }

    public function boot(): void {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueTabs' ] );
    }

    public function enqueueTabs(): void {
        /** @var \Hybrid\Assets\Asset $script */
        $script = Assets::asset( 'js/admin/tabs.js' );

        wp_enqueue_script(
            'hbp-disabler-wp-admin-tabs',
            $script->url(),
            $script->dependencies(),
            $script->version(),
            true
        );
    }

    public function template(): void {
        echo '<div class="hbp-disabler-form-wrap">';

        printf( '<form method="post" action="%s">', esc_url( admin_url( 'options.php' ) ) );

        settings_fields( $this->panel->option() );

        echo '<div class="nav-tabs nav-tabs-orientation-horizontal">';

        $this->nav();
        $this->panels();

        echo '</div>';

        submit_button();

        echo '</form>';
        echo '</div>';
    }

    /**
     * The tab strip, with the scroll arrows tabs.js drives.
     */
    private function nav(): void {
        echo '<div class="nav-tabs-container">';

        printf(
            '<button class="scroll-arrow prev-arrow" aria-label="%s" hidden>%s</button>',
            esc_attr__( 'Previous', 'hbp-disabler' ),
            Assets::svg( '/svg/arrow-prev-small.svg' )->sanitize( false )->render() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );

        echo '<div class="tabs-wrapper">';
        echo '<nav class="nav-tab-wrapper hide-if-no-js" role="tablist" aria-orientation="horizontal">';

        foreach ( $this->tabs as $tab ) {
            printf(
                '<a href="#%1$s" id="nav-tab-%1$s" class="nav-tab" role="tab">%2$s</a>',
                esc_attr( $tab ),
                esc_html( $this->panel->definitions()->tabLabel( $tab ) )
            );
        }

        echo '</nav>';
        echo '</div>';

        printf(
            '<button class="scroll-arrow next-arrow" aria-label="%s" hidden>%s</button>',
            esc_attr__( 'Next', 'hbp-disabler' ),
            Assets::svg( '/svg/arrow-next-small.svg' )->sanitize( false )->render() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );

        echo '</div>';
    }

    /**
     * One panel per tab.
     *
     * Every panel is rendered, and tabs.js reveals one. `hide-if-js` keeps
     * all of them visible without scripting, which is the only reason the
     * screen still works when tabs.js fails to load.
     */
    private function panels(): void {
        echo '<div class="nav-tab-content">';

        foreach ( $this->tabs as $tab ) {
            printf(
                '<section id="tab-%1$s" class="hide-if-js" role="tabpanel" aria-labelledby="nav-tab-%1$s">',
                esc_attr( $tab )
            );

            do_settings_sections( $this->pageFor( $tab ) );

            echo '</section>';
        }

        echo '</div>';
    }

    /**
     * A tab's Settings API page slug. Unique per tab, deliberately.
     */
    private function pageFor( string $tab ): string {
        return $this->page . '-' . $tab;
    }
}
