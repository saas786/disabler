<?php

declare(strict_types = 1);

use Isolated\Symfony\Component\Finder\Finder;

// You can do your own things here, e.g. collecting symbols to expose dynamically
// or files to exclude.
// However beware that this file is executed by PHP-Scoper, hence if you are using
// the PHAR it will be loaded by the PHAR. So it is highly recommended to avoid
// to auto-load any code here: it can result in a conflict or even corrupt
// the PHP-Scoper analysis.

// Example of collecting files to include in the scoped build but to not scope
// leveraging the isolated finder.

/*
$excludedFiles = array_map(
    static fn( SplFileInfo $fileInfo ) => $fileInfo->getPathName(),
    iterator_to_array(
        Finder::create()->files()->in( __DIR__ ),
        false
    )
);
*/

$polyfillsBootstraps = array_map(
    static fn( SplFileInfo $fileInfo ) => $fileInfo->getPathname(),
    iterator_to_array(
        Finder::create()
                ->files()
                ->in( dirname( __DIR__, 2 ) . '/vendor/symfony/polyfill-*' )
                ->name( 'bootstrap*.php' ),
        false
    )
);

$woocommerceActionScheduler = array_map(
    static fn( SplFileInfo $fileInfo ) => $fileInfo->getPathname(),
    iterator_to_array(
        Finder::create()
                ->files()
                ->in( dirname( __DIR__, 2 ) . '/vendor/woocommerce/action-scheduler' ),
        false
    )
);

/**
 * Exclude WordPress classes/functions/constants from scoping via
 * automatically generated exclude files.
 *
 * @link https://github.com/snicco/php-scoper-wordpress-excludes
 * @return array
 */
function getWordPressStubs( string $name ): array {
    $file     = dirname( __DIR__, 2 ) . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated/' . $name;
    $contents = file_get_contents( $file );
    if ( false === $contents ) {
        throw new \RuntimeException( "Could not get contents of file {$file}" );
    }

    return json_decode( $contents, true, JSON_THROW_ON_ERROR );
}

/**
 * Exclude WooCommerce classes/functions/constants from scoping via
 * automatically generated exclude files.
 *
 * @link https://github.com/snicco/php-scoper-woocommerce-excludes
 * @return array
 */
function getWooCommerceStubs( string $name ): array {
    $file = dirname( __DIR__, 2 ) . '/vendor/sniccwp/php-scoper-woocommerce-excludes/generated/' . $name;

    if ( file_exists( $file ) ) {
        return require_once $file;
    }

    return [];
}

