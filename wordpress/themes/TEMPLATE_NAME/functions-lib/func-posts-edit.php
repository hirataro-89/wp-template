<?php

/**
 * メインクエリの投稿記事表示件数を切り替える（汎用化）
 * フィルターで設定可能な構造
 */
function custom_main_query($query)
{
	// 管理画面、メインクエリ以外には適用しない
	if (is_admin() || !$query->is_main_query()) {
		return;
	}

	// 投稿表示件数の設定（フィルターで制御可能）
	$posts_per_page_config = apply_filters('posts_per_page_config', array(
		'mobile' => 4,    // モバイル表示件数
		'desktop' => null // デスクトップ表示件数（nullの場合は管理画面設定を使用）
	));

	if (wp_is_mobile() && !empty($posts_per_page_config['mobile'])) {
		$query->set('posts_per_page', $posts_per_page_config['mobile']);
	} elseif (!wp_is_mobile() && !empty($posts_per_page_config['desktop'])) {
		$query->set('posts_per_page', $posts_per_page_config['desktop']);
	}
}
add_action('pre_get_posts', 'custom_main_query');
