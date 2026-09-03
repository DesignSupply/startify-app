---
title: Project Brief Template
status: current
last_updated: 2026-09-03
document_type: template
related_paths:
  - specifications/project/profile.md
  - specifications/README.md
  - AGENTS.md
  - README.md
---

# Project Brief Template

Startify-Appから派生するWebサイトやWebアプリケーションについて、用途・要件・デザイン方針・品質基準を共通形式で記録するTemplateです。

## 派生プロジェクトでの作成方法

派生プロジェクトでは、本Templateを複製し、次のファイルを作成してください。

```text
specifications/project/brief.md
```

作成直後のFront Matter例:

```yaml
---
title: <プロジェクト名> Project Brief
status: draft
last_updated: YYYY-MM-DD
document_type: project-brief
related_paths:
  - specifications/project/profile.md
---
```

**注意**

- `<プロジェクト名>`、`YYYY-MM-DD` などのPlaceholderは、作成時に必ず実際の値へ置換してください。空欄のまま残さないでください。
- `<記入>` などのPlaceholderも、Template利用時に必ず `decided`、`tbd`、`not-applicable`、または具体的な内容へ置換してください。Placeholderを確定値と誤解しないでください。
- Secret、Token、個人情報、非公開データは記録しないでください。

### Project Briefの `status`

| `status` | 意味 |
| --- | --- |
| `draft` | 作成直後、または要件整理中。実装判断の正本としては扱わない |
| `current` | 要件文書として確認済み・有効。**実装完了を意味しない** |

運用の流れ:

1. Templateから `brief.md` を作成し、作成直後は `status: draft` とする
2. ユーザーまたは意思決定者が内容を確認する
3. 実装判断に必要な要件を `decided` または `not-applicable` として整理する
4. 未決定事項（`tbd`）が残る場合は、現在の実装範囲を妨げないことを確認する
5. 要件文書として承認された段階で `status: current` へ変更する
6. 実装の完了状況は、コード、テスト、Issue、領域別仕様書で確認する
7. Briefと実装が不一致の場合は、根拠なくどちらかを書き換えない

`current` にできる目安:

- 現在の実装範囲に影響する `tbd` がない
- 残った `tbd` には確認方法または関連Issueがある
- 初回リリースScopeが判断できる
- ユーザーまたは意思決定者が確認済み

すべての項目が `decided` になるまで `current` にできない構成にはしません。

## 1. Templateの使い方

### 1.1. 作成手順

1. 本Template（`specifications/project/templates/brief.md`）を確認する
2. 内容を `specifications/project/brief.md` へ複製する
3. Front Matterを派生プロジェクト向けに更新する（`document_type: project-brief`、`status: draft`）
4. 各項目のPlaceholderを置換し、状態列に `decided`、`tbd`、`not-applicable` のいずれかを設定する
5. [`profile.md`](../profile.md) と役割が重複しないよう、採用技術の詳細はProfileへ委ねる
6. ユーザーまたは意思決定者が確認後、上記の目安を満たした段階で `status: current` へ変更する

### 1.2. 決定状態

各項目は、空欄ではなく次のいずれかで状態を明示してください。

| 状態 | 意味 |
| --- | --- |
| `decided` | 決定済み。実装・設計の判断基準として使用できる |
| `tbd` | 未決定。推測せず、確認または別Issueで決定する |
| `not-applicable` | このプロジェクトでは対象外 |

`tbd` には、可能であれば次を記録してください。

- 確認事項
- 判断者または役割
- 決定期限
- 関連Issue
- 現在の実装への影響

`not-applicable` の場合は、必要に応じて対象外とした理由を内容列へ記録してください。

### 1.3. 記入形式

主要項目は、次の表形式を基本とします。

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| 認証 | `not-applicable` | 初回リリースでは使用しない |
| CMS | `tbd` | 更新頻度を確認後に決定する |
| Hosting | `decided` | `<採用先を記入>` |

### 1.4. 文書の役割分担

