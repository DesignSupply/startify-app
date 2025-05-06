<?php 
  $taxonomySlug = 'blog_tag';
  $args = array(
    'taxonomy' => $taxonomySlug,
    'number' => 30,
    'format' => 'list',
    'order' => 'RAND'
  );
  wp_tag_cloud($args); 
?>
<!-- tag cloud end -->