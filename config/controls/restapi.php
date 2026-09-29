<?php

/**
 * Restapi controls.
 *
 * Loaded as `disabler.controls.restapi`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [
    'restapi.disable_rest_api_for_visitors'    => [
        'type'     => 'checkbox',
        'tab'      => 'restapi',
        'priority' => 800,
        'section'  => 'restapi',
        'label'    => static fn() => esc_html__( 'Disable REST API for visitors', 'hbp-disabler' ),
    ],

    'restapi.disable_rest_api_links'           => [
        'type'        => 'checkbox',
        'tab'         => 'restapi',
        'priority'    => 801,
        'section'     => 'restapi',
        'label'       => static fn() => esc_html__( 'Disable REST API links', 'hbp-disabler' ),
        // The sample is markup to look at, not markup to render. Panel runs
        // helper text through wp_kses_post(), which strips <link> outright, so
        // an unescaped sample arrives at the browser as an empty <code> box.
        'after_field' => static fn() => '<code>' . esc_html( '<link rel="https://api.w.org/" href="https://www.example.com/wp-json/" />' ) . '</code>',
    ],

    'restapi.disable_rest_api_rsd_link'        => [
        'type'        => 'checkbox',
        'tab'         => 'restapi',
        'priority'    => 802,
        'section'     => 'restapi',
        'label'       => static fn() => esc_html__( 'Disable REST API RSD link', 'hbp-disabler' ),
        'after_field' => static fn() => '<code>' . esc_html( '<api name="WP-API" blogID="1" preferred="false" apiLink="https://www.example.com/wp-json/" />' ) . '</code>',
    ],

    'restapi.disable_rest_api_link_in_headers' => [
        'type'        => 'checkbox',
        'tab'         => 'restapi',
        'priority'    => 803,
        'section'     => 'restapi',
        'label'       => static fn() => esc_html__( 'Disable REST API link in HTTP headers', 'hbp-disabler' ),
        'after_field' => static fn() => '<code>' . esc_html( 'Link: <https://example.com/wp-json/>; rel="https://api.w.org/"' ) . '</code>',
    ],

    'restapi.disable_application_passwords'    => [
        'type'        => 'radio',
        'tab'         => 'restapi',
        'priority'    => 804,
        'section'     => 'restapi',
        'label'       => static fn() => esc_html__( 'Disable application passwords', 'hbp-disabler' ),
        'description' => static fn() => sprintf(
                /* Translators: %s is a placeholder for a link to relevant documentation. */
                    esc_html__( 'Application Passwords let users authenticate to the REST API with a generated password, bypassing 2FA/CAPTCHA/rate limiting. Disable them if you don\'t use programmatic API access. %s', 'hbp-disabler' ),
                    '<a href="https://developer.wordpress.org/advanced-administration/security/application-passwords/" target="_blank">See</a>'
                ),
        'choices'     => static fn() => [
            'no'        => esc_html__( 'No', 'hbp-disabler' ),
            'all'       => esc_html__( 'All', 'hbp-disabler' ),
            'selective' => esc_html__( 'Selective', 'hbp-disabler' ),
        ],
        'events'      => [
            'no'        => [
                'hide' => 'restapi.application_passwords_roles',
            ],
            'all'       => [
                'hide' => 'restapi.application_passwords_roles',
            ],
            'selective' => [
                'show' => 'restapi.application_passwords_roles',
            ],
        ],
    ],

    'restapi.application_passwords_roles'      => [
        'type'        => 'multicheckbox',
        'tab'         => 'restapi',
        'priority'    => 805,
        'section'     => 'restapi',
        'label'       => static fn() => esc_html__( 'Roles', 'hbp-disabler' ),
        'description' => static fn() => esc_html__( 'Application Passwords will be disabled for the selected WordPress roles.', 'hbp-disabler' ),
        // Every role the site actually has, not a fixed three. A site with
        // shop managers or SEO editors needs to be able to name them, and a
        // hardcoded list silently drops the roles it does not know about.
        // Deferred, because roles are not registered until init.
        'choices'     => static fn(): array => wp_roles()->get_names(),
    ],
];
