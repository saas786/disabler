<?php

/**
 * SVG class.
 *
 * A simple class for rendering or displaying an SVG file.
 */

namespace HBP\Disabler\Tools;

use HBP\Disabler\Facades\Assets;

/**
 * SVG class.
 */
class SVG {
    protected static string $assetsFolder = ''; // assets

    /**
     * Returns the SVG file contents.
     *
     * @param string $name
     *
     * @return string
     */
    public static function render( $name ) {
        $svg = file_get_contents( static::path( "{$name}.svg" ) );

        return $svg ?: '';
    }

    /**
     * Displays the SVG.
     *
     * @param string $name
     *
     * @return void
     */
    public static function display( $name ) {
        echo static::render( $name );
    }

    /**
     * Returns the path to the SVG folder or file if set.
     *
     * @return string
     */
    public static function path( string $file = '' ) {
        return Assets::assetPath( static::prepareFile( $file ) );
    }

    /**
     * Returns the uri to the SVG folder or file if set.
     *
     * @return string
     */
    public static function uri( $file = '' ) {
        return Assets::assetUrl( static::prepareFile( $file ) );
    }

    public static function prepareFile( $file ) {
        $file = trim( $file, '/' );

        return $file ? static::$assetsFolder . "/svg/{$file}" : static::$assetsFolder . '/svg';
    }
}
