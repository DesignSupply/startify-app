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

  if(wp_is_block_theme()) {

    // テンプレートヘルパー・クエリ（ブロックテーマ有効時のみ）
    foreach(glob(dirname(__FILE__).'/functions/template-helpers/*.php') as $file) {
      require_once($file);
    }

    // サブクエリ用ゲッター関数
    foreach(glob(dirname(__FILE__).'/functions/queries/*.php') as $file) {
      require_once($file);
    }

  }

?>
