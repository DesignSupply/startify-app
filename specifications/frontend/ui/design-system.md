---
title: デザインシステム・UIコンポーネント仕様
status: current
last_updated: 2026-09-02
related_paths:
  - frontend/_design-tokens/
  - frontend/ui/
  - README.md
---

# デザインシステム・UIコンポーネント仕様

Startify-Appにおけるデザイントークンと、Storybookを使用したUIコンポーネント管理環境の現在仕様を定義します。

この文書は、リポジトリに存在するToken定義とUI実装を正本として扱います。デザイン上の構想、Tokenからの成果物生成、各アプリケーションへの自動反映など、未実装の仕組みは現在仕様と区別します。

## 1. 対象領域

デザインシステム関連の責務は、現在次の2領域に分かれています。

| 領域 | パス | 現在の役割 |
| --- | --- | --- |
| デザイントークン | `frontend/_design-tokens/` | 色、寸法、文字、レイアウトなどの基礎値をYAMLで定義 |
| UIコンポーネント | `frontend/ui/` | ReactコンポーネントをStorybookで表示・確認 |

両領域は同じデザインシステムを構成する資料ですが、現在は自動連携していません。Token YAMLを読み取ってCSSやTypeScriptを生成する処理や、StorybookへTokenを直接取り込む処理は実装されていません。

## 2. デザイントークン

### 2.1. ファイル構成

現在は次の7ファイルを管理しています。

| Token | ファイル | 主な定義 |
| --- | --- | --- |
| Color Scheme | `frontend/_design-tokens/color-scheme.yaml` | Palette Color、Gray Color、Status Colorと各Tone |
| Size Scale | `frontend/_design-tokens/size-scale.yaml` | Absolute・Relative Size、Layer Order、Breakpoint |
| Typography | `frontend/_design-tokens/typography.yaml` | Font Family、Size、Weight、Style、Leading、Tracking |
| Grid System | `frontend/_design-tokens/grid-system.yaml` | Container、Gutter、Row、12分割Column |
| Drop Shadow | `frontend/_design-tokens/dropshadow.yaml` | Shadow Type、方向、Elevationごとの値 |
| Corner Style | `frontend/_design-tokens/corner-style.yaml` | ShapeとBorder Radius |
| Easing | `frontend/_design-tokens/easing.yaml` | CSS Easing KeywordとCubic Bézier |

各ファイルのルートは、現在次の形で統一されています。

```yaml
token-name: tokenの識別名
token-value:
  # Token固有の定義
```

命名規則の最低限は次のとおりです。

- `token-name`は、拡張子を除いたYAMLファイル名と一致する
- ルートは`token-name`と`token-value`の2キーを持つ

個々の値、階層、命名は各YAMLファイルの現在実装を参照します。`easing.yaml`の`linear`、`dropshadow.yaml`のElevation `level4.name: highest` など、Token内部の詳細は各ファイルを正本とします。

ローカルでは、次のコマンドでYAMLの構文と最低限の構造を検証します。

```bash
cd frontend/ui
npm run check:tokens
```

検証は`frontend/ui/scripts/validate-design-tokens.mjs`が行い、対象は`frontend/_design-tokens/*.yaml`です。YAML Parserには、`frontend/ui/package.json`で直接宣言した`yaml`パッケージを使用します。検証内容は次のとおりです。

- 現在の7ファイルがすべて存在する
- 対象YAMLをすべて構文解析できる
- YAMLのルートがObjectである
- `token-name`が空でない文字列である
- `token-name`が拡張子を除いたファイル名と一致する
- `token-value`がArrayではないObjectである
- `token-value`が空ではない

構文不正または構造不正が1件でもあれば、エラー内容と対象ファイルを表示して非0で終了します。全件正常なら、検証したファイル数が分かる成功メッセージを表示して0で終了します。検証対象ディレクトリは、必要に応じて第1引数で差し替えられますが、通常の利用では引数は不要です。

### 2.2. 現在の位置付け

`frontend/_design-tokens/`は、Startify-Appに保持しているデザイン基礎値の定義元です。ただし、現在の各Frontendがこれらの値を実行時またはBuild時に参照しているわけではありません。

したがって、YAMLを変更しても次の成果物へ自動反映されません。

- Storybookで読み込むCSS
- React UIコンポーネント
- Next.js、Astro、Viteなど各FrontendのStyle
- CSS Custom Properties
- npm Package

Tokenの変更時は、影響を受ける実装が別に存在しないかリポジトリ内を確認します。生成処理が導入されるまでは、YAMLの変更だけでUIへ反映されたものと判断しません。

## 3. Startify-UIとの関係

