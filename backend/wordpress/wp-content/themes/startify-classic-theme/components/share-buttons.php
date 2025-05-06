<?php
  $siteUrl = empty($_SERVER['HTTPS']) ? "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  $twitterName = 'ツイッターアカウント名';
?>
<ul>
  <!-- Facebook -->
  <li>
    <a href="http://www.facebook.com/share.php?u=<?php echo esc_url($siteUrl); ?>&t=<?php echo esc_html(get_the_title()); ?>" target="_blank" rel="nofollow noopener noreferrer">
      シェア
    </a>
  </li>
  <!-- Twitter -->
  <li>
    <a href="https://twitter.com/intent/tweet?text=<?php echo esc_html(get_the_title()); ?>&url=<?php echo esc_url($siteUrl); ?>&related=<?php echo $twitterName; ?>" target="_blank" rel="nofollow noopener noreferrer">
      ツイート
    </a>
  </li>
  <!-- Twitter -->
  <li>
    <a href="http://b.hatena.ne.jp/entry/<?php echo esc_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" target="_blank" rel="nofollow noopener noreferrer" title="このエントリーをはてなブックマークに追加">
      はてブ
    </a>
  </li>
  <!-- Poket -->
  <li>
    <a href="https://getpocket.com/edit?url=<?php echo esc_url($siteUrl); ?>" target="_blank" rel="nofollow noopener noreferrer">
      Poket
    </a>
  </li>
  <!-- Linkedin -->
  <li>
    <a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url($siteUrl); ?>" target="_blank" rel="nofollow noopener noreferrer">
      シェア
    </a>
  </li>
  <!-- Feedly -->
  <li>
    <a href="https://feedly.com/i/subscription/feed/<?php echo esc_url($siteUrl); ?>feed/" target="_blank" rel="nofollow noopener noreferrer">
      Feedly
    </a>
  </li>
  <!-- LINE -->
  <li>
    <a href="http://line.me/R/msg/text/?<?php echo esc_html(get_the_title()); ?><?php echo esc_url($siteUrl); ?>" target="_blank" rel="nofollow noopener noreferrer">
      LINE
    </a>
  </li>
</ul>
<!-- share buttons end -->