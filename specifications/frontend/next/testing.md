---
title: Next.jsアプリケーション テスト・品質検証仕様
status: current
last_updated: 2026-09-02
related_paths:
  - frontend/next/.nvmrc
  - frontend/next/.gitignore
  - frontend/next/.prettierignore
  - frontend/next/eslint.config.mjs
  - frontend/next/markuplint.config.mjs
  - frontend/next/package.json
  - frontend/next/package-lock.json
  - frontend/next/prettier.config.mjs
  - frontend/next/src/
  - frontend/next/stylelint.config.mjs
  - frontend/next/tsconfig.json
  - frontend/next/vitest.config.ts
  - .github/workflows/next-cloudflare-ci.yml
  - .github/workflows/next-cloudflare-staging.yml
---

# Next.jsアプリケーション テスト・品質検証仕様

Startify-AppのNext.jsアプリケーションにおける、静的解析、型チェック、自動テスト、Static Export、CIの現在仕様を定義します。

個別機能のテスト対象と既知の未テスト範囲は、[認証仕様](authentication.md)、[データ取得・投稿表示仕様](data-fetching.md)、[メタデータ・Googleタグ・PWA仕様](metadata-and-pwa.md)も参照してください。

## 1. 実行環境と依存関係

コマンドは `frontend/next/` で実行します。Node.jsのバージョンは `frontend/next/.nvmrc`、npmのバージョンは `frontend/next/package.json` の `packageManager` を基準とします。

クリーン環境または依存関係変更後は、Lockfileに基づいて依存関係を導入します。

```bash
npm ci
```

`node_modules/`、`.next/`、`out/`、Coverage、TypeScript Build Info、PWA生成物はGit管理対象外です。

## 2. 標準品質チェック

コード変更時の標準チェックは次のコマンドです。

```bash
npm run check
```

`check` は次を順番に実行します。

1. `npm run lint`
2. `npm run typecheck`
3. `npm run test`

途中のコマンドが失敗した場合は、後続処理を実行せず失敗として終了します。`check` にPrettierとStatic Export Buildは含まれません。

## 3. Lint

`npm run lint` は3種類のLintを順番に実行します。

| コマンド | 対象 | 主な設定 |
| --- | --- | --- |
| `npm run lint:script` | `src/` のJavaScript・TypeScript・React | `eslint.config.mjs` |
| `npm run lint:style` | `src/**/*.{css,scss}` | `stylelint.config.mjs` |
| `npm run lint:markup` | `src/**/*.{jsx,tsx}` | `markuplint.config.mjs` |

### ESLint

ESLintはNext.js Core Web Vitals、Next.js TypeScript、Prettier互換設定を使用します。依存ディレクトリ、Build成果物、Coverage、TypeScript生成物、PWA・サイトマップ生成物を対象外とします。

### Stylelint

StylelintはStandardとTailwind CSS向け設定を使用し、Propertyのアルファベット順を検証します。SCSSには `postcss-scss` を使用し、Tailwind関連のAt-ruleを許可します。

### Markuplint

MarkuplintはJSX・TSXをReact仕様で検証します。重複属性、非推奨要素、必須属性、Landmark、参照先ID、List、WAI-ARIAなどを確認し、`img` には `alt` を必須とします。

自動修正用の `npm run fix` はStylelintとMarkuplintだけを対象とします。実行するとファイルを書き換えるため、差分を確認してから利用します。

## 4. TypeScript

型チェックは次のコマンドで行います。

```bash
npm run typecheck
```

`tsc --noEmit` を使用し、JavaScript成果物を出力しません。`tsconfig.json` は `strict: true`、Bundler Module Resolution、Next.js Plugin、`@/*` から `src/*` へのAliasを使用します。

Incremental Buildにより生成される `*.tsbuildinfo` はGit管理対象外です。

## 5. Unit・Component Test

テストにはVitest、React Testing Library、jsdomを使用します。`vitest.config.ts` は次を設定します。

- Test Environmentは `jsdom`
- `@` Aliasを `src/` へ解決
- Test用JSX RuntimeはAutomatic

現在のテストは対象コードに近い `__tests__/` へ配置し、ファイル名に `*.spec.ts` または `*.spec.tsx` を使用します。これは現在の命名慣例であり、Vitest設定でこの形式だけに制限しているものではありません。

## 6. Test Scriptと現在の対象

`npm run test` は次の3スクリプトを順番に実行します。

| コマンド | 実行対象 | 現在の主な検証 |
| --- | --- | --- |
| `npm run test:auth` | Auth Hook・SigninFormのTest Directory | ユーザー取得、Login・Logout後のQuery操作、入力検証、遷移 |
| `npm run test:posts` | Posts HookのTest Directory | 一覧・詳細取得、Zod Error、ID未指定時の無効化 |
| `npm run test:googleTags` | `src/utils/__tests__` 全体 | Googleタグの環境判定、Breadcrumb JSON-LD、JSON Serialize |

