const mix = require('laravel-mix');

require('laravel-mix-merge-manifest');

mix.setPublicPath('public');

/**
 * Builds sources maps for assets.
 *
 * @see https://github.com/JeffreyWay/laravel-mix/blob/fe4c1383bd11d25862b557587c97bafd95594365/docs/api.md#sourcemapsgenerateforproduction-devtype-productiontype
 */
mix.sourceMaps();

/**
 * Versioning and cache busting. Append a unique hash for production assets. If
 * you only want versioned assets in production, do a conditional check for
 * `mix.inProduction()`.
 *
 * @see https://github.com/JeffreyWay/laravel-mix/blob/fe4c1383bd11d25862b557587c97bafd95594365/docs/api.md#versionfiles
 */
mix.version();

mix
  .js('resources/js/blocks/disable-embeds/index.js', 'public/js/blocks/disable-embeds/index.js')
  .react()
  .mergeManifest();
