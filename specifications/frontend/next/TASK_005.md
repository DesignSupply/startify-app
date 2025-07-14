---
title: Next.jsアプリケーション開発タスクリスト:静的ルーティングページ、404エラーページ追加
id: next_task_005
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:静的ルーティングページ、404エラーページ追加

静的ルーティングを持つページコンポーネントと、404エラーページテンプレートを作成します。

---

## 1. 静的ルーティングページの作成

静的ルーティングを持つページを作成します、ページコンポーネントを `/frontend/next/src/app/example/` のディレクトリ配下に作成し、`http://localhost:3000/example` でアクセスできるようにします。またページ固有のメタデータ設定も合わせて対応します。


```tsx
import type { Metadata } from 'next';
import JsonLd from '@/components/JsonLd';
import { metaDefault } from '@/utils/meta';

export const metadata: Metadata = {
  title: `静的ルーティングページサンプル | ${process.env.APPNAME}`,
  alternates: { canonical: `${process.env.APPURL}/example` },
  openGraph: {
    type: 'article',
    url: `${process.env.APPURL}/example`,
    title: `静的ルーティングページサンプル | ${process.env.APPNAME}`,
    description: metaDefault.openGraph?.description,
    siteName: metaDefault.openGraph?.siteName,
    locale: metaDefault.openGraph?.locale,
    images: metaDefault.openGraph?.images
  },
  twitter: {
    site: metaDefault.twitter?.site,
    title: `静的ルーティングページサンプル | ${process.env.APPNAME}`,
    description: metaDefault.twitter?.description,
    images: metaDefault.twitter?.images
  }
};

export default function ExamplePage() {
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}`, name: 'HOME' }
    },
    {
      '@type': 'ListItem',
      position: 2,
      item: { '@id': `${process.env.APPURL}/example`, name: '静的ルーティングページサンプル' }
    }
  ];

  return (
    <main className="app-main">
      <h1>静的ルーティングページサンプル</h1>
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}
```

---

## 2. 404エラーページの作成

404エラーページを作成します、`/frontend/next/src/app/not-found.tsx` を以下のように作成します。Next.js 15.x系では`not-found.tsx`で`metadata`のエクスポートができないため、`useEffect`でクライアントサイドでタイトルを設定します。

```tsx
'use client';

import { useEffect } from 'react';

export default function NotFoundErrorPage() {
  useEffect(() => {
    document.title = `お探しのページが見つかりません | ${process.env.NEXT_PUBLIC_APPNAME || 'サイト名'}`;
  }, []);

  return (
    <main className="app-main">
      <h1>404 Error Page Not Found</h1>
      <p>お探しのページが見つかりません</p>
    </main>
  );
}
```

---
