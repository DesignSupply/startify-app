# Startify-App Specifications

`specifications/` は、Startify-Appの現在の設計、実装規約、運用方法を、人間とAIエージェントが共有するためのドキュメント領域です。

このREADMEは、仕様書の読み方、状態、更新ルール、参照先を定義する索引です。リポジトリ全体に共通するAIエージェント向けの必須指示は、ルートの [`AGENTS.md`](../AGENTS.md) を参照してください。

## 基本方針

- 現在仕様は、既存の設計意図と現在のコード、設定、テスト、CIを照合して記録します。
- 仕様書では、現在実装されている内容と、未実装の方針や将来案を明確に区別します。
- 同じ仕様をREADME、Cursorルール、複数の仕様書へ重複して記載せず、正本となる文書へ参照を集約します。
- 実装変更で仕様が変わる場合は、関連する仕様書も同じ作業範囲で更新します。
- 仕様書と実装の不一致を発見した場合は、どちらかを根拠なく上書きせず、設計意図と現在の実装を確認します。
- 過去の実装手順は削除せず、現在仕様への情報移行後にArchiveへ移します。

## 文書の種類

### 現在仕様

現在の設計、実装規約、制約、外部インターフェイスを記録する正本です。機能を変更する際に最初に参照し、コードや設定の変更とともに更新します。

### 運用手順

セットアップ、検証、デプロイ、トラブルシューティングなど、繰り返し実行する操作を記録します。現在のコマンドや設定と一致することを維持します。

手順のうち、AIエージェントが繰り返し実行できる定型作業は、必要に応じて `.agents/skills/` へ切り出します。仕様書には目的、前提、完了条件を残し、Skillには実行手順を記載します。

### Archive

完了済みの実装タスク、廃止された設計、過去の移行手順を保存します。設計意図や変更履歴の調査には利用できますが、現在の実装指示としては使用しません。

## 文書ステータス

新しく作成または再編する仕様書は、次のステータスを明示します。

| ステータス | 意味 | 扱い |
| --- | --- | --- |
| `current` | 現在のコード、設定、運用と照合済み | 現在仕様の正本として参照する |
| `draft` | 内容を整理中、または照合が未完了 | 参考資料として扱い、実装前に確認する |
| `planned` | 未実装の方針や将来計画 | 現在仕様として適用しない |
| `archived` | 完了、廃止、置換済み | 履歴調査にのみ使用する |

ステータスがない既存文書は、移行が完了するまで `draft` 相当として扱います。ただし、後述する「現在仕様」に明示した文書は除きます。

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
- `status`: `current`、`draft`、`planned`、`archived` のいずれか
- `last_updated`: 内容を最後に実装と照合した日付
- `related_paths`: 仕様の根拠または主な適用対象となるリポジトリ相対パス

必要に応じて補足項目を追加できますが、実際に運用しないバージョン番号や所有者情報を形式だけで追加しないでください。

## 現在のドキュメント

### 現在仕様

| 領域 | 文書 | 状態 |
| --- | --- | --- |
| Laravel | [Laravelアプリケーション 概要仕様](backend/laravel/overview.md) | `current`。Laravelの役割、機能・認証境界、データ構成、検証状況を記録 |
| Laravel | [Laravelアプリケーション アーキテクチャ仕様](backend/laravel/architecture.md) | `current`。レイヤー責務、配置、命名、ルーティング、Blade構成を記録 |
| Laravel | [Laravelアプリケーション 画面・機能仕様](backend/laravel/screens-and-features.md) | `current`。画面、アクセス境界、Web処理、主要な画面遷移を記録 |
| Laravel | [Laravelアプリケーション データベース仕様](backend/laravel/database.md) | `current`。テーブル、制約、Relation、論理削除、Seederを記録 |
| Laravel | [Laravelアプリケーション 認証仕様](backend/laravel/authentication.md) | `current`。Session認証、新規登録、パスワード再設定、JWT認証APIを記録 |
| Laravel | [Laravelアプリケーション 問い合わせ・メール仕様](backend/laravel/contact-and-mail.md) | `current`。問い合わせフロー、Notification、メールTemplate、SMTP、Mailpitを記録 |
| Next.js | [Next.jsアプリケーション アーキテクチャ仕様](frontend/next/architecture.md) | `current`。実行環境、Static Export、ルーティング、コンポーネント境界を記録 |
| Next.js | [Next.jsアプリケーション 認証仕様](frontend/next/authentication.md) | `current`。Split Token、APIクライアント、認証ルーティング、フォームを記録 |
| Next.js | [Next.jsアプリケーション データ取得・投稿表示仕様](frontend/next/data-fetching.md) | `current`。TanStack Query、投稿一覧・詳細、ページネーション、実行時検証を記録 |
| Next.js | [Next.jsアプリケーション メタデータ・Googleタグ・PWA仕様](frontend/next/metadata-and-pwa.md) | `current`。Metadata、構造化データ、Googleタグ、サイトマップ、PWAを記録 |
| Next.js | [Next.jsアプリケーション テスト・品質検証仕様](frontend/next/testing.md) | `current`。Lint、型チェック、自動テスト、Static Export、CIを記録 |
| Next.js／Cloudflare | [Next.js Static Export — Cloudflare Workers Static Assets デプロイ仕様](infrastructure/cloudflare/next-static-deployment.md) | `current`。現在の設定、CI、Staging運用を記録 |

