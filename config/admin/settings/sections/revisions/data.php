<?php

return [
    'id'       => 'revisions',
    'title'    => esc_html__( 'Revisions', 'hbp-disabler' ),
    'callback' => static fn() => printf(
        /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
        esc_html__( '%1$s If a post type isn\'t listed, revisions are not enabled for that post type. %2$s', 'hbp-disabler' ),
        '<p class="description">',
        '</p>'
    ),
    'page'     => 'settings_page_hbp-disabler-settings',
];
