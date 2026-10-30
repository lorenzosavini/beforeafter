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
$privacy_id = 'publish' === get_post_status( $privacy_id ) ? $privacy_id : 0;
$cookie_id  = 'publish' === get_post_status( $cookie_id ) ? $cookie_id : 0;
$chi        = ip_page_url( 'chi-siamo' );
$tip        = $d['course'] ? ip_course_tipologia( $d['course'] ) : null;
$is_degree  = ( $tip && 0 === strpos( $tip->slug, 'laurea' ) )
	|| ( 'tipologia' === $d['type'] && '' !== $d['price'] )
	|| 'generica' === $d['type'];
list( $t_pre, $t_main, $t_code ) = ip_lp_title_parts( $d );
$spec    = ip_lp_spec( $d );
$figures = ip_lp_figures();
$img     = $d['image'];

$name    = $d['name'] ? $d['name'] : ip_opt( 'brand' );

/**
 * Titolo di sezione, con una riga di contesto facoltativa.
 */
$head = function ( $title, $sub = '' ) {
	printf( '<header class="lp-sh"><h2>%s</h2>%s</header>', esc_html( $title ), $sub ? '<p>' . esc_html( $sub ) . '</p>' : '' );
};
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0e261d">
<?php if ( ip_opt( 'font_brand' ) ) : // Montserrat solo con connessione veloce, senza precaricarlo: la banda va prima all'immagine principale. ?><script>(function(d){var c=navigator.connection||{};if(c.saveData||/2g|3g/.test(c.effectiveType||''))return;d.documentElement.classList.add('wf')})(document)</script><?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php echo ip_notice_bar( true ); // phpcs:ignore ?>

<?php echo ip_disclosure_bar( true ); // phpcs:ignore ?>

