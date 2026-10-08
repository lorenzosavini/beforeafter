<?php
defined( 'ABSPATH' ) || exit;
get_header();

if ( is_search() ) {
	/* translators: %s: termine cercato. */
	$title = sprintf( 'Risultati per «%s»', get_search_query() );
} elseif ( is_home() ) {
	$blog  = (int) get_option( 'page_for_posts' );
	$title = $blog ? get_the_title( $blog ) : 'News';
} else {
	$title = wp_strip_all_tags( get_the_archive_title() );
}
?>
<header class="phead">
	<div class="wrap narrow">
		<?php ip_breadcrumbs(); ?>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( is_search() ) : ?>
			<?php get_search_form(); ?>
		<?php elseif ( is_archive() && get_the_archive_description() ) : ?>
			<div class="phead-lead"><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</div>
</header>

<div class="sec">
	<div class="wrap narrow">
		<?php if ( have_posts() ) : ?>
			<ul class="postlist">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li>
						<?php if ( 'post' === get_post_type() ) : ?>
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<?php elseif ( 'corso' === get_post_type() && ip_course_tipologia() ) : ?>
							<span class="postlist-type"><?php echo esc_html( ip_course_tipologia()->name ); ?></span>
						<?php endif; ?>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => 'Precedenti',
				'next_text' => 'Successivi',
			) );
			?>
		<?php else : ?>
			<p>Nessun risultato. Prova con un’altra parola oppure <a href="#richiedi" data-scroll-form>chiedi direttamente a un orientatore</a>.</p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
