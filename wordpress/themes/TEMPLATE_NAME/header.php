<!DOCTYPE html>
<html <?php language_attributes(); //html要素のlang属性を出力 
			?>>

<head>
	<meta charset="<?php bloginfo('charset'); //文字エンコーディング情報を出力 
									?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta description="" />
	<meta name="robots" content="noindex" />
	<title>
		<?php wp_title('|', true, 'right'); //ページタイトルを出力?>
		<?php bloginfo('name'); //サイト名を表示?>
	</title>
	<?php
	if (WP_DEBUG) {
		$root = "http://localhost:5173";
		$css_ext = "scss";
		$js_ext = "ts";
		echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
	} else {
		$root = get_template_directory_uri();
		$css_ext = "css";
		$js_ext = "js";
	}
	?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<header class="l-header js-header">
		<div class="l-header__inner p-header">
			<h1 class="p-header__logo"><a href="/"><img src="<?php echo get_template_directory_uri() ?>/assets/images/logo.png " alt="ロゴ画像"></a></h1>

			<!-- ハンバーガーメニュー -->
			<button type="button" class="c-hg-btn js-btn" aria-label="メニュー">
				<span class="c-hg-btn__line js-line"></span>
				<span class="c-hg-btn__line js-line"></span>
				<span class="c-hg-btn__line js-line"></span>
			</button>
		</div>

	</header>