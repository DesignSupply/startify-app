import { globalIgnores } from 'eslint/config';
import { dirname } from 'path';
import { fileURLToPath } from 'url';
import { FlatCompat } from '@eslint/eslintrc';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const compat = new FlatCompat({
  baseDirectory: __dirname,
});

const eslintConfig = [
  ...compat.extends('next/core-web-vitals', 'next/typescript', 'prettier'),
  globalIgnores([
    '.next/**',
    'out/**',
    'build/**',
    'node_modules/**',
    'coverage/**',
    'next-env.d.ts',
    '*.tsbuildinfo',
    'public/sw.js',
    'public/workbox-*.js',
    'public/worker-*.js',
    'public/sw.js.map',
    'public/workbox-*.js.map',
    'public/worker-*.js.map',
    'public/fallback-*.js',
    'public/sitemap*.xml',
  ]),
];

export default eslintConfig;
