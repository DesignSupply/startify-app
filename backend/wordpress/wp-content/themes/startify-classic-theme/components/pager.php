<?php
  if(get_previous_post_link()) {
    previous_post_link('%link', '&laquo;&nbsp;前の記事へ', false);
  }
  if(get_next_post_link()) {
    next_post_link('%link', '次の記事へ&nbsp;&raquo;', false);
  }
?>
<!-- pager end -->