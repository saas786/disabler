<?php
/**
 * Autoload bootstrap file.
 */

namespace HBP\Disabler;

if ( file_exists( dirname( DISABLER_FILE ) . '/vendor/scoper-autoload.php' ) ) {
    require_once dirname( DISABLER_FILE ) . '/vendor/scoper-autoload.php';
} elseif ( file_exists( dirname( DISABLER_FILE ) . '/vendor/autoload.php' ) ) {
    require_once dirname( DISABLER_FILE ) . '/vendor/autoload.php';
}

/*
 * Action scheduler.
 *
 * It needs to be called early because it hooks into `plugins_loaded`.
 *
 * @see https://actionscheduler.org/usage/#loading-action-scheduler
 */
if ( file_exists( dirname( DISABLER_FILE ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
    require_once dirname( DISABLER_FILE ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
}

array_map(
    static function ( $file ) {
        require_once dirname( DISABLER_FILE ) . "/inc/{$file}.php";
    },
    [
        'functions-helpers',
    ]
);
