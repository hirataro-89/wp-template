<?php

/**
 * シンプル構造化データ（JSON-LD）システム
 * 必要最小限の構造化データを出力
 */

/**
 * 構造化データの基本設定
 * 必要に応じてここの値を直接変更してください
 */
function get_simple_structured_data_config()
{
	return apply_filters('simple_structured_data_config', array(
		// 組織名（サイト名）
		'organization_name' => get_bloginfo('name'),
		// 組織のURL（サイトURL）
		'organization_url' => home_url('/'),
		// 組織のロゴ画像URL（適宜パスを変更してください）
		'organization_logo' => get_template_directory_uri() . '/images/logo.png',
		// パンくずリスト構造化データの出力フラグ
		'enable_breadcrumbs' => true
	));
}

/**
 * 構造化データを出力
 */
function add_simple_structured_data()
{
	$structured_data = array();
	$config = get_simple_structured_data_config();

	// Organization（組織情報）- 全ページ共通
	// SEO効果：サイト運営者の信頼性向上
	$organization = array(
		'@context' => 'https://schema.org',
		'@type' => 'Organization',
		'name' => $config['organization_name'],
		'url' => $config['organization_url']
	);

	// ロゴがある場合は追加
	if (!empty($config['organization_logo'])) {
		$organization['logo'] = array(
			'@type' => 'ImageObject',
			'url' => $config['organization_logo']
		);
	}

	$structured_data[] = $organization;

	// WebSite（サイト情報）- トップページのみ
	// SEO効果：サイト全体の概要を検索エンジンに伝達
	if (is_front_page()) {
		$website = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebSite',
			'name' => get_bloginfo('name'),
			'url' => home_url('/'),
			'description' => get_bloginfo('description'),
			'publisher' => array(
				'@type' => 'Organization',
				'name' => $config['organization_name']
			)
		);
		$structured_data[] = $website;
	}

	// Article/WebPage（記事・ページ）- 個別ページ
	// SEO効果：記事やページの詳細情報を検索エンジンに提供
	if (is_singular()) {
		$page_data = add_page_structured_data($config);
		if ($page_data) {
			$structured_data[] = $page_data;
		}
	}

	// BreadcrumbList（パンくずリスト）- トップページ以外
	// SEO効果：サイト構造の理解とナビゲーション向上
	if (!is_front_page() && $config['enable_breadcrumbs'] && function_exists('get_custom_breadcrumb_structured_data')) {
		$breadcrumb_data = get_custom_breadcrumb_structured_data();
		if (!empty($breadcrumb_data)) {
			$breadcrumb_list = array(
				'@context' => 'https://schema.org',
				'@type' => 'BreadcrumbList',
				'itemListElement' => $breadcrumb_data
			);
			$structured_data[] = $breadcrumb_list;
		}
	}

	// JSON-LD形式で出力（headタグ内に挿入）
	// Google推奨のJSON-LD形式で構造化データを出力
	if (!empty($structured_data)) {
		echo '<script type="application/ld+json">' . PHP_EOL;
		echo json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
		echo '</script>' . PHP_EOL;
	}
}
add_action('wp_head', 'add_simple_structured_data', 20);

/**
 * ページの構造化データを生成
 */
function add_page_structured_data($config)
{
	$post = get_post();
	if (!$post) {
		return null;
	}

	$page_data = array(
		'@context' => 'https://schema.org',
		'@type' => is_single() ? 'Article' : 'WebPage',
		'headline' => get_the_title(),
		'url' => get_permalink(),
		'datePublished' => get_the_date('c'),
		'dateModified' => get_the_modified_date('c'),
		'author' => array(
			'@type' => 'Organization',
			'name' => $config['organization_name']
		),
		'publisher' => array(
			'@type' => 'Organization',
			'name' => $config['organization_name']
		)
	);

	// 抜粋または概要を説明として追加
	// SEO効果：検索結果のスニペット表示改善
	$description = get_the_excerpt();
	if (empty($description)) {
		$description = wp_trim_words(strip_tags(get_the_content()), 30);
	}
	if ($description) {
		$page_data['description'] = $description;
	}

	// アイキャッチ画像（OGP画像としても使用）
	// SEO効果：検索結果やSNSシェア時の視覚的訴求力向上
	if (has_post_thumbnail()) {
		$thumbnail_url = wp_get_attachment_image_url(get_post_thumbnail_id(), 'full');
		if ($thumbnail_url) {
			$page_data['image'] = array(
				'@type' => 'ImageObject',
				'url' => $thumbnail_url
			);
		}
	}

	return $page_data;
}

/**
 * ========================================
 * カスタマイズ方法（このファイル内で完結）
 * ========================================
 *
 * 1. 基本設定の変更
 *    get_simple_structured_data_config() 関数内の値を直接編集
 *    例：organization_name を '株式会社サンプル' に変更
 *        organization_logo を '/images/logo.svg' に変更
 *
 * 2. フィルターを使用したカスタマイズ
 *    このファイルの最下部に以下のような関数を追加：
 *
 * function custom_structured_data_config($config) {
 *     $config['organization_name'] = '株式会社サンプル';
 *     $config['organization_logo'] = get_template_directory_uri() . '/images/logo.svg';
 *     $config['enable_breadcrumbs'] = false; // パンくず無効化
 *     return $config;
 * }
 * add_filter('simple_structured_data_config', 'custom_structured_data_config');
 *
 * ========================================
 * 出力される構造化データの種類
 * ========================================
 * - Organization: 組織情報（全ページ）
 * - WebSite: サイト情報（トップページのみ）
 * - Article/WebPage: 記事・ページ情報（個別ページ）
 * - BreadcrumbList: パンくずリスト（トップページ以外）
 */
