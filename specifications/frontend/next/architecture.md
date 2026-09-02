---
title: Next.jsアプリケーション アーキテクチャ仕様
status: current
last_updated: 2026-09-02
related_paths:
  - frontend/next/.nvmrc
  - frontend/next/package.json
  - frontend/next/package-lock.json
  - frontend/next/next.config.mjs
  - frontend/next/tsconfig.json
  - frontend/next/.env.example
  - frontend/next/src/app/
  - frontend/next/src/components/
  - frontend/next/src/providers/
  - frontend/next/src/stores/
---

# Next.jsアプリケーション アーキテクチャ仕様

Startify-AppのNext.jsアプリケーションにおける、現在の実行環境、レンダリング方式、ディレクトリ責務、ルーティング、コンポーネント境界を定義します。

認証、データ取得、メタデータ、PWA、テストの詳細は、それぞれの現在仕様へ順次分離します。本書では、それらを接続するアプリケーション全体の構造を扱います。

## 1. 技術構成

主要な実行環境とライブラリは次のとおりです。正確な依存バージョンは `frontend/next/package.json` と `frontend/next/package-lock.json` を正本とします。

| 項目 | 現在の構成 |
| --- | --- |
| Node.js | `22.12.0`（`frontend/next/.nvmrc`） |
| npm | `10.8.2` |
| Next.js | `15.5.22`、App Router |
| React | `19` |
| 言語 | TypeScript、`strict: true` |
| スタイル | グローバルCSS。Tailwind CSS 4は設定済みだがUtility Class未使用、Sassは設定済みだがSCSS未使用 |
| UI状態 | Zustand |
| API・サーバー状態 | TanStack Query |
| スキーマ・フォーム検証 | Zod、React Hook Form |
| テスト | Vitest、React Testing Library、jsdom |
| 配信形式 | Static Export |

Node.jsはホストOSで実行し、`server/` のDocker環境には含めません。

## 2. 実行・配信方式

`frontend/next/next.config.mjs` では `output: 'export'` を有効にし、ビルド成果物を `frontend/next/out/` に生成します。

現在の主な制約は次のとおりです。

- サーバー常駐を前提とするSSRやNext.jsの実行時APIを使用しない
- URLは末尾スラッシュを使用する（`trailingSlash: true`）
- Next.js Image Optimizationサーバーを使用しない（`images.unoptimized: true`）
- 動的ルートはビルド時に公開対象パラメーターを確定する
- 環境変数はStatic Exportのビルド時に解決される
- `out/`、`.next/`、PWA生成物はGit管理対象外とする

Cloudflareへの配信構成、環境区分、CI、デプロイ手順の詳細は、[Next.js Static Export — Cloudflare Workers Static Assets デプロイ仕様](../../infrastructure/cloudflare/next-static-deployment.md) を参照してください。

## 3. ルーティング

ルーティングにはApp Routerを使用します。現在のページ構成は次のとおりです。

| URL | ルートファイル | 概要 |
| --- | --- | --- |
| `/` | `src/app/page.tsx` | トップページ |
| `/example/` | `src/app/example/page.tsx` | 静的ルーティングのサンプル |
| `/signin/` | `src/app/signin/page.tsx` | サインイン |
| `/dashboard/` | `src/app/(auth)/dashboard/page.tsx` | 認証保護されたダッシュボード |
| `/posts/` | `src/app/(auth)/posts/page.tsx` | 認証保護された投稿一覧 |
| `/posts/[id]/` | `src/app/(auth)/posts/[id]/page.tsx` | ビルド時生成される投稿詳細 |
| 存在しないURL | `src/app/not-found.tsx` | 404ページ |

`(auth)` はURLに現れないRoute Groupです。配下のページは `src/app/(auth)/layout.tsx` で `AuthGuard` に包み、クライアント側で認証状態を確認します。

投稿詳細では `generateStaticParams()` と `dynamicParams = false` を使用します。ビルド時に生成されなかったIDを、配信後に動的生成することはできません。

## 4. Server ComponentとClient Component

App Routerのページとレイアウトは、ブラウザーAPIやクライアントフックが必要でない限りServer Componentを基本とします。

現在のルートでは、ページの構造とビルド時処理を `page.tsx`、ブラウザー上の状態や操作を `_content.tsx` に分離しています。

```text
src/app/[route]/
├── page.tsx       # Server Component: ページ構造、メタデータ、ビルド時処理
└── _content.tsx   # Client Component: フック、状態、イベント、ブラウザーAPI
```

