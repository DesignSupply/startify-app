// @ts-nocheck
import { resolve } from 'path';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import vue from '@vitejs/plugin-vue';
import handlebars from 'vite-plugin-handlebars';
import pug from 'vite-plugin-pug';
import glsl from 'vite-plugin-glsl';
import globule from 'globule';
import siteData from './sitedata.mjs';

const env = {
  url: {
    development: 'http://localhost:2000',
    production: 'https://example.com'
  },
  siteData
}

const htmlFiles = globule.find('src/**/*.html', {
  ignore: [ 
    'src/hbs/*.html',
    'src/**/_*.html' 
  ]
});

const multiPageObject = Object.fromEntries(htmlFiles.map((path) => {
  const relativePath = path.replace('src/', '');
  const key = relativePath.replace(/\//g, '-').replace('.html', '');
  return [key, resolve(__dirname, path)];
}));

// @ts-ignore
export default defineConfig(({ command }) => ({
  server: {
    port: 2000,
  },
  root: 'src',
  base: './',
  publicDir: resolve(__dirname, 'src/static'),
  build: {
    outDir: resolve(__dirname, 'dist'),
    emptyOutDir: true,
    assetsDir: 'assets',
    assetsInlineLimit: 0,
    cssCodeSplit: false,
    sourcemap: true,
    rollupOptions: {
      input: multiPageObject,
      output: {
        entryFileNames: 'assets/scripts/[name].min.js',
        chunkFileNames: 'assets/scripts/[name].min.js',
        assetFileNames: (assetFile) => {
          if(assetFile.name !== undefined) {
            if(/\.css$/.test(assetFile.name)) {
              return 'assets/stylesheets/[name].min.[ext]';
            } else if(/\.(gif|jpeg|jpg|png|svg|webp)$/.test(assetFile.name)) {
              return 'assets/images/[name].min.[ext]';
            } else if(/\.(ttf|otf|eot|woff|woff2)$/.test(assetFile.name)) {
              return 'assets/fonts/[name].[ext]';
            } else {
              return 'assets/[name].[ext]';
            }
          }
        } 
      }
    }
  },
  resolve: {
    alias: [
      { find: '@/react', replacement: resolve(__dirname, 'src/tsx') },
      { find: '@/vue', replacement: resolve(__dirname, 'src/vue') },
      { find: '@', replacement: resolve(__dirname, 'src') }
    ],
    extensions: [
      '.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json', '.vue'
    ]
  },
  css: {
    devSourcemap: true
  },
  plugins: [
    handlebars({
      context: (pagePath) => {
        return {
          envUrl: command === 'serve' ? env.url.development : env.url.production,
          siteName: env.siteData.siteName,
          siteUrl: env.siteData.siteUrl,
          pageMeta: env.siteData.pageMeta[pagePath]
        }
      },
      partialDirectory: [
        resolve(__dirname, 'src/hbs'),
        resolve(__dirname, 'src/hbs/components'),
      ],
      helpers: {
        equal: (a, b) => a === b,
      }
    }),
    pug({
      localImports: true
    }, {
      envUrl: command === 'serve' ? env.url.development : env.url.production,
      siteName: env.siteData.siteName,
      siteUrl: env.siteData.siteUrl,
      pageMeta: env.siteData.pageMeta
    }),
    react(),
    vue(),
    glsl({
      include: [
        'src/glsl/**/*.glsl',
        'src/glsl/**/*.wgsl',
        'src/glsl/**/*.vert',
        'src/glsl/**/*.frag',
        'src/glsl/**/*.vs',
        'src/glsl/**/*.fs',
      ]
    })
  ]
}));
