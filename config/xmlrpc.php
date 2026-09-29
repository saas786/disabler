<?php

/**
 * Xmlrpc defaults.
 *
 * The config tier of the resolution order: what a setting resolves to when
 * nothing is stored and no preset overrides it. Carried over from the old
 * flat defaults file, split by section and stripped of its section prefix.
 */

return [
    'custom_xmlrpc_headers'          => '',
    'custom_xmlrpc_methods'          => '',
    'custom_xmlrpc_whitelist_ips'    => '',
    'disable_xmlrpc'                 => 'no',
    'disable_xmlrpc_headers'         => [],
    'remove_xmlrpc_pingback_link'    => 0,
    'xmlrpc_methods'                 => [],
    'xmlrpc_remove_rsd_link'         => 0,
    'xmlrpc_remove_wlwmanifest_link' => 0,
    'xmlrpc_whitelist_jetpack_ips'   => 0,
];
