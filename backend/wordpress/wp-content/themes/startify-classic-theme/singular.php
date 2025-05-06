<?php get_header(); ?>
      <main>
<?php get_template_part('components/page-header'); ?>        
        <article>
          <section>
            <div>
              <h1>
                ページコンテンツ（シングルページ）
              </h1>
              <!-- シングルページ メインループ start -->
                <?php if(have_posts()): ?>
                  <?php if(post_password_required($post->ID)): ?>
                    <!-- パスワード保護中 -->
                    <?php echo get_the_password_form(); ?>
                  <?php else: ?>
                    <!-- パスワード解除後 -->
                    <?php while(have_posts()): the_post(); ?>
<?php get_template_part('components/content-single'); ?>
<?php get_template_part('components/share-buttons'); ?>
<?php get_template_part('components/pager'); ?>     
<?php comments_template(); ?>            
                    <?php endwhile; ?>
                  <?php endif; ?>
                <?php else: ?>
                  <!-- 投稿がない場合 -->
                  <p>投稿記事がありません</p>
                <?php endif; ?>
              <!-- シングルページ メインループ end -->
            </div>
          </section>
        </article>
        <!-- article end -->
<?php get_sidebar(); ?>
      </main>
      <!-- main end -->
<?php get_template_part('components/breadcrumb'); ?>
<?php get_footer(); ?>