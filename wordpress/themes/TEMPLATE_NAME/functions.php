<?php

/**
 * Functions
 */
// スクリプトとスタイルの読み込み
get_template_part('functions-lib/func-enqueue');

// 基本設定
get_template_part('functions-lib/func-base');

// セキュリティー対応
get_template_part('functions-lib/func-security');

// ショートコードの設定
get_template_part('functions-lib/func-shortcode');

// URLのショートカット設定
get_template_part('functions-lib/func-url');

// デフォルト投稿タイプのラベル変更
get_template_part('functions-lib/func-add-posttype-post');

// メインクエリの投稿記事表示件数を切り替える
get_template_part('functions-lib/func-posts-edit');

// ターム名を検索対象に含める
get_template_part('functions-lib/func-search-query');

// カテゴリーアーカイブページの設定
get_template_part('functions-lib/func-posts-per-page');

// 構造化データの設定（汎用化済み）
get_template_part('functions-lib/func-structured-data');

// カスタムリライトルールの設定
get_template_part('functions-lib/func-rewrite-url');

// パンくずリストの設定
get_template_part('functions-lib/func-breadcrumb');
