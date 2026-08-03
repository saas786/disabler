<?php

return [
    'disable_rest_api_for_visitors'    => [
        'id'          => 'disable_rest_api_for_visitors',
        'title'       => static fn() => esc_html__( 'Disable REST API for visitors', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'setting_key' => 'restapi_disable_rest_api_for_visitors',
    ],
    'disable_rest_api_links'           => [
        'id'          => 'disable_rest_api_links',
        'title'       => static fn() => esc_html__( 'Disable REST API links', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'after_field' => '<code>' . esc_html( '<link rel="https://api.w.org/" href="https://www.example.com/wp-json/" />' ) . '</code>',
        'setting_key' => 'restapi_disable_rest_api_links',
    ],
    'disable_rest_api_rsd_link'        => [
        'id'          => 'disable_rest_api_rsd_link',
        'title'       => static fn() => esc_html__( 'Disable Rest API RSD link', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'after_field' => '<code>' . esc_html( '<api name="WP-API" blogID="1" preferred="false" apiLink="https://www.example.com/wp-json/" />' ) . '</code>',
        'setting_key' => 'restapi_disable_rest_api_rsd_link',
    ],
    'disable_rest_api_link_in_headers' => [
        'id'          => 'disable_rest_api_link_in_headers',
        'title'       => static fn() => esc_html__( 'Disable REST API link in HTTP headers', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'after_field' => '<code>' . esc_html( 'Link: <https://example.com/wp-json/>; rel="https://api.w.org/"' ) . '</code>',
        'setting_key' => 'restapi_disable_rest_api_link_in_headers',
    ],
    'application_passwords'            => [
        'type'   => 'group',
        'fields' => static function () {
            $fields = [];

            $fields['disable_application_passwords'] = [
                'id'              => 'disable_application_passwords',
                'title'           => static fn() => esc_html__( 'Disable Application Passwords', 'hbp-disabler' ),
                'type'            => 'radio',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'restapi',
                'setting_key'     => 'restapi_disable_application_passwords',
                'container-class' => 'restapi_disable_application_passwords-wrap',
                'value'           => 'no',
                'choices'         => static fn() => [
                    'no'        => esc_html__( 'No', 'hbp-disabler' ),
                    'all'       => esc_html__( 'All', 'hbp-disabler' ),
                    'selective' => esc_html__( 'Selective', 'hbp-disabler' ),
                ],
                'events'          => [
                    'no'        => [
                        'hide' => '.restapi_application_passwords_roles-wrap',
                    ],
                    'all'       => [
                        'hide' => '.restapi_application_passwords_roles-wrap',
                    ],
                    'selective' => [
                        'show' => '.restapi_application_passwords_roles-wrap',
                    ],
                ],
                /* Translators: %1$s will be replaced with a line break. */
                'description'     => static fn() => sprintf(
                /* Translators: %s is a placeholder for a link to relevant documentation. */
                    esc_html__( 'Application Passwords let users authenticate to the REST API with a generated password, bypassing 2FA/CAPTCHA/rate limiting. Disable them if you don\'t use programmatic API access. %s', 'hbp-disabler' ),
                    '<a href="https://developer.wordpress.org/advanced-administration/security/application-passwords/" target="_blank">See</a>'
                ),
            ];

            $fields['application_passwords_roles'] = [
                'id'              => 'application_passwords_roles',
                'title'           => static fn() => esc_html__( 'Roles', 'hbp-disabler' ),
                'type'            => 'multiCheckbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'restapi',
                'setting_key'     => 'restapi_application_passwords_roles',
                'choices'         => wp_roles()->get_names(),
                'container-class' => 'restapi_application_passwords_roles-wrap',
                'description'     => static fn() => esc_html__( 'Application Passwords will be disabled for the selected WordPress roles.', 'hbp-disabler' ),
            ];

            return $fields;
        },
    ],
];
