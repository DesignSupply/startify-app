---
title: Next.jsアプリケーション開発タスクリスト:APIデータのキャッシュ・同期ステート管理（ReactQuery）
id: next_task_011
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

<!-- markdownlint-disable MD025 -->
# Next.jsアプリケーション開発タスクリスト:APIデータのキャッシュ・同期ステート管理（ReactQuery）

---

## 1. モックデータの設計と配置

### 1.1. モックJSONファイル配置

`/public/mock/posts.json` に、APIの本番想定レスポンスとなる投稿一覧を格納するJSONファイルを新たに作成する。ひとまず `[{ id, publishedAt, author, title, body }]` 構造で10件分を並べ、日付や投稿者名などは多様な値を入れておく。

### 1.2. データの想定内容

- `id`: 一意の数値
- `publishedAt`: ISO 8601（例: `2025-12-01T10:00:00Z`）で、一覧でソート可能な値
- `author`: `displayName` や `handle` のような文字列
- `title`: 画面に表示される見出し
- `body`: 本文テキスト（段落やHTMLを含んでもよい）

各オブジェクトは必要に応じてタグやカテゴリなどの補助フィールドを含めても良いが、上述の5項目が必須となるよう調整し、実装中に `Zod`などで検証しやすい形にしておく。

### 1.3. キャッシュポリシーの設計

このJSONを基に参考となるqueryKey（例: `['posts']`、詳細ページ用に `['posts', id]`）とstaleTime値を想定し、「何をキャッシュ」ではなく「どの単位で持つか」を明文化しておく。  

- **一覧（`['posts']`）**  
  - `staleTime`: 10秒程度（連続表示中は再フェッチしない）  
  - `cacheTime`: 5分（タブを閉じるまでキャッシュ保持）  
  - `refetchOnWindowFocus`: `false`（Provider側の設定）  
  - 操作に応じて `invalidateQueries(['posts'])` を使い、都度最新化できるようにする  

- **詳細（`['posts', id]`）**  
  - `staleTime`: 0（常に最新）  
  - `cacheTime`: 1分程度（直後の戻りで再利用可）  
  - `enabled`: `Boolean(id)`  
  - `queryKey`にIDを含めることで同一 `id` 同士のキャッシュを共有させる  

将来的に`/public/mock/posts.json` から`/api/posts`へ切り替える場合は、`helpers/api.ts`のbaseURLを環境変数で切り替えるだけで`queryKey`の構造は変更せずそのまま利用可能であることを追記しておく。

---

## 2. モックデータ用型定義の整備

### 2.1. 投稿データ型の定義

`/frontend/next/src/types/posts.ts`（または既存の型定義ファイル）に以下を定義する。`Post` に `id`、`publishedAt`、`author`、`title`、`body`（HTML含む）を入れ、`PostListResponse = Post[]` のように型をまとめる。`publishedAt` は `string`（ISO）として扱い、フック内で `new Date()` 変換が必要な箇所には注記する。

この型はReact Queryフック`usePostsQuery`/`usePostQuery`、さらに後続の一覧・詳細コンポーネントとVitestテストの両方からimportして再利用する。`@/types/posts`から直接 `Post` を取り込み、ログインフォームのスキーマも将来的に外だししたときに同様に直接インポートするスタイルで統一する。

### 2.2. Zod によるバリデーションスキーマ（オプション）

`zod`を採用している場合は`src/schemas/posts.ts`に`postSchema` / `postsSchema`を定義し、`mock/posts.json`を読み込む段階で`safeParse`してデータ構造を検証する。また、React Queryの`select`で`z.parse`を通す使い方も想定する。

### 2.3. 型定義とドキュメントの連携

`TASK_011` で用意した `posts.json` のフォーマットと型定義を一致させる。型・スキーマの変更があった場合は `mock/posts.json` も更新し、`TODO/README` などに「型更新時の手順」や `zod` の`strict` オプションを使う観点を記録しておく。

---

## 3. React Query Providerの構成確認

### 3.1. Provider配置の確認

`/frontend/next/src/providers/ReactQueryProvider.tsx`をルートレベル（`/src/app/layout.tsx`）でしっかりラップしているか確認し、今回追加した`usePostsQuery`系の各フックからも同一の`QueryClient`を参照できるようにする。また、`'use client'`の配置漏れがないかもチェック。

### 3.2. QueryClientの設定確認

既存の`defaultOptions`（`staleTime`/`refetchOnWindowFocus`/`retry`）が本番APIのキャッシュポリシーに合致するか再確認し、必要なら`queries`側で一覧用（`['posts']`）と詳細用（`['posts', id]`）のオーバーライドを行う形にする。`mutation`の`retry`設定もAPI仕様に準じておく。

### 3.3. Devtoolsと環境判定

開発環境のみ`ReactQueryDevtools`を読み込ませる記述があるか、`process.env.NODE_ENV !== 'production'`の分岐と効果的な初期状態（開発では閉じた状態でOK）を確認する。モックデータ確認時にDevtoolsが活躍するよう設定する。

### 3.4. Providerまわりのドキュメント

今回のAPIフェッチ方針とProviderの組み合わせ（共通`QueryClient`＋キャッシュポリシー）を`TASK_011`に追記して共有しておく。その内容を基に後続のフック/コンポーネント側で実装を揃え、構成に変更があれば都度記録する。

---

## 4. モックデータフェッチ用フックの実装

### 4.1. API レスポンスの検証（フック側で統一）

外部APIを扱う際はレスポンスを`postsSchema.safeParse`/`postSchema.parse`などで検証する方針とし、その処理は`usePostsQuery/usePostQuery`の`queryFn`で行う。`MOCK_POSTS_PATH`などでモックファイルのパスを定数化し、`USE_MOCK_POSTS`の真偽値で`window.location.origin`付きのmockか`NEXT_PUBLIC_API_BASE_URL`に由来する本番API（`/api/v1/posts`）を切り替えることで柔軟な運用を可能にしていることも追記する。モック／本番APIを含めて共通の検証ロジックを用意することで、APIヘルパーの中身に手を加えずに型安全にチェックできることを明記する。

### 4.2. 一覧・詳細用 React Query フック

`src/hooks/posts/usePosts.ts`などに`usePostsQuery()`（`queryKey: ['posts']`, `staleTime: 10_000`, `cacheTime: 5 * 60_000`）と`usePostQuery(id)`（`queryKey: ['posts', id]`, `enabled: Boolean(id)`, `staleTime: 0`, `cacheTime: 60_000`）を実装。どちらも`postsSchema.safeParse`および`postSchema.parse`をかませ、ReactQueryの`select`で`postSchema.parse(data)`する構成を想定。

### 4.3. キャッシュ・同期振る舞いの記録

フック実装時には「一覧 -> 詳細 -> 詳細更新 -> `invalidateQueries(['posts'])`で一覧のキャッシュ更新」までの流れを想定し、キャッシュ制御の方針（`refetchOnWindowFocus: false`, `enabled`, `queryKey`）を`TASK_011`に追記する。`mutation`への適用（`retry`を0）についても併せて触れる。

### 4.4. ドキュメントとコードの整合

`TASK_011`に各ファイルのパス（`src/hooks/posts/usePosts.ts`など）と主要なオプション（`queryKey` / `staleTime` / `select`）を箇条書きで記載し、次のVitestテストでもその期待値を参照できるようにする。

---

## 5. Vitestによるモックデータ取得テスト

---
