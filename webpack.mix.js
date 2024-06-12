const mix = require('laravel-mix');

require('laravel-mix-merge-manifest');

mix.setPublicPath('public');

/**
 * Set Laravel Mix options.
 *
 * @see https://github.com/JeffreyWay/laravel-mix/blob/fe4c1383bd11d25862b557587c97bafd95594365/docs/url-rewriting.md#css-url-rewriting
 * @see https://github.com/webpack-contrib/terser-webpack-plugin#options
 */
mix.options({
  postCss: [require('postcss-preset-env')()],
  processCssUrls: false,
  terser: {
    terserOptions: {
      output: {
        comments: false,
      },
    },
    extractComments: false,
  },
});

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
  .js('resources/js/admin/tabs.js', 'js/admin')
  .js('resources/js/admin/settings.js', 'js/admin')
  .sass('resources/scss/admin/notices.scss', 'css/admin')
  .sass('resources/scss/admin/settings.scss', 'css/admin')
  .mergeManifest();

/**
 * Add custom Webpack configuration.
 *
 * Laravel Mix doesn't currently minimize images while using its `.copy()`
 * function, so we're using the `CopyPlugin` for processing and copying
 * images into the distribution folder.
 *
 * @see https://github.com/JeffreyWay/laravel-mix/blob/fe4c1383bd11d25862b557587c97bafd95594365/docs/quick-webpack-configuration.md
 * @see https://webpack.js.org/configuration/
 */
mix.webpackConfig({
  stats: 'minimal',
  devtool: mix.inProduction() ? false : 'source-map',
  performance: { hints: false },
  externals: { jquery: 'jQuery' },
});
