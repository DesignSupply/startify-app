---
title: Next.jsアプリケーション メタデータ・Googleタグ・PWA仕様
status: current
last_updated: 2026-08-11
related_paths:
  - frontend/next/.env.example
  - frontend/next/.gitignore
  - frontend/next/next.config.mjs
  - frontend/next/next-sitemap.config.js
  - frontend/next/package.json
  - frontend/next/public/
  - frontend/next/src/app/
  - frontend/next/src/components/AdsenseUnit.tsx
  - frontend/next/src/components/GoogleAdsenseScript.tsx
  - frontend/next/src/components/JsonLd.tsx
  - frontend/next/src/utils/fonts.ts
  - frontend/next/src/utils/googleTags.ts
  - frontend/next/src/utils/jsonLd.ts
  - frontend/next/src/utils/meta.ts
  - frontend/next/src/utils/__tests__/
---

# Next.jsアプリケーション メタデータ・Googleタグ・PWA仕様

Startify-AppのNext.jsアプリケーションにおける、Metadata、構造化データ、フォント、Google Analytics、Google AdSense、サイトマップ、PWAの現在仕様を定義します。

Static Export、環境別ビルド、Cloudflare配信の詳細は[Next.js Static Export — Cloudflare Workers Static Assets デプロイ仕様](../../infrastructure/cloudflare/next-static-deployment.md)を参照してください。

## 1. 共通Metadata

`src/utils/meta.ts` の `metaDefault` を、`src/app/layout.tsx` の共通Metadataとして使用します。主な値はビルド時の環境変数から生成します。

| 環境変数 | 用途 |
| --- | --- |
| `APPURL` | `metadataBase`、Canonical URL、アイコン、Manifest、OGPの基準URL |
| `APPNAME` | Title、Application Name、OGP、Twitter、Apple Web App名 |
| `APPDESCRIPTION` | Description、OGP、Twitter |
| `APPAUTHOR` | Author、Creator、Publisher |

これらはServer Componentとビルド処理で使用します。`APPURL` は `new URL()` へ渡す完全なURLであり、ビルド対象環境のHTTPS URLを設定します。

共通Metadataには次を含めます。

- Title、Description、Application Name、Author
- Keywords、Referrer、Creator、Publisher
- index・followを許可するRobots
- `APPURL` を基準としたCanonical URL
- SVG FaviconとApple Touch Icon
- `/manifest.json`
- Website形式のOpen Graph
- Summary Card形式のTwitter Metadata
- Apple Web App設定とiOS Splash Screen候補
- 電話番号、住所、メールアドレスの自動検出無効化

`src/app/example/page.tsx` はページ固有のTitle、Canonical、Open Graph、Twitter Metadataを上書きします。404ページは固有Titleを設定します。その他のページは、現在、共通Metadataを継承します。そのため、`/signin/`、`/dashboard/`、`/posts/`、`/posts/[id]/` を含むページ固有Canonicalを持たないルートでは、トップページ用の `APPURL` がCanonicalになります。

現在のCanonical、Open Graph、Breadcrumb JSON-LDの一部URLは末尾スラッシュを付けずに生成します。Next.jsは `trailingSlash: true` のため、実際に配信するURLとの末尾スラッシュ表記は統一されていません。

## 2. Metadata用アセットの現在の制約

次の参照先はMetadataに定義されていますが、現在の追跡ファイルには存在しません。

- `/assets/images/apple_touch.png`
- `/assets/images/ogp.png`
- `iosSplashScreens` に列挙された `/assets/images/splashscreens/ios/` 配下の画像

Manifest用の192px・512pxアイコンと2枚のScreenshot画像は配置済みです。Metadataへ新しい画像を追加する場合は、参照だけでなく実ファイルの配置とStatic Export成果物への出力を確認します。

## 3. 構造化データ

`src/components/JsonLd.tsx` は、`src/utils/jsonLd.ts` を使用してSchema.orgの `BreadcrumbList` を出力します。

