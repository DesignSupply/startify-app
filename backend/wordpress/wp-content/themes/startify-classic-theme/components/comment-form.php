<?php
  $args = array(
    'fields' => array(
      'author' => 
        '<label for="author">'.__('Name').($req ? '<span>【必須】</span>' : '').'</label>'
        .'<input id="author" name="author" type="text" value="'.esc_attr($commenter['comment_author']).'" placeholder="お名前・ハンドルネーム">',
      'email' => 
      '<label for="email">'.__('Email').($req ? '<span>【必須】</span>' : '').'</label>'
      .'<input id="email" name="email" type="email" value="'.esc_attr($commenter['comment_author_email']).'" placeholder="メールアドレス">'
    ),
    'comment_field' => '<label for="comment">コメント<span>【必須】</span></label><textarea class="__text-input" id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="コメントを追加">' . '</textarea>',
    'title_reply' => 'コメントフォーム',
    'comment_notes_before' => '<p>記事に関するご質問やご意見などありましたら下記のコメントフォームよりお気軽に投稿ください。なおメールアドレスは公開されませんのでご安心ください。</p>',
    'comment_notes_after' => '<p>内容に問題なければ、お名前・ハンドルネームとメールアドレスを入力いただき、下記の「コメントを送信」ボタンを押してください。</p>',
    'label_submit' => 'コメントを送信',

  );
  comment_form($args);
?>
<!-- comment form end -->