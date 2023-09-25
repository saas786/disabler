<?php

return [
    'id'       => 'feeds',
    'title'    => esc_html__( 'Feeds', 'hbp-disabler' ),
    'callback' => static fn() => printf(
        /* translators: %s Section Description. %s */
        esc_html__( '%1$sWordPress outputs your content in many different formats, across many different URLs (like RSS feeds of your posts and categories). It’s generally good practice to disable the formats you’re not actively using.%2$s', 'hbp-disabler' ),
        '<p class="description">',
        '</p>'
    ),
    'page'     => 'settings_page_hbp-disabler-settings',
];
