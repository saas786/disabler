<?php

namespace HBP\Disabler;

use Hybrid\Core\Application;
use function Hybrid\app;
use function Hybrid\booted;

$disabler = booted() ? app() : new Application( WP_CONTENT_DIR . '/.hbp-disabler', false );

$disabler->useConfigPath( dirname( DISABLER_FILE ) . '/config' );
$disabler->useResourcePath( dirname( DISABLER_FILE ) . '/resources' );
$disabler->usePublicPath( dirname( DISABLER_FILE ) . '/public' );
$disabler->useStoragePath( $disabler->bootstrapPath() . '/storage' );

$disabler->bootstrap();

do_action( 'hbp/disabler/before/providers/register', $disabler );

$disabler->register( \Hybrid\Action\Scheduler\Provider::class );
$disabler->register( \Hybrid\Assets\Provider::class );
$disabler->register( \Hybrid\Log\Provider::class );
$disabler->register( \Hybrid\View\Provider::class );
$disabler->register( \HBP\Disabler\View\Provider::class );
$disabler->register( \HBP\Disabler\Admin\Provider::class );
$disabler->register( \HBP\Disabler\Optimize\Provider::class );
$disabler->register( Provider::class );

do_action( 'hbp/disabler/after/providers/register', $disabler );

do_action( 'hbp/disabler/before/boot', $disabler );

// ------------------------------------------------------------------------------
// Bootstrap the application.
// ------------------------------------------------------------------------------
//
// Calls the application `boot()` method, which launches the application. Pat
// yourself on the back for a job well done.

$disabler->boot();

do_action( 'hbp/disabler/after/boot', $disabler );
