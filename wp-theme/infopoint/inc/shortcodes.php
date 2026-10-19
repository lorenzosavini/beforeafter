<?php
/**
 * Shortcode per comporre le pagine dall'editor senza page builder.
 *
 * [ip_modulo tipo="info|cfu|callback" titolo="" testo=""]
 * [ip_corsi tipologia="laurea-triennale,laurea-magistrale" filtro="si"]
 * [ip_agevolazioni]
 * [ip_sedi]
 * [ip_contatti]
 * [ip_passi]
 * [ip_cta titolo=""]
 * [ip_faq]
 * [ip_ufficiale fonte="https://www.unimarconi.it/…/"]
 */

defined( 'ABSPATH' ) || exit;

function ip_buffer( $cb ) {
	ob_start();
	$cb();
	return ob_get_clean();
}

add_shortcode( 'ip_modulo', function ( $a ) {
	$a = shortcode_atts( array( 'tipo' => 'info', 'titolo' => '', 'testo' => '', 'corso' => 0, 'pulsante' => '' ), $a );
	return ip_buffer( function () use ( $a ) {
		ip_form( array( 'type' => $a['tipo'], 'title' => $a['titolo'], 'text' => $a['testo'], 'course' => $a['corso'], 'button' => $a['pulsante'] ) );
	} );
} );

add_shortcode( 'ip_corsi', function ( $a ) {
	$a = shortcode_atts( array( 'tipologia' => '', 'filtro' => 'si', 'limite' => 300 ), $a );
	return ip_buffer( function () use ( $a ) {
		ip_course_list( array_filter( array_map( 'trim', explode( ',', $a['tipologia'] ) ) ), 'no' !== $a['filtro'], (int) $a['limite'] );
	} );
} );

/**
 * Elenco corsi raggruppato per tipologia, con filtro testuale e per tipo.
 */
function ip_course_list( $slugs = array(), $filter = true, $limit = 300 ) {
	$tips = ip_tipologie();
	if ( $slugs ) {
		$tips = array_values( array_filter( $tips, function ( $t ) use ( $slugs ) {
			return in_array( $t->slug, $slugs, true );
		} ) );
	}
	if ( ! $tips ) {
		echo '<p>Nessun corso pubblicato.</p>';
		return;
	}
	?>
	<div class="clist" data-clist>
		<?php if ( $filter ) : ?>
			<div class="cfilter">
				<label class="cfilter-search">
					<?php echo ip_icon( 'search', 18 ); // phpcs:ignore ?>
					<span class="sr">Cerca un corso</span>
					<input type="search" placeholder="Cerca per nome o classe (es. psicologia, L-18)" data-cf-q>
				</label>
				<?php if ( count( $tips ) > 1 ) : ?>
					<div class="chips" role="group" aria-label="Filtra per tipologia">
						<button type="button" class="chip" aria-pressed="true" data-cf-t="">Tutti</button>
						<?php foreach ( $tips as $t ) : ?>
							<button type="button" class="chip" aria-pressed="false" data-cf-t="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?> <span><?php echo (int) $t->count; ?></span></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php foreach ( $tips as $t ) : ?>
			<?php
			$q = new WP_Query( array(
				'post_type'      => 'corso',
				'posts_per_page' => $limit,
				'no_found_rows'  => true,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'tax_query'      => array( array( 'taxonomy' => 'tipologia', 'terms' => $t->term_id, 'include_children' => false ) ),
			) );
			if ( ! $q->have_posts() ) {
				continue;
			}
			?>
			<section class="cgroup" data-group="<?php echo esc_attr( $t->slug ); ?>">
				<?php if ( count( $tips ) > 1 ) : // Con una sola tipologia titolo e descrizione sono già nella testata. ?>
				<h2 class="cgroup-title"><?php echo esc_html( $t->name ); ?> <span><?php echo (int) $q->post_count; ?></span></h2>
				<?php endif; ?>
				<?php if ( $t->description && count( $tips ) > 1 ) : ?>
					<p class="cgroup-desc"><?php echo esc_html( $t->description ); ?></p>
				<?php endif; ?>
				<ul class="crows">
					<?php
					while ( $q->have_posts() ) {
						$q->the_post();
						ip_course_row();
					}
					wp_reset_postdata();
					?>
				</ul>
			</section>
		<?php endforeach; ?>
		<p class="cempty" hidden>Nessun corso corrisponde alla ricerca. <a href="#richiedi" data-scroll-form>Chiedi a un orientatore</a>: l’offerta si aggiorna spesso.</p>
	</div>
	<?php
}

