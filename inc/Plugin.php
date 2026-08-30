<?php

namespace HBP\Disabler;

/**
 * Plugin class.
 */
class Plugin {
    /**
     * The current version of the plugin.
     */
    const VERSION = '4.0.4';

    /**
     * The current db version of the plugin.
     */
    const DB_VERSION = '4.0.4';

    /**
     * The option this plugin's settings live in.
     *
     * Named here rather than on the settings screen because the frontend
     * reads the same row and must not have to reach into an admin class --
     * and because a default option name derived from the namespace would be
     * `disabler_settings`, which is a different, long-deleted option.
     */
    const SETTINGS_OPTION = 'hbp_disabler_settings';

    /**
     * The current release date of the plugin.
     */
    const RELEASE_DATE = '8 July, 2026 12:00AM (GMT + 5)';
}