<header class="lp-top">
	<div class="lp-w lp-top-in">
		<span class="lp-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo ip_logo_image( 'lp-logo', 'eager', '(max-width: 900px) 170px, 250px' ); // phpcs:ignore ?>
			<?php else : ?>
				<span class="lp-mark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( ip_opt( 'brand' ), 0, 1 ) ) ); ?></span>
				<span class="lp-brand-t"><strong><?php echo esc_html( ip_opt( 'brand' ) ); ?></strong><small><?php echo esc_html( ip_role_label() ); ?></small></span>
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
			<div class="lp-hero-img"><?php echo wp_get_attachment_image( $img, 'full', false, array( 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(max-width: 700px) 35vw, (max-width: 900px) 100vw, 45vw' ) ); // Da telefono resta sotto una velatura al 93%: basta la versione piccola. ?></div>
		<?php endif; ?>
		<div class="lp-w lp-hero-in">
			<div class="lp-hero-t">
				<?php $k_txt = false !== strpos( $d['kicker'], ip_opt( 'brand' ) ) ? ip_role_label() : $d['kicker']; ?>
				<p class="lp-badge-row"><span class="hero-badge"><?php echo esc_html( ip_opt( 'brand' ) ); ?></span> <span class="lp-badge-t"><?php echo esc_html( $k_txt ); ?></span></p>
				<h1>
					<?php if ( $t_pre ) : ?><span class="lp-h1-pre"><?php echo esc_html( $t_pre ); ?></span><?php endif; ?>
					<?php $h1 = ip_hl( $t_main, true ); echo false === strpos( $h1, 'class="hl"' ) ? '<span class="hl">' . $h1 . '</span>' : $h1; // phpcs:ignore ?>
					<?php if ( $t_code ) : ?><span class="lp-h1-code"><?php echo esc_html( $t_code ); ?></span><?php endif; ?>
				</h1>
				<?php if ( $d['sub'] ) : ?><p class="lp-lead"><?php echo ip_hl( $d['sub'] ); // phpcs:ignore ?></p><?php endif; ?>

				<?php if ( $spec ) : ?>
					<ul class="lp-chips">
						<?php foreach ( $spec as $label => $value ) : ?>
							<li><span><?php echo esc_html( $label ); ?></span> <?php echo esc_html( $value ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $d['points'] ) : ?>
					<ul class="lp-pts">
						<?php foreach ( array_slice( $d['points'], 0, 3 ) as $p ) : ?><li><?php echo ip_icon( 'check', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $p ); ?></span></li><?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="lp-acts">
					<?php echo ip_phone_link( 'phone1', 'btn btn-white' ); // phpcs:ignore ?>
					<?php if ( ip_opt( 'whatsapp' ) ) : ?>
						<a class="btn btn-line-light" href="<?php echo esc_url( ip_wa_href( 'Ciao, vorrei informazioni su ' . wp_strip_all_tags( $d['title'] ) . '.' ) ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 18 ); // phpcs:ignore ?><span>Scrivici su WhatsApp</span></a>
					<?php endif; ?>
				</div>

				<?php if ( $d['price'] && 'agevolazione' !== $d['type'] ) : ?>
					<p class="lp-price"><?php echo wp_kses( $d['price'], array( 'strong' => array() ) ); ?><?php if ( ip_agev_deadline_text() && false !== strpos( $d['price'], 'agevolazioni' ) ) : ?><br><small><?php echo esc_html( ip_agev_deadline_text() ); ?></small><?php endif; ?></p>
				<?php elseif ( $d['deadline'] ) : ?>
					<p class="lp-price"><small>Richieste entro il <?php echo esc_html( $d['deadline'] ); ?></small></p>
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
				<p class="lp-assure">Gratuito e senza impegno · Ti seguiamo fino all’iscrizione</p>
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
			<?php $tabs = ip_lp_tabs( $d['content'] ); ?>
			<section class="lp-sec">
				<div class="lp-w">
					<?php $head( 'Cosa studi in ' . $name, 'Insegnamenti, piani di studio e sbocchi del corso.' ); ?>
					<div class="lp-tabs" data-lp-tabs>
						<div class="lp-tabs-nav" role="tablist">
							<?php foreach ( $tabs as $i => $t ) : ?>
								<button type="button" role="tab" id="tab-<?php echo esc_attr( $t['key'] ); ?>" aria-controls="pan-<?php echo esc_attr( $t['key'] ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"><?php echo esc_html( $t['title'] ); ?></button>
							<?php endforeach; ?>
						</div>
						<div class="lp-tabs-body">
							<?php foreach ( $tabs as $i => $t ) : ?>
								<div class="lp-pan" role="tabpanel" id="pan-<?php echo esc_attr( $t['key'] ); ?>" aria-labelledby="tab-<?php echo esc_attr( $t['key'] ); ?>">
									<h3 class="lp-pan-h"><?php echo esc_html( $t['title'] ); ?></h3>
									<div class="lp-prose<?php echo $t['long'] ? ' lp-clip' : ''; ?>" id="txt-<?php echo esc_attr( $t['key'] ); ?>"><?php echo $t['html']; // phpcs:ignore ?></div>
									<?php if ( $t['long'] ) : ?><button type="button" class="lp-more" data-lp-expand="txt-<?php echo esc_attr( $t['key'] ); ?>">Leggi tutto</button><?php endif; ?>
								</div>
							<?php endforeach; ?>
							<aside class="lp-tabs-cta">
								<p>Vuoi il piano di studi completo, con esami e crediti di ogni anno?</p>
								<a class="lp-btn" href="#richiedi" data-scroll-form>Ricevilo gratis</a>
							</aside>
						</div>
					</div>
				</div>
			</section>
		<?php elseif ( 'tipologia' === $d['type'] && $d['courses'] ) : ?>
			<section class="lp-sec">
				<div class="lp-w">
					<?php $head( count( $d['courses'] ) . ' corsi: scegli quello che fa per te', 'Tocca un corso: lo aggiungiamo alla tua richiesta.' ); ?>
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
							<?php $head( 'Come funziona ' . $name ); ?>
							<p class="lp-note">Importi e condizioni stabiliti dall’Università Marconi. Ti confermiamo l’importo esatto prima dell’iscrizione.</p>
							<?php if ( ! ip_agev_deadline( $d['agev'] ) ) : ?><?php echo ip_agev_deadline_note( 'lp-due-note' ); // phpcs:ignore ?><?php endif; ?>
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
					<?php $head( 'Cosa puoi studiare all’Università Marconi', 'Scegli un’area: un orientatore ti presenta i corsi adatti a te.' ); ?>
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
					<?php $head( count( $d['curricula'] ) > 1 ? count( $d['curricula'] ) . ' piani di studio tra cui scegliere' : 'Il piano di studi' ); ?>
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
					<div class="lp-split-h"><?php $head( 'Quanto costa ' . $name ); ?></div>
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
						<?php $head( 'Quanto costa' . ( 'corso' === $d['type'] ? ' ' . $name : ' la laurea' ) ); ?>
						<?php if ( $fees['std_retta'] ) : ?>
							<div class="lp-std">
								<p><span>Retta standard</span><strong><?php echo esc_html( ip_eur( $fees['std_retta'] ) ); ?></strong> l’anno</p>
								<?php if ( $fees['std_rata'] ) : ?><p><span>oppure</span><strong><?php echo esc_html( ip_eur( $fees['std_rata'] ) ); ?></strong> al mese</p><?php endif; ?>
							</div>
						<?php endif; ?>
						<p class="lp-note">Rate senza costi aggiuntivi. Importi stabiliti dall’Ateneo.</p>
						<?php echo ip_agev_deadline_note( 'lp-due-note' ); // phpcs:ignore ?>
					</div>
					<div>
						<?php ip_agevolazioni( 6, array( 'no_std' => true, 'priced' => true, 'note' => false ) ); ?>
						<p class="lp-tab-foot">Altre agevolazioni e convenzioni con enti e aziende: <a href="#richiedi" data-scroll-form>chiedici quale ti spetta</a>.</p>
					</div>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php $m = $on( 'sedi' ) ? ip_lp_map_data() : array( 'count' => 0 ); ?>
	<?php if ( $m['count'] && ! empty( $m['map']['regions'] ) ) : ?>
		<section class="lp-sec lp-sedi">
			<div class="lp-w lp-map-grid">
				<?php // La mappa (≈ 30 KB) arriva a parte, quando ci si avvicina: la pagina si apre prima. ?>
				<div class="lp-map" data-lp-map data-map-src="<?php echo esc_url( add_query_arg( 'ip_lp_map', substr( md5( ip_lp_map_svg( $m ) ), 0, 10 ), home_url( '/' ) ) ); ?>" style="aspect-ratio: <?php echo esc_attr( $m['map']['w'] . ' / ' . $m['map']['h'] ); ?>"></div>
				<div class="lp-map-t">
					<?php $head( 'Gli esami li sostieni in presenza, in ' . $m['count'] . ' sedi', count( $m['regions'] ) . ' regioni: scegli la più comoda quando prenoti l’appello. Elenco ufficiale dell’Ateneo, aggiornato automaticamente.' ); ?>
					<div class="lp-regs">
						<?php foreach ( $m['regions'] as $reg => $sites ) : ?>
							<details data-region="<?php echo esc_attr( $reg ); ?>">
								<summary><strong><?php echo esc_html( $reg ); ?></strong><span><?php echo esc_html( implode( ', ', array_unique( wp_list_pluck( $sites, 'citta' ) ) ) ); ?></span></summary>
								<ul><?php foreach ( $sites as $st ) : ?><li><strong><?php echo esc_html( $st['citta'] ); ?></strong> <?php echo esc_html( $st['indirizzo'] ); ?></li><?php endforeach; ?></ul>
							</details>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'passi' ) && ip_rows( 'steps' ) ) : ?>
		<section class="lp-sec lp-sec-soft">
			<div class="lp-w">
				<?php $head( ip_opt( 't_passi' ) ); ?>
				<ol class="lp-steps">
					<?php foreach ( ip_rows( 'steps' ) as $st ) : ?>
						<li><h3><?php echo esc_html( $st['titolo'] ?? '' ); ?></h3><p><?php echo esc_html( $st['testo'] ?? '' ); ?></p></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php $team = ip_lp_team(); ?>
	<?php if ( $on( 'team' ) && ( $team['text'] || $team['photo'] || $team['people'] ) ) : ?>
		<section class="lp-sec lp-team has-img">
			<div class="lp-w lp-team-grid">
				<?php if ( $team['photo'] ) : ?>
					<figure class="lp-team-img"><?php echo wp_get_attachment_image( $team['photo'], 'large', false, array( 'loading' => 'lazy', 'alt' => esc_attr( ip_opt( 'brand' ) ) ) ); ?></figure>
				<?php else : ?>
					<div class="lp-team-call">
						<p class="lp-team-mark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( ip_opt( 'brand' ), 0, 1 ) ) ); ?></p>
						<p>Chiamaci o scrivici: ti risponde un orientatore di <?php echo esc_html( ip_opt( 'brand' ) ); ?>.</p>
						<?php if ( ip_opt( 'phone1' ) ) : ?><a class="lp-team-tel" href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo esc_html( ip_opt( 'phone1' ) ); ?></a><?php endif; ?>
						<?php if ( $wa ) : ?><a class="lp-team-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 18 ); // phpcs:ignore ?> Scrivici su WhatsApp</a><?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="lp-team-t">
					<?php $head( $team['title'] ); ?>
					<p class="lp-team-lead"><?php echo esc_html( $team['text'] ); ?></p>
					<?php if ( $team['people'] ) : ?>
						<ul class="lp-people">
							<?php foreach ( $team['people'] as $pp ) : ?>
								<li>
									<?php if ( ! empty( $pp['foto'] ) ) : ?><img src="<?php echo esc_url( $pp['foto'] ); ?>" alt="" loading="lazy" width="64" height="64"><?php else : ?><span class="lp-ini" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $pp['nome'], 0, 1 ) ) ); ?></span><?php endif; ?>
									<span><strong><?php echo esc_html( $pp['nome'] ); ?></strong><?php echo esc_html( $pp['ruolo'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<dl class="lp-office">
						<?php if ( ip_opt( 'address' ) ) : ?><div><dt>Sede</dt><dd><?php echo nl2br( esc_html( ip_opt( 'address' ) ) ); ?></dd></div><?php endif; ?>
						<?php if ( ip_opt( 'hours' ) ) : ?><div><dt>Orari</dt><dd><?php echo nl2br( esc_html( ip_opt( 'hours' ) ) ); ?></dd></div><?php endif; ?>
						<?php if ( ip_opt( 'company_name' ) ) : ?><div><dt>Chi siamo</dt><dd><?php echo esc_html( ip_legal_line() ); ?> <button type="button" class="lp-link" data-lp-open="lp-chi">Leggi di più</button></dd></div><?php endif; ?>
					</dl>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $on( 'recensioni' ) && ip_rows( 'reviews' ) ) : ?>
		<section class="lp-sec">
			<div class="lp-w">
				<?php $head( ip_opt( 't_reviews' ) ); ?>
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
					<?php $head( ip_opt( 't_faq' ) ); ?>
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
			<?php echo ip_consent_button( 'lp-link', 'Preferenze cookie' ); // phpcs:ignore ?>
		</p>
		<?php if ( ip_opt( 'disclaimer' ) ) : ?><p class="lp-foot-d"><?php echo esc_html( ip_text( 'disclaimer' ) ); ?></p><?php endif; ?>
	</div>
</footer>

<?php if ( ip_site_on( 'mbar' ) ) : ?>
<nav class="mbar" aria-label="Contatto rapido">
	<?php if ( ip_opt( 'phone1' ) ) : ?>
		<a href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo ip_icon( 'phone', 20 ); // phpcs:ignore ?><span>Chiama</span></a>
	<?php endif; ?>
	<?php if ( $wa ) : ?>
		<a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="whatsapp"><?php echo ip_icon( 'whatsapp', 20 ); // phpcs:ignore ?><span>WhatsApp</span></a>
	<?php endif; ?>
	<a class="mbar-main" href="#richiedi" data-scroll-form><?php echo ip_icon( 'doc', 20 ); // phpcs:ignore ?><span>Richiedi info</span></a>
</nav>
<?php endif; ?>

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
ip_lp_dialog( 'cookie', 'Cookie policy', ip_lp_page_html( $cookie_id ), $cookie_id ? get_permalink( $cookie_id ) : '' );
?>

<?php
if ( $o['uscita'] ) {
	ip_exit_banner( array( 'landing' => $lp_id, 'course' => $d['course'], 'interest' => $d['interest'] ) );
}
?>

<?php wp_footer(); ?>
</body>
</html>
