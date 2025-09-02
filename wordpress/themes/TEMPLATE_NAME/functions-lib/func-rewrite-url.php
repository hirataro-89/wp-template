<?php

/**
 * カスタムリライトルール設定
 */

// リライトルール追加
function add_custom_rewrite_rules()
{
	// 必要なルールをここに追加
	// add_rewrite_rule('^news/?$', 'index.php?category_name=news', 'top');
	// add_rewrite_rule('^portfolio/?$', 'index.php?post_type=portfolio', 'top');
}
add_action('init', 'add_custom_rewrite_rules');

// カスタムクエリ変数追加（必要時のみ）
function add_custom_query_vars($vars)
{
	// $vars[] = 'custom_search';
	return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');

// テーマ有効化時にルールフラッシュ
function flush_rules_on_activation()
{
	add_custom_rewrite_rules();
	flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rules_on_activation');

// 管理者用：リライトルール手動フラッシュ
// 使い方：サイトURL/?flush_rules=1 にアクセス
if (isset($_GET['flush_rules']) && $_GET['flush_rules'] == '1' && current_user_can('administrator')) {
	add_action('init', function () {
		// リライトルールをクリア＆再生成
		flush_rewrite_rules();

		// デバッグモード時は管理画面に成功メッセージを表示
		if (WP_DEBUG) {
			add_action('admin_notices', function () {
				echo '<div class="notice notice-success"><p>リライトルールをフラッシュしました</p></div>';
			});
		}
	});
}
