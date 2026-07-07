<?php

/**
 * Autoload bootstrap file.
 */

namespace HBP\Disabler;

if ( ! class_exists( Plugin::class ) && file_exists( DISABLER_DIR . '/vendor/scoper-autoload.php' ) ) {
    require_once DISABLER_DIR . '/vendor/scoper-autoload.php';
} elseif ( ! class_exists( Plugin::class ) && file_exists( DISABLER_DIR . '/vendor/autoload.php' ) ) {
    require_once DISABLER_DIR . '/vendor/autoload.php';
}

/*
 * Action scheduler.
 *
 * Note: It needs to be called early because it hooks into `plugins_loaded`.
 *
 * @see https://actionscheduler.org/usage/#loading-action-scheduler
 */
if ( file_exists( DISABLER_DIR . '/vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
    require_once DISABLER_DIR . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
}

array_map(
    static function ( $file ) {
        require_once DISABLER_DIR . "/inc/{$file}.php";
    },
    [
        'functions-helpers',
    ]
);
