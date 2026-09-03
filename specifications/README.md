# Startify-App Specifications

`specifications/` は、Startify-Appの現在の設計、実装規約、運用方法を、人間とAIエージェントが共有するためのドキュメント領域です。

このREADMEは、仕様書の読み方、状態、更新ルール、参照先を定義する索引です。リポジトリ全体に共通するAIエージェント向けの必須指示は、ルートの [`AGENTS.md`](../AGENTS.md) を参照してください。

派生プロジェクトでは、最初に [`project/profile.md`](project/profile.md) を確認し、どの領域を採用するかを判断してください。用途・要件・デザイン方針は、[`project/templates/brief.md`](project/templates/brief.md) から `specifications/project/brief.md` を作成して管理します。採用状態が `active` の領域を現在採用中とします。個別仕様書を現在仕様として適用する条件は、領域が `active`、仕様書の扱いが `applicable`、文書 `status` が `current` です（詳細は [`project/profile.md`](project/profile.md) §3）。文書 Front Matter の `status`（例: `current`）は**文書自体**の状態であり、プロジェクトへの適用可否は採用状態・仕様書の扱いと合わせて判断します。`document_type: project-brief` の Brief における `status: current` は、要件文書として確認済み・有効であることを表し、**実装完了を保証しません**。採用状態が `inactive` または `planned` な領域、または `status: planned` / `draft` の個別仕様書は、現在仕様として適用しません。

## 基本方針

- 現在仕様は、既存の設計意図と現在のコード、設定、テスト、CIを照合して記録します。
- 仕様書では、現在実装されている内容と、未実装の方針や将来案を明確に区別します。
- 同じ仕様をREADME、ツール固有の補助指示、複数の仕様書へ重複して記載せず、正本となる文書へ参照を集約します。
- 実装変更で仕様が変わる場合は、関連する仕様書も同じ作業範囲で更新します。
- 仕様書と実装の不一致を発見した場合は、どちらかを根拠なく上書きせず、設計意図と現在の実装を確認します。
- 古くなった文書は、有効な内容を現在仕様へ反映して参照先を更新した後、リポジトリから削除します。過去の内容はGit履歴で確認します。

## 文書の種類

### 現在仕様

現在の設計、実装規約、制約、外部インターフェイスを記録する正本です。機能を変更する際に最初に参照し、コードや設定の変更とともに更新します。

### 運用手順

セットアップ、検証、デプロイ、トラブルシューティングなど、繰り返し実行する操作を記録します。現在のコマンドや設定と一致することを維持します。

手順のうち、AIエージェントが繰り返し実行できる定型作業は、必要に応じて `.agents/skills/` へ切り出します。仕様書には目的、前提、完了条件を残し、Skillには実行手順を記載します。

## 文書ステータス

新しく作成または再編する仕様書は、次のステータスを明示します。

| ステータス | 意味 | 扱い |
| --- | --- | --- |
| `current` | 現在のコード、設定、運用と照合済み | 現在仕様の正本として参照する |
| `draft` | 内容を整理中、または照合が未完了 | 参考資料として扱い、実装前に確認する |
| `planned` | 未実装の方針や将来計画 | 現在仕様として適用しない |

`document_type: project-brief` の Brief における `status: current` は、要件文書として確認済み・有効であることを表します。実装の完了状況は、コード、テスト、Issue、領域別仕様書で確認してください。

ステータスがない文書は `draft` 相当として扱います。ただし、後述する「現在仕様」に明示した文書は除きます。

## 推奨メタデータ

新しく作成または再編する個別仕様書では、先頭に次のFront Matterを付けます。

```yaml
---
title: 文書タイトル
status: current
last_updated: YYYY-MM-DD
related_paths:
  - path/to/source
---
```

- `title`: 文書の内容を表す名称
- `status`: `current`、`draft`、`planned` のいずれか
- `last_updated`: 内容を最後に実装と照合した日付
- `related_paths`: 仕様の根拠または主な適用対象となるリポジトリ相対パス

必要に応じて補足項目を追加できますが、実際に運用しないバージョン番号や所有者情報を形式だけで追加しないでください。

## 現在のドキュメント

### プロジェクト構成

| 文書 | 状態 | 内容 |
| --- | --- | --- |
| [プロジェクト構成・派生運用仕様](project/profile.md) | `current` | 採用範囲、コード状態、仕様書の扱い、派生初期化手順、構成表 |

`profile.md` は採用技術と仕様書の適用範囲を管理します。用途・要件・デザイン方針は、派生先で作成する `specifications/project/brief.md` で管理します（Startify-App本体には存在しません）。

