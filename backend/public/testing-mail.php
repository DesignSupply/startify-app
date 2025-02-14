<?php
  $to = 'test@example.com';
  $subject = 'テストメール件名';
  $message = "テストメールの本文です\r\n";
  $headers = "From: webmaster@example.com\r\n" .
    "Reply-To: webmaster@example.com\r\n" .
    "Content-Type: text/plain; charset=UTF-8\r\n" .
    "Content-Transfer-Encoding: 8bit\r\n" .
    "X-Mailer: PHP/" . phpversion();
  if (mail($to, $subject, $message, $headers)) {
    echo 'メール送信成功';
  } else {
    echo 'メール送信失敗';
  }
?>
