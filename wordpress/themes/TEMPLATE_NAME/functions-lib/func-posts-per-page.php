<?php

/**
 * 投稿タイプ・カテゴリーごとの投稿表示件数の制御
 */

// 投稿タイプ・カテゴリーごとの投稿表示件数を設定
function set_posts_per_page($query)
{
    if (!is_admin() && $query->is_main_query()) {
        // 投稿タイプ・カテゴリーごとの表示件数を定義
        $posts_per_page_settings = [
            // カテゴリー別の設定
            'category' => [
                'news' => 4,
            ],
            // カスタム投稿タイプ別の設定
            // 'post_type' => [
            //     'case' => 9,
            //     'service' => 6
            // ]
        ];

        // カテゴリーの場合
        if (is_category()) {
            $current_category = get_query_var('category_name');
            if (isset($posts_per_page_settings['category'][$current_category])) {
                $query->set('posts_per_page', $posts_per_page_settings['category'][$current_category]);
            }
        }

        // カスタム投稿タイプの場合
        // $current_post_type = get_post_type();
        // if ($current_post_type && isset($posts_per_page_settings['post_type'][$current_post_type])) {
        //     $query->set('posts_per_page', $posts_per_page_settings['post_type'][$current_post_type]);
        // }
    }
}
add_action('pre_get_posts', 'set_posts_per_page');
