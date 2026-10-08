<?php
/**
 * Landing di conversione: pagina chiusa, senza menu né collegamenti in uscita.
 * Si compone da Landing → Crea landing; i contenuti arrivano da ip_lp_data().
 * Stile in assets/landing.css, caricato solo qui.
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
$wa          = ip_wa_href();
$form_titles = array(
	'corso'        => 'Ricevi piano di studi e costi',
	'tipologia'    => 'Ti aiutiamo a scegliere il corso',
	'agevolazione' => 'Verifica se ne hai diritto',
	'generica'     => ip_opt( 'form_title' ),
);
$privacy_id = (int) ip_opt( 'privacy_page' ) ? (int) ip_opt( 'privacy_page' ) : (int) get_option( 'wp_page_for_privacy_policy' );
$cookie_id  = (int) ip_opt( 'cookie_page' );
$chi        = ip_page_url( 'chi-siamo' );
$tip        = $d['course'] ? ip_course_tipologia( $d['course'] ) : null;
$is_degree  = ( $tip && 0 === strpos( $tip->slug, 'laurea' ) )
	|| ( 'tipologia' === $d['type'] && '' !== $d['price'] )
	|| 'generica' === $d['type'];
list( $t_pre, $t_main, $t_code ) = ip_lp_title_parts( $d );
$spec    = ip_lp_spec( $d );
$figures = ip_lp_figures();
$img     = $d['image'];

/**
 * Intestazione di sezione: occhiello con filetto e titolo.
 */
$head = function ( $kicker, $title ) {
	printf( '<header class="lp-sh"><p class="lp-k">%s</p><h2>%s</h2></header>', esc_html( $kicker ), esc_html( $title ) );
};
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0e261d">
<?php if ( ip_opt( 'font_brand' ) ) : ?><link rel="preload" href="<?php echo esc_url( IP_URI . '/assets/fonts/montserrat-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin><?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( ip_opt( 'disclosure_bar' ) && ip_text( 'disclosure' ) ) : ?>
<div class="lp-disc" role="note">
	<div class="lp-w"><p><?php echo esc_html( ip_text( 'disclosure' ) ); ?> <button type="button" class="lp-link" data-lp-open="lp-chi">Chi siamo</button></p></div>
</div>
<?php endif; ?>

