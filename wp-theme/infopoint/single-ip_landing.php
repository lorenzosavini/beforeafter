<?php
/**
 * Landing di conversione: pagina chiusa, senza menu né collegamenti in uscita.
 * Si compone da Landing → Crea landing; i contenuti arrivano da ip_lp_data().
 */
defined( 'ABSPATH' ) || exit;
the_post();
$lp_id = get_the_ID();
$d     = ip_lp_data( $lp_id );
$o     = $d['o'];
$sec   = $o['sezioni'];
$on    = function ( $k ) use ( $sec ) {
	return in_array( $k, $sec, true );
};
$wa    = ip_wa_href();
$form_titles = array(
	'corso'        => 'Ricevi piano di studi e costi',
	'tipologia'    => 'Ti aiutiamo a scegliere il corso',
	'agevolazione' => 'Verifica se ne hai diritto',
	'generica'     => ip_opt( 'form_title' ),
);
$privacy_id = (int) ip_opt( 'privacy_page' ) ? (int) ip_opt( 'privacy_page' ) : (int) get_option( 'wp_page_for_privacy_policy' );
$cookie_id  = (int) ip_opt( 'cookie_page' );
$chi        = ip_page_url( 'chi-siamo' );
$is_degree  = ( 'corso' === $d['type'] && ( $t = ip_course_tipologia( $d['course'] ) ) && 0 === strpos( $t->slug, 'laurea' ) )
	|| ( 'tipologia' === $d['type'] && '' !== $d['price'] )
	|| 'generica' === $d['type'];
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="<?php echo esc_attr( ip_opt( 'color_brand' ) ? ip_opt( 'color_brand' ) : '#225e48' ); ?>">
<?php if ( ip_opt( 'font_brand' ) ) : ?><link rel="preload" href="<?php echo esc_url( IP_URI . '/assets/fonts/montserrat-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin><?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( ip_opt( 'disclosure_bar' ) && ip_text( 'disclosure' ) ) : ?>
<div class="disclosure" role="note">
	<div class="wrap"><p><?php echo esc_html( ip_text( 'disclosure' ) ); ?> <button type="button" class="lp-link" data-lp-open="lp-chi">Chi siamo</button></p></div>
</div>
<?php endif; ?>

<header class="hdr lp-hdr">
	<div class="wrap hdr-in">
		<span class="brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand-logo', 'alt' => esc_attr( ip_opt( 'brand' ) ), 'loading' => 'eager' ) ); ?>
			<?php else : ?>
				<span class="brand-mark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( ip_opt( 'brand' ), 0, 1 ) ) ); ?></span>
				<span class="brand-text">
					<strong><?php echo esc_html( ip_opt( 'brand' ) ); ?></strong>
					<small>Agenzia partner UniMarconi<?php echo ip_opt( 'city' ) ? ' · ' . esc_html( ip_opt( 'city' ) ) : ''; ?></small>
				</span>
			<?php endif; ?>
		</span>
		<div class="lp-hdr-act">
			<?php if ( $wa ) : ?>
				<a class="lp-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp" aria-label="WhatsApp"><?php echo ip_icon( 'whatsapp', 22 ); // phpcs:ignore ?></a>
			<?php endif; ?>
			<?php echo ip_phone_link( 'phone1', 'hdr-phone' ); // phpcs:ignore ?>
		</div>
	</div>
</header>

