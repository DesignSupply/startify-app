---
title: Next.jsアプリケーション開発タスクリスト:プロジェクト開発環境構築（ローカル環境）
id: next_task_001
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# UIコンポーネント・画面デザイン制作タスクリスト:プロジェクト開発環境構築（ローカル環境）

Next.jsのプロジェクトをローカル環境に作成します。ここではパッケージマネージャーにnpmを使用する前提で進めています。

---

## 1. モジュールのインストール

必要なモジュールをプロジェクトディレクトリにインストールします。言語はTypeScriptを使用し、TailwindCSSのフレームワークとESLintも使えるようにしておきます。
また、ルーティングシステムはApp Routerを採用し、エイリアスとソースコードディレクトリの設定も行なっておきます。

- パス: `/frontend/next/`

```bash
cd frontend/next && npx create-next-app@latest . --typescript --tailwind --eslint --app --src-dir --import-alias "@/*"
```

---

## 2. StyleLint、MarkupLint、Prettierの導入と設定

Next.jsのプロジェクトで各種リンター（StyleLint、MarkupLint）フォーマッター（Prettier）を使えるようにします。
モダンな開発環境を実現するため、すべての設定ファイルはmjs形式で統一します。

### 2.1 StyleLintのインストールと設定ファイルの作成

StyleLintを使用してCSSやSassファイルの品質を保つための設定を行います。

1. 必要なパッケージのインストール

```bash
npm install -D stylelint stylelint-config-standard stylelint-config-tailwindcss stylelint-order postcss-syntax
```

2. `stylelint.config.mjs`の作成

```bash
cat > stylelint.config.mjs << 'EOL'
/** @type {import('stylelint').Config} */
const config = {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-tailwindcss'
  ],
  plugins: [
    'stylelint-order'
  ],
  rules: {
    'order/properties-alphabetical-order': true,
    'selector-class-pattern': null,
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: [
          'tailwind',
          'apply',
          'variants',
          'responsive',
          'screen',
          'layer'
        ]
      }
    ]
  }
};

export default config;
EOL
```

3. package.jsonにスクリプトを追加:

```json
"lint:style": "stylelint \"src/**/*.{css,scss}\"",
"fix:style": "stylelint \"src/**/*.{css,scss}\" --fix"
```

### 2.2 MarkupLintのインストールと設定ファイルの作成

MarkupLintを使用してJSX/TSXのマークアップの品質を保つための設定を行います。

1. 必要なパッケージのインストール

```bash
npm install -D markuplint @markuplint/jsx-parser @markuplint/react-spec
```

2. `markuplint.config.mjs`の作成

```bash
cat > markuplint.config.mjs << 'EOL'
/** @type {import('@markuplint/ml-config').Config} */
export default {
  extends: [
    'markuplint:recommended'
  ],
  parser: {
    '\\.jsx$': '@markuplint/jsx-parser',
    '\\.tsx$': '@markuplint/jsx-parser'
  },
  specs: {
    '\\.jsx$': '@markuplint/react-spec',
    '\\.tsx$': '@markuplint/react-spec'
  },
  rules: {
    'character-reference': false,
    'attr-duplication': true,
    'deprecated-element': true,
    'required-attr': true,
    'landmark-roles': true,
    'required-h1': false,
    'no-refer-to-non-existent-id': true,
    'use-list': true,
    'no-empty-palpable-content': false,
    'no-hard-code-id': false,
    'wai-aria': true,
    'ineffective-attr': true
  },
  nodeRules: [
    {
      selector: 'img',
      rules: {
        'required-attr': ['alt']
      }
    }
  ]
};
EOL
```

3. package.jsonにスクリプトを追加

```json
"lint:markup": "markuplint \"src/**/*.{jsx,tsx}\"",
"fix:markup": "markuplint --fix \"src/**/*.{jsx,tsx}\""
```

### 2.3 Prettierのインストールと設定ファイルの作成

コードフォーマッターとしてPrettierをインストールし、mjs形式の設定ファイルで構成します。

1. 必要なパッケージのインストール

```bash
npm install -D prettier eslint-config-prettier
```

2. `prettier.config.mjs`の作成

```bash
cat > prettier.config.mjs << 'EOL'
/** @type {import("prettier").Config} */
const config = {
  printWidth: 100,
  tabWidth: 2,
  useTabs: false,
  semi: true,
  singleQuote: true,
  trailingComma: 'es5',
  bracketSpacing: true,
  bracketSameLine: false,
  arrowParens: 'always',
  endOfLine: 'lf'
};

export default config;
EOL
```

3. `.prettierignore`の作成

```bash
cat > .prettierignore << 'EOL'
.next
.vscode
dist
node_modules
public
out
package-lock.json
yarn.lock
pnpm-lock.yaml
bun.lockb
EOL
```

4. ESLintとの連携設定のためeslint.config.mjsを更新

```bash
cat > eslint.config.mjs << 'EOL'
import { FlatCompat } from '@eslint/eslintrc';

const compat = new FlatCompat({
  baseDirectory: import.meta.dirname,
});

const eslintConfig = [
  ...compat.extends('next/core-web-vitals', 'next/typescript', 'prettier'),
];

export default eslintConfig;
EOL
```

5. package.jsonにスクリプトを追加

```json
"format": "prettier --write \"src/**/*.{js,jsx,ts,tsx,css,json,md}\"",
"format:check": "prettier --check \"src/**/*.{js,jsx,ts,tsx,css,json,md}\""
```

---

## 3. リンター、フォーマッターのスクリプトコマンド変更

package.jsonのスクリプトコマンドを下記のように変更します。

```json
{
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "start": "next start",
    "lint": "npm run lint:script && npm run lint:style && npm run lint:markup",
    "lint:script": "next lint",
    "lint:style": "stylelint \"src/**/*.{css,scss}\"",
    "lint:markup": "markuplint \"src/**/*.{jsx,tsx}\"",
    "fix": "npm run fix:style && npm run fix:markup",
    "fix:style": "stylelint \"src/**/*.{css,scss}\" --fix",
    "fix:markup": "markuplint --fix \"src/**/*.{jsx,tsx}\"",
    "format": "prettier --write \"src/**/*.{js,jsx,ts,tsx,css,json,md}\"",
    "format:check": "prettier --check \"src/**/*.{js,jsx,ts,tsx,css,json,md}\""
  } ,
}
```

---

## 4. 環境変数ファイルの作成

プロジェクト内で環境変数を使えるようにします。下記の環境変数ファイルを作成します。

- `.env.development` : 開発環境用の環境変数ファイル
- `.env.production` : 本番環境用の環境変数ファイル
- `.env.local` : 秘匿情報用の環境変数ファイル
- `.env.example` : サンプル用の環境変数ファイル

開発環境用と本番環境用のファイルには、それぞれアプリケーションのURLを変数として格納しておきます。
`.env.example` についてはGitの追跡対象とします。

```.env.development
APPURL=http://localhost:3000

NEXT_PUBLIC_APPURL=http://localhost:3000
```

```.env.production
APPURL=https://example.com

NEXT_PUBLIC_APPURL=https://example.com
```

```.env.example
APPURL=https://example.com

NEXT_PUBLIC_APPURL=https://example.com
```
