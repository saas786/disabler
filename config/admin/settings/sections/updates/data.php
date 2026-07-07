<?php

return [
    'id'       => 'updates',
    'title'    => static fn() => esc_html__( 'Updates', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
