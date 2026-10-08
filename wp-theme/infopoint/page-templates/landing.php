<?php
/**
 * Template Name: Landing per campagne (senza menu)
 *
 * Pensata per il traffico a pagamento: nessuna via di fuga, modulo sopra la
 * piega, telefono sempre visibile. Il contenuto della pagina va sotto il modulo.
 */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="hero hero-landing">
		<div class="wrap hero-in">
			<div class="hero-text">
				<p class="eyebrow"><?php echo esc_html( ip_opt( 'brand' ) ); ?> · Agenzia partner UniMarconi</p>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="hero-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<ul class="ticks">
					<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Laurea con valore legale, riconosciuta dal MUR</li>
					<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Iscrizioni aperte tutto l’anno, senza test</li>
					<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Studi online quando vuoi, esami vicino a casa</li>
					<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?>Tutor dedicato per tutto il percorso</li>
				</ul>
				<?php if ( ! ip_opt( 'disclosure_bar' ) ) : ?>
					<p class="hero-who"><?php echo esc_html( ip_text( 'disclosure' ) ); ?></p>
				<?php endif; ?>
				<?php if ( ip_price_from() ) : ?>
					<p class="hero-price">Retta da <strong><?php echo esc_html( ip_price_from() ); ?> al mese</strong> con le agevolazioni</p>
				<?php endif; ?>
			</div>
			<div class="hero-form">
				<?php ip_form( array( 'title' => 'Parla con un orientatore', 'text' => 'Ti richiamiamo per spiegarti corsi, costi e agevolazioni. Gratis e senza impegno.' ) ); ?>
			</div>
		</div>
	</section>
	<?php if ( trim( get_the_content() ) ) : ?>
		<div class="sec">
			<div class="wrap prose">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php
endwhile;
ip_cta_band( 'Hai ancora qualche dubbio?' );
get_footer();
