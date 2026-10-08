<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<header class="phead">
	<div class="wrap narrow">
		<p class="eyebrow">Errore 404</p>
		<h1>Questa pagina non esiste più</h1>
		<p class="phead-lead">Può darsi che il corso abbia cambiato nome o indirizzo. Cercalo nell’elenco aggiornato o chiamaci: ti rispondiamo subito.</p>
	</div>
</header>
<div class="sec">
	<div class="wrap narrow">
		<p class="btnrow">
			<a class="btn btn-primary" href="<?php echo esc_url( ip_courses_url() ); ?>">Tutti i corsi</a>
			<?php echo ip_phone_link( 'phone1', 'btn btn-line' ); // phpcs:ignore ?>
			<a class="btn btn-line" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		</p>
	</div>
</div>
<?php
get_footer();
