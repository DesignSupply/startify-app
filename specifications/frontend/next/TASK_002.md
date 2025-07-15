---
title: Next.jsアプリケーション開発タスクリスト:プロジェクトディレクトリ再構成、共通コンポーネント作成
id: next_task_002
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:プロジェクトディレクトリ再構成、共通コンポーネント作成

既存のファイルディレクトリの再構成とアプリケーション内の共通コンポーネントを作成します。

---

## 1. ソースコードと静的アセットファイルディレクトリ内の再構成

下記の通りにソースコードディレクトリと静的アセットファイルディレクトリ配下に用途に合わせたディレクトリを作成し、その中に対応するファイルが格納されるよう再構成を行います。

```
/next
├── /public
│   ├── favicon.svg
│   ├── /assets
│   │   ├── images
│   │   ├── fonts
├── /src
│   ├── /app
│   │   ├── layout.tsx
│   │   ├── page.tsx
│   │   ├── /example
│   │   │   └── page.tsx
│   ├── /components
│   │   ├── Header.tsx
│   │   ├── Footer.tsx
│   ├── /features
│   ├── /hooks
│   ├── /utils
│   ├── /contexts
│   ├── /providers
│   ├── /styles
│   │   ├── globals.css
│   └── /types
```

---

## 2. ルートレイアウトコンポーネント（/src/app/layout.tsx）の作成

ルートレイアウトのコンポーネント（`/frontend/next/src/app/layout.tsx`）を下記のように作成します。

```tsx
import '@/styles/globals.css';

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body>
        <div className="app-layout">
          {children}
        </div>
      </body>
    </html>
  );
}
```

---

## 3. トップページコンポーネント（/src/app/page.tsx）の作成

トップページのページコンポーネント（`/frontend/next/src/app/page.tsx`）を下記のように作成します。

```tsx
export default function Home() {
  return (
    <main className="app-main">
      <h1>トップページ</h1>
    </main>
  );
}

```

---

## 4. 共通コンポーネントの作成

下記の通り、アプリケーション内において共通のコンポーネントを作成します。

- `/frontend/next/src/components/Header.tsx` : ヘッダーコンポーネント
- `/frontend/next/src/components/Footer.tsx` : フッターコンポーネント
- `/frontend/next/src/components/OffCanvas.tsx` : オフキャンバス要素コンポーネント

```tsx
export default function Header() {
  return (
    <header className="app-header">ヘッダー</header>
  );
}

export default function Footer() {
  return (
    <footer className="app-footer">フッター</footer>
  );
}

export default function OffCanvas() {
  return (
    <div className="app-offcanvas">オフキャンバス要素</div>
  );
}
```

ルートレイアウトコンポーネント側で各コンポーネントを呼び出します。

```tsx
import '@/styles/globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';

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
}
```

---
