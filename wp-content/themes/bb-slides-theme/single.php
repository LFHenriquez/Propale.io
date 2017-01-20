<?php
add_filter( 'fl_topbar_enabled', '__return_false' );
add_filter( 'fl_fixed_header_enabled', '__return_false' );
add_filter( 'fl_header_enabled', '__return_false' );
add_filter( 'fl_footer_enabled', '__return_false' );
get_header(); 
?>

<div class="fl-content-full">
	<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
		<?php get_template_part('content'); ?>
	<?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>