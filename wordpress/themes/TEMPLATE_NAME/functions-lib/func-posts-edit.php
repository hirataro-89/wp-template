<?php

/**
 * *********************************************************
 *  メインクエリの投稿記事表示件数を切り替える
 *  管理画面の表示設定と切り替えしたい場合使用します
 * *********************************************************
 */
function custom_main_query($query)
{
  // 管理画面、メインクエリ以外には適用しない
  if (is_admin() || !$query->is_main_query()) {
    return;
  }
  if (wp_is_mobile()) {
    $query->set('posts_per_page', '4'); //SP用投稿数設定
  }
}
add_action('pre_get_posts', 'custom_main_query');
