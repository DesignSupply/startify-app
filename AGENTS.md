# Startify-App Agent Guidelines

この文書は、Startify-Appで作業するAIエージェントに共通する必須指示です。特定のエディターやエージェント製品に依存せず、リポジトリ全体に適用します。

## リポジトリの概要

Startify-Appは、WebアプリケーションおよびWebサイトの開発基盤です。主に次の領域で構成されています。

- `backend/laravel/`: LaravelアプリケーションとAPI
- `backend/wordpress/`: WordPress本体とクラシックテーマ
- `frontend/next/`: Next.jsアプリケーション
- `frontend/astro/`: Astroアプリケーション
- `frontend/vite/`: Viteベースのフロントエンド環境
- `frontend/ui/`: StorybookとUIコンポーネント
- `frontend/_design-tokens/`: 共通デザイントークン
- `server/`: Docker Compose、Nginx、PHP、MariaDB、Mailpitのローカル環境
- `specifications/`: 人間とAIが共有する現在仕様および関連資料

## 指示と情報の優先順位

指示や資料が矛盾する場合は、原則として次の順序で判断してください。

1. 現在の依頼におけるユーザーからの明示的な指示
2. 対象ファイルに適用される、より深い階層の `AGENTS.md`
3. このルート `AGENTS.md`
4. `specifications/` の現在仕様
5. 対象コード、設定、テスト、CIが示す現在の実装事実
6. `.cursor/rules/` など、ツール固有の補助指示
7. Archiveされた資料や完了済みの実装タスク

仕様書と実装が一致しない場合は、片方を根拠なく正しいものとして上書きしないでください。差異を確認し、依頼の範囲内で解消できない場合はユーザーへ報告してください。

## 作業開始時の確認

変更を始める前に、次を確認してください。

1. 現在のブランチと `git status`
2. staged、unstaged、untrackedを含む既存差分
3. 依頼の対象範囲と変更が必要なファイル
4. 対象領域に関係する仕様書、設定、テスト、CI
5. 変更後に必要となる検証

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

- `specifications/` は、現在の設計と実装規約を記録する正本として整備します。ドキュメントの移行が完了するまでは、現在仕様として明示された文書のみを正本として扱ってください。
- `TASK_*.md` と `TASKS.md` には完了済みの実装手順や古い記述が含まれるため、現在仕様として無条件に適用しないでください。
- 完了済みタスクやArchive資料は、設計意図や変更履歴を調査する目的で参照してください。
- 現在仕様へ内容を移す場合は、既存文書の意図を確認したうえで、コード、設定、テスト、CIと照合してください。
- READMEには利用者向けの概要と導線を置き、詳細仕様やエージェント向け指示を重複させないでください。
- 文書には、リポジトリ内に実在するパス、スクリプト、環境変数、設定値を記載してください。
- 未実装の方針、将来案、確認できない要件は、現在仕様と区別して明記してください。

## 現在の参照ルーティング

ドキュメント再編が完了するまでは、次の資料と現在の実装を併せて確認してください。

| 対象 | 主な参照先 |
| --- | --- |
| リポジトリ概要、セットアップ | `README.md` |
| Laravelの画面、API、DB | `.cursor/rules/app-overview.mdc`、`specifications/backend/laravel/` |
| Laravelの実装規約 | `.cursor/rules/dev-backend.mdc`、`backend/laravel/` |
| WordPress | `.cursor/rules/cms-overview.mdc`、`specifications/backend/wordpress/` |
| Next.js | `specifications/frontend/next/architecture.md`、`specifications/frontend/next/authentication.md`、`.cursor/rules/dev-frontend.mdc`、`specifications/frontend/next/`、`frontend/next/` |
| デザイントークン、UI | `.cursor/rules/app-design.mdc`、`frontend/_design-tokens/`、`frontend/ui/` |
| Docker、ローカル環境 | `.cursor/rules/env-overview.mdc`、`specifications/server/docker/`、`server/` |
| Next.jsのCloudflare配信 | `specifications/infrastructure/cloudflare/next-static-deployment.md` |

`.cursor/rules/` と既存の `TASK_*.md` は移行前の資料を含みます。記述が現在の実装と一致することを確認してから利用してください。

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
