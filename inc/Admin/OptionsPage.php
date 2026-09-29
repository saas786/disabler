<?php

/**
 * Plugin settings screen.
 */

namespace HBP\Disabler\Admin;

use HBP\Disabler\Facades\Assets;
use HBP\Disabler\Plugin;
use HBP\Settings\Ui\Admin\Page;
use HBP\Settings\Ui\PanelFactory;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\Collection;

/**
 * The settings screen, built from the control declarations in config.
 *
 * There is no rendering here any more. Controls, tabs, sections, ordering,
 * visibility and sanitizing all come from `disabler.controls.*` by way of
 * hbp/settings-ui. What is left is the plugin's own three decisions: where the
 * menu entry goes, who may see it, and which assets it needs.
 */
class OptionsPage implements Bootable {
    /**
     * Settings key/identifier.
     */
    public const OPTION = Plugin::SETTINGS_OPTION;

    /**
     * Menu slug of the settings screen.
     */
    public const SLUG = 'hbp-disabler-settings';

    private ?Page $page = null;

    public function __construct( private readonly PanelFactory $panels ) {}

    public function boot(): void {
        // These two are what Page::boot() registers. Calling it instead would
        // mean constructing Page here, at plugins_loaded -- and constructing
        // it reads the control declarations and translates the menu label,
        // neither of which is safe that early. Registering the callbacks
        // directly means the screen is built the first time something asks
        // for it, which is on an admin hook and therefore after init.
        add_action( 'admin_init', fn() => $this->page()->register() );
        add_action( 'admin_menu', fn() => $this->page()->menu() );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ], 1 );
    }

    /**
     * The screen.
     *
     * Built once and reused, because Page registers hooks and a second
     * instance would register them twice.
     */
    public function page(): Page {
        if ( $this->page instanceof Page ) {
            return $this->page;
        }

        $panel = $this->panels->make( 'disabler', self::OPTION );

        // One view, not one per tab. Page's own tab strip reloads the screen
        // to switch tabs; this plugin switches without one, so TabbedView
        // renders every tab and tabs.js reveals the current one.
        $view = new TabbedView( $panel, $panel->definitions()->tabs(), self::SLUG );

        $views = new Collection( [ $view->name() => $view ] );

        return $this->page = new Page(
            self::SLUG,
            $views,
            esc_html_x( 'Disabler', 'admin screen', 'hbp-disabler' ),
            'manage_options',
            'options-general.php'
        );
    }

    /**
     * Register the option. Once for the whole option, not once per tab.
     *
     * A wrapper rather than hooking Panel::registerSetting directly, for the
     * same reason boot() uses closures: reaching the panel's method means
     * building the panel, and building it reads the control declarations
     * before init. Going through the factory here defers that to admin_init.
     * PanelFactory memoises per namespace, so this is the same instance
     * page() uses, not a second one.
     */
    public function registerSetting(): void {
        $this->panels->make( 'disabler', self::OPTION )->registerSetting();
    }

    public function enqueueAssets(): void {
        if ( ! $this->isSettingsScreen() ) {
            return;
        }

        /** @var \Hybrid\Assets\Asset $script */
        $script = Assets::asset( 'js/admin/settings.js' );
        wp_enqueue_script(
            'hbp-disabler-wp-admin-settings',
            $script->url(),
            $script->dependencies(),
            $script->version(),
            true
        );

        /** @var \Hybrid\Assets\Asset $style */
        $style = Assets::asset( 'css/admin/settings.css' );
        wp_enqueue_style(
            'hbp-disabler-wp-admin-settings',
            $style->url(),
            $style->dependencies(),
            $style->version()
        );
    }

    /**
     * Whether the current request is this plugin's settings screen.
     *
     * admin_enqueue_scripts fires on every admin page, so without this the
     * settings assets load site-wide.
     */
    private function isSettingsScreen(): bool {
        return isset( $_GET['page'] ) && self::SLUG === sanitize_key( wp_unslash( $_GET['page'] ) );
    }
}
