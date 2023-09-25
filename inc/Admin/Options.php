<?php
/**
 * Options Helper.
 *
 * This is an options helper class for quickly getting theme options.
 */

namespace HBP\Disabler\Admin;

use function Hybrid\Tools\config;

/**
 * Options class.
 */
class Options {

    /**
     * Settings key/identifier.
     */
    public static string $option_key = 'hbp_disabler_settings';

    /**
     * Gets an option by name. If name is omitted, returns all options.
     *
     * @param  string $name
     * @return mixed
     */
    public static function get( $name = '', $default = null ) {
        $settings = wp_parse_args( get_option( self::$option_key, [] ), static::defaults() );

        if ( ! $name ) {
            return $settings;
        }

        return $settings[ $name ] ?? $default;
    }

    /**
     * Returns an array of all default options.
     *
     * @return array
     */
    public static function defaults() {
        return config( 'admin.settings.defaults', [] );
    }

}
