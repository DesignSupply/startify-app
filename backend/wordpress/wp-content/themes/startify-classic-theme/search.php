<?php get_header(); ?>
      <main>
<?php get_template_part('components/page-header'); ?>        
        <article>
          <section>
            <h1>
              ページコンテンツ（検索結果ページ）
            </h1>
            <?php if($wp_query->found_posts > 0): ?>
              <p>
                「<?php echo get_query_var('s'); ?>」の検索結果、「<?php echo $wp_query->found_posts; ?>」件が該当しました。
              </p>
            <?php endif; ?>
            <!-- 検索結果ページ メインループ start -->
              <?php if(have_posts()): ?>
                <?php while(have_posts()): the_post(); ?>
<?php get_template_part('components/loop'); ?>   
                <?php endwhile; ?>
              <?php else: ?>
                <!-- 投稿がない場合 -->
                <p>投稿記事がありません</p>
              <?php endif; ?>
            <!-- 検索結果ページ メインループ end -->
          </section>
        </article>
        <!-- article end -->
<?php get_template_part('components/pagenation'); ?>      
<?php get_sidebar(); ?>
      </main>
      <!-- main end -->
<?php get_template_part('components/breadcrumb'); ?>
<?php get_footer(); ?>