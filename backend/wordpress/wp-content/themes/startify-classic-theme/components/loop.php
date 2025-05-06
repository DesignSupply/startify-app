<!-- 記事インデックス番号 -->
<?php
  if($wp_query->query) {
    $paged = get_query_var('paged');
    $perPage = get_query_var('posts_per_page');
    $paged === 0 ? $paged = 1 : $paged = get_query_var('paged');
    $count = $wp_query->current_post + 1; 
    echo '#'.(($paged - 1) * $perPage + $count);
  }
?>
<br>

<!-- 投稿タイトル -->
<?php echo esc_html(get_the_title()); ?>
<br>

<!-- パーマリンク -->
<a href="<?php echo esc_url(get_the_permalink()); ?>"><?php echo esc_url(get_the_permalink()); ?></a>
<br>

<!-- サムネイル画像 -->
<?php
  if(has_post_thumbnail()) {
    echo '<img src="'.get_the_post_thumbnail_url(get_the_ID(), 'full').'" alt="'.wp_strip_all_tags(esc_html(get_the_title())).'">';
  } else {
    echo '<img src="********.jpg" alt="'.wp_strip_all_tags(esc_html(get_the_title())).'">';
  }
?>
<br>

<!-- 抜粋 -->
<?php the_excerpt(); ?>
<br>