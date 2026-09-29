<?php

/**
 * Feeds controls.
 *
 * Loaded as `disabler.controls.feeds`. The outer key groups the file;
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
    'feeds.note'                           => [
        'type'     => 'html',
        'tab'      => 'feeds',
        'priority' => 699,
        'section'  => 'feeds',
        'label'    => static fn() => esc_html__( 'Note', 'hbp-disabler' ),
        'content'  => static fn() => sprintf(
            /* Translators: %1$s will be replaced with the opening paragraph tag, %2$s will be replaced with the closing paragraph tag. */
            esc_html__( '%1$sWordPress outputs your content in many different formats, across many different URLs (like RSS feeds of your posts and categories). It\u2019s generally good practice to disable the formats you\u2019re not actively using.%2$s', 'hbp-disabler' ),
            '<p class="description">',
            '</p>'
        ),
    ],

    'feeds.rss_feed_redirect'              => [
        'type'     => 'select',
        'tab'      => 'feeds',
        'priority' => 700,
        'section'  => 'feeds',
        'label'    => static fn() => esc_html__( 'Redirect feeds (if disabled)', 'hbp-disabler' ),
        'choices'  => static fn() => [
            'redirect' => esc_html__( 'Redirect to homepage', 'hbp-disabler' ),
            '404'      => esc_html__( '404', 'hbp-disabler' ),
        ],
    ],

    'feeds.disable_feed_global'            => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 701,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable global feed', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing an overview of recent posts. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Feed" href="https://www.example.com/feed/" />' ) . '</code>' ),
    ],

    'feeds.disable_feed_global_comments'   => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 702,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable global comment feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( ' Removes URLs providing an overview of recent comments. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Comments Feed" href="https://www.example.com/comments/feed/" />' ) . '</code>' ),
        'description' => static fn() => esc_html__( 'Also disables post comment feeds.', 'hbp-disabler' ),
    ],

    'feeds.disable_feed_post_comments'     => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 703,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable post comments feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing recent comments on each post. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Example post Comments Feed" href="https://www.example.com/example-post/feed/" />' ) . '</code>' ),
    ],

    'feeds.disable_feed_authors'           => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 704,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable post authors feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing recent posts by specific authors. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Posts by Example Author Feed" href="https://www.example.com/author/example-author/feed/" />' ) . '</code>' ),
    ],

    'feeds.disable_feed_post_types'        => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 705,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable post type feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing recent posts for each post type. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Movies Feed" href="https://www.example.com/movies/feed/" />' ) . '</code>' ),
    ],

    'feeds.disable_feed_categories'        => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 706,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable category feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing recent posts for each category. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - News Category Feed" href="https://www.example.com/category/news/feed/" />' ) . '</code>' ),
    ],

    'feeds.disable_feed_tags'              => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 707,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable tag feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing recent posts for each tag. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Blue Tag Feed" href="https://www.example.com/tag/blue/feed/" />' ) . '</code>' ),
    ],

    'feeds.disable_feed_custom_taxonomies' => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 708,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable custom taxonomy feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing recent posts for each custom taxonomy. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Large size Feed" href="https://www.example.com/size/large/feed/" />' ) . '</code>' ),
    ],

    'feeds.disable_feed_search'            => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 709,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable search results feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs providing search result information. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Search Results for \'example\' Feed" href="https://www.example.com/search/example/feed/rss2/" />' ) . '</code>' ),
    ],

    'feeds.disable_atom_rdf_feeds'         => [
        'type'        => 'checkbox',
        'tab'         => 'feeds',
        'priority'    => 710,
        'section'     => 'feeds',
        'label'       => static fn() => esc_html__( 'Disable Atom / RDF feeds', 'hbp-disabler' ),
        'after_field' => static fn() => sprintf( 'Removes URLs that provide alternative (legacy) formats of the above. E.g., %1$s', '<br/><code>' . esc_html( '<link rel="alternate" type="application/rss+xml" title="Example Website - Feed" href="https://www.example.com/feed/atom/" />' ) . '</code>' ),
    ],
];
