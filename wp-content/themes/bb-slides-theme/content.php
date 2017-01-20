<article <?php post_class( 'fl-post' ); ?> id="fl-post-<?php the_ID(); ?>" itemscope itemtype="http://schema.org/BlogPosting">
	<div class="fl-post-content clearfix" itemprop="text">
		<?php the_content(); ?>
	</div><!-- .fl-post-content -->
</article>
<!-- .fl-post -->