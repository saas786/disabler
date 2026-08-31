<?php

namespace HBP\Disabler\Optimize;

use Hybrid\Contracts\Bootable;
use Hybrid\Tools\Arr;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use function HBP\Disabler\maybe_define_constant;

/**
 * Everything this plugin does to WordPress' update machinery.
 *
 * The decisions all live in UpdatePolicy, resolved once at `init`. What is
 * left here is wiring: which hook, which verdict, in that order. No callback
 * re-reads a setting, so none of them can disagree with another about what
 * the user asked for.
 */
class Updates implements Bootable {

    use AccessiblePrivateMethods;

    /**
     * Resolved at `init`, because reading settings any earlier means reading
     * them before the option store and its presets are available.
     */
    private UpdatePolicy $policy;

    public function boot(): void {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
    }

    private function initHooks(): void {
        $this->policy = UpdatePolicy::fromSettings();

        if ( ! $this->policy->enabled ) {
            return;
        }

        self::add_filter( 'wp_get_update_data', [ $this, 'updateCounter' ], 10, 2 );
        self::add_action( 'admin_init', [ $this, 'updateNotice' ] );
        self::add_filter( 'site_status_tests', [ $this, 'siteStatusTests' ] );

        /*
         * Filter / disable schedule checks.
         *
         * @link https://wordpress.org/support/topic/possible-performance-improvement/#post-8970451
         */
        self::add_action( 'admin_init', [ $this, 'disableScheduleHook' ] );
        self::add_action( 'schedule_event', [ $this, 'filterCronEvents' ] );

        self::add_filter( 'bulk_actions-plugins', [ $this, 'bulkActionsPlugins' ] );
        self::add_filter( 'bulk_actions-plugins-network', [ $this, 'bulkActionsPlugins' ] );
        self::add_filter( 'bulk_actions-themes', [ $this, 'bulkActionsThemes' ] );
        self::add_filter( 'bulk_actions-themes-network', [ $this, 'bulkActionsThemes' ] );

        self::add_filter( 'pre_site_transient_update_core', [ $this, 'lastCheckedCore' ] );
        self::add_filter( 'automatic_updates_is_vcs_checkout', [ $this, 'isVCSCheckout' ] );

        // Core auto-updates, one filter per release level.
        self::add_filter( 'allow_minor_auto_core_updates', [ $this, 'allowMinorCore' ] );
        self::add_filter( 'allow_major_auto_core_updates', [ $this, 'allowMajorCore' ] );
        self::add_filter( 'allow_dev_auto_core_updates', [ $this, 'allowDevCore' ] );
        self::add_filter( 'auto_update_core', [ $this, 'autoUpdateCore' ] );

        // Get rid of the version number in the footer.
        self::add_filter( 'update_footer', [ $this, 'updateFooter' ], 11 );

        // Auto-update decisions, and the list-table toggles that mirror them.
        self::add_filter( 'auto_update_plugin', [ $this, 'autoUpdatePlugin' ] );
        self::add_filter( 'plugins_auto_update_enabled', [ $this, 'autoUpdatePlugin' ] );
        self::add_filter( 'auto_update_theme', [ $this, 'autoUpdateTheme' ], 1 );
        self::add_filter( 'themes_auto_update_enabled', [ $this, 'autoUpdateTheme' ] );
        self::add_filter( 'auto_update_translation', [ $this, 'autoUpdateTranslation' ] );
        self::add_filter( 'async_update_translation', [ $this, 'autoUpdateTranslation' ] );

        $this->handleGeneralUpdates();
        $this->handlePluginUpdates();
        $this->handleThemeUpdates();
        $this->handleTranslationUpdates();
        $this->handleUpdateEmails();
        $this->handleConstants();
    }

    // ---------------------------------------------------------------------
    // Wholesale
    // ---------------------------------------------------------------------

