<?php
/**
 * Template Name: Pagina con modulo laterale
 * Template Post Type: page, post
 */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<?php ip_page_band( get_the_title(), has_excerpt() ? get_the_excerpt() : '', ip_page_band_pre( get_the_ID() ) ); ?>
	<div class="wrap with-aside">
		<div class="prose">
			<?php the_content(); ?>
		</div>
		<aside class="aside">
			<div class="aside-sticky">
				<?php ip_form(); ?>
			</div>
		</aside>
	</div>
	<?php
endwhile;
ip_cta_band();
get_footer();
