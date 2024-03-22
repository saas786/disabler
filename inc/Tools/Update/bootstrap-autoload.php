<?php
/**
 * Autoload update bootstrap file.
 *
 * This file is used to autoload classes and functions necessary for the update
 * to run. Classes utilize the PSR-4 autoloader in Composer which is defined in
 * `composer.json`.
 */

namespace HBP\Disabler\Tools\Update;

// ------------------------------------------------------------------------------
// Autoload Update functions files.
// ------------------------------------------------------------------------------
//
// Load any functions-files from the `/inc/Tools/Update/` folder that are needed. Add additional
// files to the array without the `.php` extension.
array_map(
    static function ( $file ) {
        require_once dirname( DISABLER_FILE ) . "/inc/Tools/Update/{$file}.php";
    },
    [
        'functions-update/functions-update-3_0_0',
        'functions-update/functions-update-3_0_3',
        'functions-update/functions-update-4_0_0_RC_2',
    ]
);
