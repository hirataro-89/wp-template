<?php

/**
 * Enqueue scripts and styles.
 */
function add_custom_scripts()
{
    // WP_DEBUGの安全な確認
    $is_debug = defined('WP_DEBUG') && constant('WP_DEBUG');

    // 開発環境の判定（より柔軟に）
    $is_dev = $is_debug ||
        (defined('WP_ENVIRONMENT_TYPE') && constant('WP_ENVIRONMENT_TYPE') === 'development') ||
        (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost:8888');

    if ($is_dev) {
        $root = "http://localhost:5173";
        $css_ext = "scss";
        $js_ext = "js";

        // Vite開発サーバーとの連携
        wp_enqueue_script('vite-client', $root . '/@vite/client', array(), null, true);

        // テーマのCSSの追加（開発用）
        $style_path = $root . '/assets/style/style.' . $css_ext;
        wp_enqueue_style('theme-styles', $style_path, array(), null, false);

        // 開発用のJS（モジュール形式）
        wp_enqueue_script('theme-scripts', $root . '/assets/js/script.' . $js_ext, array(), null, true);
    } else {
        $root = get_template_directory_uri();
        $css_ext = "css";
        $js_ext = "js";

        // 本番用のCSS（バージョン付き）
        $style_path = $root . '/assets/style/style.' . $css_ext;
        $version = filemtime(get_template_directory() . '/assets/style/style.' . $css_ext);
        wp_enqueue_style('theme-styles', $style_path, array(), $version, false);

        // 本番用のJS
        wp_enqueue_script('theme-scripts', $root . '/assets/js/script.' . $js_ext, array('jquery'), '1.0.0', true);
    }

    // Google Fontsの追加
    wp_enqueue_style('google-fonts-montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Noto+Serif+JP:wght@300;400;500;700&display=swap', false);
    wp_enqueue_style('google-fonts-noto', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Noto+Sans+JP:wght@400;700&family=Noto+Serif+JP:wght@300;400;500;700&display=swap', false);

    // jQueryの追加（開発環境では除外）
    if (!$is_dev) {
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.js', array(), '3.6.0', true);
    }
}

add_action('wp_enqueue_scripts', 'add_custom_scripts');

/**
 * WordPressでもtype='module'を付与
 */
function add_type_attribute($tag, $handle, $src)
{
    $target_handler = ['theme-scripts'];
    if (!in_array($handle, $target_handler)) return $tag;

    // 開発環境ではモジュール形式
    $is_debug = defined('WP_DEBUG') && constant('WP_DEBUG');
    $is_dev = $is_debug ||
        (defined('WP_ENVIRONMENT_TYPE') && constant('WP_ENVIRONMENT_TYPE') === 'development') ||
        (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost:8888');

    if ($is_dev) {
        $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }

    return $tag;
}
add_filter('script_loader_tag', 'add_type_attribute', 10, 3);

/**
 * crossorigin属性を持つタグに対する対応
 */
function add_rel_preconnect($html, $handle, $href, $media)
{
    if ('google-fonts-montserrat' === $handle || 'google-fonts-noto' === $handle || 'swiper' === $handle) {
        $html = <<<EOT
<link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
$html
EOT;
    }
    return $html;
}

add_filter('style_loader_tag', 'add_rel_preconnect', 10, 4);

/**
 * 開発環境でのデバッグ情報表示
 */
function debug_development_info()
{
    $is_debug = defined('WP_DEBUG') && constant('WP_DEBUG');
    $is_dev = $is_debug ||
        (defined('WP_ENVIRONMENT_TYPE') && constant('WP_ENVIRONMENT_TYPE') === 'development') ||
        (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost:8888');

    if ($is_dev && current_user_can('administrator')) {
        echo '<!-- Development Mode: Vite Server Connected -->';
    }
}
add_action('wp_head', 'debug_development_info');
