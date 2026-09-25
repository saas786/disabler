<?php

namespace HBP\Disabler\Facades;

use HBP\Disabler\Admin\Notices as AdminNotices;
use Hybrid\Core\Facades\Facade;

/**
 * Static entry point to the plugin's admin notice queue. The accessor is the
 * container singleton registered in PluginServiceProvider, so every caller
 * works on the instance that registered the notice renderers.
 *
 * @see \Hybrid\Tools\WordPress\AdminNotices
 *
 * @method static void add(string $name)
 * @method static void remove(string $name)
 * @method static bool has(string $name)
 * @method static array<int, string> all()
 * @method static void clear()
 * @method static string dismissUrl(string $name)
 */
class Notices extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return AdminNotices::class;
    }
}
