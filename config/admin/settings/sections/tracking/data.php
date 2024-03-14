<?php

return [
    'id'       => 'tracking',
    'title'    => esc_html__( 'Usage Tracking', 'hbp-disabler' ),
    'callback' => static fn() => printf(
        /* translators: %s Section Description. %s */
        esc_html__( '%1$s This setting enables anonymous usage data collection for the plugin, including WordPress information, installed plugins/themes, and server details. %2$s', 'hbp-disabler' ),
        '<p class="description">',
        '</p>'
    ),
    'page'     => 'settings_page_hbp-disabler-settings',
];