<main id="main">
	<section class="hero hero-photo lp-hero">
		<?php if ( $d['image'] ) : ?>
			<?php echo wp_get_attachment_image( $d['image'], 'full', false, array( 'class' => 'hero-bg', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ); ?>
		<?php endif; ?>
		<div class="wrap hero-in">
			<div class="hero-text">
				<?php if ( $d['kicker'] ) : ?><p class="hero-kicker"><?php echo esc_html( $d['kicker'] ); ?></p><?php endif; ?>
				<h1><?php echo esc_html( $d['title'] ); ?></h1>
				<?php if ( $d['sub'] ) : ?><p class="hero-lead"><?php echo esc_html( $d['sub'] ); ?></p><?php endif; ?>
				<?php if ( $d['points'] ) : ?>
					<ul class="lp-points">
						<?php foreach ( $d['points'] as $p ) : ?>
							<li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $p ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $d['deadline'] ) : ?>
					<p class="lp-deadline"><?php echo ip_icon( 'clock', 18 ); // phpcs:ignore ?> Valida per le richieste presentate entro il <?php echo esc_html( $d['deadline'] ); ?></p>
				<?php endif; ?>
				<?php if ( $d['price'] ) : ?>
					<p class="hero-price"><?php echo wp_kses( $d['price'], array( 'strong' => array() ) ); ?></p>
				<?php endif; ?>
			</div>
			<div class="hero-form">
				<?php
				ip_form( array(
					'type'     => $o['modulo'],
					'course'   => $d['course'],
					'interest' => $d['interest'],
					'landing'  => $lp_id,
					'inline'   => true,
					'title'    => $o['mod_titolo'] ? $o['mod_titolo'] : $form_titles[ $d['type'] ],
					'text'     => $o['mod_testo'],
					'button'   => $o['pulsante'],
				) );
				?>
			</div>
		</div>
	</section>

	<?php if ( $on( 'dati' ) && $d['facts'] ) : ?>
		<section class="facts lp-facts">
			<div class="wrap facts-in">
				<?php foreach ( array_slice( $d['facts'], 0, 4, true ) as $label => $value ) : ?>
					<p><strong><?php echo esc_html( $value ); ?></strong><span><?php echo esc_html( $label ); ?></span></p>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'scheda' ) ) : ?>
		<?php if ( 'corso' === $d['type'] && trim( wp_strip_all_tags( $d['content'] ) ) ) : ?>
			<section class="sec">
				<div class="wrap lp-narrow">
					<header class="sec-head"><h2>Il corso</h2></header>
					<div class="prose lp-clip" id="lp-scheda"><?php echo $d['content']; // phpcs:ignore ?></div>
					<p class="lp-expand-row"><button type="button" class="btn btn-line" data-lp-expand="lp-scheda">Leggi tutta la scheda</button>
					<a class="btn btn-accent" href="#richiedi" data-scroll-form>Ricevi il piano di studi</a></p>
				</div>
			</section>
		<?php elseif ( 'tipologia' === $d['type'] && $d['courses'] ) : ?>
			<section class="sec">
				<div class="wrap">
					<header class="sec-head"><h2>Scegli il corso</h2></header>
					<div class="lp-courses">
						<?php foreach ( $d['courses'] as $cid ) : ?>
							<?php
							$code  = ip_meta( 'code', $cid );
							$label = ip_course_name( $cid ) . ( $code ? ' (' . $code . ')' : '' );
							$meta  = array_filter( array( ip_meta( 'cfu', $cid ) ? ip_meta( 'cfu', $cid ) . ' CFU' : '', ip_meta( 'durata', $cid ), ip_meta( 'retta', $cid ) ) );
							?>
							<article class="lp-course">
								<?php if ( has_post_thumbnail( $cid ) ) : ?>
									<div class="lp-course-img"><?php echo get_the_post_thumbnail( $cid, 'ip-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?><?php if ( $code ) : ?><span class="ccard-code"><?php echo esc_html( $code ); ?></span><?php endif; ?></div>
								<?php endif; ?>
								<div class="lp-course-body">
									<h3><?php echo esc_html( ip_course_name( $cid ) ); ?></h3>
									<?php if ( $meta ) : ?><p class="ccard-meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p><?php endif; ?>
									<a class="btn btn-line btn-block" href="#richiedi" data-scroll-form data-set-interest="<?php echo esc_attr( $label ); ?>">Mi interessa</a>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php elseif ( 'agevolazione' === $d['type'] ) : ?>
			<?php $cond = ip_lp_lines( ip_meta( 'cond', $d['agev'] ) ); $det = ip_meta( 'det', $d['agev'] ); ?>
			<?php if ( $cond || $det ) : ?>
				<section class="sec">
					<div class="wrap lp-narrow">
						<header class="sec-head"><h2>Condizioni</h2></header>
						<?php if ( $cond ) : ?>
							<ul class="lp-cond"><?php foreach ( $cond as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?></ul>
						<?php endif; ?>
						<?php if ( $det ) : ?><p class="lp-det"><?php echo nl2br( esc_html( $det ) ); ?></p><?php endif; ?>
						<p class="source">Importi e condizioni stabiliti dall’Università degli Studi Guglielmo Marconi e aggiornati automaticamente dal sito ufficiale. Ti confermiamo l’importo esatto prima dell’iscrizione.</p>
						<p><a class="btn btn-accent" href="#richiedi" data-scroll-form>Verifica se ne hai diritto</a></p>
					</div>
				</section>
			<?php endif; ?>
		<?php elseif ( 'generica' === $d['type'] && ip_tipologie() ) : ?>
			<section class="sec">
				<div class="wrap">
					<header class="sec-head"><h2>Cosa puoi studiare</h2></header>
					<div class="lp-tips">
						<?php foreach ( ip_tipologie() as $tt ) : ?>
							<a class="lp-tip" href="#richiedi" data-scroll-form data-set-interest="<?php echo esc_attr( $tt->name ); ?>"><strong><?php echo esc_html( $tt->name ); ?></strong><span><?php echo (int) $tt->count; ?> corsi · Mi interessa</span></a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $on( 'piani' ) && $d['curricula'] ) : ?>
		<section class="sec sec-alt">
			<div class="wrap lp-narrow">
				<header class="sec-head"><h2>Piani di studio</h2></header>
				<div class="prose">
					<?php foreach ( $d['curricula'] as $cu ) : ?>
						<details>
							<summary><?php echo esc_html( html_entity_decode( get_the_title( $cu ), ENT_QUOTES, 'UTF-8' ) ); ?></summary>
							<div><?php echo ip_lp_unlink( do_blocks( $cu->post_content ) ); // phpcs:ignore ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'costi' ) && 'agevolazione' !== $d['type'] ) : ?>
		<?php $cost = $d['course'] ? ip_meta( 'retta', $d['course'] ) : ''; ?>
		<?php if ( $cost ) : ?>
			<section class="sec">
				<div class="wrap lp-narrow">
					<header class="sec-head"><h2>Quanto costa</h2></header>
					<p class="lp-cost"><strong><?php echo esc_html( $cost ); ?></strong></p>
					<p>Importo stabilito dall’Ateneo e pagato direttamente all’Università. Ti spieghiamo rateizzazione e agevolazioni a cui hai diritto.</p>
					<p><a class="btn btn-accent" href="#richiedi" data-scroll-form>Chiedi le agevolazioni</a></p>
				</div>
			</section>
		<?php elseif ( $is_degree && wp_count_posts( 'agevolazione' )->publish ) : ?>
			<section class="sec">
				<div class="wrap">
					<header class="sec-head"><h2>Quanto costa</h2></header>
					<?php if ( ip_retta_std() ) : ?>
						<p class="sec-lead">Retta standard <?php echo esc_html( ip_retta_std() ); ?>, rateizzabile senza costi aggiuntivi. Con le agevolazioni dell’Ateneo puoi pagare meno: ecco quelle in vigore.</p>
					<?php endif; ?>
					<?php
					ip_agevolazioni_table( 6 );
					$more = (int) wp_count_posts( 'agevolazione' )->publish - 6;
					?>
					<?php if ( $more > 0 ) : ?>
						<p class="lp-expand-row"><a class="btn btn-accent" href="#richiedi" data-scroll-form>Ci sono altre <?php echo (int) $more; ?> agevolazioni: chiedici quale ti spetta</a></p>
					<?php endif; ?>
					<p class="source">Importi dell’Università degli Studi Guglielmo Marconi, aggiornati automaticamente dal sito ufficiale.</p>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $on( 'passi' ) && ip_rows( 'steps' ) ) : ?>
		<section class="sec sec-alt">
			<div class="wrap">
				<header class="sec-head"><h2><?php echo esc_html( ip_opt( 't_passi' ) ); ?></h2></header>
				<?php echo do_shortcode( '[ip_passi]' ); // phpcs:ignore ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'recensioni' ) && ip_rows( 'reviews' ) ) : ?>
		<section class="sec">
			<div class="wrap">
				<header class="sec-head"><h2><?php echo esc_html( ip_opt( 't_reviews' ) ); ?></h2></header>
				<div class="reviews">
					<?php foreach ( ip_rows( 'reviews' ) as $r ) : ?>
						<?php $stars = min( 5, max( 0, (int) $r['voto'] ) ); ?>
						<figure class="review">
							<?php if ( $stars ) : ?><p class="review-stars" aria-label="<?php echo esc_attr( $stars ); ?> su 5"><?php echo esc_html( str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ) ); ?></p><?php endif; ?>
							<blockquote><?php echo esc_html( $r['testo'] ); ?></blockquote>
							<figcaption><strong><?php echo esc_html( $r['nome'] ); ?></strong><?php if ( $r['corso'] ) : ?> · <?php echo esc_html( $r['corso'] ); ?><?php endif; ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'faq' ) && ip_rows( 'faq' ) ) : ?>
		<section class="sec">
			<div class="wrap lp-narrow">
				<header class="sec-head"><h2><?php echo esc_html( ip_opt( 't_faq' ) ); ?></h2></header>
				<?php ip_faq_list( ip_rows( 'faq' ) ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'finale' ) ) : ?>
		<section class="endform">
			<div class="wrap endform-in">
				<div class="endform-text">
					<h2>Preferisci che ti chiamiamo noi?</h2>
					<p>Lascia nome e numero: un orientatore ti richiama quando preferisci, ti spiega il percorso, i costi e le agevolazioni. La consulenza è gratuita e non ti impegna.</p>
					<div class="contacts">
						<?php if ( ip_opt( 'phone1' ) ) : ?>
							<a class="contact" href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo ip_icon( 'phone', 22 ); // phpcs:ignore ?><span><small><?php echo esc_html( ip_opt( 'phone1_label' ) ); ?></small><?php echo esc_html( ip_opt( 'phone1' ) ); ?></span></a>
						<?php endif; ?>
						<?php if ( $wa ) : ?>
							<a class="contact" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 22 ); // phpcs:ignore ?><span><small>WhatsApp</small>Scrivici un messaggio</span></a>
						<?php endif; ?>
						<?php if ( ip_opt( 'hours' ) ) : ?>
							<div class="contact"><?php echo ip_icon( 'clock', 22 ); // phpcs:ignore ?><span><small>Orari</small><?php echo nl2br( esc_html( ip_opt( 'hours' ) ) ); ?></span></div>
						<?php endif; ?>
					</div>
				</div>
				<?php ip_form( array( 'type' => 'callback', 'course' => $d['course'], 'interest' => $d['interest'], 'landing' => $lp_id, 'inline' => true, 'id' => 'richiamo' ) ); ?>
			</div>
		</section>
	<?php endif; ?>
