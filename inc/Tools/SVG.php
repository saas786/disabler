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
    /**
     * Relative folder (within the assets directory) where SVGs live.
     */
    protected static string $svgFolder = 'svg';

    /**
     * Returns the SVG file contents.
     *
     * @param string $name
     * @param bool   $inherit
     */
    public static function render( string $name, bool $inherit = false ): string {
        $path = Assets::assetPath( static::prepareFile( "{$name}.svg" ), $inherit );

        if ( ! $path || ! is_readable( $path ) ) {
            return '';
        }

        $svg = file_get_contents( $path );

        return $svg ?: '';
    }

    /**
     * Displays the SVG.
     *
     * @param string $name
     * @param bool   $inherit
     */
    public static function display( string $name, bool $inherit = false ): void {
        echo static::render( $name, $inherit );
    }

    /**
     * Prefixes a file with the SVG folder path.
     *
     * @param string $file
     */
    protected static function prepareFile( string $file ): string {
        $file = trim( $file, '/' );

        return $file ? static::$svgFolder . "/{$file}" : static::$svgFolder;
    }
}
