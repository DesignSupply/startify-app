<?php
  $data_list = array();
  if(!is_front_page() && !is_home()) {
    if(is_page()) {
      // 固定ページ
      if(get_parent_page_ID()) {
        $postId = $post->ID;
        $parentPostArray = array_reverse(get_post_ancestors($post)); 
        // 固定ページ（親・先祖ページ）
        foreach($parentPostArray as $index => $parentsPostId) {
          $data_list_item = ',{ "@type": "ListItem", "position": '.($index + 2).', "item": { "@id": "'.esc_url(get_the_permalink($parentsPostId)).'", "name": "'.esc_html(get_the_title($parentsPostId)).'" }}';
          array_push($data_list, $data_list_item);
          $parentPages = $index + 1;
        }
        // 固定ページ（子孫ページ）
        $data_list_item_last = ',{ "@type": "ListItem", "position": '.($parentPages + 2).', "item": { "@id": "'.esc_url(get_the_permalink($postId)).'", "name": "'.esc_html(get_the_title($postId)).'" }}';
        array_push($data_list, $data_list_item_last);
      } else {
        // 固定ページ（子ページ）
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.esc_url(get_the_permalink()).'", "name": "'.esc_html(single_post_title('',false)).'" }}'
        );
      }
    } else if(is_post_type_archive()) {
      $postTypeObject = get_post_type_object(get_query_var('post_type'));
      $postTypeName = $postTypeObject->labels->name;
      if(is_date_archive()) {
        // 日付アーカイブページ
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_query_var('post_type')).'", "name": "'.$postTypeName.'" }}',
          ',{ "@type": "ListItem", "position": 3, "item": { "@id": "'.esc_url(home_url('/')).$wp_query->query['year'].'/'.$wp_query->query['monthnum'].'/?post_type='.$wp_query->query['post_type'].'", "name": "'.get_query_var('year').'年'.get_query_var('monthnum').'月の投稿一覧" }}'
        );
      } else {
        // アーカイブページ
        $data_list = array(
          ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_query_var('post_type')).'", "name": "'.$postTypeName.'" }}'
        );
      }
    } else if(is_tax()) {
      // タクソノミーページ
      $postTypeObject = get_post_type_object(get_post_type());
      $postTypeName = $postTypeObject->labels->name;
      $taxonomySlug = get_query_var('taxonomy');
      $termName = urldecode(get_query_var('term'));
      $data_list = array(
        ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_post_type()).'", "name": "'.$postTypeName.'" }}',
        ',{ "@type": "ListItem", "position": 3, "item": { "@id": "'.get_term_link($termName, $taxonomySlug).'", "name": "'.esc_html(single_term_title('',false)).'の記事一覧" }}'
      );
    } else if(is_singular(get_post_type())) {
      // シングルページ
      $postTypeObject = get_post_type_object(get_post_type());
      $postTypeName = $postTypeObject->labels->name;
      $data_list = array(
        ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_post_type_archive_link(get_post_type()).'", "name": "'.$postTypeName.'" }}',
        ',{ "@type": "ListItem", "position": 3, "item": { "@id": "'.esc_url(get_the_permalink()).'", "name": "'.esc_html(get_the_title()).'" }}'
      );
    } else if(is_author()) {
      // 投稿者アーカイブページ
      global $post;
      $author = get_userdata($post->post_author);
      $data_list = array(
        ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.get_author_posts_url(get_the_author_meta('ID')).'", "name": "'.$author->display_name.'の記事一覧" }}'
      );
    } else if(is_search()) {
      // 検索結果ページ
      $data_list = array(
        ',{ "@type": "ListItem", "position": 2, "item": { "@id": "'.esc_url(get_home_url()).'/?s='.esc_html($_GET['s']).'", "name": "キーワード検索結果" }}'
      );
    } else if(is_404()) {
      // 404ページ
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
?>
<!-- json LD end -->