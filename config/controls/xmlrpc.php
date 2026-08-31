<?php

/**
 * Xmlrpc controls.
 *
 * Loaded as `disabler.controls.xmlrpc`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [

    // The tab's opening note. An `html` control like any other, first in
    // priority order, rather than anything the section itself carries: the
    // screen renders controls, and a note is just a control that stores
    // nothing.
    'xmlrpc.note'                           => [
        'type'     => 'html',
        'tab'      => 'xmlrpc',
        'priority' => 999,
        'section'  => 'xmlrpc',
        'label'    => static fn() => esc_html__( 'Note', 'hbp-disabler' ),
        'content'  => static fn() => sprintf(
            /* Translators: %1$s opening paragraph tag, %2$s closing paragraph tag, %3$s opening code tag, %4$s closing code tag. */
            esc_html__( '%1$s If you select %3$sCompletely%4$s, than you don\'t need to select any other settings below, leave them as is, otherwise you can make adjustments as per your needs. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>',
            '<code>',
            '</code>'
        ),
    ],

    'xmlrpc.disable_xmlrpc'                 => [
        'type'     => 'radio',
        'tab'      => 'xmlrpc',
        'priority' => 1000,
        'section'  => 'xmlrpc',
        'label'    => static fn() => esc_html__( 'Disable XML-RPC', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'no'         => esc_html__( 'No', 'hbp-disabler' ),
            'completely' => esc_html__( 'Completely', 'hbp-disabler' ),
            'selective'  => esc_html__( 'Selective', 'hbp-disabler' ),
        ],
        'events'   => [
            'no'         => [
                'hide' => [
                    'xmlrpc.xmlrpc_whitelist_jetpack_ips',
                    'xmlrpc.custom_xmlrpc_whitelist_ips',
                    'xmlrpc.xmlrpc_methods',
                    'xmlrpc.custom_xmlrpc_methods',
                    'xmlrpc.disable_xmlrpc_headers',
                    'xmlrpc.custom_xmlrpc_headers',
                    'xmlrpc.xmlrpc_remove_rsd_link',
                    'xmlrpc.xmlrpc_remove_wlwmanifest_link',
                    'xmlrpc.remove_xmlrpc_pingback_link',
                ],
            ],
            'completely' => [
                'hide' => [
                    'xmlrpc.xmlrpc_methods',
                    'xmlrpc.custom_xmlrpc_methods',
                    'xmlrpc.disable_xmlrpc_headers',
                    'xmlrpc.custom_xmlrpc_headers',
                    'xmlrpc.xmlrpc_remove_rsd_link',
                    'xmlrpc.xmlrpc_remove_wlwmanifest_link',
                    'xmlrpc.remove_xmlrpc_pingback_link',
                ],
                'show' => [
                    'xmlrpc.xmlrpc_whitelist_jetpack_ips',
                    'xmlrpc.custom_xmlrpc_whitelist_ips',
                ],
            ],
            'selective'  => [
                'show' => [
                    'xmlrpc.xmlrpc_whitelist_jetpack_ips',
                    'xmlrpc.custom_xmlrpc_whitelist_ips',
                    'xmlrpc.xmlrpc_methods',
                    'xmlrpc.custom_xmlrpc_methods',
                    'xmlrpc.disable_xmlrpc_headers',
                    'xmlrpc.custom_xmlrpc_headers',
                    'xmlrpc.xmlrpc_remove_rsd_link',
                    'xmlrpc.xmlrpc_remove_wlwmanifest_link',
                    'xmlrpc.remove_xmlrpc_pingback_link',
                ],
            ],
        ],
    ],

    'xmlrpc.xmlrpc_whitelist_jetpack_ips'   => [
        'type'     => 'checkbox',
        'tab'      => 'xmlrpc',
        'priority' => 1001,
        'section'  => 'xmlrpc',
        'label'    => 'Allowlist Jetpack IPs',
    ],

    'xmlrpc.custom_xmlrpc_whitelist_ips'    => [
        'type'        => 'textarea',
        'tab'         => 'xmlrpc',
        'priority'    => 1002,
        'section'     => 'xmlrpc',
        'label'       => 'Allowlist additional IPs',
        'description' => static fn() => esc_html__( 'Separate multiple IP\'s with line breaks.', 'hbp-disabler' ),
    ],

    'xmlrpc.xmlrpc_methods'                 => [
        'type'     => 'multicheckbox',
        'tab'      => 'xmlrpc',
        'priority' => 1003,
        'section'  => 'xmlrpc',
        'label'    => 'XML-RPC methods',
        'choices'  => [
            'wp.getUsersBlogs'                 => 'wp.getUsersBlogs',
            'wp.getUsers'                      => 'wp.getUsers',
            'pingback.ping'                    => 'pingback.ping',
            'pingback.extensions.getPingbacks' => 'pingback.extensions.getPingbacks',
            'system.getCapabilities'           => 'system.getCapabilities',
            'system.listMethods'               => 'system.listMethods',
            'system.multicall'                 => 'system.multicall',
        ],
    ],

    'xmlrpc.custom_xmlrpc_methods'          => [
        'type'        => 'textarea',
        'tab'         => 'xmlrpc',
        'priority'    => 1004,
        'section'     => 'xmlrpc',
        'label'       => 'Additional XML-RPC methods',
        'description' => static fn() => esc_html__( 'Separate multiple methods with line breaks.', 'hbp-disabler' ),
    ],

    'xmlrpc.disable_xmlrpc_headers'         => [
        'type'        => 'multicheckbox',
        'tab'         => 'xmlrpc',
        'priority'    => 1005,
        'section'     => 'xmlrpc',
        'label'       => 'XML-RPC HTTP headers',
        'after_field' => '<ul><li><code>X-Pingback: https://www.example.com/xmlrpc.php</code></li></ul>',
        'choices'     => [
            'X-Pingback' => 'X-Pingback',
        ],
    ],

    'xmlrpc.custom_xmlrpc_headers'          => [
        'type'        => 'textarea',
        'tab'         => 'xmlrpc',
        'priority'    => 1006,
        'section'     => 'xmlrpc',
        'label'       => 'Additional XML-RPC headers',
        'description' => static fn() => esc_html__( 'Separate multiple headers with line breaks.', 'hbp-disabler' ),
    ],

    'xmlrpc.xmlrpc_remove_rsd_link'         => [
        'type'        => 'checkbox',
        'tab'         => 'xmlrpc',
        'priority'    => 1007,
        'section'     => 'xmlrpc',
        'label'       => 'Remove RSD link',
        'after_field' => static fn() => '<code>' . esc_html( '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://www.example.com/xmlrpc.php?rsd" />' ) . '</code>',
    ],

    'xmlrpc.xmlrpc_remove_wlwmanifest_link' => [
        'type'        => 'checkbox',
        'tab'         => 'xmlrpc',
        'priority'    => 1008,
        'section'     => 'xmlrpc',
        'label'       => 'Remove WLW manifest link',
        'after_field' => static fn() => '<code>' . esc_html( '<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://www.example.com/wp-includes/wlwmanifest.xml" />' ) . '</code>',
    ],

    'xmlrpc.remove_xmlrpc_pingback_link'    => [
        'type'        => 'checkbox',
        'tab'         => 'xmlrpc',
        'priority'    => 1009,
        'section'     => 'xmlrpc',
        'label'       => 'Remove pingback XML-RPC link',
        'after_field' => static fn() => '<code>' . esc_html( '<link rel="pingback" href="https://www.example.com/xmlrpc.php" />' ) . '</code>',
    ],
];