- `'use client'` は必要な境界にだけ指定する
- Server ComponentからClient Componentへ渡す値はシリアライズ可能にする
- ページ全体を安易にClient Componentへ変更しない
- クライアントフックを使う部分は、ルートコンポーネントから分離する
- ビルド時にファイルを読む処理はServer Component側に置く

投稿詳細ページは、Server ComponentでモックJSONを読み込み、Zodで検証してから、Client Componentへ `initialPost` として渡します。Client Component側では、その値をTanStack Queryの初期データとして利用します。

## 5. ルートレイアウトとProvider

`src/app/layout.tsx` はアプリケーション全体のルートレイアウトです。主に次を担当します。

- 共通MetadataとViewport
- Noto Sans JPのフォント変数
- グローバルスタイル
- `ReactQueryProvider`
- `Base`による共通レイアウト
- Google Analytics／AdSenseの環境別出力

`Base` はClient Componentで、Header、Footer、OffCanvasとページコンテンツをまとめます。テーマ状態は `src/stores/siteThemeStore.ts` のZustandストアから取得し、`data-theme` 属性へ反映します。

TanStack Queryの `QueryClient` は `src/providers/ReactQueryProvider.tsx` でブラウザーセッションごとに生成します。API状態をZustandへ保存せず、TanStack Queryで管理します。

旧useContext実装の次のファイルは、移行記録として残っていますが、現在のProviderツリーでは使用しません。

- `src/contexts/_siteThemeContext.ts`
- `src/providers/_SiteThemeProvider.tsx`

新しい実装からこれらへ依存しないでください。

## 6. 状態管理の使い分け

状態は、その責務と共有範囲に応じて管理方法を選択します。

| 状態の種類 | 管理方法 | 現在の例 |
| --- | --- | --- |
| Component内で完結する一時状態 | React Local State | Form送信時のAPI Error |
| Form入力とValidation | React Hook FormとZod | Signin Form |
| URL共有、Reload、戻る・進むに対応する状態 | Next.js RouterとSearch Params | 投稿一覧の `page`、Signinの `next` |
| 複数の離れたUIで共有するClient State | Zustand | Light・Dark Theme |
| API由来でCache、同期、再取得するServer State | TanStack Query | 認証済みUser、投稿一覧・詳細 |
| Access Token | 認証仕様で定めるModule Memory | `storeAccessToken.ts` |

状態管理では次を原則とします。

- 単一Component内で完結する状態を必要以上にGlobal Stateへ昇格しない
- APIレスポンスをZustandへ複製せず、TanStack Queryを正本とする
- URLとして共有・復元すべき状態をStoreだけで管理しない
- 同じ状態を複数の仕組みへ重複保存しない
- Access TokenをZustand、TanStack Query、Web Storageへ保存しない
- 新しい状態を追加する前に、所有者、共有範囲、永続化、再取得の要否を確認する

認証状態とAccess Tokenの詳細は[Next.jsアプリケーション 認証仕様](authentication.md)、投稿Queryの詳細は[Next.jsアプリケーション データ取得・投稿表示仕様](data-fetching.md)を参照してください。

### React Contextの扱い

アプリケーション独自の共有UI状態には原則としてZustandを使用し、状態管理を目的とした独自React Contextは新設しません。

React Contextは全面的に禁止せず、次の場合に利用を検討できます。

- 外部LibraryがProviderを必要とする
- 特定のComponent Tree内だけへ設定や依存関係を渡す
- 複数のScopeまたはStore Instanceを明示的に分離する

独自Contextを採用する場合は、Props、Composition、Local State、ZustandではなくContextが必要な理由を確認します。更新頻度の高いGlobal Stateを、Context Valueへまとめて配置しません。

TanStack Queryの `QueryClientProvider` のようにLibraryが提供するContextは、そのLibraryの仕様にしたがって使用します。旧Theme Contextは移行記録であり、新しい実装から利用しません。

### Theme Stateの現在実装

ThemeはGlobal UI Stateの現在の実装例です。

- `src/stores/siteThemeStore.ts` が `light` と `dark` を管理する
- 初期値は `light`
- `ThemeSwitch` がZustandの `setTheme()` を呼び出す
- `ThemeSwitch` は `OffCanvas` 内に配置する
- `Base` が現在値を `data-theme` 属性へ反映する
- `globals.css` が `body:has([data-theme='dark'])` でColor Variableを切り替える
- System Themeとの同期、永続化、Zustand Devtoolsは使用しない
- ページを再読み込みするとModule Stateが再初期化され、`light` に戻る
- Theme切り替えは現在、自動テストの対象外

