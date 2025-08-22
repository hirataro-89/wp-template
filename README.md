# 開発環境に関するDOC

## 各種ガイドライン

[案件概要はこちら](./doc/coding-guidelines.md)

[コーディングガイドラインはこちら](./doc/coding-guidelines.md)

[PRやissueに関するガイドラインはこちら](./doc/pr-issue-guidelines.md)

## 説明動画

[こちら](https://defiant-crow-3a6.notion.site/1cd5a20fea11451aa4c16f1490afeea8?pvs=4)

## 必要環境

- Node.js （18系以上推奨）
- Docker(WordPress化する場合のみ)
  - [こちら](https://matsuand.github.io/docs.docker.jp.onthefly/get-docker/)からDockerをインストール
  - アプリを立ち上げ、アカウント登録し起動しておく
  - ※WordPress化しないならこの工程は不要

## 開発環境立ち上げ

- Dockerを立ち上げる(WordPress化する場合のみ)
  - macの場合、ステータスバーにDockerのアイコンが表示されていて`running`となっていればOK
- `yarn`とたたいて`node_module`をインストール

## 静的制作時

- `yarn dev`
  - ローカルサーバー`localhost:5173`が立ち上がる
- 静的資材は基本`src`フォルダ内で作成。
- CSSやJavaScriptは直接`.scss`ファイルや`.js`を参照すれば、Viteがいい感じにしてくれます。
- ※WordPress化しない場合、`wordpress`ディレクトリは不要なので削除でOK
  - gitignoreに記載されているコメントアウトも消す（監視対象外にするため）

```html
<link rel="stylesheet" href="/assets/style/style.scss" />
<script src="/assets/js/script.js" type="module"></script>
```

- header等の共通パーツは`includes`フォルダ内で作成し、再利用できるようにしています。
  - `handlebars`というプラグインを使用
  - [使い方参考記事](https://zenn.dev/tamon_kondo/articles/e6aceb1ea15f4b)

```html
{{> header}} // includes/header.htmlを呼び出す
```

あとは通常の手順でHTML・CSS・JavaScriptを開発していけばOK。

## WordPressテーマ開発時（改善版）

### 方法1: 手動起動

1. `yarn wp-start`
   - 初回は色々ダウンロードするので時間かかる
2. `yarn dev:wp`
   - Vite開発サーバーがWordPressモードで起動
3. `localhost:8888`にアクセスしてWordPressを確認
   - 初回ログイン時は別テーマがアクティブになっているので、「TEMPLATE NAME」をアクティブにしてください

### 方法2: 自動起動（推奨）

- `yarn wp-dev`
  - WordPress環境とVite開発サーバーが同時に起動
  - WordPressが準備できるまで自動で待機してからViteサーバーを起動

### 開発の流れ

- 開発時は`src`フォルダ内のファイルを操作してください
- Viteのホットリロードが有効なので、ファイル変更時に自動でブラウザが更新されます
- WordPress環境でも`localhost:5173`のViteサーバーからアセットを読み込むため、高速な開発が可能です

### 環境判定の仕組み

WordPress環境では以下の条件で開発モードを判定します：

- `WP_DEBUG`が`true`の場合
- `WP_ENVIRONMENT_TYPE`が`development`の場合
- ホストが`localhost:8888`の場合

開発モード時は：

- Vite開発サーバー（`localhost:5173`）からアセットを読み込み
- ホットリロードが有効
- ソースマップが生成される
- モジュール形式のJavaScriptが使用される

### 終了時

- `yarn wp-stop`でDockerのコンテナを停止

## 画像の格納先、読み込み方について

画像は`src/public/images/`に格納してください。<br>
なお、`avif`もしくは`webp`形式に自動で変換するようなscript入れてるので、<br>
画像を読み込む際は基本 `avif`もしくは`webp`でお願いします<br>
(画像の変換は`vite.config.js`で設定可能)

### 画像最適化の新機能

- **AVIF形式優先**: WebPより高圧縮率のAVIFをデフォルトで生成
- **複数サイズ生成**: 1x, 2xサイズの自動生成（`image@2x.avif`など）
- **自動リサイズ**: 最大1920x1080pxを超える画像は自動でリサイズ
- **元画像最適化**: JPEG/PNGの品質向上版も生成

フォルダ構造

```
└includes // コンポーネントフォルダ
  └hoge.html
  └fuga.html
└src
  └assets
    └styles
      └style.scss
    └js
      └script.js
  └public
    └images
      └background.png
      └js.png
      └static.png
```

▼HTML

```html
<picture>
  <source srcset="/images/static.avif" type="image/avif" />
  <source srcset="/images/static.webp" type="image/webp" />
  <img
    src="/images/static.png"
    loading="lazy"
    width="512"
    height="512"
    alt=""
  />
</picture>
```

▼CSS

```css
background-image: image-set(
  url('/images/background.avif') type('image/avif'),
  url('/images/background.webp') type('image/webp'),
  url('/images/background.png') type('image/png')
);
```

jsで画像ファイルを読み込む場合はViteにビルド時にパス解決されるよう`import`文で読み込んでください。

▼JS

```js
import imgsrc from "/assets/images/js.png";
// jsから画像を読み込むサンプル
const canvas = document.querySelector<HTMLCanvasElement>("#canvas");
const context = canvas!.getContext("2d");
const image = new Image(300, 300);
image.src = imgsrc;
image.addEventListener("load", () => {
  context?.drawImage(image, 0, 0, 300, 300);
})
```

▼PHP

```php
<img src="<?php echo get_template_directory_uri();?>/images/static.png" alt="" width="300" height="300" />
```

上記のように静的資材HTMLのコードの頭に`<?php echo get_template_directory_uri();?>`を付与することでうまく読み込めるようになります。

## 静的資材ビルドについて

- 静的資材をビルドする場合は`yarn build`を実行
- `dist`フォルダに一式出力される

## WordPress用ビルドについて

- **開発用ビルド**: `yarn build:wp:dev` - ソースマップ有効、非圧縮
- **本番用ビルド**: `yarn build:wp:prod` - 圧縮、ソースマップ無効
- `wordpress/themes/TEMPLATE_NAME/`内に`assets`フォルダと`images`フォルダが出力される
  - `assets`フォルダにはビルドした各種CSSやJavaScriptが出力される
  - `images`には画像が出力される

## 品質管理について

### 自動品質チェック

- **コミット前**: 自動でESLint、Prettier、Stylelintが実行される
- **プッシュ前**: 品質チェック + ビルドテストが実行される

### 手動品質チェック

```bash
# 品質チェック実行
yarn quality-check

# 自動修正
yarn lint:fix && yarn prettier:fix
```

## WordPressのログイン方法

- `http://localhost:8888/wp-admin/`にアクセス
- ID: `admin`
- パスワード: `password`
  - 初回は言語設定が英語なので日本語に変えておくと良い。

## WordPressコンテンツの同期方法

WordPress内で作成した記事やページ、その他設定などはNPM Scriptsの`wp-contents export`コマンドでバックアップファイルを出力できます。このバックアップファイルをGitなどで管理し、`wp-contents import`でそのバックアップファイルをインポートして開発者間でのWordPressコンテンツを同期できます。あくまで単一のバックアップファイルなので差分管理などはできず、頻繁な更新には向きません。（コンフリクトしてもどちらかのファイルしか採用できません）

## 自動インポートファイル生成機能

このプロジェクトには、JavaScriptおよびSCSSファイルのインポートを自動化するためのシェルスクリプト`generate-imports.sh`が含まれています。このスクリプトは、指定されたディレクトリ内のすべてのJavaScriptおよびSCSSファイルを自動的にインポートするファイルを生成します。

### 使用方法

1. スクリプトに実行権限を付与します。

   ```bash
   chmod +x generate-imports.sh
   ```

2. スクリプトを実行します。
   ```bash
   ./generate-imports.sh
   ```

### 機能

- `src/assets/js/script.js`に、`src/assets/js`ディレクトリ内のすべてのJavaScriptファイルをインポートします。ただし、`script.js`自身はインポートしません。
- 各`src/assets/style`内のディレクトリに`_index.scss`ファイルを生成し、そのディレクトリ内のすべてのSCSSファイルをインポートします。ただし、`_index.scss`自身はインポートしません。
- `src/assets/style/style.scss`に、各`_index.scss`ファイルをインポートします。

この機能により、手動でインポート文を追加する手間を省き、プロジェクトの管理を容易にします。
