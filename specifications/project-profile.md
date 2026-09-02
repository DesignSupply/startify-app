---
title: プロジェクト構成・派生運用仕様
status: current
last_updated: 2026-09-03
related_paths:
  - AGENTS.md
  - README.md
  - specifications/
  - frontend/
  - backend/
  - server/
  - .github/workflows/
---

# プロジェクト構成・派生運用仕様

Startify-Appを自社サイトや各種プロジェクトへ横展開するとき、**どの領域を採用するか**、**コードをどう扱うか**、**仕様書をどう参照するか**を管理する正本です。

個別領域の詳細設計は、引き続き各領域の仕様書を正本とします。本書は、それらの**適用範囲**を決める上位の構成表です。

```text
project-profile.md
  ├─ 採用状態
  ├─ コード状態
  └─ 仕様書の扱い
       ↓
適用対象となる領域別仕様書
       ↓
コード・設定・テスト・CI
```

## 1. 文書の目的

- 派生プロジェクトごとに、Startify-App由来の構成をどこまで採用するかを記録する
- コード削除後も、基本仕様書を参考資料として保持できる方針を定義する
- 構成変更時に、実装・設定・仕様書・CIの同期ルールを共有する
- 人間とAIエージェントが、同じ前提で作業範囲を判断できるようにする

## 2. 適用範囲の正本としての位置づけ

| 文書 | 役割 |
| --- | --- |
| `specifications/project-profile.md`（本書） | プロジェクトの**採用範囲**の正本 |
| 領域別仕様書（例: `specifications/frontend/next/`） | 採用した領域の**詳細仕様**の正本 |
| コード・設定・テスト・CI | 現在の**実装事実**の正本 |

