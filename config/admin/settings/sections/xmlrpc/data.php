<?php

return [
    'id'       => 'xmlrpc',
    'title'    => esc_html__( 'XML-RPC', 'hbp-disabler' ),
    'callback' => static fn() => printf(
        /* translators: %s Section Description. %s */
        esc_html__( '%1$s If you select %3$sCompletely%4$s, than you don\'t need to select any other settings below, leave them as is, otherwise you can make adjustments as per your needs. %2$s', 'hbp-disabler' ),
        '<p class="description">',
        '</p>',
        '<code>',
        '</code>'
    ),
    'page'     => 'settings_page_hbp-disabler-settings',
];
