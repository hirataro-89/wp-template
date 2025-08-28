<?php

/**
 * スクリプトとスタイルの読み込み処理（Splide CSS対応版）
 */

/**
 * Enqueue scripts and styles.
 */
function add_custom_scripts()
{
	if (WP_DEBUG) {
		$root = "http://localhost:5173";
		$css_ext = "scss";
		$js_ext = "js";
		wp_enqueue_script('vite-client', $root . '/@vite/client', array(), null, true);

		// テーマのCSSの追加（バージョンなし）
		$style_path = $root . '/assets/style/style.' . $css_ext;
		wp_enqueue_style('theme-styles', $style_path, array(), null, false);

		// 開発環境でもSplideのCSSを読み込み（もしSCSSがあれば）
		$splide_scss_path = $root . '/assets/style/splide.' . $css_ext;
		wp_enqueue_style('splide-styles', $splide_scss_path, array(), null, false);
	} else {
		$root = get_template_directory_uri();
		$css_ext = "css";
		$js_ext = "js";

		// テーマのメインCSSの追加（バージョンあり）
		$style_path = $root . '/assets/style/style.' . $css_ext;
		$version = filemtime(get_template_directory() . '/assets/style/style.' . $css_ext);
		wp_enqueue_style('theme-styles', $style_path, array(), $version, false);

		// 本番環境でSplideのローカルCSSを追加
		$splide_css_path = $root . '/assets/style/splide.' . $css_ext;
		$splide_css_file = get_template_directory() . '/assets/style/splide.' . $css_ext;

		if (file_exists($splide_css_file)) {
			$splide_version = filemtime($splide_css_file);
			wp_enqueue_style('splide-styles', $splide_css_path, array(), $splide_version, false);
		}

		// 本番環境でSplideのローカルJSも追加
		$splide_js_path = $root . '/assets/js/splide.' . $js_ext;
		$splide_js_file = get_template_directory() . '/assets/js/splide.' . $js_ext;

		if (file_exists($splide_js_file)) {
			$splide_js_version = filemtime($splide_js_file);
			wp_enqueue_script('splide-local', $splide_js_path, array('jquery'), $splide_js_version, true);
		}
	}

	// Google Fontsの追加
	wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Baskervville:ital,wght@0,400..700;1,400..700&family=Jost:ital,wght@0,100..900;1,100..900&family=Noto+Sans+JP:wght@100..900&family=Noto+Serif+JP:wght@200..900&display=swap', false);

	// Font Awesomeの追加
	wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), '6.7.2', false);

	// jQueryの追加
	wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.js', array(), '3.6.0', true);


	// テーマのJSの追加
	wp_enqueue_script('theme-scripts', $root . '/assets/js/script.' . $js_ext, array('jquery'), '1.0.0', true);

	// YubinBango: 郵便番号から住所自動入力
	// お問い合わせ・エントリーページのみ読み込み
	if (is_page(array('contact', 'entry'))) {
		wp_enqueue_script('yubinbango', 'https://yubinbango.github.io/yubinbango/yubinbango.js', array(), '1.0.0', true);

		// フォームエラー処理とYubinBangoボタン機能のスクリプトを追加
		// Contact Form 7とYubinBangoに依存
		wp_enqueue_script('form-error', get_template_directory_uri() . '/assets/js/_form-error.js', array('contact-form-7', 'yubinbango'), '1.0.0', true);
	}
}

add_action('wp_enqueue_scripts', 'add_custom_scripts');

// 以下のコードは既存のまま
// WordPressでもtype='module'を付与
function add_type_attribute($tag, $handle, $src)
{
	$target_handler = ['theme-scripts', 'vite-client'];
	if (!in_array($handle, $target_handler)) return $tag;
	$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
	return $tag;
}
add_filter('script_loader_tag', 'add_type_attribute', 10, 3);

// crossorigin属性を持つタグに対する対応
function add_rel_preconnect($html, $handle)
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

add_filter('style_loader_tag', 'add_rel_preconnect', 10, 2);
