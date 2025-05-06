<?php

  // カスタム投稿タイプ登録
  require_once(dirname(__FILE__).'/post-types/blog.php');

  // カスタム分類登録
  require_once(dirname(__FILE__).'/taxonomies/blog-category.php');
  require_once(dirname(__FILE__).'/taxonomies/blog-tag.php');

  // メインクエリ書き換え（投稿者アーカイブ）
  function custom_archive_author($query) {
    if(is_admin() || !$query->is_main_query()) {
      return;
    }
    if($query->is_author()) {
      $query->set('post_type', 'blog');
    }
  }
  add_action('pre_get_posts','custom_archive_author');

  // キーワード検索のクエリ変更
  function custom_archive_search($searchResult, $wp_query) {
    global $wpdb;
    if(!$wp_query->is_search) {
      return $searchResult;  
    }
    if(!isset($wp_query->query_vars)) {
      return $searchResult;  
    }
    $keywords = explode(' ', isset($wp_query->query_vars['s']) ? $wp_query->query_vars['s'] : '');
    if(count($keywords) > 0) {
      $searchResult = '';
      $searchResult .= "
        AND post_type = ('page' 
          OR 'blog'
        )";
      foreach ($keywords as $keyword) {
        if(!empty($keyword)) {
          $keywords = '%'.esc_sql($keyword).'%';
          $searchResult .= " 
            AND (
              {$wpdb->posts}.post_title LIKE '{$keywords}'
                OR {$wpdb->posts}.post_content LIKE '{$keywords}'
                OR {$wpdb->posts}.ID IN (
                  SELECT distinct post_id
                  FROM {$wpdb->postmeta}
                  WHERE meta_value LIKE '{$keywords}'
                )
            ) ";
        }
      }
    }
    return $searchResult;
  }
  add_filter('posts_search','custom_archive_search', 10, 2);

  // GETパラメーターでのタクソノミーアーカイブ用クエリ変数追加
  function add_custom_query_vars($query_vars) {
    $querys = array(
      'category',
      'tags'
    );
    foreach($querys as $val){
      $query_vars[] = $val;
    }
    return $query_vars;
  }
  add_filter('query_vars', 'add_custom_query_vars');

?>