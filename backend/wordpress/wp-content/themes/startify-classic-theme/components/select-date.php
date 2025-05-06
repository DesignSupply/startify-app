<select onchange="document.location.href=this.options[this.selectedIndex].value;">
  <option value=""><?php echo esc_attr(__('投稿月を選択')); ?></option> 
  <?php 
    $postType = 'blog';
    wp_get_archives( 
      array(
        'post_type' => $postType,
        'type' => 'monthly', 
        'format' => 'option', 
        'show_post_count' => 1 
      ) 
    ); 
  ?>
</select>
<!-- select date end -->