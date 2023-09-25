<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use stdClass;

class Updates implements Bootable {

    use AccessiblePrivateMethods;

    /**
     * Boot.
     */
    public function boot() {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
    }

    private function initHooks(): void {
        if ( 'no' === Options::get( 'updates_disable_updates' ) ) {
            return;
        }

        self::add_filter( 'wp_get_update_data', [ $this, 'updateCounter' ], 10, 2 );
        self::add_action( 'admin_init', [ $this, 'updateNotice' ] );
        self::add_filter( 'site_status_tests', [ $this, 'siteStatusTests' ] );

        /*
         * Filter / Disable schedule checks.
         *
         * @link https://wordpress.org/support/topic/possible-performance-improvement/#post-8970451
         */
        self::add_action( 'admin_init', [ $this, 'disableScheduleHook' ] );
        self::add_action( 'schedule_event', [ $this, 'filterCronEvents' ] );

        self::add_filter( 'bulk_actions-plugins', [ $this, 'disableBulkActionsPlugins' ] );
        self::add_filter( 'bulk_actions-plugins-network', [ $this, 'disableBulkActionsPlugins' ] );

        // Remove bulk action for updating themes.
        self::add_filter( 'bulk_actions-themes', [ $this, 'disableBulkActionsThemes' ] );
        self::add_filter( 'bulk_actions-themes-network', [ $this, 'disableBulkActionsThemes' ] );

        // Time based transient checks.
        self::add_filter( 'pre_site_transient_update_core', [ $this, 'lastCheckedCore' ] );

        self::add_filter( 'automatic_updates_is_vcs_checkout', [ $this, 'isVCSCheckout' ] );

        // Handle minor core updates.
        self::add_filter( 'allow_minor_auto_core_updates', [ $this, 'allowMinorAutoCoreUpdates' ] );

        // Handle major core updates.
        self::add_filter( 'allow_major_auto_core_updates', [ $this, 'allowMajorAutoCoreUpdates' ] );

        // Handle dev core updates.
        self::add_filter( 'allow_dev_auto_core_updates', [ $this, 'allowDevAutoCoreUpdates' ] );

        // Disable overall core updates.
        self::add_filter( 'auto_update_core', [ $this, 'autoUpdateCore' ] );

        // Get rid of the version number in the footer.
        self::add_filter( 'update_footer', [ $this, 'updateFooter' ], 11 );

        // Disable automatic plugin updates (used by WP to force push security fixes).
        self::add_filter( 'auto_update_plugin', [ $this, 'autoUpdatePlugin' ] );

        // Hide UI to edit the plugins auto-update update option on plugins list.
        self::add_filter( 'plugins_auto_update_enabled', [ $this, 'pluginsAutoUpdateEnabled' ] );

        // Disable automatic theme updates (used by WP to force push security fixes).
        self::add_filter( 'auto_update_theme', [ $this, 'autoUpdateTheme' ], 1 );

        // Hide UI to edit the themes auto-update update option on themes list.
        self::add_filter( 'themes_auto_update_enabled', [ $this, 'themesAutoUpdateEnabled' ] );

        add_filter( 'auto_update_translation', [ $this, 'autoUpdateTranslation' ] );
        add_filter( 'async_update_translation', [ $this, 'asyncUpdateTranslation' ] );

        $this->handleGeneralUpdates();
        $this->handlePluginUpdates();
        $this->handleThemeUpdates();
        $this->handleTranslationUpdates();
        $this->handleUpdateEmails();
        $this->handleConstants();
    }

    private function handleGeneralUpdates() {
        if ( 'all' !== Options::get( 'updates_disable_updates' ) ) {
            return;
        }

        // Admin UI items.
        self::add_action( 'admin_menu', [ $this, 'adminMenuItems' ], 9999 );
        self::add_action( 'network_admin_menu', [ $this, 'msAdminMenuItems' ], 9999 );

        // Disable automatic updater updates.
        add_filter( 'automatic_updater_disabled', '__return_true' );

        // Disable update check schedule.
        remove_action( 'init', 'wp_schedule_update_checks' );

        // Define core constants for more protection.
        if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) {
            define( 'AUTOMATIC_UPDATER_DISABLED', true );
        }

