<?php

return [
    'id'       => 'admin_bar',
    'title'    => esc_html__( 'Admin Bar', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
