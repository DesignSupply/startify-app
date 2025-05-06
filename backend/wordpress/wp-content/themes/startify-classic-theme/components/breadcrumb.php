<ul>
  <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">  
    <a href="<?php echo esc_url(home_url()); ?>" itemprop="item"><span itemprop="name">HOME</span></a>
    <meta itemprop="position" content="1" />
  </li>
  <?php
    if(!is_front_page() && !is_home()) {
      if(is_page()) {
        // 固定ページ
        if(get_parent_page_ID()) {
          $postId = $post->ID;
          $parentPostArry = array_reverse(get_post_ancestors($post)); 
          // 固定ページ（親・先祖ページ）
          foreach($parentPostArry as $index => $parentsPostId) {
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">'; 
            echo '<a href="'.esc_url(get_the_permalink($parentsPostId)).'" itemprop="item"><span itemprop="name">'.esc_html(get_the_title($parentsPostId)).'</span></a>';
            echo '<meta itemprop="position" content="'.($index + 2).'" /></li>';
            $parentPages = $index + 1;
          }
          // 固定ページ（子ページ）
          echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
          echo '<a href="'.esc_url(get_the_permalink($postId)).'" itemprop="item"><span itemprop="name">'.esc_html(get_the_title($postId)).'</span></a>';
          echo '<meta itemprop="position" content="'.($parentPages + 2).'" /></li>';
        } else {
          echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
          echo '<a href="'.esc_url(get_the_permalink()).'" itemprop="item"><span itemprop="name">'.esc_html(single_post_title('',false)).'</span></a>';
          echo '<meta itemprop="position" content="2" /></li>';
        }
      } else if(is_post_type_archive()) {
        $postTypeObject = get_post_type_object(get_query_var('post_type'));
        $postTypeName = $postTypeObject->labels->name;
        if(is_date_archive()) {
          // 日付アーカイブページ
          echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
          echo '<a href="'.get_post_type_archive_link(get_query_var('post_type')).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
          echo '<meta itemprop="position" content="2" /></li>';
          echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
          echo '<a href="'.esc_url(home_url('/')).$wp_query->query['year'].'/'.$wp_query->query['monthnum'].'/?post_type='.$wp_query->query['post_type'].'"><span itemprop="name">'.get_query_var('year').'年'.get_query_var('monthnum').'月の投稿一覧</span></a>';
          echo '<meta itemprop="position" content="3" /></li>';
        } else {
          // アーカイブページ
          echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
          echo '<a href="'.get_post_type_archive_link(get_query_var('post_type')).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
          echo '<meta itemprop="position" content="2" /></li>';
        }
      } else if(is_tax()) {
        // タクソノミーページ
        $postTypeObject = get_post_type_object(get_post_type());
        $postTypeName = $postTypeObject->labels->name;
        $taxonomySlug = get_query_var('taxonomy');
        $termName = urldecode(get_query_var('term'));
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="'.get_post_type_archive_link(get_post_type()).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
        echo '<meta itemprop="position" content="2" /></li>';
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="'.get_term_link($termName, $taxonomySlug).'" itemprop="item"><span itemprop="name">'.esc_html(single_term_title('',false)).'の記事一覧</span></a>';
        echo '<meta itemprop="position" content="3" /></li>';
      } else if(is_singular(get_post_type())) {
        // シングルページ
        $postTypeObject = get_post_type_object(get_post_type());
        $postTypeName = $postTypeObject->labels->name;
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="'.get_post_type_archive_link(get_post_type()).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
        echo '<meta itemprop="position" content="2" /></li>';
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="'.esc_url(get_the_permalink()).'" itemprop="item"><span itemprop="name">'.esc_html(single_post_title('',false)).'</span></a>';
        echo '<meta itemprop="position" content="3" /></li>';
      } else if(is_author()) {
        // 投稿者アーカイブページ
        global $post;
        $author = get_userdata($post->post_author);
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="'.get_author_posts_url(get_the_author_meta('ID')).'" itemprop="item"><span itemprop="name">'.$author->display_name.'の記事一覧</span></a>';
        echo '<meta itemprop="position" content="2" /></li>';
      } else if(is_search()) {
        // 検索結果ページ
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span itemprop="name">キーワード検索結果</span><meta itemprop="position" content="2" /></li>';
      } else if(is_404()) {
        // 404ページ
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span itemprop="name">Page Not Found</span><meta itemprop="position" content="2" /></li>';
      }
    }
  ?>
</ul>
<!-- breadcrumb list end -->