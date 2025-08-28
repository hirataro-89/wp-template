<?php

/**
 * 汎用パンくずリストシステム
 * Breadcrumb NavXT プラグインの代替として作成
 *
 * 特徴:
 * - プラグイン不要
 * - 高速動作
 * - 完全カスタマイズ可能
 * - エラーハンドリング強化
 * - 設定可能な構造
 */

// 定数定義
define('BREADCRUMB_ENABLE_DEBUG', false);

/**
 * パンくず設定を取得（テーマごとにカスタマイズ可能）
 * functions.phpで add_filter('breadcrumb_config', 'your_custom_breadcrumb_config') として設定可能
 */
function get_breadcrumb_config() {
	// デフォルト設定（各テーマでオーバーライド可能）
	$config = apply_filters('breadcrumb_config', array(
		// 投稿タイプごとのラベル設定
		'post_type_labels' => array(
			'post' => 'お知らせ一覧',
			'news' => 'ニュース一覧',
			'blog' => 'ブログ一覧'
		),
		// カスタムURL設定
		'custom_urls' => array(
			// 例: 'news' => home_url('/news/')
		),
		// カテゴリー名のマッピング
		'category_mapping' => array(
			'news' => 'お知らせ一覧'
		),
		// 固定ページの階層設定
		'page_hierarchy' => array(
			// 例: 'child-page' => 'parent-page'
		)
	));
	
	return $config;
}

// パンくず用のURL設定（動的生成・カスタマイズ可能）
function get_breadcrumb_urls()
{
	$config = get_breadcrumb_config();
	$default_urls = array(
		'news' => home_url('/news/'),
		'blog' => home_url('/blog/')
	);
	
	// カスタムURLがあれば優先
	return array_merge($default_urls, $config['custom_urls']);
}

/**
 * パンくずリストの構造化データ（JSON-LD）を生成
 * func-structured-data.phpで呼び出される
 */
function get_custom_breadcrumb_structured_data()
{
	try {
		$breadcrumbs = custom_get_breadcrumb_items();
		
		// 構造化データ形式に変換（Schema.org BreadcrumbList仕様準拠）
		$structured_data = array();
		$position = 1;

		foreach ($breadcrumbs as $breadcrumb) {
			// 現在ページ（current: true）は構造化データに含めない
			if ($breadcrumb['current']) {
				continue;
			}

			// URLが空の場合もスキップ
			if (empty($breadcrumb['url'])) {
				continue;
			}

			$structured_data[] = array(
				'@type' => 'ListItem',
				'position' => $position,
				'name' => $breadcrumb['title'],
				'item' => array(
					'@type' => 'WebPage',
					'@id' => $breadcrumb['url']
				)
			);
			$position++;
		}

		return $structured_data;
	} catch (Exception $e) {
		return array();
	}
}

/**
 * 構造化データと共有するためのパンくず配列を生成する関数
 * @return array パンくずの項目配列
 */
function custom_get_breadcrumb_items()
{
	static $breadcrumbs = null;
	if ($breadcrumbs !== null) {
		return $breadcrumbs;
	}

	$breadcrumbs = array();
	$urls = get_breadcrumb_urls();
	$config = get_breadcrumb_config();

	// ホーム
	$breadcrumbs[] = array(
		'title' => 'TOP',
		'url' => home_url('/'),
		'current' => false
	);

	// 各ページタイプごとの処理
	if (is_front_page()) {
		// トップページの場合は何もしない（TOPのみ表示）
		
	} elseif (is_post_type_archive()) {
		$post_type = get_post_type();
		$post_type_object = get_post_type_object($post_type);
		
		// カスタムラベルがあれば使用、なければオブジェクトのラベル
		$archive_title = isset($config['post_type_labels'][$post_type]) 
			? $config['post_type_labels'][$post_type]
			: ($post_type_object ? $post_type_object->labels->name : 'アーカイブ');
		
		$breadcrumbs[] = array(
			'title' => $archive_title,
			'url' => '',
			'current' => true
		);
		
	} elseif (is_single()) {
		$post_type = get_post_type();
		
		if ($post_type === 'post' || isset($config['post_type_labels'][$post_type])) {
			// 投稿タイプのアーカイブページへのリンクを追加
			$archive_link = get_post_type_archive_link($post_type);
			$archive_title = isset($config['post_type_labels'][$post_type]) 
				? $config['post_type_labels'][$post_type]
				: 'アーカイブ';
			
			if ($archive_link || isset($urls[strtolower($post_type)])) {
				$archive_url = $archive_link ?: $urls[strtolower($post_type)];
				$breadcrumbs[] = array(
					'title' => $archive_title,
					'url' => $archive_url,
					'current' => false
				);
			}
			
			$breadcrumbs[] = array(
				'title' => get_the_title() ?: '詳細ページ',
				'url' => '',
				'current' => true
			);
		} else {
			$post_type_object = get_post_type_object($post_type);
			$archive_link = get_post_type_archive_link($post_type);
			if ($archive_link && $post_type_object) {
				$breadcrumbs[] = array(
					'title' => $post_type_object->labels->name ?: 'アーカイブ',
					'url' => $archive_link,
					'current' => false
				);
			}
			$breadcrumbs[] = array(
				'title' => get_the_title() ?: '詳細ページ',
				'url' => '',
				'current' => true
			);
		}
		
	} elseif (is_category()) {
		$cat = get_queried_object();
		
		$cat_title = isset($config['category_mapping'][$cat->slug]) 
			? $config['category_mapping'][$cat->slug]
			: $cat->name;
		
		$breadcrumbs[] = array(
			'title' => $cat_title,
			'url' => '',
			'current' => true
		);
		
	} elseif (is_page()) {
		$page_slug = get_post_field('post_name', get_the_ID());
		
		// ページ階層を確認
		if (isset($config['page_hierarchy'][$page_slug])) {
			$parent_slug = $config['page_hierarchy'][$page_slug];
			$parent_page = get_page_by_path($parent_slug);
			
			if ($parent_page) {
				$breadcrumbs[] = array(
					'title' => $parent_page->post_title,
					'url' => get_permalink($parent_page),
					'current' => false
				);
			}
		}
		
		$breadcrumbs[] = array(
			'title' => get_the_title() ?: '固定ページ',
			'url' => '',
			'current' => true
		);
		
	} elseif (is_404()) {
		$breadcrumbs[] = array(
			'title' => '404 Page Not Found',
			'url' => '',
			'current' => true,
			'class' => 'current-item-404'
		);
	}

	return $breadcrumbs;
}

