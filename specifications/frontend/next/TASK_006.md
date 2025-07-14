---
title: Next.jsアプリケーション開発タスクリスト:グローバルステート管理（useContext）
id: next_task_006
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:グローバルステート管理（useContext）

useContextを使ったグローバルステート管理の処理を実装します。

---

## 1. サイトテーマ切り替え

### 1.1. ContextとProviderの作成

サイトのテーマ（Light or Dark）を切り替える処理とコンポーネントを作成し、テーマの値をグローバルステートで管理できるようにします。まずは、`/frontend/next/src/contexts/siteThemeContext.ts` と `/frontend/next/src/providers/SiteThemeProvider.tsx` を作成します。

```ts
'use client';

import { createContext, useContext } from 'react';

export type stateType = {
  currentTheme: 'light' | 'dark';
}

export type siteThemeContextType = {
  state: stateType;
  setState: React.Dispatch<React.SetStateAction<stateType>>;
};

export const defaultState: stateType = {
  currentTheme: 'light',
};

export const SiteThemeContext = createContext<siteThemeContextType>({
  state: defaultState,
  setState: () => {}
});

export const useSiteThemeContext = () => useContext(SiteThemeContext);

```

```tsx
'use client';

import { useState } from 'react';
import { SiteThemeContext, defaultState, stateType } from '@/contexts/siteThemeContext';

export default function SiteThemeProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = useState<stateType>(defaultState);
  const contextValue = { state, setState };

  return (
    <SiteThemeContext.Provider value={contextValue}>{children}</SiteThemeContext.Provider>
  );
}
```

### 1.2. ルートレイアウトコンポーネントでインポート

作成したProviderをルートレイアウトコンポーネント（`/frontend/next/src/app/layout.tsx`）でインポートし、アプリ内でグローバルステートとして扱えるようにします。

```tsx
import SiteThemeProvider from '@/providers/SiteThemeProvider'; // 追加

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body className={`${notoSansJP.variable}`}>
        <SiteThemeProvider> {/* 追加 */}
          <div className="app-layout">
            <Header />
            {children}
            <Footer />
          </div>
          <OffCanvas />
        </SiteThemeProvider>
      </body>
    </html>
  );
}
```

### 1.3. ステートのスコープ用ラッパーコンポーネントの追加

グローバル変数をカスタムデータ属性で扱えるよう、Provider直下に各要素を包括するラッパーコンポーネントを（`/frontend/next/src/components/Base.tsx`）として追加します。

```tsx
'use client';

import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';
import { useSiteThemeContext } from '@/contexts/siteThemeContext';

export default function Base({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const themeMode = useSiteThemeContext().state.currentTheme;

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

ラッパーコンポーネント追加に伴い、ルートレイアウトコンポーネントを修正します。

```tsx
import type { Viewport, Metadata } from 'next';
import '@/styles/globals.css';
import Base from '@/components/Base';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';
import SiteThemeProvider from '@/providers/SiteThemeProvider';

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
    </html>
  );
}
```

### 1.4. テーマ切り替え用のUIコンポーネント実装

グローバルステートの値を更新できる処理と操作できるUIコンポーネントを作成します。 `/frontend/next/src/components/ThemeSwitch.tsx` のコンポーネントを作成し、画面上に表示できるよう共通コンポーネント内で呼び出します。

```tsx
'use client';

import { useSiteThemeContext, stateType } from '@/contexts/siteThemeContext';

export default function ThemeSwitch() {
  const { state, setState } = useSiteThemeContext();

  const changeHandler = (event: React.ChangeEvent<HTMLInputElement>) => {
    const newTheme = event.target.value as stateType['currentTheme'];
    setState(prev => ({
      ...prev,
      currentTheme: newTheme
    }));
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
          checked={state.currentTheme === 'light'}
        />
        ライト
      </label>
      <label>
        <input 
          type="radio" 
          name="theme" 
          value="dark" 
          aria-label="Dark Mode" 
          onChange={changeHandler}
          checked={state.currentTheme === 'dark'}
        />
        ダーク
      </label>
    </>
  );
}
```

作成したテーマ切り替え用のUIコンポーネントを共通コンポーネントに含めます。今回はオフキャンバス要素（`/frontend/next/src/components/OffCanvas.tsx`）の中に配置します。

```tsx
import ThemeSwitch from '@/components/ThemeSwitch';

export default function OffCanvas() {
  return (
    <div className="app-offcanvas">
      オフキャンバス要素
      <ThemeSwitch />
    </div>
  );
} 
```

スタイルシート（`/frontend/next/src/styles/globals.css`）でテーマ切り替えに合わせてデザインが変わるように修正し
ます。ここではメディアクエリではなく、data-theme属性の値で分岐するようにします。

```css
:root {
  --background: #fff;
  --foreground: #171717;
}

@theme inline {
  --color-background: var(--background);
  --color-foreground: var(--foreground);
}

/* @media (prefers-color-scheme: dark) {
  :root {
    --background: #0a0a0a;
    --foreground: #ededed;
  }
} */

body:has([data-theme="dark"]) {
  --background: #0a0a0a;
  --foreground: #ededed;
}

body {
  background: var(--background);
  color: var(--foreground);
  font-family: var(--font-noto-sans-jp);
}
```

---
