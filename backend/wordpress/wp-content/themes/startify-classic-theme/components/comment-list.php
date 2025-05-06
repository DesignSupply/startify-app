<?php if(have_comments()): ?>
  <h1>
    コメント一覧
  </h1>
  <p>
    <?php
      $count = get_comments(
        array(
          'status' => 'approve',
          'post_id' => get_the_ID(),
          'type' => 'comment',
          'count' => true
        )
      );
      echo $count.'件のコメント';
    ?>
  </p>
  <!-- comment count end -->
  <ul id="comments">
    <?php 
      $args = array(
        'avatar_size' => 48,
        'style' => 'ul',
        'type' => 'comment',
        'callback' => 'mytheme_comments'
      );
      wp_list_comments($args);
    ?>
  </ul>
  <!-- comment content end -->
  <?php 
    if(get_comment_pages_count() > 1) {
      previous_comments_link('&laquo;&nbsp;前のコメント');
      next_comments_link('次のコメント&nbsp;&raquo;');
    }
  ?>
  <!-- comment pager end -->
  <?php if(get_comments_number() - $count > 0): ?>
    <p>
      <?php 
        echo get_comments_number() - $count.'件のトラックバック';
      ?>
    </p>
    <ul>
      <?php 
        $args = array(
          'style' => 'div',
          'type' => 'pings',
          'callback' => 'mytheme_pings'
        );
        wp_list_comments($args);
      ?>
    </ul>
  <?php endif; ?>
  <!-- trackback & pingback end -->
<?php endif; ?>
<!-- comment list end -->