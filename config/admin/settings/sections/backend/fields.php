<?php

return [
    'disable_self_ping'      => [
        'id'          => 'disable_self_ping',
        'title'       => esc_html__( 'Disable self pings', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'backend',
        'after_field' => esc_html__( '(i.e. trackbacks/pings from your own domain).', 'hbp-disabler' ),
        'setting_key' => 'backend_disable_self_ping',
        'events'      => [
            'true'  => [
                'show' => '.backend_disable_self_ping_urls-wrap',
            ],
            'false' => [
                'hide' => '.backend_disable_self_ping_urls-wrap',
            ],
        ],
    ],
    'disable_self_ping_urls' => [
        'id'              => 'disable_self_ping_urls',
        'title'           => esc_html__( 'Additional urls', 'hbp-disabler' ),
        'type'            => 'textarea', // callback
        'page'            => 'settings_page_hbp-disabler-settings',
        'section'         => 'backend',
        // 'after_field' => esc_html__( '(i.e. trackbacks/pings from your own domain).', 'hbp-disabler' ),
        'setting_key'     => 'backend_disable_self_ping_urls',
        'container-class' => 'backend_disable_self_ping_urls-wrap',
        /* translators: %s: Ping-back URL of website */
        'description'     => sprintf( esc_html__( 'By default, No Self Pings will exclude pings for this site (%s) but you can supply additional URLs below. Separate multiple URLs with line breaks.', 'hbp-disabler' ), esc_url( home_url() ) ),
    ],
    'disable_autosave'       => [
        'id'          => 'disable_autosave',
        'title'       => esc_html__( 'Disable auto-saving', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'backend',
        'after_field' => esc_html__( 'It will disable autosave feature for posts etc.', 'hbp-disabler' ),
        'setting_key' => 'backend_disable_autosave',
    ],
];
