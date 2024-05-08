const mix = require('laravel-mix');

const { copyItems, removeItems } = require('./bin-dev/copy/fileUtils');

const exportPath = 'disabler';

const files = [
  { name: 'disabler.php' },
  { name: 'uninstall.php' },
  { name: 'LICENSE.md' },
  { name: 'composer.json' },
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
  { name: 'config' },
  { name: 'inc' },
  { name: 'public' },
  //  { name: 'resources/js' }, // Required for WordPress.org theme review.
  //  { name: 'resources/scss' }, // Required for WordPress.org theme review.
  { name: 'vendor' },
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