<header class="lp-top">
	<div class="lp-w lp-top-in">
		<span class="lp-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'lp-logo', 'alt' => esc_attr( ip_opt( 'brand' ) ), 'loading' => 'eager' ) ); ?>
			<?php else : ?>
				<span class="lp-mark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( ip_opt( 'brand' ), 0, 1 ) ) ); ?></span>
				<span class="lp-brand-t"><strong><?php echo esc_html( ip_opt( 'brand' ) ); ?></strong><small>Agenzia partner Università Marconi<?php echo ip_opt( 'city' ) ? ' · ' . esc_html( ip_opt( 'city' ) ) : ''; ?></small></span>
			<?php endif; ?>
		</span>
		<div class="lp-top-act">
			<?php if ( $wa ) : ?>
				<a class="lp-top-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 20 ); // phpcs:ignore ?><span>WhatsApp</span></a>
			<?php endif; ?>
			<?php if ( ip_opt( 'phone1' ) ) : ?>
				<a class="lp-top-tel" href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><small>Orientamento gratuito</small><strong><?php echo esc_html( ip_opt( 'phone1' ) ); ?></strong></a>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="main">

	<section class="lp-hero<?php echo $img ? ' has-img' : ''; ?>">
		<?php if ( $img ) : ?>
			<div class="lp-hero-img"><?php echo wp_get_attachment_image( $img, 'full', false, array( 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 900px) 100vw, 45vw' ) ); ?></div>
		<?php endif; ?>
		<div class="lp-w lp-hero-in">
			<div class="lp-hero-t">
				<?php if ( $d['kicker'] ) : ?><p class="lp-k"><?php echo esc_html( $d['kicker'] ); ?></p><?php endif; ?>
				<h1>
					<?php if ( $t_pre ) : ?><span class="lp-h1-pre"><?php echo esc_html( $t_pre ); ?></span><?php endif; ?>
					<?php echo esc_html( $t_main ); ?>
					<?php if ( $t_code ) : ?><span class="lp-h1-code"><?php echo esc_html( $t_code ); ?></span><?php endif; ?>
				</h1>
				<?php if ( $d['sub'] ) : ?><p class="lp-lead"><?php echo esc_html( $d['sub'] ); ?></p><?php endif; ?>

				<?php if ( $spec ) : ?>
					<dl class="lp-spec">
						<?php foreach ( $spec as $label => $value ) : ?>
							<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( $d['points'] ) : ?>
					<ul class="lp-pts">
						<?php foreach ( $d['points'] as $p ) : ?><li><?php echo esc_html( $p ); ?></li><?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $d['deadline'] ) : ?>
					<p class="lp-due"><span>Scadenza</span> richieste entro il <?php echo esc_html( $d['deadline'] ); ?></p>
				<?php endif; ?>
				<?php if ( $d['price'] && 'agevolazione' !== $d['type'] ) : ?>
					<p class="lp-price"><?php echo wp_kses( $d['price'], array( 'strong' => array() ) ); ?></p>
				<?php endif; ?>
			</div>

			<div class="lp-hero-f">
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
				<p class="lp-assure">Gratuito e senza impegno · L’iscrizione si completa con l’Ateneo</p>
			</div>
		</div>
	</section>

	<?php if ( $figures ) : ?>
		<section class="lp-figs">
			<div class="lp-w lp-figs-in">
				<?php foreach ( $figures as $fg ) : ?>
					<p><strong><?php echo esc_html( $fg[0] ); ?></strong><span><?php echo esc_html( $fg[1] ); ?></span></p>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'scheda' ) ) : ?>
		<?php if ( 'corso' === $d['type'] && trim( wp_strip_all_tags( $d['content'] ) ) ) : ?>
			<section class="lp-sec">
				<div class="lp-w lp-split">
					<div class="lp-split-h">
						<?php $head( 'Il corso', 'Cosa studierai e dove ti porta' ); ?>
						<p class="lp-note">Testo della scheda ufficiale dell’Ateneo, aggiornato automaticamente.</p>
						<a class="lp-btn lp-btn-line" href="#richiedi" data-scroll-form>Ricevi il piano di studi completo</a>
					</div>
					<div>
						<div class="lp-prose lp-clip" id="lp-scheda"><?php echo $d['content']; // phpcs:ignore ?></div>
						<button type="button" class="lp-more" data-lp-expand="lp-scheda">Leggi tutta la scheda</button>
					</div>
				</div>
			</section>
		<?php elseif ( 'tipologia' === $d['type'] && $d['courses'] ) : ?>
			<section class="lp-sec">
				<div class="lp-w">
					<?php $head( count( $d['courses'] ) . ' corsi', 'Scegli il corso che ti interessa' ); ?>
					<div class="lp-cards">
						<?php foreach ( $d['courses'] as $cid ) : ?>
							<?php
							$code  = ip_meta( 'code', $cid );
							$label = ip_course_name( $cid ) . ( $code ? ' (' . $code . ')' : '' );
							$meta  = array_filter( array( ip_meta( 'cfu', $cid ) ? ip_meta( 'cfu', $cid ) . ' CFU' : '', ip_meta( 'durata', $cid ), ip_meta( 'retta', $cid ) ) );
							?>
							<a class="lp-card" href="#richiedi" data-scroll-form data-set-interest="<?php echo esc_attr( $label ); ?>">
								<span class="lp-card-img"><?php echo has_post_thumbnail( $cid ) ? get_the_post_thumbnail( $cid, 'ip-card', array( 'loading' => 'lazy', 'alt' => '' ) ) : ''; ?></span>
								<span class="lp-card-b">
									<?php if ( $code ) : ?><span class="lp-card-code"><?php echo esc_html( $code ); ?></span><?php endif; ?>
									<strong><?php echo esc_html( ip_course_name( $cid ) ); ?></strong>
									<?php if ( $meta ) : ?><span class="lp-card-m"><?php echo esc_html( implode( ' · ', $meta ) ); ?></span><?php endif; ?>
									<span class="lp-card-go">Mi interessa <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></span>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php elseif ( 'agevolazione' === $d['type'] ) : ?>
			<?php $cond = ip_lp_lines( ip_meta( 'cond', $d['agev'] ) ); $det = ip_meta( 'det', $d['agev'] ); ?>
			<?php if ( $cond || $det ) : ?>
				<section class="lp-sec">
					<div class="lp-w lp-split">
						<div class="lp-split-h">
							<?php $head( 'Condizioni', 'Come funziona l’agevolazione' ); ?>
							<p class="lp-note">Importi e condizioni stabiliti dall’Università Marconi e aggiornati automaticamente dal sito ufficiale. Ti confermiamo l’importo esatto prima dell’iscrizione.</p>
							<a class="lp-btn lp-btn-line" href="#richiedi" data-scroll-form>Verifica se ne hai diritto</a>
						</div>
						<div>
							<?php if ( $cond ) : ?>
								<ul class="lp-rules"><?php foreach ( $cond as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?></ul>
							<?php endif; ?>
							<?php if ( $det ) : ?><p class="lp-det"><?php echo nl2br( esc_html( $det ) ); ?></p><?php endif; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>
		<?php elseif ( 'generica' === $d['type'] && ip_tipologie() ) : ?>
			<section class="lp-sec">
				<div class="lp-w">
					<?php $head( 'Offerta formativa', 'Cosa puoi studiare' ); ?>
					<div class="lp-index">
						<?php foreach ( ip_tipologie() as $tt ) : ?>
							<a href="#richiedi" data-scroll-form data-set-interest="<?php echo esc_attr( $tt->name ); ?>"><strong><?php echo esc_html( $tt->name ); ?></strong><span><?php echo (int) $tt->count; ?> corsi</span><?php echo ip_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $on( 'piani' ) && $d['curricula'] ) : ?>
		<section class="lp-sec lp-sec-soft">
			<div class="lp-w lp-split">
				<div class="lp-split-h">
					<?php $head( count( $d['curricula'] ) > 1 ? count( $d['curricula'] ) . ' percorsi' : 'Piano di studi', 'Piani di studio' ); ?>
					<p class="lp-note">Esami, settori e crediti di ogni anno, come pubblicati dall’Ateneo.</p>
				</div>
				<div class="lp-acc">
					<?php foreach ( $d['curricula'] as $cu ) : ?>
						<details>
							<summary><?php echo esc_html( html_entity_decode( get_the_title( $cu ), ENT_QUOTES, 'UTF-8' ) ); ?></summary>
							<div class="lp-prose"><?php echo ip_lp_unlink( do_blocks( $cu->post_content ) ); // phpcs:ignore ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'costi' ) && 'agevolazione' !== $d['type'] ) : ?>
		<?php $cost = $d['course'] ? ip_meta( 'retta', $d['course'] ) : ''; ?>
		<?php if ( $cost ) : ?>
			<section class="lp-sec">
				<div class="lp-w lp-split">
					<div class="lp-split-h"><?php $head( 'Costi', 'Quanto costa' ); ?></div>
					<div class="lp-cost">
						<p class="lp-cost-n"><?php echo esc_html( $cost ); ?></p>
						<p>Importo stabilito dall’Ateneo e pagato direttamente all’Università. Ti spieghiamo rateizzazione e agevolazioni a cui hai diritto.</p>
						<a class="lp-btn" href="#richiedi" data-scroll-form>Chiedi le agevolazioni</a>
					</div>
				</div>
			</section>
		<?php elseif ( $is_degree && wp_count_posts( 'agevolazione' )->publish ) : ?>
			<?php $fees = ip_fees(); ?>
			<section class="lp-sec">
				<div class="lp-w lp-split">
					<div class="lp-split-h">
						<?php $head( 'Costi', 'Quanto costa' ); ?>
						<?php if ( $fees['std_retta'] ) : ?>
							<div class="lp-std">
								<p><span>Retta standard</span><strong><?php echo esc_html( ip_eur( $fees['std_retta'] ) ); ?></strong> l’anno</p>
								<?php if ( $fees['std_rata'] ) : ?><p><span>oppure</span><strong><?php echo esc_html( ip_eur( $fees['std_rata'] ) ); ?></strong> al mese</p><?php endif; ?>
							</div>
						<?php endif; ?>
						<p class="lp-note">Rate senza costi aggiuntivi. Importi dell’Ateneo, aggiornati automaticamente dal sito ufficiale.</p>
					</div>
					<div>
						<?php
						$agevs = get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
						$shown = 0;
						?>
						<table class="lp-tab">
							<thead><tr><th>Agevolazione</th><th class="n">Al mese</th><th class="n">All’anno</th></tr></thead>
							<tbody>
							<?php foreach ( $agevs as $ag ) : ?>
								<?php
								$rata  = ip_meta( 'rata', $ag->ID );
								$retta = ip_meta( 'retta', $ag->ID );
								$std   = 'retta-standard' === ( get_post_meta( $ag->ID, '_ip_fonte', true ) ? get_post_meta( $ag->ID, '_ip_fonte', true ) : $ag->post_name );
								if ( $std || ( ! $rata && ! $retta ) || $shown >= 7 ) {
									continue;
								}
								$shown++;
								?>
								<tr>
									<th scope="row"><strong><?php echo esc_html( html_entity_decode( $ag->post_title, ENT_QUOTES, 'UTF-8' ) ); ?></strong><span><?php echo esc_html( ip_meta( 'dest', $ag->ID ) ); ?></span></th>
									<td class="n"><?php echo $rata ? '<strong>' . esc_html( ip_eur( $rata ) ) . '</strong>' : '—'; ?></td>
									<td class="n"><?php echo $retta ? esc_html( ip_eur( $retta ) ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
						<p class="lp-tab-foot">Altre agevolazioni e convenzioni con enti e aziende: <a href="#richiedi" data-scroll-form>chiedici quale ti spetta</a>.</p>
					</div>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $on( 'passi' ) && ip_rows( 'steps' ) ) : ?>
		<section class="lp-sec lp-sec-soft">
			<div class="lp-w">
				<?php $head( 'Con noi', ip_opt( 't_passi' ) ); ?>
				<ol class="lp-steps">
					<?php foreach ( ip_rows( 'steps' ) as $st ) : ?>
						<li><h3><?php echo esc_html( $st['titolo'] ?? '' ); ?></h3><p><?php echo esc_html( $st['testo'] ?? '' ); ?></p></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'recensioni' ) && ip_rows( 'reviews' ) ) : ?>
		<section class="lp-sec">
			<div class="lp-w">
				<?php $head( 'Esperienze', ip_opt( 't_reviews' ) ); ?>
				<div class="lp-quotes">
					<?php foreach ( ip_rows( 'reviews' ) as $r ) : ?>
						<?php $stars = min( 5, max( 0, (int) $r['voto'] ) ); ?>
						<figure>
							<blockquote><?php echo esc_html( $r['testo'] ); ?></blockquote>
							<figcaption><strong><?php echo esc_html( $r['nome'] ); ?></strong><?php if ( $r['corso'] ) : ?><span><?php echo esc_html( $r['corso'] ); ?></span><?php endif; ?><?php if ( $stars ) : ?><em aria-label="<?php echo esc_attr( $stars ); ?> su 5"><?php echo esc_html( str_repeat( '★', $stars ) ); ?></em><?php endif; ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'faq' ) && ip_rows( 'faq' ) ) : ?>
		<section class="lp-sec">
			<div class="lp-w lp-split">
				<div class="lp-split-h">
					<?php $head( 'Domande', ip_opt( 't_faq' ) ); ?>
					<?php if ( ip_opt( 'phone1' ) ) : ?>
						<p class="lp-note">Non trovi la risposta? Chiamaci al <a href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo esc_html( ip_opt( 'phone1' ) ); ?></a>.</p>
					<?php endif; ?>
				</div>
				<div class="lp-acc">
					<?php foreach ( ip_rows( 'faq' ) as $r ) : ?>
						<details><summary><?php echo esc_html( $r['domanda'] ?? '' ); ?></summary><p><?php echo nl2br( esc_html( ip_fill( $r['risposta'] ?? '' ) ) ); ?></p></details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'finale' ) ) : ?>
		<section class="lp-end<?php echo $img ? ' has-img' : ''; ?>">
			<?php if ( $img ) : ?><div class="lp-end-img"><?php echo wp_get_attachment_image( $img, 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?></div><?php endif; ?>
			<div class="lp-w lp-end-in">
				<div class="lp-end-t">
					<p class="lp-k">Ultimo passo</p>
					<h2>Preferisci che ti chiamiamo noi?</h2>
					<p>Lascia nome e numero: un orientatore ti richiama quando preferisci e ti spiega percorso, costi e agevolazioni. La consulenza è gratuita e non ti impegna.</p>
					<ul class="lp-end-c">
						<?php if ( ip_opt( 'phone1' ) ) : ?><li><a href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo ip_icon( 'phone', 18 ); // phpcs:ignore ?><?php echo esc_html( ip_opt( 'phone1' ) ); ?></a></li><?php endif; ?>
						<?php if ( $wa ) : ?><li><a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 18 ); // phpcs:ignore ?>Scrivici su WhatsApp</a></li><?php endif; ?>
						<?php if ( ip_opt( 'hours' ) ) : ?><li class="lp-end-h"><?php echo ip_icon( 'clock', 18 ); // phpcs:ignore ?><span><?php echo nl2br( esc_html( ip_opt( 'hours' ) ) ); ?></span></li><?php endif; ?>
					</ul>
				</div>
				<div class="lp-end-f">
					<?php ip_form( array( 'type' => 'callback', 'course' => $d['course'], 'interest' => $d['interest'], 'landing' => $lp_id, 'inline' => true, 'id' => 'richiamo' ) ); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>

