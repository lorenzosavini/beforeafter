<?php
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$tip   = ip_course_tipologia();
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
				<?php if ( ip_meta( 'evidenza' ) ) : ?>
					<p class="phead-note"><?php echo esc_html( ip_meta( 'evidenza' ) ); ?></p>
				<?php endif; ?>
				<?php
				$sub = array();
				foreach ( array( 'dipartimento', 'area' ) as $tx ) {
					$tt = get_the_terms( $id, $tx );
					if ( $tt && ! is_wp_error( $tt ) ) {
						$sub[] = '<a href="' . esc_url( get_term_link( $tt[0] ) ) . '">' . esc_html( $tt[0]->name ) . '</a>';
					}
				}
				?>
				<?php if ( $sub ) : ?>
					<p class="phead-sub"><?php echo implode( ' · ', $sub ); // phpcs:ignore ?></p>
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
					<p><?php echo esc_html( ip_opt( 'course_empty' ) ); ?></p>
				<?php endif; ?>

				<?php $curricula = ip_course_curricula( $id ); ?>
				<?php if ( $curricula ) : ?>
					<h2 id="piani-di-studio">Piani di studio</h2>
					<div class="curricula">
						<?php foreach ( $curricula as $i => $c ) : ?>
							<details<?php echo 1 === count( $curricula ) ? ' open' : ''; ?>>
								<summary><?php echo esc_html( $c->post_title ); ?></summary>
								<div class="curriculum-body">
									<?php echo apply_filters( 'the_content', $c->post_content ); // phpcs:ignore ?>
								</div>
							</details>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php $docs = ip_meta_rows( 'docs', $id ); ?>
				<?php if ( $docs ) : ?>
					<h2>Documenti</h2>
					<ul class="docs">
						<?php foreach ( $docs as $d ) : ?>
							<?php if ( empty( $d['url'] ) ) { continue; } ?>
							<li><a href="<?php echo esc_url( $d['url'] ); ?>" target="_blank" rel="noopener" data-track="document"><?php echo ip_icon( 'doc', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $d['label'] ? $d['label'] : basename( $d['url'] ) ); ?></span><?php if ( preg_match( '/\.pdf($|\?)/i', $d['url'] ) ) : ?><small>PDF</small><?php endif; ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php $faq = ip_meta_rows( 'faq', $id ); ?>
				<?php if ( $faq ) : ?>
					<h2>Domande frequenti</h2>
					<?php ip_faq_list( $faq ); ?>
				<?php endif; ?>

				<?php if ( ip_rows( 'steps' ) ) : ?>
					<h2>Come iscriversi con noi</h2>
					<?php ip_steps(); ?>
				<?php endif; ?>
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
								<p class="muted">con le agevolazioni, rateizzabile senza interessi.<?php echo ip_opt( 'retta_std' ) ? ' Retta standard ' . esc_html( ip_opt( 'retta_std' ) ) . '.' : ''; ?></p>
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
