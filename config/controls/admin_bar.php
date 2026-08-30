<?php

/**
 * Admin Bar controls.
 *
 * Loaded as `disabler.controls.admin_bar`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [
    'admin_bar.info'              => [
        'type'     => 'html',
        'tab'      => 'admin_bar',
        'priority' => 1100,
        'section'  => 'admin_bar',
        'label'    => static fn() => esc_html__( 'Caution!', 'hbp-disabler' ),
        'class'    => '',
        'content'  => static fn() => sprintf(
                    /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
                    esc_html__( '%1$s It\'s recommended not to modify the Admin Bar, as it\'s essential for completing various tasks efficiently. %2$s', 'hbp-disabler' ),
                    '<p class="description">',
                    '</p>'
                ),
    ],

    'admin_bar.disable_admin_bar' => [
        'type'     => 'radio',
        'tab'      => 'admin_bar',
        'priority' => 1101,
        'section'  => 'admin_bar',
        'label'    => static fn() => esc_html__( 'Disable admin bar', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'no'        => esc_html__( 'No', 'hbp-disabler' ),
            'all'       => esc_html__( 'All', 'hbp-disabler' ),
            'selective' => esc_html__( 'Selective', 'hbp-disabler' ),
        ],
        'events'   => [
            'no'        => [
                'hide' => 'admin_bar.admin_bar_roles',
            ],
            'all'       => [
                'hide' => 'admin_bar.admin_bar_roles',
            ],
            'selective' => [
                'show' => 'admin_bar.admin_bar_roles',
            ],
        ],
    ],

    'admin_bar.admin_bar_roles'   => [
        'type'        => 'multicheckbox',
        'tab'         => 'admin_bar',
        'priority'    => 1102,
        'section'     => 'admin_bar',
        'label'       => static fn() => esc_html__( 'Roles', 'hbp-disabler' ),
        'description' => static fn() => esc_html__( 'The Admin Bar will be disabled on the frontend for the selected WordPress roles.', 'hbp-disabler' ),
        // See the note on restapi.application_passwords_roles: the site's own
        // roles, resolved after init rather than fixed here.
        'choices'     => static fn(): array => wp_roles()->get_names(),
        'class'       => 'small-text',
    ],
];
