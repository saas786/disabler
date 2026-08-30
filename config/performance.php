<?php

/**
 * Performance defaults.
 *
 * The config tier of the resolution order: what a setting resolves to when
 * nothing is stored and no preset overrides it. Carried over from the old
 * flat defaults file, split by section and stripped of its section prefix.
 */

return [
    'disable_embeds'      => 0,
    'disable_emojis'      => 0,
    'disable_heartbeat'   => 'no',
    'disable_widgets'     => 'no',
    'heartbeat_frequency' => '',
];
