<?php

return [

    // Editor.
    'editor_disable_autosave'                  => 'no',
    'editor_autosave_interval'                 => '',
    'editor_disable_classic_theme_styles'      => 0,
    'editor_disable_core_block_patterns'       => 0,
    'editor_disable_remote_block_patterns'     => 0,
    'editor_disable_texturization'             => 0,
    'editor_disable_capital_p'                 => 0,
    'editor_disable_autop'                     => 0,

    // Backend.
    'backend_disable_self_ping'                => 0,
    'backend_disable_self_ping_urls'           => '',

    // Frontend.
    'frontend_disable_shortlinks'              => 0,

    // Performance.
    'performance_disable_emojis'               => 0,
    'performance_disable_embeds'               => 0,
    'performance_disable_heartbeat'            => 'no',
    'performance_heartbeat_frequency'          => '',
    'performance_disable_widgets'              => 'no',

    // Media.
    'media_disable_wp_img_auto_sizes_contain'  => 0,
    'media_disable_core_lazy_loading'          => 'no',
    'media_disable_wp_img_tag_add_auto_sizes'  => 0,

    // Revisions.
    // Note: 'revisions_revisions_limit_{post_type}' is generated dynamically
    // per revision-enabled post type, so it can't be given a static default here.
    'revisions_disable_revisions'              => [ 'no' ],

    // Feeds.
    'feeds_rss_feed_redirect'                  => 'redirect',
    'feeds_disable_feed_global'                => 0,
    'feeds_disable_feed_global_comments'       => 0,
    'feeds_disable_feed_post_comments'         => 0,
    'feeds_disable_feed_authors'               => 0,
    'feeds_disable_feed_post_types'            => 0,
    'feeds_disable_feed_categories'            => 0,
    'feeds_disable_feed_tags'                  => 0,
    'feeds_disable_feed_custom_taxonomies'     => 0,
    'feeds_disable_feed_search'                => 0,
    'feeds_disable_atom_rdf_feeds'             => 0,

    // REST API.
    'restapi_disable_rest_api_for_visitors'    => 0,
    'restapi_disable_rest_api_links'           => 0,
    'restapi_disable_rest_api_rsd_link'        => 0,
    'restapi_disable_rest_api_link_in_headers' => 0,
    'restapi_disable_application_passwords'    => 'no',
    'restapi_application_passwords_roles'      => [],

    // Privacy.
    'privacy_disable_wp_generator'             => 0,
    'privacy_fake_user_agent_value'            => 0,

    // XML-RPC.
    'xmlrpc_disable_xmlrpc'                    => 'no',
    'xmlrpc_xmlrpc_whitelist_jetpack_ips'      => 0,
    'xmlrpc_custom_xmlrpc_whitelist_ips'       => '',
    'xmlrpc_xmlrpc_methods'                    => [],
    'xmlrpc_custom_xmlrpc_methods'             => '',
    'xmlrpc_disable_xmlrpc_headers'            => [],
    'xmlrpc_custom_xmlrpc_headers'             => '',
    'xmlrpc_xmlrpc_remove_rsd_link'            => 0,
    'xmlrpc_xmlrpc_remove_wlwmanifest_link'    => 0,
    'xmlrpc_remove_xmlrpc_pingback_link'       => 0,

    // Admin Bar.
    'admin_bar_disable_admin_bar'              => 'no',
    'admin_bar_admin_bar_roles'                => [],

    // Updates.
    'updates_disable_updates'                  => 'no',
    'updates_plugin_updates'                   => 'manual',
    'updates_theme_updates'                    => 'manual',
    'updates_translation_updates'              => 'default',
    'updates_core_updates'                     => 'allow_minor_core_auto_updates',
    'updates_enable_update_vcs'                => 'default',
    'updates_updates_nags_only_for_admin'      => 0,

    // Tracking.
    'tracking_allow_usage_tracking'            => 0,

];
