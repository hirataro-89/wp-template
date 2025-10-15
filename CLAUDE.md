# WordPress Development Environment - Claude Code Guidelines

## プロジェクト概要

このプロジェクトは、Vite を使用したモダンなWordPress開発環境です。静的サイト開発とWordPressテーマ開発の両方に対応しており、FLOCSS準拠のCSS設計とESモジュールベースのJavaScript開発が可能です。

## 技術スタック

### 主要技術
- **ビルドツール**: Vite 5.0.13
- **CSS設計**: FLOCSS (Foundation, Layout, Object/Component, Object/Project, Utility)
- **CSSプリプロセッサ**: Sass (SCSS) 1.70.0
- **JavaScript**: ESモジュール (ES6+)
- **WordPressローカル環境**: @wordpress/env 5.14.0 (Docker)
- **フロントエンドライブラリ**: Splide.js 4.1.4, kiso.css 1.1.4

### 開発ツール
- **Linter**: ESLint 8.56.0, Stylelint 16.2.0
- **Formatter**: Prettier 3.2.4
- **画像最適化**: Sharp 0.33.4, WebP/AVIF変換対応
- **コンポーネント**: Handlebars テンプレート

## ディレクトリ構造

```
my-best-wp-env-main/
├── src/                           # 開発用ソースファイル
│   ├── assets/
│   │   ├── style/                 # SCSS (FLOCSS構造)
│   │   │   ├── foundation/        # リセット、ベーススタイル
│   │   │   ├── global/           # 変数、関数、mixins
│   │   │   ├── layout/           # l-プレフィックス (Header, Footer等)
│   │   │   ├── component/        # c-プレフィックス (再利用可能)
│   │   │   ├── project/          # p-プレフィックス (特定ページ)
│   │   │   ├── utility/          # u-プレフィックス (単一目的)
│   │   │   └── style.scss        # メインエントリーポイント
│   │   └── js/                   # JavaScript
│   │       ├── _drawer.js        # ドロワーメニュー機能
│   │       ├── _mv-slider.js     # メインビジュアルスライダー
│   │       ├── _viewport.js      # ビューポート関連処理
│   │       └── script.js         # メインエントリーポイント
│   ├── public/images/            # 画像ファイル (WebP/AVIF自動変換)
│   ├── index.html                # 静的開発用メインページ
│   └── _components.html          # コンポーネント一覧ページ
├── includes/                     # Handlebars共通パーツ
│   ├── p-header.html
│   └── p-footer.html
├── wordpress/                    # WordPress環境
│   ├── themes/TEMPLATE_NAME/     # WordPressテーマ
│   │   ├── functions.php         # テーマ機能設定
│   │   ├── functions-lib/        # 機能別分割ファイル
│   │   ├── parts/               # テンプレートパーツ
│   │   └── assets/              # ビルド出力先
│   ├── plugins/                 # プラグイン
│   │   ├── advanced-custom-fields/
│   │   ├── seo-simple-pack/
│   │   ├── duplicator/
│   │   └── wpvivid-backuprestore/
│   └── uploads/                 # アップロードファイル
├── dist/                        # 静的ビルド出力先
├── doc/                         # ドキュメント
│   ├── coding-guidelines.md
│   ├── pr-issue-guidelines.md
│   └── summary-of-cases.md
└── bin/                         # ビルドスクリプト
    └── watch-scss-globs.js      # SCSS監視スクリプト
```

## 開発フロー

### 環境セットアップ
```bash
# 依存関係インストール
yarn

# Dockerで WordPress環境起動
yarn wp-start

# 開発サーバー起動 (静的開発)
yarn dev
```

### 開発モード
- **静的開発**: `yarn dev` → http://localhost:5173
- **WordPress開発**: `yarn wp-start` → http://localhost:8888 + `yarn dev`

### ビルド
```bash
# 静的サイト用
yarn build

# WordPress用
yarn build:wp
```

