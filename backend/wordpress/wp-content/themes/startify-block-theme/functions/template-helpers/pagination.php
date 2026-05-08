<?php

  // ページネーション出力
  function shortcode_pagination() {
    global $wp_query;
    $pages = $wp_query->max_num_pages;
    $paged = get_query_var('paged');
    $paged == 0 ? $paged = 1 : $paged = get_query_var('paged');
    $showItems = 3;
    if($pages == 0 || $pages == 1) {
      return '';
    }
    ob_start();
    echo '<ul>';
    if($paged != 1) {
      echo '<li><a href="'.get_pagenum_link(1).'">&lt;&lt;</a></li>';
      echo '<li><a href="'.get_pagenum_link($paged - 1).'">&lt;</a></li>';
    }
    for($index = 0; $index <= $showItems - 1; $index++) {
      if($index == 0) {
        echo '<li><a href="'.get_pagenum_link($paged).'">'.$paged.'</a></li>';
      } else {
        if($paged + $index <= $pages) {
          echo '<li><a href="'.get_pagenum_link($paged + $index).'">'.($paged + $index).'</a></li>';
        }
      }
    }
    if($paged != $pages) {
      echo '<li><a href="'.get_pagenum_link($paged + 1).'">&gt;</a></li>';
      echo '<li><a href="'.get_pagenum_link($pages).'">&gt;&gt;</a></li>';
    }
    echo '</ul>';
    return ob_get_clean();
  }
  add_shortcode('startify_pagination', 'shortcode_pagination');

?>
