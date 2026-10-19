<?php
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<?php ip_page_band( get_the_title(), has_excerpt() ? get_the_excerpt() : '', ip_page_band_pre( get_the_ID() ), ip_page_band_acts( get_the_ID() ) ); ?>
	<div class="sec">
		<div class="wrap prose">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;
get_footer();
