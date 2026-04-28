<?php

  // メタタグ用タイトル出力
  function seo_meta_title() {
    if(is_page() || is_singular()) {
      return esc_html(get_the_title()).'｜'.get_bloginfo('name');
    } else {
      return get_bloginfo('name'); 
    }
  }

  // メタタグ用ディスクリプション出力
  function seo_meta_description() {
    if(is_page() || is_singular()) {
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
    global $wp;
    if(is_front_page() || is_home()) {
      return esc_url(home_url());
    } else if(is_page() || is_singular()){
      return esc_url(get_the_permalink());
    } else {
      return esc_url(home_url(add_query_arg(array(), $wp->request)));
    }
  }

  // SEOメタタグ出力
  function output_seo_meta_tags() {
    ?>
    <meta name="description" content="<?php echo esc_attr(seo_meta_description()); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr(seo_meta_title()); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr(seo_meta_description()); ?>">
    <meta name="twitter:url" content="<?php echo esc_url(seo_meta_url()); ?>">
    <meta property="og:title" content="<?php echo esc_attr(seo_meta_title()); ?>">
    <meta property="og:description" content="<?php echo esc_attr(seo_meta_description()); ?>">
    <meta property="og:url" content="<?php echo esc_url(seo_meta_url()); ?>">
    <meta property="og:type" content="<?php echo esc_attr(seo_meta_type()); ?>">
    <meta property="og:image" content="<?php echo esc_url(seo_meta_ogp()); ?>">
    <link rel="canonical" href="<?php echo esc_url(seo_meta_url()); ?>">
    <?php
  }
  add_action('wp_head', 'output_seo_meta_tags', 1);

  // JSON-LD（構造化データ / パンくずリスト）出力
  function output_json_ld() {
    global $post, $wp_query;
    $data_list = array();
    if(!is_front_page() && !is_home()) {
      if(is_page()) {
        if(get_parent_page_ID()) {
          $postId = $post->ID;
          $parentPostArray = array_reverse(get_post_ancestors($post));
          foreach($parentPostArray as $index => $parentsPostId) {
            $data_list_item = ',{ "@type": "ListItem", "position": '.($index + 2).', "item": { "@id": "'.esc_url(get_the_permalink($parentsPostId)).'", "name": "'.esc_html(get_the_title($parentsPostId)).'" }}';
            array_push($data_list, $data_list_item);
            $parentPages = $index + 1;
          }
          $data_list_item_last = ',{ "@type": "ListItem", "position": '.($parentPages + 2).', "item": { "@id": "'.esc_url(get_the_permalink($postId)).'", "name": "'.esc_html(get_the_title($postId)).'" }}';
          array_push($data_list, $data_list_item_last);
        } else {
          $data_list = array(
            ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.esc_url(get_the_permalink()).'", "name": "'.esc_html(single_post_title('',false)).'" }}'
          );
        }
      } else if(is_post_type_archive()) {
        $postTypeObject = get_post_type_object(get_query_var('post_type'));
        $postTypeName = $postTypeObject->labels->name;
        if(is_date_archive()) {
          $data_list = array(
            ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_query_var('post_type')).'", "name": "'.$postTypeName.'" }}',
            ',{ "@type": "ListItem", "position": 3, "item": { "@id": "'.esc_url(home_url('/')).$wp_query->query['year'].'/'.$wp_query->query['monthnum'].'/?post_type='.$wp_query->query['post_type'].'", "name": "'.get_query_var('year').'年'.get_query_var('monthnum').'月の投稿一覧" }}'
          );
        } else {
          $data_list = array(
            ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_query_var('post_type')).'", "name": "'.$postTypeName.'" }}'
          );
        }
      } else if(is_tax()) {
        $postTypeObject = get_post_type_object(get_post_type());
        $postTypeName = $postTypeObject->labels->name;
        $taxonomySlug = get_query_var('taxonomy');
        $termName = urldecode(get_query_var('term'));
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_post_type()).'", "name": "'.$postTypeName.'" }}',
          ',{ "@type": "ListItem", "position": 3, "item": { "@id": "'.get_term_link($termName, $taxonomySlug).'", "name": "'.esc_html(single_term_title('',false)).'の記事一覧" }}'
        );
      } else if(is_singular(get_post_type())) {
        $postTypeObject = get_post_type_object(get_post_type());
        $postTypeName = $postTypeObject->labels->name;
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_post_type()).'", "name": "'.$postTypeName.'" }}',
          ',{ "@type": "ListItem", "position": 3, "item": { "@id": "'.esc_url(get_the_permalink()).'", "name": "'.esc_html(get_the_title()).'" }}'
        );
      } else if(is_author()) {
        $author = get_userdata($post->post_author);
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_author_posts_url(get_the_author_meta('ID')).'", "name": "'.$author->display_name.'の記事一覧" }}'
        );
      } else if(is_search()) {
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.esc_url(get_home_url()).'/?s='.esc_html($_GET['s']).'", "name": "キーワード検索結果" }}'
        );
      } else if(is_404()) {
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.esc_url(get_home_url()).'", "name": "Page Not Found" }}'
        );
      }
    }
    echo '<script type="application/ld+json">';
    echo '{ "@context":"http://schema.org", "@type": "BreadcrumbList", "itemListElement": [';
    echo '{ "@type": "ListItem", "position": 1, "item": { "@id": "'.esc_url(get_home_url()).'", "name": "HOME" }}';
    foreach($data_list as $data) {
      echo $data;
    }
    echo '] }';
    echo '</script>';
  }
  add_action('wp_head', 'output_json_ld', 2);

?>