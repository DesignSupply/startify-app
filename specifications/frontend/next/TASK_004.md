---
title: Next.jsアプリケーション開発タスクリスト:フォント最適化
id: next_task_004
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:フォント最適化

ウェブフォント、およびローカルのフォントデータ読み込みの最適化を行います。

---

## 1. ウェブフォント（GoogleFonts）の使用設定

デフォルトの各種メタデータが指定されたスクリプトを、`/frontend/next/src/utils/fonts.ts` へ下記の通り作成します。ここでは「Noto Sans JP」のフォントを設定します。

```ts
import { Noto_Sans_JP } from 'next/font/google';

export const notoSansJP = Noto_Sans_JP({
  subsets: ['latin'],
  variable: '--font-noto-sans-jp',
  weight: 'variable',
  display: 'swap',
  preload: true
});
```

スタイルシート側（`/frontend/next/src/styles/globals.css`）でフォント設定に反映させます。

```css
body {
  background: var(--background);
  color: var(--foreground);
  font-family: var(--font-noto-sans-jp); /* 更新 */
}
```

ルートレイアウトコンポーネント（`/frontend/next/src/app/layout.tsx`）でフォント設定をインポートします。

```tsx
import type { Viewport, Metadata } from 'next';
import '@/styles/globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts'; // 追加

export const metadata: Metadata = metaDefault;
export const viewport: Viewport = { 
  themeColor: '#000000',
  colorScheme: 'light dark',
  width: 'device-width',
  initialScale: 1
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body className={notoSansJP.variable}> {/* 更新 */}
        <div className="app-layout">
          <Header />
          {children}
          <Footer />
        </div>
        <OffCanvas />
      </body>
    </html>
  );
}
```

---

## 2. ローカルフォントデータの使用設定

ウェブフォント以外のローカルで参照するフォントデータの設定を `/frontend/next/src/utils/fonts.ts` に追加します。フォントデータは `/frontend/next/public/fonts/` 配下に格納する前提で設定しています。

```ts
import { Noto_Sans_JP } from 'next/font/google';
import localFont from 'next/font/local'; // 追加

export const notoSansJP = Noto_Sans_JP({
  subsets: ['latin'],
  variable: '--font-noto-sans-jp',
  weight: 'variable',
  display: 'swap',
  preload: true
});

// 追加
export const customFont = localFont({
  variable: '--font-custom',
  src: [
    {
      path: '/fonts/*******.woff2',
      weight: '100',
      style: 'normal'
    },
    {
      path: '/fonts/*******.woff2',
      weight: '200',
      style: 'normal'
    },
    {
      path: '/fonts/*******.woff2',
      weight: '300',
      style: 'normal'
    },
    {
      path: '/fonts/*******.woff2',
      weight: '400',
      style: 'normal'
    },
    {
      path: '/fonts/*******.woff2',
      weight: '500',
      style: 'normal'
    },
    {
      path: '/fonts/*******.woff2',
      weight: '700',
      style: 'normal'
    },
    {
      path: '/fonts/*******.woff2',
      weight: '800',
      style: 'normal'
    },
    {
      path: '/fonts/*******.woff2',
      weight: '900',
      style: 'normal'
    }
  ]
});

使用する場合には、同様にルートレイアウトコンポーネント（`/frontend/next/src/app/layout.tsx`）でフォント設定をインポートします。

```

---
