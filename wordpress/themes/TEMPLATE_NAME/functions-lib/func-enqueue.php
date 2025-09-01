<?php

/**
 * Vite専用のスクリプトとスタイル読み込み処理
 * Viteの特性を活かした最適化された設計
 */

/**
 * Vite用設定を取得する関数
 * プロジェクトごとにカスタマイズ可能
 */
function get_vite_enqueue_config()
{
	$default_config = array(
		// Vite開発サーバー設定
		'vite' => array(
			'host' => 'localhost',
			'port' => 5173,
			'protocol' => 'http',
			'hmr' => true // Hot Module Replacement
		),

		// アセットパス設定（Vite標準構成）
		'asset_paths' => array(
			'css_dir' => '/src/assets/style',
			'js_dir' => '/src/assets/js',
			'main_css' => 'style',
			'main_js' => 'script',
			'build_dir' => '/dist' // ビルド後のディレクトリ
		),

		// フォント設定
		'fonts' => array(
			'google_fonts' => array(
				'url' => 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&family=Noto+Serif+JP:wght@200..900&display=swap',
				'enabled' => true,
				'preconnect' => true // プリコネクト最適化
			),
			'custom_fonts' => array()
		),

		// jQuery設定
		'jquery' => array(
			'enabled' => true,
			'version' => '3.7.1', // 最新安定版
			'source' => 'esm' // 'esm', 'cdn', 'local'
		),

		// Vite対応機能設定
		'features' => array(
			'splide' => false,
			'yubinbango' => array(
				'enabled' => false,
				'pages' => array('contact')
			),
			'form_validation' => false,
			'css_modules' => false, // CSS Modules対応
			'scss_processing' => true // SCSS自動処理
		),

		// ESモジュール対応ライブラリ
		'esm_libraries' => array(),

		// 追加ライブラリ（従来形式も対応）
		'additional_scripts' => array(),
		'additional_styles' => array()
	);

	// フィルターでカスタマイズ可能
	return apply_filters('vite_enqueue_config', $default_config);
}

/**
 * Vite開発環境のアセットを読み込む
 */
function enqueue_vite_dev_assets($config)
{
	$vite = $config['vite'];
	$asset_paths = $config['asset_paths'];

	$dev_server_url = $vite['protocol'] . '://' . $vite['host'] . ':' . $vite['port'];

	// Vite Client（HMR対応）
	wp_enqueue_script('vite-client', $dev_server_url . '/@vite/client', array(), null, true);

	// メインCSS（SCSS）
	$style_path = $dev_server_url . $asset_paths['css_dir'] . '/' . $asset_paths['main_css'] . '.scss';
	wp_enqueue_style('theme-styles', $style_path, array(), null, false);

	// Splide（有効な場合、開発環境）
	if ($config['features']['splide']) {
		$splide_path = $dev_server_url . $asset_paths['css_dir'] . '/splide.scss';
		wp_enqueue_style('splide-styles', $splide_path, array(), null, false);
	}
}

/**
 * Vite本番環境のアセットを読み込む（manifest.json使用）
 */
function enqueue_vite_prod_assets($config)
{
	$root = get_template_directory_uri();
	$build_dir = $config['asset_paths']['build_dir'];
	$manifest_path = get_template_directory() . $build_dir . '/manifest.json';

	// Viteのmanifest.jsonがある場合は使用
	if (file_exists($manifest_path)) {
		$manifest = json_decode(file_get_contents($manifest_path), true);
		enqueue_from_manifest($manifest, $root . $build_dir, $config);
	} else {
		// フォールバック：通常のアセット読み込み
		enqueue_fallback_assets($config);
	}
}

/**
 * Viteのmanifest.jsonからアセットを読み込む
 */
function enqueue_from_manifest($manifest, $build_url, $config)
{
	$asset_paths = $config['asset_paths'];
	$main_js = $asset_paths['main_js'] . '.js';
	$main_css = $asset_paths['main_css'] . '.scss'; // Viteではエントリーポイントは.scss

	// メインJS
	if (isset($manifest[$main_js])) {
		$main_js_file = $manifest[$main_js]['file'];
		$dependencies = $config['jquery']['enabled'] ? array('jquery') : array();
		wp_enqueue_script('theme-scripts', $build_url . '/' . $main_js_file, $dependencies, null, true);
	}

	// メインCSS
	if (isset($manifest[$main_css]) && isset($manifest[$main_css]['css'])) {
		foreach ($manifest[$main_css]['css'] as $css_file) {
			wp_enqueue_style('theme-styles', $build_url . '/' . $css_file, array(), null, false);
		}
	}

	// Splideアセット（manifest内で見つかった場合）
	if ($config['features']['splide']) {
		$splide_key = $asset_paths['css_dir'] . '/splide.scss';
		if (isset($manifest[$splide_key])) {
			if (isset($manifest[$splide_key]['css'])) {
				foreach ($manifest[$splide_key]['css'] as $css_file) {
					wp_enqueue_style('splide-styles', $build_url . '/' . $css_file, array(), null, false);
				}
			}
		}
	}
}

