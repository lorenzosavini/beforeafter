<?php
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$tip   = ip_course_tipologia();
	$areas = get_the_terms( $id, 'area' );
	$stato = ip_meta( 'stato' ) ? ip_meta( 'stato' ) : 'aperte';
	$is_degree = $tip && 0 === strpos( $tip->slug, 'laurea' );
	$facts = array_filter( array(
		'Classe'     => ip_meta( 'code' ),
		'CFU'        => ip_meta( 'cfu' ),
		'Durata'     => ip_meta( 'durata' ),
		'Accesso'    => ip_meta( 'accesso' ) ? ip_meta( 'accesso' ) : ( $is_degree ? 'Libero, senza test' : '' ),
		'Lingua'     => ip_meta( 'lingua' ),
		'Modalità'   => $is_degree ? 'Online, esami in presenza' : '',
	) );
	?>
	<article class="course">
		<header class="phead phead-course">
			<div class="wrap">
				<?php ip_breadcrumbs(); ?>
				<p class="eyebrow">
					<?php echo esc_html( $tip ? $tip->name : 'Corso' ); ?>
					<span class="status status-<?php echo esc_attr( $stato ); ?>"><?php echo esc_html( ip_status_label( $stato ) ); ?></span>
				</p>
				<h1><?php the_title(); ?></h1>
				<?php if ( $areas && ! is_wp_error( $areas ) ) : ?>
					<p class="phead-sub"><?php echo esc_html( $areas[0]->name ); ?></p>
				<?php endif; ?>
				<dl class="facts-dl">
					<?php foreach ( $facts as $k => $v ) : ?>
						<div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
				<a class="btn btn-accent jump" href="#richiedi" data-scroll-form>Ricevi costi e piano di studi</a>
			</div>
		</header>

		<div class="wrap with-aside">
			<div class="prose">
				<?php if ( has_excerpt() ) : ?>
					<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<?php if ( trim( get_the_content() ) ) : ?>
					<?php the_content(); ?>
				<?php else : ?>
					<p>Stiamo completando la scheda di questo corso. Lasciaci i tuoi dati: ti inviamo il piano di studi aggiornato, i costi e le agevolazioni a cui hai diritto.</p>
				<?php endif; ?>

				<h2>Come iscriversi</h2>
				<?php ip_steps(); ?>
			</div>

			<aside class="aside">
				<div class="aside-sticky">
					<?php
					$cost = ip_meta( 'retta' );
					if ( $cost || ( $is_degree && ip_price_from() ) ) :
						?>
						<div class="costbox">
							<?php if ( $cost ) : ?>
								<p><span>Costo</span><strong><?php echo esc_html( $cost ); ?></strong></p>
							<?php else : ?>
								<p><span>Retta</span><strong>da <?php echo esc_html( ip_price_from() ); ?>/mese</strong></p>
								<p class="muted">con le agevolazioni, in 12 rate senza interessi. Retta standard € 2.760/anno.</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php
					ip_form( array(
						'course' => $id,
						'title'  => 'Informazioni su questo corso',
						'text'   => 'Ricevi piano di studi, costi e agevolazioni. Ti rispondiamo entro un giorno lavorativo.',
					) );
					?>
				</div>
			</aside>
		</div>

		<?php
		if ( $tip ) :
			$rel = new WP_Query( array(
				'post_type'      => 'corso',
				'posts_per_page' => 6,
				'no_found_rows'  => true,
				'post__not_in'   => array( $id ),
				'orderby'        => 'rand',
				'tax_query'      => array( array( 'taxonomy' => 'tipologia', 'terms' => $tip->term_id ) ),
			) );
			if ( $rel->have_posts() ) :
				?>
				<section class="sec sec-alt">
					<div class="wrap">
						<header class="sec-head">
							<h2>Altri corsi: <?php echo esc_html( strtolower( $tip->name ) ); ?></h2>
							<a href="<?php echo esc_url( get_term_link( $tip ) ); ?>">Vedi tutti <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
						</header>
						<ul class="crows">
							<?php
							while ( $rel->have_posts() ) {
								$rel->the_post();
								ip_course_row();
							}
							wp_reset_postdata();
							?>
						</ul>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>
	</article>
	<?php
endwhile;

ip_cta_band();
get_footer();
