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
	<header class="phead">
		<div class="wrap">
			<?php ip_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="phead-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<a class="btn btn-accent jump" href="#richiedi" data-scroll-form>Richiedi informazioni</a>
		</div>
	</header>
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
