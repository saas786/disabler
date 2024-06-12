<?php

return [
    'disable_shortlinks' => [
        'id'          => 'disable_shortlinks',
        'title'       => esc_html__( 'Disable shortlinks', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'frontend',
        /* Translators: %s is replaced with the example shortlink URL. */
        'after_field' => sprintf( esc_html__( 'Prevents links to WordPress\' internal \'shortlink\' URLs for your posts. For example, %1$s', 'hbp-disabler' ), '<code>' . esc_html( '<link rel="shortlink" href="https://www.example.com/?p=1" />' ) . '</code>' ),
        'setting_key' => 'frontend_disable_shortlinks',
    ],
];
