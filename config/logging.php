<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;
use function Hybrid\storage_path;
use function Hybrid\Tools\env;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */
    'default'      => env( 'LOG_CHANNEL', 'stack' ),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */
    'deprecations' => [
        'channel' => env( 'LOG_DEPRECATIONS_CHANNEL', 'null' ),
        'trace'   => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, hybrid-log uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */
    'channels'     => [
        'stack'      => [
            'driver'            => 'stack',
            'channels'          => explode( ',', (string) env( 'LOG_STACK', 'single' ) ), // sentry
            'ignore_exceptions' => false,
        ],

        'single'     => [
            'driver' => 'single',
            'path'   => storage_path( 'logs/disabler.log' ),
            'level'  => env( 'LOG_LEVEL', 'debug' ),
        ],

        'daily'      => [
            'driver' => 'daily',
            'path'   => storage_path( 'logs/disabler.log' ),
            'level'  => env( 'LOG_LEVEL', 'debug' ),
            'days'   => 14,
        ],

        'slack'      => [
            'driver'   => 'slack',
            'url'      => env( 'LOG_SLACK_WEBHOOK_URL' ),
            'username' => 'Disabler Log',
            'emoji'    => ':boom:',
            'level'    => env( 'LOG_LEVEL', 'critical' ),
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => env( 'LOG_LEVEL', 'debug' ),
            'handler'      => env( 'LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class ),
            'handler_with' => [
                'host'             => env( 'PAPERTRAIL_URL' ),
                'port'             => env( 'PAPERTRAIL_PORT' ),
                'connectionString' => 'tls://' . env( 'PAPERTRAIL_URL' ) . ':' . env( 'PAPERTRAIL_PORT' ),
            ],
        ],

        'stderr'     => [
            'driver'       => 'monolog',
            'level'        => env( 'LOG_LEVEL', 'debug' ),
            'handler'      => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter'    => env( 'LOG_STDERR_FORMATTER' ),
        ],

        'syslog'     => [
            'driver' => 'syslog',
            'level'  => env( 'LOG_LEVEL', 'debug' ),
        ],

        'errorlog'   => [
            'driver' => 'errorlog',
            'level'  => env( 'LOG_LEVEL', 'debug' ),
        ],

        'null'       => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency'  => [
            'path' => storage_path( 'logs/disabler.log' ),
        ],
        'cache'      => [
            'driver' => 'single',
            'path'   => storage_path( 'logs/cache.log' ),
            'level'  => env( 'LOG_LEVEL', 'debug' ),
        ],
        'sentry'     => [
            'driver' => 'sentry',
            'level'  => Logger::ERROR, // The minimum monolog logging level at which this handler will be triggered
            'bubble' => true, // Whether the messages that are handled can bubble up the stack or not
        ],
    ],

];
