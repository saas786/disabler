<?php

/**
 * This helper is needed to "trick" the composer autoloader into loading the
 * prefixed files. Otherwise, if the host application contains the same
 * libraries (i.e. guzzle), it won't load the files, as the file hash is the
 * same and composer would think they were already loaded.
 *
 * To prevent this we prepend a unique prefix to the hash of all autoloaded
 * files.
 *
 * @see https://github.com/humbug/php-scoper/issues/298
 */
const PREFIX_MARKER = 'Disabler_Vendor'; // VENDOR_NAMESPACE

function getFileContents( string $file ): string {
    $contents = file_get_contents( $file );

    if ( false === $contents ) {
        fail( "Could not get contents of file {$file}" );
    }

    return $contents;
}

function putFileContents( string $file, string $contents ): void {
    if ( false === file_put_contents( $file, $contents ) ) {
        fail( "Could not update contents of file {$file}" );
    }
}

function pregReplace( string $pattern, string $replacement, string $subject ): string {
    $res = preg_replace( $pattern, $replacement, $subject );

    if ( null === $res ) {
        fail( "preg_replace failed for pattern {$pattern}" );
    }

    return $res;
}

function fail( string $message ): void {
    echo "{$message}\n";
    exit( 1 );
}

function randomPrefix(): string {
    return PREFIX_MARKER . bin2hex( random_bytes( 4 ) );
}

$composer_directory = (string) ( $_SERVER['argv'][1] ?? '' );

if ( ! is_dir( $composer_directory ) ) {
    fail( "Invalid composer directory [{$composer_directory}] provided." );
}

$static_loader_path = $composer_directory . '/autoload_static.php';
$files_loader_path  = $composer_directory . '/autoload_files.php';

// No `files` autoloading at all: nothing to do.
if ( ! file_exists( $files_loader_path ) ) {
    echo "\n=> No autoload_files.php found, nothing to prefix.\n";
    exit( 0 );
}

if ( ! file_exists( $static_loader_path ) ) {
    fail( 'Found autoload_files.php but no autoload_static.php. Refusing to prefix only one of them.' );
}

$static_loader_contents = getFileContents( $static_loader_path );
$files_loader_contents  = getFileContents( $files_loader_path );

// Idempotency guard: never prefix twice.
if ( str_contains( $static_loader_contents, PREFIX_MARKER )
    || str_contains( $files_loader_contents, PREFIX_MARKER ) ) {
    echo "\n=> Autoloader already prefixed, skipping.\n";
    exit( 0 );
}

echo "\n=> Fixing autoloading issues caused by php-scoper...\n";

$prefix = randomPrefix();

// Composer `files` keys are always 32-char lowercase md5 hashes. Anchoring on
// that shape is what keeps us out of $classMap, where root-namespace keys like
// 'Normalizer' or 'Attribute' would otherwise match and silently break.
$pattern     = '/\'([a-f0-9]{32})\' => /';
$replacement = sprintf( '\'%s_$1\' => ', $prefix );

putFileContents(
    $static_loader_path,
    pregReplace( $pattern, $replacement, $static_loader_contents )
);

putFileContents(
    $files_loader_path,
    pregReplace( $pattern, $replacement, $files_loader_contents )
);

echo "=> Prefixed autoloaded files with [{$prefix}].\n";