        add_action( 'admin_init', static function () {
            // Runs for core, plugin and themes, cron hook.
            remove_action( 'wp_maybe_auto_update', 'wp_maybe_auto_update' );
        } );
    }

    /**
     * Remove menu items for updates from a standard WP install.
     */
    private function adminMenuItems() {
        if ( is_multisite() ) {
            return;
        }

        // Remove our items.
        remove_submenu_page( 'index.php', 'update-core.php' );
    }

    /**
     * Remove menu items for updates from a multisite instance.
     */
    private function msAdminMenuItems() {
        if ( ! is_network_admin() ) {
            return;
        }

        // Remove the items.
        remove_submenu_page( 'index.php', 'upgrade.php' );
    }

    /**
     * callback for filter wp_get_update_data.
     */
    private function updateCounter( $update_data, $titles ) {
        if ( 'all' === Options::get( 'updates_disable_updates' ) ) {
            return [
                'counts' => [
                    'plugins'      => 0,
                    'themes'       => 0,
                    'wordpress'    => 0,
                    'translations' => 0,
                    'total'        => 0,
                ],
                'title'  => 'No updates available',
            ];
        }

        if (
            'selective' === Options::get( 'updates_disable_updates' )
            && 'disable_core_updates' === Options::get( 'updates_core_updates' )
            && 0 < $update_data['counts']['wordpress']
        ) {
            // Reduce WordPress count from total.
            $update_data['counts']['total'] = $update_data['counts']['total'] - $update_data['counts']['wordpress'];

            // As we are disabling WordPress updates, set WordPress updates count to 0.
            $update_data['counts']['wordpress'] = 0;

            // Adjust update title.
            unset( $titles['wordpress'] );
            $update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';
        }

        if (
            'selective' === Options::get( 'updates_disable_updates' )
            && 'disable' === Options::get( 'updates_plugin_updates' ) && 0 < $update_data['counts']['plugins'] ) {
            // Reduce plugin count from total.
            $update_data['counts']['total'] = $update_data['counts']['total'] - $update_data['counts']['plugins'];

            // As we are disabling plugin updates, set plugin updates count to 0.
            $update_data['counts']['plugins'] = 0;

            // Adjust update title.
            unset( $titles['plugins'] );
            $update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';
        }

        if (
            'selective' === Options::get( 'updates_disable_updates' )
            && 'disable' === Options::get( 'updates_theme_updates' ) && 0 < $update_data['counts']['themes'] ) {
            // Reduce theme count from total.
            $update_data['counts']['total'] = $update_data['counts']['total'] - $update_data['counts']['themes'];

            // As we are disabling theme updates, set theme updates count to 0.
            $update_data['counts']['themes'] = 0;

            // Adjust update title.
            unset( $titles['themes'] );
            $update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';
        }

        if (
            'selective' === Options::get( 'updates_disable_updates' )
            && 'disable' === Options::get( 'updates_translation_updates' ) && 0 < $update_data['counts']['translations'] ) {
            // Reduce translation count from total.
            $update_data['counts']['total'] = $update_data['counts']['total'] - $update_data['counts']['translations'];

            // As we are disabling translation updates, set translation updates count to 0.
            $update_data['counts']['translations'] = 0;

            // Adjust update title.
            unset( $titles['translations'] );
            $update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';
        }

        return $update_data;
    }

    /**
     * Hide update checks in the Site Health screen.
     */
    private function siteStatusTests( $tests ) {
        if ( 'all' === Options::get( 'updates_disable_updates' ) ) {
            if ( isset( $tests['async']['background_updates'] ) ) {
                unset( $tests['async']['background_updates'] );
            }

            if ( isset( $tests['direct']['wordpress_version'] ) ) {
                unset( $tests['direct']['wordpress_version'] );
            }

            if ( isset( $tests['direct']['plugin_theme_auto_updates'] ) ) {
                unset( $tests['direct']['plugin_theme_auto_updates'] );
            }

            if ( isset( $tests['direct']['plugin_version'] ) ) {
                unset( $tests['direct']['plugin_version'] );
            }

            if ( isset( $tests['direct']['theme_version'] ) ) {
                unset( $tests['direct']['theme_version'] );
            }

            return $tests;
        }

        if ( 'selective' === Options::get( 'updates_disable_updates' ) ) {

            if ( 'disable_core_updates' === Options::get( 'updates_core_updates' ) ) {
                if ( isset( $tests['async']['background_updates'] ) ) {
                    unset( $tests['async']['background_updates'] );
                }

                if ( isset( $tests['direct']['wordpress_version'] ) ) {
                    unset( $tests['direct']['wordpress_version'] );
                }
            }

            if (
                'disable' === Options::get( 'updates_plugin_updates' )
                || 'disable' === Options::get( 'updates_theme_updates' )
            ) {
                if ( isset( $tests['direct']['plugin_theme_auto_updates'] ) ) {
                    unset( $tests['direct']['plugin_theme_auto_updates'] );
                }
            }

            if ( 'disable' === Options::get( 'updates_plugin_updates' ) ) {
                if ( isset( $tests['direct']['plugin_version'] ) ) {
                    unset( $tests['direct']['plugin_version'] );
                }
            }

            if ( 'disable' === Options::get( 'updates_theme_updates' ) ) {
                if ( isset( $tests['direct']['theme_version'] ) ) {
                    unset( $tests['direct']['theme_version'] );
                }
            }
        }

        return $tests;
    }

    /**
     * Remove all the various schedule hooks for themes, plugins, etc.
     */
    private function disableScheduleHook() {
        if ( 'all' === Options::get( 'updates_disable_updates' ) ) {
            wp_clear_scheduled_hook( 'wp_update_themes' );
            wp_clear_scheduled_hook( 'wp_update_plugins' );
            wp_clear_scheduled_hook( 'wp_version_check' );
            wp_clear_scheduled_hook( 'wp_maybe_auto_update' );

            return;
        }

        if ( 'selective' === Options::get( 'updates_disable_updates' ) ) {
            if ( 'disable' === Options::get( 'updates_theme_updates' ) ) {
                wp_clear_scheduled_hook( 'wp_update_themes' );
            }

            if ( 'disable' === Options::get( 'updates_plugin_updates' ) ) {
                wp_clear_scheduled_hook( 'wp_update_plugins' );
            }

            if ( 'disable_core_updates' === Options::get( 'updates_core_updates' ) ) {
                wp_clear_scheduled_hook( 'wp_version_check' );
                // wp_clear_scheduled_hook( 'wp_maybe_auto_update' );
            }
        }
    }

    /**
     * Filter cron events
     *
     * @see https://wordpress.org/support/topic/possible-performance-improvement/#post-8970451
     * @return bool
     */
    private function filterCronEvents( $event ) {
        if ( ! is_object( $event ) || empty( $event->hook ) ) {
            return $event;
        }

        if ( 'all' === Options::get( 'updates_disable_updates' ) ) {
            switch ( $event->hook ) {
                case 'wp_version_check':
                case 'wp_maybe_auto_update':
                case 'wp_update_plugins':
                case 'wp_update_themes':
                    $event = false;
                    break;
            }
        }

        if ( 'selective' === Options::get( 'updates_disable_updates' ) ) {
            $core_updates    = Options::get( 'updates_core_updates' ) == 'disable_core_updates';
            $plugins_updates = Options::get( 'updates_plugin_updates' ) == 'disable';
            $themes_updates  = Options::get( 'updates_theme_updates' ) == 'disable';

            switch ( $event->hook ) {
                case 'wp_version_check':
                    // case 'wp_maybe_auto_update':
                    $event = $core_updates ? false : $event;
                    break;
                case 'wp_update_plugins':
                    $event = $plugins_updates ? false : $event;
                    break;
                case 'wp_update_themes':
                    $event = $themes_updates ? false : $event;
                    break;
            }
        }

        return $event;
    }

    private function handleThemeUpdates() {
        if (
            'all' === Options::get( 'updates_disable_updates' )
            || (
                'selective' === Options::get( 'updates_disable_updates' )
                && Options::get( 'updates_theme_updates' ) == 'disable'
            )
        ) {

            // Necessary to remove after `admin_init` action.
            add_action( 'admin_init', static function () {
                // Remove update notice on themes list for each individual theme.
                remove_action( 'load-themes.php', 'wp_theme_update_rows', 20 );
            } );

            // Time based transient checks.
            self::add_filter( 'pre_site_transient_update_themes', [ $this, 'lastCheckedThemes' ] );
            self::add_filter( 'site_transient_update_themes', [ $this, 'removeUpdateThemesArray' ] );

            // Disable Theme Updates checks.
            remove_action( 'load-themes.php', 'wp_update_themes' );
            remove_action( 'load-update.php', 'wp_update_themes' );
            remove_action( 'load-update-core.php', 'wp_update_themes' );
            remove_action( 'admin_init', '_maybe_update_themes' );
            remove_action( 'wp_update_themes', 'wp_update_themes' );
        }
    }

    /**
     * Disable the ability to update themes from single
     * site and multisite bulk actions.
     *
     * @param  array $actions All the bulk actions.
     * @return array $actions  The remaining actions
     */
    private function disableBulkActionsThemes( $actions ) {
        $disable_updates = Options::get( 'updates_disable_updates' );
        $theme_updates   = Options::get( 'updates_theme_updates' );

        if ( 'all' === $disable_updates || ( 'selective' === $disable_updates && 'disable' === $theme_updates ) ) {
            $remove_actions = [ 'update-selected', 'update', 'upgrade', 'enable-auto-update-selected', 'disable-auto-update-selected' ];
        } elseif ( 'selective' === $disable_updates && 'manual' === $theme_updates ) {
            $remove_actions = [ 'enable-auto-update-selected', 'disable-auto-update-selected' ];
        } else {
            return $actions;
        }

        foreach ( $remove_actions as $key ) {
            if ( isset( $actions[ $key ] ) ) {
                unset( $actions[ $key ] );
            }
        }

        return $actions;
    }

    /**
     * Always send back that the latest version of our theme is the one we're running.
     *
     * @return object the modified output with our information
     */
    private function lastCheckedThemes() {
        // Call the global WP version.
        global $wp_version;

        // Set a blank data array.
        $data = [];

        // Build my theme data array.
        foreach ( wp_get_themes() as $theme ) {
            $data[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
        }

        // Return our object.
        return (object) [
            'last_checked'    => time(),
            'updates'         => [],
            'version_checked' => $wp_version,
            'checked'         => $data,
        ];
    }

    /**
     * Return an empty array of items requiring update for themes.
     *
     * @param  array $items All the items being passed for update.
     * @return array An empty array, or the original items if not enabled.
     */
    private function removeUpdateThemesArray( $items ) {
        return [];
    }

    private function lastCheckedNow( $transient ) {
        global $wp_version;

        include ABSPATH . WPINC . '/version.php';

        $current                  = new stdClass();
        $current->updates         = [];
        $current->version_checked = $wp_version;
        $current->last_checked    = time();

        return $current;
    }

    private function handlePluginUpdates() {
        if (
            'all' === Options::get( 'updates_disable_updates' )
            || (
                'selective' === Options::get( 'updates_disable_updates' )
                && Options::get( 'updates_plugin_updates' ) == 'disable'
            )
        ) {

            // Necessary to remove after `admin_init` action.
            add_action( 'admin_init', static function () {
                // Remove update notice on plugins list for each individual plugin.
                remove_action( 'load-plugins.php', 'wp_plugin_update_rows', 20 );
            } );

            // Time based transient checks.
            self::add_filter( 'pre_site_transient_update_plugins', [ $this, 'lastCheckedPlugins' ] );
            self::add_filter( 'site_transient_update_plugins', [ $this, 'removePluginsUpdateArray' ] );

            // Disable Plugin Updates checks.
            remove_action( 'load-update-core.php', 'wp_update_plugins' );
            remove_action( 'load-plugins.php', 'wp_update_plugins' );
            remove_action( 'load-update.php', 'wp_update_plugins' );
            remove_action( 'wp_update_plugins', 'wp_update_plugins' );
            remove_action( 'admin_init', '_maybe_update_plugins' );
        }
    }

    /**
     * Disable the ability to update plugins from single
     * site and multisite bulk actions.
     *
     * @param  array $actions All the bulk actions.
     * @return array $actions  The remaining actions
     */
    private function disableBulkActionsPlugins( $actions ) {
        $disable_updates = Options::get( 'updates_disable_updates' );
        $plugin_updates  = Options::get( 'updates_plugin_updates' );

        if ( 'all' === $disable_updates || ( 'selective' === $disable_updates && 'disable' === $plugin_updates ) ) {
            $remove_actions = [ 'update-selected', 'update', 'upgrade', 'enable-auto-update-selected', 'disable-auto-update-selected' ];
        } elseif ( 'selective' === $disable_updates && 'manual' === $plugin_updates ) {
            $remove_actions = [ 'enable-auto-update-selected', 'disable-auto-update-selected' ];
        } else {
            return $actions;
        }

        foreach ( $remove_actions as $key ) {
            if ( isset( $actions[ $key ] ) ) {
                unset( $actions[ $key ] );
            }
        }

        return $actions;
    }

    /**
     * Always send back that the latest version of our plugins are the one we're running
     *
     * @return object the modified output with our information
     */
    private function lastCheckedPlugins() {
        // Call the global WP version.
        global $wp_version;

        // Set a blank data array.
        $data = [];

        // Add our plugin file if we don't have it.
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Build my plugin data array.
        foreach ( get_plugins() as $file => $plugin ) {
            $data[ $file ] = $plugin['Version'];
        }

        // Return our object.
        return (object) [
            'last_checked'    => time(),
            'updates'         => [],
            'version_checked' => $wp_version,
            'checked'         => $data,
        ];
    }

    /**
     * Return an empty array of items requiring update for both plugins.
     *
     * @param  array $items All the items being passed for update.
     * @return array An empty array, or the original items if not enabled.
     */
    private function removePluginsUpdateArray( $items ) {
        return [];
    }

    private function handleTranslationUpdates() {
        if (
            'all' === Options::get( 'updates_disable_updates' )
            || (
                'selective' === Options::get( 'updates_disable_updates' )
                && Options::get( 'updates_translation_updates' ) == 'disable'
            )
        ) {
            // Core translation notifications.
            self::add_filter( 'site_transient_update_core', [ $this, 'disableTranslationUpdates' ] );

            // Plugin translation notifications.
            self::add_filter( 'site_transient_update_plugins', [ $this, 'disableTranslationUpdates' ] );

            // Theme translation notifications.
            self::add_filter( 'site_transient_update_themes', [ $this, 'disableTranslationUpdates' ] );
        }
    }

    private function disableTranslationUpdates( $transient ) {
        if ( is_object( $transient ) && isset( $transient->translations ) ) {
            $transient->translations = [];
        }

        return $transient;
    }

    /**
     * Tell WordPress we are on a version control system to add additional blocks.
     * Disable Auto-update updates, 'false' allows the update.
     *
     * @return bool
     */
    private function isVCSCheckout( $checkout ) {
        $disable_updates    = Options::get( 'updates_disable_updates' );
        $core_updates       = Options::get( 'updates_core_updates' );
        $enable_VCS_updates = Options::get( 'updates_enable_update_vcs' );

        if (
            'all' === $disable_updates
            || (
                'selective' === $disable_updates
                && (
                    'disable_core_updates' === $core_updates
                    || 'enable' === $enable_VCS_updates
                )
            )
        ) {
            return true;
        }

        if ( 'selective' === $disable_updates && 'disable' === $enable_VCS_updates ) {
            return false;
        }

        return $checkout;
    }

    private function allowMinorAutoCoreUpdates( $upgrade_minor ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        if ( 'selective' === $disable_updates ) {
            $core_updates = Options::get( 'updates_core_updates' );

            if ( 'disable_core_auto_updates' === $core_updates || 'disable_core_updates' === $core_updates ) {
                return false;
            }

            return 'allow_minor_core_auto_updates' === $core_updates;
        }

        return $upgrade_minor;
    }

    private function allowMajorAutoCoreUpdates( $upgrade_major ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        if ( 'selective' === $disable_updates ) {
            $core_updates = Options::get( 'updates_core_updates' );

            if ( 'disable_core_auto_updates' === $core_updates || 'disable_core_updates' === $core_updates ) {
                return false;
            }

            return 'allow_major_core_auto_updates' === $core_updates;
        }

        return $upgrade_major;
    }

    private function allowDevAutoCoreUpdates( $upgrade_major ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        if ( 'selective' === $disable_updates ) {
            $core_updates = Options::get( 'updates_core_updates' );

            if ( 'disable_core_auto_updates' === $core_updates || 'disable_core_updates' === $core_updates ) {
                return false;
            }

            return 'allow_dev_core_auto_updates' === $core_updates;
        }

        return $upgrade_major;
    }

    private function autoUpdateCore( $update ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        if ( 'selective' === $disable_updates ) {
            $core_updates = Options::get( 'updates_core_updates' );

            if ( 'disable_core_auto_updates' === $core_updates || 'disable_core_updates' === $core_updates ) {
                return false;
            }
        }

        return $update;
    }

    /**
     * Get rid of the version number in the footer.
     */
    private function updateFooter( $content ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates
            || (
                'selective' === $disable_updates
                && Options::get( 'updates_core_updates' ) === 'disable_core_updates'
            )
        ) {
            return '';
        }

        return $content;
    }

    private function autoUpdatePlugin( $update ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        $plugin_updates = Options::get( 'updates_plugin_updates' );
        if ( 'selective' === $disable_updates && ( 'manual' === $plugin_updates || 'disable' === $plugin_updates ) ) {
            return false;
        }

        if ( 'selective' === $disable_updates && 'auto' === $plugin_updates ) {
            return true;
        }

        return $update;
    }

    private function pluginsAutoUpdateEnabled( $enabled ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        $plugin_updates = Options::get( 'updates_plugin_updates' );
        if ( 'selective' === $disable_updates && ( 'manual' === $plugin_updates || 'disable' === $plugin_updates ) ) {
            return false;
        }

        if ( 'selective' === $disable_updates && 'auto' === $plugin_updates ) {
            return true;
        }

        return $enabled;
    }

    private function autoUpdateTheme( $update ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        $theme_updates = Options::get( 'updates_theme_updates' );
        if ( 'selective' === $disable_updates && ( 'manual' === $theme_updates || 'disable' === $theme_updates ) ) {
            return false;
        }

        if ( 'selective' === $disable_updates && 'auto' === $theme_updates ) {
            return true;
        }

        return $update;
    }

    private function themesAutoUpdateEnabled( $enabled ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        $theme_updates = Options::get( 'updates_theme_updates' );
        if ( 'selective' === $disable_updates && ( 'manual' === $theme_updates || 'disable' === $theme_updates ) ) {
            return false;
        }

        if ( 'selective' === $disable_updates && 'auto' === $theme_updates ) {
            return true;
        }

        return $enabled;
    }

    private function autoUpdateTranslation( $update ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        $translationUpdateOption = Options::get( 'updates_translation_updates' );
        if ( 'selective' === $disable_updates && 'disable' === $translationUpdateOption ) {
            return false;
        }

        if ( 'selective' === $disable_updates && 'auto' === $translationUpdateOption ) {
            return true;
        }

        return $update;
    }

    private function asyncUpdateTranslation( $update ) {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( 'all' === $disable_updates ) {
            return false;
        }

        $translationUpdateOption = Options::get( 'updates_translation_updates' );
        if ( 'selective' === $disable_updates && 'disable' === $translationUpdateOption ) {
            return false;
        }

        if ( 'selective' === $disable_updates && 'auto' === $translationUpdateOption ) {
            return true;
        }

        return $update;
    }

    private function updateNotice() {
        if ( $this->shouldShowUpdateNotice() ) {
            return;
        }

        $this->disableUpdateNotices();
    }

    private function shouldShowUpdateNotice(): bool {
        $disable_updates     = Options::get( 'updates_disable_updates' );
        $nags_only_for_admin = Options::get( 'updates_updates_nags_only_for_admin' );
        $core_updates        = Options::get( 'updates_core_updates' );
        $can_update_core     = current_user_can( 'update_core' );

        return ! ( 'all' === $disable_updates || ( 'selective' === $disable_updates && ( ( $nags_only_for_admin && ! $can_update_core ) || 'disable_core_updates' === $core_updates ) ) );
    }

    private function disableUpdateNotices() {
        remove_action( 'admin_notices', 'update_nag', 3 );
        remove_action( 'network_admin_notices', 'update_nag', 3 );
        remove_action( 'admin_notices', 'maintenance_nag' );
        remove_action( 'network_admin_notices', 'maintenance_nag' );
    }

    /**
     * Always send back that the latest version of WordPress is the one we're running.
     */
    private function lastCheckedCore( $update ) {
        // Call the global WP version.
        global $wp_version;

        $disable_updates = Options::get( 'updates_disable_updates' );
        $core_updates    = Options::get( 'updates_core_updates' );

        if ( 'all' === $disable_updates
            || (
                'selective' === $disable_updates
                && 'disable_core_updates' === $core_updates
            )
        ) {
            return (object) [
                'last_checked'    => time(),
                'updates'         => [],
                'version_checked' => $wp_version,
            ];
        }

        return $update;
    }

    private function handleUpdateEmails() {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( ! (
            'all' === $disable_updates
            || (
                'selective' === $disable_updates
                && Options::get( 'updates_core_updates' ) === 'disable_core_updates'
            )
        )
        ) {
            return;
        }

        // Disable update emails (for when we push the new WordPress versions manually) as well
        // as the notification there is a new version emails.
        add_filter( 'automatic_updates_send_debug_email', '__return_false' );
        add_filter( 'auto_core_update_send_email', '__return_false' );
        add_filter( 'send_core_update_notification_email', '__return_false' );
        add_filter( 'automatic_updates_send_debug_email', '__return_false', 1 );
    }

    private function handleConstants() {
        $disable_updates = Options::get( 'updates_disable_updates' );

        if ( ! (
            'all' === $disable_updates
            || (
                'selective' === $disable_updates
                && Options::get( 'updates_core_updates' ) === 'disable_core_updates'
            )
        )
        ) {
            return;
        }

        if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) ) {
            define( 'WP_AUTO_UPDATE_CORE', false );
        }
    }

}
