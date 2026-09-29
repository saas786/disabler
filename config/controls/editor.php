<?php

/**
 * Editor controls.
 *
 * Loaded as `disabler.controls.editor`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [
    'editor.disable_classic_theme_styles'  => [
        'type'        => 'checkbox',
        'tab'         => 'editor',
        'priority'    => 100,
        'section'     => 'editor',
        'label'       => static fn() => esc_html__( 'Disable classic theme styles (experimental)', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf(
            /* Translators: %s is a placeholder for a link to a commit on GitHub. */
            esc_html__( 'Prevents enqueuing or inlining classic theme styles when theme.json is not present. See %s', 'hbp-disabler' ),
            '<a href="https://github.com/WordPress/wordpress-develop/commit/3e2121c83de37335bcda944a09c2d1a8f11dab7b" target="_blank">Commit</a>'
        ),
    ],

    'editor.disable_core_block_patterns'   => [
        'type'     => 'checkbox',
        'tab'      => 'editor',
        'priority' => 101,
        'section'  => 'editor',
        'label'    => static fn() => esc_html__( 'Disable core block patterns', 'hbp-disabler' ),
    ],

    'editor.disable_remote_block_patterns' => [
        'type'     => 'checkbox',
        'tab'      => 'editor',
        'priority' => 102,
        'section'  => 'editor',
        'label'    => static fn() => esc_html__( 'Disable remote block patterns', 'hbp-disabler' ),
    ],

    'editor.disable_texturization'         => [
        'type'        => 'checkbox',
        'tab'         => 'editor',
        'priority'    => 103,
        'section'     => 'editor',
        'label'       => static fn() => esc_html__( 'Disable texturization (classic editor)', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'Disables smart quotes (a.k.a. curly quotes), em dash, en dash, and ellipsis.', 'hbp-disabler' ),
    ],

    'editor.disable_capital_p'             => [
        'type'        => 'checkbox',
        'tab'         => 'editor',
        'priority'    => 104,
        'section'     => 'editor',
        'label'       => static fn() => esc_html__( 'Disable capital P correction (classic editor)', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'Disables auto-correction of WordPress capitalization.', 'hbp-disabler' ),
    ],

    'editor.disable_autop'                 => [
        'type'        => 'checkbox',
        'tab'         => 'editor',
        'priority'    => 105,
        'section'     => 'editor',
        'label'       => static fn() => esc_html__( 'Disable automatic paragraphs (classic editor)', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'Prevents <p> tags from being automatically inserted in your posts.', 'hbp-disabler' ),
    ],

    'editor.disable_autosave'              => [
        'type'     => 'radio',
        'tab'      => 'editor',
        'priority' => 106,
        'section'  => 'editor',
        'label'    => static fn() => esc_html__( 'Disable autosave', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'no'  => esc_html__( 'No', 'hbp-disabler' ),
            'yes' => esc_html__( 'Yes', 'hbp-disabler' ),
        ],
        'events'   => [
            'no'  => [
                'show' => 'editor.autosave_interval',
            ],
            'yes' => [
                'hide' => 'editor.autosave_interval',
            ],
        ],
    ],

    'editor.autosave_interval'             => [
        'type'        => 'text',
        'tab'         => 'editor',
        'priority'    => 107,
        'section'     => 'editor',
        'label'       => static fn() => esc_html__( 'Autosave interval', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'Leave empty for default interval', 'hbp-disabler' ),
        'description' => static fn() => esc_html__( 'The default is 60 seconds. We recommend not exceeding 1800 seconds (30 minutes).', 'hbp-disabler' ),
        'class'       => 'small-text',
    ],
];
