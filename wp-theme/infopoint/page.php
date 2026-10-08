<?php
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
		</div>
	</header>
	<div class="sec">
		<div class="wrap prose">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;
get_footer();