[Startify-UIのDesign資料](https://lab.designsupply-web.com/startify-ui/documents/design/)を、Startify-AppのToken内容や構成を今後見直す際の設計上の参照先とします。

ただし、Startify-UIの公開資料と、このリポジトリのYAMLは自動同期されていません。現在は色の値や表現形式、分類名などに差異があり、Startify-UI側にあるIconやPatternもStartify-AppのTokenとして定義されていません。

現在仕様を確認するときはリポジトリ内のYAMLを参照し、Startify-UIの内容を未反映のまま実装済みとして扱いません。全面同期、Token分類の再設計、Icon・Patternの追加は将来の検討事項です。

## 4. Storybook環境

### 4.1. 実行環境

UIコンポーネント管理環境は`frontend/ui/`に配置し、ReactとStorybookを使用しています。Packageと正確なVersionは`frontend/ui/package.json`と`frontend/ui/package-lock.json`を正本とします。

依存関係の導入とStorybookの起動は、`frontend/ui/`で実行します。

```bash
npm ci
npm run storybook
```

Lockfileから依存関係を再現する標準手順として、`npm ci`を使用します。

StorybookはPort `6006`で起動します。

静的Buildは次のコマンドで確認します。

```bash
npm run build-storybook
```

生成される`frontend/ui/storybook-static/`はGit管理対象外です。

### 4.2. Storybook設定

Storybookの主な設定は次のファイルにあります。

| ファイル | 役割 |
| --- | --- |
| `frontend/ui/.storybook/main.js` | Storyの探索、Addon、React Vite Framework、Docgenの設定 |
| `frontend/ui/.storybook/preview.js` | Global Style、Controls、Accessibility Addonの設定 |
| `frontend/ui/.storybook/vitest.setup.js` | StorybookとAccessibility AnnotationのTest設定 |
| `frontend/ui/vitest.config.js` | Playwright Chromiumを使用するBrowser Test設定 |
| `frontend/ui/styles/preview.scss` | Storybook Previewへ適用するStyleの入口 |

現在のPreview Styleは、CDN経由で`@designsupply/startify-ui@0.2.3`のBuild済みCSSを読み込みます。ローカルのToken YAMLからCSSを生成しているものではありません。

Accessibility Addonは有効ですが、`a11y.test`は`todo`です。違反はTest UIへ表示する設定であり、現在は違反によってTestを失敗させる設定ではありません。

## 5. UIコンポーネント

### 5.1. 配置

現在のコンポーネントとStoryは次に配置します。

| 種別 | パス | 現在の状態 |
| --- | --- | --- |
| Reactコンポーネント | `frontend/ui/components/tsx/` | `Button.tsx`を実装 |
| HTMLコンポーネント | `frontend/ui/components/html/` | `.gitkeep`だけの予約領域 |
| Story | `frontend/ui/stories/` | `Button.stories.tsx`を実装 |

### 5.2. Button

`frontend/ui/components/tsx/Button.tsx`は、`button`または`a`として出力できるReactコンポーネントです。現在は次のPropsを持ちます。

| Prop | 主な選択肢・役割 |
| --- | --- |
| `htmlElement` | `button`、`a` |
| `variant` | `primary`、`secondary` |
| `size` | `small`、`default`、`large` |
| `display` | `block`、`inline` |
| `shape` | `square`、`rounded`、`pill` |
| `state` | `normal`、`hover`、`active`、`focus`、`disabled` |
| `color` | `default`または定義済みのColor ID |

Style用Classには`su-button-`を基点とする命名を使用します。旧Cursorルールに記載されていた`l-`、`c-`、`u-`、`js-`の共通Prefixは、現在のUI実装には適用されていません。

StorybookにはPrimary、Secondary、Disabled Button、Enabled Anchor、Disabled AnchorのStoryがあり、ControlsとAutodocsの対象です。

Native Buttonは、`state="disabled"` またはNative属性の `disabled={true}` のどちらかを指定した場合にDisabledとして扱います。Disabled用CSS ClassとNativeの `disabled` 属性を同期し、`state="disabled"` を `disabled={false}` で解除できない構成です。`type` の初期値は `button` とし、呼び出し側が指定した `submit` または `reset` を維持します。

Anchorは `state="disabled"` の場合に `aria-disabled="true"` と `tabIndex={-1}` を設定し、`href` を出力しません。Click Eventは `preventDefault()` と `stopPropagation()` で停止し、呼び出し側の `onClick` を実行しません。Enabled時は呼び出し側の `href`、`tabIndex`、`onClick` を維持します。Componentが管理する `className`、`disabled`、`aria-disabled`、`tabIndex`、`onClick`、`type` は、呼び出し側のProps展開で意図せず上書きされない順序で設定します。

## 6. Test・品質検証

現在はStorybook向けのVitest・Playwright設定が存在しますが、`frontend/ui/package.json`にはTestとType Checkを実行するScriptがありません。また、PlaywrightのChromiumがローカル環境へ導入されていない場合、Browser Testを完了できません。

現時点で利用者向けに定義されている検証コマンドは、デザイントークンの構造検証とStorybookの静的Buildです。

```bash
cd frontend/ui
npm run check:tokens
npm run build-storybook
```

StorybookのローカルTest環境、Test Script、Type Check CommandはIssue #61で整備します。Storybook TestのCI組み込みはIssue #61の対象外であり、将来の検討事項です。デザイントークン検証のCI組み込みも、現在は将来の検討事項です。

依存パッケージの監査で確認された既知脆弱性と、Storybook・Vitest関連Packageの更新はIssue #59で管理しています。

## 7. 旧Cursorルールの扱い

旧Cursorルールには、現在の共通実装では裏付けられない具体的な規則と、引き続き考慮すべき一般的な品質方針が併記されていました。この文書では両者を区別して扱います。

### 7.1. 共通仕様として採用しない項目

次の項目は、Startify-App全体へ適用する設定、生成処理、共通実装、Testが現在存在しないか、現在のUI実装と一致しません。

- Default Theme Colorの具体的な割り当て
- Tokenから生成されたCSS Custom Propertiesの使用
- `l-`、`c-`、`u-`、`js-`によるClass命名
- 共通HTML Layout
- 共通Breakpointの運用方法
- HTML、CSS、JavaScriptへ一律に2スペースのIndentを適用する規則
- HTMLのClass名へ一律にKebab Caseを適用する規則
- WebP、Critical CSSなど特定の手法を一律に必須とする規則

そのため、これらを現在のStartify-App全体へ適用する共通仕様としては採用しません。各Frontendで同様の規則を採用している場合は、その領域の設定、実装、個別仕様書を基準とします。

### 7.2. 変更時に考慮する品質方針

次の内容は、現在の全コンポーネントで達成を検証済みの要件ではありませんが、UIを追加・変更するときの品質上の考慮事項として維持します。

- RegularなColumn LayoutにはCSS Gridを基本とし、要素数や寸法が可変のLayoutではFlexboxも検討する
- Viewport幅や入力方法の違いを考慮し、対象FrontendのBreakpointと実機確認方針に従う
- SemanticなHTML、Keyboard操作、Screen Reader、必要なARIA属性を考慮する
- 文字と背景のContrastは、旧資料が目標としていたWCAG 2.1 AAの通常文字`4.5:1`以上を参考にする
- 画像のFormatと寸法を用途に合わせ、不要な転送量を避ける
- Animationでは、Layoutへの影響が小さい`transform`と`opacity`を優先的に検討する
- 未使用StyleやRender処理がPerformanceへ与える影響を確認する

これらの達成をこの文書だけで保証するものではありません。対象製品の要件、実装、Testに合わせて具体的な基準を定めます。

各Frontend固有のLayout、Responsive Design、Accessibility、Performance要件は、対象アプリケーションの実装と個別仕様書を基準とします。共通化する場合は、実装、検証方法、適用範囲を定めたうえでこの仕様書を更新します。

## 8. 変更時の方針

- Tokenを変更するときは、YAMLの`token-name`とFile名、内部参照の整合性を確認します。
- Token名を変更するときは、リポジトリ全体で旧名称の参照を確認します。
- UIコンポーネントを追加・変更するときは、対応するStoryを追加または更新します。
- Component API、Class名、外部CSSのVersionを変更するときは、既存Storyへの影響を確認します。
- 実装変更で現在仕様が変わる場合は、この文書も同じ作業範囲で更新します。
- Startify-UIの公開資料との差異を解消する変更は、単純な値の置換として扱わず、影響するFrontendと移行方法を確認します。

## 9. 既知の課題と将来検討

### 登録済みIssue

| Issue | 内容 |
| --- | --- |
| #59 | Storybook・Vitest関連の依存パッケージ更新と既知脆弱性の解消 |
| #61 | StorybookのローカルTest環境と検証コマンドの整備 |

Issueの実装完了後は、実装結果に合わせて該当記述を更新し、解消した既知課題をこの節から削除します。

### 将来検討

- Startify-UIとStartify-AppのToken値・分類の見直し
- Token SchemaまたはDTCGなどの標準形式の採用
- YAMLからCSS・TypeScriptなどを生成する仕組み
- 各FrontendとStorybookへのTokenの自動連携
- TokenまたはUI成果物のPackage化
- Icon・Pattern Tokenの追加
- デザイントークン検証のCI組み込み
- Visual Regression Test
- Accessibility違反を失敗として扱う基準
- Storybook TestのCI組み込み

これらは現在未実装であり、現在仕様として適用しません。