`test:googleTags` という名称ですが、現在はDirectory指定のため `googleTags.spec.ts` だけでなく `jsonLd.spec.ts` も実行します。

`npm run test` は上記の対象Directoryを明示しており、リポジトリ内の全Test Fileを自動探索するScriptではありません。新しいドメインや別DirectoryへTestを追加する場合は、既存Scriptの対象に含まれることを確認し、必要に応じて個別のTest Scriptと `npm run test` を更新します。

AuthとPostsにはWatch用Scriptがあります。

```bash
npm run test:auth:watch
npm run test:posts:watch
```

## 7. 自動テストの現在の制約

現在は次の仕組みを導入していません。

- Coverage計測ScriptとCoverage閾値
- Browser E2E Test
- Visual Regression Test
- Accessibility専用Test Runner
- API・Cloudflare環境との自動結合テスト

また、共通APIクライアントの自動Refresh、AuthGuard、投稿UI・ページネーション、Metadata出力、Google Script出力、Manifest、Service WorkerなどはUnit・Component Testの対象外です。機能固有の詳細は各仕様書の検証セクションを参照してください。

## 8. Format

Prettierは次のScriptで実行します。

```bash
npm run format:check
npm run format
```

`format:check` は確認のみ、`format` は対象ファイルを書き換えます。対象は `src/` 配下のJavaScript、TypeScript、React、CSS、SCSS、JSON、Markdownです。

Prettierは現在 `npm run check` とCIの個別Stepには含まれません。ESLintではPrettierと競合するRuleを無効化しますが、Prettier形式そのものを標準品質チェックで検証しているわけではありません。

## 9. Static Export検証

Static Exportとサイトマップ生成は次のコマンドで確認します。

```bash
npm run build:cf
```

`build:cf` は `npm run build` を呼び、`next build` と `next-sitemap` を実行します。Buildは `npm run check` に含まれないため、ルーティング、Metadata、PWA、Static Exportへ影響する変更では別途実行します。

Buildによって `.next/`、`out/`、Service Worker、Workbox、Fallback Script、サイトマップなどが生成されます。これらの生成物をstageしません。

## 10. GitHub Actions CI

`.github/workflows/next-cloudflare-ci.yml` は、Next.js関連ファイルまたは対象Workflowの変更を含むPull Requestと、`main`へのPushで実行します。

CIのQuality Jobは次を実行します。

1. `npm ci`
2. `npm run check`
3. `npm run build:cf`
4. 主要なHTML、サイトマップ、Service Worker、Offline Page、Static Assetの存在確認
5. `wrangler deploy --dry-run` による設定検証

CI用の公開値を環境変数として設定し、Cloudflareへ実デプロイしません。WorkflowのActionはCommit SHAで固定します。

現在のPath Filterは `frontend/next/**` と対象のNext.js Workflowです。`frontend/next/.nvmrc` は `frontend/next/**` に含まれます。`specifications/` やルート `AGENTS.md` だけの変更ではNext.js CIは起動しません。

## 11. Cloudflare Staging Workflow

`.github/workflows/next-cloudflare-staging.yml` は、手動実行または対象パスを変更した `main` へのPushで動作します。

Quality CheckとStatic Export成果物確認に加え、Staging用の必須変数、`NEXT_PUBLIC_DEPLOY_ENV=staging`、Cloudflare認証情報を検証してから実デプロイします。

Staging Workflowは外部環境を変更します。通常のローカル検証として実行せず、明示的にStaging Deployが必要な場合だけ利用します。

## 12. 変更種別ごとの検証

実装中は変更対象に対応する個別Testを反復確認に使用し、コード変更の作業完了前には原則として `npm run check` を実行します。Routing、Metadata、PWA、Build設定などStatic Exportへ影響する変更では、さらに `npm run build:cf` を実行します。

変更範囲とリスクに応じた個別確認は次を基本とします。

| 変更 | 実装中の個別確認 |
| --- | --- |
| Auth Hook・Form | `npm run test:auth` |
| Posts Hook | `npm run test:posts` |
| Googleタグ・JSON-LD Utility | `npm run test:googleTags` |
| TypeScript・React・Style・Markup | `npm run check` |
| Routing、Metadata、PWA、Build設定 | `npm run check` と `npm run build:cf` |
| 依存関係・Lockfile | `npm ci`、`npm run check`、`npm run build:cf` |
| ドキュメントのみ | Link、Path、Command、環境変数、設定値、`git diff --check` |

失敗した検証を回避するためにTestやLint Ruleを無効化しません。実行できなかった検証は理由とともに報告します。