| 文書 | 役割 |
| --- | --- |
| [`profile.md`](../profile.md) | 採用技術、コード状態、仕様書の適用範囲 |
| `specifications/project/brief.md` | 派生プロジェクト固有の目的、要件、デザイン方針、品質基準 |
| 領域別仕様書 | 各技術領域の詳細な設計・実装仕様 |
| GitHub Issue | 個別の変更内容、作業単位、完了条件 |

例: 認証について

- **Brief**: 認証が必要か、誰に必要か
- **Profile**: どの認証基盤やBackendを採用するか
- **領域別仕様書**: Cookie、Token、CSRF、APIなどの詳細

### 1.5. 記録してはいけないもの

- Secret、Token、API Key、パスワード
- 個人情報、非公開データ、本番環境の実値
- 環境変数の実値

### 1.6. AIエージェント向けルール

- `decided` な内容を実装・設計の判断基準として扱う
- `tbd` を推測で確定しない
- `tbd` が実装を左右する場合はユーザーへ確認するかIssueへ分離する
- `not-applicable` な機能を勝手に追加しない
- 将来候補を初回リリースへ混在させない
- Briefと [`profile.md`](../profile.md) が矛盾する場合は、根拠なく変更せず報告する
- Brief変更によるコード・仕様・テストへの影響を確認する
- 実装前にBriefと対象Issueの完了条件を確認する
- デザインが `tbd` の場合、一般的なTemplate表現を最終デザインとして確定しない
- 参考サイトをそのまま複製しない
- Secret、Token、個人情報、非公開データを記録しない

---

以下は、派生プロジェクトの `brief.md` へ複製した後に記入するセクションです。Placeholderをそのまま残さないでください。

## 2. プロジェクト概要

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| プロジェクト名 | `tbd` | `<記入>` |
| サイトまたはアプリケーションの種類 | `tbd` | `<例: コーポレートサイト / Webアプリケーション / ランディングページ>` |
| 背景 | `tbd` | `<記入>` |
| 目的 | `tbd` | `<記入>` |
| 解決したい課題 | `tbd` | `<記入>` |
| 成功条件 | `tbd` | `<記入>` |
| 初回リリース予定 | `tbd` | `<記入>` |
| 意思決定者 | `tbd` | `<役割で記入。例: プロダクトオーナー、デザイン責任者>` |
| 関連資料 | `tbd` | `<必要に応じて記入>` |

## 3. 対象ユーザーと利用場面

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| 想定ユーザー | `tbd` | `<記入>` |
| ユーザーの目的 | `tbd` | `<記入>` |
| 主な利用場面 | `tbd` | `<記入>` |
| 主な利用端末 | `tbd` | `<例: Desktop / Mobile / Tablet>` |
| 利用環境 | `tbd` | `<例: 社内 / 一般公開 / 会員限定>` |
| 主要なユーザーフロー | `tbd` | `<必要に応じて記入>` |
| Accessibility上の考慮事項 | `tbd` | `<記入>` |

架空のPersona作成は必須ではありません。

## 4. Scope

| 区分 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| 初回リリースに含める | `tbd` | `<記入>` |
| 初回リリースに含めない | `tbd` | `<記入>` |
| 将来候補 | `tbd` | `<記入>` |
| 明示的な対象外 | `tbd` | `<記入>` |
| 既知の制約 | `tbd` | `<記入>` |

**ルール**: 将来候補は現在要件として扱わない。初回リリースScopeと混在させない。

## 5. ページ・画面・機能

画面が不要なプロジェクトでは、該当行を `not-applicable` としてください。

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| ページまたは画面 | `tbd` | `<記入>` |
| 各ページの目的 | `tbd` | `<必要に応じて記入>` |
| 主要コンテンツ | `tbd` | `<記入>` |
| 必須機能 | `tbd` | `<記入>` |
| 入力フォーム | `tbd` | `<記入>` |
| 認証 | `tbd` | `<必要か、誰に必要か>` |
| 権限 | `tbd` | `<記入>` |
| Loading State | `tbd` | `<記入>` |
| Empty State | `tbd` | `<記入>` |
| Error State | `tbd` | `<記入>` |
| 外部サービス連携 | `tbd` | `<記入>` |

