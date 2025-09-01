<?php

/**
 * シンプルなカスタムリライトルール
 * 必要な機能だけに絞った軽量版
 */

/**
 * カスタムリライトルールを追加
 * 使いたいルールを直接記述する方式
 */
function add_simple_rewrite_rules()
{
	// ===========================================
	// ここに必要なリライトルールを追加
	// ===========================================

	// 例1: /news/ → お知らせ一覧
	// add_rewrite_rule('^news/?$', 'index.php?category_name=news', 'top');
	// add_rewrite_rule('^news/page/([0-9]+)/?$', 'index.php?category_name=news&paged=$matches[1]', 'top');

	// 例2: /portfolio/ → ポートフォリオアーカイブ
	// add_rewrite_rule('^portfolio/?$', 'index.php?post_type=portfolio', 'top');
	// add_rewrite_rule('^portfolio/page/([0-9]+)/?$', 'index.php?post_type=portfolio&paged=$matches[1]', 'top');

	// 例3: /company/about/ → 固定ページ
	// add_rewrite_rule('^company/about/?$', 'index.php?pagename=company-about', 'top');

	// 例4: カスタムクエリ変数を使った特殊ページ
	// add_rewrite_rule('^search/([^/]+)/?$', 'index.php?custom_search=$matches[1]', 'top');
}
add_action('init', 'add_simple_rewrite_rules');

/**
 * カスタムクエリ変数を追加
 * 上記でカスタム変数を使う場合のみ必要
 */
function add_simple_query_vars($vars)
{
	// カスタムクエリ変数を追加（必要に応じて）
	// $vars[] = 'custom_search';

	return $vars;
}
add_filter('query_vars', 'add_simple_query_vars');

/**
 * テーマ有効化時にリライトルールをフラッシュ
 */
function flush_rewrite_rules_on_activation()
{
	add_simple_rewrite_rules();
	flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_on_activation');

/**
 * 管理者用：リライトルール手動フラッシュ
 * URLに ?flush_rules=1 を追加してアクセス
 */
if (isset($_GET['flush_rules']) && $_GET['flush_rules'] == '1' && current_user_can('administrator')) {
	add_action('init', function () {
		flush_rewrite_rules();
		if (defined('WP_DEBUG') && WP_DEBUG) {
			add_action('admin_notices', function () {
				echo '<div class="notice notice-success"><p>リライトルールをフラッシュしました</p></div>';
			});
		}
	});
}
