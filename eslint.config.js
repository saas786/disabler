// eslint.config.js
const antfu = require( '@antfu/eslint-config' ).default;

module.exports = antfu(
  // Configures for antfu's config.
  {
    typescript : false,
    // This replaces the old `.gitignore`
    ignores    : [],
  },
  // From the second arguments they are ESLint Flat Configs
  // you can have multiple configs.
  {
    rules: {
      'style/space-in-parens'    : [ 'error', 'always' ],
      'semi': [
        'error',
        'always',
        {
          omitLastInOneLineBlock     : false,
          omitLastInOneLineClassBody : false,
        },
      ],
      'style/semi'              : [ 'error', 'always' ],
      'curly'                   : [ 'error', 'multi-line' ],
      'style/indent'            : 'off',
      'style/indent-binary-ops' : 'off',
      'style/no-tabs'           : 'off',
      'style/no-multi-spaces'   : [ 'warn', {
        ignoreEOLComments : false,
        exceptions        : {
          VariableDeclarator   : true, // Allows alignment for variable assignments
          ImportDeclaration    : true, // Allows alignment for imports
          Property             : true, // Allows alignment for object properties
          AssignmentExpression : true, // Allows alignment for assignment expressions
        },
      } ],
      'style/key-spacing': [ 'error', {
        align: {
          beforeColon : true,
          afterColon  : true,
          on          : 'colon',
        },
      } ],
      'style/array-bracket-spacing'      : [ 'error', 'always' ],
      'style/object-curly-spacing'       : [ 'error', 'always' ],
      'no-unused-vars'                   : 'off',
      'unused-imports/no-unused-imports' : 'off',
      'unused-imports/no-unused-vars'    : [
        'warn',
        {
          vars              : 'all',
          varsIgnorePattern : '^_',
          args              : 'after-used',
          argsIgnorePattern : '^_',
        },
      ],
      'style/brace-style': [ 'error', '1tbs', { allowSingleLine: true } ],
    },
  }
);
