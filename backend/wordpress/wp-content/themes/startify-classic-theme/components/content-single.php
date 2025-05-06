<!-- 投稿ID -->
<?php echo get_the_ID(); ?>
<br>

<!-- 投稿タイトル -->
<?php echo esc_html(get_the_title()); ?>
<br>

<!-- パーマリンク -->
<a href="<?php echo esc_url(get_the_permalink()); ?>"><?php echo esc_url(get_the_permalink()); ?></a>
<br>

<!-- ターム（複数対応） -->
<?php echo get_the_term_list($post->ID, 'blog_category', 'Category：', ','); ?>
<br>
<?php echo get_the_term_list($post->ID, 'blog_tag', 'Tags：', ','); ?>
<br>

<!-- 投稿日 -->
<?php echo get_the_date('Y/m/d（D）'); ?>
<br>

<!-- 最終更新日 -->
<?php
  if (get_the_modified_date('Y.m.d') !== get_the_time('Y.m.d')) {
    echo '<time datetime="'.post_modified('Y-m-d').'">'.post_modified('Y.n.j').'</time>';
  }
?>
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

<!-- 本文 -->
<?php the_content('More…'); ?>
<br>

<!-- 分割ページ用リンク -->
<?php
  $arg = array(
    'before' => '<ul>',
    'after' => '</ul>',
    'link_before' => '<li>',
    'link_after' => '</li>',
  );
  wp_link_pages($arg);
?>
<br>

<!-- 投稿者名 -->
<?php echo get_the_author(); ?>
<br>

<!-- 投稿者アバター画像 -->
<?php echo get_avatar(get_the_author_meta('ID')); ?>
<br>

<!-- 投稿者アバター紹介文 -->
<?php echo esc_html(get_the_author_meta('description')); ?>
<br>

<!-- 投稿者アーカイブページリンク -->
<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>">
  <?php echo get_author_posts_url(get_the_author_meta('ID')); ?>
</a>
<br>

<!-- 編集ページリンク -->
<?php
  if(is_user_logged_in()) {
    echo '<a href="'.get_edit_post_link().'">編集する</a>';
  }
?>
<br>