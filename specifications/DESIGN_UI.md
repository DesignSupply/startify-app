# UIデザインガイドライン

---

## 画面レイアウト

全体の画面レイアウトは、`frontend/_ui/ui.html` のHTML構造をベースに、下記のテンプレート用HTMLファイルの内容をそのまま使用し、配置したものを基本とする。なお、以下のリストのネスト構造は、実際のHTML構造に対応するものとする。

- head要素
  - 共通メタ情報: `frontend/_ui/head/head.html`
  - 画面固有メタ情報: `frontend/_ui/head/meta.html`
- body要素
  - レイアウトコンテナー: `frontend/_ui/layouts/layout.html`
    - ヘッダーコンポーネント: `frontend/_ui/components/header.html`
    - コンテンツコンポーネント: `frontend/_ui/components/main.html`
    - フッターコンポーネント: `frontend/_ui/components/footer.html`
  - オフキャンバスコンポーネント: `frontend/_ui/components/offcanvas.html`

`frontend/_ui/layouts/` あるいは `frontend/_ui/components/` ディレクトリに配置されたHTMLファイルは、テンプレートとして使用される前提のフォーマットとなっており、`<!-- child components -->` というコメントが配置されている箇所に、子要素のとして別のテンプレート用HTMLファイルの中身が配置される想定とします。





