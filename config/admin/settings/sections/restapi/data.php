<?php

return [
    'id'       => 'restapi',
    'title'    => static fn() => esc_html__( 'Rest API', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