</main>

<footer class="ftr lp-ftr">
	<div class="wrap ftr-bottom">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( ip_legal_line() ); ?>
			<?php if ( $chi ) : ?> · <button type="button" class="lp-link" data-lp-open="lp-chi">Chi siamo</button><?php endif; ?>
			<?php if ( $privacy_id ) : ?> · <button type="button" class="lp-link" data-lp-open="lp-privacy">Privacy</button><?php endif; ?>
			<?php if ( $cookie_id ) : ?> · <button type="button" class="lp-link" data-lp-open="lp-cookie">Cookie</button><?php endif; ?>
		</p>
		<?php if ( ip_opt( 'disclaimer' ) ) : ?>
			<p class="ftr-disc"><?php echo esc_html( ip_text( 'disclaimer' ) ); ?></p>
		<?php endif; ?>
	</div>
</footer>

<nav class="mbar" aria-label="Contatto rapido">
	<?php if ( ip_opt( 'phone1' ) ) : ?>
		<a href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo ip_icon( 'phone', 20 ); // phpcs:ignore ?><span>Chiama</span></a>
	<?php endif; ?>
	<?php if ( $wa ) : ?>
		<a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 20 ); // phpcs:ignore ?><span>WhatsApp</span></a>
	<?php endif; ?>
	<a class="mbar-main" href="#richiedi" data-scroll-form><?php echo ip_icon( 'doc', 20 ); // phpcs:ignore ?><span>Richiedi info</span></a>
