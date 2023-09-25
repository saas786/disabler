<?php

return [
    'id'       => 'privacy',
    'title'    => esc_html__( 'Privacy', 'hbp-disabler' ),
    'callback' => static fn() => printf(
        /* translators: %s Section Description. %s */
        esc_html__( '%1$s These settings help obfuscate information about your blog to the world (including to Wordpress.org). While they don\'t protect you from anything, they do make it a little harder for people to get information about you and your site. %2$s', 'hbp-disabler' ),
        '<p class="description">',
        '</p>'
    ),
    'page'     => 'settings_page_hbp-disabler-settings',
];
