<?php

return [
    'id'       => 'privacy',
    'title'    => esc_html__( 'Privacy', 'hbp-disabler' ),
    'callback' => static fn() => printf(
        /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
        esc_html__( '%1$s These settings help obfuscate information about your blog to the world (including WordPress.org). While they don\'t protect you from anything, they do make it a little harder for people to obtain information about you and your site. %2$s', 'hbp-disabler' ),
        '<p class="description">',
        '</p>'
    ),
    'page'     => 'settings_page_hbp-disabler-settings',
];