### プロジェクト共通Template

| 文書 | 状態 | 内容 |
| --- | --- | --- |
| [Project Brief Template](project/templates/brief.md) | `current` | 派生プロジェクト向けの要件・デザイン方針Template。派生時に `specifications/project/brief.md` を作成する |

Template から作成した `brief.md` は、作成直後 `status: draft` とし、要件確認後に `status: current` へ変更します。Brief の `current` は実装完了を意味しません。

### 現在仕様

| 領域 | 文書 | 状態 |
| --- | --- | --- |
| Laravel | [Laravelアプリケーション 概要仕様](backend/laravel/overview.md) | `current`。Laravelの役割、機能・認証境界、データ構成、検証状況を記録 |
| Laravel | [Laravelアプリケーション アーキテクチャ仕様](backend/laravel/architecture.md) | `current`。レイヤー責務、配置、命名、ルーティング、Blade構成を記録 |
| Laravel | [Laravelアプリケーション 画面・機能仕様](backend/laravel/screens-and-features.md) | `current`。画面、アクセス境界、Web処理、主要な画面遷移を記録 |
| Laravel | [Laravelアプリケーション データベース仕様](backend/laravel/database.md) | `current`。テーブル、制約、Relation、論理削除、Seederを記録 |
| Laravel | [Laravelアプリケーション 認証仕様](backend/laravel/authentication.md) | `current`。Session認証、新規登録、パスワード再設定、JWT認証APIを記録 |
| Laravel | [Laravelアプリケーション 問い合わせ・メール仕様](backend/laravel/contact-and-mail.md) | `current`。問い合わせフロー、Notification、メールTemplate、SMTP、Mailpitを記録 |
| Laravel | [Laravelアプリケーション コンテンツ管理仕様](backend/laravel/content-management.md) | `current`。投稿、カテゴリ、タグの閲覧・管理、Validation、Relation、論理削除を記録 |
| Laravel | [Laravelアプリケーション ファイル管理仕様](backend/laravel/file-management.md) | `current`。管理者向けアップロード、非公開Storage、Metadata、画像Preview、Download、削除を記録 |
| Laravel | [Laravelアプリケーション ユーザー・プロフィール管理仕様](backend/laravel/user-and-profile-management.md) | `current`。一般・管理者プロフィール、管理者による一般ユーザー更新、論理削除・復元を記録 |
| Laravel | [Laravelアプリケーション Validation・Security仕様](backend/laravel/validation-and-security.md) | `current`。入力検証、認証・認可境界、CSRF、Cookie、CORS、JWT、出力、ログ、依存監査を記録 |
| WordPress | [WordPress 概要仕様](backend/wordpress/overview.md) | `current`。WordPress領域の役割、公開構成、Git管理境界、独自Theme・Plugin、Coreとの統合方針を記録 |
| WordPress | [WordPress クラシックテーマ仕様](backend/wordpress/classic-theme.md) | `current`。Template、共通部品、Theme Support、Asset、表示、Metadata、コメント、メール、管理画面を記録 |
| WordPress | [WordPress コンテンツ・API仕様](backend/wordpress/content-and-api.md) | `current`。投稿、Taxonomy、検索、Archive、REST API、Ajaxを記録 |
| Docker | [Dockerローカル開発環境 概要仕様](server/docker/overview.md) | `current`。Service構成、Network、Volume、Host、HTTPS、環境変数、ローカル環境の境界を記録 |
| Docker | [Dockerローカル開発環境 セットアップ・運用手順](server/docker/setup-and-operations.md) | `current`。初回構築、日常操作、検証、トラブルシューティング、破棄時の注意を記録 |
| デザインシステム | [デザインシステム・UIコンポーネント仕様](frontend/ui/design-system.md) | `current`。デザイントークン、Storybook、UIコンポーネント、検証状況を記録 |
| Next.js | [Next.jsアプリケーション アーキテクチャ仕様](frontend/next/architecture.md) | `current`。実行環境、Static Export、ルーティング、コンポーネント境界を記録 |
| Next.js | [Next.jsアプリケーション 認証仕様](frontend/next/authentication.md) | `current`。Split Token、APIクライアント、認証ルーティング、フォームを記録 |
| Next.js | [Next.jsアプリケーション データ取得・投稿表示仕様](frontend/next/data-fetching.md) | `current`。TanStack Query、投稿一覧・詳細、ページネーション、実行時検証を記録 |
| Next.js | [Next.jsアプリケーション メタデータ・Googleタグ・PWA仕様](frontend/next/metadata-and-pwa.md) | `current`。Metadata、構造化データ、Googleタグ、サイトマップ、PWAを記録 |
| Next.js | [Next.jsアプリケーション テスト・品質検証仕様](frontend/next/testing.md) | `current`。Lint、型チェック、自動テスト、Static Export、CIを記録 |
| Next.js／Cloudflare | [Next.js Static Export — Cloudflare Workers Static Assets デプロイ仕様](infrastructure/cloudflare/next-static-deployment.md) | `current`。現在の設定、CI、Staging運用を記録 |

