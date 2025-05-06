<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
<?php get_template_part('components/meta'); ?>
<?php get_template_part('components/json-ld'); ?>
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <noscript>※当ウェブサイトを快適に閲覧して頂くためjavascriptを有効にしてください</noscript>
    <div class="base">
      <header>
        <section>
<?php get_template_part('components/login-button'); ?>        
<?php get_template_part('components/logo'); ?>          
          <h1>ヘッダー</h1>
        </section>
        <nav>
<?php get_template_part('components/widget-global-navi'); ?>
        </nav>
      </header>
      <!-- header end -->