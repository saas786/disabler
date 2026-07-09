<?php

return [
    'id'       => 'backend',
    'title'    => static fn() => esc_html__( 'Backend', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
