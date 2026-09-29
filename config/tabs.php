<?php

/**
 * Tab labels.
 *
 * Read as `disabler.tabs.{slug}` when a tab is drawn. Each may be a closure,
 * so the translation is deferred until the screen is actually rendered.
 *
 * The order of tabs is not set here: a tab sorts by the priority of its
 * earliest control, which the control declarations carry.
 */

return [
    'editor'      => static fn() => esc_html__( 'Editor', 'hbp-disabler' ),
    'backend'     => static fn() => esc_html__( 'Backend', 'hbp-disabler' ),
    'frontend'    => static fn() => esc_html__( 'Frontend', 'hbp-disabler' ),
    'performance' => static fn() => esc_html__( 'Performance', 'hbp-disabler' ),
    'media'       => static fn() => esc_html__( 'Media', 'hbp-disabler' ),
    'revisions'   => static fn() => esc_html__( 'Revisions', 'hbp-disabler' ),
    'feeds'       => static fn() => esc_html__( 'Feeds', 'hbp-disabler' ),
    'restapi'     => static fn() => esc_html__( 'REST API', 'hbp-disabler' ),
    'privacy'     => static fn() => esc_html__( 'Privacy', 'hbp-disabler' ),
    'xmlrpc'      => static fn() => esc_html__( 'XML-RPC', 'hbp-disabler' ),
    'admin_bar'   => static fn() => esc_html__( 'Admin Bar', 'hbp-disabler' ),
    'updates'     => static fn() => esc_html__( 'Updates', 'hbp-disabler' ),
    'tracking'    => static fn() => esc_html__( 'Usage Tracking', 'hbp-disabler' ),
];
