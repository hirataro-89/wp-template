<?php

/**
 * Vite対応のスクリプトとスタイル読み込み処理
 * シンプルで実用的な設計
 */
function add_vite_scripts()
{
	// WordPressの標準jQueryを無効化
	wp_deregister_script('jquery');
	wp_deregister_script('jquery-migrate');
	if (WP_DEBUG) {
		// 開発環境（Vite開発サーバーから読み込み）
		$root = "http://localhost:5173";

		// Vite Client（HMR対応）
		wp_enqueue_script('vite-client', $root . '/@vite/client', array(), null, true);

		// メインCSS（SCSS）
		wp_enqueue_style('theme-styles', $root . '/src/assets/style/style.scss', array(), null, false);

		// メインJS
		wp_enqueue_script('theme-scripts', $root . '/src/assets/js/script.js', array(), null, true);
	} else {
		// 本番環境（ビルド済みファイルから読み込み）
		$root = get_template_directory_uri();
		$assets_dir = get_template_directory() . '/assets';

		// メインCSS（キャッシュバスティング対応）
		$style_path = $root . '/assets/style/style.css';  // ブラウザ用URL
		$style_file = $assets_dir . '/style/style.css';   // サーバー内ファイルパス
		if (file_exists($style_file)) {
			$version = filemtime($style_file);  // 更新日時をバージョン番号に
			wp_enqueue_style('theme-styles', $style_path, array(), $version, false);
		}

		// ビルドで生成された追加CSSファイルを自動読み込み（Splide等）
		$css_files = glob($assets_dir . '/style/*.css');
		foreach ($css_files as $css_file) {
			$filename = basename($css_file, '.css');
			// メインのstyle.cssは既に読み込み済みなのでスキップ
			if ($filename !== 'style') {
				$css_path = $root . '/assets/style/' . basename($css_file);  // ブラウザ用URL
				$version = filemtime($css_file);  // キャッシュバスティング用
				wp_enqueue_style('theme-' . $filename, $css_path, array(), $version, false);
			}
		}

		// メインJS（全てのJSがバンドル済み・キャッシュバスティング対応）
		$script_path = $root . '/assets/js/script.js';   // ブラウザ用URL
		$script_file = $assets_dir . '/js/script.js';    // サーバー内ファイルパス
		if (file_exists($script_file)) {
			$version = filemtime($script_file);  // 更新日時をバージョン番号に
			wp_enqueue_script('theme-scripts', $script_path, array(), $version, true);
		}
	}

	// Google Fonts
	wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&family=Noto+Serif+JP:wght@200..900&display=swap', false);
}

add_action('wp_enqueue_scripts', 'add_vite_scripts');

/**
 * ES Modules対応（type="module"属性を追加）
 */
function add_vite_module_attribute($tag, $handle, $src)
{
	// Vite関連のスクリプトにmodule属性を追加
	$module_handlers = ['theme-scripts', 'vite-client'];

	if (in_array($handle, $module_handlers)) {
		$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
	}

	return $tag;
}
add_filter('script_loader_tag', 'add_vite_module_attribute', 10, 3);

/**
 * Google Fonts最適化（プリコネクト）
 */
function add_font_preconnect($html, $handle)
{
	if ('google-fonts' === $handle) {
		$html = <<<EOT
		<link rel='preconnect' href='https://fonts.googleapis.com'>
		<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
		$html
		EOT;
	}
	return $html;
}
add_filter('style_loader_tag', 'add_font_preconnect', 10, 2);
