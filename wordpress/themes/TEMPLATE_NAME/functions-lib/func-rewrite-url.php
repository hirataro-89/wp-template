<?php
// カスタムリライトルールを追加
function add_custom_rewrite_rules()
{
    // /news/ → お知らせ一覧
    add_rewrite_rule(
        '^news/?$',
        'index.php?category_name=news',
        'top'
    );

    // /news/page/2/ → お知らせ一覧のページネーション
    add_rewrite_rule(
        '^news/page/([0-9]+)/?$',
        'index.php?category_name=news&paged=$matches[1]',
        'top'
    );

    // フィルター機能用のクエリ変数を追加
    add_rewrite_tag('%filter%', '([^&]+)');

    // /hotnews/ → ホットニュース一覧（カスタム投稿）
    add_rewrite_rule(
        '^hotnews/?$',
        'index.php?post_type=hotnews',
        'top'
    );

    // /hotnews/page/2/ → ホットニュースのページネーション
    add_rewrite_rule(
        '^hotnews/page/([0-9]+)/?$',
        'index.php?post_type=hotnews&paged=$matches[1]',
        'top'
    );
}
add_action('init', 'add_custom_rewrite_rules');

// テーマ有効化時にリライトルールをフラッシュ
function flush_rewrite_rules_on_theme_activation()
{
    add_custom_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_on_theme_activation');

// 必要に応じて手動でフラッシュする関数（デバッグ用）
function manual_flush_rewrite_rules()
{
    if (current_user_can('administrator')) {
        flush_rewrite_rules();
    }
}

// 使い方: URLに ?flush_rules=1 を追加してアクセス
if (isset($_GET['flush_rules']) && $_GET['flush_rules'] == '1') {
    add_action('init', 'manual_flush_rewrite_rules');
}

// カテゴリーページでのクエリ修正
function modify_news_category_query($query)
{
    // 管理画面やメインクエリ以外は除外
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // newsカテゴリーページの場合
    if (is_category('news')) {
        // レスポンシブ対応の投稿件数設定
        $posts_per_page = wp_is_mobile() ? 3 : 4;
        $query->set('posts_per_page', $posts_per_page);

        // フィルターが指定されている場合
        $current_filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : '';
        if (!empty($current_filter)) {
            $query->set('tax_query', array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => 'news'
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
add_action('pre_get_posts', 'modify_news_category_query');

// ホットニュースアーカイブページでのクエリ修正
function modify_hotnews_archive_query($query)
{
    // 管理画面やメインクエリ以外は除外
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // hotnewsアーカイブページの場合
    if (is_post_type_archive('hotnews')) {
        // レスポンシブ対応の投稿件数設定
        $posts_per_page = wp_is_mobile() ? 3 : 4;
        $query->set('posts_per_page', $posts_per_page);
    }
}
add_action('pre_get_posts', 'modify_hotnews_archive_query');
