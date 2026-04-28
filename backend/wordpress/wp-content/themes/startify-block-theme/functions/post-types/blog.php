<?php

  function create_custom_post_type_blog() {
    /**
     * カスタム投稿名：ブログ
     * スラッグ: blog
     * URLに使用するスラッグ: blog
     */
    $postTypeName = 'ブログ';
    $postTypeSlug = 'blog';
    $postTypeSlugFront = 'blog';

    $labels = array(
      "name" => __($postTypeName),
      "singular_name" => __($postTypeName),
      "all_items" => $postTypeName.'一覧'
    );
    $args = array(
      "label" => __($postTypeName),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "delete_with_user" => false,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => true,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "rewrite" => array( 
        "slug" => $postTypeSlugFront, 
        "with_front" => true 
      ),
      "query_var" => true,
      "menu_position" => 5,
      "supports" => array( 
        "title", 
        "editor", 
        "author",
        "thumbnail", 
        "excerpt",
        "comments",
        "revisions"
      ),
    );
    register_post_type($postTypeSlug, $args);
  }
  add_action('init', 'create_custom_post_type_blog');

?>