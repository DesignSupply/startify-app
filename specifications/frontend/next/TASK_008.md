---
title: Next.jsアプリケーション開発タスクリスト:PWA対応
id: next_task_008
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:PWA対応

Next.jsのプロジェクトをPWAに対応させます。

---

## 1. PWAの設定

Next.js公式のライブラリを使用します。

```bash
npm install next-pwa
```

モジュールインストール後、Next.jsの設定ファイル（`/frontend/next/next.config.mjs`）をPWAに対応させるために修正します。

```mjs
/** @type {import('next').NextConfig} */
import withPWA from 'next-pwa';

const nextConfig = withPWA({
  dest: 'public',
  register: true,
  skipWaiting: true,
  disable: process.env.NODE_ENV === 'development',
  runtimeCaching: [
    {
      urlPattern: /^https:\/\/example.com\/.*/, // 本番環境の公開ドメイン
      handler: 'NetworkFirst',
      options: {
        cacheName: 'api-cache',
        expiration: {
          maxEntries: 10,
          maxAgeSeconds: 24 * 60 * 60,
        },
        networkTimeoutSeconds: 10,
      },
    },
    {
      urlPattern: /\.(png|jpg|jpeg|svg|gif|webp)$/,
      handler: 'CacheFirst',
      options: {
        cacheName: 'image-cache',
        expiration: {
          maxEntries: 50,
          maxAgeSeconds: 30 * 24 * 60 * 60,
        },
      },
    },
    {
      urlPattern: /\.(woff|woff2|ttf|eot)$/,
      handler: 'CacheFirst',
      options: {
        cacheName: 'font-cache',
        expiration: {
          maxEntries: 10,
          maxAgeSeconds: 60 * 24 * 60 * 60,
        },
      },
    },
    {
      urlPattern: /\.(css|js)$/,
      handler: 'StaleWhileRevalidate',
      options: {
        cacheName: 'static-resources',
        expiration: {
          maxEntries: 30,
          maxAgeSeconds: 7 * 24 * 60 * 60,
        },
      },
    },
  ],
  fallbacks: {
    document: '/offline.html'
  }
})({
  sassOptions: {
    includePaths: ['./src/styles'],
  },
  output: 'export',
  trailingSlash: true,
  images: {
    unoptimized: true
  },
  staticPageGenerationTimeout: 300
});

export default nextConfig;
```

続いてマニフェストファイルを（`/frontend/next/public/manifest.json`）で作成します。start_urlには本番環境の公開ドメインが入ります。

```json
{
  "id": "/",
  "theme_color": "#000000",
  "background_color": "#ffffff",
  "orientation": "any",
  "display": "standalone",
  "scope": "/",
  "start_url": "https://example.com/",
  "name": "Startify-App",
  "short_name": "Startify-App",
  "description": "Startify-Appは、AI駆動の開発に対応するために設計されたウェブアプリケーション・ウェブサイトの開発環境です。",
  "icons": [
    {
      "src": "/images/icons/_icon_192_maskable.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/images/icons/_icon_192_maskable.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "maskable",
      "background_color": "#ffffff"
    },
    {
      "src": "/images/icons/_icon_512_maskable.png",
      "sizes": "512x512",
      "type": "image/png"
    },
    {
      "src": "/images/icons/_icon_512_maskable.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable",
      "background_color": "#ffffff"
    }
  ],
  "screenshots": [
    {
      "src": "/images/splashscreens/splashscreens_iphonexsmax_splash.png",
      "type": "image/png",
      "sizes": "1242x2688",
      "form_factor": "narrow"
    },
    {
      "src": "/images/splashscreens/splashscreens_ipadpro2_splash.png",
      "type": "image/png",
      "sizes": "2048x2732",
      "form_factor": "wide"
    }
  ]
}
```

ビルド時に生成されるファイルを、Gitの追跡対象外に加えます。（`/frontend/next/.gitignore`）に追加します。

```.gitignore

# PWA files（追加）
**/public/sw.js
**/public/workbox-*.js
**/public/worker-*.js
**/public/sw.js.map
**/public/workbox-*.js.map
**/public/worker-*.js.map
**/public/fallback-*.js
**/public/sitemap*.xml

```

---

## 2. オフライン用ページの作成

オフライン時に表示させるページファイル（`/frontend/next/public/offline.html`）も作成します。

```html
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>オフライン - Startify-App</title>
    <style>
      body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f5f5f5;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
      }
      
      .offline-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 40px;
        text-align: center;
        max-width: 400px;
        width: 90%;
      }
      
      .offline-icon {
        font-size: 48px;
        margin-bottom: 20px;
        color: #666;
      }
      
      .offline-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 16px;
        color: #333;
      }
      
      .offline-message {
        font-size: 16px;
        color: #666;
        line-height: 1.6;
        margin-bottom: 24px;
      }
      
      .retry-button {
        background-color: #000;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
      }
      
      .retry-button:hover {
        background-color: #333;
      }
      
      .retry-button:active {
        background-color: #555;
      }
    </style>
  </head>
  <body>
    <div class="offline-container">
      <div class="offline-icon">📱</div>
      <h1 class="offline-title">オフライン</h1>
      <p class="offline-message">
          現在インターネットに接続されていません。<br>
          ネットワーク接続を確認してから再度お試しください。
      </p>
      <button class="retry-button" onclick="window.location.reload()">
          再読み込み
      </button>
    </div>
  </body>
</html>
```

---

## 3. 画像ファイルの配置

設定終了後は、必要に応じて公開ディレクトリ配下にアイコン画像やスプラッシュスクリーン画像をそれぞれ格納します。

---

## 4. ビルドとテスト

PWA設定が正しく動作するかビルドコマンドでテストします。

```bash
npm run build
```

ビルドが成功すると、`public/`ディレクトリにサービスワーカー（`sw.js`）やその他のPWA関連ファイルが自動生成されます。

---
