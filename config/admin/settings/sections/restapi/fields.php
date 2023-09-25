<?php

return [
    'disable_rest_api_for_visitors'    => [
        'id'          => 'disable_rest_api_for_visitors',
        'title'       => esc_html__( 'Disable REST API for visitors', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'setting_key' => 'restapi_disable_rest_api_for_visitors',
    ],
    'disable_rest_api_links'           => [
        'id'          => 'disable_rest_api_links',
        'title'       => esc_html__( 'Disable REST API links', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'after_field' => '<code>' . esc_html( '<link rel="https://api.w.org/" href="https://www.example.com/wp-json/" />' ) . '</code>',
        'setting_key' => 'restapi_disable_rest_api_links',
    ],
    'disable_rest_api_rsd_link'        => [
        'id'          => 'disable_rest_api_rsd_link',
        'title'       => esc_html__( 'Disable Rest API RSD link', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'after_field' => '<code>' . esc_html( '<api name="WP-API" blogID="1" preferred="false" apiLink="https://www.example.com/wp-json/" />' ) . '</code>',
        'setting_key' => 'restapi_disable_rest_api_rsd_link',
    ],
    'disable_rest_api_link_in_headers' => [
        'id'          => 'disable_rest_api_link_in_headers',
        'title'       => esc_html__( 'Disable REST API link in HTTP headers', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'restapi',
        'after_field' => '<code>' . esc_html( 'Link: <https://example.com/wp-json/>; rel="https://api.w.org/"' ) . '</code>',
        'setting_key' => 'restapi_disable_rest_api_link_in_headers',
    ],
];
