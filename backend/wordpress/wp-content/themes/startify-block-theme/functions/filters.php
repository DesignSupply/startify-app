<?php

  // タイトルタグ区切り文字変更
  function rewrite_title_separator($separator) {
    $separator = '|';
    return $separator;
  }
  add_filter('document_title_separator', 'rewrite_title_separator');

  // Admin Bar の非表示
  function disable_admin_bar() {
    return false;
  }
  add_filter('show_admin_bar', 'disable_admin_bar');

  // 抜粋文字数の指定
  function custom_excerpt_length($length) {   
    return 60; 
  }   
  add_filter('excerpt_length', 'custom_excerpt_length');

  // 抜粋文末文字の指定
  function custom_excerpt_more($more) {
    return '…';
  }
  add_filter('excerpt_more', 'custom_excerpt_more');

  // パスワード保護投稿の抜粋文変更
  function excerpt_password_protected($excerpt) {
    if(post_password_required()) {
      $excerpt = 'この投稿はパスワードで保護されています';
    }
    return $excerpt;
  }
  add_filter('the_excerpt', 'excerpt_password_protected');

  // パスワード保護投稿の入力フォーム導入文変更
  function description_password_protected($text) {
    $data_set = array(
      'このコンテンツはパスワードで保護されています。閲覧するには以下にパスワードを入力してください。' => 'このコンテンツの閲覧はパスワードが必要です',
      'パスワード: ' => '【パスワード】',
      '確定' => '送信'
    );
    $search = array_keys($data_set);
    $replace = array_values($data_set);
    $text = str_replace($search, $replace, $text);
    return $text;
  }
  add_filter('the_password_form', 'description_password_protected');

  // パスワード保護投稿のタイトルプレフィックス変更
  function prefix_password_protected() {
    return '【パスワード保護】%s';
  }
  add_filter('protected_title_format', 'prefix_password_protected');

  // 非公開投稿のタイトルプレフィックス変更
  function prefix_private() {
    return '【非公開】%s';
  }
  add_filter('private_title_format', 'prefix_private');

  // ユーザープロフィール情報追加
  function add_user_profile($userProfile) {
    $userProfile['Facebook'] = __('Facebookページ');
    $userProfile['Twitter'] = __('Twitterページ');
    return $userProfile;
  }
  add_filter('user_contactmethods', 'add_user_profile');

  // 最終更新日の出力
  function post_modified($date) {
    $modifiedDate = get_the_modified_time('U');
    $postDate = get_the_time('U');
    if ($modifiedDate < $postDate) {
      return get_the_time($mod_date);
    } else if($modifiedDate === $postDate) {
      return get_the_modified_time($date);
    } else {
      return get_the_modified_time($date);
    }
  }

  // 固定ページの親ページID取得
  function get_parent_page_ID() {
    global $post;
    if(is_page() && $post->post_parent) {
      return $post->post_parent;
    } else {
      return false;
    }
  }

  // 固定ページでスラッグ指定の子ページ判定
  function is_subpage_by_slug($slug) {
    global $post;
    if(is_page($slug) || get_page_by_path($slug)->ID == $post->post_parent) {
      return true;
    } else {
      return false;
    }
  }

  // 日付別アーカイブのチェック
  function is_date_archive() {
    if(get_query_var('year') && get_query_var('monthnum')) {
      return true;
    }
  }

  // 自動整形機能（wpautop）設定
  remove_filter('the_excerpt', 'wpautop');

  // ショートコード内のHTML要素許可
  function custom_kses_allowed_html($tags, $context) {
    $tags['source']['srcset'] = true;
    return $tags;
  }
  add_filter('wp_kses_allowed_html', 'custom_kses_allowed_html', 10, 2);
  
  // プラグイン・テーマの自動更新設定
  add_filter('auto_update_plugin', '__return_true');
  add_filter('auto_update_theme', '__return_true');

?>