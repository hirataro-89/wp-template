<?php

/**
 * 高松産業案件用 自作パンくずリストシステム
 * Breadcrumb NavXT プラグインの代替として作成
 *
 * 特徴:
 * - プラグイン不要
 * - 高速動作
 * - 完全カスタマイズ可能
 * - エラーハンドリング強化
 * - ホットニュース対応
 */

// 定数定義
define('BREADCRUMB_ENABLE_DEBUG', false);

// パンくず用のURL設定（環境に依存しない方法）
function get_breadcrumb_urls()
{
	return array(
		'news' => home_url('/news/'),
		'hotnews' => home_url('/hotnews/'),
		'business' => home_url('/business/'),
		'recruit' => home_url('/recruit/'),
		'requirements' => home_url('/requirements/')
	);
}

// 採用系ページの共通パンくず生成
function get_recruit_breadcrumbs($current_title = null)
{
	$urls = get_breadcrumb_urls();
	$breadcrumbs = array();

	$breadcrumbs[] = array(
		'title' => '採用情報',
		'url' => $urls['recruit'],
		'current' => false
	);
	$breadcrumbs[] = array(
		'title' => '募集要項',
		'url' => $urls['requirements'],
		'current' => false
	);

	if ($current_title) {
		$breadcrumbs[] = array(
			'title' => $current_title,
			'url' => '',
			'current' => true
		);
	}

	return $breadcrumbs;
}

/**
 * パンくずリストの構造化データ（JSON-LD）を生成
 */