</nav>

<template id="ip-thanks">
	<p class="lead-title">Grazie{nome}, richiesta ricevuta!</p>
	<p>Un orientatore ti contatta entro un giorno lavorativo al numero che hai indicato. Tieni il telefono a portata di mano: la chiamata arriva da un numero di <?php echo esc_html( ip_opt( 'brand' ) ); ?>.</p>
	<?php if ( ip_opt( 'phone1' ) ) : ?>
		<p><a class="btn btn-primary btn-block" href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo ip_icon( 'phone', 18 ); // phpcs:ignore ?> Hai fretta? Chiamaci ora</a></p>
	<?php endif; ?>
</template>

<?php
// Pagine legali e «chi siamo» in finestre: si leggono senza lasciare la landing.
ip_lp_dialog( 'chi', 'Chi siamo', do_shortcode( '[ip_chi_siamo]' ), $chi );
ip_lp_dialog( 'privacy', 'Informativa privacy', ip_lp_page_html( $privacy_id ), $privacy_id ? get_permalink( $privacy_id ) : '' );
ip_lp_dialog( 'cookie', 'Cookie', ip_lp_page_html( $cookie_id ), $cookie_id ? get_permalink( $cookie_id ) : '' );
?>

<?php if ( $o['uscita'] ) : ?>
	<dialog class="lp-dialog lp-exit" data-lp-exit aria-label="Ti richiamiamo noi">
		<div class="lp-dialog-in">
			<button type="button" class="lp-x" data-lp-close aria-label="Chiudi"><?php echo ip_icon( 'close', 22 ); // phpcs:ignore ?></button>
			<?php ip_form( array( 'type' => 'callback', 'course' => $d['course'], 'interest' => $d['interest'], 'landing' => $lp_id, 'inline' => true, 'id' => 'lp-exit-form', 'title' => 'Prima di andare: ti richiamiamo noi?', 'text' => 'Lascia nome e numero. Un orientatore ti spiega costi e agevolazioni, gratis e senza impegno.' ) ); ?>
		</div>
	</dialog>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
