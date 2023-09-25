<?php
/**
 * Implementation of Assets interface to handle plugin assets.
 */

namespace HBP\Disabler\Assets;

use Hybrid\Assets\Contracts\Assets;

/**
 * Class Component
 * Handles plugin assets - path and URL.
 */
class Component implements Assets {

    /**
     * Retrieve the absolute filesystem path of a file within the plugin.
     *
     * @param  string $file File path within the plugin.
     * @return string Absolute filesystem path of the file.
     */
    public function path( $file = '' ) {
        $plugin_path = plugin_dir_path( $this->plugin_file );

        if ( $file ) {
            return $plugin_path . ltrim( $file, '/' );
        }

        return $plugin_path;
    }

    /**
     * Retrieve the URL for a file within the plugin.
     *
     * @param  string $file File path within the plugin.
     * @return string URL of the file.
     */
    public function url( $file = '' ) {
        $plugin_url = plugin_dir_url( $this->plugin_file );

        if ( $file ) {
            return $plugin_url . ltrim( $file, '/' );
        }

        return $plugin_url;
    }

}
