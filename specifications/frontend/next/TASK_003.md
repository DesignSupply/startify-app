---
title: Next.jsアプリケーション開発タスクリスト:メタデータ設定
id: next_task_003
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:メタデータ設定

各ページにおけるメタデータ（共通・ページ固有）を設定します。

---

## 1. デフォルトメタデータ設定

デフォルトの各種メタデータが指定されたスクリプトを、`/frontend/next/src/utils/meta.ts` へ下記の通り作成します。

```ts
import type { Metadata } from 'next';

export const meta: Metadata = {
  metadataBase: new URL(`${process.env.APPURL}`),
  title: `${process.env.APPNAME}`,
  description: `${process.env.APPDESCRIPTION}`,
  applicationName: `${process.env.APPNAME}`,
  authors: {
    name: `${process.env.APPAUTHOR}`,
    url: `${process.env.APPURL}`
  },
  generator: 'Next.js',
  keywords: ['ウェブサイト制作', 'アプリケーション開発'],
  referrer: 'origin',
  creator: `${process.env.APPAUTHOR}`,
  publisher: `${process.env.APPAUTHOR}`,
  robots: {
    index: true,
    follow: true
  },
  alternates: { canonical: `${process.env.APPURL}` },
  icons: [
    {
      rel: 'icon',
      url: `${process.env.APPURL}/favicon.svg`,
      sizes: 'any',
      type: 'image/svg+xml'
    },
    {
      rel: 'apple-touch-icon',
      url: `${process.env.APPURL}/assets/images/apple_touch.png`
    }
  ],
  manifest: `${process.env.APPURL}/manifest.json`,
  openGraph: {
    type: 'website',
    url: `${process.env.APPURL}`,
    title: `${process.env.APPNAME}`,
    description: `${process.env.APPDESCRIPTION}`,
    siteName: `${process.env.APPNAME}`,
    locale: 'ja_JP',
    images: [{ url: `/assets/images/ogp.png` }]
  },
  twitter: {
    card: 'summary',
    site: '', // @X_USER_NAME
    title: `${process.env.APPNAME}`,
    description: `${process.env.APPDESCRIPTION}`,
    images: [{ url: `/assets/images/icons/ogp.png` }]
  },
  verification: {
    google: ''
  },
  appleWebApp: {
    capable: true,
    title: `${process.env.APPNAME}`,
    statusBarStyle: 'black-translucent',
    startupImage: iosSplashScreens
  },
  formatDetection: {
    telephone: false,
    address: false,
    email: false
  },
  other: {
    'msvalidate.01': '',
    'p:domain_verify': ''
  }
};
```

---

## 2. スプラッシュスクリーン設定の追加

PWA対応としてスプラッシュスクリーンのリストを `/frontend/next/src/utils/meta.ts` へ追加します。（対象となる端末はその都度検討する）


