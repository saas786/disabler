<?php

return [
    'id'       => 'frontend',
    'title'    => static fn() => esc_html__( 'Frontend', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
