<?php get_header(); ?>
      <main>
<?php get_template_part('components/page-header'); ?>        
        <article>
          <section>
            <h1>
              ページコンテンツ（404ページ）
            </h1>
            <p>
              お探しのページが見つかりません。URLを確認の上、再度アクセスしてください。また、ページが削除される場合もございます。詳しくは<a href="mailto:<?php echo antispambot(get_option('admin_email')); ?>">ウェブサイト管理者</a>までお問い合わせください。
            </p>
          </section>
        </article>
        <!-- article end -->
<?php get_template_part('components/pagenation'); ?>      
<?php get_sidebar(); ?>
      </main>
      <!-- main end -->
<?php get_template_part('components/breadcrumb'); ?>
<?php get_footer(); ?>