/**
 * フォールバック：通常のアセット読み込み
 */
function enqueue_fallback_assets($config)
{
	$root = get_template_directory_uri();
	$build_dir = $config['asset_paths']['build_dir'];
	$asset_paths = $config['asset_paths'];

	// CSS
	$style_path = $root . $build_dir . '/style.css';
	$style_file = get_template_directory() . $build_dir . '/style.css';

	if (file_exists($style_file)) {
		$version = filemtime($style_file);
		wp_enqueue_style('theme-styles', $style_path, array(), $version, false);
	}

	// JS
	$script_path = $root . $build_dir . '/script.js';
	$script_file = get_template_directory() . $build_dir . '/script.js';

	if (file_exists($script_file)) {
		$version = filemtime($script_file);
		$dependencies = $config['jquery']['enabled'] ? array('jquery') : array();
		wp_enqueue_script('theme-scripts', $script_path, $dependencies, $version, true);
	}
}

/**
 * フォントの読み込み（プリコネクト最適化対応）
 */
function enqueue_fonts($config)
{
	$fonts = $config['fonts'];

	// Google Fonts
	if ($fonts['google_fonts']['enabled'] && !empty($fonts['google_fonts']['url'])) {
		wp_enqueue_style('google-fonts', $fonts['google_fonts']['url'], false);
	}

	// カスタムフォント
	if (!empty($fonts['custom_fonts'])) {
		foreach ($fonts['custom_fonts'] as $handle => $font_data) {
			wp_enqueue_style($handle, $font_data['url'], array(), $font_data['version'] ?? null);
		}
	}
}

/**
 * jQuery ESモジュール対応読み込み
 */
function enqueue_jquery($config)
{
	$jquery_config = $config['jquery'];

	if (!$jquery_config['enabled']) {
		return;
	}

	switch ($jquery_config['source']) {
		case 'esm':
			// ES Modules版jQuery（Vite推奨）
			$jquery_url = 'https://cdn.skypack.dev/jquery@' . $jquery_config['version'];
			wp_enqueue_script('jquery', $jquery_url, array(), $jquery_config['version'], true);
			break;
		case 'cdn':
			// 従来のCDN版
			$jquery_url = 'https://code.jquery.com/jquery-' . $jquery_config['version'] . '.min.js';
			wp_enqueue_script('jquery', $jquery_url, array(), $jquery_config['version'], true);
			break;
		case 'local':
		default:
			// WordPressデフォルト
			break;
	}
}

/**
 * YubinBangoとフォーム関連機能（Vite対応）
 */
function enqueue_form_features($config)
{
	$yubinbango = $config['features']['yubinbango'];

	if (!$yubinbango['enabled']) {
		return;
	}

	if (!empty($yubinbango['pages']) && is_page($yubinbango['pages'])) {
		wp_enqueue_script('yubinbango', 'https://yubinbango.github.io/yubinbango/yubinbango.js', array(), '1.0.0', true);

		// フォームバリデーション（Vite環境対応）
		if ($config['features']['form_validation']) {
			$is_dev = WP_DEBUG;
			if ($is_dev) {
				$vite = $config['vite'];
				$dev_server_url = $vite['protocol'] . '://' . $vite['host'] . ':' . $vite['port'];
				$form_error_url = $dev_server_url . '/src/assets/js/_form-error.js';
			} else {
				$form_error_url = get_template_directory_uri() . '/dist/_form-error.js';
				$form_error_file = get_template_directory() . '/dist/_form-error.js';
				if (!file_exists($form_error_file)) {
					return; // ファイルが存在しない場合は何もしない
				}
			}
			wp_enqueue_script('form-error', $form_error_url, array('contact-form-7', 'yubinbango'), '1.0.0', true);
		}
	}
}

/**
 * ESモジュール対応ライブラリの読み込み
 */
function enqueue_esm_libraries($config)
{
	foreach ($config['esm_libraries'] as $handle => $lib_data) {
		wp_enqueue_script(
			$handle,
			$lib_data['url'],
			$lib_data['deps'] ?? array(),
			$lib_data['version'] ?? null,
			$lib_data['in_footer'] ?? true
		);
	}
}

/**
 * 追加アセットの読み込み（従来形式対応）
 */
function enqueue_additional_assets($config)
{
	// 追加スタイル
	foreach ($config['additional_styles'] as $handle => $style_data) {
		wp_enqueue_style(
			$handle,
			$style_data['url'],
			$style_data['deps'] ?? array(),
			$style_data['version'] ?? null,
			$style_data['media'] ?? 'all'
		);
	}

	// 追加スクリプト
	foreach ($config['additional_scripts'] as $handle => $script_data) {
		wp_enqueue_script(
			$handle,
			$script_data['url'],
			$script_data['deps'] ?? array(),
			$script_data['version'] ?? null,
			$script_data['in_footer'] ?? true
		);
	}
}

