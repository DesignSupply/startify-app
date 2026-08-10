---
title: Next.jsアプリケーション データ取得・投稿表示仕様
status: current
last_updated: 2026-08-11
related_paths:
  - frontend/next/public/mock/posts.json
  - frontend/next/src/app/(auth)/posts/
  - frontend/next/src/components/posts/
  - frontend/next/src/features/posts/mockConfig.ts
  - frontend/next/src/helpers/api.ts
  - frontend/next/src/hooks/posts/
  - frontend/next/src/providers/ReactQueryProvider.tsx
  - frontend/next/src/schemas/posts.ts
  - frontend/next/src/types/posts.ts
  - frontend/next/src/utils/formatDate.ts
  - frontend/next/package.json
  - frontend/next/next.config.mjs
---

# Next.jsアプリケーション データ取得・投稿表示仕様

Startify-AppのNext.jsアプリケーションにおける、一般データの取得、TanStack Queryによるサーバー状態管理、投稿一覧・詳細表示、ページネーション、実行時検証を定義します。

認証APIとAccess Tokenの扱いは[Next.jsアプリケーション 認証仕様](authentication.md)、Static Exportとルーティングの基本方針は[Next.jsアプリケーション アーキテクチャ仕様](architecture.md)を参照してください。

## 1. 現在のデータソース

投稿機能は現在、`frontend/next/public/mock/posts.json` をデータソースとして使用します。`src/features/posts/mockConfig.ts` の設定は次のとおりです。

| 定数 | 現在値 | 用途 |
| --- | --- | --- |
| `USE_MOCK_POSTS` | `true` | モックと実APIの選択 |
| `MOCK_POSTS_PATH` | `/mock/posts.json` | モックJSONの公開パス |
| `PRODUCTION_POSTS_PATH` | `/api/v1/posts` | 将来の実APIパス |

`USE_MOCK_POSTS` は環境変数ではなく、ソースコード上の定数です。現在の実装で環境ごとにデータソースを切り替える仕組みはありません。

モックJSONには10件の投稿を格納します。各投稿は次の構造です。

| フィールド | 型 | 必須 | 用途 |
| --- | --- | --- | --- |
| `id` | number | 必須 | 投稿の識別子と詳細URL |
| `publishedAt` | ISO 8601 datetime string | 必須 | 公開日時 |
| `author` | string | 必須 | 投稿者名 |
| `title` | string | 必須 | タイトル |
| `body` | string | 必須 | HTMLを含み得る本文 |
| `tags` | string[] | 任意 | タグ |
| `categories` | string[] | 任意 | カテゴリー |

## 2. 型と実行時検証

TypeScript型は `src/types/posts.ts`、Zodスキーマは `src/schemas/posts.ts` に定義します。

- `Post`: 単一投稿のTypeScript型
- `PostListResponse`: `Post[]`
- `postSchema`: 単一投稿のZodスキーマ
- `postsSchema`: 投稿配列のZodスキーマ

TypeScript型だけで外部データを信頼せず、モックJSONまたはAPIレスポンスを読み込む境界でZod検証を行います。一覧取得とビルド時ファイル読み込みでは `postsSchema.safeParse()`、詳細取得では対象投稿へ `postSchema.safeParse()` を適用し、失敗時はZod Errorとして処理します。

投稿構造を変更する場合は、型、Zodスキーマ、モックJSON、利用コンポーネント、テストを同じ作業範囲で更新します。

## 3. 共通APIクライアント

クライアント側の取得には `src/helpers/api.ts` の `apiFetch()` を使用します。投稿取得では絶対URLを渡し、`apiFetch()` によるJSON解析と非成功レスポンスのError変換を利用します。

モック利用時は、ブラウザーの `window.location.origin` と `MOCK_POSTS_PATH` を結合した同一OriginのURLを取得します。Server環境では `window` が存在しないため、投稿フックによる取得はClient Component内で行います。

認証が必要なAPIで `apiFetch()` を利用する場合は、API契約に応じて `auth`、`autoRefresh`、`withCredentials` を明示します。現在の投稿取得はこれらを指定しておらず、Bearer TokenやRefresh処理を使用しません。