- `buildBreadcrumbListJsonLd()` が `@context`、`@type`、`itemListElement` を組み立てる
- `serializeJsonLd()` がJSON文字列内の `<` を `\u003c` へ置換する
- `<script type="application/ld+json">` として出力する

現在、Breadcrumb JSON-LDを出力するページはトップ、`/example/`、`/signin/` です。URLは `APPURL` を基準にビルド時生成します。

## 4. Viewportとフォント

ルートレイアウトのViewportは次の設定です。

| 項目 | 現在値 |
| --- | --- |
| `themeColor` | `#000000` |
| `colorScheme` | `light dark` |
| `width` | `device-width` |
| `initialScale` | `1` |

`src/utils/fonts.ts` は `next/font/google` のNoto Sans JPを使用します。

- CSS変数は `--font-noto-sans-jp`
- Variable Weightを使用
- `display: swap`
- Preloadを有効化
- ルートの`body`へフォント変数Classを設定

ローカルフォントのサンプルコードはコメントアウトされており、現在は使用しません。`public/assets/fonts/` にはプレースホルダー以外のフォントファイルがありません。

## 5. Googleタグの環境制御

Googleタグの判定は `src/utils/googleTags.ts` に集約します。`NEXT_PUBLIC_DEPLOY_ENV` は `development`、`staging`、`production` のみを有効な値として扱います。

Google AnalyticsとGoogle AdSenseは、次の両方を満たす場合だけ出力します。

1. `NEXT_PUBLIC_DEPLOY_ENV=production`
2. 対応するIDが空文字ではない

| 環境変数 | 用途 |
| --- | --- |
| `NEXT_PUBLIC_GOOGLE_ANALYTICS_ID` | GA4 Measurement ID |
| `NEXT_PUBLIC_GOOGLE_ADSENSE_ID` | AdSense Publisher ID |

Development、Staging、未設定、不正な環境値では、IDが設定されていてもタグを出力しません。`NEXT_PUBLIC_*` はブラウザーへ公開される識別子であり、秘密情報を設定しません。

Googleタグの出力判定とIDはStatic Exportのビルド時に固定されます。Cloudflare側の変数だけを変更しても既存成果物には反映されないため、値を変更した環境向けに再ビルドと再デプロイが必要です。

## 6. Google AnalyticsとAdSense

Google Analyticsは `@next/third-parties/google` の `GoogleAnalytics` をルートレイアウトから出力します。

AdSenseは次の2層で構成します。

- `GoogleAdsenseScript`: Publisher IDを含むGoogleのScriptを `afterInteractive` で読み込む
- `AdsenseUnit`: 広告枠の `ins.adsbygoogle` を描画し、Client ComponentのEffectで広告Queueへ追加する

`AdsenseUnit` はパス変更を監視しますが、Component Instanceごとの `didEffect` によりQueue追加を1回に制限します。現在はトップページのClient Componentに、プレースホルダーのSlot ID `XXXXXXXXXX` を指定した広告枠があります。実運用前に有効なSlot IDへ置き換える必要があります。

## 7. サイトマップ

`npm run build` は `next build` の後に `next-sitemap` を実行します。`next-sitemap.config.js` の現在設定は次のとおりです。

| 項目 | 現在値 |
| --- | --- |
| `siteUrl` | `APPURL`。未設定時は `https://example.com` |
| `generateRobotsTxt` | `false` |
| `outDir` | `./out` |

生成される`sitemap.xml`と分割サイトマップは`out/`へ配置されます。`out/`と`public/sitemap*.xml`はGit管理対象外です。サイトマップ生成時は、対象環境の公開URLを `APPURL` に設定します。

現在の設定にはルートの除外条件がなく、`/signin/`、`/dashboard/`、`/posts/`、`/posts/[id]/` もサイトマップへ含まれます。

