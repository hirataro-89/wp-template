<?php
/**
 * ターム名を検索対象に含める
 */
function custom_search_query($search, $wp_query)
{
    // コアが生成した検索句が空か、検索キーワードが届いていない場合はそのまま返す。
    if (empty($search) || empty($wp_query->query_vars['search_terms'])) {
        return $search;
    }

    global $wpdb;

    try {
        // キーワードをトリムして空要素を捨て、無意味な条件が混ざらないように調整。
        $terms = array_filter(array_map('trim', (array) $wp_query->query_vars['search_terms']));

        if (empty($terms)) {
            return $search;
        }

        $conditions = [];

        foreach ($terms as $term) {
            $like = '%' . $wpdb->esc_like($term) . '%';

            // 投稿・抜粋・関連ターム名をまたいで検索するための安全なSQL断片を生成。
            $prepared = $wpdb->prepare(
                "({$wpdb->posts}.post_title LIKE %s
                    OR {$wpdb->posts}.post_content LIKE %s
                    OR {$wpdb->posts}.post_excerpt LIKE %s
                    OR EXISTS (
                        SELECT 1 FROM {$wpdb->terms}
                        INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                        INNER JOIN {$wpdb->term_relationships} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
                        WHERE {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID
                            AND {$wpdb->terms}.name LIKE %s
                    )
                )",
                $like,
                $like,
                $like,
                $like
            );

            if ($prepared !== false) {
                $conditions[] = $prepared;
            }
        }

        // 有効な条件がある場合にだけ既存の検索句を差し替える。
        if (!empty($conditions)) {
            $search = ' AND ' . implode(' AND ', $conditions);
        }
    } catch (Throwable $e) {
        // ユーザーには内部エラーを見せず、デバッグ時だけ詳細をログに残す。
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[custom_search_query] ' . $e->getMessage());
        }
    }

    return $search;
}
add_filter('posts_search', 'custom_search_query', 10, 2);
