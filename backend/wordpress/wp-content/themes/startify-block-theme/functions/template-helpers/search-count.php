<?php

  // 検索結果カウント表示
  function shortcode_search_count() {
    global $wp_query;
    if($wp_query->found_posts > 0) {
      return '<p>「' . esc_html(get_query_var('s')) . '」の検索結果、「' . $wp_query->found_posts . '」件が該当しました。</p>';
    }
    return '';
  }
  add_shortcode('startify_search_count', 'shortcode_search_count');

?>
