<?php

return [
    'id'       => 'performance',
    'title'    => static fn() => esc_html__( 'Performance', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