```ts
import type { Metadata } from 'next';

// 追加
export const iosSplashScreens = [
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_16_Pro_Max_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_16_Pro_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_16_Plus__iPhone_15_Pro_Max__iPhone_15_Plus__iPhone_14_Pro_Max_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_16__iPhone_15_Pro__iPhone_15__iPhone_14_Pro_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_14_Plus__iPhone_13_Pro_Max__iPhone_12_Pro_Max_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_14__iPhone_13_Pro__iPhone_13__iPhone_12_Pro__iPhone_12_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_13_mini__iPhone_12_mini__iPhone_11_Pro__iPhone_XS__iPhone_X_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_11_Pro_Max__iPhone_XS_Max_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_11__iPhone_XR_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_8_Plus__iPhone_7_Plus__iPhone_6s_Plus__iPhone_6_Plus_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/iPhone_8__iPhone_7__iPhone_6s__iPhone_6__4.7__iPhone_SE_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/4__iPhone_SE__iPod_touch_5th_generation_and_later_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 1032px) and (device-height: 1376px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/13__iPad_Pro_M4_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/12.9__iPad_Pro_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 834px) and (device-height: 1210px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/11__iPad_Pro_M4_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/11__iPad_Pro__10.5__iPad_Pro_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/10.9__iPad_Air_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/10.5__iPad_Air_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/10.2__iPad_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/9.7__iPad_Pro__7.9__iPad_mini__9.7__iPad_Air__9.7__iPad_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)',
    url: '/assets/images/splashscreens/ios/8.3__iPad_Mini_landscape.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_16_Pro_Max_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_16_Pro_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_16_Plus__iPhone_15_Pro_Max__iPhone_15_Plus__iPhone_14_Pro_Max_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_16__iPhone_15_Pro__iPhone_15__iPhone_14_Pro_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_14_Plus__iPhone_13_Pro_Max__iPhone_12_Pro_Max_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_14__iPhone_13_Pro__iPhone_13__iPhone_12_Pro__iPhone_12_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_13_mini__iPhone_12_mini__iPhone_11_Pro__iPhone_XS__iPhone_X_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_11_Pro_Max__iPhone_XS_Max_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_11__iPhone_XR_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_8_Plus__iPhone_7_Plus__iPhone_6s_Plus__iPhone_6_Plus_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/iPhone_8__iPhone_7__iPhone_6s__iPhone_6__4.7__iPhone_SE_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/4__iPhone_SE__iPod_touch_5th_generation_and_later_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 1032px) and (device-height: 1376px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/13__iPad_Pro_M4_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/12.9__iPad_Pro_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 834px) and (device-height: 1210px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/11__iPad_Pro_M4_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/11__iPad_Pro__10.5__iPad_Pro_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/10.9__iPad_Air_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/10.5__iPad_Air_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/10.2__iPad_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/9.7__iPad_Pro__7.9__iPad_mini__9.7__iPad_Air__9.7__iPad_portrait.png'
  },
  {
    rel: 'apple-touch-startup-image',
    media:
      'screen and (device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)',
    url: '/assets/images/splashscreens/ios/8.3__iPad_Mini_portrait.png'
  }
];

export const meta: Metadata = {
```

---

## 3. ルートレイアウトコンポーネントへのデフォルトメタデータ設定

デフォルトのメタデータをルートレイアウトコンポーネント（`/frontend/next/src/app/layout.tsx`）に設定するよう更新します。


```tsx
import type { Viewport, Metadata } from 'next'; // 追加
import '@/styles/globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';
import { metaDefault } from '@/utils/meta'; // 追加

export const metadata: Metadata = metaDefault; // 追加
export const viewport: Viewport = { 
  themeColor: '#000000',
  colorScheme: 'light dark',
  width: 'device-width',
  initialScale: 1
}; // 追加
export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body>
        <div className="app-layout">
          <Header />
          {children}
          <Footer />
        </div>
        <OffCanvas />
      </body>
    </html>
  );
};
```

---

## 4. JSON-LD出力用スクリプト、コンポーネントの作成

JSON-LDを出力する処理とコンポーネントを（`/frontend/next/src/components/JsonLd.tsx`）に作成し実装します。

```tsx
'use client';

export type propsType = {
  jsonLd: {
    '@type': string;
    position: number;
    item: {
      '@id': string;
      name: string;
    };
  }[];
};

export default function JsonLd(props: propsType) {
  const jsonData = {
    '@context': 'http://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: props.jsonLd
  };
  
  return (
    <>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonData) }} />
    </>
  );
} 

```

作成したコンポーネントはページコンポーネント側で読み込み、各種ページに合わせた構造化データを受け渡します。ここではトップページコンポーネント（`/frontend/next/src/app/page.tsx`）に設定します。

```tsx
import JsonLd from '@/components/JsonLd';

export default function HomePage() {
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}`, name: 'HOME' }
    }
  ];

  return (
    <main>
      <h1>トップページ</h1>
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}

```
