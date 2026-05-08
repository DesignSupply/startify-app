<?php

  // ブログ投稿一覧取得
  // 対応: index.php カスタム投稿サブループ / Ajax投稿デモサブループ
  function sub_query_posts_blog($args = array()) {
    $defaults = array(
      'post_status'      => 'publish',
      'post_type'        => 'blog',
      'posts_per_page'   => 5,
      'suppress_filters' => false,
      // 'ignore_sticky_posts' => false,
      // 'tax_query' => array(
      //   'relation' => 'AND',
      //   array(),
      //   array()
      // ),
      // 'meta_query' => array(
      //   'relation' => 'AND',
      //   array(),
      //   array()
      // ),
      // 'date_query' => array(
      //   array(),
      //   array()
      // ),
      // 's' => '',
      // 'order' => 'DESC',
      // 'orderby' => array()
    );
    return get_posts(wp_parse_args($args, $defaults));
  }

?>
