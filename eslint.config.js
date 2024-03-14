// eslint.config.js
const antfu = require('@antfu/eslint-config').default;

module.exports = antfu(
  // Configures for antfu's config.
  {
    typescript: false,
    // This replaces the old `.gitignore`
    ignores: [],
  },
  // From the second arguments they are ESLint Flat Configs
  // you can have multiple configs.
  {
    rules: {
      'semi': [
        'error',
        'always',
        {
          omitLastInOneLineBlock: false,
          omitLastInOneLineClassBody: false,
        },
      ],
      'style/semi': ['error', 'always'],
      'curly': ['error', 'multi-line'],
    },
  },
);
