<?php

/**
 * 📄 投稿表示件数の制御
 *
 * カテゴリーやカスタム投稿タイプごとに表示件数を個別設定できます。
 *
 * 📝 使用例:
 * add_filter('posts_per_page_settings', function($settings) {
 *     $settings['category']['news'] = 5;        // newsカテゴリーは5件
 *     $settings['post_type']['product'] = 12;   // productは12件
 *     return $settings;
 * });
 */

/**
 * 投稿表示件数を設定
 *
 * @param WP_Query $query WordPressクエリオブジェクト
 */
function set_posts_per_page($query)
{
    // フロントエンドのメインクエリのみ対象
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // 設定を取得（フィルターでカスタマイズ可能）
    $settings = apply_filters('posts_per_page_settings', [
        'category' => [
            // 例: 'news' => 5,
        ],
        'post_type' => [
            // 例: 'product' => 12,
        ]
    ]);

    // カテゴリーページの処理
    if (is_category()) {
        // 現在表示中のカテゴリースラッグを取得
        $category = get_query_var('category_name');
        // そのカテゴリーに専用設定があるかチェック
        if (isset($settings['category'][$category])) {
            // 専用設定の表示件数を適用
            $query->set('posts_per_page', $settings['category'][$category]);
        }
    }

    // カスタム投稿タイプアーカイブの処理
    if (is_post_type_archive()) {
        // 現在表示中の投稿タイプ名を取得
        $post_type = get_query_var('post_type');
        // 投稿タイプが存在し、専用設定があるかチェック
        if ($post_type && isset($settings['post_type'][$post_type])) {
            // 専用設定の表示件数を適用
            $query->set('posts_per_page', $settings['post_type'][$post_type]);
        }
    }
}

add_action('pre_get_posts', 'set_posts_per_page');