<footer class="lp-foot">
	<div class="lp-w">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( ip_legal_line() ); ?></p>
		<p class="lp-foot-l">
			<?php if ( $chi ) : ?><button type="button" class="lp-link" data-lp-open="lp-chi">Chi siamo</button><?php endif; ?>
			<?php if ( $privacy_id ) : ?><button type="button" class="lp-link" data-lp-open="lp-privacy">Privacy</button><?php endif; ?>
			<?php if ( $cookie_id ) : ?><button type="button" class="lp-link" data-lp-open="lp-cookie">Cookie</button><?php endif; ?>
		</p>
		<?php if ( ip_opt( 'disclaimer' ) ) : ?><p class="lp-foot-d"><?php echo esc_html( ip_text( 'disclaimer' ) ); ?></p><?php endif; ?>
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
	<div class="lp-thx">
		<p class="lead-title">Grazie{nome}, richiesta ricevuta.</p>
		<p>Un orientatore ti contatta entro un giorno lavorativo al numero che hai indicato. La chiamata arriva da <?php echo esc_html( ip_opt( 'brand' ) ); ?>.</p>
		<?php if ( ip_opt( 'phone1' ) ) : ?>
			<p><a class="lp-btn lp-btn-block" href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call">Hai fretta? Chiama il <?php echo esc_html( ip_opt( 'phone1' ) ); ?></a></p>
		<?php endif; ?>
	</div>
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
			<button type="button" class="lp-x" data-lp-close aria-label="Chiudi"><?php echo ip_icon( 'close', 20 ); // phpcs:ignore ?></button>
			<?php ip_form( array( 'type' => 'callback', 'course' => $d['course'], 'interest' => $d['interest'], 'landing' => $lp_id, 'inline' => true, 'id' => 'lp-exit-form', 'title' => 'Prima di andare: ti richiamiamo noi?', 'text' => 'Lascia nome e numero. Un orientatore ti spiega costi e agevolazioni, gratis e senza impegno.' ) ); ?>
		</div>
	</dialog>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
