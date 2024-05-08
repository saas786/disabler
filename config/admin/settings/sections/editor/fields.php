<?php

return [
    'disable_classic_theme_styles' => [
        'id'          => 'disable_classic_theme_styles',
        'title'       => esc_html__( 'Disable Classic Theme Styles (experimental)', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'editor',
        'after_field' => sprintf(
            /* Translators: %s is a placeholder for a link to a commit on GitHub. */
            esc_html__( 'Prevents enqueuing or inlining classic theme styles when theme.json is not present. See %s', 'hbp-disabler' ),
            '<a href="https://github.com/WordPress/wordpress-develop/commit/3e2121c83de37335bcda944a09c2d1a8f11dab7b" target="_blank">Commit</a>'
        ),
        'setting_key' => 'editor_disable_classic_theme_styles',
    ],
    'disable_texturization'        => [
        'id'          => 'disable_texturization',
        'title'       => esc_html__( 'Disable Texturization (Classic Editor)', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'editor',
        'after_field' => esc_html__( 'Disables smart quotes (a.k.a. curly quotes), em dash, en dash, and ellipsis.', 'hbp-disabler' ),
        'setting_key' => 'editor_disable_texturization',
    ],
    'disable_capital_p'            => [
        'id'          => 'disable_capital_p',
        'title'       => esc_html__( 'Disable Capital P (Classic Editor)', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'editor',
        'after_field' => esc_html__( 'Disables auto-correction of WordPress capitalization.', 'hbp-disabler' ),
        'setting_key' => 'editor_disable_capital_p',
    ],
    'disable_autop'                => [
        'id'          => 'disable_autop',
        'title'       => esc_html__( 'Disable paragraphs (Classic Editor)', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'editor',
        'after_field' => esc_html__( 'Prevents <p> tags from being automatically inserted in your posts.', 'hbp-disabler' ),
        'setting_key' => 'editor_disable_autop',
    ],
    'autosave'                     => [
        'type'   => 'group',
        'fields' => static function () {
            $fields = [];

            $fields['disable_autosave'] = [
                'id'              => 'disable_autosave',
                'title'           => esc_html__( 'Disable Autosave', 'hbp-disabler' ),
                'type'            => 'radio',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'editor',
                'setting_key'     => 'editor_disable_autosave',
                'container-class' => 'editor_disable_autosave-wrap',
                'value'           => 'no',
                'choices'         => [
                    'no'  => esc_html__( 'No', 'hbp-disabler' ),
                    'yes' => esc_html__( 'Yes', 'hbp-disabler' ),
                ],
                'events'          => [
                    'no'  => [
                        'show' => '.editor_autosave_interval-wrap',
                    ],
                    'yes' => [
                        'hide' => '.editor_autosave_interval-wrap',
                    ],
                ],
            ];

            $fields['autosave_interval'] = [
                'id'              => 'autosave_interval',
                'title'           => esc_html__( 'Autosave interval', 'hbp-disabler' ),
                'type'            => 'text',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'editor',
                'setting_key'     => 'editor_autosave_interval',
                'after_field'     => esc_html__( 'Leave empty for default interval', 'hbp-disabler' ),
                'class'           => 'small-text',
                'container-class' => 'editor_autosave_interval-wrap',
                /* Translators: %1$s will be replaced with a line break. */
                'description'     => sprintf( esc_html__( 'The default is 60 seconds. We recommend not exceeding 1800 seconds (30 minutes).', 'hbp-disabler' ), '<br/>' ),
            ];

            return $fields;
        },
    ],
];
