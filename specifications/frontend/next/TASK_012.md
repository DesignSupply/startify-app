---
title: Next.jsアプリケーション開発タスクリスト:APIデータのページ展開と動的ルーティングページ作成
id: next_task_012
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

<!-- markdownlint-disable MD025 -->
# Next.jsアプリケーション開発タスクリスト:APIデータのページ展開と動的ルーティングページ作成

---

## 1. 固定ルーティングの一覧ページ作成とAPIデータの展開

### 1.1. 認証ルート内の固定ページとして実装

「/posts」を「(auth)」グループ配下に配置して`src/app/(auth)/posts/page.tsx`を追加し、`(auth)/layout.tsx`や`AuthGuard`を通じて共通UIと認証チェックを踏襲する。未認証ユーザーは`redirect()`や`notFound()`で`signin`へ誘導するような流れを明記しておく。

### 1.2. APIデータの取得とUI表示

既存の`usePostsQuery`を呼び出し、`Articles`コンポーネント（`src/components/posts/Articles.tsx`）へ取得した`posts`配列を渡して描画する。表示項目は`title`・`publishedAt`・`author`・`body`抜粋などで、`Link`の`href`に`post.id`を渡して`/posts/[id]`への遷移を用意する。本文抜粋は`dangerouslySetInnerHTML`でHTMLを展開しつつ40文字以上は`…`を付けている。React Queryを使っているため`isLoading`/`isError`を条件分岐で判定しており、`Suspense`は使っていない（将来必要なら`useQuery`側で`suspense: true`を渡す）ことを注記するとよい。さらに`data-testid`を振って後続テストで参照しやすくする。

表示の際の投稿日フォーマットは共通のユーティリティ`src/utils/formatDate.ts`を使い、`time` 要素に`dateTime`付きで出力する。`Intl.DateTimeFormat`の設定やロケールをここで集約することで、他の画面と整合性を保てるようにする。

※ページネーションのUIは次のタスク「## 2ページネーションの実装」でまとめて実装するため、ここではローカルなスライス処理やボタン類の記述は省略する。

### 1.3. 画面内ナビゲーションと操作

一覧コンテンツをプレーンなHTML構成で描画しつつ、`page` クエリや内部ステートで表示件数とページ番号の下地を用意しておく（詳細は後続セクションでページネーション化）。`publishedAt` を表示する際は `Intl.DateTimeFormat` などでフォーマットする位置を明示し、`useMemo` で整形結果をキャッシュする案も記しておく。

### 1.4. モックデータとの照合と手順

モックJSON（`public/mock/posts.json`）からデータを読み込む前提なので、`USE_MOCK_POSTS`フラグが`window.location.origin+/mock/posts.json`を指すことを確認し、開発者が`npm run dev`で`/posts`にアクセスして一覧を確認する手順を追記する。`postSchema`/`postsSchema`との整合性を保ち、フィールド構造が変わる際はスキーマとJSONを同時に更新する手順をドキュメント化する。

---

## 2. ページネーションの実装

### 2.1. ページ状態と表示件数の管理

`ITEMS_PER_PAGE`（現状は2件）を定めた上で、`page`クエリや内部ステートで現在ページを管理し、`visiblePosts=posts.slice((page - 1) * ITEMS_PER_PAGE, page * ITEMS_PER_PAGE)` で`Articles`に渡す範囲を切り出す。投稿総数から`totalPages=Math.max(1, Math.ceil(posts.length / ITEMS_PER_PAGE))`を導き、`page`が1未満や最大値より大きい場合は内側にクリップするロジックを入れておく。`isLoading`や`isError`時はナビゲーションを非表示（もしくはdisabled）とし、表示中は`data-testid`を振ってテストやE2Eでの参照を容易にする。

### 2.2. ナビゲーション UI の構造

`<nav>` 内に以下を横並びで配置し、「<<」「<」「ページ番号」「>」「>>」の順で並べる。各ボタンには`aria-label`や`aria-current`（現在のページ）を最低限付けつつ、必要であれば`data-testid`も追加する。実装は`src/components/posts/Pagination.tsx`にまとめているので、投稿一覧専用のラッパーとして使い、今後汎用化する場合はこのコンポーネントをベースに拡張する。  

- `<<`（first）: `page=1` に移動  
- `<`（prev）: `page=Math.max(1, currentPage - 1)`  
- ページ番号（例: 1 2 3 4 5）: 現在ページの前後数件を表示し、現在ページのみ`aria-current="page"`を設定  
- `>`（next）: `page=Math.min(totalPages, currentPage + 1)`  
- `>>`（last）: `page=totalPages`  

クリック時は`router.push`か`router.replace`でクエリを更新し、`page`ステートと同期する形にする。ページ番号の階層が多くなる場合は省略記号（`…`）を入れてもよい。

