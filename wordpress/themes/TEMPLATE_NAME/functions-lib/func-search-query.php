<?php 
/**
 * ターム名を検索対象に含める
 */
function custom_search_query($search, $wp_query) {
  if (!empty($search) && !empty($wp_query->query_vars['search_terms'])) {
    global $wpdb;
    $q = $wp_query->query_vars['search_terms'];
    $search = '';
    foreach ($q as $term) {
      $search .= " AND (
        ($wpdb->posts.post_title LIKE '%$term%')
        OR ($wpdb->posts.post_content LIKE '%$term%')
        OR ($wpdb->posts.post_excerpt LIKE '%$term%')
        OR EXISTS (
          SELECT * FROM $wpdb->terms 
          INNER JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
          INNER JOIN $wpdb->term_relationships ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
          WHERE $wpdb->term_relationships.object_id = $wpdb->posts.ID
          AND $wpdb->terms.name LIKE '%$term%'
        )
      )";
    }
  }
  return $search;
}
add_filter('posts_search', 'custom_search_query', 10, 2);