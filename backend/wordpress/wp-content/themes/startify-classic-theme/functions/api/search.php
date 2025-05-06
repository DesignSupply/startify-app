<?php

  // 投稿対象のキーワード検索結果全件記事取得の独自エンドポイント追加
  function add_rest_endpoint_all_posts_search() {
    register_rest_route(
      'wp/api',
      '/search/(?P<keywords>.*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+)',
      array(
        'methods' => 'GET',
        'callback' => 'get_all_posts_search',
        'permission_callback' => function() { return true; }
      )
    );
  }
  function get_all_posts_search($parameter) {
    $args = array(
      'posts_per_page' => -1,
      'post_type' => array(
        'blog'
      ),
      's' => urldecode($parameter['keywords']),
      'post_status' => 'publish'
    );
    $query = new WP_Query($args);
    $targets = $query->posts;
    $result = array();
    foreach($targets as $post) {
      $data = array(
        'ID' => $post->ID,
        'thumbnail' => get_the_post_thumbnail_url($post->ID, 'full'),
        'slug' => $post->post_name,
        'date' => $post->post_date,
        'modified' => $post->post_modified,
        'title' => $post->post_title,
        'excerpt' => $post->post_excerpt,
        'content' => $post->post_content,
      );
      array_push($result, $data);
    };
    return $result;
  }
  add_action('rest_api_init', 'add_rest_endpoint_all_posts_search');

?>