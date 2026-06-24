// NODE.JS (NODE-enabled)
import antfu from '@antfu/eslint-config';

import { baseJSRules } from './bin-dev/tools/configs/eslint.config.mjs'; // Import root config

export default antfu(
  {
    typescript : false,
    node       : false, // Explicitly enable node
    ignores    : [
      '**/vendor/**',
      '**/public/**',
      '**/node_modules/**', // only ignoring it, in case accidently ran eslint on whole root, so want to avoid messing npm packages
      // '**/package.json',
    ],
    rules: {
      /* e18e rules */
      'e18e/prefer-spread-syntax': 'off',
      'e18e/prefer-static-regex': 'off',
      'e18e/prefer-array-at': 'off',
      'e18e/prefer-timer-args': 'off',
      'e18e/prefer-array-fill': 'off',
      'e18e/prefer-array-to-reversed': 'off',
      'e18e/prefer-array-to-spliced': 'off',
      'e18e/prefer-array-to-sorted': 'off',
      'e18e/prefer-array-from-map': 'off',
      'e18e/prefer-date-now': 'off',
      'e18e/prefer-object-has-own': 'off',
    },
  },
).append( baseJSRules );
