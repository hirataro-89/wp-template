<?php
/**
 * 投稿タイプのラベルを変更（汎用化）
 * フィルターで設定可能な構造
 */
function change_post_type_labels() {
	// デフォルト設定（フィルターでオーバーライド可能）
	$post_type_labels = apply_filters('custom_post_type_labels', array(
		'post' => 'お知らせ'  // 投稿 → お知らせ に変更
	));
	
	// 各投稿タイプのラベルを変更
	foreach ($post_type_labels as $post_type => $new_name) {
		if ($post_type === 'post') {
			change_default_post_labels($new_name);
		}
	}
}

function change_default_post_labels($name) {
	global $wp_post_types;
	
	$labels = &$wp_post_types['post']->labels;
	$labels->name = $name;
	$labels->singular_name = $name;
	$labels->add_new_item = $name.'の新規追加';
	$labels->edit_item = $name.'の編集';
	$labels->new_item = '新規'.$name;
	$labels->view_item = $name.'を表示';
	$labels->search_items = $name.'を検索';
	$labels->not_found = $name.'が見つかりませんでした';
	$labels->not_found_in_trash = 'ゴミ箱に'.$name.'は見つかりませんでした';
}

function change_default_post_menu_labels() {
	global $menu;
	global $submenu;
	
	$post_type_labels = apply_filters('custom_post_type_labels', array(
		'post' => 'お知らせ'
	));
	
	if (isset($post_type_labels['post'])) {
		$name = $post_type_labels['post'];
		$menu[5][0] = $name;
		$submenu['edit.php'][5][0] = $name.'一覧';
		$submenu['edit.php'][10][0] = '新しい'.$name;
	}
}

add_action('init', 'change_post_type_labels');
add_action('admin_menu', 'change_default_post_menu_labels');
