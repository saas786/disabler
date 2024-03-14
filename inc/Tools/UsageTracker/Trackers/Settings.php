<?php

namespace HBP\Disabler\Tools\UsageTracker\Trackers;

use Hybrid\Usage\Tracker\Contracts\CollectionInterface;
use Hybrid\Usage\Tracker\Contracts\Tracker;

/**
 * Settings.
 */
class Settings implements CollectionInterface, Tracker {

    /**
     * Returns the tracker data.
     *
     * @return array
     */
    public function get() {
        return [];
    }

}
