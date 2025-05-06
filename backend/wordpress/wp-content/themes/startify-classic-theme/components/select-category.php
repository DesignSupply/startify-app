<?php 
  $taxonomySlug = 'blog_category';
  $taxonomySlugFront = 'blog-category';
  $taxonomyArchivePath = esc_url(home_url('/')).$taxonomySlugFront.'/';
  wp_dropdown_categories( 
    array(
      'taxonomy' => $taxonomySlug,
      'name' => $taxonomySlug,
      'id' => 'select_date',
      'show_option_none' => 'カテゴリを選択',
      'show_count' => 1 ,
      'value_field' => 'slug'
    ) 
  ); 
  echo 
    '<script>document.getElementById("select_date").addEventListener("change", function(){'.
    'location.href = "'.$taxonomyArchivePath.'" + '.'this.options[this.selectedIndex].value;});</script>';
?>
<!-- select category end -->