<?php

return [
    'admin_bar' => [
        'type'   => 'group',
        'fields' => static function () {
            $fields = [];

            $fields['admin_bar_info'] = [
                'id'          => 'admin_bar_info',
                'title'       => esc_html__( 'Caution!', 'hbp-disabler' ),
                'type'        => 'html',
                'page'        => 'settings_page_hbp-disabler-settings',
                'section'     => 'admin_bar',
                'setting_key' => 'admin_bar_info',
                'class'       => '',
                'callback'    => static fn() => printf(
                    /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
                    esc_html__( '%1$s It\'s recommended not to modify the Admin Bar, as it\'s essential for completing various tasks efficiently. %2$s', 'hbp-disabler' ),
                    '<p class="description">',
                    '</p>'
                ),
            ];

            $fields['disable_admin_bar'] = [
                'id'              => 'disable_admin_bar',
                'title'           => esc_html__( 'Disable Admin Bar', 'hbp-disabler' ),
                'type'            => 'radio',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'admin_bar',
                'setting_key'     => 'admin_bar_disable_admin_bar',
                'container-class' => 'admin_bar_disable_admin_bar-wrap',
                'value'           => 'no',
                'choices'         => [
                    'no'        => 'No',
                    'all'       => 'All',
                    'selective' => 'Selective',
                ],
                'events'          => [
                    'no'        => [
                        'hide' => '.admin_bar_admin_bar_roles-wrap',
                    ],
                    'all'       => [
                        'hide' => '.admin_bar_admin_bar_roles-wrap',
                    ],
                    'selective' => [
                        'show' => '.admin_bar_admin_bar_roles-wrap',
                    ],
                ],
            ];

            $fields['admin_bar_roles'] = [
                'id'              => 'admin_bar_roles',
                'title'           => esc_html__( 'Roles', 'hbp-disabler' ),
                'type'            => 'multiCheckbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'admin_bar',
                'setting_key'     => 'admin_bar_admin_bar_roles',
                'choices'         => wp_roles()->get_names(),
                'class'           => 'small-text',
                'container-class' => 'admin_bar_admin_bar_roles-wrap',
                'description'     => esc_html__( 'The Admin Bar will be disabled on the frontend for the selected WordPress roles.', 'hbp-disabler' ),
            ];

            return $fields;
        },
    ],
];