## 4. TanStack Query Provider

`src/providers/ReactQueryProvider.tsx` はルートレイアウトからアプリケーション全体を包み、ブラウザーセッションごとに1つの `QueryClient` を生成します。

Providerの現在の既定値は次のとおりです。

| 設定 | 現在値 |
| --- | --- |
| Query `staleTime` | `0` |
| Query `refetchOnWindowFocus` | `false` |
| Query `retry` | 401は再試行なし。その他は最大2回再試行 |
| Mutation `retry` | `0` |

TanStack Query DevtoolsはProduction以外で読み込み、初期状態は閉じています。API・サーバー状態はTanStack Queryで管理し、Zustandへ保存しません。

## 5. 投稿Query

`src/hooks/posts/usePosts.ts` が一覧と詳細のQueryを提供します。

### 投稿一覧

`usePostsQuery()` は次の設定でモックJSONを取得します。

| 項目 | 現在値 |
| --- | --- |
| Query Key | `['posts']` |
| `staleTime` | 10秒 |
| `gcTime` | 個別指定なし。TanStack Queryの既定値を使用 |

取得した配列全体を `postsSchema` で検証し、成功した値だけをQueryデータとして返します。現在は並び替えや絞り込みを行わず、モックJSONまたはAPIレスポンスの配列順をそのまま表示順として使用します。

### 投稿詳細

`usePostQuery(id, options?)` は次の設定を使用します。

| 項目 | 現在値 |
| --- | --- |
| Query Key | `['posts', id]` |
| `enabled` | `Boolean(id)` |
| `staleTime` | `0` |
| `gcTime` | 個別指定なし。TanStack Queryの既定値を使用 |

現在は詳細専用APIを呼び出さず、投稿一覧を取得して一致する `id` を検索します。対象がなければ `Post not found` Errorを投げ、対象投稿を `postSchema` で再検証します。呼び出し側は `initialData` などの標準的なQuery Optionを追加できます。

一覧の `['posts']` と詳細の `['posts', id]` は別のQuery Cacheです。`usePostQuery()` は一覧Queryのキャッシュから対象投稿を直接取得せず、Query Function内で投稿一覧を再取得します。

現在のNext.js投稿機能は、コンテンツデータの読み取りと表示だけを担当します。投稿の作成、更新、削除に対応するMutationと、書き込み後の `invalidateQueries()` は実装していません。認証機能で使用するPOSTリクエストは、この投稿データの読み取り専用方針とは別のAPI契約です。

## 6. 投稿一覧ページ

`/posts/` は認証Route Group内のページです。`page.tsx` はServer Componentとしてページ構造とSuspense境界を持ち、`_content.tsx` が投稿取得、ページ状態、画面操作を担当します。

- 読み込み中は `読み込み中...` を表示する
- 取得失敗時はErrorのメッセージを `role="alert"` で表示する
- 取得成功時は表示対象を `Articles` へ渡す
- 投稿が0件の場合は `表示する投稿はありません。` と表示する
- ダッシュボードへ戻るリンクを表示する

`Articles` はタイトル、公開日、投稿者、本文の先頭40文字、詳細リンクを表示します。公開日は `src/utils/formatDate.ts` により既定で `ja-JP` 形式へ変換し、`time` 要素の `dateTime` には元の値を設定します。

## 7. ページネーション

一覧は1ページ5件でクライアント側ページネーションを行います。全投稿を取得した後、現在ページに対応する配列を `slice()` で抽出します。

- URLは `/posts/?page=X` 形式を使用する
- `page` が未指定、非数値、1未満の場合は1として扱う
- 最大ページを超える場合は最大ページとして扱う
- URL更新には `router.replace()` を使用し、既存のQuery Parameterを維持する
- 先頭、前、ページ番号、次、末尾の各ボタンを表示する
- 現在ページには `aria-current="page"` を設定してボタンを無効化する
- ページ番号は先頭、末尾、現在ページ前後2件を基本とし、間を省略記号で表す

現在は総ページ数が1の場合もページネーション自体を描画し、移動ボタンを無効化します。URL上の範囲外ページは表示上クリップしますが、正規化したURLへの置換は行いません。また、小数の `page` を整数へ丸める検証はありません。

