<?php

  // メタタグ用タイトル出力
  function seo_meta_title() {
    if(is_page() || is_singular()) {
      // 固定ページ・シングルページ
      return esc_html(get_the_title()).'｜'.get_bloginfo('name');
    } else {
      return get_bloginfo('name'); 
    }
  }

  // メタタグ用ディスクリプション出力
  function seo_meta_description() {
    if(is_page() || is_singular()) {
      // 固定ページ・シングルページ
      return esc_html(get_the_excerpt());
    } else {
      return get_bloginfo('description'); 
    }
  }

  // メタタグ用OGPイメージ出力
  function seo_meta_ogp() {
    if(is_singular()) {
      return get_the_post_thumbnail_url(get_the_ID(), 'full');
    } else {
      return '*********************.jpg';
    }
  }

  // メタタグ用ページタイプ出力
  function seo_meta_type() {
    if(is_front_page() || is_home()) {
      return 'website';
    } else {
      return 'article';
    }
  }

  // メタタグ用ページURL出力
  function seo_meta_url() {
    if(is_front_page() || is_home()) {
      return esc_url(home_url());
    } else if(is_page() || is_singular()){
      return esc_url(get_the_permalink());
    } else {
      return (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }
  }

?>