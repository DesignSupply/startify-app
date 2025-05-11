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

---

## 5. Sassのインストールと設定

スタイルシートについてはオプションとしてSassを使えるようにします。TailwindCSSと併用して、より柔軟なスタイリングを可能にします。本プロジェクトではSCSS記法のみをサポートします。

### 5.1 Sassのインストール

1. 必要なパッケージのインストール

```bash
npm install -D sass
```

### 5.2 Next.jsの設定

Next.jsはSassを自動的にサポートしているため、特別な設定は必要ありません。ただし、Sassコンパイルのオプションをカスタマイズする場合は、`next.config.mjs`に以下の設定を追加します。

```bash
cat > next.config.mjs << 'EOL'
/** @type {import('next').NextConfig} */
const nextConfig = {
  sassOptions: {
    includePaths: ['./src/styles'],
  },
};

export default nextConfig;
EOL
```

### 5.4 StyleLintのSassサポート追加

StyleLintがSassファイルを適切に処理できるように設定を更新します。

1. 必要なパッケージのインストール

```bash
npm install -D stylelint-scss postcss-scss
```

2. `stylelint.config.mjs`の更新

```bash
cat > stylelint.config.mjs << 'EOL'
/** @type {import('stylelint').Config} */
const config = {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-tailwindcss'
  ],
  plugins: [
    'stylelint-order',
    'stylelint-scss'
  ],
  overrides: [
    {
      files: ['**/*.scss'],
      customSyntax: 'postcss-scss',
      rules: {
        'at-rule-no-unknown': null,
        'scss/at-rule-no-unknown': [
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
    }
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

### 5.5 TailwindとSassの併用のベストプラクティス

TailwindCSSとSassを効果的に組み合わせるためのガイドラインを示します。

1. **Tailwindを優先する**:
   - 基本的なレイアウトやスタイリングにはTailwindのユーティリティクラスを使用
   - コンポーネントの基本構造はTailwindで構築

2. **Sassの使用ケース**:
   - 複雑なアニメーションや変数を用いた計算が必要な場合
   - カスタムミックスインによる再利用可能なスタイルパターン
   - Tailwindだけでは表現が難しい複雑なセレクターや入れ子構造
   - グローバル変数やテーマ設定の一元管理

3. **モジュールスコープの活用**:
   - コンポーネント固有のスタイルには`.module.scss`を使用
   - グローバルスタイルの衝突を防ぐ

4. **tailwind-mergeの活用**:
   - TailwindクラスとSCSSモジュールを適切にマージ
   - クラス名の衝突を防ぐ

```bash
npm install -D tailwind-merge
```

---
