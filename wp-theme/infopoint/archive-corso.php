<?php
defined( 'ABSPATH' ) || exit;
get_header();

$term = is_tax() ? get_queried_object() : null;
?>
<?php
$count = $term ? (int) $term->count : (int) wp_count_posts( 'corso' )->publish;
$sites = count( ip_rows( 'exam_sites' ) );
$from  = ip_price_from();
?>
<header class="phead phead-band">
	<div class="wrap">
		<?php ip_breadcrumbs(); ?>
		<?php if ( $term ) : ?>
			<h1><span class="h1-pre">Corsi online all’Università Marconi</span> <span class="hl"><?php echo esc_html( $term->name ); ?></span></h1>
			<?php if ( $term->description ) : ?>
				<p class="phead-lead"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
		<?php else : ?>
			<h1><span class="h1-pre">Offerta formativa dell’Università Marconi</span> <span class="hl">Tutti i corsi</span></h1>
			<p class="phead-lead">Lauree triennali, magistrali e a ciclo unico, master e corsi per insegnanti. Cerca il corso che ti interessa e chiedi costi e piano di studi: ti rispondiamo entro un giorno lavorativo.</p>
		<?php endif; ?>
		<div class="band-row">
			<dl class="band-stats">
				<div><dt>Corsi</dt><dd><?php echo (int) $count; ?></dd></div>
				<?php if ( $sites ) : ?><div><dt>Sedi d’esame</dt><dd><?php echo (int) $sites; ?></dd></div><?php endif; ?>
				<?php if ( $from && ( ! $term || 0 === strpos( $term->slug, 'laurea' ) ) ) : ?><div><dt>Retta</dt><dd>da <?php echo esc_html( $from ); ?>/mese</dd></div><?php endif; ?>
			</dl>
			<div class="phead-acts">
				<a class="btn btn-accent" href="#richiedi" data-scroll-form>Chiedi a un orientatore</a>
				<?php echo ip_phone_link( 'phone1', 'btn btn-white' ); // phpcs:ignore ?>
			</div>
		</div>
	</div>
</header>

<div class="sec sec-search">
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
