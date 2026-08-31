<?php

/**
 * Section headings and intro notes.
 *
 * Read as `disabler.sections.{slug}`. A section may declare a plain string, in
 * which case it is the heading, or an array carrying a `label` and a
 * `description`. The description prints above that section's controls.
 *
 * Only the sections that need a note are here. An undeclared section gets no
 * heading, which is what we want: one section per tab is the common case, and
 * a heading there would just repeat the tab the reader has already clicked.
 *
 * `label` is left off deliberately for the same reason -- these all sit alone
 * on their tab, so the note is the only thing worth printing.
 *
 * Closures throughout, so no translation runs before `init`.
 */

return [

    'feeds'     => [
        'description' => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$sWordPress outputs your content in many different formats, across many different URLs (like RSS feeds of your posts and categories). It’s generally good practice to disable the formats you’re not actively using.%2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'privacy'   => [
        'description' => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$s These settings help obfuscate information about your blog to the world (including WordPress.org). While they don\'t protect you from anything, they do make it a little harder for people to obtain information about you and your site. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'revisions' => [
        'description' => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$s If a post type isn\'t listed, revisions are not enabled for that post type. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'tracking'  => [
        'description' => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$s This setting enables anonymous usage data collection for the plugin, including WordPress information, installed plugins/themes, and server details. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'xmlrpc'    => [
        'description' => static fn() => sprintf(
            /* Translators: %1$s opening paragraph tag, %2$s closing paragraph tag, %3$s opening code tag, %4$s closing code tag. */
            esc_html__( '%1$s If you select %3$sCompletely%4$s, than you don\'t need to select any other settings below, leave them as is, otherwise you can make adjustments as per your needs. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>',
            '<code>',
            '</code>'
        ),
    ],
];