/**
 * カスタムパンくずリストを出力する関数（表示担当）
 * エラーハンドリング強化版
 */
function custom_breadcrumb()
{
	try {
		// 配列生成ロジックを新しい関数に委譲
		$breadcrumbs = custom_get_breadcrumb_items();

		// パンくずリストの中身のみを出力（Schema.org対応）
		custom_breadcrumb_output($breadcrumbs);
	} catch (Exception $e) {
		// フォールバック：最低限のパンくず表示
		custom_breadcrumb_fallback();
	}
}

/**
 * エラー時のフォールバック関数
 */
function custom_breadcrumb_fallback()
{
	echo '<span class="breadcrumb-fallback">';
	echo '<a href="' . esc_url(home_url('/')) . '">';
	echo '<span>TOP</span>';
	echo '</a>';
	echo '<span class="breadcrumb-separator"> &gt; </span>';
	echo '<span class="current-item">現在のページ</span>';
	echo '</span>';
}

/**
 * パンくずリストのHTML出力（既存のdivラッパー内で使用）
 */
function custom_breadcrumb_output($breadcrumbs)
{
	if (empty($breadcrumbs)) {
		custom_breadcrumb_fallback();
		return;
	}

	// マイクロデータは削除し、純粋なHTMLとして出力
	// JSON-LD形式の構造化データは func-structured-data.php で別途出力

	$position = 1;
	foreach ($breadcrumbs as $index => $breadcrumb) {
		// 区切り文字（最初以外）
		if ($index > 0) {
			echo '<span class="breadcrumb-separator"> &gt; </span>';
		}

		// パンくず項目
		if ($breadcrumb['current'] || empty($breadcrumb['url'])) {
			// 現在ページまたはリンクなし
			$class = isset($breadcrumb['class']) ? $breadcrumb['class'] : 'current-item';
			echo '<span class="' . esc_attr($class) . '">' . esc_html($breadcrumb['title']) . '</span>';
		} else {
			// リンクあり
			echo '<a href="' . esc_url($breadcrumb['url']) . '">';
			echo '<span>' . esc_html($breadcrumb['title']) . '</span>';
			echo '</a>';
		}

		$position++;
	}
}

/**
 * 使用方法とカスタマイズ方法:
 *
 * 【基本的な使用方法】
 * <div class="your-custom-breadcrumb-class">
 *     <?php custom_breadcrumb(); ?>
 * </div>
 *
 * 【カスタマイズ方法】
 * functions.phpに以下のような設定を追加：
 *
 * function my_breadcrumb_config($config) {
 *     $config['post_type_labels']['post'] = 'ニュース一覧';
 *     $config['custom_urls']['news'] = home_url('/custom-news/');
 *     $config['category_mapping']['blog'] = 'ブログ記事';
 *     $config['page_hierarchy']['child-page'] = 'parent-page';
 *     return $config;
 * }
 * add_filter('breadcrumb_config', 'my_breadcrumb_config');
 */