<?php if (  $wp_query->max_num_pages > 1 ) { ?>

    <div class="navigation clearfix">
        <?php 
        if (function_exists("SPaginate"))
          {
            SPaginate();
          }
  ?>     
    </div><!-- .navigation -->
    
<?php } ?>