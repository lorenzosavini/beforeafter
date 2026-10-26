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
	// Scheda dati della testata: al massimo cinque voci, la retta sempre.
	$price = ip_meta( 'retta' ) ? ip_meta( 'retta' ) : ( $is_degree && ip_price_from() ? 'da ' . ip_price_from() . '/mese' : '' );
	$price = ip_part_on( 'price', $id ) ? $price : '';
	$facts = ip_part_on( 'facts', $id ) ? $facts : array();
	$photo = has_post_thumbnail() && ip_part_on( 'photo', $id );
	foreach ( array( 'Modalità', 'Lingua' ) as $drop ) {
		if ( count( $facts ) + ( $price ? 1 : 0 ) > 5 ) {
			unset( $facts[ $drop ] );
		}
	}
	// Titolo: «Laurea triennale in» piccolo sopra, nome del corso evidenziato, classe a parte.
	$t_pre  = '';
	$t_main = get_the_title();
	$t_code = '';
	if ( preg_match( '/^(.*\S)\s*\(([A-Z0-9][A-Z0-9\/ -]{0,14})\)$/u', $t_main, $m ) ) {
		$t_main = $m[1];
		$t_code = $m[2];
	}
	if ( $tip && 0 === mb_stripos( $t_main, $tip->name . ' in ' ) ) {
		$t_pre  = mb_substr( $t_main, 0, mb_strlen( $tip->name ) + 3 );
		$t_main = mb_substr( $t_main, mb_strlen( $tip->name ) + 4 );
	} elseif ( preg_match( '/^(.{6,60}?\sin)\s(.+)$/u', $t_main, $m ) ) {
		$t_pre  = $m[1];
		$t_main = $m[2];
	}
	?>
	<article class="course">
		<header class="phead phead-course<?php echo $photo ? ' has-img' : ''; ?>">
			<div class="wrap phead-grid">
				<div class="phead-main">
				<?php ip_breadcrumbs(); ?>
				<p class="eyebrow">
					<?php echo $t_pre ? '' : esc_html( $tip ? $tip->name : 'Corso' ); ?>
					<?php if ( ip_part_on( 'status', $id ) ) : ?><span class="status status-<?php echo esc_attr( $stato ); ?>"><?php echo esc_html( ip_status_label( $stato ) ); ?></span><?php endif; ?>
				</p>
				<h1<?php echo mb_strlen( $t_main ) > 38 ? ' class="h1-long"' : ''; ?>>
					<?php if ( $t_pre ) : ?><span class="h1-pre"><?php echo esc_html( $t_pre ); ?></span><?php endif; ?>
					<span class="hl"><?php echo esc_html( $t_main ); ?></span>
					<?php if ( $t_code ) : ?><span class="h1-code"><?php echo esc_html( $t_code ); ?></span><?php endif; ?>
				</h1>
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
				<div class="phead-acts">
					<a class="btn btn-accent" href="#richiedi" data-scroll-form>Ricevi costi e piano di studi</a>
					<?php echo ip_phone_link( 'phone1', 'btn btn-white' ); // phpcs:ignore ?>
				</div>
				<?php if ( $facts || $price ) : ?>
				<dl class="facts-dl">
					<?php foreach ( $facts as $k => $v ) : ?>
						<div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ); ?></dd></div>
					<?php endforeach; ?>
					<?php if ( $price ) : ?>
						<div class="facts-price"><dt><?php echo ip_meta( 'retta' ) ? 'Costo' : 'Retta'; ?></dt><dd><?php echo esc_html( $price ); ?></dd></div>
					<?php endif; ?>
				</dl>
				<?php endif; ?>
				</div>
				<?php if ( $photo ) : ?>
					<figure class="phead-img"><?php the_post_thumbnail( 'medium_large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); ?></figure>
				<?php endif; ?>
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

				<?php $curricula = ip_part_on( 'curricula', $id ) ? ip_course_curricula( $id ) : array(); ?>
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

				<?php $docs = ip_part_on( 'docs', $id ) ? ip_meta_rows( 'docs', $id ) : array(); ?>
				<?php if ( $docs ) : ?>
					<h2>Documenti</h2>
					<ul class="docs">
						<?php foreach ( $docs as $d ) : ?>
							<?php if ( empty( $d['url'] ) ) { continue; } ?>
							<li><a href="<?php echo esc_url( $d['url'] ); ?>" target="_blank" rel="noopener" data-track="document"><?php echo ip_icon( 'doc', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $d['label'] ? $d['label'] : basename( $d['url'] ) ); ?></span><?php if ( preg_match( '/\.pdf($|\?)/i', $d['url'] ) ) : ?><small>PDF</small><?php endif; ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>


				<?php $faq = ip_part_on( 'faq', $id ) ? ip_meta_rows( 'faq', $id ) : array(); ?>
				<?php if ( $faq ) : ?>
					<h2>Domande frequenti</h2>
					<?php ip_faq_list( $faq ); ?>
				<?php endif; ?>

				<?php if ( ip_rows( 'steps' ) && ip_part_on( 'steps', $id ) ) : ?>
					<h2>Come iscriversi con noi</h2>
					<?php ip_steps(); ?>
				<?php endif; ?>
			</div>

			<aside class="aside">
				<div class="aside-sticky">
					<?php
					$cost = ip_meta( 'retta' );
					if ( ip_part_on( 'price', $id ) && ( $cost || ( $is_degree && ip_price_from() ) ) ) :
						?>
						<div class="costbox">
							<?php if ( $cost ) : ?>
								<p><span>Costo</span><strong><?php echo esc_html( $cost ); ?></strong></p>
							<?php else : ?>
								<p><span>Retta</span><strong>da <?php echo esc_html( ip_price_from() ); ?>/mese</strong></p>
								<p class="muted">importi stabiliti dall’Ateneo e pagati direttamente all’Università, con le agevolazioni e rateizzabili senza interessi.<?php echo ip_retta_std() ? ' Retta standard ' . esc_html( ip_retta_std() ) . '.' : ''; ?></p>
								<?php echo ip_agev_deadline_note( 'muted agev-due' ); // phpcs:ignore ?>
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
		if ( $tip && ip_part_on( 'related', $id ) ) :
			$rel = new WP_Query( array(
				'post_type'      => 'corso',
				'posts_per_page' => 4,
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
							<a class="more-link" href="<?php echo esc_url( get_term_link( $tip ) ); ?>">Vedi tutti</a>
						</header>
						<div class="cards">
							<?php
							while ( $rel->have_posts() ) {
								$rel->the_post();
								ip_course_card();
							}
							wp_reset_postdata();
							?>
						</div>
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
