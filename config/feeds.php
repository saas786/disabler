<?php

/**
 * Feeds defaults.
 *
 * The config tier of the resolution order: what a setting resolves to when
 * nothing is stored and no preset overrides it. Carried over from the old
 * flat defaults file, split by section and stripped of its section prefix.
 */

return [
    'disable_atom_rdf_feeds'         => 0,
    'disable_feed_authors'           => 0,
    'disable_feed_categories'        => 0,
    'disable_feed_custom_taxonomies' => 0,
    'disable_feed_global'            => 0,
    'disable_feed_global_comments'   => 0,
    'disable_feed_post_comments'     => 0,
    'disable_feed_post_types'        => 0,
    'disable_feed_search'            => 0,
    'disable_feed_tags'              => 0,
    'rss_feed_redirect'              => 'redirect',
];