### 移行対象

| 領域 | 現在の配置 | 内容 |
| --- | --- | --- |
| Laravel | `specifications/backend/laravel/TASKS.md`、`specifications/backend/laravel/TASK_*.md` | 実装済み機能の作業手順。残る詳細仕様を抽出後にArchive予定 |
| WordPress | `specifications/backend/wordpress/TASKS.md` | 未整理のタスクリスト。現在のテーマ実装を基に仕様化予定 |
| Docker | `specifications/server/docker/TASKS.md`、`specifications/server/docker/TASK_*.md` | 環境構築手順。現在のCompose、Makefile、Nginx設定と照合後に再編予定 |

## 参照と更新の手順

仕様の確認または更新は、原則として次の順序で行います。

1. ルートの `AGENTS.md` とこのREADMEを読む
2. 対象領域の `current` な仕様書を確認する
3. 関連する既存の `TASK_*.md` や `.cursor/rules/` から設計意図を確認する
4. 現在のコード、設定、テスト、CIと照合する
5. 一致する内容を現在仕様へ反映する
6. 不一致や未確定事項を、現在仕様、将来計画、要確認事項に分ける
7. 関連するリンク、パス、コマンド、環境変数名を検証する

## 更新時のルール

- 現在仕様には、実装済みで確認できる内容を現在形で記載します。
- 未実装の内容は `planned` な文書または明確に区別した将来項目へ記載します。
- 実装方法の長い時系列ではなく、現在の構造、責務、制約、検証方法を中心に記載します。
- コード例は仕様理解に必要な最小限とし、実装全体を複製しません。
- コード表記で示すリポジトリ内のファイルパスは、リポジトリルートからの相対パスで記載します。Markdownリンクのリンク先は、リンク元ファイルからの相対パスで記載します。
- コマンドには実行ディレクトリまたは前提条件を明記します。
- 環境変数の実値、秘密情報、ローカル固有の設定は記載しません。
- 文書の移動や名称変更を行った場合は、README、`AGENTS.md`、Cursorルールを含む参照元を更新します。

## 推奨する構成

ドキュメント移行後は、次の構成を基本とします。名称や分割単位は、既存文書から現在仕様を抽出する過程で調整できます。

```text
specifications/
├── README.md
├── architecture/
│   └── repository-structure.md
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
│   │   └── validation-and-security.md
│   └── wordpress/
│       ├── overview.md
│       └── theme.md
├── frontend/
│   ├── design-system.md
│   └── next/
│       ├── architecture.md
│       ├── authentication.md
│       ├── data-fetching.md
│       ├── metadata-and-pwa.md
│       └── testing.md
├── infrastructure/
│   ├── local-docker.md
│   └── cloudflare/
│       └── next-static-deployment.md
└── archive/
    ├── backend/laravel/
    └── server/docker/
```

この構成は移行先の指針であり、未作成の文書を現在仕様として扱うものではありません。

## Archiveへの移動条件

既存のタスク文書は、次の条件を満たしてからArchiveへ移します。

1. 現在も有効な設計意図と実装規約を抽出した
2. 現在のコード、設定、テスト、CIとの不一致を確認した
3. 必要な内容を `current` な仕様書へ移した
4. 移動後の参照先とリンクを更新した
5. Git差分上で、内容の削除ではなく移動として追跡できる状態にした

Archiveへの移動と現在仕様の大幅な書き換えは、レビューしやすいよう原則として別コミットに分けます。
