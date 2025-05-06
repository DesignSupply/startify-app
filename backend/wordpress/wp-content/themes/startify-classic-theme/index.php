<?php get_header(); ?>
      <main>
<?php get_template_part('components/page-header'); ?>
        <article>
          <section>
            <h1>
              <a href="<?php echo esc_url(home_url()); ?>">
                <img src="<?php echo get_template_directory_uri(); ?>/screenshot.png" alt="<?php echo get_bloginfo('name'); ?>">
              </a>
            </h1>
          </section>
        </article>
        <article>
          <section>
            <h2>
              ページコンテンツ（トップページ）
            </h2>
            <!-- カスタム投稿 サブループ start -->
              <?php
                $args = array(
                  'post_status' => 'publish',
                  'post_type' => 'blog',
                  'posts_per_page' => 5,
                  // 'ignore_sticky_posts' => false,
                  // 'tax_query' => array(
                  //   'relation' => 'AND',
                  //   array(),
                  //   array()
                  // ),
                  // 'meta_query' => array(
                  //   'relation' => 'AND',
                  //   array(),
                  //   array()
                  // ),
                  // 'date_query' => array(
                  //   array(),
                  //   array()
                  // ),
                  // 's' => '',
                  // 'order' => 'DESC',
                  // 'orderby' => array()
                );
                $the_query = new WP_Query($args);
                if($the_query->have_posts()):
              ?>
                <?php while($the_query->have_posts()): $the_query->the_post(); ?>
<?php get_template_part('components/loop'); ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
              <?php else: ?>
                <!-- 投稿がない場合 -->
                <p>該当する投稿記事がありません</p>
              <?php endif; ?>
            <!-- カスタム投稿 サブループ end -->
          </section>
        </article>
        <!-- article end -->
        <article>
          <section>
            <h2>
              Ajax読み込みコンテンツ
            </h2>
            <!-- Ajaxローディング サブループ start -->
            <?php
              // 初期表示件数
              $default_posts = 3;
              // Ajaxローディング対象クエリ
              $args = array(
                'posts_per_page' => $default_posts,
                'post_type' => 'blog'
              );
              $default_ajax_loading_query = new WP_Query($args);
              if($default_ajax_loading_query->have_posts()):
            ?>
            <div id="infinite_loading_container">
              <!-- Ajaxローディング 追加読み込み記事 -->
              <?php while($default_ajax_loading_query->have_posts()): $default_ajax_loading_query->the_post(); ?>
<?php get_template_part('components/loop'); ?>
              <?php endwhile; ?>
            </div>
            <?php wp_reset_postdata(); ?>
            <?php endif; ?>
            <!-- Ajaxローディング サブループ end -->
            <?php if($default_ajax_loading_query->found_posts > $default_posts): ?>
              <!-- Ajaxローディング 追加読み込みボタン -->
              <button type="button" id="infinite_loading_button">もっと読み込む</button>
            <?php endif; ?>
          </section>
        </article>
        <!-- article end -->
        <!-- article end -->
        <article>
          <section>
            <h2>
              Ajax投稿デモ
            </h2>
            <!-- Ajax投稿デモ サブループ start -->
            <?php 
              $args = array(
                'post_status' => 'publish',
                'post_type' => 'blog',
                'posts_per_page' => 3,
              );
              $ajax_post_sample_query = new WP_Query($args);
              if($ajax_post_sample_query->have_posts()):
            ?>
              <?php while($ajax_post_sample_query->have_posts()): $ajax_post_sample_query->the_post(); ?>
                <button type="button" data-post-id="<?php echo get_the_ID(); ?>" class="ajax-post-submit">AjaxでPOST送信</button>
<?php get_template_part('components/loop'); ?>
              <?php endwhile; ?>
              <?php wp_reset_postdata(); ?>
            <?php endif; ?>
            <!-- Ajax投稿デモ サブループ end -->
          </section>
        </article>
<?php get_sidebar(); ?>
      </main>
      <!-- main end -->
<?php get_template_part('components/breadcrumb'); ?>
<?php get_footer(); ?>