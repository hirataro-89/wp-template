<?php
/**
 * func-shortcode
 * ショートコード管理
 * 投稿ページや固定ページ上でwordpressの関数を使用する
 */

 /**
 * 例
 * @example <img src="[home_url]/img/hoge.jpg" alt="hoge">
 */

/* home_urlを返す */
add_shortcode('home_url', 'sc_home_url');

function sc_home_url($atts, $content = null) {
  return home_url();
}

/* テンプレートパスを返す */
add_shortcode('temp_path', 'sc_temp_path');

function sc_temp_path($atts, $content = null) {
  return get_theme_file_uri();
}

/* assetsパスを返す */
add_shortcode('assets_path', 'sc_assets_path');

function sc_assets_path($atts, $content = null) {
  return get_theme_file_uri() . '/assets';
}

/* 画像パスを返す */
/* @example <img src="[img_path]/hoge.jpg" alt="hoge"> */
add_shortcode('img_path', 'sc_img_path');

function sc_img_path($atts, $content = null) {
  return get_theme_file_uri() . '/assets/images';
}

/* mediaフォルダへのURL */
add_shortcode('uploads_path', 'sc_uploads_path');

function sc_uploads_path($atts, $content = null) {
  return wp_upload_dir()['baseurl'];
}
