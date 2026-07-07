<?php

return [
    'id'       => 'media',
    'title'    => static fn() => esc_html__( 'Media', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