## 8. PWA

PWAは `next-pwa` を `next.config.mjs` で適用します。

| 設定 | 現在値 |
| --- | --- |
| Service Worker出力先 | `public/` |
| 自動登録 | 有効 |
| `skipWaiting` | 有効 |
| Development | PWA無効 |
| Document Fallback | `/offline.html` |

Runtime Cacheは次の方針です。

| 対象 | Strategy | 保持条件 |
| --- | --- | --- |
| `https://example.com/` 配下 | Network First | 最大10件、24時間、Timeout 10秒 |
| 画像 | Cache First | 最大50件、30日 |
| Font | Cache First | 最大10件、60日 |
| CSS・JavaScript | Stale While Revalidate | 最大30件、7日 |

Service Worker、Workbox、Fallback Script、生成サイトマップは `.gitignore` で除外します。これらの生成物をstageしません。

## 9. Web App ManifestとOffline Page

`public/manifest.json` は次の基本設定を持ちます。

- `id` と `scope` は `/`
- `display` は `standalone`
- `orientation` は `any`
- Theme Colorは黒、Background Colorは白
- アプリ名と説明はStartify-Appの固定値
- 192px・512pxの通常／Maskableアイコン
- Narrow・Wide用のScreenshot

`public/offline.html` はService WorkerのDocument Fallbackです。ネットワーク切断の案内と、`window.location.reload()` を実行する再読み込みボタンを提供します。

## 10. 現在の制約

- Manifestの `start_url` は `https://example.com/` 固定で、環境変数と連動しない。異なるOriginへ配信した場合は、Manifestの起動URLとして有効に扱われない可能性がある
- PWAのNetwork First対象も `https://example.com/` 固定で、現在のAPI Base URLや環境別ドメインと連動しない
- 共通Robotsは `index, follow` で、サインインと認証保護ページもページ固有設定なしで継承する
- ページ固有Canonicalを持たないルートはトップページ用の `APPURL` を継承する
- サインインと認証保護ページを含め、サイトマップから除外するルートを設定していない
- Canonical、Open Graph、Breadcrumb JSON-LDの一部URLは、`trailingSlash: true` の配信URLと末尾スラッシュ表記が一致しない
- Metadataが参照するApple Touch Icon、OGP画像、iOS Splash Screen画像は未配置
- Google Verification、Twitter Account、Microsoft・Pinterest Verificationは空文字のまま
- Googleタグの出力判定はテスト済みだが、実際のScript出力や広告枠は自動テストしていない
- Manifest、Offline Page、Service Worker、Runtime Cache、サイトマップ成果物の内容は自動テストしていない

環境別のManifestやRuntime Cacheを必要とする場合は、ビルド時生成または環境別設定として設計します。未配置アセットや未設定識別子は、実値が決まるまで現在仕様として存在するものと扱いません。

Metadata、JSON-LD、Googleタグ、サイトマップ、`NEXT_PUBLIC_*` を含む環境変数の値はStatic Exportのビルド時に固定されます。設定変更を反映するには、対象環境の値を渡して再ビルドし、成果物を再デプロイします。

## 11. 検証

Googleタグの環境判定テストは `frontend/next/` で実行します。

```bash
npm run test:googleTags
```

lint、型チェック、全テスト:

```bash
npm run check
```

Metadata、サイトマップ、PWA成果物を含むStatic Export:

```bash
npm run build:cf
```

ビルド後は `out/` のHTML、Manifest参照、サイトマップ、Service Worker登録と、生成されたURLが対象環境に一致することを確認します。実デプロイは通常の検証に含めません。

## 12. 移行元資料

本書は、次の既存資料から設計意図を抽出し、現在のコード、設定、テストと照合して再構成しています。

- `.cursor/rules/dev-frontend.mdc`

この資料はドキュメント移行が完了するまで設計意図の確認に使用しますが、現在仕様としては本書と現在の実装を優先します。
