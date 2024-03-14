<?php
/**
 * Installation related functions and actions.
 */

namespace HBP\Disabler\Tools\Update;

use HBP\Disabler\Admin\Notices;
use HBP\Disabler\Plugin;
use Hybrid\Log\Facades\Log;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use function HBP\Disabler\maybe_define_constant;
use function Hybrid\Action\Scheduler\queue;

/**
 * PluginInstall Class.
 */
class PluginInstall {

    use AccessiblePrivateMethods;

    /**
     * DB updates and callbacks that need to be run per version.
     *
     * Please note that these functions are invoked when Plugin is updated from a previous version,
     * but NOT when Plugin is newly installed.
     *
     * @var array
     */
    private static $db_updates = [
        '3.0.0'         => [
            __NAMESPACE__ . '\update_3_0_0_options',
            __NAMESPACE__ . '\update_3_0_0_db_version',
        ],
        '3.0.3'         => [
            __NAMESPACE__ . '\update_3_0_3_options',
            __NAMESPACE__ . '\update_3_0_3_db_version',
        ],
        '3.1.0-alpha.1' => [
            __NAMESPACE__ . '\update_3_1_0_alpha_1_options',
            __NAMESPACE__ . '\update_3_1_0_alpha_1_db_version',
        ],
    ];

    /**
     * Option name used to track new installations of plugin.
     */
    const NEWLY_INSTALLED_OPTION = 'hbp_disabler_newly_installed';

    /**
     * Boot.
     */
    public function boot() {
        add_action( 'plugins_loaded', [ __CLASS__, 'init_plugin_version' ], -1 );
        add_action( 'init', [ __CLASS__, 'check_version' ], 5 );
        add_action( 'hbp_disabler_run_update_callback', [ __CLASS__, 'run_update_callback' ] );
        add_action( 'hbp_disabler_update_db_to_current_version', [ __CLASS__, 'update_db_version' ] );
        add_action( 'admin_init', [ __CLASS__, 'install_actions' ] );
        add_filter( 'cron_schedules', [ __CLASS__, 'cron_schedules' ] );
        self::add_action( 'admin_init', [ __CLASS__, 'newly_installed' ] );
    }

    /**
     * Set initial Plugin version,
     * so we can do update if its installed on old version.
     */
    public static function init_plugin_version() {
        if ( self::is_new_install() && is_null( get_option( 'hbp_disabler_db_version', null ) ) ) {
            self::update_db_version( '2.3.1' );
        }
    }

    /**
     * Trigger `hbp_disabler_newly_installed` action for new installations.
     */
    private static function newly_installed() {
        if ( 'yes' === get_option( self::NEWLY_INSTALLED_OPTION, false ) ) {
            /**
             * Run when Plugin has been installed for the first time.
             */
            do_action( 'hbp_disabler_newly_installed' );

            update_option( self::NEWLY_INSTALLED_OPTION, 'no' );
        }
    }

    /**
     * Check Plugin version and run the updater is required.
     *
     * This check is done on all requests and runs if the versions do not match.
     */
    public static function check_version() {
        $version         = get_option( 'hbp_disabler_db_version' );
        $code_version    = Plugin::VERSION;
        $requires_update = version_compare( $version, $code_version, '<' );

        if ( ! defined( 'IFRAME_REQUEST' ) && $requires_update ) {
            self::install();

            /**
             * Run after Plugin has been updated.
             */
            do_action( 'hbp_disabler_updated' );
        }
    }

    /**
     * Run manual database update.
     */
    public static function run_manual_database_update() {
        self::update();
    }

    /**
     * Run an update callback when triggered by ActionScheduler.
     *
     * @param string $update_callback Callback name.
     */
    public static function run_update_callback( $update_callback ) {
        include_once dirname( DISABLER_FILE ) . '/inc/Tools/Update/bootstrap-autoload.php';

        if ( is_callable( $update_callback ) ) {
            self::run_update_callback_start( $update_callback );
            $result = (bool) call_user_func( $update_callback );
            self::run_update_callback_end( $update_callback, $result );
        }
    }

    /**
     * Triggered when a callback will run.
     *
     * @param string $callback Callback name.
     */
    protected static function run_update_callback_start( $callback ) {
        maybe_define_constant( 'HBP_DISABLER_UPDATING', true );
    }

    /**
     * Triggered when a callback has ran.
     *
     * @param string $callback Callback name.
     * @param bool   $result   Return value from callback. Non-false need to run again.
     */
    protected static function run_update_callback_end( $callback, $result ) {
        if ( $result ) {
            queue()->add(
                'hbp_disabler_run_update_callback',
                [
                    'update_callback' => $callback,
                ],
                'hbp-disabler-db-updates'
            );
        }
    }

    /**
     * Update DB version to current.
     *
     * @param string|null $version New Plugin DB version or null.
     */
    public static function update_db_version( $version = null ) {
        update_option( 'hbp_disabler_db_version', is_null( $version ) ? Plugin::VERSION : $version );
    }

    /**
     * Install actions when a update button is clicked within the admin area.
     *
     * This function is hooked into admin_init to affect admin only.
     */
    public static function install_actions() {
        if ( ! empty( $_GET['do_update_hbp_disabler'] ) ) { // WPCS: input var ok.
            check_admin_referer( 'hbp_disabler_db_update', 'hbp_disabler_db_update_nonce' );
            self::update();
            Notices::add_notice( 'update', true );
        }
    }

