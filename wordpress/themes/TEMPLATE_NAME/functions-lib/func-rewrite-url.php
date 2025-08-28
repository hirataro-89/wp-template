<?php

/**
 * 汎用カスタムリライトルールシステム
 * フィルターで設定可能な構造
 * 必要な時だけカスタムURLルールを追加できる
 */

/**
 * リライトルールの設定を取得
 */
function get_rewrite_rules_config() {
	// デフォルト設定（フィルターでカスタマイズ可能）
	$config = apply_filters('rewrite_rules_config', array(
		// カテゴリーリライトルール
		'category_rules' => array(
			// 例: 'news' => array('pattern' => '^news/?$', 'query' => 'index.php?category_name=news')
		),
		
		// カスタム投稿タイプリライトルール
		'post_type_rules' => array(
			// 例: 'portfolio' => array('pattern' => '^portfolio/?$', 'query' => 'index.php?post_type=portfolio')
		),
		
		// カスタムリライトルール（完全カスタム）
		'custom_rules' => array(
			// 例: array('pattern' => '^special/([^/]+)/?$', 'query' => 'index.php?special=$matches[1]')
		),
		
		// クエリ変数の追加
		'query_vars' => array(
			// 例: 'special', 'filter'
		),
		
		// ページネーション設定
		'pagination_rules' => array(
			// 自動生成するかどうか
			'auto_generate' => true,
			// カスタムページネーションパターン
			'custom_patterns' => array()
		)
	));
	
	return $config;
}

/**
 * カスタムリライトルールを追加
 */
function add_custom_rewrite_rules()
{
	$config = get_rewrite_rules_config();
	
	// カテゴリー用リライトルール
	foreach ($config['category_rules'] as $slug => $rule_data) {
		if (empty($rule_data['pattern']) || empty($rule_data['query'])) {
			continue;
		}
		
		add_rewrite_rule($rule_data['pattern'], $rule_data['query'], 'top');
		
		// ページネーション自動生成
		if ($config['pagination_rules']['auto_generate']) {
			$pagination_pattern = str_replace('/?$', '/page/([0-9]+)/?$', $rule_data['pattern']);
			$pagination_query = $rule_data['query'] . '&paged=$matches[1]';
			add_rewrite_rule($pagination_pattern, $pagination_query, 'top');
		}
	}
	
	// カスタム投稿タイプ用リライトルール
	foreach ($config['post_type_rules'] as $post_type => $rule_data) {
		if (empty($rule_data['pattern']) || empty($rule_data['query'])) {
			continue;
		}
		
		add_rewrite_rule($rule_data['pattern'], $rule_data['query'], 'top');
		
		// ページネーション自動生成
		if ($config['pagination_rules']['auto_generate']) {
			$pagination_pattern = str_replace('/?$', '/page/([0-9]+)/?$', $rule_data['pattern']);
			$pagination_query = $rule_data['query'] . '&paged=$matches[1]';
			add_rewrite_rule($pagination_pattern, $pagination_query, 'top');
		}
	}
	
	// 完全カスタムリライトルール
	foreach ($config['custom_rules'] as $rule) {
		if (empty($rule['pattern']) || empty($rule['query'])) {
			continue;
		}
		
		$priority = $rule['priority'] ?? 'top';
		add_rewrite_rule($rule['pattern'], $rule['query'], $priority);
	}
	
	// カスタムページネーションパターン
	foreach ($config['pagination_rules']['custom_patterns'] as $pattern_data) {
		if (empty($pattern_data['pattern']) || empty($pattern_data['query'])) {
			continue;
		}
		
		add_rewrite_rule($pattern_data['pattern'], $pattern_data['query'], 'top');
	}
	
	// クエリ変数の追加
	foreach ($config['query_vars'] as $var) {
		if (!empty($var)) {
			add_rewrite_tag('%' . $var . '%', '([^&]+)');
		}
	}
}
add_action('init', 'add_custom_rewrite_rules');

/**
 * カスタムクエリ変数の追加
 */
