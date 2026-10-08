<?php
defined( 'ABSPATH' ) || exit;
$landing = is_page_template( 'page-templates/landing.php' );

// Ogni pagina finisce con un modulo: se il contenuto non ne aveva uno, lo aggiungiamo qui.
if ( empty( $GLOBALS['ip_has_form'] ) && ! is_404() && ! ( (int) ip_opt( 'thanks_page' ) && is_page( (int) ip_opt( 'thanks_page' ) ) ) ) :
	?>
	<section class="endform">
		<div class="wrap endform-in">
			<div class="endform-text">
				<h2>Parla con un orientatore</h2>
				<p>Ti spieghiamo come funziona lo studio online, quanto costa davvero il percorso che ti interessa e quali agevolazioni puoi ottenere. La consulenza è gratuita e non ti impegna.</p>
				<?php ip_contact_block(); ?>
			</div>
			<?php ip_form(); ?>
		</div>
	</section>
	<?php
endif;
?>
</main>

<footer class="ftr">
	<?php if ( ! $landing ) : ?>
		<div class="wrap ftr-grid">
			<div class="ftr-brand">
				<p class="ftr-name"><?php echo esc_html( ip_opt( 'brand' ) ); ?></p>
				<?php if ( ip_opt( 'address' ) ) : ?>
					<p><?php echo nl2br( esc_html( ip_opt( 'address' ) ) ); ?></p>
				<?php endif; ?>
				<?php if ( ip_opt( 'address_exam' ) ) : ?>
					<p><span class="ftr-label">Sede d’esame</span><br><?php echo nl2br( esc_html( ip_opt( 'address_exam' ) ) ); ?></p>
				<?php endif; ?>
			</div>
			<div>
				<p class="ftr-h">Offerta formativa</p>
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'ftr-list', 'depth' => 1 ) ); ?>
				<?php else : ?>
					<ul class="ftr-list">
						<?php foreach ( ip_tipologie() as $t ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<div>
				<p class="ftr-h">Contatti</p>
				<ul class="ftr-list">
					<?php foreach ( array( 'phone1', 'phone2' ) as $p ) : ?>
						<?php if ( ip_opt( $p ) ) : ?>
							<li><a href="<?php echo esc_attr( ip_tel_href( ip_opt( $p ) ) ); ?>" data-track="call"><?php echo esc_html( ip_opt( $p ) ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ( ip_opt( 'email' ) ) : ?>
						<li><a href="mailto:<?php echo esc_attr( ip_opt( 'email' ) ); ?>"><?php echo esc_html( ip_opt( 'email' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( ip_opt( 'hours' ) ) : ?>
						<li class="ftr-hours"><?php echo nl2br( esc_html( ip_opt( 'hours' ) ) ); ?></li>
					<?php endif; ?>
				</ul>
			</div>
			<?php if ( ip_opt( 'facebook' ) || ip_opt( 'instagram' ) || has_nav_menu( 'legal' ) ) : ?>
			<div>
				<p class="ftr-h">Seguici</p>
				<ul class="ftr-list">
					<?php if ( ip_opt( 'facebook' ) ) : ?><li><a href="<?php echo esc_url( ip_opt( 'facebook' ) ); ?>" target="_blank" rel="noopener">Facebook</a></li><?php endif; ?>
					<?php if ( ip_opt( 'instagram' ) ) : ?><li><a href="<?php echo esc_url( ip_opt( 'instagram' ) ); ?>" target="_blank" rel="noopener">Instagram</a></li><?php endif; ?>
				</ul>
				<?php if ( has_nav_menu( 'legal' ) ) : ?>
					<?php wp_nav_menu( array( 'theme_location' => 'legal', 'container' => false, 'menu_class' => 'ftr-list ftr-legal', 'depth' => 1 ) ); ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="wrap ftr-bottom">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( ip_opt( 'company_name' ) ? ip_opt( 'company_name' ) : ip_opt( 'brand' ) ); ?><?php echo ip_opt( 'vat' ) ? ' · P.IVA ' . esc_html( ip_opt( 'vat' ) ) : ''; ?><?php if ( ip_privacy_url() ) : ?> · <a href="<?php echo esc_url( ip_privacy_url() ); ?>">Privacy</a><?php endif; ?><?php if ( (int) ip_opt( 'cookie_page' ) ) : ?> · <a href="<?php echo esc_url( get_permalink( (int) ip_opt( 'cookie_page' ) ) ); ?>">Cookie</a><?php endif; ?></p>
		<?php if ( ip_opt( 'disclaimer' ) ) : ?>
			<p class="ftr-disc"><?php echo esc_html( ip_opt( 'disclaimer' ) ); ?></p>
		<?php endif; ?>
	</div>
</footer>

<nav class="mbar" aria-label="Contatto rapido">
	<?php if ( ip_opt( 'phone1' ) ) : ?>
		<a href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo ip_icon( 'phone', 20 ); // phpcs:ignore ?><span>Chiama</span></a>
	<?php endif; ?>
	<?php if ( ip_wa_href() ) : ?>
		<a href="<?php echo esc_url( ip_wa_href() ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 20 ); // phpcs:ignore ?><span>WhatsApp</span></a>
	<?php endif; ?>
	<a class="mbar-main" href="#richiedi" data-scroll-form><?php echo ip_icon( 'doc', 20 ); // phpcs:ignore ?><span>Richiedi info</span></a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
