const { copyItems, removeItems } = require( './bin-dev/copy/fileUtils' );

const exportPath = 'disabler-prefixed';

// Built assets. The PHP and vendor tree is written by `composer prefix`,
// which runs php-scoper; this only adds what webpack produced, since
// php-scoper has no reason to walk it.
const folders = [
  { name: 'public/css' },
  { name: 'public/js' },
  { name: 'public/svg' },
  { name: 'public/lang' },
  // { name: 'public', trimPath: 'public' },
];

// Composer artefacts that must not ship.
const deleteFiles = [
  `${exportPath}/vendor/bin`,
  `${exportPath}/vendor/composer/installers`,
];

// Awaited in order. The deletes below target paths inside the tree the copy
// writes, so running the two concurrently makes the result depend on which
// finishes first.
async function main() {
  await copyItems( exportPath, folders );
  console.log( 'Folders copied successfully!' );

  await removeItems( deleteFiles );
  console.log( 'Unwanted files removed successfully!' );
}

// Exit non-zero on failure, so the calling npm script stops rather than
// packaging a half-written export. Logging alone would leave the exit code at
// 0 and the chain would carry on.
main().catch( ( error ) => {
  console.error( 'Export failed:', error );
  process.exitCode = 1;
} );
