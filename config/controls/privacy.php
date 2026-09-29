<?php

/**
 * Privacy controls.
 *
 * Loaded as `disabler.controls.privacy`. The outer key groups the file;
 * the inner keys are the setting keys.
 *
 * Event targets name control keys. The row class each control carries is
 * derived from the same key, so the two cannot drift apart.
 */

return [

    // The tab's opening note. An `html` control like any other, first in
    // priority order, rather than anything the section itself carries: the
    // screen renders controls, and a note is just a control that stores
    // nothing.
    'privacy.note'                  => [
        'type'     => 'html',
        'tab'      => 'privacy',
        'priority' => 899,
        'section'  => 'privacy',
        'label'    => static fn() => esc_html__( 'Note', 'hbp-disabler' ),
        'content'  => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$s These settings help obfuscate information about your blog to the world (including WordPress.org). While they don\'t protect you from anything, they do make it a little harder for people to obtain information about you and your site. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'privacy.disable_wp_generator'  => [
        'type'        => 'checkbox',
        'tab'         => 'privacy',
        'priority'    => 900,
        'section'     => 'privacy',
        'label'       => static fn() => esc_html__( 'Disable WordPress generator tag', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes the meta tag <code>' . esc_html( '<meta name="generator" content="WordPress %1$s">' ) . '</code>', esc_attr( get_bloginfo( 'version' ) ) ),
    ],

    'privacy.fake_user_agent_value' => [
        'type'        => 'checkbox',
        'tab'         => 'privacy',
        'priority'    => 901,
        'section'     => 'privacy',
        'label'       => static fn() => esc_html__( 'Send a generic user agent', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'Prevents WordPress from sending your URL information when checking for updates.', 'hbp-disabler' ),
    ],
];