    /**
     * Install Plugin.
     */
    public static function install() {
        if ( ! is_blog_installed() ) {
            return;
        }

        // Check if we are not already running this routine.
        if ( self::is_installing() ) {
            return;
        }

        // If we made it till here nothing is running yet, lets set the transient now.
        set_transient( 'hbp_disabler_installing', 'yes', MINUTE_IN_SECONDS * 10 );
        maybe_define_constant( 'HBP_DISABLER_INSTALLING', true );

        if ( self::is_new_install() && ! get_option( self::NEWLY_INSTALLED_OPTION, false ) ) {
            update_option( self::NEWLY_INSTALLED_OPTION, 'yes' );
        }

        self::remove_admin_notices();
        self::create_cron_jobs();
        self::maybe_set_activation_transients();
        self::update_hbp_disabler_version();
        self::maybe_update_db_version();

        delete_transient( 'hbp_disabler_installing' );

        // Use add_option() here to avoid overwriting this value with each
        // plugin version update. We base plugin age off of this value.
        add_option( 'hbp_disabler_admin_install_timestamp', time() );

        /**
         * Run after Plugin has been installed or updated.
         */
        do_action( 'hbp_disabler_installed' );

        /**
         * Run after Plugin Admin has been installed or updated.
         */
        do_action( 'hbp_disabler_admin_installed' );
    }

    /**
     * Returns true if we're installing.
     *
     * @return bool
     */
    private static function is_installing() {
        return 'yes' === get_transient( 'hbp_disabler_installing' );
    }

    /**
     * Reset any notices added to admin.
     */
    private static function remove_admin_notices() {
        Notices::remove_all_notices();
    }

    /**
     * Is this a brand new Plugin install?
     *
     * A brand new install has no version yet. Also treat empty installs as 'new'.
     *
     * @return bool
     */
    public static function is_new_install() {
        // Since v1.
        $v2_settings = get_option( 'disabler_autop', null );

        // Since v3.
        $v3_settings = get_option( 'disabler_options', null );

        // Since v3.0.3.
        $v3_0_3_settings = get_option( 'disabler_settings', null );

        // $settings = get_option( 'disabler_settings', null );
        // $old_version = get_option( 'disabler_version', null );
        // $old_db_version = get_option( 'disabler_db_version', null );

        // Since v3.1.
        $settings = get_option( 'hbp_disabler_settings', null );
        $version  = get_option( 'hbp_disabler_version', null );

        return is_null( $version )
            || is_null( $settings )
            || is_null( $v3_0_3_settings )
            || is_null( $v3_settings )
            || is_null( $v2_settings );
    }

    /**
     * Is a DB update needed?
     *
     * @return bool
     */
    public static function needs_db_update() {
        $current_db_version = get_option( 'hbp_disabler_db_version', null );
        $updates            = self::get_db_update_callbacks();
        $update_versions    = array_keys( $updates );
        usort( $update_versions, 'version_compare' );

        return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
    }

    /**
     * See if we need to set redirect transients for activation or not.
     */
    private static function maybe_set_activation_transients() {
        if ( self::is_new_install() ) {
            set_transient( '_hbp_disabler_activation_redirect', 1, 30 );
        }
    }

    /**
     * See if we need to show or run database updates during install.
     */
    private static function maybe_update_db_version() {
        if ( self::needs_db_update() ) {
            /**
             * Allow Plugin to auto-update without prompting the user.
             */
            if ( apply_filters( 'hbp_disabler_enable_auto_update_db', false ) ) {
                self::update();
            } else {
                Notices::add_notice( 'update', true );
            }
        } else {
            self::update_db_version();
        }
    }

    /**
     * Update Plugin version to current.
     */
    private static function update_hbp_disabler_version() {
        update_option( 'hbp_disabler_version', Plugin::VERSION );
    }

    /**
     * Get list of DB update callbacks.
     *
     * @return array
     */
    public static function get_db_update_callbacks() {
        return self::$db_updates;
    }

    /**
     * Push all needed DB updates to the queue for processing.
     */
    private static function update() {
        $current_db_version = get_option( 'hbp_disabler_db_version' );
        $loop               = 0;

        foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
            if ( version_compare( $current_db_version, $version, '<' ) ) {
                foreach ( $update_callbacks as $update_callback ) {
                    Log::info(
                        sprintf( 'Queuing %s - %s', $version, $update_callback ),
                        [ 'source' => 'hbp_disabler_db_updates' ]
                    );

                    queue()->schedule_single(
                        time() + $loop,
                        'hbp_disabler_run_update_callback',
                        [
                            'update_callback' => $update_callback,
                        ],
                        'hbp-disabler-db-updates'
                    );

                    ++$loop;
                }
            }
        }

        // After the callbacks finish, update the db version to the current WC version.
        $current_version = Plugin::VERSION;
        if ( version_compare( $current_db_version, $current_version, '<' ) &&
            ! queue()->get_next( 'hbp_disabler_update_db_to_current_version' ) ) {
            queue()->schedule_single(
                time() + $loop,
                'hbp_disabler_update_db_to_current_version',
                [
                    'version' => $current_version,
                ],
                'hbp-disabler-db-updates'
            );
        }
    }

    /**
     * Add more cron schedules.
     *
     * @param  array $schedules List of WP scheduled cron jobs.
     * @return array
     */
    public static function cron_schedules( $schedules ) {
        $schedules['monthly'] = [
            'display'  => __( 'Monthly', 'hbp-disabler' ),
            'interval' => 2635200,
        ];

        $schedules['fifteendays'] = [
            'display'  => __( 'Every 15 Days', 'hbp-disabler' ),
            'interval' => 1296000,
        ];

        return $schedules;
    }

    /**
     * Create cron jobs (clear them first).
     */
    private static function create_cron_jobs() {
        wp_clear_scheduled_hook( 'hbp_disabler_cleanup_logs' );

        wp_schedule_event( time() + ( 3 * HOUR_IN_SECONDS ), 'daily', 'hbp_disabler_cleanup_logs' );
    }

}
