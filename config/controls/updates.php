<?php

/**
 * Updates controls.
 *
 * Loaded as `disabler.controls.updates`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [
    'updates.disable_updates'             => [
        'type'     => 'radio',
        'tab'      => 'updates',
        'priority' => 1200,
        'section'  => 'updates',
        'label'    => static fn() => esc_html__( 'Disable updates', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'no'        => esc_html__( 'No', 'hbp-disabler' ),
            'all'       => esc_html__( 'All', 'hbp-disabler' ),
            'selective' => esc_html__( 'Selective', 'hbp-disabler' ),
        ],
        'events'   => [
            'no'        => [
                'hide' => [
                    'updates.plugin_updates',
                    'updates.theme_updates',
                    'updates.translation_updates',
                    'updates.core_updates',
                    'updates.enable_update_vcs',
                    'updates.updates_nags_only_for_admin',
                ],
            ],
            'all'       => [
                'hide' => [
                    'updates.plugin_updates',
                    'updates.theme_updates',
                    'updates.translation_updates',
                    'updates.core_updates',
                    'updates.enable_update_vcs',
                    'updates.updates_nags_only_for_admin',
                ],
            ],
            'selective' => [
                'show' => [
                    'updates.plugin_updates',
                    'updates.theme_updates',
                    'updates.translation_updates',
                    'updates.core_updates',
                    'updates.enable_update_vcs',
                    'updates.updates_nags_only_for_admin',
                ],
            ],
        ],
    ],

    'updates.plugin_updates'              => [
        'type'     => 'radio',
        'tab'      => 'updates',
        'priority' => 1201,
        'section'  => 'updates',
        'label'    => static fn() => esc_html__( 'Plugin updates', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'manual'  => esc_html__( 'Manual', 'hbp-disabler' ),
            'auto'    => esc_html__( 'Auto', 'hbp-disabler' ),
            'disable' => esc_html__( 'Disable', 'hbp-disabler' ),
        ],
    ],

    'updates.theme_updates'               => [
        'type'     => 'radio',
        'tab'      => 'updates',
        'priority' => 1202,
        'section'  => 'updates',
        'label'    => static fn() => esc_html__( 'Theme updates', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'manual'  => esc_html__( 'Manual', 'hbp-disabler' ),
            'auto'    => esc_html__( 'Auto', 'hbp-disabler' ),
            'disable' => esc_html__( 'Disable', 'hbp-disabler' ),
        ],
    ],

    'updates.translation_updates'         => [
        'type'     => 'radio',
        'tab'      => 'updates',
        'priority' => 1203,
        'section'  => 'updates',
        'label'    => static fn() => esc_html__( 'Translation updates', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'default' => esc_html__( 'Default', 'hbp-disabler' ),
            'auto'    => esc_html__( 'Auto', 'hbp-disabler' ),
            'disable' => esc_html__( 'Disable', 'hbp-disabler' ),
        ],
    ],

    'updates.core_updates'                => [
        'type'     => 'radio',
        'tab'      => 'updates',
        'priority' => 1204,
        'section'  => 'updates',
        'label'    => static fn() => esc_html__( 'WordPress core updates', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'disable_core_updates'          => esc_html__( 'Disable updates', 'hbp-disabler' ),
            'disable_core_auto_updates'     => esc_html__( 'Disable auto updates', 'hbp-disabler' ),
            'allow_minor_core_auto_updates' => esc_html__( 'Allow minor auto updates', 'hbp-disabler' ),
            'allow_major_core_auto_updates' => esc_html__( 'Allow major auto updates', 'hbp-disabler' ),
            'allow_dev_core_auto_updates'   => esc_html__( 'Allow development auto updates', 'hbp-disabler' ),
        ],
    ],

    'updates.enable_update_vcs'           => [
        'type'        => 'radio',
        'tab'         => 'updates',
        'priority'    => 1205,
        'section'     => 'updates',
        'label'       => static fn() => esc_html__( 'Treat as a version-controlled install', 'hbp-disabler' ),
        'description' => static fn() => esc_html__( 'WordPress skips automatic updates when it finds a .git, .hg, .svn or .bzr folder, so that a deployment is never overwritten in place. Choose Yes to claim version control even when no such folder is found, or No to allow automatic updates when one is.', 'hbp-disabler' ),
        'choices'     => static fn() => [
            'default' => esc_html__( 'Default', 'hbp-disabler' ),
            'enable'  => esc_html__( 'Yes, skip automatic updates', 'hbp-disabler' ),
            'disable' => esc_html__( 'No, allow automatic updates', 'hbp-disabler' ),
        ],
    ],

    'updates.updates_nags_only_for_admin' => [
        'type'        => 'checkbox',
        'tab'         => 'updates',
        'priority'    => 1206,
        'section'     => 'updates',
        'label'       => static fn() => esc_html__( 'Show update notices to administrators only', 'hbp-disabler' ),
        'description' => static fn() => esc_html__( 'Hides WordPress core update available notice for users who don\'t have `update_core` capability access.', 'hbp-disabler' ),
    ],
];
