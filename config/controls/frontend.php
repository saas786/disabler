<?php

/**
 * Frontend controls.
 *
 * Loaded as `disabler.controls.frontend`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [
    'frontend.disable_shortlinks' => [
        'type'        => 'checkbox',
        'tab'         => 'frontend',
        'priority'    => 300,
        'section'     => 'frontend',
        'label'       => static fn() => esc_html__( 'Disable shortlinks', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( esc_html__( 'Prevents links to WordPress\' internal \'shortlink\' URLs for your posts. For example, %1$s', 'hbp-disabler' ), '<code>' . esc_html( '<link rel="shortlink" href="https://www.example.com/?p=1" />' ) . '</code>' ),
    ],
];
