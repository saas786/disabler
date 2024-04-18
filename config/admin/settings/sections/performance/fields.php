<?php

return [
    'disable_emojis'  => [
        'id'          => 'disable_emojis',
        'title'       => esc_html__( 'Disable Emojis', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'performance',
        'setting_key' => 'performance_disable_emojis',
    ],
    'disable_embeds'  => [
        'id'          => 'disable_embeds',
        'title'       => esc_html__( 'Disable Embeds', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'performance',
        'setting_key' => 'performance_disable_embeds',
        'description' => esc_html__( 'Prevents others from embedding content from your site and removes JavaScript requests related to WordPress embeds.', 'hbp-disabler' ),
    ],
    'heartbeat'       => [
        'type'   => 'group',
        'fields' => static function () {
            $fields = [];

            $fields['heartbeat_info'] = [
                'id'          => 'heartbeat_info',
                'title'       => esc_html__( 'Heartbeat', 'hbp-disabler' ),
                'type'        => 'html',
                'page'        => 'settings_page_hbp-disabler-settings',
                'section'     => 'performance',
                'setting_key' => 'performance_heartbeat_info',
                'class'       => '',
                'callback'    => static fn() => printf(
                /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
                    esc_html__( '%1$s The WordPress Heartbeat API uses /wp-admin/admin-ajax.php to run AJAX calls from the web-browser. While this is great and all it can also cause high CPU usage and crazy amounts of PHP calls. For example, if you leave your dashboard open it will keep sending POST requests to this file on a regular interval, every 15 seconds. Here is an example below of it happening. %2$s', 'hbp-disabler' ),
                    '<p class="description">',
                    '</p>'
                ),
            ];

            $fields['disable_heartbeat'] = [
                'id'              => 'disable_heartbeat',
                'title'           => esc_html__( 'Disable Heartbeat', 'hbp-disabler' ),
                'type'            => 'radio',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'performance',
                'setting_key'     => 'performance_disable_heartbeat',
                'container-class' => 'performance_disable_heartbeat-wrap',
                'value'           => 'no',
                'choices'         => [
                    'no'                            => 'No',
                    'everywhere'                    => 'Everywhere',
                    'on_dashboard_page'             => 'In admin panel',
                    'allow_only_on_post_edit_pages' => 'Only allow when editing Posts/Pages',
                ],
                'events'          => [
                    'no'                            => [
                        'show' => '.performance_heartbeat_frequency-wrap',
                    ],
                    'everywhere'                    => [
                        'hide' => '.performance_heartbeat_frequency-wrap',
                    ],
                    'on_dashboard_page'             => [
                        'show' => '.performance_heartbeat_frequency-wrap',
                    ],
                    'allow_only_on_post_edit_pages' => [
                        'show' => '.performance_heartbeat_frequency-wrap',
                    ],
                ],
            ];

            $fields['heartbeat_frequency'] = [
                'id'              => 'heartbeat_frequency',
                'title'           => esc_html__( 'Heartbeat frequency', 'hbp-disabler' ),
                'type'            => 'text',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'performance',
                'setting_key'     => 'performance_heartbeat_frequency',
                'after_field'     => esc_html__( 'Leave empty for default frequency', 'hbp-disabler' ),
                'class'           => 'small-text',
                'container-class' => 'performance_heartbeat_frequency-wrap',
                /* Translators: %1$s will be replaced with a line break, %2$s will be replaced with the opening <strong> tag, and %3$s will be replaced with the closing </strong> tag. */
                'description'     => sprintf( esc_html__( 'We recommend you 60 seconds, default is 20 seconds. %1$s %2$s Note:%3$s When \'Everywhere\' is set, Heartbeat frequency won\'t have any effect.', 'hbp-disabler' ), '<br/>', '<strong>', '</strong>' ),
            ];

            return $fields;
        },
    ],
    'disable_widgets' => [
        'id'          => 'disable_widgets',
        'title'       => esc_html__( 'Disable Widgets', 'hbp-disabler' ),
        'type'        => 'radio',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'performance',
        'setting_key' => 'performance_disable_widgets',
        'value'       => 'no',
        'choices'     => [
            'no'   => esc_html__( 'No', 'hbp-disabler' ),
            'all'  => esc_html__( 'All', 'hbp-disabler' ),
            'core' => esc_html__( 'Core (only)', 'hbp-disabler' ),
        ],
    ],
];
