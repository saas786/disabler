<?php
/**
 * Plugins screen.
 */

namespace HBP\Disabler\Admin;

use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

class PluginsPage {

    use AccessiblePrivateMethods;

    /**
     * Boot.
     */
    public function boot(): void {
        self::add_filter( 'plugin_action_links_' . DISABLER_BASENAME, [ $this, 'pluginActionLinks' ], 10, 2 );
    }

    private function pluginActionLinks( $links, $file ) {

        if ( DISABLER_BASENAME === $file && current_user_can( 'manage_options' ) ) {
            $settings_url   = admin_url( 'options-general.php?page=hbp-disabler-settings' );
            $settings_title = esc_attr__( 'Visit the Disabler plugin page', 'hbp-disabler' );
            $settings_text  = esc_html__( 'Settings', 'hbp-disabler' );

            $settings_link = '<a href="' . $settings_url . '" title="' . $settings_title . '">' . $settings_text . '</a>';

            array_unshift( $links, $settings_link );
        }

        return $links;
    }

}
