<?php

  // 日付アーカイブ説明文表示
  function shortcode_date_archive_description() {
    if(is_date_archive()) {
      return '<p>' . get_query_var('year') . '年' . get_query_var('monthnum') . '月の記事一覧</p>';
    }
    return '';
  }
  add_shortcode('startify_date_archive_description', 'shortcode_date_archive_description');

?>