    private function handleGeneralUpdates(): void {
        if ( ! $this->policy->everything ) {
            return;
        }

        self::add_action( 'admin_menu', [ $this, 'adminMenuItems' ], 9999 );
        self::add_action( 'network_admin_menu', [ $this, 'msAdminMenuItems' ], 9999 );

        add_filter( 'automatic_updater_disabled', '__return_true' );

        remove_action( 'init', 'wp_schedule_update_checks' );

        maybe_define_constant( 'AUTOMATIC_UPDATER_DISABLED', true );

        add_action( 'admin_init', static function (): void {
            // The cron hook that runs core, plugin and theme auto-updates.
            remove_action( 'wp_maybe_auto_update', 'wp_maybe_auto_update' );
        } );
    }

    /**
     * Remove the updates menu item on a single site.
     */
    private function adminMenuItems(): void {
        if ( is_multisite() ) {
            return;
        }

        remove_submenu_page( 'index.php', 'update-core.php' );
    }

    /**
     * Remove the updates menu item in network admin.
     */
    private function msAdminMenuItems(): void {
        if ( ! is_network_admin() ) {
            return;
        }

        remove_submenu_page( 'index.php', 'upgrade.php' );
    }

    // ---------------------------------------------------------------------
    // Counts and Site Health
    // ---------------------------------------------------------------------

    /**
     * Drop the counts for whatever is switched off, and the total with them.
     *
     * @param array $update_data Counts and title, as core assembled them.
     * @param array $titles Per-kind title fragments.
     */
    private function updateCounter( $update_data, $titles ) {
        if ( $this->policy->everything ) {
            // A dedicated string rather than an empty title, because the admin
            // bar prints this as the tooltip on the updates item and an empty
            // one reads as a rendering fault rather than as good news.
            return [
                'counts' => array_fill_keys(
                    [ 'plugins', 'themes', 'wordpress', 'translations', 'total' ],
                    0
                ),
                'title'  => esc_attr__( 'No updates available', 'hbp-disabler' ),
            ];
        }

        $stripped = false;

        foreach ( $this->countable() as $count => $verdict ) {
            if ( ! $verdict->isOff() || empty( $update_data['counts'][ $count ] ) ) {
                continue;
            }

            $update_data['counts']['total'] -= $update_data['counts'][ $count ];
            $update_data['counts'][ $count ] = 0;

            unset( $titles[ $count ] );

            $stripped = true;
        }

        // Only when something was removed. Rebuilding unconditionally would
        // overwrite a title another plugin had already filtered, on every
        // request, including ones where this plugin changed nothing.
        if ( $stripped ) {
            $update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';
        }

        return $update_data;
    }

    /**
     * Hide the update checks in Site Health that no longer apply.
     *
     * @param array $tests Registered tests, grouped by direct and async.
     */
    private function siteStatusTests( $tests ) {
        // Arr::forget takes dotted keys and is a no-op on ones that are not
        // there, so the isset() guard each of these used to carry is dead
        // weight.
        if ( $this->policy->core->isOff() ) {
            Arr::forget( $tests, [ 'async.background_updates', 'direct.wordpress_version' ] );
        }

        if ( $this->policy->plugins->isOff() ) {
            Arr::forget( $tests, 'direct.plugin_version' );
        }

        if ( $this->policy->themes->isOff() ) {
            Arr::forget( $tests, 'direct.theme_version' );
        }

        if ( $this->policy->plugins->isOff() || $this->policy->themes->isOff() ) {
            Arr::forget( $tests, 'direct.plugin_theme_auto_updates' );
        }

        return $tests;
    }

    // ---------------------------------------------------------------------
    // Cron
    // ---------------------------------------------------------------------

    /**
     * Clear the scheduled check for anything switched off.
     */
    private function disableScheduleHook(): void {
        foreach ( $this->scheduledHooks() as $hook => $verdict ) {
            if ( $verdict->isOff() ) {
                wp_clear_scheduled_hook( $hook );
            }
        }

        if ( $this->policy->everything ) {
            wp_clear_scheduled_hook( 'wp_maybe_auto_update' );
        }
    }

    /**
     * Refuse to schedule a check that has just been cleared.
     *
     * Clearing alone is not enough: core reschedules on the next request, so
     * without this the hook comes straight back.
     *
     * @see https://wordpress.org/support/topic/possible-performance-improvement/#post-8970451
     */
    private function filterCronEvents( $event ) {
        if ( ! is_object( $event ) || empty( $event->hook ) ) {
            return $event;
        }

        if ( $this->policy->everything && 'wp_maybe_auto_update' === $event->hook ) {
            return false;
        }

        $verdict = $this->scheduledHooks()[ $event->hook ] ?? null;

        return $verdict?->isOff() ? false : $event;
    }

