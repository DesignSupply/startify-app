      <footer>
        <section>
          <h1>
            フッター
          </h1>
<?php get_template_part('components/widget-sitemap'); ?>
<?php get_template_part('components/copyright'); ?>
        </section>
      </footer>
      <!-- footer end -->
    </div>
    <!-- base end -->
    <div class="external"></div>
    <!-- external end -->
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-**************"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-**************');
    </script>
    <?php wp_footer(); ?>
  </body>
</html>