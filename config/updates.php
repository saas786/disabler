<?php

/**
 * Updates defaults.
 *
 * The config tier of the resolution order: what a setting resolves to when
 * nothing is stored and no preset overrides it. Carried over from the old
 * flat defaults file, split by section and stripped of its section prefix.
 */

return [
    'core_updates'                => 'allow_minor_core_auto_updates',
    'disable_updates'             => 'no',
    'enable_update_vcs'           => 'default',
    'plugin_updates'              => 'manual',
    'theme_updates'               => 'manual',
    'translation_updates'         => 'default',
    'updates_nags_only_for_admin' => 0,
];
