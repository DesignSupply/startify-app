<?php

  // 編集リンク出力（ログイン時のみ）
  function shortcode_edit_link() {
    if(is_user_logged_in()) {
      return '<a href="' . get_edit_post_link() . '">編集する</a>';
    }
    return '';
  }
  add_shortcode('startify_edit_link', 'shortcode_edit_link');

?>