## コーディング規則

### HTML

#### BEM/FLOCSS命名規則
```html
<!-- Component (c-プレフィックス) -->
<button class="c-button c-button--black">
  
<!-- Project (p-プレフィックス) -->
<header class="p-header l-header">
  
<!-- Layout (l-プレフィックス) -->
<div class="l-inner">
  
<!-- Utility (u-プレフィックス) -->
<span class="u-text__inline-block">
```

#### 画像処理
```html
<!-- Picture要素でWebP対応 -->
<picture>
  <source srcset="/images/sample.webp" type="image/webp">
  <img src="/images/sample.png" loading="lazy" width="512" height="512" alt="">
</picture>
```

#### 必須属性
- `img`タグ: `width`, `height`, `loading="lazy"` (mv以外)
- サイト内リンク: ルート相対パス (`/`で始まる)
- クラス名: 省略しない (例: `.title` ○, `.ttl` ×)

### SCSS/CSS

#### FLOCSS階層構造
```scss
// style.scss (エントリーポイント)
@use "foundation";     // リセット、ベース
@use "global";         // 変数、関数
@use "layout/**";      // レイアウト関連
@use "component/**";   // 再利用可能コンポーネント
@use "project/**";     // ページ固有スタイル
@use "utility/**";     // 単一目的クラス
```

#### CSS設計原則
```scss
// CSS Variables使用
.c-button {
  padding: calc(14 * var(--to-rem)) calc(60 * var(--to-rem));
  color: var(--color-black);
  border: 1px solid currentColor;
}

// margin/padding方向指定
.example {
  margin-block-start: var(--spacing-md);  // 上方向
  margin-inline-start: var(--spacing-sm); // 左方向
}

// ネスト禁止、font-sizeはrem使用
.c-component {
  font-size: calc(16 * var(--to-rem));
}
```

#### レスポンシブ対応
```scss
@include mq(md) {
  .c-button {
    padding: calc(16 * var(--to-rem)) calc(80 * var(--to-rem));
  }
}
```

### JavaScript

#### ESモジュール構造
```javascript
// script.js (自動生成されたエントリーポイント)
import './_drawer.js';
import './_mv-slider.js';
import './_viewport.js';
```

#### 変数宣言
- `const`, `let`を使用
- `var`は使用禁止

#### 画像インポート
```javascript
import imgSrc from "/assets/images/sample.png";
const image = new Image();
image.src = imgSrc;
```

### WordPress

#### テンプレート構造
```php
// functions.php - 機能別分割
get_template_part('functions-lib/func-enqueue');
get_template_part('functions-lib/func-base');
get_template_part('functions-lib/func-security');
```

#### Vite統合
```php
// WP_DEBUG による開発/本番切り替え
if (WP_DEBUG) {
    // 開発環境: Viteサーバーから読み込み
    $root = "http://localhost:5173";
    wp_enqueue_style('theme-styles', $root . '/src/assets/style/style.scss');
} else {
    // 本番環境: ビルド済みファイル
    $root = get_template_directory_uri();
    wp_enqueue_style('theme-styles', $root . '/assets/style/style.css');
}
```

## ファイル命名規則

### 画像ファイル
- **形式**: `カテゴリ[_名前][_連番][_状態].拡張子`
- **文字**: 英小文字・数字・ハイフン・アンダースコアのみ
- **配置**: `/src/public/images/` (ページ別分割禁止)

**例**:
```
bg_sample.png          # 背景画像
image_mv_01.webp       # メインビジュアル画像
icon_arrow.svg         # アイコン
```

### SCSS/JS ファイル
```
# SCSS
_c-button.scss         # Component
_p-header.scss         # Project  
_l-inner.scss          # Layout
_u-text.scss           # Utility

# JavaScript
_drawer.js             # 機能別JS
script.js              # メインエントリー
```

## コンポーネント開発

