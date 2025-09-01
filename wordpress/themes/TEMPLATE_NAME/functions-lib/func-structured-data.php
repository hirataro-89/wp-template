<?php

/**
 * シンプル構造化データ（JSON-LD）システム
 * 必要最小限の構造化データを出力
 */

/**
 * 構造化データの基本設定
 */
function get_simple_structured_data_config() {
	return apply_filters('simple_structured_data_config', array(
		'organization_name' => get_bloginfo('name'),
		'organization_url' => home_url('/'),
		'organization_logo' => get_template_directory_uri() . '/images/logo.png',
		'enable_breadcrumbs' => true
	));
}

/**
 * 構造化データを出力
 */
function add_structured_data()
{
	$structured_data = array();
	$config = get_structured_data_config();

	// Organization（組織情報）- 全ページ共通
	if (!empty($config['organization']['name'])) {
		$organization = array(
			'@context' => 'https://schema.org',
			'@type' => 'Organization',
			'name' => $config['organization']['name'],
			'url' => $config['organization']['url']
		);
		
		if (!empty($config['organization']['logo'])) {
			$organization['logo'] = array(
				'@type' => 'ImageObject',
				'url' => $config['organization']['logo']
			);
		}
		
		$structured_data[] = $organization;
	}

	// WebSite（サイト情報）- トップページのみ
	if (is_front_page() && !empty($config['website']['name'])) {
		$website = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebSite',
			'name' => $config['website']['name'],
			'url' => home_url('/'),
			'description' => $config['website']['description'] ?: '',
			'publisher' => array(
				'@type' => 'Organization',
				'name' => $config['organization']['name']
			)
		);
		$structured_data[] = $website;
	}

	// Article（記事）- 投稿・固定ページ
	if (is_singular() && $config['enable_articles']) {
		$article_data = generate_article_structured_data($config);
		if ($article_data) {
			$structured_data[] = $article_data;
		}
	}

	// FAQPage（よくある質問）
	if (!empty($config['faq_pages']) && !empty($config['faq_items'])) {
		$page_slug = get_post_field('post_name', get_the_ID());
		
		if (in_array($page_slug, $config['faq_pages'])) {
			$faq_data = generate_faq_structured_data($config['faq_items']);
			if ($faq_data) {
				$structured_data[] = $faq_data;
			}
		}
	}

	// JobPosting（求人情報）
	if (!empty($config['job_pages']) && !empty($config['job_postings'])) {
		$page_slug = get_post_field('post_name', get_the_ID());
		
		if (in_array($page_slug, $config['job_pages'])) {
			foreach ($config['job_postings'] as $job) {
				$job_data = generate_job_posting_structured_data($job, $config);
				if ($job_data) {
					$structured_data[] = $job_data;
				}
			}
		}
	}

	// BreadcrumbList（パンくずリスト）- トップページ以外
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

	// JSON-LD形式で出力
	if (!empty($structured_data)) {
		echo '<script type="application/ld+json">' . PHP_EOL;
		echo json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
		echo '</script>' . PHP_EOL;
	}
}
add_action('wp_head', 'add_structured_data', 20);

/**
 * 記事の構造化データを生成
 */
function generate_article_structured_data($config)
{
	if (!is_singular()) {
		return null;
	}

	$post = get_post();
	if (!$post) {
		return null;
	}

	$article = array(
		'@context' => 'https://schema.org',
		'@type' => is_single() ? 'Article' : 'WebPage',
		'headline' => get_the_title(),
		'url' => get_permalink(),
		'datePublished' => get_the_date('c'),
		'dateModified' => get_the_modified_date('c'),
		'author' => array(
			'@type' => 'Organization',
			'name' => $config['organization']['name']
		),
		'publisher' => array(
			'@type' => 'Organization',
			'name' => $config['organization']['name']
		)
	);

	// 抜粋またはコンテンツの最初の部分を説明として追加
	$description = get_the_excerpt();
	if (empty($description)) {
		$description = wp_trim_words(strip_tags(get_the_content()), 30);
	}
	if ($description) {
		$article['description'] = $description;
	}

	// アイキャッチ画像
	if (has_post_thumbnail()) {
		$thumbnail_id = get_post_thumbnail_id();
		$thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'full');
		
		if ($thumbnail_url) {
			$article['image'] = array(
				'@type' => 'ImageObject',
				'url' => $thumbnail_url
			);
		}
	}

	return $article;
}