add_shortcode( 'ip_agevolazioni', function ( $a ) {
	$a = shortcode_atts( array( 'stile' => 'schede' ), $a );
	return ip_buffer( 'tabella' === $a['stile'] ? 'ip_agevolazioni_table' : 'ip_agevolazioni' );
} );

/**
 * Schede delle agevolazioni.
 *
 * @param int   $limit Numero massimo (-1 = tutte).
 * @param array $args  no_std: senza la retta standard; priced: solo con importo; note: nota sulle scadenze in fondo.
 */
function ip_agevolazioni( $limit = -1, $args = array() ) {
	$args = wp_parse_args( $args, array( 'no_std' => false, 'priced' => false, 'note' => true ) );
	$q    = get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	$num  = function ( $v ) {
		return (float) str_replace( ',', '.', str_replace( '.', '', (string) $v ) );
	};
	$q = array_values( array_filter( $q, function ( $p ) use ( $args ) {
		$key = get_post_meta( $p->ID, '_ip_fonte', true ) ? get_post_meta( $p->ID, '_ip_fonte', true ) : $p->post_name;
		if ( $args['no_std'] && 'retta-standard' === $key ) {
			return false;
		}
		return ! $args['priced'] || ip_meta( 'rata', $p->ID ) || ip_meta( 'retta', $p->ID );
	} ) );
	if ( $limit > 0 ) {
		$q = array_slice( $q, 0, $limit );
	}
	if ( ! $q ) {
		return;
	}
	$fees = ip_fees();
	$std  = $num( $fees['std_retta'] );
	$best = $num( $fees['min_rata'] );
	echo '<div class="agev">';
	foreach ( $q as $p ) {
		$title = get_the_title( $p );
		$retta = ip_meta( 'retta', $p->ID );
		$rata  = ip_meta( 'rata', $p->ID );
		$cond  = array_filter( array_map( 'trim', explode( "\n", ip_meta( 'cond', $p->ID ) ) ) );
		$det   = ip_meta( 'det', $p->ID );
		$save  = $std && $num( $retta ) > 0 && $num( $retta ) < $std ? $std - $num( $retta ) : 0;
		$full  = $std && $num( $retta ) === $std;
		?>
		<?php
		$is_best = $best && $num( $rata ) === $best;
		$top     = array_slice( $cond, 0, 3 );
		$cut     = function ( $t ) {
			return mb_strlen( $t ) > 110 ? rtrim( mb_substr( $t, 0, 108 ), ' ,;.' ) . '…' : $t;
		};
		?>
		<article class="agev-item<?php echo $is_best ? ' agev-best' : ''; ?>">
			<header class="agev-hd">
				<?php if ( $is_best ) : ?><p class="agev-flag">La più conveniente</p><?php endif; ?>
				<?php if ( ip_agev_deadline( $p->ID ) ) : ?><p class="agev-badge"><?php echo ip_icon( 'calendar', 14 ); // phpcs:ignore ?> Richieste entro il <?php echo esc_html( ip_agev_deadline( $p->ID ) ); ?></p><?php endif; ?>
				<div class="agev-head">
					<span class="agev-ico"><?php echo ip_icon( ip_agev_icon( $title ), 20 ); // phpcs:ignore ?></span>
					<h3><?php echo esc_html( $title ); ?></h3>
				</div>
				<p class="agev-dest"><?php echo esc_html( ip_meta( 'dest', $p->ID ) ); ?></p>
			</header>
			<div class="agev-body">
				<div class="agev-price<?php echo $rata || $retta ? '' : ' agev-price-none'; ?>">
					<?php if ( $rata ) : ?>
						<p><strong>€ <?php echo esc_html( $rata ); ?></strong><span>/mese</span></p>
						<?php if ( $retta ) : ?><small>€ <?php echo esc_html( number_format( $num( $retta ), 0, ',', '.' ) ); ?> l’anno</small><?php endif; ?>
					<?php elseif ( $retta ) : ?>
						<p><strong>€ <?php echo esc_html( number_format( $num( $retta ), 0, ',', '.' ) ); ?></strong><span>/anno</span></p>
					<?php else : ?>
						<p class="agev-ask">Importo su misura</p><small>Ti diciamo noi quanto paghi</small>
					<?php endif; ?>
					<?php if ( $save ) : ?>
						<span class="agev-save">Risparmi € <?php echo esc_html( number_format( $save, 0, ',', '.' ) ); ?> l’anno</span>
					<?php elseif ( $full ) : ?>
						<span class="agev-save agev-full">Retta piena</span>
					<?php endif; ?>
				</div>
				<?php if ( $top ) : ?>
					<ul class="agev-checks">
						<?php foreach ( $top as $c ) : ?><li><?php echo ip_icon( 'check', 16 ); // phpcs:ignore ?><span><?php echo esc_html( $cut( $c ) ); ?></span></li><?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( count( $cond ) > 3 || $det || array_filter( $top, function ( $c ) { return mb_strlen( $c ) > 110; } ) ) : ?>
					<details>
						<summary>Tutte le condizioni</summary>
						<?php if ( $cond ) : ?><ul><?php foreach ( $cond as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?></ul><?php endif; ?>
						<?php if ( $det ) : ?><p class="agev-det"><?php echo nl2br( esc_html( $det ) ); ?></p><?php endif; ?>
					</details>
				<?php endif; ?>
				<a class="agev-cta" href="#richiedi" data-scroll-form data-interest="<?php echo esc_attr( $title ); ?>">Verifica se ne hai diritto <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
			</div>
		</article>
		<?php
	}
	echo '</div>';
	if ( $args['note'] ) {
		echo ip_agev_deadline_note(); // phpcs:ignore
	}
}

add_shortcode( 'ip_sedi', function () {
	$by = array();
	foreach ( ip_rows( 'exam_sites' ) as $r ) {
		$by[ $r['regione'] ?: 'Altre sedi' ][] = $r;
	}
	if ( ! $by ) {
		return '';
	}
	ksort( $by );
	return ip_buffer( function () use ( $by ) {
		echo '<div class="sedi">';
		foreach ( $by as $region => $list ) {
			echo '<section><h3>' . esc_html( $region ) . '</h3><ul>';
			foreach ( $list as $st ) {
				printf(
					'<li><strong>%s</strong><a href="%s" target="_blank" rel="noopener">%s</a></li>',
					esc_html( $st['citta'] ),
					esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $st['indirizzo'] . ' ' . $st['citta'] ) ),
					esc_html( $st['indirizzo'] )
				);
			}
			echo '</ul></section>';
		}
		echo '</div>';
	} );
} );

