const mix = require('laravel-mix');

const { copyItems, removeItems } = require('./bin-dev/copy/fileUtils');

const exportPath = 'disabler-prefixed';

const files = [
  { name: 'public/mix-manifest.json' },
];

// Call the asynchronous function.
copyItems(exportPath, files)
  .then(() => {
    console.log('Files copied successfully!');
  })
  .catch((error) => {
    console.error('An error occurred:', error);
  });

const folders = [
  { name: 'public/css' },
  { name: 'public/js' },
  { name: 'public/lang' },
  // { name: 'public', trimPath: 'public' },
];

// Call the asynchronous function.
copyItems(exportPath, folders)
  .then(() => {
    console.log('Folders copied successfully!');
  })
  .catch((error) => {
    console.error('An error occurred:', error);
  });

// Delete unnecessary files and folders.
const deleteFiles = [
	`mix-manifest.json`,
	`${exportPath}/vendor/bin`,
	`${exportPath}/vendor/composer/installers`,
];

// Call the asynchronous function.
removeItems(deleteFiles)
  .then(() => {
    console.log('Files copied successfully!');
  })
  .catch((error) => {
    console.error('An error occurred:', error);
  });