### 2.3. URL/ステート同期とクエリ対応

`useSearchParams`または`useRouter`を使って`/posts?page=X`形式のクエリを読取・更新し、ユーザーがブラウザの戻る/進むを使ったときも同じページを表示できるようにする。`page`クエリが未指定なら`1`、数値以外・範囲外の場合はクリップする。クエリ変更時に`replace`するか`push`するかはUXに合わせて決める（戻るで重複しないよう`replace`を使う案あり）。

### 2.4. 表示例とエッジケース

表示例: `<< < 1 2 [3] 4 5 > >>`（`[3]` が現在）とし、クリック操作で`Articles`に渡す`visiblePosts`が切り替わる。`totalPages`が1ならナビゲーションを抑え、`posts`が空なら`<p>表示する投稿はありません。</p>`のままにする。`posts.length`が`ITEMS_PER_PAGE`未満でも`totalPages`は1なのでナビゲーションは非表示・disabledとする。

### 2.5. 次の実装ステップとの組合せ

この構成を `## 3. 動的ルーティングの詳細ページ作成` とつなげれば一覧→詳細→戻るの導線が完成するため、`Articles` に渡す `page` の切り替えロジックとナビゲーションの振る舞いをこのセクションで固めておく。

---

## 3. 動的ルーティングの詳細ページ作成

### 3.1. ページ構成とルーティング

`/posts/[id]/page.tsx` を `(auth)` グループ配下に追加し、`params.id` を受け取って `usePostQuery(id)` あるいは `usePostsQuery`＋絞り込みで該当投稿を取得する。存在しない `id` のときは `notFound()` を返す、または `redirect('/posts')` することで未認証/不正ID対策を入れる。モックデータの `id` は数値であるため、`Number(params.id)` などで変換しつつ `postSchema` によるバリデーションも併用する。

**SSG（`output: 'export'`）利用時の注意**  

- `dynamicParams: false` かつ未生成パラメーターにアクセスするとNext側で「missing param」扱いとなり500エラーが返る（ページ内の `notFound()` には到達しない）。  
- 開発サーバーで未生成IDを404として確認したい場合は、一時的に `next.config.mjs` の `output: 'export'` 設定をコメントアウトなどで外す／dev用フラグで無効化する運用が必要。  
- 本番向けSSGビルド時は `output: 'export'` を有効に戻し、`generateStaticParams` に含まれるIDのみ配信する想定。

**モック/本番切り替えの管理**  

- 投稿データのモック利用は `frontend/next/src/features/posts/mockConfig.ts` の `USE_MOCK_POSTS` / `MOCK_POSTS_PATH` / `PRODUCTION_POSTS_PATH` を単一の参照元として管理し、`usePosts/usePostQuery` と `page.tsx` のファイル読込で同じ定数を参照する。  
- `USE_MOCK_POSTS=false` とする場合は、`page.tsx` でのファイル読み込みを避け、API経由に切り替える実装が必要（現状はモック前提）。

### 3.2. コンテンツ表示と共通化

詳細ページのコンテンツは`Article`コンポーネントの（`src/components/posts/Article.tsx`）単一インスタンスとして描画し、タイトル・投稿日・投稿者・本文（HTML）のほか、必要ならタグ／カテゴリ／共有ボタンへの導線も組み込む。`formatDate`のユーティリティを使って`time`を整形し、`dangerouslySetInnerHTML`によるHTML出力は一覧と同じ処理を共有する。戻るボタンや記事一覧へのリンクも忘れずに。

### 3.3. データ取得とキャッシュ連携

`usePostQuery(id)` に`queryKey: ['posts', id]`を与えることで一覧のキャッシュと連携し、すでにフェッチ済みの`posts`があるときは詳細の再取得を抑制する。必要であれば`page`クエリと連動して`invalidateQueries(['posts'])`を使い、詳細編集後の一覧リフレッシュも考慮する。`getStaticPaths`/`getStaticProps`相当はまだ使わずクライアントサイド主体にし、`USE_MOCK_POSTS`のままで動作可能なままにしておく。

### 3.4. エラー・ロードハンドリング

`usePostQuery`の`isLoading`/`isError`を使って状態表示を行い、エラー時にはカスタムメッセージと`Link`で一覧に戻るよう案内する。`post`が取得できない場合は`notFound()`を呼び出す（Next.js App Router）。`Pagination`や`Articles`側で`data-testid`を振っていたので、詳細ページも同じ`data-testid`を活かしてE2Eで検証できるようにする。

### 3.5. 次のステップ

一覧とページネーションの流れに加えて詳細ページを実装すれば、モック投稿の一覧→詳細の導線が完成する。必要に応じて`Articles`/`Article`コンポーネントの共通化を進めてから詳細ページをビルドすると、DRYな構成を保ちながら仕様を満たせる。

---
