<?php

return [
    'id'       => 'editor',
    'title'    => static fn() => esc_html__( 'Editor', 'hbp-disabler' ),
    'callback' => static fn() => '',
    'page'     => 'settings_page_hbp-disabler-settings',
];