## 6. コンテンツ・データ

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| コンテンツの種類 | `tbd` | `<記入>` |
| コンテンツ提供者 | `tbd` | `<記入>` |
| 更新担当 | `tbd` | `<記入>` |
| 更新頻度 | `tbd` | `<記入>` |
| データ取得元 | `tbd` | `<記入>` |
| CMS | `tbd` | `<記入>` |
| API | `tbd` | `<記入>` |
| Static ContentとDynamic Content | `tbd` | `<記入>` |
| 画像 | `tbd` | `<記入>` |
| 動画 | `tbd` | `<記入>` |
| Download File | `tbd` | `<記入>` |
| 多言語 | `tbd` | `<記入>` |
| Privacy・Legal関連コンテンツ | `tbd` | `<記入>` |

実データ、個人情報、Token、認証情報は記録しないでください。

## 7. ブランド・デザイン方針

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| ブランドの目的 | `tbd` | `<記入>` |
| ブランドの印象 | `tbd` | `<記入>` |
| デザインキーワード | `tbd` | `<記入>` |
| Tone & Manner | `tbd` | `<記入>` |
| 色の方向性 | `tbd` | `<記入>` |
| Typography | `tbd` | `<記入>` |
| 余白 | `tbd` | `<記入>` |
| 情報密度 | `tbd` | `<記入>` |
| 角、線、影 | `tbd` | `<記入>` |
| 写真 | `tbd` | `<記入>` |
| イラスト | `tbd` | `<記入>` |
| Icon | `tbd` | `<記入>` |
| Layout | `tbd` | `<記入>` |
| Responsive Design | `tbd` | `<記入>` |
| Animation | `tbd` | `<記入>` |
| Interaction | `tbd` | `<記入>` |
| Dark Mode | `tbd` | `<記入>` |
| 参考サイト・参考資料 | `tbd` | `<URLまたは資料名>` |
| 避けたい表現 | `tbd` | `<記入>` |
| 既存ブランドガイド | `tbd` | `<記入>` |

「モダン」「おしゃれ」などの抽象語だけで完結させず、目指す表現と避ける表現を比較できる形式で記録してください。

記入例（プロジェクト固有の確定値ではありません）:

```text
目指す表現:
静か、信頼感、余白が広い、編集的、専門性がある

避ける表現:
過度なGradient、派手なAnimation、情報密度の高いDashboard風
```

Color Token、Component、Spacingなどの詳細仕様は、デザインシステム仕様（[`frontend/ui/design-system.md`](../../frontend/ui/design-system.md)）へ分離してください。

## 8. 品質要件

数値目標や対応範囲が未決定の場合は推測せず `tbd` としてください。

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| 対応Browser | `tbd` | `<記入>` |
| 対応端末 | `tbd` | `<記入>` |
| Responsive要件 | `tbd` | `<記入>` |
| Accessibility目標 | `tbd` | `<記入>` |
| Performance目標 | `tbd` | `<記入>` |
| Core Web Vitals | `tbd` | `<記入>` |
| SEO | `tbd` | `<記入>` |
| Metadata | `tbd` | `<記入>` |
| 構造化データ | `tbd` | `<記入>` |
| Sitemap | `tbd` | `<記入>` |
| OGP | `tbd` | `<記入>` |
| Analytics | `tbd` | `<記入>` |
| Error Monitoring | `tbd` | `<記入>` |
| Security | `tbd` | `<記入>` |
| Privacy | `tbd` | `<記入>` |
| Test方針 | `tbd` | `<記入>` |
| Visual Regression Test | `tbd` | `<記入>` |
| CI要件 | `tbd` | `<記入>` |

## 9. 技術的制約

