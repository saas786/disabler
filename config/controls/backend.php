<?php

/**
 * Backend controls.
 *
 * Loaded as `disabler.controls.backend`, which Definitions expands into the
 * controls it maps: the outer key groups the file, the inner keys are the
 * setting keys.
 *
 * Note there is no `container-class` here. The row class is derived from the
 * control key, and an `events` target names a control key rather than a
 * selector, so the class a control carries and the selector a rule points at
 * come from one place and cannot drift apart.
 */

return [
    'backend.disable_self_ping'      => [
        'type'        => 'checkbox',
        'tab'         => 'backend',
        'priority'    => 200,
        'section'     => 'backend',
        'label'       => static fn() => esc_html__( 'Disable self pings', 'hbp-disabler' ),
        'after_field' => static fn() => esc_html__( 'Prevents trackbacks/pings from your own domain.', 'hbp-disabler' ),
        'events'      => [
            'true'  => [ 'show' => 'backend.disable_self_ping_urls' ],
            'false' => [ 'hide' => 'backend.disable_self_ping_urls' ],
        ],
    ],

    'backend.disable_self_ping_urls' => [
        'type'        => 'textarea',
        'tab'         => 'backend',
        'priority'    => 201,
        'section'     => 'backend',
        'label'       => static fn() => esc_html__( 'Additional urls', 'hbp-disabler' ),
        /* Translators: %s Ping-back URL of website. */
        'description' => static fn() => sprintf( esc_html__( 'By default, the "No Self Pings" setting will exclude pings for this site (%s) but you can provide additional URLs below. Separate multiple URLs with line breaks.', 'hbp-disabler' ), esc_url( home_url() ) ),
    ],
];