return [
    'exclude-classes'         => [
        ...getWordPressStubs( 'exclude-wordpress-classes.json' ),
        // ...getWordPressStubs( 'exclude-wordpress-constants.json' ),
        // ...getWordPressStubs( 'exclude-wordpress-functions.json' ),
        ...getWordPressStubs( 'exclude-wordpress-interfaces.json' ),
        ...getWooCommerceStubs( 'exclude-woocommerce-classes.php' ),
        // ...getWooCommerceStubs( 'exclude-woocommerce-constants.php' ),
        // ...getWooCommerceStubs( 'exclude-woocommerce-functions.php' ),
        ...getWooCommerceStubs( 'exclude-woocommerce-interfaces.php' ),
        ...getWooCommerceStubs( 'exclude-woocommerce-packages-classes.php' ),
        // ...getWooCommerceStubs( 'exclude-woocommerce-packages-constants.php' ),
        // ...getWooCommerceStubs( 'exclude-woocommerce-packages-functions.php' ),
        ...getWooCommerceStubs( 'exclude-woocommerce-packages-interfaces.php' ),
        ...getWooCommerceStubs( 'exclude-woocommerce-packages-traits.php' ),
        ...getWooCommerceStubs( 'exclude-woocommerce-traits.php' ),
    ],
    'exclude-constants'       => array_merge(
    // ['VENDOR_CAPS_PLUGIN_ENV', 'VENDOR_CAPS_DELETE_DATA_ON_DELETION', 'VENDOR_CAPS_PLUGIN_DEBUG', 'WP_CLI'],
    // ['/^SYMFONY\_[\p{L}_]+$/'],
    // [ 'STDIN' ],
    // Monolog
        [ '#^ZEND\_MONITOR\_EVENT\_SEVERITY\_[\p{L}_]+$#' ],
        // @see https://github.com/WordPress/WordPress/blob/bbe67a01472b10a83181b126d5edadf4f6caff0c/wp-includes/cron.php#L997
        [ 'DISABLE_WP_CRON' ],
        // Disabler plugin
        [ '#^Disabler\_[\p{L}_]+$#' ],
        getWordPressStubs( 'exclude-wordpress-constants.json' ),
        getWooCommerceStubs( 'exclude-woocommerce-constants.php' ),
        getWooCommerceStubs( 'exclude-woocommerce-packages-constants.php' )
    ),

    // List of excluded files, i.e. files for which the content will be left untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    'exclude-files'           => [
        // these paths are relative to this file location, so it should be in the root directory
        '../../vendor/symfony/deprecation-contracts/function.php',
        './plugin.php',
        ...$woocommerceActionScheduler,
    ],
    'exclude-functions'       => array_merge(
        getWordPressStubs( 'exclude-wordpress-functions.json' ),
        getWooCommerceStubs( 'exclude-woocommerce-functions.php' ),
        getWooCommerceStubs( 'exclude-woocommerce-packages-functions.php' )
    ),

    // List of symbols to consider internal i.e. to leave untouched.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#excluded-symbols
    'exclude-namespaces'      => [
        'HBP\Disabler',
        // 'Hybrid'                     // The Acme\Foo namespace (and sub-namespaces)
        // 'Acme\Foo'                     // The Acme\Foo namespace (and sub-namespaces)
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],
    'expose-classes'          => [],
    'expose-constants'        => [],
    'expose-functions'        => [],

    // List of symbols to expose.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#exposed-symbols
    'expose-global-classes'   => false,
    'expose-global-constants' => false,
    'expose-global-functions' => false,
    'expose-namespaces'       => [
        // 'Acme\Foo'                     // The Acme\Foo namespace (and sub-namespaces)
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // This configuration entry is completely ignored when using Box.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#finders-and-paths
    'finders'                 => [
        Finder::create()->files()->in( 'config' ),
        Finder::create()->files()->in( 'inc' ),
        Finder::create()->files()->in( 'public/views' ),
        Finder::create()
                ->files()
                ->ignoreVCS( true )
                ->notName( '/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/' )
                ->exclude( [
                    'doc',
                    'test',
                    'test_old',
                    'tests',
                    'Tests',
                    'vendor-bin',
                ] )
                ->notPath([
                    // Exclude all libraries for WordPress, WooCommerce
                    // '#woocommerce/action-scheduler/#',
                    // Exclude libraries
                    // '#symfony/deprecation-contracts/#',
                    // Exclude tests from libraries
                    '#psr/log/Psr/Log/Test/#',
                    // Exclude php-scoper exclusion package(s)
                    '#sniccowp/#',
                    '#sniccwp/#',
                ])
                ->in( 'vendor' ),
        Finder::create()->append( [
            'plugin.php',
            'readme.txt',
            'composer.json',
        ] ),
    ],

    // The base output directory for the prefixed files.
    // This will be overridden by the 'output-dir' command line option if present.
    // 'output-dir'              => 'build',

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#patchers
    'patchers'                => [
        static function ( string $filePath, string $prefix, string $content ): string {
            // Hybrid Core framework relies on class aliases,
            // as they can be registered as string,
            // so php-scoper won't be able to prefix them,
            // so manually prefixing it via patchers.
            if ( ! str_ends_with( $filePath, 'hybrid-core/src/Core/Bootstrap/RegisterFacades.php' ) ) {
                return $content;
            }

            $content = str_replace(
                'AliasLoader::getInstance($aliases)->register();',
                sprintf(
                    '$prefixed_aliases=[];
					foreach( $aliases as $alias=>$class){
						$prefixed_aliases["%s\\\\".$alias]=$class;
					}
					$aliases=$prefixed_aliases;
					AliasLoader::getInstance( $aliases )->register();',
                    $prefix
                ),
                $content
            );

            return $content;
        },
    ],
    // The prefix configuration. If a non-null value is used, a random prefix
    // will be generated instead.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#prefix
    'prefix'                  => 'HBP_Disabler_Vendor',
];
