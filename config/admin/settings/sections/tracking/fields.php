<?php

return [
    'allow_usage_tracking' => [
        'id'          => 'allow_usage_tracking',
        'title'       => esc_html__( 'Allow usage tracking', 'hbp-disabler' ),
        'type'        => 'checkbox', // callback
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'tracking',
        'after_field' => esc_html__( 'It will allows us to collect data about our plugin usage.', 'hbp-disabler' ),
        'setting_key' => 'tracking_allow_usage_tracking',
    ],
];