派生プロジェクトでは、作業開始時に本書を確認し、採用状態が `active` の領域を**現在採用中**として扱います。個別仕様書を現在仕様として適用する条件は、[§3](#3-文書-status-と領域の採用状態の違い)で定義します。

## 3. 文書 `status` と領域の採用状態の違い

| 概念 | 対象 | 例 |
| --- | --- | --- |
| 文書 `status` | **文書自体**が現在内容と照合済みか | `current`、`draft`、`planned` |
| 採用状態 | **プロジェクト**がその領域を採用しているか | `active`、`planned`、`inactive` |
| コード状態 | **リポジトリ**に実装がどう存在するか | `present`、`reserved` など |
| 仕様書の扱い | **派生先**でその領域の仕様書をどう読むか | `applicable`、`reference` など |

判断条件は次のとおりです。

```text
現在採用中の領域
= 採用状態が active

現在仕様として適用する個別仕様書
= 領域が active
  かつ 仕様書の扱いが applicable
  かつ 文書 status が current
```

採用状態が `active` でも、仕様書の扱いが `none` の領域（例: Vite、Astro）は**採用中だが個別仕様書は未整備**です。その場合は、コード・設定・テスト・CI を実装事実として確認します。

個別仕様書の Front Matter に `status: current` があっても、本書で採用状態が `inactive`、または仕様書の扱いが `reference` / `planned` / `none` であれば、派生先の現在仕様としては適用しません。文書 `status` が `planned` または `draft` の領域別仕様書も、実装と照合して `current` へ更新するまで現在仕様として適用しません。その場合はコード・設定・テスト・CI を実装事実として確認し、文書は参考資料として扱います。

## 4. 管理する3つの軸

### 4.1. 採用状態

| 状態 | 意味 |
| --- | --- |
| `active` | 現在のプロジェクトで採用している |
| `planned` | 将来の採用候補。現在実装済みとは扱わない |
| `inactive` | 現在のプロジェクトでは採用しない |

`active` は構成として採用していることを表します。詳細仕様書の存在や最新性は、仕様書の扱いとコード状態で別途確認します。

### 4.2. コード状態

| 状態 | 意味 |
| --- | --- |
| `present` | Git管理対象の実装が存在する |
| `reserved` | `.gitkeep` などの予約領域のみ存在する |
| `absent` | 実装が存在しない |
| `removed` | 派生後にコードを削除済み |
| `setup-managed` | Gitには完成物を保持せず、Setup時に導入・生成する |

`setup-managed` の例:

- Laravel の `vendor/`（`composer install`）
- WordPress Core（`make wp-download` / `make wp-update`）
- Node.js の `node_modules/`、各FrontendのBuild成果物
- Next.js の `out/`、Storybook の `storybook-static/`

### 4.3. 仕様書の扱い

| 扱い | 意味 |
| --- | --- |
| `applicable` | このプロジェクトの現在仕様として適用する |
| `reference` | 基盤由来の参考資料として保持する |
| `planned` | 将来導入時の検討資料として扱う |
| `none` | 対応する個別 `current` 仕様書が未整備 |

## 5. Startify-App本体の現在構成

2026-09-03 時点の Git 管理対象と仕様書を照合した構成表です。Startify-App本体では、下表の採用状態・コード状態・仕様書の扱いが現在の前提です。

| 領域 | 採用状態 | コード状態 | 仕様書の扱い | 実装パス | 参照仕様 | 依存・連動領域 | 備考 |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Laravel | `active` | `present` | `applicable` | `backend/laravel/` | [Laravel 概要](backend/laravel/overview.md) ほか `backend/laravel/` | Docker、MariaDB、Mailpit、Next.js API | `vendor/` は `setup-managed` |
| WordPressクラシックテーマ | `active` | `present` | `applicable` | `backend/wordpress/wp-content/themes/startify-classic-theme/` | [WordPress 概要](backend/wordpress/overview.md)、[クラシックテーマ](backend/wordpress/classic-theme.md)、[コンテンツ・API](backend/wordpress/content-and-api.md) | Docker、WordPress Core、Nginx | WordPress Core は `setup-managed`。ブロックテーマは Git 未追跡 |
| Docker | `active` | `present` | `applicable` | `server/` | [Docker 概要](server/docker/overview.md)、[セットアップ・運用](server/docker/setup-and-operations.md) | Laravel、WordPress、Nginx、MariaDB、Mailpit | `server/.env` は Git 管理外 |
| Next.js | `active` | `present` | `applicable` | `frontend/next/` | [アーキテクチャ](frontend/next/architecture.md) ほか `frontend/next/` | Laravel API、Cloudflare、Docker（ローカル検証は Node 単体） | Static Export 前提 |
| デザイントークン／UI／Storybook | `active` | `present` | `applicable` | `frontend/_design-tokens/`、`frontend/ui/` | [デザインシステム](frontend/ui/design-system.md) | 相互参照のみ（自動連携なし） | Token と UI は YAML / Storybook で別管理 |
| Vite | `active` | `present` | `none` | `frontend/vite/` | — | なし（独立した静的サイト用） | 専用 `current` 仕様書なし |
| Astro | `active` | `present` | `none` | `frontend/astro/` | — | なし（独立した静的サイト用） | 専用 `current` 仕様書なし |
| Cloudflare（Next.js配信） | `active` | `present` | `applicable` | `.github/workflows/next-cloudflare-*.yml`、`frontend/next/wrangler.jsonc` | [Cloudflare デプロイ](infrastructure/cloudflare/next-static-deployment.md) | Next.js Static Export | CI は Next.js 変更時のみ |
| Nuxt | `planned` | `reserved` | `none` | `frontend/nuxt/`（`.gitkeep` のみ） | — | — | 予約領域 |
| React Native | `planned` | `reserved` | `none` | `frontend/react-native/`（`.gitkeep` のみ） | — | — | 予約領域 |

**Startify-App本体に含めないもの**

- **FastAPI**: 実装パスも仕様書も存在しない。将来の新規領域追加例として後述する
- **WordPressブロックテーマ**: ローカルにディレクトリが見える場合があっても、現在 Git 管理対象のファイルはない

## 6. 領域間の依存・連動関係

| 関係 | 内容 |
| --- | --- |
| Next.js 認証 ↔ Laravel API | Next.js のログイン・投稿取得は Laravel JWT API に依存。Laravel を `inactive` にする場合、Next.js 認証・データ取得も見直す |
| Next.js ↔ Cloudflare | Static Export 成果物 `out/` を Workers Static Assets へ配信。Workflow、`wrangler.jsonc`、`.nvmrc`、`package-lock.json` と整合が必要 |
| Cloudflare Workflow ↔ Next.js 設定 | `frontend/next/.nvmrc`、lockfile、Wrangler 設定、Build Script をセットで確認する |
| Docker ↔ Laravel | PHP アプリ、MariaDB、Mailpit、Storage Link、JWT 鍵生成など Setup 手順が連動 |
| Docker ↔ WordPress | Core 取得、公開 Link、Database、Theme 配置が連動。Core は `setup-managed` |
| Docker ↔ Nginx / MariaDB / Mailpit / Webroot | `server/docker/` 配下の Service 構成と Host 名（`localhost`、`cms.localhost` など）が共通基盤 |
| UI / Storybook ↔ デザイントークン | 同一デザインシステムの資料だが、現在は自動反映しない。Token 変更だけでは UI に反映されない |
| Laravel メール ↔ Mailpit | 問い合わせ・通知メールのローカル確認は Mailpit に依存 |
| 未採用領域の削除 | `inactive` にしただけでは関連 Workflow や import を自動削除しない。連動領域を先に確認する |

`inactive` への変更を理由に、AIエージェントや自動処理が関連コードを勝手に削除しないでください。削除はユーザーの明示的判断に基づきます。

## 7. 基本仕様書を派生先へ保持する方針

派生プロジェクトでは、コードを削除しても Startify-App 由来の基本仕様書を保持できます。

| ルール | 内容 |
| --- | --- |
| 現在仕様として読む | 領域が `active`、仕様書の扱いが `applicable`、文書 `status` が `current` の個別仕様書 |
| 現在実装として読まない | 採用状態が `planned` または `inactive` な領域。または、上記3条件を満たさない個別仕様書 |
| 採用中だが仕様書未整備 | 採用状態が `active`、仕様書の扱いが `none` の領域。コード・設定・テスト・CI を実装事実として確認する |
| パス・コマンドの保証 | コードが `removed` または `absent` の場合、仕様書内のパスやコマンドが派生先に実在するとは限らない |
| 再導入時 | 派生元の対応 Revision と Startify-App の最新状態を確認し、コード・設定・テスト・CI と仕様書を再検証する |
| 実装済み判断 | 仕様書を保持しているだけでは、機能を実装済みと判断しない |

採用状態は本書の構成表で一元管理し、個別仕様書の Front Matter へ採用状態を一括追加しません。

## 8. 派生元情報（派生先で記録する項目）

Startify-App本体の文書へ、更新のたびに変わる自身の Commit Hash を固定値として書き込みません。派生プロジェクト初期化時に、次の Template をプロジェクト固有資料（本書の派生先版、README、内部 Wiki など）へ記録します。

```yaml
source:
  repository: DesignSupply/startify-app
  revision: <派生元のGit Commit>
  derived_at: YYYY-MM-DD
```

## 9. 派生プロジェクトの初期化手順

1. Startify-App から新しい Repository を作成する
2. Git 履歴を引き継ぐか、新しい履歴で開始するか決定する
3. Repository 名、Remote、Default Branch を設定する
4. 派生元 Repository、Revision、派生日を記録する（[§8](#8-派生元情報派生先で記録する項目)）
5. `project-profile.md` を派生プロジェクト向けに更新する
6. 各領域を `active`、`planned`、`inactive` へ分類する
7. コード状態と仕様書の扱いを記録する
8. [§6](#6-領域間の依存連動関係) の依存関係を確認する
9. 不要なコード、設定、Workflow を**ユーザー判断**に基づいて整理する
10. Repository 名、Package 名、アプリ名、URL、Metadata を変更する
11. 環境変数、Secrets、Cloudflare 設定を棚卸しする
12. README、`AGENTS.md`、仕様書を派生先に合わせる
13. 削除後の import、Script、パス、リンクを確認する
14. 採用領域に応じた検証を実行する
15. 実装・構成表・仕様書が一致した状態で変更を確定する

## 10. 初期化・棚卸しの確認対象

実在パスと設定項目を根拠に、少なくとも次を確認します。環境変数や Secrets の**実値**は記録しません。詳細は各領域の仕様書を参照してください。

| 区分 | 確認対象 |
| --- | --- |
| Repository | Repository 名、Git Remote、Default Branch |
| ドキュメント | README、`AGENTS.md`、仕様書内のプロジェクト名 |
| Package 名 | 各 `package.json`（例: `frontend/next/package.json` の `next`）、`backend/laravel/composer.json` |
| Frontend Metadata | Next.js / Astro / Vite の Manifest、Metadata、サイト情報 |
| WordPress | テーマ情報（`style.css` など） |
| Cloudflare | `frontend/next/wrangler.jsonc` の Worker 名（Startify-App 本体: `startify-app-next`） |
| CI | `.github/workflows/` の Workflow 名、対象パス、Environment |
| 環境変数 Template | `server/.env.example`、`backend/laravel/.env.example`、`frontend/next/.env.example` |
| 外部設定 | GitHub Secrets / Variables、Cloudflare Account / Worker / Environment |
| ローカル Host | `localhost`、`cms.localhost`、`testing.localhost`、`api.localhost` |
| Git 境界 | 各 `.gitignore` と Build 成果物・Setup 生成物の除外 |

## 11. コードを保持・削除する判断方法

| 判断 | 指針 |
| --- | --- |
| 保持 | 採用状態が `active` または `planned` の領域 |
| 削除候補 | 採用状態が `inactive` とし、依存・連動領域の整理も完了した領域 |
| 仕様書のみ保持 | コードが `removed` / `absent` でも、仕様書の扱いが `reference` として残す場合は、構成表で明示する |
| 自動削除禁止 | 未採用コードを AI エージェントがユーザーの指示なく削除しない |
| 連動確認 | Workflow、import、Script、README リンク、CI パスを削除後に再確認する |

## 12. 構成変更時の同期ルール

- 本書だけを先に将来状態へ変更しない
- 構成変更は、コード、設定、README、`AGENTS.md`、関連仕様書、テスト、CI への影響を確認する
- `active` への変更は、実装と検証が完了した変更と同じ PR で行う
- `inactive` への変更は、依存・連動領域を整理した変更と同じ PR で行う
- コード削除はユーザーの明示的な判断に基づく
- 仕様書だけを保持する場合は、現在仕様か参考資料かを構成表で明示する

## 13. 新しい技術領域の追加（FastAPI の例）

FastAPI は Startify-App 本体には存在しません。将来、新しい Backend を追加する場合の手順例です。未実装の具体構成を現在仕様として書き込まないでください。

1. 実装パスを決定する（例: `backend/fastapi/`）
2. 仕様書パスを決定する（例: `specifications/backend/fastapi/overview.md`）
3. 依存・連動領域を整理する（Docker、認証、Frontend API など）
4. 環境変数、Setup、検証方法を定義する
5. 本書の構成表へ追加する（初期は `planned` または `inactive`）
6. [`specifications/README.md`](README.md) の索引を更新する
7. [`AGENTS.md`](../AGENTS.md) の参照ルーティングを更新する
8. 実装と検証が完了するまでは `active` として扱わない

## 14. 派生後の検証チェックリスト

採用した領域に応じて、少なくとも次を確認します。

| 領域 | 代表的な検証 |
| --- | --- |
| Docker | `cd server && make ps`、対象 Host への HTTPS アクセス |
| Laravel | `make laravel-test`、`make laravel-route`（Docker 起動後） |
| WordPress | `https://cms.localhost/` の表示、Theme・REST API |
| Next.js | `cd frontend/next && npm run check && npm run build:cf` |
| Cloudflare | Wrangler dry-run、Workflow 対象パスの整合 |
| デザイントークン / UI | `cd frontend/ui && npm run check:tokens && npm run test:storybook && npm run typecheck` |
| Vite / Astro | 各 `frontend/*` で `npm ci` と dev / build |
| ドキュメント | 本書の構成表と README、領域別仕様書、実装パスの一致 |
| Git | 不要領域削除後に import・Workflow・リンク切れがないこと |

## 15. 関連資料

- 仕様書索引: [`specifications/README.md`](README.md)
- エージェント共通指示: [`AGENTS.md`](../AGENTS.md)
- 利用者向け導線: [`README.md`](../README.md)
