const path = require('path');

module.exports = {
  root: true,
  env: {
    browser: true,
    es2024: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:@wordpress/eslint-plugin/recommended',
    'plugin:import/recommended',
    'plugin:jsx-a11y/recommended',
    'stylelint-config-standard',
    'plugin:prettier/recommended',
  ],
  overrides: [
    {
      files: ['plagins/**/*.{js}', 'themes/**/*.{js}'],
      rules: {
        'n/no-unsupported-features/node-builtins': 'off',
      },
    },
  ],
  plugins: ['@wordpress', 'jsx-a11y', 'stylelint', 'prettier', 'import'],
  rules: {
    'no-console': 0,
    'n/no-missing-import': 'off',
    'jsx-a11y/click-events-have-key-events': 'off',
    'no-unused-vars': 'off',
    'prettier/prettier': 'error',
    'sort-imports': [
      'error',
      {
        ignoreCase: true,
        ignoreDeclarationSort: true,
      },
    ],
    'import/no-unresolved': ['error'],
    'import/no-extraneous-dependencies': ['error', { packageDir: [path.resolve(__dirname)] }],
    'import/extensions': [
      'error',
      'ignorePackages',
      {
        js: 'never',
        jsx: 'never',
      },
    ],
    'import/order': [
      'error',
      {
        'newlines-between': 'always',
        groups: [
          'builtin',
          'external',
          'internal',
          'sibling',
          'parent',
          'index',
          'object',
          'type',
          'unknown',
        ],
        pathGroups: [
          {
            pattern: 'components',
            group: 'internal',
          },
          {
            pattern: 'common',
            group: 'internal',
          },
          {
            pattern: 'routes/ **',
            group: 'internal',
          },
          {
            pattern: 'assets/**',
            group: 'internal',
            position: 'after',
          },
        ],
        pathGroupsExcludedImportTypes: ['internal'],
        alphabetize: {
          order: 'asc',
          caseInsensitive: true,
        },
      },
    ],
  },
  settings: {
    react: {
      version: 'detect',
    },
    'import/extensions': ['.js'],
    'import/ignore': ['.css'],
  },
  globals: {
    getApp: false,
    Page: false,
    wx: false,
    App: false,
    getCurrentPages: false,
    Component: false,
    Raven: 'readonly',
  },
};