add_shortcode( 'ip_contatti', function () {
	return ip_buffer( 'ip_contact_block' );
} );

function ip_contact_block() {
	$wa = ip_wa_href();
	?>
	<div class="contacts">
		<?php foreach ( array( 'phone1', 'phone2' ) as $p ) : ?>
			<?php if ( ip_opt( $p ) ) : ?>
				<a class="contact" href="<?php echo esc_attr( ip_tel_href( ip_opt( $p ) ) ); ?>" data-track="call">
					<?php echo ip_icon( 'phone', 22 ); // phpcs:ignore ?>
					<span><small><?php echo esc_html( ip_opt( $p . '_label' ) ); ?></small><?php echo esc_html( ip_opt( $p ) ); ?></span>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $wa ) : ?>
			<a class="contact" href="<?php echo esc_url( $wa ); ?>" data-track="whatsapp" target="_blank" rel="noopener">
				<?php echo ip_icon( 'whatsapp', 22 ); // phpcs:ignore ?>
				<span><small>WhatsApp</small>Scrivici un messaggio</span>
			</a>
		<?php endif; ?>
		<?php if ( ip_opt( 'email' ) ) : ?>
			<a class="contact" href="mailto:<?php echo esc_attr( ip_opt( 'email' ) ); ?>" data-track="email">
				<?php echo ip_icon( 'mail', 22 ); // phpcs:ignore ?>
				<span><small>Email</small><?php echo esc_html( ip_opt( 'email' ) ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( ip_opt( 'address' ) ) : ?>
			<div class="contact">
				<?php echo ip_icon( 'pin', 22 ); // phpcs:ignore ?>
				<span><small>Sede</small><?php echo nl2br( esc_html( ip_opt( 'address' ) ) ); ?>
				<?php if ( ip_opt( 'maps_url' ) ) : ?><br><a href="<?php echo esc_url( ip_opt( 'maps_url' ) ); ?>" target="_blank" rel="noopener">Indicazioni stradali</a><?php endif; ?></span>
			</div>
		<?php endif; ?>
		<?php if ( ip_opt( 'address_exam' ) ) : ?>
			<div class="contact">
				<?php echo ip_icon( 'doc', 22 ); // phpcs:ignore ?>
				<span><small>Sede d’esame</small><?php echo nl2br( esc_html( ip_opt( 'address_exam' ) ) ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( ip_opt( 'hours' ) ) : ?>
			<div class="contact">
				<?php echo ip_icon( 'clock', 22 ); // phpcs:ignore ?>
				<span><small>Orari</small><?php echo nl2br( esc_html( ip_opt( 'hours' ) ) ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

add_shortcode( 'ip_passi', function () {
	return ip_buffer( 'ip_steps' );
} );

function ip_steps() {
	$steps = ip_rows( 'steps' );
	if ( ! $steps ) {
		return;
	}
	echo '<ol class="steps">';
	foreach ( $steps as $st ) {
		printf( '<li><h3>%s</h3><p>%s</p></li>', esc_html( $st['titolo'] ?? '' ), esc_html( $st['testo'] ?? '' ) );
	}
	echo '</ol>';
}

add_shortcode( 'ip_faq', function () {
	return ip_buffer( function () {
		ip_faq_list( ip_rows( 'faq' ) );
	} );
} );

/**
 * Elenco domande/risposte apribili.
 */
function ip_faq_list( $rows ) {
	if ( ! $rows ) {
		return;
	}
	echo '<div class="faq">';
	foreach ( $rows as $r ) {
		printf( '<details><summary>%s</summary><p>%s</p></details>', esc_html( $r['domanda'] ?? '' ), nl2br( esc_html( ip_fill( $r['risposta'] ?? '' ) ) ) );
	}
	echo '</div>';
}

add_shortcode( 'ip_cta', function ( $a ) {
	$a = shortcode_atts( array( 'titolo' => '' ), $a );
	return ip_buffer( function () use ( $a ) {
		ip_cta_band( $a['titolo'] );
	} );
} );

/**
 * [ip_chi_siamo] — scheda di trasparenza: chi siete, che rapporto avete con
 * l'Ateneo, cosa fate e cosa no. È la pagina che Google e gli utenti cercano
 * per capire che il sito non è quello dell'Università.
 */
add_shortcode( 'ip_chi_siamo', function () {
	return ip_buffer( function () {
		$azienda = ip_opt( 'company_name' ) ? ip_opt( 'company_name' ) : ip_opt( 'brand' );
		?>
		<div class="whois">
			<p class="whois-lead"><?php echo esc_html( ip_text( 'disclosure' ) ); ?></p>
			<dl class="whois-dl">
				<div><dt>Denominazione del polo</dt><dd><?php echo esc_html( ip_opt( 'brand' ) ); ?></dd></div>
				<div><dt>Gestito da</dt><dd><?php echo esc_html( $azienda ); ?></dd></div>
				<?php foreach ( array( 'legal_address' => 'Sede legale', 'address' => 'Sede operativa', 'vat' => 'Partita IVA', 'rea' => 'REA', 'capital' => 'Capitale sociale', 'pec' => 'PEC', 'email' => 'Email', 'phone1' => 'Telefono', 'phone2' => 'Altro telefono' ) as $k => $l ) : ?>
					<?php if ( ip_opt( $k ) ) : ?>
						<div><dt><?php echo esc_html( $l ); ?></dt><dd><?php echo nl2br( esc_html( ip_opt( $k ) ) ); ?></dd></div>
					<?php endif; ?>
				<?php endforeach; ?>
				<div><dt>Rapporto con l’Ateneo</dt><dd><?php echo esc_html( ucfirst( ip_opt( 'partner_role' ) ) ); ?><?php echo ip_opt( 'partner_since' ) ? ' (' . esc_html( ip_opt( 'partner_since' ) ) . ')' : ''; ?><?php if ( ip_opt( 'partner_letter' ) ) : ?> · <a href="<?php echo esc_url( ip_opt( 'partner_letter' ) ); ?>" target="_blank" rel="noopener">Lettera di autorizzazione dell’Ateneo (PDF)</a><?php endif; ?></dd></div>
			</dl>
			<div class="whois-cols">
				<section>
					<h2>Cosa facciamo</h2>
					<ul>
						<li>Orientamento gratuito sulla scelta del corso</li>
						<li>Richiesta della prevalutazione dei crediti (CFU)</li>
						<li>Assistenza nella procedura di immatricolazione</li>
						<li>Supporto durante il percorso: piano di studi, esami, scadenze</li>
					</ul>
				</section>
				<section>
					<h2>Cosa non facciamo</h2>
					<ul>
						<li>Non siamo l’Università e non rilasciamo titoli: i titoli sono rilasciati dall’Università degli Studi Guglielmo Marconi</li>
						<li>Non incassiamo rette o tasse universitarie: si pagano direttamente all’Ateneo (PagoPA o addebito SEPA)</li>
						<li>Non decidiamo ammissioni, convalide o agevolazioni: le stabilisce l’Ateneo</li>
					</ul>
				</section>
			</div>
			<p class="whois-note">Marchi e denominazioni dell’Università degli Studi Guglielmo Marconi appartengono ai rispettivi titolari e sono citati per indicare il servizio offerto.</p>
		</div>
		<?php
	} );
} );

/**
 * Agevolazioni in tabella: più leggibile delle schede quando si confrontano
 * gli importi. [ip_agevolazioni stile="tabella"]
 */
/**
 * Elenco compatto delle agevolazioni (home): retta standard come riferimento,
 * poi una riga per agevolazione con icona, a chi è rivolta, prezzo e risparmio.
 * Su telefono ogni riga diventa un blocco, senza scorrimento laterale.
 */
function ip_agevolazioni_list( $limit = 6 ) {
	$num  = function ( $v ) {
		return (float) str_replace( ',', '.', str_replace( '.', '', (string) $v ) );
	};
	$fees = ip_fees();
	$std  = $num( $fees['std_retta'] );
	$best = $num( $fees['min_rata'] );
	$rows = array();
	foreach ( get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) ) as $p ) {
		$key = get_post_meta( $p->ID, '_ip_fonte', true ) ? get_post_meta( $p->ID, '_ip_fonte', true ) : $p->post_name;
		if ( 'retta-standard' !== $key && ( ip_meta( 'rata', $p->ID ) || ip_meta( 'retta', $p->ID ) ) ) {
			$rows[] = $p;
		}
	}
	// Dalla più conveniente: costo mensile (rata, o retta annua divisa per 12).
	$month = function ( $p ) use ( $num ) {
		return ip_meta( 'rata', $p->ID ) ? $num( ip_meta( 'rata', $p->ID ) ) : $num( ip_meta( 'retta', $p->ID ) ) / 12;
	};
	usort( $rows, function ( $a, $b ) use ( $month ) {
		return $month( $a ) <=> $month( $b );
	} );
	$rows = array_slice( $rows, 0, $limit > 0 ? $limit : count( $rows ) );
	if ( ! $rows ) {
		return;
	}
	?>
	<div class="alist">
		<?php if ( $std ) : ?>
			<p class="alist-std"><span>Retta standard</span> <strong><?php echo esc_html( ip_eur( $fees['std_retta'] ) ); ?> l’anno</strong><?php echo $fees['std_rata'] ? ' · ' . esc_html( ip_eur( $fees['std_rata'] ) ) . ' al mese' : ''; ?> <em>Con le agevolazioni paghi meno:</em></p>
		<?php endif; ?>
		<ul class="alist-rows">
			<?php foreach ( $rows as $p ) : ?>
				<?php
				$title = get_the_title( $p );
				$rata  = ip_meta( 'rata', $p->ID );
				$retta = ip_meta( 'retta', $p->ID );
				$save  = $std && $num( $retta ) > 0 && $num( $retta ) < $std ? $std - $num( $retta ) : 0;
				$is_b  = $best && $num( $rata ) === $best;
				?>
				<li class="alist-row<?php echo $is_b ? ' is-best' : ''; ?>">
					<span class="alist-ico"><?php echo ip_icon( ip_agev_icon( $title ), 20 ); // phpcs:ignore ?></span>
					<div class="alist-t">
						<h3><?php echo esc_html( $title ); ?><?php if ( $is_b ) : ?> <span class="alist-flag">La più conveniente</span><?php endif; ?></h3>
						<p><?php echo esc_html( ip_meta( 'dest', $p->ID ) ); ?></p>
						<?php if ( ip_agev_deadline( $p->ID ) ) : ?><p class="alist-due"><?php echo ip_icon( 'calendar', 14 ); // phpcs:ignore ?> Richieste entro il <?php echo esc_html( ip_agev_deadline( $p->ID ) ); ?></p><?php endif; ?>
					</div>
					<div class="alist-p">
						<?php if ( $rata ) : ?>
							<strong>€ <?php echo esc_html( $rata ); ?><small>/mese</small></strong>
							<?php if ( $retta ) : ?><span>€ <?php echo esc_html( number_format( $num( $retta ), 0, ',', '.' ) ); ?> l’anno</span><?php endif; ?>
						<?php else : ?>
							<strong>€ <?php echo esc_html( number_format( $num( $retta ), 0, ',', '.' ) ); ?><small>/anno</small></strong>
						<?php endif; ?>
						<?php if ( $save ) : ?><em>−€ <?php echo esc_html( number_format( $save, 0, ',', '.' ) ); ?> l’anno</em><?php endif; ?>
					</div>
					<a class="alist-cta" href="#richiedi" data-scroll-form data-interest="<?php echo esc_attr( $title ); ?>">Verifica <?php echo ip_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
	echo ip_agev_deadline_note(); // phpcs:ignore
}

function ip_agevolazioni_table( $limit = -1 ) {
	$q = get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => $limit, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	if ( ! $q ) {
		return;
	}
	echo '<div class="atable-wrap"><table class="atable"><thead><tr><th>Agevolazione</th><th>A chi è rivolta</th><th class="num">Al mese</th><th class="num">All’anno</th><th><span class="sr">Azioni</span></th></tr></thead><tbody>';
	foreach ( $q as $p ) {
		$retta = ip_meta( 'retta', $p->ID );
		$rata  = ip_meta( 'rata', $p->ID );
		printf(
			'<tr><th scope="row">%s%s</th><td>%s</td><td class="num">%s</td><td class="num">%s</td><td class="act"><a href="#richiedi" data-scroll-form data-interest="%s">Verifica</a></td></tr>',
			esc_html( get_the_title( $p ) ),
			ip_agev_deadline( $p->ID ) ? '<span class="agev-badge">Entro il ' . esc_html( ip_agev_deadline( $p->ID ) ) . '</span>' : '',
			esc_html( ip_meta( 'dest', $p->ID ) ),
			$rata ? '<strong>€ ' . esc_html( $rata ) . '</strong>' : '—',
			$retta ? '€ ' . esc_html( number_format( (float) $retta, 0, ',', '.' ) ) : '—',
			esc_attr( get_the_title( $p ) )
		);
	}
	echo '</tbody></table></div>';
	echo ip_agev_deadline_note(); // phpcs:ignore
}

/**
 * Testo ufficiale incorporato: [ip_ufficiale fonte="https://www.unimarconi.it/…/"]
 * Mostra la pagina importata da quell'indirizzo, che il sistema di
 * aggiornamento tiene allineata al sito dell'Ateneo.
 */
add_shortcode( 'ip_ufficiale', function ( $a ) {
	$a  = shortcode_atts( array( 'fonte' => '' ), $a );
	$id = ip_official_page( $a['fonte'] );
	if ( ! $id ) {
		return '';
	}
	$html = do_blocks( get_post_field( 'post_content', $id ) );
	return '<div class="ufficiale">' . ip_local_links( $html ) . '</div>';
} );

/**
 * Pagina del sito importata da un indirizzo ufficiale.
 */
function ip_official_page( $url ) {
	if ( ! $url ) {
		return 0;
	}
	$q = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_ip_fonte', 'value' => array( untrailingslashit( $url ), trailingslashit( $url ) ), 'compare' => 'IN' ) ),
	) );
	return $q ? (int) $q[0] : 0;
}

/**
 * Indirizzi ufficiali incorporati nelle pagine del tema con [ip_ufficiale].
 */
function ip_embedded_sources() {
	$out = array();
	foreach ( ip_import_pages() as $p ) {
		if ( ! empty( $p['content'] ) && preg_match_all( '/\[ip_ufficiale fonte="([^"]+)"/', $p['content'], $m ) ) {
			$out = array_merge( $out, array_map( 'trailingslashit', $m[1] ) );
		}
	}
	return $out;
}
