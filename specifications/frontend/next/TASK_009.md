---
title: Next.jsアプリケーション開発タスクリスト:認証APIのログイン機能
id: next_task_009
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Next.jsアプリケーション開発タスクリスト:認証APIのログイン機能

バックエンド側で実装されている認証APIを用いたログイン機能を実装します。

---

## 1. ライブラリのインストール

認証状態管理とAPI通信のキャッシュに、TanStack Query（React Query）を採用し、Devtoolsは開発時のみ読み込む（本番バンドルに含めない）ようにインストールする。
また、フォーム周りに、react-hook-formとzodを使用する。

```bash
cd ./frontend/next
npm install @tanstack/react-query react-hook-form zod @hookform/resolvers
npm install -D @tanstack/react-query-devtools
```

---

## 2. 環境変数の追加

`/frontend/next/.env.development`と、`/frontend/next/.env.production` それぞれのファイルにAPI用のURLを環境変数として追加します。

```dotenv
# development
NEXT_PUBLIC_API_BASE_URL=https://api.localhost/api/v1

# production
NEXT_PUBLIC_API_BASE_URL=https://api.example.com/api/v1
```

---

## 3. 認証用プロバイダー、フック、ラッパー処理の実装

認証APIを扱う際に必要となる各種処理を実装します。

### 3.1. 認証用プロバイダー追加

`/frontend/next/src/providers/ReactQueryProvider.tsx` を作成し、React Query用のプロバイダーを用意し、 `/frontend/next/src/app/layout.tsx` で全体をラップします。

### 3.2. 認証API各種処理の作成

API使用時のラッパー処理を `/frontend/next/src/helpers/api.ts` に作成します。

- baseURL付与、JSONのパース処理、エラー正規化（invalid_credentials/refresh_invalid/token_expired…）などを共通化
- メソッド指定、認証、refresh、credentialsをオプションで指定
- `auth: true` の時だけBearer付与（デフォルトはfalse）
- `withCredentials: true` の時だけ `credentials: 'include'`（デフォルトはfalse）
- `autoRefresh: true` の時だけ401→refresh→1回だけリトライ（login/refresh/logoutは除外、デフォルトはfalse）
- メソッドはGETとPOSTのジェネリクス型
- CSRF対策としてリクエストヘッダーに `X-Requested-With: XMLHttpRequest` の常時追加

アクセストークン用のストア関数を `/frontend/next/src/helpers/storeAccessToken.ts` に作成します。

- `setAccessToken(token)` 、`getAccessToken()` 、`clearAccessToken()` の各種メソッドをエクスポートし、外部から参照できるようにする
- メモリ保持はクライアント限定。サーバー側/SSRでは使わない前提で（'use client'モジュール内に配置）

API認証の処理を `/frontend/next/src/features/auth/apiAuth.ts` に作成します。

- `login({ email, password })` 、`refresh()` 、`logout()` 、`me()` といったAPI処理に対応する各種処理を実装
- ラッパー処理を用いてHTTP呼び出しを行う

React Query用のフック処理を `/frontend/next/src/hooks/auth/useAuth.ts` に作成します。

- `useLoginMutation()` 、`useLogoutMutation()` 、`useMeQuery()` といったReact Queryを扱う際の各種処理を実装
- キャッシュ、トークン更新、成功/失敗時の処理など
- Query Keyは `['auth','me']` に統一
- `seLoginMutation()` 成功時に `setAccessToken→invalidate(['auth','me'])`
- `useLogoutMutation()` 成功時に `clearAccessToken→invalidate`

---

## 4. 認証ルーティングの作成

### 4.1. 認証ルーティング用のグループとレイアウトコンポーネントを作成

`/frontend/next/src/app/(auth)` として認証用のルーティンググループを作成する。直下に認証ルーティング用のレイアウトコンポーネントを `/frontend/next/src/app/(auth)/layout.tsx` として作成します。

### 4.2. ルートガードコンポーネントの作成

ルートガード用のコンポーネントを `/frontend/next/src/components/auth/AuthGuard.tsx` に作成する。

- `useMeQuery` で未認証の場合には `/signin` にリダイレクトさせる。
- `/frontend/next/src/app/(auth)/layout.tsx` 配下をラップする。
- `'use client'` を付与し、 `useMeQuery` のローディング中は子要素をブロック

---

## 5. フォームUI、認証保護ページの作成

### 5.1. ログインフォームページとフォームUIの作成

`/frontend/next/src/app/signin/page.tsx` にログインページのルーティングとページコンポーネントを作成します。
また、そのページコンポーネント内で `/frontend/next/src/components/auth/SigninForm.tsx` としてフォームUIと処理を含めたコンポーネントを設置する。

- react-hook-formとzodでバリデーション機能を実装、エラーメッセージを表示
  - メールアドレス
    - 必須入力
    - メールアドレスフォーマット
  - パスワード
    - 必須入力
    - 8文字以上
- ログイン成功時には `/(auth)/dashboard` にリダイレクト
- ログイン失敗時にはエラー表示
- フォームUIについてはスタイルはつけず、プレーンなHTMLのみで実装

### 5.2. 認証保護ページの作成

`/frontend/next/src/app/(auth)/dashboard/page.tsx` に認証保護ページのルーティングとページコンポーネントを作成します。
また、そのページコンポーネント内で `/frontend/next/src/components/dashboard/DashboardContents.tsx` としてダッシュボードに表示されるコンテンツコンポーネントを設置し、その中でユーザー名の表示と、`/frontend/next/src/components/auth/SignoutButton.tsx` を作成しログアウトボタンとして表示させる。

- `useMeQuery` でユーザー名を表示する
- ログアウトボタンを設置する、ログアウト後は `/signin` にリダイレクトさせる

---

## 6. 認証APIのログイン機能のテスト

---
