<?php

  // ページヘッダータイトル出力
  function shortcode_page_header() {
    global $post;
    $title = '';
    if(is_front_page() || is_home()) {
      $title = get_bloginfo('name');
    } else if(is_page()) {
      $title = esc_html(single_post_title('', false));
    } else if(is_post_type_archive(get_post_type())) {
      $postTypeObject = get_post_type_object(get_post_type());
      $postTypeName = $postTypeObject->labels->name;
      if(is_date_archive()) {
        $postYear = get_query_var('year');
        $postMonth = get_query_var('monthnum');
        $title = $postTypeName . '｜' . $postYear . '年' . $postMonth . '月';
      } else {
        $title = $postTypeName;
      }
    } else if(is_singular(get_post_type(get_the_ID()))) {
      $postTypeObject = get_post_type_object(get_post_type());
      $title = $postTypeObject->labels->name;
    } else if(is_tax()) {
      $postTypeObject = get_post_type_object(get_post_type());
      $postTypeName = $postTypeObject->labels->name;
      $termName = esc_html(single_term_title('', false));
      $title = $postTypeName . '｜' . $termName;
    } else if(is_author()) {
      $author = get_userdata($post->post_author);
      $title = $author->display_name . 'の記事';
    } else if(is_search()) {
      $title = 'キーワード検索結果';
    } else if(is_404()) {
      $title = 'Page Not Found';
    }
    ob_start();
    ?>
    <h1>ページタイトル：<?php echo $title; ?></h1>
    <?php
    return ob_get_clean();
  }
  add_shortcode('startify_page_header', 'shortcode_page_header');

  // パンくずリスト出力
  function shortcode_breadcrumb() {
    global $post, $wp_query;
    ob_start();
    ?>
    <ul itemscope itemtype="https://schema.org/BreadcrumbList">
      <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <a href="<?php echo esc_url(home_url()); ?>" itemprop="item"><span itemprop="name">HOME</span></a>
        <meta itemprop="position" content="1" />
      </li>
      <?php
        if(!is_front_page() && !is_home()) {
          if(is_page()) {
            if(get_parent_page_ID()) {
              $postId = $post->ID;
              $parentPostArray = array_reverse(get_post_ancestors($post));
              foreach($parentPostArray as $index => $parentsPostId) {
                echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
                echo '<a href="'.esc_url(get_the_permalink($parentsPostId)).'" itemprop="item"><span itemprop="name">'.esc_html(get_the_title($parentsPostId)).'</span></a>';
                echo '<meta itemprop="position" content="'.($index + 2).'" /></li>';
                $parentPages = $index + 1;
              }
              echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
              echo '<a href="'.esc_url(get_the_permalink($postId)).'" itemprop="item"><span itemprop="name">'.esc_html(get_the_title($postId)).'</span></a>';
              echo '<meta itemprop="position" content="'.($parentPages + 2).'" /></li>';
            } else {
              echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
              echo '<a href="'.esc_url(get_the_permalink()).'" itemprop="item"><span itemprop="name">'.esc_html(single_post_title('', false)).'</span></a>';
              echo '<meta itemprop="position" content="2" /></li>';
            }
          } else if(is_post_type_archive()) {
            $postTypeObject = get_post_type_object(get_query_var('post_type'));
            $postTypeName = $postTypeObject->labels->name;
            if(is_date_archive()) {
              echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
              echo '<a href="'.get_post_type_archive_link(get_query_var('post_type')).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
              echo '<meta itemprop="position" content="2" /></li>';
              echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
              echo '<a href="'.esc_url(home_url('/')).$wp_query->query['year'].'/'.$wp_query->query['monthnum'].'/?post_type='.$wp_query->query['post_type'].'"><span itemprop="name">'.get_query_var('year').'年'.get_query_var('monthnum').'月の投稿一覧</span></a>';
              echo '<meta itemprop="position" content="3" /></li>';
            } else {
              echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
              echo '<a href="'.get_post_type_archive_link(get_query_var('post_type')).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
              echo '<meta itemprop="position" content="2" /></li>';
            }
          } else if(is_tax()) {
            $postTypeObject = get_post_type_object(get_post_type());
            $postTypeName = $postTypeObject->labels->name;
            $taxonomySlug = get_query_var('taxonomy');
            $termName = urldecode(get_query_var('term'));
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="'.get_post_type_archive_link(get_post_type()).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
            echo '<meta itemprop="position" content="2" /></li>';
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="'.get_term_link($termName, $taxonomySlug).'" itemprop="item"><span itemprop="name">'.esc_html(single_term_title('', false)).'の記事一覧</span></a>';
            echo '<meta itemprop="position" content="3" /></li>';
          } else if(is_singular(get_post_type())) {
            $postTypeObject = get_post_type_object(get_post_type());
            $postTypeName = $postTypeObject->labels->name;
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="'.get_post_type_archive_link(get_post_type()).'" itemprop="item"><span itemprop="name">'.$postTypeName.'</span></a>';
            echo '<meta itemprop="position" content="2" /></li>';
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="'.esc_url(get_the_permalink()).'" itemprop="item"><span itemprop="name">'.esc_html(single_post_title('', false)).'</span></a>';
            echo '<meta itemprop="position" content="3" /></li>';
          } else if(is_author()) {
            $author = get_userdata($post->post_author);
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="'.get_author_posts_url(get_the_author_meta('ID')).'" itemprop="item"><span itemprop="name">'.$author->display_name.'の記事一覧</span></a>';
            echo '<meta itemprop="position" content="2" /></li>';
          } else if(is_search()) {
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<span itemprop="name">キーワード検索結果</span><meta itemprop="position" content="2" /></li>';
          } else if(is_404()) {
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<span itemprop="name">Page Not Found</span><meta itemprop="position" content="2" /></li>';
          }
        }
      ?>
    </ul>
    <?php
    return ob_get_clean();
  }
  add_shortcode('startify_breadcrumb', 'shortcode_breadcrumb');

  // コピーライト出力
  function shortcode_copyright() {
    return '<span>&copy;&nbsp;' . date('Y') . '&nbsp;' . get_bloginfo('name') . '</span>';
  }
  add_shortcode('startify_copyright', 'shortcode_copyright');

  // ページネーション出力
  function shortcode_pagination() {
    global $wp_query;
    $pages = $wp_query->max_num_pages;
    $paged = get_query_var('paged');
    $paged == 0 ? $paged = 1 : $paged = get_query_var('paged');
    $showItems = 3;
    if($pages == 0 || $pages == 1) {
      return '';
    }
    ob_start();
    echo '<ul>';
    if($paged != 1) {
      echo '<li><a href="'.get_pagenum_link(1).'">&lt;&lt;</a></li>';
      echo '<li><a href="'.get_pagenum_link($paged - 1).'">&lt;</a></li>';
    }
    for($index = 0; $index <= $showItems - 1; $index++) {
      if($index == 0) {
        echo '<li><a href="'.get_pagenum_link($paged).'">'.$paged.'</a></li>';
      } else {
        if($paged + $index <= $pages) {
          echo '<li><a href="'.get_pagenum_link($paged + $index).'">'.($paged + $index).'</a></li>';
        }
      }
    }
    if($paged != $pages) {
      echo '<li><a href="'.get_pagenum_link($paged + 1).'">&gt;</a></li>';
      echo '<li><a href="'.get_pagenum_link($pages).'">&gt;&gt;</a></li>';
    }
    echo '</ul>';
    return ob_get_clean();
  }
  add_shortcode('startify_pagination', 'shortcode_pagination');

  // 検索結果カウント表示
  function shortcode_search_count() {
    global $wp_query;
    if($wp_query->found_posts > 0) {
      return '<p>「' . esc_html(get_query_var('s')) . '」の検索結果、「' . $wp_query->found_posts . '」件が該当しました。</p>';
    }
    return '';
  }
  add_shortcode('startify_search_count', 'shortcode_search_count');

  // タクソノミーページ説明文表示
  function shortcode_taxonomy_description() {
    return '<p>「' . esc_html(single_term_title('', false)) . '」の記事一覧</p>';
  }
  add_shortcode('startify_taxonomy_description', 'shortcode_taxonomy_description');

  // 投稿者アーカイブ説明文表示
  function shortcode_author_description() {
    return '<p>「' . esc_html(get_query_var('author_name')) . '」が投稿した記事の一覧です。</p>';
  }
  add_shortcode('startify_author_description', 'shortcode_author_description');

  // 日付アーカイブ説明文表示
  function shortcode_date_archive_description() {
    if(is_date_archive()) {
      return '<p>' . get_query_var('year') . '年' . get_query_var('monthnum') . '月の記事一覧</p>';
    }
    return '';
  }
  add_shortcode('startify_date_archive_description', 'shortcode_date_archive_description');

  // 編集リンク出力（ログイン時のみ）
  function shortcode_edit_link() {
    if(is_user_logged_in()) {
      return '<a href="' . get_edit_post_link() . '">編集する</a>';
    }
    return '';
  }
  add_shortcode('startify_edit_link', 'shortcode_edit_link');

?>
