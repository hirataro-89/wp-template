<?php

/**
 * func-url
 * パスを定義
 * 記述例
 *
 * <img src="<?php img_path('/common/logo.svg'); ?>" alt="">
 * 出力例:
 * <img src="https://xxx.com/common/logo.svg" alt="">
 *
 * <a href="<?php page_path(); ?>"></a>
 * 出力例:
 * <a href="https://xxx.com/"></a>
 *
 * <a href="<?php page_path('news'); ?>"></a>
 * 出力例:
 * <a href="https://xxx.com/news/"></a>
 *
 * <a href="<?php page_path('#works'); ?>"></a>
 * 出力例:
 * <a href="https://xxx.com/#works"></a>
 *
 * <a href="<?php page_path('sample.pdf'); ?>"></a>
 * 出力例:
 * <a href="https://xxx.com/sample.pdf"></a>
 */


/* テンプレートパスを返す */
function temp_path($file = "")
{
  echo esc_url(get_theme_file_uri($file));
}
/* assetsパスを返す */
function assets_path($file = "")
{
  echo esc_url(get_theme_file_uri('/assets' . $file));
}
/* 画像パスを返す */
function img_path($file = "")
{
  echo esc_url(get_theme_file_uri('/images' . $file));
}
/* mediaフォルダへのURL */
function uploads_path()
{
  echo esc_url(wp_upload_dir()['baseurl']);
}

/* ホームURLのパスを返す */
function page_path($page = "")
{
  // スラッシュが不要な場合は処理しない
  if (strpos($page, '#') === false && strpos($page, '?') === false && !preg_match('/\.[a-zA-Z0-9]+$/', $page)) {
    $page .= '/';
  }
  echo esc_url(home_url($page));
}

/* カテゴリーリンクを返す（echoではなくreturn） */
function category_path($category_slug = "")
{
  if (empty($category_slug)) {
    return '#'; // 空なら # を返すようにして安全対策
  }
  return esc_url(get_category_link(get_cat_ID($category_slug)));
}
