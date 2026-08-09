---
title: Next.jsアプリケーション アーキテクチャ仕様
status: current
last_updated: 2026-08-10
related_paths:
  - frontend/.nvmrc
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
| Node.js | `22.12.0`（`frontend/.nvmrc`） |
| npm | `10.8.2` |
| Next.js | `15.5.22`、App Router |
| React | `19` |
| 言語 | TypeScript、`strict: true` |
| スタイル | Tailwind CSS 4、Sass、グローバルCSS |
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

## 6. ディレクトリ責務

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

## 7. 環境変数

環境変数の名前とサンプル値は `frontend/next/.env.example` で管理します。実値を含む `.env*` はGit管理対象外です。

- `APP*`: Server Componentやビルド処理で使用する値
- `NEXT_PUBLIC_*`: Client Componentから参照できる公開値
- `NEXT_PUBLIC_*` に秘密情報を設定しない
- Development、Staging、Productionの値を同一ファイルへ混在させない
- Static Exportではビルド後に環境変数を差し替えられないため、対象環境の値で再ビルドする

環境別のファイルとCloudflare Workflowへの値の渡し方は、Cloudflareデプロイ仕様を参照してください。

## 8. 実装時の規約

- 新しいルートはServer Componentを基本とし、クライアント処理だけを分離する
- ルート固有のClient Componentは、現在の構成に合わせて同じルート配下へ置く
- 複数ルートで再利用するUIは `components/` へ置く
- ドメイン固有の処理は、対応するドメインディレクトリへ置く
- UI状態にはZustand、API・サーバー状態にはTanStack Queryを使用する
- 外部データ用のZodスキーマがある場合は、データを読み込む境界で実行時検証する
- Static Exportで利用できないNext.js機能を導入しない
- 生成物や環境変数の実値をGitへ追加しない

## 9. 検証

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

## 10. 移行元資料

本書は、次の既存資料から設計意図を抽出し、現在のコードと設定に合わせて再構成しています。

- `.cursor/rules/dev-frontend.mdc`
- `specifications/frontend/next/TASK_001.md`
- `specifications/frontend/next/TASK_002.md`
- `specifications/frontend/next/TASK_003.md`
- `specifications/frontend/next/TASK_004.md`
- `specifications/frontend/next/TASK_005.md`
- `specifications/frontend/next/TASK_006.md`
- `specifications/frontend/next/TASK_007.md`
- `specifications/frontend/next/TASK_008.md`
- `specifications/frontend/next/TASK_009.md`
- `specifications/frontend/next/TASK_010.md`
- `specifications/frontend/next/TASK_011.md`
- `specifications/frontend/next/TASK_012.md`

これらはドキュメント移行が完了するまで設計意図の確認に使用しますが、現在仕様としては本書と現在の実装を優先します。