function get_custom_breadcrumb_structured_data()
{
	try {
		$breadcrumbs = array();
		$urls = get_breadcrumb_urls();

		// ホーム
		$breadcrumbs[] = array(
			'title' => 'TOP',
			'url' => home_url('/'),
			'current' => false
		);

		// 既存のロジックと同じ処理（DRYの原則に従い、別途共通化が望ましい）
		if (is_front_page()) {
			// トップページの場合は構造化データ不要
			return array();
		} elseif (is_post_type_archive()) {
			$post_type = get_post_type();
			$post_type_object = get_post_type_object($post_type);

			if ($post_type === 'hotnews') {
				$breadcrumbs[] = array(
					'title' => '高産ホットニュース',
					'url' => '',
					'current' => true
				);
			} else {
				$breadcrumbs[] = array(
					'title' => $post_type_object ? $post_type_object->labels->name : 'アーカイブ',
					'url' => '',
					'current' => true
				);
			}
		} elseif (is_single()) {
			$post_type = get_post_type();

			if ($post_type === 'post') {
				$breadcrumbs[] = array(
					'title' => 'お知らせ一覧',
					'url' => $urls['news'],
					'current' => false
				);
				$breadcrumbs[] = array(
					'title' => get_the_title() ?: '投稿詳細',
					'url' => '',
					'current' => true
				);
			} elseif ($post_type === 'hotnews') {
				$breadcrumbs[] = array(
					'title' => '高産ホットニュース',
					'url' => $urls['hotnews'],
					'current' => false
				);
				$breadcrumbs[] = array(
					'title' => get_the_title() ?: 'ホットニュース詳細',
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
			if ($cat && $cat->slug === 'news') {
				$breadcrumbs[] = array(
					'title' => 'お知らせ一覧',
					'url' => '',
					'current' => true
				);
			}
		} elseif (is_page()) {
			$page_slug = get_post_field('post_name', get_the_ID());

			switch ($page_slug) {
				case 'maker':
					$breadcrumbs[] = array(
						'title' => '事業案内',
						'url' => $urls['business'],
						'current' => false
					);
					$breadcrumbs[] = array(
						'title' => get_the_title() ?: 'メーカー情報',
						'url' => '',
						'current' => true
					);
					break;

				case 'requirements':
					$breadcrumbs[] = array(
						'title' => '採用情報',
						'url' => $urls['recruit'],
						'current' => false
					);
					$breadcrumbs[] = array(
						'title' => get_the_title() ?: '募集要項',
						'url' => '',
						'current' => true
					);
					break;

				case 'entry':
				case 'entry-confirm':
					$breadcrumbs = array_merge($breadcrumbs, get_recruit_breadcrumbs('エントリー'));
					break;

				case 'entry-thanks':
					$breadcrumbs = array_merge($breadcrumbs, get_recruit_breadcrumbs('採用募集の応募完了'));
					break;

				case 'confirm':
					$breadcrumbs[] = array(
						'title' => 'お問い合わせ',
						'url' => '',
						'current' => true
					);
					break;

				case 'thanks':
					$breadcrumbs[] = array(
						'title' => 'お問い合わせ',
						'url' => '',
						'current' => true
					);
					break;

				default:
					$breadcrumbs[] = array(
						'title' => get_the_title() ?: '固定ページ',
						'url' => '',
						'current' => true
					);
					break;
			}
		} elseif (is_404()) {
			$breadcrumbs[] = array(
				'title' => '404 Page Not Found',
				'url' => '',
				'current' => true,
				'class' => 'current-item-404'
			);
		}

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

		if ($post_type === 'hotnews') {
			$breadcrumbs[] = array('title' => '高産ホットニュース', 'url' => '', 'current' => true);
		} else {
			$breadcrumbs[] = array('title' => $post_type_object ? $post_type_object->labels->name : 'アーカイブ', 'url' => '', 'current' => true);
		}
	} elseif (is_single()) {
		$post_type = get_post_type();
		if ($post_type === 'post') {
			$breadcrumbs[] = array('title' => 'お知らせ一覧', 'url' => $urls['news'], 'current' => false);
			$breadcrumbs[] = array('title' => get_the_title() ?: '投稿詳細', 'url' => '', 'current' => true);
		} elseif ($post_type === 'hotnews') {
			$breadcrumbs[] = array('title' => '高産ホットニュース', 'url' => $urls['hotnews'], 'current' => false);
			$breadcrumbs[] = array('title' => get_the_title() ?: 'ホットニュース詳細', 'url' => '', 'current' => true);
		} else {
			$post_type_object = get_post_type_object($post_type);
			$archive_link = get_post_type_archive_link($post_type);
			if ($archive_link && $post_type_object) {
				$breadcrumbs[] = array('title' => $post_type_object->labels->name ?: 'アーカイブ', 'url' => $archive_link, 'current' => false);
			}
			$breadcrumbs[] = array('title' => get_the_title() ?: '詳細ページ', 'url' => '', 'current' => true);
		}
	} elseif (is_category()) {
		$cat = get_queried_object();
		if ($cat && $cat->slug === 'news') {
			$breadcrumbs[] = array('title' => 'お知らせ一覧', 'url' => '', 'current' => true);
		}
	} elseif (is_page()) {
		$page_slug = get_post_field('post_name', get_the_ID());
		switch ($page_slug) {
			case 'maker':
				$breadcrumbs[] = array('title' => '事業案内', 'url' => $urls['business'], 'current' => false);
				$breadcrumbs[] = array('title' => get_the_title() ?: 'メーカー情報', 'url' => '', 'current' => true);
				break;
			case 'requirements':
				$breadcrumbs[] = array('title' => '採用情報', 'url' => $urls['recruit'], 'current' => false);
				$breadcrumbs[] = array('title' => get_the_title() ?: '募集要項', 'url' => '', 'current' => true);
				break;
			case 'entry':
			case 'entry-confirm':
				$breadcrumbs = array_merge($breadcrumbs, get_recruit_breadcrumbs('エントリー'));
				break;
			case 'entry-thanks':
				$breadcrumbs = array_merge($breadcrumbs, get_recruit_breadcrumbs('採用募集の応募完了'));
				break;
			case 'confirm':
			case 'thanks':
				$breadcrumbs[] = array('title' => 'お問い合わせ', 'url' => '', 'current' => true);
				break;
			case 'faq':
				$breadcrumbs[] = array('title' => 'よくあるご質問', 'url' => '', 'current' => true);
				break;
			default:
				$breadcrumbs[] = array('title' => get_the_title() ?: '固定ページ', 'url' => '', 'current' => true);
				break;
		}
	} elseif (is_404()) {
		$breadcrumbs[] = array('title' => '404 Page Not Found', 'url' => '', 'current' => true, 'class' => 'current-item-404');
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
	echo '<span> &gt; </span>';
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
 * 使用方法:
 *
 * 既存のHTMLはそのまま残して、bcn_display()だけを置き換え
 *
 * 【変更前】
 * <div class="your-custom-breadcrumb-class">
 *     <?php if(function_exists('bcn_display')) { bcn_display(); } ?>
 * </div>
 *
 * 【変更後】
 * <div class="your-custom-breadcrumb-class">
 *     <?php custom_breadcrumb(); ?>
 * </div>
 */
