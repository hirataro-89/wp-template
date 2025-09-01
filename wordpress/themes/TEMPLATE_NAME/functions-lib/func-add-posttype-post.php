<?php

/**
 * 投稿タイプのラベルを「お知らせ」に変更
 */

// 投稿タイプのラベル変更
function change_post_labels()
{
	global $wp_post_types;

	$labels = &$wp_post_types['post']->labels;
	$labels->name = 'お知らせ';
	$labels->singular_name = 'お知らせ';
	$labels->add_new_item = 'お知らせの新規追加';
	$labels->edit_item = 'お知らせの編集';
	$labels->new_item = '新規お知らせ';
	$labels->view_item = 'お知らせを表示';
	$labels->search_items = 'お知らせを検索';
	$labels->not_found = 'お知らせが見つかりませんでした';
	$labels->not_found_in_trash = 'ゴミ箱にお知らせは見つかりませんでした';
}

// 管理画面メニューのラベル変更
function change_post_menu_labels()
{
	global $menu, $submenu;

	$menu[5][0] = 'お知らせ';
	$submenu['edit.php'][5][0] = 'お知らせ一覧';
	$submenu['edit.php'][10][0] = '新しいお知らせ';
}

add_action('init', 'change_post_labels');
add_action('admin_menu', 'change_post_menu_labels');
