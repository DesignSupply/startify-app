<?php

  // ウィジェット登録
  function add_widgets_sidebar() {
    register_sidebar(
      array(
        'name' => 'ウィジェット',
        'id' => 'widget-sidebar',
        'description' => '管理画面から編集可能なウィジェットです',
        'before_widget' => '<div>',
        'after_widget' => '</div>',
        'before_title' => '',
        'after_title' => ''
      )
    );
  }
  add_action('widgets_init', 'add_widgets_sidebar');

  // カスタムメニュー登録
  register_nav_menus( 
    array(
      'global-navi' => 'グローバルナビゲーション',
      'sitemap' => 'サイトマップ'
    )
  );
  
?>