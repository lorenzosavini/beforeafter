<?php
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$cat = get_the_category();
	?>
	<article>
		<?php ip_page_band( get_the_title(), '', get_the_date() . ( $cat ? ' · ' . $cat[0]->name : '' ) ); ?>
		<div class="sec">
			<div class="wrap narrow prose">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="cover"><?php the_post_thumbnail( 'large', array( 'fetchpriority' => 'high', 'loading' => 'eager' ) ); ?></figure>
				<?php endif; ?>
				<?php the_content(); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;
ip_cta_band();
get_footer();