### 新規コンポーネント追加

1. **SCSS作成**
```scss
// src/assets/style/component/_c-newcomponent.scss
@use "../global" as *;

.c-newcomponent {
  // スタイル定義
}
```

2. **HTML例作成**
```html
<!-- src/_components.html に追加 -->
<div class="c-newcomponent">
  コンポーネント内容
</div>
```

3. **自動インポート**
- SCSS: `@use "component/**"` により自動読み込み
- ビルド時に `foundation/_index.scss` が自動生成

### モディファイア規則
```scss
// BEM形式のモディファイア
.c-button.c-button--black {
  color: var(--color-white);
  background-color: var(--color-black);
}
```

## ビルドプロセス

### Vite設定

#### 開発環境
- HMR (Hot Module Replacement) 対応
- SCSS即座コンパイル
- ES6+ → モダンブラウザ対応
- 画像WebP/AVIF自動変換

#### 本番ビルド
```javascript
// vite.config.js
export default defineConfig(({ mode }) => ({
  build: {
    outDir: mode === "wp" ? 
      resolve(__dirname, "wordpress/themes/TEMPLATE_NAME/") : 
      resolve(__dirname, "dist"),
    rollupOptions: {
      input: mode === "wp" ? inputsForWordPress : inputsForStatic
    }
  }
}));
```

### 画像最適化
```javascript
// 自動品質調整
ViteImageOptimizer({
  png: { quality: 80 },
  jpeg: { quality: 80 },
  webp: { quality: 80 },
  avif: { quality: 80 }
})
```

## WordPress 固有設定

### プラグイン構成
- **Advanced Custom Fields**: カスタムフィールド
- **SEO Simple Pack**: SEO対策
- **Duplicator**: バックアップ・移行
- **WPVivid**: バックアップ

### 環境設定
```json
// .wp-env.json
{
  "core": "https://wordpress.org/wordpress-6.4.2.zip",
  "config": { "WP_DEBUG": true },
  "mappings": {
    "wp-content/themes": "./wordpress/themes",
    "wp-content/plugins": "./wordpress/plugins"
  }
}
```

### データ同期
```bash
# エクスポート
yarn wp-contents export

# インポート  
yarn wp-contents import
```

## ベストプラクティス

### パフォーマンス
- CSS Custom Properties活用
- 画像遅延読み込み (`loading="lazy"`)
- WebP/AVIF対応
- Google Fonts プリコネクト

### アクセシビリティ
- セマンティックHTML
- 適切なalt属性
- カラーコントラスト配慮

### SEO
- 構造化データ対応
- メタタグ適切設定
- 画像最適化

### 保守性
- 機能別ファイル分割
- BEM/FLOCSS準拠
- TypeScriptライクな開発体験

## デバッグ・トラブルシューティング

### よくある問題
1. **SCSS変更が反映されない**
   - `bin/watch-scss-globs.js` が監視中か確認
   - Vite サーバー再起動

2. **WordPress画像パス問題**
   ```php
   // 正しいパス指定
   <img src="<?php echo get_template_directory_uri();?>/images/sample.png">
   ```

3. **JavaScript ESモジュールエラー**
   - `script.js` の自動生成インポート確認
   - Vite クライアント読み込み確認

### 開発時の注意点
- WordPressのHMRは無効化済み（手動リロード必要）
- WP_DEBUG による開発/本番環境切り替え
- ビルド前に dist/ クリーンアップ実行

## Git運用規則

### ブランチ戦略
- main: 本番環境
- develop: 開発環境
- feature/xxx: 機能開発

### PR運用
- レビュワー: 平林を設定
- 細かい機能単位でPR作成
- issue連携必須

### コミットメッセージ
```
add: 新機能追加
fix: バグ修正  
update: 機能改善
refactor: リファクタリング
```

このガイドラインに従って開発を進めることで、統一性のある保守しやすいコードベースを維持できます。