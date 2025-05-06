<?php

  // 各種フック処理
  require_once(dirname(__FILE__).'/functions/actions.php');
  require_once(dirname(__FILE__).'/functions/filters.php');

  // 投稿データ・クエリ操作
  require_once(dirname(__FILE__).'/functions/models.php');

  // SEOメタ情報出力
  require_once(dirname(__FILE__).'/functions/seo.php');

  // ウィジェット出力
  require_once(dirname(__FILE__).'/functions/widgets.php');

  // メール送信処理
  require_once(dirname(__FILE__).'/functions/mail.php');
  
  // REST API設定
  require_once(dirname(__FILE__).'/functions/api.php');

  // コメント入出力
  require_once(dirname(__FILE__).'/functions/comment.php');

  // Ajax処理
  require_once(dirname(__FILE__).'/functions/ajax.php');

?>