<?php

  // カスタム投稿（blog）の全件記事取得の独自エンドポイント追加
  require_once(dirname(__FILE__).'/api/blog-archive.php');

  // カスタム投稿（blog）の個別記事取得の独自エンドポイント追加
  require_once(dirname(__FILE__).'/api/blog-single.php');

  // 投稿対象のキーワード検索結果全件記事取得の独自エンドポイント追加
  require_once(dirname(__FILE__).'/api/search.php');

?>