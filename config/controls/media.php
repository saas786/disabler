<?php

/**
 * Media controls.
 *
 * Loaded as `disabler.controls.media`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [
    'media.disable_wp_img_auto_sizes_contain' => [
        'type'        => 'checkbox',
        'tab'         => 'media',
        'priority'    => 500,
        'section'     => 'media',
        'label'       => static fn() => esc_html__( 'Disable image sizing CSS containment', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf(
            /* Translators: %s is a placeholder for a link to a relevant code on GitHub. */
            esc_html__( 'Removes CSS containment rule applied to lazy-loaded images. %s', 'hbp-disabler' ),
            '<a href="https://github.com/WordPress/WordPress/blob/7.0/wp-includes/media.php#L2095" target="_blank">See</a>'
        ),
    ],

    'media.disable_wp_img_tag_add_auto_sizes' => [
        'type'        => 'checkbox',
        'tab'         => 'media',
        'priority'    => 501,
        'section'     => 'media',
        'label'       => static fn() => esc_html__( 'Disable adding \'auto\' to image sizes attribute', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf(
            /* Translators: %s is a placeholder for a link to a relevant code on GitHub. */
            esc_html__( 'Prevents WordPress from automatically adding \'auto\' sizing to lazy-loaded images. Automatically disables CSS containment as well. %s', 'hbp-disabler' ),
            '<a href="https://github.com/WordPress/WordPress/blob/7.0/wp-includes/media.php#L2016" target="_blank">See</a>'
        ),
    ],

    'media.disable_core_lazy_loading'         => [
        'type'        => 'radio',
        'tab'         => 'media',
        'priority'    => 502,
        'section'     => 'media',
        'label'       => static fn() => esc_html__( 'Disable core lazy loading', 'hbp-disabler' ),
        'description' => static fn() => sprintf(
            /* Translators: %1$s and %2$s are placeholders for links to relevant hooks on developer.wordpress.org. */
            esc_html__( 'Disables WordPress\' native lazy loading (%1$s) for images and iframes, which can conflict with lazy-load logic added by themes or other plugins. \'Force eager loading\' keeps the attribute but sets it to \'eager\' instead of removing it entirely, for elements where lazy loading causes layout shift or LCP issues (%2$s).', 'hbp-disabler' ),
            '<a href="https://developer.wordpress.org/reference/hooks/wp_lazy_loading_enabled/" target="_blank">wp_lazy_loading_enabled</a>',
            '<a href="https://developer.wordpress.org/reference/hooks/wp_img_tag_add_loading_attr/" target="_blank">wp_img_tag_add_loading_attr</a>'
        ),
        'choices'     => static fn() => [
            'no'    => esc_html__( 'No', 'hbp-disabler' ),
            'yes'   => esc_html__( 'Yes', 'hbp-disabler' ),
            'eager' => esc_html__( 'Force eager loading', 'hbp-disabler' ),
        ],
    ],
];
