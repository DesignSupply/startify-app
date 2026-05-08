<?php

  // ブログ投稿件数取得
  // 対応: index.php Ajaxローディング 追加読み込みボタンの表示判定
  function sub_query_count_posts_blog($args = array()) {
    $defaults = array(
      'post_status'      => 'publish',
      'post_type'        => 'blog',
      'posts_per_page'   => -1,
      'suppress_filters' => false,
      'fields'           => 'ids',
    );
    return count(get_posts(wp_parse_args($args, $defaults)));
  }

?>
