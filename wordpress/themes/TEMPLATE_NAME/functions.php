<?php
/**
 * Bathe functions
 *
 * @package Bathe
 */

/**
 * Set up theme defaults and registers support for various WordPress feaures.
 */
add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'bathe', get_theme_file_uri( 'languages' ) );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
			)
		);
		add_theme_support(
			'custom-background',
			apply_filters(
				'bathe_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 200,
				'width'       => 50,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'bathe' ),
			)
		);
	}
);

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
add_action(
	'after_setup_theme',
	function () {
		$GLOBALS['content_width'] = apply_filters( 'bathe_content_width', 960 );
	},
	0
);

/**
 * Register widget area.
 */
add_action(
	'widgets_init',
	function () {
		register_sidebar(
			array(
				'name'          => __( 'Sidebar', 'bathe' ),
				'id'            => 'sidebar-1',
				'description'   => '',
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
);

/**
 * Enqueue scripts and styles.
 */
function add_custom_scripts() {
    // Google Fontsの追加
    wp_enqueue_style( 'google-fonts-montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Noto+Serif+JP:wght@300;400;500;700&display=swap', false );
    wp_enqueue_style( 'google-fonts-noto', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Noto+Sans+JP:wght@400;700&family=Noto+Serif+JP:wght@300;400;500;700&display=swap', false );

    // swiperのCSSの追加
    wp_enqueue_style( 'swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', false );

    // テーマのCSSの追加
    wp_enqueue_style( 'theme-styles', get_theme_file_uri('assets/style/style.css'), false );

    // jQueryの追加
    wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.6.0.js', array(), '3.6.0', true );

    // swiperのJSの追加
    wp_enqueue_script( 'swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array('jquery'), '8.0.0', true );

    // テーマのJSの追加
    wp_enqueue_script( 'theme-scripts', get_theme_file_uri('assets/js/script.js'), array('jquery'), '1.0.0', true, 'module' );
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