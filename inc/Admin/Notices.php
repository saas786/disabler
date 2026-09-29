<?php

namespace HBP\Disabler\Admin;

use HBP\Disabler\Facades\Assets;
use HBP\Disabler\Tools\Update\PluginInstall;
use Hybrid\Tools\WordPress\AdminNotices;
use Hybrid\View;
use function Hybrid\Action\Scheduler\queue;

/**
 * Plugin admin notices.
 *
 * The queue, its storage and the dismiss link live in AdminNotices; this
 * class only supplies the plugin's notices and their styles. Resolve it from
 * the container so every caller shares the registered renderers.
 */
class Notices extends AdminNotices {
    /**
     * Screens the notices show on.
     */
    private const SCREENS = [ 'dashboard', 'plugins' ];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( 'hbp_disabler', 'update_plugins', self::SCREENS );
    }

    /**
     * Register the plugin's notices and hook everything up.
     */
    public function boot(): void {
        $this->register( 'update', $this->update_notice( ...) );

        add_action( 'admin_print_styles', $this->enqueue_styles( ...) );

        parent::boot();
    }

    /**
     * Enqueue the notice styles where notices render.
     */
    private function enqueue_styles(): void {
        if ( ! $this->all() || ! current_user_can( $this->capability ) || ! $this->onAllowedScreen() ) {
            return;
        }

        /** @var \Hybrid\Assets\Asset $notices_style */
        $notices_style = Assets::asset( 'css/admin/notices.css' );
        wp_enqueue_style(
            'hbp-disabler-admin-notices',
            $notices_style->url(),
            $notices_style->dependencies(),
            $notices_style->version()
        );

        // Add RTL support.
        wp_style_add_data( 'hbp-disabler-admin-notices', 'rtl', 'replace' );
    }

    /**
     * If we need to update, include a message with the update button.
     */
    private function update_notice(): void {
        if ( PluginInstall::needs_db_update() ) {
            $next_scheduled_date = queue()->get_next( 'hbp_disabler_run_update_callback', null, 'hbp-disabler-db-updates' );

            if (
                $next_scheduled_date
                // A query var is a string or an array; empty() reads both.
                // phpcs:ignore SlevomatCodingStandard.ControlStructures.DisallowEmpty.DisallowedEmpty, SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable, WordPress.Security.NonceVerification.Recommended
                || ! empty( $_GET['do_update_hbp_disabler'] )
            ) {
                View\display( 'HBP/Disabler::admin/html-notice-updating' );
            } else {
                View\display( 'HBP/Disabler::admin/html-notice-update' );
            }
        } else {
            View\display( 'HBP/Disabler::admin/html-notice-updated', [ 'dismiss_url' => $this->dismissUrl( 'update' ) ] );
        }
    }
}
