<?php get_header(); ?>
      <main>
<?php get_template_part('components/page-header'); ?>        
        <article>
          <section>
            <h1>
              ページコンテンツ（固定ページ）
            </h1>
            <!-- 固定ページ メインループ start -->
              <?php if(have_posts()): ?>
                <?php while(have_posts()): the_post(); ?>
<?php get_template_part('components/content-page'); ?>  
                <?php endwhile; ?>
              <?php else: ?>
                <!-- 投稿がない場合 -->
                <p>投稿記事がありません</p>
              <?php endif; ?>
            <!-- 固定ページ メインループ end -->
          </section>
        </article>
        <!-- article end -->
<?php get_template_part('components/pagenation'); ?>      
<?php get_sidebar(); ?>
      </main>
      <!-- main end -->
<?php get_template_part('components/breadcrumb'); ?>
<?php get_footer(); ?>