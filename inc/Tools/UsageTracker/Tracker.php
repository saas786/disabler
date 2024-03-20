<?php

namespace HBP\Disabler\Tools\UsageTracker;

use HBP\Disabler\Admin\Options;
use HBP\Disabler\Tools\UsageTracker\Trackers\Settings;
use Hybrid\Usage\Tracker\Tracker as UsageTracker;

class Tracker extends UsageTracker {

    /**
     * The tracking option name.
     *
     * @var string
     */
    public $option_name = 'hbp_disabler_usage_tracker_last_request';

    /**
     * The tracking prefix for cron job.
     *
     * @var string
     */
    public $cron_prefix = 'hbp_disabler_usage_tracker_last_request';

    /**
     * Registers all hooks to WordPress.
     *
     * @return void
     */
    public function boot() {
        if ( ! $this->tracking_enabled() ) {
            return;
        }

        parent::boot();

        // Add an action hook that will be triggered at the specified time by `wp_schedule_single_event()`.
        add_action( 'disabler_send_tracking_data_after_core_update', [ $this, 'send' ] );

        // Call `wp_schedule_single_event()` after a WordPress core update.
        add_action( 'upgrader_process_complete', [ $this, 'schedule_tracking_data_sending' ], 10, 2 );
    }

    /**
     * See if we should run tracking at all.
     *
     * @return bool True when we can track, false when we can't.
     */
    public function tracking_enabled() {
        // Check if we're allowing tracking.
        if ( Options::get( 'tracking_allow_usage_tracking' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Schedules a new sending of the tracking data after a WordPress core update.
     *
     * @param  bool|\WP_Upgrader $upgrader Optional. WP_Upgrader instance or false.
     *                                     Depending on context, it might be a Theme_Upgrader,
     *                                     Plugin_Upgrader, Core_Upgrade, or Language_Pack_Upgrader.
     *                                     instance. Default false.
     * @param  array             $data     Array of update data.
     * @return void
     */
    public function schedule_tracking_data_sending( $upgrader = false, $data = [] ) {
        // Return if it's not a WordPress core update.
        if ( ! $upgrader || ! isset( $data['type'] ) || 'core' !== $data['type'] ) {
            return;
        }

        /*
         * To uniquely identify the scheduled cron event, `wp_next_scheduled()`
         * needs to receive the same arguments as those used when originally
         * scheduling the event otherwise it will always return false.
         */
        if ( ! wp_next_scheduled( 'disabler_send_tracking_data_after_core_update', [ true ] ) ) {
            /*
             * Schedule sending of data tracking 6 hours after a WordPress core
             * update. Pass a `true` parameter for the callback `$force` argument.
             */
            wp_schedule_single_event( time() + ( HOUR_IN_SECONDS * 6 ), 'disabler_send_tracking_data_after_core_update', [ true ] );
        }
    }

    /**
     * Init the collector for collecting the data.
     */
    public function init_collector() {
        parent::init_collector();

        $this->add_collection( new Settings() );
    }

}
