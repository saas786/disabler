const { copyItems, removeItems } = require( './bin-dev/copy/fileUtils' );

const exportPath = 'disabler-prefixed';

const folders = [
  { name: 'public/css' },
  { name: 'public/js' },
  { name: 'public/svg' },
  { name: 'public/lang' },
  // { name: 'public', trimPath: 'public' },
];

// Call the asynchronous function.
copyItems( exportPath, folders )
  .then( () => {
    console.log( 'Folders copied successfully!' );
  } )
  .catch( ( error ) => {
    console.error( 'An error occurred:', error );
  } );

// Delete unnecessary files and folders.
const deleteFiles = [
  `${exportPath}/vendor/bin`,
  `${exportPath}/vendor/composer/installers`,
];

// Call the asynchronous function.
removeItems( deleteFiles )
  .then( () => {
    console.log( 'Files copied successfully!' );
  } )
  .catch( ( error ) => {
    console.error( 'An error occurred:', error );
  } );
