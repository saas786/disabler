<?php

/**
 * This helper is needed to "trick" composer autoloader to load the prefixed
 * files Otherwise if owncloud/core contains the same libraries ( i.e. guzzle )
 * it won't load the files, as the file hash is the same and thus composer would
 * think this was already loaded.
 *
 * In order to prevent this we append a unique prefix to the hash of all
 * autoloaded files.
 *
 * @see https://github.com/humbug/php-scoper/issues/298
 * @see https://github.com/maluueu/enterprise/blob/9f7b199de93ae418331fa9e19c54ba770e6aaaff/src/Snicco/skeleton/distributed-plugin/plugin/php-scoper/fix-static-file-autoloader.php
 */
function getFileContents( string $file ): string {
    $contents = file_get_contents( $file );

    if ( false === $contents ) {
        echo "Could not get contents of file {$file}\n";
        exit( 1 );
    }

    return $contents;
}

function putFileContents( string $file, string $contents ): void {
    $res = file_put_contents( $file, $contents );

    if ( false === $res ) {
        echo "Could not update contents up file {$file}\n";
        exit( 1 );
    }
}

function pregReplace( string $pattern, string $replacement, string $subject ): string {
    $res = preg_replace( $pattern, $replacement, $subject );

    if ( null === $res ) {
        echo "preg_replace failed for static_loader_contents\n";
        exit( 1 );
    }

    return $res;
}

function randomPrefix(): string {
    $date = (string) ( new DateTime( 'now' ) )->getTimestamp();
    $sub  = substr( md5( $date ), 0, 8 );
    if ( false === $sub ) {
        echo "Could not generate random prefix.\n";
        exit( 1 );
    }

    return 'VENDOR_NAMESPACE' . $sub;
}

$composer_directory = (string) ( $_SERVER['argv'][1] ?? '' );

if ( ! is_dir( $composer_directory ) ) {
    echo "Invalid composer directory [{$composer_directory}] provided.\n";
    exit( 1 );
}

echo "\n";
echo "=> Fixing autoloading issues caused by php-scoper...\n";

$prefix = randomPrefix();

$static_loader_path = $composer_directory . '/autoload_static.php';
if ( file_exists( $static_loader_path ) ) {
    $static_loader_contents = getFileContents( $static_loader_path );
    $static_loader_contents = pregReplace(
        '/\'([A-Za-z0-9]*?)\' => __DIR__ \. (.*?),/',
        sprintf( '\'%s_$1\' => __DIR__ . $2,', $prefix ),
        $static_loader_contents
    );
    file_put_contents( $static_loader_path, $static_loader_contents );
}

$files_loader_path = $composer_directory . '/autoload_files.php';
if ( file_exists( $files_loader_path ) ) {
    $autoload_files_content = getFileContents( $files_loader_path );
    $autoload_files_content = pregReplace(
        '/\'(.*?)\' => (.*?),/',
        sprintf( '\'%s_$1\' => $2,', $prefix ),
        $autoload_files_content
    );
    putFileContents( $files_loader_path, $autoload_files_content );
}
