<?php

  // カスタム投稿（blog）の全件記事取得の独自エンドポイント追加
  function add_rest_endpoint_single_posts_from_blog() {
    register_rest_route(
      'wp/api',
      '/blog/(?P<id>[\d]+)',
      array(
        'methods' => 'GET',
        'callback' => 'get_single_posts_from_blog',
        'permission_callback' => function() { return true; }
      )
    );
  }
  function get_single_posts_from_blog($parameter) {
    $args_all = array(
      'posts_per_page' => -1,
      'post_type' => 'blog',
      'post_status' => 'publish',
      'orderby' => 'date',
      'order' => 'DESC',
    );
    $all_posts = get_posts($args_all);
    $all_posts_ids = array();
    foreach($all_posts as $post) {
      array_push($all_posts_ids, $post->ID);
    }
    $args_single = array(
      'posts_per_page' => 1,
      'post_type' => 'blog',
      'post_status' => 'publish',
      'include' => $parameter['id']
    );
    $single_post = get_posts($args_single);
    $single_post_index = !empty($single_post) ? array_search((int) $parameter['id'], $all_posts_ids, true) : -2;
    $prev_post_id = $single_post_index < count($all_posts_ids) - 1 ? $single_post_index + 1 : null;
    $next_post_id = !is_null($single_post_index) && ($single_post_index > 0) ? $single_post_index - 1 : null;
    $targets = array($all_posts[$next_post_id], $single_post[0], $all_posts[$prev_post_id]);
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
  add_action('rest_api_init', 'add_rest_endpoint_single_posts_from_blog');

?>