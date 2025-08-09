import vueParser from 'vue-eslint-parser';
import tsParser from '@typescript-eslint/parser';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';

export default [
  // Ignore common output dirs
  { ignores: ['**/node_modules/**', '**/dist/**', '**/build/**'] },

  // Vue / Common TS(JS) files (no React rules here)
  {
    files: ['**/*.{ts,js,vue}'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tsParser,
        project: ['./tsconfig.json'],
        tsconfigRootDir: new URL('.', import.meta.url),
        ecmaVersion: 'latest',
        sourceType: 'module',
        extraFileExtensions: ['.vue'],
      },
    },
    plugins: { '@typescript-eslint': tsPlugin },
    rules: {
      'no-console': 'warn',
      'no-unused-vars': 'warn',
      '@typescript-eslint/ban-ts-comment': 'warn',
    },
  },

  // React-only rules for TSX/JSX files
  {
    files: ['**/*.{tsx,jsx}'],
    languageOptions: {
      parser: tsParser,
      parserOptions: {
        project: ['./tsconfig.json'],
        tsconfigRootDir: new URL('.', import.meta.url),
        ecmaVersion: 'latest',
        sourceType: 'module',
        ecmaFeatures: { jsx: true },
      },
    },
    plugins: { 'react-hooks': reactHooks, 'react-refresh': reactRefresh },
    rules: {
      ...reactHooks.configs.recommended.rules,
      ...(reactRefresh.configs?.recommended?.rules || {}),
      // Allow exporting custom hooks from component files if needed
      'react-refresh/only-export-components': ['warn', { allowExportNames: ['use*'] }],
    },
  },
];
