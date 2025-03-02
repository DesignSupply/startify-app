# UIデザイン制作

## デザイントークン

画面上のレイアウトやUIコンポーネント、テキストなどは定義されたデザイントークンに基づいてスタイルが適用されるものとする。
デザイントークンを導入することで、UIデザインに一貫性をもたせ、クオリティコントロールとデザインの調整時に柔軟性を持たせることを目的とします。

- カラースキーム: `frontend/_design-tokens/color-scheme.yaml`
  - デザインテーマやアプリケーションのステータスを示す配色に関するトークン
- サイズスケール: `frontend/_design-tokens/size-scale.yaml`
  - 要素の幅やサイズ、余白などのサイズに関するトークン
- タイポグラフィ: `frontend/_design-tokens/typography.yaml`
  - 書体や文字サイズ、ウエイトなどのフォントに関するトークン
- グリッドシステム: `frontend/_design-tokens/grid-system.yaml`
  - コンテンツ幅やグリッドレイアウトのグリッド数、余白などを定義するトークン
- ドロップシャドウ: `frontend/_design-tokens/dropshadow.yaml`
  - 要素のドロップシャドウの色やオフセットなどを定義するトークン
- コーナースタイル: `frontend/_design-tokens/corner-style.yaml`
  - 要素の角の丸みやコーナーの形状を定義するトークン
- イージング: `frontend/_design-tokens/easing.yaml`
  - アニメーションのイージングを定義するトークン

---

## デザインガイドライン

アプリケーションやウェブサイトの世界観やブランドイメージを表現するために必要となる、配色やフォントなどをデザインのガイドラインとして定義します。
基本的にはデザイントークンで定義されているものを目的に合わせて選定します。デザインガイドラインを定めることでデザインに独自性を持たせることができます。

### テーマカラー

テーマカラーはデザインテーマにあわせて定義される配色で、デザイントークンとは別に定義されるものとし、ベースカラー、プライマリーカラー、セカンダリーカラー、アクセントカラーの4軸で配色を定義する。
基本的には、`frontend/_design-tokens/theme-color.yaml` に定義されているカラースキームを使用することとする。
テーマカラーの指定がない場合、デフォルトではベースカラー、プライマリーカラー、セカンダリーカラー、アクセントカラーを以下のように定義する。

- ベースカラー: `base`
  - `gray-color` の `id: gray-cool` の `tone0` から `tone9` を使用
- プライマリーカラー: `primary`
  - `palette-color` の `id: blue-violet` の `tone0` から `tone9` を使用
- セカンダリーカラー: `secondary`
  - `palette-color` の `id: light-blue` の `tone0` から `tone9` を使用
- アクセントカラー: `accent`
  - `palette-color` の `id: orange-yellow` の `tone0` から `tone9` を使用 

---

## 画面レイアウト

新規作成する際の画面レイアウトは、`frontend/_ui/page.html` のHTML構造をベースに、下記のテンプレート用HTMLファイルの内容に沿って配置したものを基本とする。
なお、以下のリストのネスト構造は、実際にコーディングを行うHTMLのネスト構造に対応するものとする。

- head要素
  - 共通メタ情報: `frontend/_ui/head/head.html`
  - 画面固有メタ情報: `frontend/_ui/head/meta.html`
- body要素
  - レイアウトコンテナー: `frontend/_ui/layouts/layout.html`
    - ヘッダーコンポーネント: `frontend/_ui/components/header.html`
    - コンテンツコンポーネント: `frontend/_ui/components/main.html`
    - フッターコンポーネント: `frontend/_ui/components/footer.html`
  - オフキャンバスコンポーネント: `frontend/_ui/components/offcanvas.html`

上記で記載されている各種HTMLテンプレートファイルは、指示がない限りすべて使用するものとし、上記のHTML基本の画面レイアウトを構成するHTMLとする。

---

## コーディング方針

### 基本コーディングルール 

- HTML、CSS、JavaScriptファイルのコード内インデントは、2スペース（半角スペース2つ分）とする。
- HTMLのclass属性値は、ケバブケースで記載する。
- テンプレート用HTMLファイルは、`frontend/_ui/layouts/` あるいは `frontend/_ui/components/` ディレクトリに配置され、コードの一貫性を保つため、例外を除き基本的にはこれらのHTMLファイルの中身をそのまま使用するようにします。
- `frontend/_ui/layouts/` あるいは `frontend/_ui/components/` ディレクトリに配置されたHTMLファイルは、テンプレートとして使用される前提のフォーマットとなっており、`<!-- child components -->` というコメントが配置されている箇所に、子要素のとして別のテンプレート用HTMLファイルの中身が配置される想定とします。
- `frontend/_ui/layouts/` と `frontend/_ui/components/` と `frontend/_ui/head/` ディレクトリに配置されたHTMLファイル、ならびにベースのHTMLである `frontend/_ui/page.html` については、**直接編集を加えずに記載されたHTMLの内容をコピーし、新規作成するHTMLファイル内に書き写していく**形でコーディングを進めていきます。
- `frontend/_ui/layouts/` および `frontend/_ui/components/` ディレクトリに配置されたHTMLファイルにおいて、あらかじめ設定されたclass名は削除、変更せずに残しておくものとする。
- 新規作成するHTMLファイルは、`backend/_webroot/preview` ディレクトリ配下に配置します。

### スタイルシートに関するコーディングルール

- スタイルシートは別ファイルで管理せず、head要素内に、style要素を配置して管理し、1つのHTMLで完結させる形を基本とします。
- スタイルリセット用のCSSは、 `frontend/_ui/layouts/head/head.html` のテンプレート内に用意されているものを使用します。

### レイアウトに関するコーディングルール

- カラムレイアウトを実装する場合には、基本的にGrid Layoutを使用する。内包するアイテム要素数が可変長など、フレキシブルなレイアウト調整が求められる場合にはFlexbox Layoutで対応することもOKとする。

### デザインに関するコーディングルール

- デザイントークンで使用されている値を扱う場合には、CSS変数を利用し、効率よくスタイルを指定できるようにします。

---