    // ---------------------------------------------------------------------
    // Bulk actions
    // ---------------------------------------------------------------------

    private function bulkActionsPlugins( $actions ) {
        return $this->stripActions( $actions, $this->policy->plugins );
    }

    private function bulkActionsThemes( $actions ) {
        return $this->stripActions( $actions, $this->policy->themes );
    }

    /**
     * @param array $actions Bulk actions offered by the list table.
     */
    private function stripActions( $actions, Verdict $verdict ): array {
        return array_diff_key(
            $actions,
            array_flip( $this->policy->strippedBulkActions( $verdict ) )
        );
    }

    // ---------------------------------------------------------------------
    // Transients: report everything as current
    // ---------------------------------------------------------------------

    private function handlePluginUpdates(): void {
        if ( ! $this->policy->plugins->isOff() ) {
            return;
        }

        add_action( 'admin_init', static function (): void {
            remove_action( 'load-plugins.php', 'wp_plugin_update_rows', 20 );
        } );

        self::add_filter( 'pre_site_transient_update_plugins', [ $this, 'lastCheckedPlugins' ] );
        add_filter( 'site_transient_update_plugins', '__return_empty_array' );

        remove_action( 'load-update-core.php', 'wp_update_plugins' );
        remove_action( 'load-plugins.php', 'wp_update_plugins' );
        remove_action( 'load-update.php', 'wp_update_plugins' );
        remove_action( 'wp_update_plugins', 'wp_update_plugins' );
        remove_action( 'admin_init', '_maybe_update_plugins' );
    }

    private function handleThemeUpdates(): void {
        if ( ! $this->policy->themes->isOff() ) {
            return;
        }

        add_action( 'admin_init', static function (): void {
            remove_action( 'load-themes.php', 'wp_theme_update_rows', 20 );
        } );

        self::add_filter( 'pre_site_transient_update_themes', [ $this, 'lastCheckedThemes' ] );
        add_filter( 'site_transient_update_themes', '__return_empty_array' );

        remove_action( 'load-themes.php', 'wp_update_themes' );
        remove_action( 'load-update.php', 'wp_update_themes' );
        remove_action( 'load-update-core.php', 'wp_update_themes' );
        remove_action( 'admin_init', '_maybe_update_themes' );
        remove_action( 'wp_update_themes', 'wp_update_themes' );
    }

    private function handleTranslationUpdates(): void {
        if ( ! $this->policy->translations->isOff() ) {
            return;
        }

        foreach ( [ 'core', 'plugins', 'themes' ] as $kind ) {
            self::add_filter( "site_transient_update_{$kind}", [ $this, 'emptyTranslations' ] );
        }
    }

    private function emptyTranslations( $transient ) {
        if ( is_object( $transient ) && isset( $transient->translations ) ) {
            $transient->translations = [];
        }

        return $transient;
    }

    /**
     * Report every installed plugin as already current.
     */
    private function lastCheckedPlugins() {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return $this->checkedTransient( wp_list_pluck( get_plugins(), 'Version' ) );
    }

