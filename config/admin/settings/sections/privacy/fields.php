<?php

return [
    'disable_wp_generator'  => [
        'id'          => 'disable_wp_generator',
        'title'       => static fn() => esc_html__( 'Disable WordPress generator tag', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'privacy',
        'after_field' => static fn() => sprintf( 'Removes the meta tag <code>' . esc_html( '<meta name="generator" content="WordPress %1$s">' ) . '</code>', esc_attr( get_bloginfo( 'version' ) ) ),
        'setting_key' => 'privacy_disable_wp_generator',
    ],
    'fake_user_agent_value' => [
        'id'          => 'fake_user_agent_value',
        'title'       => static fn() => esc_html__( 'Fake User Agent', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'privacy',
        'after_field' => static fn() => esc_html__( 'Prevents WordPress from sending your URL information when checking for updates.', 'hbp-disabler' ),
        'setting_key' => 'privacy_fake_user_agent_value',
    ],
];
