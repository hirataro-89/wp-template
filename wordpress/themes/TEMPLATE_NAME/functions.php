<?php
/**
 * Enqueue scripts and styles.
 */
function add_custom_scripts() {
	if (WP_DEBUG) {
        $root = "http://localhost:5173";
        $css_ext = "scss";
        $js_ext = "js";
        wp_enqueue_script('vite-client', $root . '/@vite/client', array(), null, true);
    } else {
        $root = get_template_directory_uri();
        $css_ext = "css";
        $js_ext = "js";
    }
    // Google Fontsの追加
    wp_enqueue_style( 'google-fonts-montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Noto+Serif+JP:wght@300;400;500;700&display=swap', false );
    wp_enqueue_style( 'google-fonts-noto', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Noto+Sans+JP:wght@400;700&family=Noto+Serif+JP:wght@300;400;500;700&display=swap', false );

    // swiperのCSSの追加
    wp_enqueue_style( 'swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', false );

    // テーマのCSSの追加
    //wp_enqueue_style( 'theme-styles', get_theme_file_uri('assets/style/style.css'), false );
		wp_enqueue_style('theme-style', $root . '/assets/style/style.' . $css_ext, false);

    // jQueryの追加
    wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.6.0.js', array(), '3.6.0', true );

    // swiperのJSの追加
    wp_enqueue_script( 'swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array('jquery'), '8.0.0', true );
		
    // テーマのJSの追加
    //wp_enqueue_script( 'theme-scripts', get_theme_file_uri('assets/js/script.js'), array('jquery'), '1.0.0', true);
		wp_enqueue_script('theme-scripts', $root . '/assets/js/script.' . $js_ext, array('jquery'), '1.0.0', true);
}

add_action( 'wp_enqueue_scripts', 'add_custom_scripts' );


// WordPressでもtype='module'を付与
function add_type_attribute($tag, $handle, $src)
{
    $target_handler = ['theme-scripts'];
    if (!in_array($handle, $target_handler)) return $tag;
    $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    return $tag;
}
add_filter('script_loader_tag', 'add_type_attribute', 10, 3);

// crossorigin属性を持つタグに対する対応
function add_rel_preconnect( $html, $handle, $href, $media ) {
    if ( 'google-fonts-montserrat' === $handle || 'google-fonts-noto' === $handle || 'swiper' === $handle ) {
        $html = <<<EOT
<link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
$html
EOT;
    }
    return $html;
}

add_filter( 'style_loader_tag', 'add_rel_preconnect', 10, 4 );