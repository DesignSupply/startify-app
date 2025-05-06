<?php get_header(); ?>
      <main>
<?php get_template_part('components/page-header'); ?>        
        <article>
          <section>
            <h1>
              ページコンテンツ（タクソノミーページ）
            </h1>
            <p>
              「<?php echo esc_html(single_term_title('',false)); ?>」の記事一覧
            </p>
            <!-- タクソノミーページ メインループ start -->
              <?php if(have_posts()): ?>
                <?php while(have_posts()): the_post(); ?>
<?php get_template_part('components/loop'); ?>   
                <?php endwhile; ?>
              <?php else: ?>
                <!-- 投稿がない場合 -->
                <p>投稿記事がありません</p>
              <?php endif; ?>
            <!-- タクソノミーページ メインループ end -->
          </section>
        </article>
        <!-- article end -->
<?php get_template_part('components/pagenation'); ?>      
<?php get_sidebar(); ?>
      </main>
      <!-- main end -->
<?php get_template_part('components/breadcrumb'); ?>
<?php get_footer(); ?>