## 7. Stylingの現在構成

現在の画面Styleは `src/styles/globals.css` のグローバルCSSで実装します。Theme用のCSS Variableと、`data-theme` に応じたLight・Darkの値を定義しています。

Tailwind CSS 4は依存関係とPostCSS Pluginを導入済みで、StylelintもTailwindのAt-ruleを許可します。ただし、現在は次の状態です。

- `globals.css` の `@import 'tailwindcss'` はコメントアウトされている
- `@theme inline` はTheme用CSS Variableの定義に使用している
- 現在のTSXではTailwind Utility Classを使用していない
- `tailwind-merge` は依存関係にあるが、現在のコードから使用していない

Sassは依存関係へ導入し、`next.config.mjs` の `sassOptions.includePaths` に `src/styles` を指定しています。StylelintはSCSS用ParserとRuleを設定済みですが、現在の `src/` にはSCSS FileとCSS Modulesがありません。

旧TASKにある「Tailwindを優先し、必要に応じてSassを使用する」という方針は、現在の実装規約として確定していません。新しいStyling方式を導入または統一する場合は、グローバルCSS、Tailwind Utility、CSS Modules、Sassの責務と共存方法を別途決定し、使用する方式に合わせて設定と本書を更新します。

## 8. ディレクトリ責務

`frontend/next/src/` 配下の主な責務は次のとおりです。

| ディレクトリ | 責務 |
| --- | --- |
| `app/` | App Routerのページ、レイアウト、Route Group、ルート固有コンテンツ |
| `components/` | 共通UIとドメイン単位の表示コンポーネント |
| `features/` | ドメイン固有のAPI処理や設定 |
| `hooks/` | クライアントフックとそのテスト |
| `helpers/` | APIクライアントやトークン保存などの補助処理 |
| `providers/` | TanStack Queryなどを提供するルートレベルProvider |
| `stores/` | ZustandによるクライアントUI状態 |
| `schemas/` | Zodスキーマ |
| `types/` | 共有TypeScript型 |
| `utils/` | メタデータ、JSON-LD、フォント、整形などの汎用処理 |
| `styles/` | グローバルスタイル |

`components/`、`features/`、`hooks/` は、共通処理を除き `auth`、`posts` などのドメイン単位で分けます。テストは対象に近い `__tests__/` へ配置します。

TypeScriptの内部参照には、`tsconfig.json` で定義した `@/*` から `src/*` へのパスエイリアスを使用します。

## 9. 環境変数

環境変数の名前とサンプル値は `frontend/next/.env.example` で管理します。実値を含む `.env*` はGit管理対象外です。

- `APP*`: Server Componentやビルド処理で使用する値
- `NEXT_PUBLIC_*`: Client Componentから参照できる公開値
- `NEXT_PUBLIC_*` に秘密情報を設定しない
- Development、Staging、Productionの値を同一ファイルへ混在させない
- Static Exportではビルド後に環境変数を差し替えられないため、対象環境の値で再ビルドする

環境別のファイルとCloudflare Workflowへの値の渡し方は、Cloudflareデプロイ仕様を参照してください。

## 10. 実装時の規約

- 新しいルートはServer Componentを基本とし、クライアント処理だけを分離する
- ルート固有のClient Componentは、現在の構成に合わせて同じルート配下へ置く
- 複数ルートで再利用するUIは `components/` へ置く
- ドメイン固有の処理は、対応するドメインディレクトリへ置く
- 状態は本書の「状態管理の使い分け」にしたがい、責務の異なる管理方法へ重複保存しない
- 外部データ用のZodスキーマがある場合は、データを読み込む境界で実行時検証する
- Static Exportで利用できないNext.js機能を導入しない
- 生成物や環境変数の実値をGitへ追加しない

## 11. 検証

コマンドは `frontend/next/` で実行します。

依存関係を変更した場合、またはクリーン環境で確認する場合:

```bash
npm ci
```

lint、型チェック、テスト:

```bash
npm run check
```

Static Exportとサイトマップ生成:

```bash
npm run build:cf
```

Cloudflareへの実デプロイは通常のコード検証に含めません。Wranglerプレビューやデプロイが必要な場合は、Cloudflareデプロイ仕様の環境別手順にしたがってください。
