<?php

use function HBP\Disabler\get_revision_post_types;

return [
    'disable_revisions' => [
        'id'           => 'disable_revisions',
        'title'        => esc_html__( 'Disable revisions', 'hbp-disabler' ),
        'type'         => 'select',
        'page'         => 'settings_page_hbp-disabler-settings',
        'section'      => 'revisions',
        /* Translators: %1$s will be replaced with the opening <p> tag, %2$s will be replaced with closing tags and a line break. */
        'before_field' => sprintf( esc_html__( '%1$s To select multiple post types, hold ctrl key while selecting. Do not select a post type if you are unsure. %2$s', 'hbp-disabler' ), '<p>', '</p><br/>' ),
        'setting_key'  => 'revisions_disable_revisions',
        'multiple'     => true,
        'choices'      => static function () {
            $choices = [
                'no'  => 'No',
                'all' => 'All',
            ];

            return array_merge( $choices, get_revision_post_types() );
        },
        'events'       => static function () {
            // Setting it initially,
            // to keep them on top of other items.
            $events = [
                'no'  => [],
                'all' => [],
            ];

            $all_selectors = [ '.revisions_limit_description-wrap' ];

            foreach ( get_revision_post_types() as $type => $name ) {
                $all_selectors[] = ".revisions_revisions_limit_$type-wrap";
            }

            foreach ( get_revision_post_types() as $type => $name ) {
                $events[ $type ] = [
                    'show' => $all_selectors,
                    'hide' => ".revisions_revisions_limit_$type-wrap",
                ];
            }

            $events['no'] = [ 'show' => $all_selectors ];

            $events['all'] = [ 'hide' => $all_selectors ];

            return $events;
        },
    ],
    'revisions'         => [
        'type'   => 'group',
        'fields' => static function () {
            $fields = [];

            $fields['revisions_limit_description'] = [
                'id'              => 'revisions_limit_description',
                'title'           => null,
                'type'            => 'html', // callback
                'page'            => 'settings_page_hbp-disabler-settings',
                'section'         => 'revisions',
                'setting_key'     => 'revisions_limit_description',
                'container-class' => 'revisions_limit_description-wrap',
                'callback'        => static fn() => printf(
                /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
                    esc_html__( '%1$s Set the revisions limit for each selected post type. Leave the field empty for default behavior. %2$s', 'hbp-disabler' ),
                    '<p class="description">',
                    '</p>'
                ),
            ];

            foreach ( get_revision_post_types() as $type => $name ) {
                $_post   = new WP_Post( (object) [ 'post_type' => $type ] );
                $to_keep = wp_revisions_to_keep( $_post );

                $value = -1 === $to_keep || '-1' === $to_keep ? '' : (int) $to_keep;

                $fields[ $type ] = [
                    'id'              => 'revisions_limit_' . $type,
                    'title'           => $name,
                    'type'            => 'text', // callback
                    'page'            => 'settings_page_hbp-disabler-settings',
                    'section'         => 'revisions',
                    'setting_key'     => 'revisions_revisions_limit_' . $type,
                    'class'           => 'small-text',
                    'container-class' => "revisions_revisions_limit_$type-wrap",
                    'value'           => $value,
                ];
            }

            return $fields;
        },
    ],
];
