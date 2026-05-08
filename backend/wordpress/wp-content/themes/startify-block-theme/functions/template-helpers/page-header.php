<?php

  // ページヘッダータイトル出力
  function shortcode_page_header() {
    global $post;
    $title = '';
    if(is_front_page() || is_home()) {
      $title = get_bloginfo('name');
    } else if(is_page()) {
      $title = esc_html(single_post_title('', false));
    } else if(is_post_type_archive(get_post_type())) {
      $postTypeObject = get_post_type_object(get_post_type());
      $postTypeName = $postTypeObject->labels->name;
      if(is_date_archive()) {
        $postYear = get_query_var('year');
        $postMonth = get_query_var('monthnum');
        $title = $postTypeName . '｜' . $postYear . '年' . $postMonth . '月';
      } else {
        $title = $postTypeName;
      }
    } else if(is_singular(get_post_type(get_the_ID()))) {
      $postTypeObject = get_post_type_object(get_post_type());
      $title = $postTypeObject->labels->name;
    } else if(is_tax()) {
      $postTypeObject = get_post_type_object(get_post_type());
      $postTypeName = $postTypeObject->labels->name;
      $termName = esc_html(single_term_title('', false));
      $title = $postTypeName . '｜' . $termName;
    } else if(is_author()) {
      $author = get_userdata($post->post_author);
      $title = $author->display_name . 'の記事';
    } else if(is_search()) {
      $title = 'キーワード検索結果';
    } else if(is_404()) {
      $title = 'Page Not Found';
    }
    ob_start();
    ?>
    <h1>ページタイトル：<?php echo $title; ?></h1>
    <?php
    return ob_get_clean();
  }
  add_shortcode('startify_page_header', 'shortcode_page_header');

?>
