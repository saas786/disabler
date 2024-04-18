<?php

return [
    'disable_texturization' => [
        'id'          => 'disable_texturization',
        'title'       => esc_html__( 'Disable Texturization (Classic Editor)', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'frontend',
        'after_field' => esc_html__( 'Disables smart quotes (a.k.a. curly quotes), em dash, en dash, and ellipsis.', 'hbp-disabler' ),
        'setting_key' => 'frontend_disable_texturization',
    ],
    'disable_capital_p'     => [
        'id'          => 'disable_capital_p',
        'title'       => esc_html__( 'Disable Capital P (Classic Editor)', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'frontend',
        'after_field' => esc_html__( 'Disables auto-correction of WordPress capitalization.', 'hbp-disabler' ),
        'setting_key' => 'frontend_disable_capital_p',
    ],
    'disable_autop'         => [
        'id'          => 'disable_autop',
        'title'       => esc_html__( 'Disable paragraphs (Classic Editor)', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'frontend',
        'after_field' => esc_html__( 'Prevents <p> tags from being automatically inserted in your posts.', 'hbp-disabler' ),
        'setting_key' => 'frontend_disable_autop',
    ],
    'disable_shortlinks'    => [
        'id'          => 'disable_shortlinks',
        'title'       => esc_html__( 'Disable shortlinks', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'frontend',
        /* Translators: %s is replaced with the example shortlink URL. */
        'after_field' => sprintf( esc_html__( 'Prevents links to WordPress\' internal \'shortlink\' URLs for your posts. For example, %1$s', 'hbp-disabler' ), '<code>' . esc_html( '<link rel="shortlink" href="https://www.example.com/?p=1" />' ) . '</code>' ),
        'setting_key' => 'frontend_disable_shortlinks',
    ],
];
