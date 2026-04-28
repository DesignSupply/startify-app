<?php

  // コメントコールバック
  function mytheme_comments($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment; 
    ?>
      <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>">
          <dl>
            <div>
              <dt>
                <?php echo get_avatar($comment, $args['avatar_size']); ?>
              </dt>
              <dd>
                <ul>
                  <li>
                    <?php printf('<cite class="fn">%s</cite>', get_comment_author_link()); ?>
                  </li>
                  <!-- comment author link end -->
                  <li>
                    <?php
                      $commentIdParent = $comment->comment_parent;
                      if($commentIdParent != 0):
                    ?>
                      <a href="<?php echo esc_url(get_comment_link($commentIdParent)); ?>">
                        <?php echo get_comment_author($commentIdParent); ?>さんへの返信
                      </a>
                    <?php else: ?>
                      <a href="#top">「<?php echo esc_html(get_the_title()); ?>」へのコメント</a>
                    <?php endif; ?>
                    <?php
                      if($comment->comment_approved == '0') {
                        echo '<span>このコメントは承認待ちです。</span>';
                      }
                    ?>
                  </li>
                  <!-- comment title end -->
                  <li>
                    <a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>">
                      <?php printf(__('%1$s at %2$s'), get_comment_date(), get_comment_time()); ?>
                    </a>
                    <?php edit_comment_link('［編集］','',''); ?>
                  </li>
                  <!-- comment date end -->
                </ul>
                <!-- comment meta end -->
                <div>
                  <?php comment_text(); ?>
                </div>
                <!-- comment text end -->
                <div>
                  <?php 
                    comment_reply_link(
                      array_merge( 
                        $args, 
                        array(
                          'reply_text'=>'このコメントに返信',
                          // 'add_below' =>$add_below,
                          'depth'   =>$depth,
                          'max_depth' =>$args['max_depth']
                        )
                      )
                    );
                  ?>
                </div>
                <!-- comment reply end -->
              </dd>
            </div>
          </dl>
        </div>
        <!-- comment ID end -->
    <?php
  }

  // トラックバック・ピンバックコールバック
  function mytheme_pings($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    ?>
      <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>">
          <ul>
            <li>
              <?php printf(__('%s'), get_comment_author_link()); ?>
            <li>
            <!-- comment title end -->
            <li>
              <?php printf(__('%1$s'), get_comment_date()); ?>
              <?php edit_comment_link(__('［編集］'),'',''); ?>
            <li>
            <!-- comment date end -->
            <li>
              <?php comment_text(); ?>
            <li>
            <!-- comment text end -->
          </ul>
        </div>
        <!-- comment ID end -->
    <?php
  }

?>