function add_custom_query_vars($vars)
{
	$config = get_rewrite_rules_config();
	
	foreach ($config['query_vars'] as $var) {
		if (!empty($var) && !in_array($var, $vars)) {
			$vars[] = $var;
		}
	}
	
	return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');

/**
 * テーマ有効化時にリライトルールをフラッシュ
 */
function flush_rewrite_rules_on_theme_activation()
{
	add_custom_rewrite_rules();
	flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_on_theme_activation');

/**
 * 管理者用：手動でリライトルールをフラッシュする機能
 */
function manual_flush_rewrite_rules()
{
	if (current_user_can('administrator')) {
		flush_rewrite_rules();
		// デバッグ情報を表示（必要に応じて）
		if (defined('WP_DEBUG') && WP_DEBUG) {
			add_action('admin_notices', function() {
				echo '<div class="notice notice-success"><p>Rewrite rules have been flushed.</p></div>';
			});
		}
	}
}

// 使い方: URLに ?flush_rules=1 を追加してアクセス
if (isset($_GET['flush_rules']) && $_GET['flush_rules'] == '1') {
	add_action('init', 'manual_flush_rewrite_rules');
}

/**
 * カスタムクエリ修正（アーカイブページ対応）
 */
function modify_custom_archive_query($query)
{
	// 管理画面やメインクエリ以外は除外
	if (is_admin() || !$query->is_main_query()) {
		return;
	}
	
	$config = get_rewrite_rules_config();
	
	// カテゴリーページでの追加処理
	if (is_category()) {
		$cat = get_queried_object();
		
		// 設定されたカテゴリーの場合のみ処理
		if (isset($config['category_rules'][$cat->slug])) {
			$category_config = $config['category_rules'][$cat->slug];
			
			// 投稿件数の設定
			if (!empty($category_config['posts_per_page'])) {
				$posts_per_page = wp_is_mobile() && !empty($category_config['posts_per_page_mobile']) 
					? $category_config['posts_per_page_mobile'] 
					: $category_config['posts_per_page'];
				
				$query->set('posts_per_page', $posts_per_page);
			}
			
			// フィルター処理
			if (!empty($category_config['enable_filter']) && isset($_GET['filter'])) {
				$current_filter = sanitize_text_field($_GET['filter']);
				if (!empty($current_filter)) {
					$query->set('tax_query', array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'category',
							'field' => 'slug',
							'terms' => $cat->slug
						),
						array(
							'taxonomy' => 'category',
							'field' => 'slug',
							'terms' => $current_filter
						)
					));
				}
			}
		}
	}
	
	// カスタム投稿タイプアーカイブでの追加処理
	if (is_post_type_archive()) {
		$post_type = get_query_var('post_type');
		
		if (isset($config['post_type_rules'][$post_type])) {
			$post_type_config = $config['post_type_rules'][$post_type];
			
			// 投稿件数の設定
			if (!empty($post_type_config['posts_per_page'])) {
				$posts_per_page = wp_is_mobile() && !empty($post_type_config['posts_per_page_mobile']) 
					? $post_type_config['posts_per_page_mobile'] 
					: $post_type_config['posts_per_page'];
				
				$query->set('posts_per_page', $posts_per_page);
			}
		}
	}
}
add_action('pre_get_posts', 'modify_custom_archive_query');

/**
 * 使用方法とカスタマイズ例:
 *
 * functions.phpに以下のような設定を追加：
 *
 * function my_rewrite_rules_config($config) {
 *     // /news/ → お知らせ一覧
 *     $config['category_rules']['news'] = array(
 *         'pattern' => '^news/?$',
 *         'query' => 'index.php?category_name=news',
 *         'posts_per_page' => 6,
 *         'posts_per_page_mobile' => 3,
 *         'enable_filter' => true
 *     );
 *     
 *     // /portfolio/ → ポートフォリオアーカイブ
 *     $config['post_type_rules']['portfolio'] = array(
 *         'pattern' => '^portfolio/?$',
 *         'query' => 'index.php?post_type=portfolio',
 *         'posts_per_page' => 9
 *     );
 *     
 *     // 完全カスタムルール
 *     $config['custom_rules'][] = array(
 *         'pattern' => '^special/([^/]+)/?$',
 *         'query' => 'index.php?special=$matches[1]',
 *         'priority' => 'top'
 *     );
 *     
 *     // クエリ変数の追加
 *     $config['query_vars'][] = 'special';
 *     $config['query_vars'][] = 'filter';
 *     
 *     return $config;
 * }
 * add_filter('rewrite_rules_config', 'my_rewrite_rules_config');
 *
 * ページネーションの無効化:
 * function disable_pagination($config) {
 *     $config['pagination_rules']['auto_generate'] = false;
 *     return $config;
 * }
 * add_filter('rewrite_rules_config', 'disable_pagination');
 */