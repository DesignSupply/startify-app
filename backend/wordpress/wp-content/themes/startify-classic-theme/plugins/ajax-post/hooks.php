<?php

  // Ajax更新処理
  function create_localize_script() {
    $data = array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('ajax_nonce_action')
    );
    wp_localize_script('ajax-post-js', 'ajax_object', $data);
  }
  add_action('wp_enqueue_scripts', 'create_localize_script');

  // ログインユーザー用
  function ajax_post() {
    check_ajax_referer('ajax_nonce_action', 'nonce', ture);
    echo json_encode(
      array(
        'post_id'=>isset($_POST['post_id']) ? $_POST['post_id'] : null,
      )
    );
    wp_die();
  }
  add_action( 'wp_ajax_ajax_post_action', 'ajax_post' );

  // 非ログインユーザー用
  function ajax_post_nopriv() {
    check_ajax_referer('ajax_nonce_action', 'nonce', ture);
    echo json_encode(
      array(
        'post_id'=>isset($_POST['post_id']) ? $_POST['post_id'] : null,
      )
    );
    wp_die();
  }
  add_action( 'wp_ajax_nopriv_ajax_post_action', 'ajax_post_nopriv' );

?>