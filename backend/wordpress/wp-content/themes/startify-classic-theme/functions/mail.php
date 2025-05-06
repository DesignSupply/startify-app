<?php

  // レビュー待ち投稿のメール通知
  function alert_pending($new_status, $old_status, $post) {
    if($old_status != 'pending' && $new_status === 'pending') {
      $siteName = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
      $adminEmail = wp_specialchars_decode(get_option('admin_email'), ENT_QUOTES);
      $postTitle = wp_specialchars_decode($post->post_title, ENT_QUOTES);
      $editLink = wp_specialchars_decode(get_edit_post_link($post->ID), ENT_QUOTES);
      $addHeader = "From: ".$siteName."<".$adminEmail.">\n";
      $addHeader .= "Reply-to: ".$adminEmail;
      $to = $adminEmail;
      $subject = "【{$siteName}】承認待ち投稿のお知らせ";
      $message = <<< EOD
【{$siteName} 】ウェブサイト管理者様
サイト寄稿者より下記の承認待ちの記事が投稿されました。確認してください。

記事タイトル：{$postTitle}
編集画面リンク：{$editLink}

EOD;
      wp_mail($to, $subject, $message, $headers);
    }
  }
  add_action('transition_post_status', 'alert_pending', 10, 3);

?>