    /**
     * Report every installed theme as already current.
     */
    private function lastCheckedThemes() {
        $versions = [];

        foreach ( wp_get_themes() as $theme ) {
            $versions[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
        }

        return $this->checkedTransient( $versions );
    }

    /**
     * Report core as already current.
     */
    private function lastCheckedCore( $update ) {
        return $this->policy->core->isOff()
            ? $this->checkedTransient()
            : $update;
    }

    /**
     * An update transient saying a check just happened and found nothing.
     *
     * @param array|null $checked Versions core would have compared against.
     */
    private function checkedTransient( ?array $checked = null ): object {
        global $wp_version;

        $transient = (object) [
            'last_checked'    => time(),
            'updates'         => [],
            'version_checked' => $wp_version,
        ];

        // Core omits `checked` on the core transient and requires it on the
        // plugin and theme ones, so this is not merely cosmetic.
        if ( null !== $checked ) {
            $transient->checked = $checked;
        }

        return $transient;
    }

    // ---------------------------------------------------------------------
    // Auto-update filters
    // ---------------------------------------------------------------------

    private function autoUpdatePlugin( $update ) {
        return $this->policy->plugins->autoUpdate( (bool) $update );
    }

    private function autoUpdateTheme( $update ) {
        return $this->policy->themes->autoUpdate( (bool) $update );
    }

    private function autoUpdateTranslation( $update ) {
        return $this->policy->translations->autoUpdate( (bool) $update );
    }

    private function allowMinorCore( $allowed ) {
        return $this->policy->allowsCoreAuto( 'minor', (bool) $allowed );
    }

    private function allowMajorCore( $allowed ) {
        return $this->policy->allowsCoreAuto( 'major', (bool) $allowed );
    }

    private function allowDevCore( $allowed ) {
        return $this->policy->allowsCoreAuto( 'dev', (bool) $allowed );
    }

    /**
     * A veto only.
     *
     * The three release-level filters above decide whether core updates
     * itself. This one can refuse, but must never force -- returning true
     * would overrule a level filter that had already said no.
     */
    private function autoUpdateCore( $update ) {
        return $this->policy->core->blocksAuto() ? false : $update;
    }

    /**
     * Tell WordPress this is a version-controlled checkout.
     */
    private function isVCSCheckout( $checkout ) {
        return $this->policy->vcs ?? $checkout;
    }

    // ---------------------------------------------------------------------
    // Notices, footer, email, constants
    // ---------------------------------------------------------------------

    private function updateFooter( $content ) {
        return $this->policy->core->isOff() ? '' : $content;
    }

    private function updateNotice(): void {
        if ( ! $this->hidesUpdateNotice() ) {
            return;
        }

        remove_action( 'admin_notices', 'update_nag', 3 );
        remove_action( 'network_admin_notices', 'update_nag', 3 );
        remove_action( 'admin_notices', 'maintenance_nag' );
        remove_action( 'network_admin_notices', 'maintenance_nag' );
    }

    /**
     * Whether the core update nag should be suppressed for this user.
     *
     * The nags-only-for-admin case is per-user rather than per-site, which is
     * why it is asked here and not resolved into the policy.
     */
    private function hidesUpdateNotice(): bool {
        if ( $this->policy->core->isOff() ) {
            return true;
        }

        return $this->policy->nagsOnlyForAdmin && ! current_user_can( 'update_core' );
    }

    private function handleUpdateEmails(): void {
        if ( ! $this->policy->core->isOff() ) {
            return;
        }

        // Both the "a new version is available" mail and the debug mail sent
        // after an attempted update.
        add_filter( 'automatic_updates_send_debug_email', '__return_false' );
        add_filter( 'auto_core_update_send_email', '__return_false' );
        add_filter( 'send_core_update_notification_email', '__return_false' );
    }

    private function handleConstants(): void {
        if ( ! $this->policy->core->isOff() ) {
            return;
        }

        maybe_define_constant( 'WP_AUTO_UPDATE_CORE', false );
    }

    // ---------------------------------------------------------------------
    // Maps
    // ---------------------------------------------------------------------

    /**
     * The `wp_get_update_data` count keys, and the verdict governing each.
     *
     * @return array<string, Verdict>
     */
    private function countable(): array {
        return [
            'wordpress'    => $this->policy->core,
            'plugins'      => $this->policy->plugins,
            'themes'       => $this->policy->themes,
            'translations' => $this->policy->translations,
        ];
    }

    /**
     * The cron hooks that check for updates, and the verdict governing each.
     *
     * `wp_maybe_auto_update` is deliberately absent: it performs updates for
     * all three kinds at once, so no single verdict governs it. It is handled
     * separately, and only when everything is off.
     *
     * @return array<string, Verdict>
     */
    private function scheduledHooks(): array {
        return [
            'wp_version_check'  => $this->policy->core,
            'wp_update_plugins' => $this->policy->plugins,
            'wp_update_themes'  => $this->policy->themes,
        ];
    }
}
