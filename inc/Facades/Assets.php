<?php

namespace HBP\Disabler\Facades;

use Hybrid\Core\Facades\Facade;

/**
 * @see \Hybrid\Assets\Component
 *
 * @method static string assetUrl(string $file)
 * @method static string assetPath(string $file)
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
