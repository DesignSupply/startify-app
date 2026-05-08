<?php

  // コピーライト出力
  function shortcode_copyright() {
    return '<span>&copy;&nbsp;' . date('Y') . '&nbsp;' . get_bloginfo('name') . '</span>';
  }
  add_shortcode('startify_copyright', 'shortcode_copyright');

?>
