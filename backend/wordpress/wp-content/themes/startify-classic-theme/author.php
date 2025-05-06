<?php get_header(); ?>
      <main>
<?php get_template_part('components/page-header'); ?>        
        <article>
          <section>
            <h1>
              ページコンテンツ（投稿者アーカイブページ）
            </h1>
            <p>
              「<?php echo get_query_var('author_name'); ?>」が投稿した記事の一覧です。
            </p>
            <!-- 投稿者アーカイブページ メインループ start -->
              <?php if(have_posts()): ?>
                <?php while(have_posts()): the_post(); ?>
<?php get_template_part('components/loop'); ?>   
                <?php endwhile; ?>
              <?php else: ?>
                <!-- 投稿がない場合 -->
                <p>投稿記事がありません</p>
              <?php endif; ?>
            <!-- 投稿者アーカイブページ メインループ end -->
          </section>
        </article>
        <!-- article end -->
<?php get_template_part('components/pagenation'); ?>      
<?php get_sidebar(); ?>
      </main>
      <!-- main end -->
<?php get_template_part('components/breadcrumb'); ?>
<?php get_footer(); ?>