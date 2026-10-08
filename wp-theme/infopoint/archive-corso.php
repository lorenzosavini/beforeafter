<?php
defined( 'ABSPATH' ) || exit;
get_header();

$term = is_tax() ? get_queried_object() : null;
?>
<header class="phead">
	<div class="wrap">
		<?php ip_breadcrumbs(); ?>
		<?php if ( $term ) : ?>
			<p class="eyebrow">Corsi UniMarconi</p>
			<h1><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( $term->description ) : ?>
				<p class="phead-lead"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
		<?php else : ?>
			<p class="eyebrow">Offerta formativa</p>
			<h1>Tutti i corsi dell’Università Marconi</h1>
			<p class="phead-lead">Lauree triennali, magistrali e a ciclo unico, master e corsi per insegnanti. Cerca il corso che ti interessa e chiedi costi e piano di studi: ti rispondiamo entro un giorno lavorativo.</p>
		<?php endif; ?>
	</div>
</header>

<div class="sec">
	<div class="wrap">
		<?php
		if ( $term && 'tipologia' === $term->taxonomy ) {
			ip_course_list( array( $term->slug ), true );
		} elseif ( $term ) {
			echo '<ul class="crows">';
			while ( have_posts() ) {
				the_post();
				ip_course_row();
			}
			echo '</ul>';
		} else {
			ip_course_list();
		}
		?>
	</div>
</div>

<?php
ip_cta_band();
get_footer();
