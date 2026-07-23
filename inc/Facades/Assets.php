<?php

namespace HBP\Disabler\Facades;

use Hybrid\Core\Facades\Facade;

/**
 * @see \Hybrid\Assets\Plugin
 *
 * @method static string url(string $file)
 * @method static string path(string $file)
 * @method static string assetUrl(string $file, bool $inherit)
 * @method static string assetPath(string $file, bool $inherit)
 * @method static Asset asset(string $file, bool $inherit, string $overrideManifestDirectory = '')
 */
class Assets extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'hbp/disabler/assets';
    }
}
