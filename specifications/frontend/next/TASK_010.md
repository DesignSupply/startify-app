---
title: Next.jsアプリケーション開発タスクリスト:グローバルステート管理のライブラリ移行（Zustand）
id: next_task_010
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:グローバルステート管理のライブラリ移行（Zustand）

ProviderコンポーネントでのContextの状態管理を、Zustandのライブラリを使用した状態管理に移行します。サーバー側の状態管理についてはそのままReactQueryで管理するようにします。

**補足**
- 実施内容: `zustand` の追加（本体のみ）
- 非対象: `persist` や `devtools` などのミドルウェア導入（後続フェーズで必要時に検討）
- 互換性: React 19 / Next 15と互換。TypeScript型定義は同梱のため追加不要

---

## 1. ライブラリのインストール

UI側のContext状態をZustandで置き換えるための最小限の依存追加のみを行います。その他ライブラリのバージョン変更やUI/UXの変更は行いません。下記手順でライブラリをインストールします。

```bash
cd ./frontend/next
npm install zustand@^5
```

**注意事項**
- 他依存のバージョンは変更しない（本プロジェクトのバージョン方針に従う）
- `zustand` はメジャー 5系に固定して導入する（`npm install zustand@^5` を使用し、将来的なメジャー自動更新を防ぐ）

---

## 2. ストア作成

ここではUIテーマ状態（`light` / `dark`）を保持・更新するZustandストアを新規作成し、以後はこのストアから状態を取得・更新します。サーバー側・APIの状態管理（React Query）は対象外です。

**追加/編集ファイル**
- 追加: `frontend/next/src/stores/siteThemeStore.ts`

**実装要件**
- ファイル先頭に `'use client'` を付与（クライアント専用）
- 状態: `currentTheme: 'light' | 'dark'`（初期値 `'light'`）
- アクション: `setTheme(mode)`（同期更新で十分）
- エクスポート名: `useSiteThemeStore`（Zustandのフック）
- セレクターで必要な値のみ購読できる構成にする（再レンダリング抑制のため）

**実装例**
```ts
'use client';

import { create } from 'zustand';

export type ThemeMode = 'light' | 'dark';

type SiteThemeState = {
  currentTheme: ThemeMode;
  setTheme: (mode: ThemeMode) => void;
};

export const useSiteThemeStore = create<SiteThemeState>((set) => ({
  currentTheme: 'light',
  setTheme: (mode) => set({ currentTheme: mode }),
}));
```

**命名・利用ルール**
- 取得はセレクターを用いる（例: `useSiteThemeStore(s => s.currentTheme)`）
- 更新はアクションを直接呼ぶ（例: `useSiteThemeStore(s => s.setTheme)`）
- 既存のContext由来の型・関数名（`stateType`, `useSiteThemeContext`）は使用しない

**注意事項**
- 永続化やDevTools等のミドルウェアは本フェーズでは導入しない（必要時は別フェーズで検討）
- SSRで使用しない（クライアントコンポーネント内でのみ使用）

---

## 3. カスタムフックを使ったコンポーネント側の取得更新処理追加

Zustandのストア（`useSiteThemeStore`）を用いて、既存のContext依存箇所を段階的に置換します。まずはUIテーマの取得・更新を行うコンポーネントから適用します。

**対象/編集ファイル**
 - 編集: `frontend/next/src/components/ThemeSwitch.tsx`
 - 編集: `frontend/next/src/components/Base.tsx`

**変更要件（共通）**
 - 先頭の `'use client'` は維持すること
 - `@/contexts/siteThemeContext` からのimportを削除
 - `@/stores/siteThemeStore` から `useSiteThemeStore` をimport
 - 取得はセレクター、更新はアクションを直接呼ぶ

**変更例（ThemeSwitch.tsx）**
```tsx
'use client';
import { useSiteThemeStore } from '@/stores/siteThemeStore';
// import { useSiteThemeContext } from '@/contexts/siteThemeContext'; // context

export default function ThemeSwitch() {
  const theme = useSiteThemeStore((s) => s.currentTheme);
  const setTheme = useSiteThemeStore((s) => s.setTheme);
  // const { state, setState } = useSiteThemeContext(); // context
 
  const changeHandler = (event: React.ChangeEvent<HTMLInputElement>) => {
    const newTheme = event.target.value as 'light' | 'dark';
    setTheme(newTheme);
    // setState((prev) => ({ ...prev, currentTheme: newTheme })); // context
  };

  return (
    <>
      <label>
        <input
          type="radio"
          name="theme"
          value="light"
          aria-label="Light Mode"
          onChange={changeHandler}
          checked={theme === 'light'}
         />
        ライト
        {/* context: checked={state.currentTheme === 'light'} */}
       </label>
       <label>
        <input
          type="radio"
          name="theme"
          value="dark"
          aria-label="Dark Mode"
          onChange={changeHandler}
          checked={theme === 'dark'}
        />
        ダーク
        {/* context: checked={state.currentTheme === 'dark'} */}
      </label>
    </>
  );
}
```