## 8. 投稿詳細とStatic Export

`/posts/[id]/` はStatic Exportのビルド時に生成します。

1. Server Componentの `page.tsx` が `public/mock/posts.json` をファイルとして読み込む
2. 投稿配列を `postsSchema` で検証する
3. `generateStaticParams()` がすべての投稿IDを返す
4. 対象投稿を検索し、存在しなければ `notFound()` を呼ぶ
5. 対象投稿を `initialPost` としてClient Componentへ渡す
6. `usePostQuery()` が `initialData` として利用し、クライアント側のQuery状態へ接続する

`dynamicParams = false` のため、ビルド時に生成されなかったIDを配信後に動的生成しません。`initialData` を利用しても詳細Queryの `staleTime` は0であるため、マウント後の再取得対象になります。

詳細画面は `Article` でタイトル、公開日、投稿者、本文を表示し、投稿一覧へのリンクを提供します。読み込み失敗または投稿を取得できない場合は、Errorと一覧へのリンクを表示します。

## 9. HTML本文の扱い

一覧の抜粋と詳細本文は `dangerouslySetInnerHTML` で描画します。現在はモックJSONを信頼できるリポジトリ内データとして扱い、HTML Sanitizerを適用していません。一覧の抜粋はHTML文字列自体を40文字で切るため、内容によってはHTML要素の途中で切れる可能性があります。

外部APIやユーザー入力へ切り替える場合は、保存時または表示前に許可要素・属性を限定してSanitizeしてください。信頼できないHTMLを現在の表示処理へ直接渡してはいけません。

## 10. 実API切り替えと書き込み基盤の現在の制約

`PRODUCTION_POSTS_PATH` は将来用に定義されていますが、実APIへの切り替えは現在未完成です。

- `USE_MOCK_POSTS=false` では、詳細ページのビルド時ファイル読み込みが明示的にErrorとなる
- Laravelには現在 `/api/v1/posts` のルートがない
- 投稿取得は `auth` と `autoRefresh` を指定しておらず、認証保護APIには未対応
- `NEXT_PUBLIC_API_BASE_URL` が `/api/v1` を含む現在構成では、`PRODUCTION_POSTS_PATH=/api/v1/posts` をそのまま結合するとAPIプレフィックスが重複する
- Static Exportで詳細ページを生成するための投稿IDを、ビルド時にどのように取得するか未設計

実APIへ移行する場合は、API Base URLとパスの責務、認証要否、レスポンスSchema、ビルド時のID取得、障害時の挙動を一体で設計し、環境変数またはビルド設定でデータソースを選択できるようにします。

将来の投稿作成、更新、削除を、Next.jsからLaravel API経由で行うか、Laravel MPAで行うか、Cloudflare Workersなど別の基盤で行うかは未決定です。読み取りと書き込みで異なる基盤を使用する可能性もあります。データストア、認証・認可、API契約、Static Exportとの整合を含めて別途設計するため、これらは将来案であり、現在仕様には含めません。

## 11. 検証

投稿Queryのテストは `frontend/next/` で実行します。

```bash
npm run test:posts
```

lint、型チェック、全テストを含む確認:

```bash
npm run check
```

Static Exportと投稿詳細ルートの生成確認:

```bash
npm run build:cf
```

現在の自動テストは次を対象とします。

- 投稿一覧を取得できること
- 一覧Schema検証失敗がErrorになること
- ID指定時に対象投稿を取得できること
- ID未指定時に取得しないこと
- 詳細Schema検証失敗がErrorになること

現在、投稿一覧・詳細コンポーネント、ページネーション、URL同期、HTML表示、ビルド時ファイル読み込み、`generateStaticParams()` は自動テストの対象外です。

## 12. 移行元資料

本書は、次の既存資料から設計意図を抽出し、現在のコード、設定、テストと照合して再構成しています。

- `specifications/frontend/next/TASK_011.md`
- `specifications/frontend/next/TASK_012.md`
- `.cursor/rules/dev-frontend.mdc`

これらはドキュメント移行が完了するまで設計意図の確認に使用しますが、現在仕様としては本書と現在の実装を優先します。
