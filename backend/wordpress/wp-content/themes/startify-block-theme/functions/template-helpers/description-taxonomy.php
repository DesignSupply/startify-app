<?php

  // タクソノミーページ説明文表示
  function shortcode_taxonomy_description() {
    return '<p>「' . esc_html(single_term_title('', false)) . '」の記事一覧</p>';
  }
  add_shortcode('startify_taxonomy_description', 'shortcode_taxonomy_description');

?>
