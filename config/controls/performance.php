<?php

/**
 * Performance controls.
 *
 * Loaded as `disabler.controls.performance`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [
    'performance.disable_emojis'              => [
        'type'     => 'checkbox',
        'tab'      => 'performance',
        'priority' => 400,
        'section'  => 'performance',
        'label'    => static fn() => esc_html__( 'Disable emojis', 'hbp-disabler' ),
    ],

    'performance.disable_embeds'              => [
        'type'        => 'checkbox',
        'tab'         => 'performance',
        'priority'    => 401,
        'section'     => 'performance',
        'label'       => static fn() => esc_html__( 'Disable embeds', 'hbp-disabler' ),
        'description' => static fn() => esc_html__( 'Prevents others from embedding content from your site and removes JavaScript requests related to WordPress embeds.', 'hbp-disabler' ),
    ],

    'performance.heartbeat_info'              => [
        'type'     => 'html',
        'tab'      => 'performance',
        'priority' => 402,
        'section'  => 'performance',
        'label'    => static fn() => esc_html__( 'Heartbeat', 'hbp-disabler' ),
        'class'    => '',
        'content'  => static fn() => sprintf(
                    /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
                    esc_html__( '%1$s The WordPress Heartbeat API uses /wp-admin/admin-ajax.php to run AJAX calls from the web-browser. While this is great and all it can also cause high CPU usage and crazy amounts of PHP calls. For example, if you leave your dashboard open it will keep sending POST requests to this file on a regular interval, every 15 seconds. %2$s', 'hbp-disabler' ),
                    '<p class="description">',
                    '</p>'
                ),
    ],

    'performance.disable_heartbeat'           => [
        'type'     => 'radio',
        'tab'      => 'performance',
        'priority' => 403,
        'section'  => 'performance',
        'label'    => static fn() => esc_html__( 'Disable Heartbeat', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'no'                            => esc_html__( 'No', 'hbp-disabler' ),
            'everywhere'                    => esc_html__( 'Everywhere', 'hbp-disabler' ),
            'on_dashboard_page'             => esc_html__( 'In admin panel', 'hbp-disabler' ),
            'allow_only_on_post_edit_pages' => esc_html__( 'Only allow when editing Posts/Pages', 'hbp-disabler' ),
        ],
        'events'   => [
            'no'                            => [
                'show' => 'performance.heartbeat_frequency',
            ],
            'everywhere'                    => [
                'hide' => 'performance.heartbeat_frequency',
            ],
            'on_dashboard_page'             => [
                'show' => 'performance.heartbeat_frequency',
            ],
            'allow_only_on_post_edit_pages' => [
                'show' => 'performance.heartbeat_frequency',
            ],
        ],
    ],

    'performance.heartbeat_frequency'         => [
        'type'        => 'text',
        'tab'         => 'performance',
        'priority'    => 404,
        'section'     => 'performance',
        'label'       => static fn() => esc_html__( 'Heartbeat frequency', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'Leave empty for default frequency', 'hbp-disabler' ),
        'description' => static fn() => sprintf( esc_html__( 'We recommend you 60 seconds, default is 15 seconds. %1$s %2$s Note:%3$s When \'Everywhere\' is set, Heartbeat frequency won\'t have any effect.', 'hbp-disabler' ), '<br/>', '<strong>', '</strong>' ),
        'class'       => 'small-text',
    ],

    'performance.disable_speculative_loading' => [
        'type'        => 'radio',
        'tab'         => 'performance',
        'priority'    => 406,
        'section'     => 'performance',
        'label'       => static fn() => esc_html__( 'Disable speculative loading', 'hbp-disabler' ),
        'description' => static fn() => esc_html__( 'Since WordPress 6.8 the browser is told to fetch pages a visitor looks likely to open next, before they click. This is off for logged-in users and for sites without pretty permalinks either way. Prefetch requests the page; prerender also runs its scripts, which can fire analytics and other side effects on pages nobody visits.', 'hbp-disabler' ),
        'choices'     => static fn() => [
            'no'        => esc_html__( 'No', 'hbp-disabler' ),
            'all'       => esc_html__( 'All', 'hbp-disabler' ),
            'prerender' => esc_html__( 'Prerender (only)', 'hbp-disabler' ),
        ],
    ],

    'performance.disable_widgets'             => [
        'type'     => 'radio',
        'tab'      => 'performance',
        'priority' => 405,
        'section'  => 'performance',
        'label'    => static fn() => esc_html__( 'Disable widgets', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'no'   => esc_html__( 'No', 'hbp-disabler' ),
            'all'  => esc_html__( 'All', 'hbp-disabler' ),
            'core' => esc_html__( 'Core (only)', 'hbp-disabler' ),
        ],
    ],
];
