<?php

namespace HBP\Disabler\Admin;

use HBP\Disabler\Facades\Assets;
use HBP\Disabler\Tools\Update\PluginInstall;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use Hybrid\View;
use function Hybrid\Action\Scheduler\queue;

/**
 * Notices Class.
 */
class Notices {

    use AccessiblePrivateMethods;

    /**
     * Stores notices.
     *
     * @var array
     */
    private static $notices = [];

    /**
     * Array of notices - name => callback.
     *
     * @var array
     */
    private static $core_notices = [
        'update' => 'update_notice',
    ];

    /**
     * Boot.
     */
    public function boot(): void {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
    }

    private function initHooks() {
        self::$notices = get_option( 'hbp_disabler_admin_notices', [] );

        add_action( 'admin_init', [ __CLASS__, 'hide_notices' ], 20 );
        add_action( 'shutdown', [ __CLASS__, 'store_notices' ] );

        // @TODO: This prevents Action Scheduler async jobs from storing empty list of notices during plugin installation.
        if ( ! PluginInstall::is_new_install() ) {
            add_action( 'shutdown', [ __CLASS__, 'store_notices' ] );
        }

        if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
            add_action( 'admin_print_styles', [ __CLASS__, 'add_notices' ] );
        }
    }

    /**
     * Store notices to DB.
     */
    public static function store_notices(): void {
        update_option( 'hbp_disabler_admin_notices', self::get_notices() );
    }

    /**
     * Get notices.
     *
     * @return array
     */
    public static function get_notices() {
        return self::$notices;
    }

    /**
     * Remove all notices.
     */
    public static function remove_all_notices(): void {
        self::$notices = [];
    }

    /**
     * Show a notice.
     *
     * @param string $name       Notice name.
     * @param bool   $force_save Force saving inside this method instead of at the 'shutdown'.
     */
    public static function add_notice( $name, $force_save = false ) {
        self::$notices = array_unique( array_merge( self::get_notices(), [ $name ] ) );

        if ( $force_save ) {
            // Adding early save to prevent more race conditions with notices.
            self::store_notices();
        }
    }

    /**
     * Remove a notice from being displayed.
     *
     * @param string $name       Notice name.
     * @param bool   $force_save Force saving inside this method instead of at the 'shutdown'.
     */
    public static function remove_notice( $name, $force_save = false ) {
        self::$notices = array_diff( self::get_notices(), [ $name ] );
        delete_option( 'hbp_disabler_admin_notice_' . $name );

        if ( $force_save ) {
            // Adding early save to prevent more race conditions with notices.
            self::store_notices();
        }
    }

    /**
     * Remove a given set of notices.
     *
     * An array of notice names or a regular expression string can be passed, in the later case
     * all the notices whose name matches the regular expression will be removed.
     *
     * @param  array|string $names_array_or_regex An array of notice names, or a string representing a regular expression.
     * @param  bool         $force_save           Force saving inside this method instead of at the 'shutdown'.
     * @return void
     */
    public static function remove_notices( $names_array_or_regex, $force_save = false ) {
        if ( ! is_array( $names_array_or_regex ) ) {
            $names_array_or_regex = array_filter( self::get_notices(), static fn( $notice_name ) => 1 === preg_match( $names_array_or_regex, $notice_name ) );
        }

        self::set_notices( array_diff( self::get_notices(), $names_array_or_regex ) );

        if ( $force_save ) {
            // Adding early save to prevent more race conditions with notices.
            self::store_notices();
        }
    }

    /**
     * See if a notice is being shown.
     *
     * @param  string $name Notice name.
     * @return bool
     */
    public static function has_notice( $name ) {
        return in_array( $name, self::get_notices(), true );
    }

    /**
     * Hide a notice if the GET variable is set.
     */
    public static function hide_notices() {
        if ( isset( $_GET['hbp-disabler-hide-notice'] )
            && isset( $_GET['_hbp_disabler_notice_nonce'] )
        ) { // WPCS: input var ok, CSRF ok.
            if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_hbp_disabler_notice_nonce'] ) ), 'hbp_disabler_hide_notices_nonce' ) ) { // WPCS: input var ok, CSRF ok.
                wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'hbp-disabler' ) );
            }

            $notice_name = sanitize_text_field( wp_unslash( $_GET['hbp-disabler-hide-notice'] ) ); // WPCS: input var ok, CSRF ok.

            if ( ! ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
                wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'hbp-disabler' ) );
            }

            self::hide_notice( $notice_name );
        }
    }

    /**
     * Hide a single notice.
     *
     * @param string $name Notice name.
     */
    private static function hide_notice( $name ) {
        self::remove_notice( $name );

        update_user_meta( get_current_user_id(), 'dismissed_' . $name . '_notice', true );

        do_action( 'hbp_disabler_hide_' . $name . '_notice' );
    }

    /**
     * Add notices + styles if needed.
     */
    public static function add_notices() {
        $notices = self::get_notices();

        if ( empty( $notices ) ) {
            return;
        }

        $screen          = get_current_screen();
        $screen_id       = $screen ? $screen->id : '';
        $show_on_screens = [
            'dashboard',
            'plugins',
        ];

        // Notices should only show on the main dashboard, and on the plugin's screen.
        if ( ! in_array( $screen_id, $show_on_screens, true ) ) {
            return;
        }

        wp_enqueue_style(
            'hbp-disabler-admin-notices',
            Assets::assetUrl( 'css/admin/notices.css' ),
            [],
            null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        );

        // Add RTL support.
        wp_style_add_data( 'hbp-disabler-admin-notices', 'rtl', 'replace' );

        foreach ( $notices as $notice ) {
            if ( ! empty( self::$core_notices[ $notice ] )
                && apply_filters( 'hbp/disabler/show_admin_notice', true, $notice )
            ) {
                add_action( 'admin_notices', [ __CLASS__, self::$core_notices[ $notice ] ] );
            } else {
                add_action( 'admin_notices', [ __CLASS__, 'output_custom_notices' ] );
            }
        }
    }

    /**
     * Add a custom notice.
     *
     * @param string $name        Notice name.
     * @param string $notice_html Notice HTML.
     */
    public static function add_custom_notice( $name, $notice_html ): void {
        self::add_notice( $name );

        update_option( 'hbp_disabler_admin_notice_' . $name, wp_kses_post( $notice_html ) );
    }

    /**
     * Output any stored custom notices.
     */
    public static function output_custom_notices(): void {
        $notices = self::get_notices();

        if ( ! empty( $notices ) ) {
            foreach ( $notices as $notice ) {
                if ( empty( self::$core_notices[ $notice ] ) ) {
                    $notice_html = get_option( 'hbp_disabler_admin_notice_' . $notice );

                    if ( $notice_html ) {
                        View\display( 'admin/html-notice-custom', [
                            'notice'      => $notice,
                            'notice_html' => $notice_html,
                        ] );
                    }
                }
            }
        }
    }

    /**
     * If we need to update, include a message with the update button.
     */
    public static function update_notice(): void {
        if ( PluginInstall::needs_db_update() ) {
            $next_scheduled_date = queue()->get_next( 'hbp_disabler_run_update_callback', null, 'hbp-disabler-db-updates' );

            if ( $next_scheduled_date || ! empty( $_GET['do_update_hbp_disabler'] ) ) { // WPCS: input var ok, CSRF ok.
                View\display( 'admin/html-notice-updating' );
            } else {
                View\display( 'admin/html-notice-update' );
            }
        } else {
            View\display( 'admin/html-notice-updated' );
        }
    }

}
