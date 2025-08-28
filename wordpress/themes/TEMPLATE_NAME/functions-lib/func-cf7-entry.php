<?php
/**
 * func-cf7-entry
 * Contact Form 7のエントリーフォーム用動的値設定・JS読み込み
 */

// Contact Form 7のhidden fieldに動的な値を設定
function cf7_dynamic_hidden_fields($tag) {
    // 共通のジョブタイプサニタイズ処理
    $job_type = $_GET['job'] ?? 'honsha';
    $allowed_jobs = ['honsha', 'seibu'];
    $job_type = in_array($job_type, $allowed_jobs) ? $job_type : 'honsha';
    
    // job-type fieldの場合
    if ($tag['name'] == 'job-type') {
        $tag['values'] = array($job_type);
    }

    // job-title fieldの場合
    if ($tag['name'] == 'job-title') {
        // 職種名マッピング
        $job_titles = [
            'honsha' => '本社営業',
            'seibu' => '西部営業所営業'
        ];
        $tag['values'] = array($job_titles[$job_type]);
    }

    return $tag;
}
add_filter('wpcf7_form_tag', 'cf7_dynamic_hidden_fields');

// JavaScriptでも確実に値を設定（念のため）
function enqueue_entry_form_scripts() {
    if (is_page('entry')) {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'entry-form-js',
            get_template_directory_uri() . '/assets/js/_entry-form.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_entry_form_scripts');
