<?php

namespace HBP\Disabler\Optimize;

use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;
use function HBP\Disabler\setting;

class AdminBar implements Bootable {

    use AccessiblePrivateMethods;

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
    }

    private function initHooks(): void {
        if ( ! is_user_logged_in() || 'no' === setting( 'admin_bar.disable_admin_bar' ) ) {
            return;
        }

        add_filter( 'show_admin_bar', [ $this, 'showAdminBar' ], \PHP_INT_MAX - 100 );
    }

    /**
     * Conditionally shows or hides the admin bar on the frontend.
     *
     * @return bool True if the admin bar should be shown, false otherwise.
     */
    public function showAdminBar(): bool {
        $show_admin_bar = true;

        if ( 'all' === setting( 'admin_bar.disable_admin_bar' ) ) {
            $show_admin_bar = false;
        }

        if ( 'selective' === setting( 'admin_bar.disable_admin_bar' ) ) {
            $selected_roles = setting( 'admin_bar.admin_bar_roles', [] );

            foreach ( wp_get_current_user()->roles as $role ) {
                if ( in_array( $role, $selected_roles, true ) ) {
                    $show_admin_bar = false;

                    break;
                }
            }
        }

        return $show_admin_bar;
    }
}
