<?php

/**
 * 投稿タイプ・カテゴリーごとの投稿表示件数の制御
 *
 * このファイルでは、WordPressのカテゴリーページやカスタム投稿タイプのアーカイブページで
 * 表示する投稿数を個別に設定できるようにしています。
 *
 * 使用例:
 * フィルターを使って以下のように設定可能
 * add_filter('custom_posts_per_page_settings', function($settings) {
 *     $settings['category']['news'] = 5;        // newsカテゴリーは5件表示
 *     $settings['post_type']['product'] = 12;   // productカスタム投稿タイプは12件表示
 *     return $settings;
 * });
 */

/**
 * 投稿タイプ・カテゴリーごとの投稿表示件数を設定する関数
 *
 * @param WP_Query $query WordPressのクエリオブジェクト
 * @return void
 */
function set_posts_per_page($query)
{
    // 管理画面ではなく、メインクエリの場合のみ実行
    // これにより、フロントエンドの表示のみに影響し、管理画面には影響しない
    if (!is_admin() && $query->is_main_query()) {

        /**
         * 投稿タイプ・カテゴリーごとの表示件数を定義
         *
         * フィルター 'custom_posts_per_page_settings' を使用することで、
         * 他のファイルやプラグインから設定を追加・変更可能
         *
         * 設定形式:
         * 'category' => [
         *     'カテゴリースラッグ' => 表示件数
         * ],
         * 'post_type' => [
         *     'カスタム投稿タイプ名' => 表示件数
         * ]
         */
        $posts_per_page_settings = apply_filters('custom_posts_per_page_settings', [
            // カテゴリー別の設定
            'category' => [
                // デフォルトは空
                // フィルターを使って以下のように追加可能:
                // 'news' => 5,     // newsカテゴリーは5件表示
                // 'blog' => 10,    // blogカテゴリーは10件表示
            ],
            // カスタム投稿タイプ別の設定
            'post_type' => [
                // デフォルトは空
                // フィルターを使って以下のように追加可能:
                // 'product' => 12,  // productカスタム投稿タイプは12件表示
                // 'service' => 8,   // serviceカスタム投稿タイプは8件表示
            ]
        ]);

        // カテゴリーページの場合の処理
        if (is_category()) {
            // 現在のカテゴリースラッグを取得
            $current_category = get_query_var('category_name');

            // 設定にそのカテゴリーの表示件数が定義されている場合
            if (isset($posts_per_page_settings['category'][$current_category])) {
                // クエリの表示件数を設定値に変更
                $query->set('posts_per_page', $posts_per_page_settings['category'][$current_category]);
            }
        }

        // カスタム投稿タイプのアーカイブページの場合の処理
        if (is_post_type_archive()) {
            // 現在のカスタム投稿タイプ名を取得
            $current_post_type = get_query_var('post_type');

            // カスタム投稿タイプが存在し、設定にその投稿タイプの表示件数が定義されている場合
            if ($current_post_type && isset($posts_per_page_settings['post_type'][$current_post_type])) {
                // クエリの表示件数を設定値に変更
                $query->set('posts_per_page', $posts_per_page_settings['post_type'][$current_post_type]);
            }
        }
    }
}
add_action('pre_get_posts', 'set_posts_per_page');
