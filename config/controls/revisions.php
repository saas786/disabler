<?php

/**
 * Revisions controls.
 *
 * The interesting one. The old screen used a `group` field whose `fields` key
 * was a callable, because the limit inputs are one per registered post type
 * and that set is not known at config-load time.
 *
 * There is no group type any more. A declaration may itself be a closure
 * returning a map of declarations, so the computed set is just controls --
 * which means storage, sanitizing and preset visibility work on them exactly
 * as they do on a hand-written control.
 */

use function HBP\Disabler\get_revision_post_types;

return [

    // The tab's opening note. An `html` control like any other, first in
    // priority order, rather than anything the section itself carries: the
    // screen renders controls, and a note is just a control that stores
    // nothing.
    'revisions.note'              => [
        'type'     => 'html',
        'tab'      => 'revisions',
        'priority' => 599,
        'section'  => 'revisions',
        'label'    => static fn() => esc_html__( 'Note', 'hbp-disabler' ),
        'content'  => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$s If a post type isn\'t listed, revisions are not enabled for that post type. %2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'revisions.disable_revisions' => [
        'type'         => 'multiselect',
        'tab'          => 'revisions',
        'priority'     => 600,
        'section'      => 'revisions',
        'label'        => static fn() => esc_html__( 'Disable revisions', 'hbp-disabler' ),
        /* Translators: %1$s will be replaced with the opening <p> tag, %2$s will be replaced with closing tags and a line break. */
        'before_field' => static fn() => sprintf( esc_html__( '%1$s To select multiple post types, hold ctrl key while selecting. Do not select a post type if you are unsure. %2$s', 'hbp-disabler' ), '<p>', '</p><br/>' ),
        'choices'      => static function () {
            $choices = [
                'no'  => esc_html__( 'No', 'hbp-disabler' ),
                'all' => esc_html__( 'All', 'hbp-disabler' ),
            ];

            return array_merge( $choices, get_revision_post_types() );
        },
        'events'       => static function () {
            // Setting it initially, to keep them on top of other items.
            $events = [
                'no'  => [],
                'all' => [],
            ];

            // Targets are control keys. Panel turns each into the class that
            // control actually carries, so nothing here has to know or repeat
            // a class name.
            $targets = [ 'revisions.limit_description' ];

            foreach ( array_keys( get_revision_post_types() ) as $type ) {
                $targets[] = "revisions.revisions_limit_{$type}";
            }

            foreach ( array_keys( get_revision_post_types() ) as $type ) {
                $events[ $type ] = [
                    'show' => $targets,
                    'hide' => "revisions.revisions_limit_{$type}",
                ];
            }

            $events['no']  = [ 'show' => $targets ];
            $events['all'] = [ 'hide' => $targets ];

            return $events;
        },
    ],

    // One limit input per registered post type, computed on read.
    'revisions.limits'            => static function (): array {
        $controls = [
            'revisions.limit_description' => [
                'type'     => 'html',
                'tab'      => 'revisions',
                'priority' => 601,
                'section'  => 'revisions',
                'label'    => static fn() => esc_html__( 'Note', 'hbp-disabler' ),
                'content'  => static fn() => sprintf(
                    /* Translators: %1$s opening paragraph tag, %2$s closing paragraph tag. */
                    esc_html__( '%1$s Set the revisions limit for each selected post type. Leave the field empty for default behavior. %2$s', 'hbp-disabler' ),
                    '<p class="description">',
                    '</p>'
                ),
            ],
        ];

        foreach ( get_revision_post_types() as $type => $name ) {
            $controls[ "revisions.revisions_limit_{$type}" ] = [
                'type'     => 'text',
                'tab'      => 'revisions',
                'priority' => 602,
                'section'  => 'revisions',
                'label'    => $name,
                'class'    => 'small-text',
            ];
        }

        return $controls;
    },
];
