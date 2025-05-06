<?php
  if (post_password_required()) {
  return;
  }

  // コメントリスト出力
  require_once(dirname(__FILE__).'/components/comment-list.php');

  // コメントフォーム出力
  require_once(dirname(__FILE__).'/components/comment-form.php');

?>