## 参照と更新の手順

仕様の確認または更新は、原則として次の順序で行います。

1. ルートの `AGENTS.md` とこのREADMEを読む
2. [`project/profile.md`](project/profile.md) で対象領域の採用状態を確認する（`active` が現在採用中）
3. 派生先に `specifications/project/brief.md` が存在する場合は確認する（`status: current` かつ `document_type: project-brief` の場合、用途・要件・デザイン方針の正本として参照）
4. 対象領域について、個別仕様書が現在仕様として適用できるか確認する（`active`、`applicable`、`status: current`）
5. 該当する個別仕様書を確認する。仕様書の扱いが `none` の領域は、コード・設定・テスト・CI を実装事実として確認する
6. 必要に応じてGit履歴、GitHub Issue、ユーザーから提供された過去資料から設計意図を確認する
7. 現在のコード、設定、テスト、CIと照合する
8. 一致する内容を現在仕様へ反映する
9. 不一致や未確定事項を、現在仕様、将来計画、要確認事項に分ける
10. 関連するリンク、パス、コマンド、環境変数名を検証する

派生プロジェクトの構成選択・初期化の詳細は [`project/profile.md`](project/profile.md) を参照してください。Project Brief の作成は [`project/templates/brief.md`](project/templates/brief.md) から行います。

## 更新時のルール

- 現在仕様には、実装済みで確認できる内容を現在形で記載します。
- 未実装の内容は `planned` な文書または明確に区別した将来項目へ記載します。
- 実装方法の長い時系列ではなく、現在の構造、責務、制約、検証方法を中心に記載します。
- コード例は仕様理解に必要な最小限とし、実装全体を複製しません。
- コード表記で示すリポジトリ内のファイルパスは、リポジトリルートからの相対パスで記載します。Markdownリンクのリンク先は、リンク元ファイルからの相対パスで記載します。
- コマンドには実行ディレクトリまたは前提条件を明記します。
- 環境変数の実値、秘密情報、ローカル固有の設定は記載しません。
- 文書の移動や名称変更を行った場合は、README、`AGENTS.md`、ツール固有の補助指示を含む参照元を更新します。

## 現在の構成

現在仕様として管理している文書は、次の構成です。新しい領域を追加するときは、対象コードの責務に合わせて分割単位を決め、この構成と「現在のドキュメント」を更新します。

```text
specifications/
├── README.md
├── project/
│   ├── profile.md
│   └── templates/
│       └── brief.md
├── backend/
│   ├── laravel/
│   │   ├── overview.md
│   │   ├── architecture.md
│   │   ├── screens-and-features.md
│   │   ├── database.md
│   │   ├── authentication.md
│   │   ├── contact-and-mail.md
│   │   ├── content-management.md
│   │   ├── file-management.md
│   │   ├── user-and-profile-management.md
│   │   └── validation-and-security.md
│   └── wordpress/
│       ├── overview.md
│       ├── classic-theme.md
│       └── content-and-api.md
├── frontend/
│   ├── ui/
│   │   └── design-system.md
│   └── next/
│       ├── architecture.md
│       ├── authentication.md
│       ├── data-fetching.md
│       ├── metadata-and-pwa.md
│       └── testing.md
├── infrastructure/
│   └── cloudflare/
│       └── next-static-deployment.md
└── server/
│   └── docker/
│       ├── overview.md
│       └── setup-and-operations.md
```

## 過去資料の扱い

古い仕様書や作業記録のための専用保存領域は設けません。次の条件を満たしてからリポジトリから削除します。

1. 現在も有効な設計意図と実装規約を抽出した
2. 現在のコード、設定、テスト、CIとの不一致を確認した
3. 必要な内容を `current` な仕様書へ移した
4. 削除後の参照先とリンクを更新した
5. 必要な設計意図や対応状況が、現在仕様またはGitHub Issueから確認できる状態にした

古い文書の削除と現在仕様の大幅な書き換えは、レビューしやすいよう原則として別コミットに分けます。削除後の内容や変更経緯はGit履歴から確認します。