採用技術の正本は [`profile.md`](../profile.md) です。Briefには、プロジェクト要件に影響する制約だけを記録してください。

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| `profile.md` との整合 | `tbd` | [`profile.md`](../profile.md) を確認し、採用技術と矛盾がないことを確認する |
| Hosting | `tbd` | `<要件としての制約>` |
| Domain | `tbd` | `<記入>` |
| Environment | `tbd` | `<記入>` |
| Browser制約 | `tbd` | `<記入>` |
| 外部サービス | `tbd` | `<記入>` |
| 既存システムとの連携 | `tbd` | `<記入>` |
| データ保存場所 | `tbd` | `<記入>` |
| 認証要件 | `tbd` | `<Brief上の要件。実装詳細は領域別仕様書>` |
| PackageやLicense上の制約 | `tbd` | `<記入>` |

## 10. 運用・リリース

未実装の運用方法を現在の手順として断定しないでください。

| 項目 | 状態 | 内容・判断基準 |
| --- | --- | --- |
| Hosting先 | `tbd` | `<記入>` |
| Domain | `tbd` | `<記入>` |
| Development | `tbd` | `<記入>` |
| Staging | `tbd` | `<記入>` |
| Production | `tbd` | `<記入>` |
| コンテンツ更新方法 | `tbd` | `<記入>` |
| 運用担当 | `tbd` | `<役割で記入>` |
| Backup | `tbd` | `<記入>` |
| Monitoring | `tbd` | `<記入>` |
| Analytics確認 | `tbd` | `<記入>` |
| リリース条件 | `tbd` | `<記入>` |
| Rollback方針 | `tbd` | `<記入>` |
| 保守方針 | `tbd` | `<記入>` |
| 初回リリース後の優先事項 | `tbd` | `<記入>` |

## 11. Acceptance Criteria

すべてを一律に必須とせず、`decided`、`tbd`、`not-applicable` で管理してください。可能な範囲で検証方法または根拠を記録してください。

| 項目 | 状態 | 内容・判断基準・検証方法 |
| --- | --- | --- |
| 必須ページ・画面 | `tbd` | `<記入>` |
| 必須機能 | `tbd` | `<記入>` |
| 対象外機能が混在していない | `tbd` | `<記入>` |
| デザイン方針 | `tbd` | `<記入>` |
| Responsive表示 | `tbd` | `<記入>` |
| Accessibility | `tbd` | `<記入>` |
| Performance | `tbd` | `<記入>` |
| SEO・Metadata | `tbd` | `<記入>` |
| Test | `tbd` | `<記入>` |
| Build | `tbd` | `<記入>` |
| Deployment | `tbd` | `<記入>` |
| コンテンツ更新方法 | `tbd` | `<記入>` |
| 運用引き継ぎ | `tbd` | `<記入>` |

## 12. 未決定事項

| 項目 | 状態 | 確認内容 | 判断者・役割 | 期限 | 関連Issue | 実装への影響 |
| --- | --- | --- | --- | --- | --- | --- |
| `<項目>` | `tbd` | `<確認内容>` | `<役割>` | `<期限>` | `<Issue番号>` | `<影響>` |

## 13. 意思決定

長い作業履歴は記録せず、詳細はGitHub IssueやGit履歴へ委ねてください。

| 項目 | 状態 | 決定内容 | 決定理由 | 関連Issue | 影響範囲 |
| --- | --- | --- | --- | --- | --- |
| `<項目>` | `decided` | `<決定内容>` | `<理由>` | `<Issue番号>` | `<影響範囲>` |

## 14. 関連資料

| 資料 | 参照 |
| --- | --- |
| プロジェクト構成 | [`profile.md`](../profile.md) |
| 仕様書索引 | [`specifications/README.md`](../../README.md) |
| エージェント共通指示 | [`AGENTS.md`](../../../AGENTS.md) |
| 利用者向け導線 | [`README.md`](../../../README.md) |
| 領域別仕様書 | [`profile.md`](../profile.md) の構成表から該当領域を参照 |
| 関連Issue | `<GitHub Issue URL>` |
| Design資料 | `<URLまたは資料名>` |
| Content資料 | `<URLまたは資料名>` |
| 外部資料 | `<URL>` |
