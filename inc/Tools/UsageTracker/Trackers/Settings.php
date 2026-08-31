<?php

namespace HBP\Disabler\Tools\UsageTracker\Trackers;

use HBP\Disabler\Plugin;
use HBP\Settings\Ui\PanelFactory;
use Hybrid\Usage\Tracker\Contracts\CollectionInterface;
use Hybrid\Usage\Tracker\Contracts\Tracker;
use function HBP\Disabler\container;
use function HBP\Disabler\setting;

/**
 * Settings tracker for usage tracking.
 */
class Settings implements CollectionInterface, Tracker {
    /**
     * Retrieve the settings tracker data.
     *
     * Reports the *effective* value of every declared control, which is what
     * the old merged options-over-defaults array reported. Reading the stored
     * option alone would under-report every install that never opened the
     * settings screen, and so would look like nobody uses the defaults.
     *
     * @return array<string, array<string, mixed>>
     */
    public function get() {
        $settings = [];

        foreach ( array_keys( container()->resolve( PanelFactory::class )->make( 'disabler', Plugin::SETTINGS_OPTION )->definitions()->all() ) as $key ) {
            $settings[ $key ] = setting( $key );
        }

        return [ 'disabler' => $settings ];
    }
}