/**
 * FAQ構造化データを生成
 */
function generate_faq_structured_data($faq_items)
{
	if (empty($faq_items)) {
		return null;
	}

	$mainEntity = array();
	foreach ($faq_items as $item) {
		if (empty($item['question']) || empty($item['answer'])) {
			continue;
		}

		$mainEntity[] = array(
			'@type' => 'Question',
			'name' => $item['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text' => $item['answer']
			)
		);
	}

	if (empty($mainEntity)) {
		return null;
	}

	return array(
		'@context' => 'https://schema.org',
		'@type' => 'FAQPage',
		'mainEntity' => $mainEntity
	);
}

/**
 * JobPosting構造化データを生成
 */
function generate_job_posting_structured_data($job, $config)
{
	if (empty($job['title'])) {
		return null;
	}

	$job_posting = array(
		'@context' => 'https://schema.org',
		'@type' => 'JobPosting',
		'title' => $job['title'],
		'datePosted' => isset($job['datePosted']) ? $job['datePosted'] : date('Y-m-d'),
		'validThrough' => isset($job['validThrough']) ? $job['validThrough'] : date('Y-m-d', strtotime('+6 months')),
		'employmentType' => isset($job['employmentType']) ? $job['employmentType'] : 'FULL_TIME',
		'hiringOrganization' => array(
			'@type' => 'Organization',
			'name' => $config['organization']['name'],
			'url' => $config['organization']['url']
		)
	);

	// 職務内容
	if (!empty($job['description'])) {
		$job_posting['description'] = $job['description'];
	}

	// 勤務地
	if (!empty($job['location'])) {
		$job_posting['jobLocation'] = array(
			'@type' => 'Place',
			'address' => array(
				'@type' => 'PostalAddress',
				'addressLocality' => $job['location']['city'] ?? '',
				'addressRegion' => $job['location']['prefecture'] ?? '',
				'postalCode' => $job['location']['postalCode'] ?? '',
				'streetAddress' => $job['location']['street'] ?? '',
				'addressCountry' => 'JP'
			)
		);
	}

	// 給与
	if (!empty($job['salary'])) {
		$job_posting['baseSalary'] = array(
			'@type' => 'MonetaryAmount',
			'currency' => 'JPY',
			'value' => array(
				'@type' => 'QuantitativeValue',
				'minValue' => $job['salary']['min'] ?? 0,
				'maxValue' => $job['salary']['max'] ?? 0,
				'unitText' => $job['salary']['unit'] ?? 'MONTH'
			)
		);
	}

	// 勤務時間
	if (!empty($job['workHours'])) {
		$job_posting['workHours'] = $job['workHours'];
	}

	// 福利厚生
	if (!empty($job['benefits'])) {
		$job_posting['benefits'] = $job['benefits'];
	}

	return $job_posting;
}

/**
 * 使用方法とカスタマイズ例:
 *
 * functions.phpに以下のような設定を追加：
 *
 * function my_structured_data_config($config) {
 *     // 組織情報のカスタマイズ
 *     $config['organization']['name'] = '株式会社サンプル';
 *     $config['organization']['logo'] = get_template_directory_uri() . '/images/logo.svg';
 *     
 *     // FAQページ設定
 *     $config['faq_pages'] = array('faq', 'contact');
 *     $config['faq_items'] = array(
 *         array(
 *             'question' => 'よくある質問1',
 *             'answer' => 'これが答えです。'
 *         ),
 *         array(
 *             'question' => 'よくある質問2', 
 *             'answer' => 'こちらも答えです。'
 *         )
 *     );
 *     
 *     // 求人情報設定
 *     $config['job_pages'] = array('recruit');
 *     $config['job_postings'] = array(
 *         array(
 *             'title' => '営業職',
 *             'description' => '新規顧客開拓を担当',
 *             'location' => array(
 *                 'city' => '東京都',
 *                 'prefecture' => '東京都',
 *                 'postalCode' => '100-0001',
 *                 'street' => '千代田区1-1-1'
 *             ),
 *             'salary' => array(
 *                 'min' => 250000,
 *                 'max' => 400000,
 *                 'unit' => 'MONTH'
 *             ),
 *             'workHours' => '9:00～18:00',
 *             'benefits' => '社会保険完備、交通費支給'
 *         )
 *     );
 *     
 *     return $config;
 * }
 * add_filter('structured_data_config', 'my_structured_data_config');
 */