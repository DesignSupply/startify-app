<?php

  // 投稿者アーカイブ説明文表示
  function shortcode_author_description() {
    return '<p>「' . esc_html(get_query_var('author_name')) . '」が投稿した記事の一覧です。</p>';
  }
  add_shortcode('startify_author_description', 'shortcode_author_description');

?>
