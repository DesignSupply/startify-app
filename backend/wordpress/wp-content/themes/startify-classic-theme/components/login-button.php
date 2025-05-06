<?php
  if(is_user_logged_in()) {
    echo '<a href="'.wp_logout_url().'">ログアウト</a>';
  } else {
    echo '<a href="'.wp_login_url().'">ログイン</a>';
  }
?>
<!-- login button end -->