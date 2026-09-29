<?php

/**
 * Tracking controls.
 *
 * Loaded as `disabler.controls.tracking`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [

    // The tab's opening note. An `html` control like any other, first in
    // priority order, rather than anything the section itself carries: the
    // screen renders controls, and a note is just a control that stores
    // nothing.
    'tracking.note'                 => [
        'type'     => 'html',
        'tab'      => 'tracking',
        'priority' => 1299,
        'section'  => 'tracking',
        'label'    => static fn() => esc_html__( 'Note', 'hbp-disabler' ),
        'content'  => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$s This setting enables anonymous usage data collection for the plugin, including WordPress information, installed plugins/themes, and server details. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'tracking.allow_usage_tracking' => [
        'type'        => 'checkbox',
        'tab'         => 'tracking',
        'priority'    => 1300,
        'section'     => 'tracking',
        'label'       => static fn() => esc_html__( 'Allow usage tracking', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'It will allows us to collect data about our plugin usage.', 'hbp-disabler' ),
    ],
];
