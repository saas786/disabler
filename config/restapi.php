<?php

/**
 * Restapi defaults.
 *
 * The config tier of the resolution order: what a setting resolves to when
 * nothing is stored and no preset overrides it. Carried over from the old
 * flat defaults file, split by section and stripped of its section prefix.
 */

return [
    'application_passwords_roles'      => [],
    'disable_application_passwords'    => 'no',
    'disable_rest_api_for_visitors'    => 0,
    'disable_rest_api_link_in_headers' => 0,
    'disable_rest_api_links'           => 0,
    'disable_rest_api_rsd_link'        => 0,
];
