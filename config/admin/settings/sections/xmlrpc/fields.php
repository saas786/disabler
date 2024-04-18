<?php

return [
    'disable_xmlrpc' => [
        'id'          => 'disable_xmlrpc',
        'title'       => esc_html__( 'Disable XML-RPC', 'hbp-disabler' ),
        'type'        => 'radio',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'xmlrpc',
        'setting_key' => 'xmlrpc_disable_xmlrpc',
        'value'       => 'no',
        'choices'     => [
            'no'         => 'No',
            'completely' => 'Completely',
            'selective'  => 'Selective',
        ],
        'events'      => [
            'no'         => [
                'hide' => [
                    '.xmlrpc_xmlrpc_whitelist_jetpack_ips-wrap',
                    '.xmlrpc_custom_xmlrpc_whitelist_ips-wrap',
                    '.xmlrpc_xmlrpc_methods-wrap',
                    '.xmlrpc_custom_xmlrpc_methods-wrap',
                    '.xmlrpc_disable_xmlrpc_headers-wrap',
                    '.xmlrpc_custom_xmlrpc_headers-wrap',
                    '.xmlrpc_xmlrpc_remove_rsd_link-wrap',
                    '.xmlrpc_xmlrpc_remove_wlwmanifest_link-wrap',
                    '.xmlrpc_remove_xmlrpc_pingback_link-wrap',
                ],
            ],
            'completely' => [
                'hide' => [
                    '.xmlrpc_xmlrpc_methods-wrap',
                    '.xmlrpc_custom_xmlrpc_methods-wrap',
                    '.xmlrpc_disable_xmlrpc_headers-wrap',
                    '.xmlrpc_custom_xmlrpc_headers-wrap',
                    '.xmlrpc_xmlrpc_remove_rsd_link-wrap',
                    '.xmlrpc_xmlrpc_remove_wlwmanifest_link-wrap',
                    '.xmlrpc_remove_xmlrpc_pingback_link-wrap',
                ],
                'show' => [
                    '.xmlrpc_xmlrpc_whitelist_jetpack_ips-wrap',
                    '.xmlrpc_custom_xmlrpc_whitelist_ips-wrap',
                ],
            ],
            'selective'  => [
                'show' => [
                    '.xmlrpc_xmlrpc_whitelist_jetpack_ips-wrap',
                    '.xmlrpc_custom_xmlrpc_whitelist_ips-wrap',
                    '.xmlrpc_xmlrpc_methods-wrap',
                    '.xmlrpc_custom_xmlrpc_methods-wrap',
                    '.xmlrpc_disable_xmlrpc_headers-wrap',
                    '.xmlrpc_custom_xmlrpc_headers-wrap',
                    '.xmlrpc_xmlrpc_remove_rsd_link-wrap',
                    '.xmlrpc_xmlrpc_remove_wlwmanifest_link-wrap',
                    '.xmlrpc_remove_xmlrpc_pingback_link-wrap',
                ],
            ],
        ],
    ],
    'xmlrpc'         => [
        'type'   => 'group',
        'fields' => static function () {
            $fields = [];

            $fields['xmlrpc_whitelist_jetpack_ips'] = [
                'id'              => 'xmlrpc_whitelist_jetpack_ips',
                'title'           => 'Whitelist jetpack IP\'s',
                'type'            => 'checkbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_xmlrpc_whitelist_jetpack_ips',
                'container-class' => 'xmlrpc_xmlrpc_whitelist_jetpack_ips-wrap',
            ];

            $fields['custom_xmlrpc_whitelist_ips'] = [
                'id'              => 'custom_xmlrpc_whitelist_ips',
                'title'           => 'Whitelist additional IP\'s',
                'type'            => 'textarea',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_custom_xmlrpc_whitelist_ips',
                /* Translators: %s XML-RPC custom whitelist IP's. */
                'description'     => esc_html__( 'Separate multiple IP\'s with line breaks.', 'hbp-disabler' ),
                'container-class' => 'xmlrpc_custom_xmlrpc_whitelist_ips-wrap',
            ];

            // WP core xml methods.
            $methods = [
                // @see https://github.com/WordPress/WordPress/blob/cc0246c154bc090bc95849f27a2747b3e524f116/wp-includes/class-wp-xmlrpc-server.php#L70
                // WordPress API.
                'wp.getUsersBlogs'                 => 'wp.getUsersBlogs',
                'wp.getUsers'                      => 'wp.getUsers',

                // Pingback.
                'pingback.ping'                    => 'pingback.ping',
                'pingback.extensions.getPingbacks' => 'pingback.extensions.getPingbacks',

                // 'demo.sayHello'                    => 'demo.sayHello',
                // 'demo.addTwoNumbers'               => 'demo.addTwoNumbers',

                // @see https://github.com/WordPress/WordPress/blob/cc0246c154bc090bc95849f27a2747b3e524f116/wp-includes/IXR/class-IXR-server.php#L183
                'system.getCapabilities'           => 'system.getCapabilities',
                'system.listMethods'               => 'system.listMethods',
                'system.multicall'                 => 'system.multicall',
            ];

            $fields['xmlrpc_methods'] = [
                'id'              => 'xmlrpc_methods',
                'title'           => 'XML RPC Methods',
                'type'            => 'multiCheckbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_xmlrpc_methods',
                'choices'         => $methods,
                'container-class' => 'xmlrpc_xmlrpc_methods-wrap',
            ];

            $fields['custom_xmlrpc_methods'] = [
                'id'              => 'custom_xmlrpc_methods',
                'title'           => 'Additional XML RPC Methods',
                'type'            => 'textarea',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_custom_xmlrpc_methods',
                /* Translators: %s XML-RPC custom methods. */
                'description'     => esc_html__( 'Separate multiple methods with line breaks.', 'hbp-disabler' ),
                'container-class' => 'xmlrpc_custom_xmlrpc_methods-wrap',
            ];

            $headers = [
                'X-Pingback' => 'X-Pingback',
            ];

            $headers_code = '';

            array_walk( $headers, static function ( $header ) use ( &$headers_code ) {
                $headers_code .= sprintf( '<li><code>%s: https://www.example.com/xmlrpc.php</code></li>', $header );
            } );

            $after_field = '<ul>' . $headers_code . '</ul>';

            $fields['disable_xmlrpc_headers'] = [
                'id'              => 'disable_xmlrpc_headers',
                'title'           => 'XML-RPC HTTP header(s)',
                'type'            => 'multiCheckbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_disable_xmlrpc_headers',
                'choices'         => $headers,
                'after_field'     => $after_field,
                'container-class' => 'xmlrpc_disable_xmlrpc_headers-wrap',
            ];

            $fields['custom_xmlrpc_headers'] = [
                'id'              => 'custom_xmlrpc_headers',
                'title'           => 'Additional XML RPC Headers',
                'type'            => 'textarea',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_custom_xmlrpc_headers',
                /* Translators: %s XML-RPC custom headers. */
                'description'     => esc_html__( 'Separate multiple headers with line breaks.', 'hbp-disabler' ),
                'container-class' => 'xmlrpc_custom_xmlrpc_headers-wrap',
            ];

            $fields['xmlrpc_remove_rsd_link'] = [
                'id'              => 'xmlrpc_remove_rsd_link',
                'title'           => 'Remove RSD link',
                'type'            => 'checkbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_xmlrpc_remove_rsd_link',
                'after_field'     => '<code>' . esc_html( '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://www.example.com/xmlrpc.php?rsd" />' ) . '</code>',
                'container-class' => 'xmlrpc_xmlrpc_remove_rsd_link-wrap',
            ];

            $fields['xmlrpc_remove_wlwmanifest_link'] = [
                'id'              => 'xmlrpc_remove_wlwmanifest_link',
                'title'           => 'Remove WLW Manifest link',
                'type'            => 'checkbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_xmlrpc_remove_wlwmanifest_link',
                'after_field'     => '<code>' . esc_html( '<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://www.example.com/wp-includes/wlwmanifest.xml" />' ) . '</code>',
                'container-class' => 'xmlrpc_xmlrpc_remove_wlwmanifest_link-wrap',
            ];

            $fields['remove_xmlrpc_pingback_link'] = [
                'id'              => 'remove_xmlrpc_pingback_link',
                'title'           => 'Remove the pingback XML-RPC link',
                'type'            => 'checkbox',
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'xmlrpc',
                'setting_key'     => 'xmlrpc_remove_xmlrpc_pingback_link',
                'after_field'     => '<code>' . esc_html( '<link rel="pingback" href="https://www.example.com/xmlrpc.php" />' ) . '</code>',
                'container-class' => 'xmlrpc_remove_xmlrpc_pingback_link-wrap',
            ];

            return $fields;
        },
    ],
];
