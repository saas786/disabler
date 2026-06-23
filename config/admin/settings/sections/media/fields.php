<?php

return [
    'disable_wp_img_auto_sizes_contain' => [
        'id'          => 'disable_wp_img_auto_sizes_contain',
        'title'       => esc_html__( 'Disable image sizing CSS containment', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'media',
        'after_field' => sprintf(
            /* Translators: %s is a placeholder for a link to a relevant code on GitHub. */
            esc_html__( 'Removes CSS containment rule applied to lazy-loaded images. %s', 'hbp-disabler' ),
            '<a href="https://github.com/WordPress/WordPress/blob/7.0/wp-includes/media.php#L2095" target="_blank">See</a>'
        ),
        'setting_key' => 'editor_disable_wp_img_auto_sizes_contain',
    ],
    'disable_wp_img_tag_add_auto_sizes' => [
        'id'          => 'disable_wp_img_tag_add_auto_sizes',
        'title'       => esc_html__( 'Disable adding \'auto\' to image sizes attribute', 'hbp-disabler' ),
        'type'        => 'checkbox',
        'page'        => 'settings_page_hbp-disabler-settings',
        'section'     => 'media',
        'after_field' => sprintf(
        /* Translators: %s is a placeholder for a link to a relevant code on GitHub. */
            esc_html__( 'Prevents WordPress from automatically adding \'auto\' sizing to lazy-loaded images. Automatically disables CSS containment as well. %s', 'hbp-disabler' ),
            '<a href="https://github.com/WordPress/WordPress/blob/7.0/wp-includes/media.php#L2016" target="_blank">See</a>'
        ),
        'setting_key' => 'editor_disable_wp_img_tag_add_auto_sizes',
    ],

];