**変更例（Base.tsx）**
```tsx
'use client';

import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';
import { useSiteThemeStore } from '@/stores/siteThemeStore';
// import { useSiteThemeContext } from '@/contexts/siteThemeContext'; // context

export default function Base({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  // const themeMode = useSiteThemeContext().state.currentTheme; // context
  const themeMode = useSiteThemeStore((s) => s.currentTheme);

  return (
    <div className="app-base" data-theme={themeMode}>
      <div className="app-layout">
        <Header />
        {children}
        <Footer />
      </div>
      <OffCanvas />
    </div>
  );
}
```

**注意事項**
 - まだProviderの撤去は行わない（次フェーズで対応）。この段階ではContextとZustandが共存しても実害のない形に留める
 - セレクターは必要な値に限定し、再レンダリングを最小化する

---

## 4. Contextで使用しているProviderの置換

ContextのProviderラップを撤去し、Zustandストア前提の構成に置き換えます。まずは `app/layout.tsx` から `SiteThemeProvider` のimportとJSXラップを外します。

**対象/編集ファイル**
- 編集: `frontend/next/src/app/layout.tsx`

**変更要件**
- `import SiteThemeProvider from '@/providers/SiteThemeProvider';` を削除（コメントで残す）
- JSXの `<SiteThemeProvider> ... </SiteThemeProvider>` を削除（コメントで残す）
- `ReactQueryProvider` や外部スクリプト（GA/Adsense）は現状維持
- `Base` 配下でZustandの値を参照するため、機能的な差分は発生しないこと

**変更例（layout.tsx）**
```tsx
import type { Viewport, Metadata } from 'next';
import { Suspense } from 'react';
import '@/styles/globals.css';
import Base from '@/components/Base';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';
// import SiteThemeProvider from '@/providers/SiteThemeProvider'; // context
import ReactQueryProvider from '@/providers/ReactQueryProvider';
import { GoogleAnalytics } from '@next/third-parties/google';
import GoogleAdsenseScript from '@/components/GoogleAdsenseScript';

export const metadata: Metadata = metaDefault;
export const viewport: Viewport = {
  themeColor: '#000000',
  colorScheme: 'light dark',
  width: 'device-width',
  initialScale: 1,
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body className={`${notoSansJP.variable}`}>
        {/* <SiteThemeProvider> */} {/* context */}
          <ReactQueryProvider>
            <Suspense>
              <Base>{children}</Base>
            </Suspense>
          </ReactQueryProvider>
        {/* </SiteThemeProvider> */} {/* context */}
      </body>
      {process.env.NODE_ENV !== 'development' && process.env.GOOGLE_ANALYTICS_ID && (
        <GoogleAnalytics gaId={process.env.GOOGLE_ANALYTICS_ID} />
      )}
      {process.env.NODE_ENV !== 'development' && process.env.GOOGLE_ADSENSE_ID && (
        <GoogleAdsenseScript />
      )}
    </html>
  );
}
```

**注意事項**
- この段階では `providers/SiteThemeProvider.tsx` と `contexts/siteThemeContext.ts` のファイル削除は行わない（次フェーズの検証後に削除）
- すでに `ThemeSwitch` / `Base` はZustandに切り替済みであることが前提

---

## 5. ライブラリ移行後の状態管理テスト

移行完了後に、以下の動作確認を行います（フェーズ横断の事後チェックを集約）。

**動作確認リスト**
- ビルドが成功する（`npm run build`）
- ランタイムエラーやTypeScriptエラーが発生しない（未使用importは削除済み）
- `ThemeSwitch` の切替で `Base` の `data-theme` が即時に `light/dark` へ更新される
- `SiteThemeProvider` のimport/JSX撤去後も未解決参照がない
- 画面表示・テーマ切替・認証/遷移の動作が従来どおりの挙動である

---
