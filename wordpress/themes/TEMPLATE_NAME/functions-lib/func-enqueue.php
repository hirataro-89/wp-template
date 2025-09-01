<?php

/**
 * Vite対応のスクリプトとスタイル読み込み処理
 * シンプルで実用的な設計
 */
function add_vite_scripts()
{
	if (WP_DEBUG) {
		// 開発環境（Vite開発サーバーから読み込み）
		$root = "http://localhost:5173";

		// Vite Client（HMR対応）
		wp_enqueue_script('vite-client', $root . '/@vite/client', array(), null, true);

		// メインCSS（SCSS）
		wp_enqueue_style('theme-styles', $root . '/src/assets/style/style.scss', array(), null, false);

		// メインJS
		wp_enqueue_script('theme-scripts', $root . '/src/assets/js/script.js', array('jquery'), null, true);
	} else {
		// 本番環境（ビルド済みファイルから読み込み）
		$root = get_template_directory_uri();

		// CSS（バージョン管理付き）
		$style_path = $root . '/assets/style/style.css';
		$style_file = get_template_directory() . '/assets/style/style.css';
		if (file_exists($style_file)) {
			$version = filemtime($style_file);
			wp_enqueue_style('theme-styles', $style_path, array(), $version, false);
		}

		// JS（バージョン管理付き）
		$script_path = $root . '/assets/js/script.js';
		$script_file = get_template_directory() . '/assets/js/script.js';
		if (file_exists($script_file)) {
			$version = filemtime($script_file);
			wp_enqueue_script('theme-scripts', $script_path, array('jquery'), $version, true);
		}
	}

	// Google Fonts
	wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&family=Noto+Serif+JP:wght@200..900&display=swap', false);

	// jQuery（ESM対応版）
	wp_enqueue_script('jquery', 'https://cdn.skypack.dev/jquery@3.7.1', array(), '3.7.1', true);
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
