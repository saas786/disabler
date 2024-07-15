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
 *
 *  Provides methods for retrieving plugin options.
 */
class Options {

    /**
     * Settings key/identifier.
     */
    public static string $option_key = 'hbp_disabler_settings';

    /**
     * Gets an option by name. If name is omitted, returns all options.
     *
     * @param  string $name    The name of the option to retrieve. Optional.
     * @param  mixed  $default Default value to return if the option does not exist.
     * @return mixed|null The value of the option, or all options if $name is empty.
     */
    public static function get( $name = '', $default = null ) {
        $settings = self::all();

        if ( ! $name ) {
            return $settings;
        }

        return $settings[ $name ] ?? $default;
    }

    /**
     * Returns an array of all default options.
     *
     * @return array The default options.
     */
    public static function defaults() {
        return config( 'admin.settings.defaults', [] );
    }

    /**
     * Retrieves all options merged with defaults.
     *
     * @return array The merged array of options.
     */
    public static function all() {
        return wp_parse_args( get_option( self::$option_key, [] ), static::defaults() );
    }

}
