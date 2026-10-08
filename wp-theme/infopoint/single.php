<?php
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$cat = get_the_category();
	?>
	<article>
		<header class="phead">
			<div class="wrap narrow">
				<?php ip_breadcrumbs(); ?>
				<p class="eyebrow">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<?php if ( $cat ) : ?> · <a href="<?php echo esc_url( get_category_link( $cat[0] ) ); ?>"><?php echo esc_html( $cat[0]->name ); ?></a><?php endif; ?>
				</p>
				<h1><?php the_title(); ?></h1>
			</div>
		</header>
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
