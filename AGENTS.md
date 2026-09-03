# Startify-App Agent Guidelines

この文書は、Startify-Appで作業するAIエージェントに共通する必須指示です。特定のエディターやエージェント製品に依存せず、リポジトリ全体に適用します。

## リポジトリの概要

Startify-Appは、WebアプリケーションおよびWebサイトの開発基盤です。主に次の領域で構成されています。

- `backend/laravel/`: LaravelアプリケーションとAPI
- `backend/wordpress/`: WordPress本体とクラシックテーマ
- `frontend/next/`: Next.jsアプリケーション
- `frontend/nuxt/`: Nuxt.js用の未実装予約領域
- `frontend/astro/`: Astroアプリケーション
- `frontend/vite/`: Viteベースのフロントエンド環境
- `frontend/ui/`: StorybookとUIコンポーネント
- `frontend/_design-tokens/`: 共通デザイントークン
- `frontend/react-native/`: React Native用の未実装予約領域
- `server/`: Docker Compose、Nginx、PHP、MariaDB、Mailpitのローカル環境
- `specifications/`: 人間とAIが共有する現在仕様および関連資料

## 指示と情報の優先順位

指示や資料が矛盾する場合は、原則として次の順序で判断してください。

1. 現在の依頼におけるユーザーからの明示的な指示
2. 対象ファイルに適用される、より深い階層の `AGENTS.md`
3. このルート `AGENTS.md`
4. `specifications/project/profile.md` のプロジェクト採用範囲
5. `specifications/project/brief.md`（存在し、`status: current` かつ `document_type: project-brief` として現在要件として有効な場合）
6. `specifications/` の現在仕様（[§3の条件](specifications/project/profile.md#3-文書-status-と領域の採用状態の違い)を満たす個別仕様書）
7. 対象コード、設定、テスト、CIが示す現在の実装事実
8. `.cursor/rules/` など、ツール固有の補助指示
9. Git履歴、完了済みのGitHub Issue、ユーザーから提供された過去資料

仕様書と実装が一致しない場合は、片方を根拠なく正しいものとして上書きしないでください。差異を確認し、依頼の範囲内で解消できない場合はユーザーへ報告してください。

## 作業開始時の確認

変更を始める前に、次を確認してください。

1. 現在のブランチと `git status`
2. staged、unstaged、untrackedを含む既存差分
3. 依頼の対象範囲と変更が必要なファイル
4. `specifications/project/profile.md` の採用状態・コード状態・仕様書の扱い
5. 派生先に `specifications/project/brief.md` が存在する場合は、その内容（`status: current` かつ `document_type: project-brief` の場合は用途・要件・デザイン方針の正本として確認）
6. 対象領域に関係する仕様書、設定、テスト、CI
7. 変更後に必要となる検証

調査やレビューだけを依頼された場合は、明示的に変更も求められていない限り、ファイルを変更しないでください。

## 既存差分の保護

- ユーザーや他のエージェントが作成した既存差分を上書き、削除、取り消ししないでください。
- 依頼と無関係なファイルの修正、整形、リネームを行わないでください。
- 既存差分と作業対象が重なる場合は、内容を確認して既存の意図を維持してください。
- 予期しない変更は保護し、作業対象と重複して安全に進められない場合は、勝手に復元せずユーザーへ報告してください。
- `.env`、秘密鍵、証明書、トークン、認証情報などを追跡対象へ追加しないでください。
- ビルド出力、キャッシュ、依存ディレクトリなど、Git管理対象外の生成物を追加しないでください。

## 変更方針

- 依頼を満たすために必要な最小範囲を変更してください。
- 無関係なリファクタリングや依存パッケージ更新を同じ変更へ混ぜないでください。
- 対象領域で使われている命名、構造、実装パターンを確認し、それに合わせてください。
- 新しい依存パッケージは、既存機能で代替できないこと、導入の必要性、影響範囲を確認してから追加してください。
- コードを変更する場合は、関連する仕様、テスト、設定、CIへの影響を確認してください。
- 仕様を変更する場合は、現在のコードや設定に裏付けられた記述になっていることを確認してください。

## 仕様書の扱い

- `specifications/project/profile.md` を、プロジェクトの**適用範囲**の正本として扱います。作業開始時に確認し、採用状態が `active` の領域を現在採用中として扱います。
- 派生先に `specifications/project/brief.md` が存在する場合は、作業開始時に確認します。`status: current` かつ `document_type: project-brief` の Brief を、用途・要件・デザイン方針の正本として扱います。`status: current` かつ `decided` な要件を判断基準として使います。Startify-App本体には `brief.md` が存在しないため、存在する現在仕様として扱いません。
- Brief の `status: current` は要件文書として確認済み・有効であることを表し、**実装完了を意味しません**。実装の完了状況はコード、テスト、Issue、領域別仕様書で確認します。
- `tbd` を勝手に確定しません。`tbd` が作業を左右する場合はユーザーへ確認します。
- `not-applicable` な機能を勝手に追加しません。
- `profile.md` と `brief.md` が矛盾する場合は、根拠なく変更せず報告します。
- Project Brief Template の利用手順は [`specifications/project/templates/brief.md`](specifications/project/templates/brief.md) を参照してください。Secret や個人情報を Brief へ記録しません。
- 個別仕様書を現在仕様として適用する条件は、領域が `active`、仕様書の扱いが `applicable`、文書 `status` が `current` です。詳細は [`specifications/project/profile.md`](specifications/project/profile.md) を参照してください。
- 採用状態が `planned` または `inactive` の領域を、現在実装済みとして扱いません。
- 採用状態が `active` でも、仕様書の扱いが `none` の領域（例: Vite、Astro）は、コード・設定・テスト・CI を実装事実として確認します。
- 文書 `status` が `planned` または `draft` の領域別仕様書を、実装と照合して `current` へ更新するまで現在仕様として適用しません。
- コードが存在しない領域では、領域別仕様書内のパスやコマンドが派生先に実在するとは限りません。
- 未採用コードをユーザーの指示なく削除しません。構成変更時は、構成表と実装・設定・仕様書を同期します。
- `specifications/` 内の詳細手順は [`specifications/project/profile.md`](specifications/project/profile.md) を参照してください。
- `specifications/` は、現在の設計と実装規約を記録する正本です。文書のステータスを確認し、`current`な文書を現在仕様として扱ってください。
- Git履歴、完了済みのGitHub Issue、ユーザーから提供された過去資料は、設計意図や変更履歴を調査する目的でのみ参照してください。
- リポジトリ外の過去資料から現在仕様へ内容を移す場合は、資料の意図を確認したうえで、コード、設定、テスト、CIと照合してください。
- READMEには利用者向けの概要と導線を置き、詳細仕様やエージェント向け指示を重複させないでください。
- 文書には、リポジトリ内に実在するパス、スクリプト、環境変数、設定値を記載してください。
- 未実装の方針、将来案、確認できない要件は、現在仕様と区別して明記してください。

## 現在の参照ルーティング

対象領域に応じて、次の資料と現在の実装を併せて確認してください。

| 対象 | 主な参照先 |
| --- | --- |
| プロジェクト採用範囲、派生運用 | `specifications/project/profile.md` |
| Project Brief Template | `specifications/project/templates/brief.md` |
| 派生先 Project Brief（存在する場合） | `specifications/project/brief.md` |
| リポジトリ概要、セットアップ | `README.md` |
| Laravelの概要、画面、API、DB、認証 | `specifications/backend/laravel/overview.md`、`specifications/backend/laravel/screens-and-features.md`、`specifications/backend/laravel/database.md`、`specifications/backend/laravel/authentication.md`、`specifications/backend/laravel/` |
| Laravelの問い合わせ、メール | `specifications/backend/laravel/contact-and-mail.md`、`backend/laravel/app/Notifications/`、`backend/laravel/resources/views/emails/` |
| Laravelの投稿、カテゴリ、タグ | `specifications/backend/laravel/content-management.md`、`backend/laravel/app/Models/`、`backend/laravel/app/Http/Controllers/` |
| Laravelのファイル管理 | `specifications/backend/laravel/file-management.md`、`backend/laravel/app/Services/UploadedFileService.php`、`backend/laravel/config/filesystems.php` |
| Laravelのユーザー、プロフィール管理 | `specifications/backend/laravel/user-and-profile-management.md`、`backend/laravel/app/Http/Controllers/ProfileController.php`、`backend/laravel/app/Http/Controllers/AdminProfileController.php`、`backend/laravel/app/Http/Controllers/AdminUsersController.php` |
| LaravelのValidation、Security | `specifications/backend/laravel/validation-and-security.md`、`backend/laravel/app/Http/Requests/`、`backend/laravel/app/Http/Middleware/`、`backend/laravel/config/` |
| Laravelの実装規約 | `specifications/backend/laravel/architecture.md`、`backend/laravel/` |
| WordPress | `specifications/backend/wordpress/overview.md`、`specifications/backend/wordpress/classic-theme.md`、`specifications/backend/wordpress/content-and-api.md`、`backend/wordpress/` |
| Next.js | `specifications/frontend/next/architecture.md`、`specifications/frontend/next/authentication.md`、`specifications/frontend/next/data-fetching.md`、`specifications/frontend/next/metadata-and-pwa.md`、`specifications/frontend/next/testing.md`、`specifications/frontend/next/`、`frontend/next/` |
| デザイントークン、UI | `specifications/frontend/ui/design-system.md`、`frontend/_design-tokens/`、`frontend/ui/` |
| Docker、ローカル環境 | `specifications/server/docker/overview.md`、`specifications/server/docker/setup-and-operations.md`、`server/` |
| Next.jsのCloudflare配信 | `specifications/infrastructure/cloudflare/next-static-deployment.md` |

## 検証方針

- 変更範囲とリスクに応じた、最小限かつ十分な検証を行ってください。
- テストだけでなく、必要に応じてlint、型チェック、ビルド、設定検証も行ってください。
- ドキュメント変更では、リンク、ファイルパス、コマンド、環境変数名、設定値が現在のリポジトリと一致することを確認してください。
- 検証によって生成されたGit管理対象外ファイルをコミットしないでください。
- 実行できなかった検証や失敗した検証は、理由とともに最終報告へ記載してください。
- 検証目的で外部環境へデプロイしないでください。

代表的な検証コマンドは次のとおりです。変更対象に必要なものだけを実行してください。

Next.js:

```bash
cd frontend/next
npm run check
npm run build:cf
```

Laravel（Docker環境起動後）:

```bash
cd server
make laravel-test
make laravel-route
```

Dockerコンテナー状態:

```bash
cd server
make ps
```

## Gitと外部状態の変更

- 明示的な依頼なしに、stage、commit、push、pull request作成を行わないでください。
- ブランチの作成や切り替えは、依頼内容と現在の作業状態を確認してから行ってください。
- デプロイ、外部サービスの設定変更、メール送信などを、検証の一環として勝手に実行しないでください。
- DB削除、ボリューム削除、追跡ファイルの削除など、復元が難しい操作は明示的な承認なしに実行しないでください。
- とくに `make destroy` はコンテナー、イメージ、ボリュームを削除するため、明示的な依頼がある場合に限って実行してください。
- コミットを依頼された場合は、レビュー可能な論理単位に分け、無関係な変更を含めないでください。

## 作業完了時の報告

最終報告には、必要に応じて次を含めてください。

- 変更または調査した内容
- 重要な設計判断と前提
- 実行した検証と結果
- 実行できなかった検証と理由
- 残っている課題や確認事項
- stage、commit、push、デプロイなどを行った場合はその結果