/**
 * メイン関数：Vite対応のスクリプトとスタイルを読み込む
 */
function add_vite_scripts()
{
	$config = get_vite_enqueue_config();
	$is_dev = WP_DEBUG;

	// 環境別アセット読み込み
	if ($is_dev) {
		enqueue_vite_dev_assets($config);
	} else {
		enqueue_vite_prod_assets($config);
	}

	// 共通アセット読み込み
	enqueue_fonts($config);
	enqueue_jquery($config);
	enqueue_form_features($config);
	enqueue_esm_libraries($config);
	enqueue_additional_assets($config);
}

add_action('wp_enqueue_scripts', 'add_vite_scripts');

/*
 * ========================================
 * Vite専用カスタマイズ例
 * ========================================
 *
 * ## 基本的な使い方
 * このファイルをそのまま読み込むだけで、Vite環境で動作します。
 *
 * ## Vite設定のカスタマイズ例
 * functions.phpやテーマの設定ファイルで以下のようにフィルターを使用：
 *
 * ```php
 * function customize_vite_config($config) {
 *     // 開発サーバーのポート変更（Next.js等と共存）
 *     $config['vite']['port'] = 3001;
 *
 *     // アセットパス変更（カスタム構成）
 *     $config['asset_paths']['css_dir'] = '/src/styles';
 *     $config['asset_paths']['js_dir'] = '/src/scripts';
 *
 *     // Splideを有効化
 *     $config['features']['splide'] = true;
 *
 *     // ESモジュール対応ライブラリ追加
 *     $config['esm_libraries']['gsap'] = array(
 *         'url' => 'https://cdn.skypack.dev/gsap',
 *         'version' => '3.12.2'
 *     );
 *
 *     // jQuery ESM版を使用
 *     $config['jquery']['source'] = 'esm';
 *
 *     return $config;
 * }
 * add_filter('vite_enqueue_config', 'customize_vite_config');
 * ```
 *
 * ## Vite設定ファイル例（vite.config.js）
 * ```javascript
 * import { defineConfig } from 'vite'
 *
 * export default defineConfig({
 *   root: './src',
 *   build: {
 *     outDir: '../dist',
 *     manifest: true,
 *     rollupOptions: {
 *       input: {
 *         main: './src/assets/js/script.js',
 *         style: './src/assets/style/style.scss'
 *       }
 *     }
 *   },
 *   server: {
 *     port: 5173,
 *     hmr: {
 *       port: 5173
 *     }
 *   }
 * })
 * ```
 *
 * ## Vite専用機能
 *
 * ### Hot Module Replacement (HMR)
 * 開発環境で自動的に有効化。スタイルやJSの変更が即座に反映されます。
 *
 * ### Manifest.json対応
 * 本番環境では自動的にViteのmanifest.jsonを読み込み、
 * ハッシュ化されたファイル名に対応します。
 *
 * ### ESモジュール対応
 * 最新のJavaScript機能を活用できます。
 *
 * ### 設定可能な項目（Vite専用）
 *
 * #### vite
 * - host: 開発サーバーのホスト名
 * - port: 開発サーバーのポート番号
 * - protocol: プロトコル（http/https）
 * - hmr: Hot Module Replacementの有効/無効
 *
 * #### asset_paths
 * - css_dir: CSSファイルのソースディレクトリ
 * - js_dir: JSファイルのソースディレクトリ
 * - build_dir: ビルド出力ディレクトリ
 *
 * #### features
 * - css_modules: CSS Modulesの有効/無効
 * - scss_processing: SCSS自動処理の有効/無効
 *
 * #### esm_libraries
 * ESモジュール対応ライブラリの配列
 * SkypackやESM.shなどのCDNから直接インポート可能
 */

// Vite専用：ES Modulesサポート
function add_vite_module_attribute($tag, $handle, $src)
{
	// Vite開発環境とビルド後のスクリプトにmodule属性を追加
	$module_handlers = ['theme-scripts', 'vite-client'];

	// ESM librariesも追加
	$config = get_vite_enqueue_config();
	$esm_handles = array_keys($config['esm_libraries']);
	$module_handlers = array_merge($module_handlers, $esm_handles);

	if (in_array($handle, $module_handlers)) {
		$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
	}

	return $tag;
}
add_filter('script_loader_tag', 'add_vite_module_attribute', 10, 3);

// Google Fonts最適化（プリコネクト）
function add_font_preconnect($html, $handle)
{
	if ('google-fonts' === $handle) {
		$config = get_vite_enqueue_config();
		if ($config['fonts']['google_fonts']['preconnect']) {
			$html = <<<EOT
<link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
$html
EOT;
		}
	}
	return $html;
}
add_filter('style_loader_tag', 'add_font_preconnect', 10, 2);
