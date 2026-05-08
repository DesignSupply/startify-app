<?php

  // ブログ投稿一覧取得（クエリパラメーターによるタクソノミーフィルタリング）
  // 対応: archive.php クエリパラメーターでのタクソノミーアーカイブ（?category= / ?tags=）
  function sub_query_posts_blog_by_params($args = array()) {
    if(get_query_var('category')) {
      $defaults = array(
        'post_type' => 'blog',
        'tax_query' => array(
          array(
            'taxonomy' => 'blog_category',
            'field'    => 'slug',
            'terms'    => get_query_var('category'),
          )
        ),
      );
    } else if(get_query_var('tags')) {
      $defaults = array(
        'post_type' => 'blog',
        'tax_query' => array(
          array(
            'taxonomy' => 'blog_tag',
            'field'    => 'slug',
            'terms'    => get_query_var('tags'),
          )
        ),
      );
    } else {
      $defaults = array(
        'post_type' => 'blog',
      );
    }
    return get_posts(wp_parse_args($args, $defaults));
  }

?>
