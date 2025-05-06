<article>
  <section>
    <h1>
      ページタイトル：
      <?php
        if(is_front_page() || is_home()) {
          // トップページ
          echo get_bloginfo('name');
        } else if(is_page()) {
          // 固定ページ
          echo esc_html(single_post_title());
        } else if(is_post_type_archive(get_post_type())) {
          $postTypeObject = get_post_type_object(get_post_type());
          $postTypeName = $postTypeObject->labels->name;
          if(is_date_archive()) {
            // 日付アーカイブページ
            $postYaer = get_query_var('year');
            $postMonth = get_query_var('monthnum');
            echo $postTypeName.'｜'.$postYaer.'年'.$postMonth.'月';
          } else {
            // アーカイブページ
            echo $postTypeName;
          }
        } else if(is_singular(get_post_type(get_the_ID()))) {
          // シングルページ
          $postTypeObject = get_post_type_object(get_post_type());
          $postTypeName = $postTypeObject->labels->name;
          echo $postTypeName;
        } else if(is_tax()) {
          // タクソノミーページ
          $postTypeObject = get_post_type_object(get_post_type());
          $postTypeName = $postTypeObject->labels->name;
          $termName = esc_html(single_term_title('',false));
          echo $postTypeName.'｜'.$termName;
        } else if(is_author()) {
          // 投稿者アーカイブページ
          global $post;
          $author = get_userdata($post->post_author);
          echo $author->display_name.'の記事';
        } else if(is_search()) {
          // 検索結果ページ
          echo 'キーワード検索結果';
        } else if(is_404()) {
          // 404ページ
          echo 'Page Not Found';
        }
      ?>
    </h1>
  </section>
</article>
<!-- page header end -->