<?php
  // デフォルト初期表示投稿数
  $default_show_posts = 3;

  // デフォルト追加投稿数
  $default_loading_posts = 3;

  // ローディング対象となる投稿タイプ
  $target_post_type = 'blog';

  // WordPress関数用ファイル読み込み
  require_once(dirname(__FILE__).'/../../../../../wp-load.php');

  // フロント側からの表示件数情報取得
  $offset_value = isset($_POST['currently_loaded_count']) ? $_POST['currently_loaded_count'] : $default_show_posts;
  $loading_count = isset($_POST['additional_loading_count']) ? $_POST['additional_loading_count'] : $default_loading_posts;

  // 追加のAjax読み込み用クエリ
  $ajax_loading_query = new WP_Query(
    array(
      'post_type' => $target_post_type,
      'posts_per_page' => (int)$loading_count,
      'offset' => (int)$offset_value
    )
  );
  $posts_count = $ajax_loading_query->found_posts;
  if($ajax_loading_query->have_posts()):
?>
  <?php 
    while($ajax_loading_query->have_posts()): 
    $ajax_loading_query->the_post(); 
  ?>
    <?php 
      $posts = $ajax_loading_query->posts;
      $remaining_count = $posts_count - $offset_value;
      $contents = array();
      foreach ($posts as $post) {
        $html = '<br>';
        $html .= esc_html(get_the_title($post->ID));
        $html .= '<br>';
        $html .= '<a href="'.esc_url(get_the_permalink($post->ID)).'">'.esc_url(get_the_permalink($post->ID)).'</a>';
        $html .= '<br>';
        if(has_post_thumbnail($post->ID)) {
          $html .= '<img src="'.get_the_post_thumbnail_url($post->ID, 'full').'" alt="'.wp_strip_all_tags(esc_html(get_the_title($post->ID))).'">';
        } else {
          $html .= '<img src="********.jpg" alt="'.wp_strip_all_tags(esc_html(get_the_title($post->ID))).'">';
        }
        $html .= '<br>';
        if(post_password_required()) {
          $html .= 'この投稿はパスワードで保護されています';
        } else {
          $html .= esc_html(get_the_excerpt($post->ID));
        }
        $html .= '<br>';
        array_push($contents, $html);
      }
    ?>
  <?php endwhile; ?>
<?php 
  $loading_complete = false;
  if($remaining_count <= $loading_count) {
    $loading_complete = true;
  }
  echo json_encode(
    array(
      'complete'=>$loading_complete,
      'content'=>$contents,
      'total_posts'=>$posts_count,
      'loading_posts_count'=>$loading_count,
      'loading_posts_start'=>$offset_value,
      'remaining_posts'=>$remaining_count
    )
  );
  endif; 
?>
<?php wp_reset_postdata(); ?>