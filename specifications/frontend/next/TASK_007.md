---
title: Next.jsアプリケーション開発タスクリスト:グーグルアナリティクス、アドセンス、サイトマップ導入
id: next_task_007
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:グーグルアナリティクス、アドセンス、サイトマップ導入

Next.jsのプロジェクトに、グーグルアナリティクスとアドセンス、サイトマップを導入します。

---

## 1. グーグルアナリティクスの導入

グーグルアナリティクスは公式のライブラリを利用します。最初にモジュールをインストールします。

```bash
npm install @next/third-parties@latest
```

モジュールインストール後、ルートレイアウトコンポーネント（`/frontend/next/src/app/layout.tsx`）でグーグルアナリティクスをインポートします。GA4のタグIDは環境変数から参照し、開発環境下では読み込まないよう分岐処理を加えておきます。

```tsx
import type { Viewport, Metadata } from 'next';
import '@/styles/globals.css';
import Base from '@/components/Base';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';
import SiteThemeProvider from '@/providers/SiteThemeProvider';
import { GoogleAnalytics } from '@next/third-parties/google'; // 追加

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
      <body className={`${notoSansJP.variable}`}>
        <SiteThemeProvider>
          <Base>{children}</Base>
        </SiteThemeProvider>
      </body>
      {/* 追加 */}
      {process.env.NODE_ENV !== 'development' && 
       process.env.GOOGLE_ANALYTICS_ID && (
        <GoogleAnalytics gaId={process.env.GOOGLE_ANALYTICS_ID} />
      )}
    </html>
  );
}
```

環境変数の設定は `/frontend/next/.env.local` ファイルで行います。ファイルを作成し、下記のようにタグIDを記述します。

```bash
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

---

## 2. アドセンスの導入

### 2.1. スクリプト作成

アドセンスの広告ユニットを表示できるようにします。まずはアドセンスのスクリプト用コンポーネントを `/frontend/next/src/components/GoogleAdsenseScript.tsx` で作成します。

```tsx
'use client';

import Script from 'next/script';

export default function GoogleAdsenseScript() {
  return (
    <>
      <Script
        async
        src={`https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${process.env.GOOGLE_ADSENSE_ID}`}
        crossOrigin="anonymous"
        strategy="afterInteractive"
      />
    </>
  );
}
```

環境変数の設定は `/frontend/next/.env.local` ファイルで行います。ファイルを作成し、下記のようにタグIDを記述します。

```bash
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
GOOGLE_ADSENSE_ID=ca-pub-XXXXXXXXXX # 追加
```

作成したアドセンス用のスクリプトを、ルートレイアウトコンポーネント（`/frontend/next/src/app/layout.tsx`）でインポートします。コンソールエラーが出ないように開発環境では出力されないようにします。

```tsx
import type { Viewport, Metadata } from 'next';
import '@/styles/globals.css';
import Base from '@/components/Base';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';
import SiteThemeProvider from '@/providers/SiteThemeProvider';
import { GoogleAnalytics } from '@next/third-parties/google'; // 追加
import GoogleAdsenseScript from '@/components/GoogleAdsenseScript'; // 追加

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
      <body className={`${notoSansJP.variable}`}>
        <SiteThemeProvider>
          <Base>{children}</Base>
        </SiteThemeProvider>
      </body>
      {
        process.env.NODE_ENV !== 'development' && 
        process.env.GOOGLE_ANALYTICS_ID && (
          <GoogleAnalytics gaId={process.env.GOOGLE_ANALYTICS_ID} />
        )
      }
      {/* 追加 */}
      {
        process.env.NODE_ENV !== 'development' && 
        process.env.GOOGLE_ADSENSE_ID && (
          <GoogleAdsenseScript />
        )
             }
     </html>
   );
 }
 ```

### 2.2. 広告ユニットコンポーネント作成

汎用的に使えるアドセンスの広告ユニット用のコンポーネントを（`/frontend/next/src/components/AdsenseUnit.tsx`）で作成します。コンポーネントのpropsにスロットIDが入るようにします。また、複数回読み込まれないようにします。

```tsx
'use client';

import { useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';

type propsType = {
  slot: string;
  format: string;
};

declare global {
  interface Window {
    adsbygoogle: { [key: string]: unknown }[];
  }
}

export default function AdsenceUnit(props: propsType) {
  const didEffect = useRef(false);
  const currentPath = usePathname();
  useEffect(() => {
    if (!didEffect.current) {
      didEffect.current = true;
      try {
        if (process.env.NODE_ENV !== 'development') {
          (window.adsbygoogle = window.adsbygoogle || []).push({});
        }
      } catch (err) {
        console.error(err);
      }
    }
  }, [currentPath]);

  return (
    <>
      {process.env.NODE_ENV !== 'development' && (
        <ins
          className="adsbygoogle"
          style={{ display: 'block' }}
          data-ad-client={process.env.GOOGLE_ADSENSE_ID}
          data-ad-slot={props.slot}
          data-ad-format={props.format}
          data-full-width-responsive="true"
        />
             )}
     </>
   );
 }
 ```

作成した広告ユニットコンポーネントを、表示させたい箇所でインポートします。ここではサンプルとしてトップページコンポーネント（`/frontend/next/src/app/page.tsx`）で使用しています。

```tsx
import JsonLd from '@/components/JsonLd';
import AdsenseUnit from '@/components/AdsenseUnit'; // 追加

export default function HomePage() {
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}`, name: 'HOME' }
    }
  ];

  return (
    <main className="app-main">
      <h1>トップページ</h1>
      {/* 追加 */}
      <AdsenseUnit slot="4034159997" format="auto" />
      <JsonLd jsonLd={jsonLdData} />
         </main>
   );
 }
 ```

---

## 3. サイトマップの導入

Next.jsのプロジェクトでサイトマップが作成できるようにします。公式のライブラリを使用します。

```bash
npm install next-sitemap
```

モジュールインストール後に、ビルド時にサイトマップが生成されるよう、 `/frontend/next/package.json` にサイトマップ生成コマンドを追加します。

```json
  ...
  "scripts": {
    ...
    "build": "next build && next-sitemap", // 更新
    ...
  }
  ...
```

---