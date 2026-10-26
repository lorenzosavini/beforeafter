<?php
/**
 * Richieste: pagina di gestione dei contatti.
 * Ricerca istantanea, filtri combinabili, schede per stato, scheda laterale
 * con note e storico, azioni di gruppo, esportazione dei risultati filtrati.
 */

defined( 'ABSPATH' ) || exit;

function ip_leads_url( $args = array() ) {
	return add_query_arg( $args, admin_url( 'admin.php?page=ip-leads' ) );
}

/* ---------------------------------------------------------------------
 * Menu e reindirizzamenti dall'elenco di WordPress
 * ------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	$n = (int) ip_new_leads_count();
	$b = $n ? ' <span class="awaiting-mod"><span class="pending-count">' . $n . '</span></span>' : '';
	add_menu_page( 'Richieste', 'Richieste' . $b, 'edit_posts', 'ip-leads', 'ip_leads_page', 'dashicons-email-alt', '3.1' );
}, 8 );

function ip_new_leads_count() {
	$n = get_transient( 'ip_new_leads' );
	if ( false === $n ) {
		$q = new WP_Query( array( 'post_type' => 'ip_lead', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_lead_stato', 'meta_value' => 'nuovo' ) );
		$n = (int) $q->found_posts;
		set_transient( 'ip_new_leads', $n, 5 * MINUTE_IN_SECONDS );
	}
	return (int) $n;
}

add_action( 'load-edit.php', function () {
	if ( ( $_GET['post_type'] ?? '' ) !== 'ip_lead' ) {
		return;
	}
	$map = array();
	if ( ! empty( $_GET['ip_stato'] ) ) {
		$map['stato'] = sanitize_key( $_GET['ip_stato'] );
	}
	if ( ! empty( $_GET['ip_canale'] ) ) {
		$map['canale'] = sanitize_text_field( wp_unslash( $_GET['ip_canale'] ) );
	}
	if ( ( $_GET['post_status'] ?? '' ) === 'trash' ) {
		$map['stato'] = 'cestino';
	}
	wp_safe_redirect( ip_leads_url( $map ) );
	exit;
} );

// Nella scheda di una richiesta il menu resta su «Richieste».
add_filter( 'parent_file', function ( $file ) {
	$s = get_current_screen();
	return $s && 'ip_lead' === $s->post_type ? 'ip-leads' : $file;
} );
add_filter( 'submenu_file', function ( $file ) {
	$s = get_current_screen();
	return $s && 'ip_lead' === $s->post_type ? 'ip-leads' : $file;
} );

/* ---------------------------------------------------------------------
 * Filtri e ricerca
 * ------------------------------------------------------------------- */

function ip_leads_filters() {
	$g = function ( $k ) {
		return isset( $_GET[ $k ] ) ? trim( sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) ) : '';
	};
	$f = array(
		's'        => $g( 's' ),
		'stato'    => sanitize_key( $g( 'stato' ) ),
		'tipo'     => sanitize_key( $g( 'tipo' ) ),
		'canale'   => $g( 'canale' ),
		'corso'    => $g( 'corso' ),
		'campagna' => $g( 'campagna' ),
		'landing'  => $g( 'landing' ),
		'periodo'  => sanitize_key( $g( 'periodo' ) ),
		'dal'      => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $g( 'dal' ) ) ? $g( 'dal' ) : '',
		'al'       => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $g( 'al' ) ) ? $g( 'al' ) : '',
		'mkt'      => in_array( $g( 'mkt' ), array( 'si', 'no' ), true ) ? $g( 'mkt' ) : '',
		'ordine'   => in_array( $g( 'ordine' ), array( 'vecchie', 'nome' ), true ) ? $g( 'ordine' ) : '',
		'per'      => in_array( (int) $g( 'per' ), array( 25, 50, 100 ), true ) ? (int) $g( 'per' ) : 25,
		'paged'    => max( 1, absint( $g( 'paged' ) ) ),
	);
	return $f;
}

/** ID delle richieste che contengono il testo cercato (nome, telefono, email, città, corso, messaggio, campagna…). */
function ip_leads_search_ids( $s ) {
	global $wpdb;
	$like   = '%' . $wpdb->esc_like( $s ) . '%';
	$digits = preg_replace( '/\D/', '', $s );
	$sql    = "SELECT DISTINCT pm.post_id FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type = 'ip_lead'
		WHERE pm.meta_key LIKE '\\_lead\\_%%' AND pm.meta_key NOT IN ('_lead_invio','_lead_log','_lead_files') AND pm.meta_value LIKE %s";
	$ids    = $wpdb->get_col( $wpdb->prepare( $sql, $like ) ); // phpcs:ignore
	if ( strlen( $digits ) >= 4 ) {
		$ids = array_merge( $ids, $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT pm.post_id FROM {$wpdb->postmeta} pm WHERE pm.meta_key = '_lead_telefono' AND REPLACE(REPLACE(REPLACE(REPLACE(pm.meta_value,' ',''),'.',''),'-',''),'+39','') LIKE %s",
			'%' . $wpdb->esc_like( ltrim( preg_replace( '/^0039|^39(?=3)/', '', $digits ), '' ) ) . '%'
		) ) );
	}
	$ids = array_merge( $ids, $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'ip_lead' AND post_title LIKE %s", $like ) ) );
	return array_values( array_unique( array_map( 'intval', $ids ) ) );
}

/**
 * Argomenti di WP_Query per i filtri dati.
 *
 * @param array $f      Filtri.
 * @param array $except Filtri da ignorare (per i conteggi delle schede).
 */
function ip_leads_query_args( $f, $except = array() ) {
	$use  = function ( $k ) use ( $f, $except ) {
		return '' !== $f[ $k ] && ! in_array( $k, $except, true );
	};
	$args = array(
		'post_type'      => 'ip_lead',
		'post_status'    => ( $use( 'stato' ) && 'cestino' === $f['stato'] ) ? 'trash' : 'publish',
		'posts_per_page' => $f['per'],
		'paged'          => $f['paged'],
		'orderby'        => 'nome' === $f['ordine'] ? 'title' : 'date',
		'order'          => 'nome' === $f['ordine'] ? 'ASC' : ( 'vecchie' === $f['ordine'] ? 'ASC' : 'DESC' ),
	);
	$mq   = array();
	if ( $use( 'stato' ) && 'cestino' !== $f['stato'] ) {
		$mq[] = array( 'key' => '_lead_stato', 'value' => $f['stato'] );
	}
	if ( $use( 'tipo' ) ) {
		$mq[] = array( 'key' => '_lead_tipo', 'value' => $f['tipo'] );
	}
	if ( $use( 'canale' ) ) {
		$mq[] = 'Diretto' === $f['canale']
			? array( 'relation' => 'OR', array( 'key' => '_lead_canale', 'value' => 'Diretto' ), array( 'key' => '_lead_canale', 'compare' => 'NOT EXISTS' ) )
			: array( 'key' => '_lead_canale', 'value' => $f['canale'] );
	}
	if ( $use( 'corso' ) ) {
		$mq[] = array( 'relation' => 'OR', array( 'key' => '_lead_corso', 'value' => $f['corso'] ), array( 'key' => '_lead_interesse', 'value' => $f['corso'] ) );
	}
	if ( $use( 'campagna' ) ) {
		$mq[] = array( 'key' => '_lead_utm_campaign', 'value' => $f['campagna'] );
	}
	if ( $use( 'landing' ) ) {
		$mq[] = array( 'key' => '_lead_landing', 'value' => $f['landing'] );
	}
	if ( $use( 'mkt' ) ) {
		$mq[] = array( 'key' => '_lead_marketing', 'value' => 'si' === $f['mkt'] ? 'sì' : 'no' );
	}
	if ( $mq ) {
		$args['meta_query'] = array_merge( array( 'relation' => 'AND' ), $mq );
	}
	if ( $use( 'periodo' ) ) {
		$range = array(
			'oggi'  => array( 'after' => 'today' ),
			'ieri'  => array( 'after' => 'yesterday', 'before' => 'today' ),
			'7'     => array( 'after' => '6 days ago midnight' ),
			'30'    => array( 'after' => '29 days ago midnight' ),
			'mese'  => array( 'after' => 'first day of this month midnight' ),
			'scelto' => array_filter( array( 'after' => $f['dal'] ? $f['dal'] . ' 00:00:00' : '', 'before' => $f['al'] ? $f['al'] . ' 23:59:59' : '' ) ),
		);
		if ( ! empty( $range[ $f['periodo'] ] ) ) {
			$args['date_query'] = array( array_merge( $range[ $f['periodo'] ], array( 'inclusive' => true ) ) );
		}
	}
	if ( $use( 's' ) ) {
		$ids              = ip_leads_search_ids( $f['s'] );
		$args['post__in'] = $ids ? $ids : array( 0 );
	}
	return $args;
}

/** Valori presenti per un campo, per i menu dei filtri. */
function ip_leads_distinct( $keys ) {
	global $wpdb;
	$in = implode( ',', array_map( function ( $k ) use ( $wpdb ) {
		return $wpdb->prepare( '%s', $k );
	}, (array) $keys ) );
	return $wpdb->get_col( "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type = 'ip_lead' AND p.post_status = 'publish' WHERE pm.meta_key IN ($in) AND pm.meta_value <> '' ORDER BY pm.meta_value LIMIT 300" ); // phpcs:ignore
}

/* ---------------------------------------------------------------------
 * Pagina
 * ------------------------------------------------------------------- */

function ip_leads_page() {
	$f      = ip_leads_filters();
	$q      = new WP_Query( ip_leads_query_args( $f ) );
	$states = ip_lead_states();
	$types  = array( 'info' => 'Informazioni', 'cfu' => 'Valutazione CFU', 'callback' => 'Richiamata' );
	$active = array_filter( array_intersect_key( $f, array_flip( array( 's', 'tipo', 'canale', 'corso', 'campagna', 'landing', 'periodo', 'mkt' ) ) ) );

	// Conteggi delle schede di stato con gli altri filtri applicati.
	$counts = array();
	foreach ( array_merge( array( '' => 'Tutte' ), $states, array( 'cestino' => 'Cestino' ) ) as $k => $l ) {
		$cf             = array_merge( $f, array( 'stato' => $k, 'per' => 1, 'paged' => 1 ) );
		$cq             = new WP_Query( array_merge( ip_leads_query_args( $cf ), array( 'fields' => 'ids', 'no_found_rows' => false ) ) );
		$counts[ $k ]   = (int) $cq->found_posts;
	}
	$keep    = array_filter( array_diff_key( $f, array( 'paged' => 1, 'per' => 1 ) ) );
	$periods = array( '' => 'Sempre', 'oggi' => 'Oggi', 'ieri' => 'Ieri', '7' => 'Ultimi 7 giorni', '30' => 'Ultimi 30 giorni', 'mese' => 'Questo mese', 'scelto' => 'Date a scelta…' );
	$msg     = isset( $_GET['ip_done'] ) ? sanitize_text_field( wp_unslash( $_GET['ip_done'] ) ) : '';
	?>
	<div class="wrap ip-admin ipa ipl" data-ipl>
		<?php
		ip_admin_header(
			'Richieste',
			'Chi vi ha scritto, da dove arriva e a che punto siete. Clicca una riga per aprire la scheda.',
			array(
				array( 'Esporta in Excel (CSV)', wp_nonce_url( add_query_arg( $keep, admin_url( 'admin-post.php?action=ip_export_leads' ) ), 'ip_export' ), 'ipa-btn-line' ),
				array( 'Email e notifiche', admin_url( 'admin.php?page=ip-settings&tab=email' ), 'ipa-btn-line' ),
			)
		);
		?>
		<?php if ( $msg ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

		<form class="ipl-bar" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" data-ipl-form>
			<input type="hidden" name="page" value="ip-leads">
			<input type="hidden" name="stato" value="<?php echo esc_attr( $f['stato'] ); ?>">
			<div class="ipl-search">
				<span class="dashicons dashicons-search"></span>
				<input type="search" name="s" value="<?php echo esc_attr( $f['s'] ); ?>" placeholder="Cerca per nome, telefono, email, città, corso, campagna…" autocomplete="off" aria-label="Cerca nelle richieste">
				<kbd>/</kbd>
			</div>
			<?php $nf = count( array_filter( array_diff_key( $active, array( 's' => 1 ) ) ) ); ?>
			<button type="button" class="button ipl-ftoggle" data-ipl-ftoggle>Filtri<?php echo $nf ? ' (' . (int) $nf . ')' : ''; ?></button>
			<div class="ipl-filters">
				<label><span>Periodo</span>
					<select name="periodo" data-ipl-period>
						<?php foreach ( $periods as $k => $l ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $f['periodo'], $k ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<label class="ipl-dates" <?php echo 'scelto' === $f['periodo'] ? '' : 'hidden'; ?>><span>Dal</span><input type="date" name="dal" value="<?php echo esc_attr( $f['dal'] ); ?>"></label>
				<label class="ipl-dates" <?php echo 'scelto' === $f['periodo'] ? '' : 'hidden'; ?>><span>Al</span><input type="date" name="al" value="<?php echo esc_attr( $f['al'] ); ?>"></label>
				<label><span>Canale</span>
					<select name="canale">
						<option value="">Tutti</option>
						<?php foreach ( array_unique( array_merge( array( 'Google Ads', 'Meta Ads', 'Ricerca organica', 'Social', 'Diretto' ), ip_leads_distinct( '_lead_canale' ) ) ) as $c ) : ?>
							<option <?php selected( $f['canale'], $c ); ?>><?php echo esc_html( $c ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label><span>Corso</span>
					<select name="corso">
						<option value="">Tutti</option>
						<?php foreach ( array_unique( ip_leads_distinct( array( '_lead_corso', '_lead_interesse' ) ) ) as $c ) : ?>
							<option <?php selected( $f['corso'], $c ); ?>><?php echo esc_html( $c ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<?php $camps = ip_leads_distinct( '_lead_utm_campaign' ); ?>
				<?php if ( $camps || $f['campagna'] ) : ?>
					<label><span>Campagna</span>
						<select name="campagna">
							<option value="">Tutte</option>
							<?php foreach ( $camps as $c ) : ?><option <?php selected( $f['campagna'], $c ); ?>><?php echo esc_html( $c ); ?></option><?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
				<?php $lps = ip_leads_distinct( '_lead_landing' ); ?>
				<?php if ( $lps || $f['landing'] ) : ?>
					<label><span>Landing</span>
						<select name="landing">
							<option value="">Tutte</option>
							<?php foreach ( $lps as $c ) : ?><option <?php selected( $f['landing'], $c ); ?>><?php echo esc_html( $c ); ?></option><?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
				<label><span>Modulo</span>
					<select name="tipo">
						<option value="">Tutti</option>
						<?php foreach ( $types as $k => $l ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $f['tipo'], $k ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<label><span>Marketing</span>
					<select name="mkt">
						<option value="">Indifferente</option>
						<option value="si" <?php selected( $f['mkt'], 'si' ); ?>>Ha acconsentito</option>
						<option value="no" <?php selected( $f['mkt'], 'no' ); ?>>Non ha acconsentito</option>
					</select>
				</label>
				<label><span>Ordine</span>
					<select name="ordine">
						<option value="">Più recenti</option>
						<option value="vecchie" <?php selected( $f['ordine'], 'vecchie' ); ?>>Meno recenti</option>
						<option value="nome" <?php selected( $f['ordine'], 'nome' ); ?>>Nome A–Z</option>
					</select>
				</label>
				<noscript><button class="button">Filtra</button></noscript>
			</div>
		</form>

		<div data-ipl-results>
			<nav class="ipl-tabs" aria-label="Stato">
				<?php foreach ( array_merge( array( '' => 'Tutte' ), $states, array( 'cestino' => 'Cestino' ) ) as $k => $l ) : ?>
					<?php
					if ( 'cestino' === $k && ! $counts[ $k ] && 'cestino' !== $f['stato'] ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( ip_leads_url( array_merge( $keep, array( 'stato' => $k ) ) ) ); ?>" class="ipl-tab<?php echo $f['stato'] === $k ? ' is-on' : ''; ?>" data-ipl-link>
						<?php if ( $k && 'cestino' !== $k ) : ?><i class="ip-dot ip-dot-<?php echo esc_attr( $k ); ?>"></i><?php endif; ?>
						<?php echo esc_html( $l ); ?> <b><?php echo (int) $counts[ $k ]; ?></b>
					</a>
				<?php endforeach; ?>
			</nav>

			<?php if ( $active ) : ?>
				<div class="ipl-chips">
					<?php
					$labels = array( 's' => 'Cerca', 'tipo' => 'Modulo', 'canale' => 'Canale', 'corso' => 'Corso', 'campagna' => 'Campagna', 'landing' => 'Landing', 'periodo' => 'Periodo', 'mkt' => 'Marketing' );
					foreach ( $active as $k => $v ) :
						$show = 'tipo' === $k ? ( $types[ $v ] ?? $v ) : ( 'periodo' === $k ? ( 'scelto' === $v ? trim( ( $f['dal'] ? 'dal ' . mysql2date( 'j/m/Y', $f['dal'] ) : '' ) . ( $f['al'] ? ' al ' . mysql2date( 'j/m/Y', $f['al'] ) : '' ) ) : $periods[ $v ] ) : ( 'mkt' === $k ? ( 'si' === $v ? 'sì' : 'no' ) : $v ) );
						$rm   = array_diff_key( $keep, array( $k => 1 ) );
						if ( 'periodo' === $k ) {
							unset( $rm['dal'], $rm['al'] );
						}
						?>
						<a class="ipl-chip" href="<?php echo esc_url( ip_leads_url( $rm ) ); ?>" data-ipl-link title="Togli questo filtro"><?php echo esc_html( $labels[ $k ] . ': ' . $show ); ?> <span aria-hidden="true">×</span></a>
					<?php endforeach; ?>
					<a class="ipl-chip-clear" href="<?php echo esc_url( ip_leads_url( $f['stato'] ? array( 'stato' => $f['stato'] ) : array() ) ); ?>" data-ipl-link>Azzera i filtri</a>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-ipl-bulk-form>
				<input type="hidden" name="action" value="ip_leads_bulk">
				<input type="hidden" name="back" value="<?php echo esc_attr( ip_leads_url( $keep ) ); ?>">
				<?php wp_nonce_field( 'ip_leads_bulk' ); ?>
				<div class="ipl-bulk" data-ipl-bulk hidden>
					<strong><span data-ipl-count>0</span> selezionate</strong>
					<?php if ( 'cestino' === $f['stato'] ) : ?>
						<button class="button" name="do" value="restore">Ripristina</button>
						<button class="button ipl-danger" name="do" value="delete" data-ipl-confirm="Eliminare definitivamente le richieste selezionate?">Elimina definitivamente</button>
					<?php else : ?>
						<select name="state" aria-label="Nuovo stato"><?php foreach ( $states as $k => $l ) : ?><option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></option><?php endforeach; ?></select>
						<button class="button button-primary" name="do" value="state">Segna come</button>
						<button class="button" name="do" value="export">Esporta selezionate</button>
						<button class="button ipl-danger" name="do" value="trash">Sposta nel cestino</button>
					<?php endif; ?>
				</div>

				<?php if ( ! $q->have_posts() ) : ?>
					<div class="ipl-empty">
						<span class="dashicons dashicons-<?php echo $active || $f['stato'] ? 'filter' : 'email-alt'; ?>"></span>
						<?php if ( $active || $f['stato'] ) : ?>
							<p><strong>Nessuna richiesta con questi filtri.</strong></p>
							<p><a href="<?php echo esc_url( ip_leads_url() ); ?>" data-ipl-link>Mostra tutte le richieste</a></p>
						<?php else : ?>
							<p><strong>Ancora nessuna richiesta.</strong></p>
							<p>Quando qualcuno compila un modulo del sito la trovi qui, con il pulsante per chiamarlo. Intanto controlla che <a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=email' ) ); ?>">le email di notifica</a> siano configurate.</p>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<table class="ipl-table">
						<thead><tr>
							<th class="ipl-cb"><input type="checkbox" data-ipl-all aria-label="Seleziona tutte"></th>
							<th>Contatto</th><th>Recapiti</th><th>Provenienza</th><th>Ricevuta</th><th>Stato</th><th class="ipl-act"><span class="screen-reader-text">Azioni</span></th>
						</tr></thead>
						<tbody>
							<?php foreach ( $q->posts as $p ) : ?>
								<?php ip_leads_row( $p, $f ); ?>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</form>

			<?php if ( $q->max_num_pages > 1 || $q->found_posts > 25 ) : ?>
				<div class="ipl-foot">
					<span><?php echo esc_html( sprintf( '%d richieste · pagina %d di %d', $q->found_posts, $f['paged'], max( 1, $q->max_num_pages ) ) ); ?></span>
					<span class="ipl-pages">
						<?php
						echo paginate_links( array( // phpcs:ignore
							'base'      => add_query_arg( 'paged', '%#%', ip_leads_url( array_merge( $keep, array( 'per' => $f['per'] ) ) ) ),
							'format'    => '',
							'current'   => $f['paged'],
							'total'     => max( 1, $q->max_num_pages ),
							'prev_text' => '‹',
							'next_text' => '›',
						) );
						?>
					</span>
					<label>Per pagina
						<select data-ipl-per><?php foreach ( array( 25, 50, 100 ) as $n ) : ?><option <?php selected( $f['per'], $n ); ?>><?php echo (int) $n; ?></option><?php endforeach; ?></select>
					</label>
				</div>
			<?php endif; ?>
		</div>

		<aside class="ipl-drawer" data-ipl-drawer hidden aria-label="Scheda della richiesta">
			<div class="ipl-drawer-in" data-ipl-drawer-body></div>
		</aside>
		<div class="ipl-shade" data-ipl-shade hidden></div>
	</div>
	<?php
}

function ip_leads_initials( $name ) {
	$parts = preg_split( '/\s+/', trim( $name ) );
	$i     = '';
	foreach ( array_slice( $parts, 0, 2 ) as $w ) {
		$i .= mb_strtoupper( mb_substr( $w, 0, 1 ) );
	}
	return $i ? $i : '?';
}

function ip_leads_wa( $tel ) {
	$wa = preg_replace( '/\D/', '', (string) $tel );
	$wa = 10 === strlen( $wa ) && '3' === $wa[0] ? '39' . $wa : $wa;
	return '3' === substr( $wa, 2, 1 ) ? 'https://wa.me/' . $wa : '';
}

function ip_leads_row( $p, $f ) {
	$m     = function ( $k ) use ( $p ) {
		return (string) get_post_meta( $p->ID, '_lead_' . $k, true );
	};
	$name  = trim( $m( 'nome' ) . ' ' . $m( 'cognome' ) );
	$name  = $name ? $name : get_the_title( $p );
	$what  = $m( 'corso' ) ? $m( 'corso' ) : $m( 'interesse' );
	$tel   = $m( 'telefono' );
	$mail  = $m( 'email' );
	$st    = $m( 'stato' ) ? $m( 'stato' ) : 'nuovo';
	$ch    = $m( 'canale' ) ? $m( 'canale' ) : ( $m( 'utm_source' ) ? 'Campagna' : 'Diretto' );
	$wa    = ip_leads_wa( $tel );
	$types = array( 'info' => 'Informazioni', 'cfu' => 'Valutazione CFU', 'callback' => 'Richiamata' );
	$note  = $m( 'note' );
	$hue   = abs( crc32( $name ) ) % 360;
	?>
	<tr data-ipl-row="<?php echo (int) $p->ID; ?>" class="ipl-st-<?php echo esc_attr( $st ); ?>">
		<td class="ipl-cb"><input type="checkbox" name="ids[]" value="<?php echo (int) $p->ID; ?>" aria-label="Seleziona <?php echo esc_attr( $name ); ?>"></td>
		<td>
			<div class="ipl-who">
				<span class="ipl-av" style="--h:<?php echo (int) $hue; ?>"><?php echo esc_html( ip_leads_initials( $name ) ); ?></span>
				<div>
					<a class="ipl-name" href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" data-ipl-open><?php echo esc_html( $name ); ?></a>
					<small><?php echo esc_html( $what ? $what : ( $types[ $m( 'tipo' ) ] ?? '' ) ); ?></small>
					<?php if ( $note ) : ?><small class="ipl-note" title="<?php echo esc_attr( $note ); ?>"><span class="dashicons dashicons-edit"></span> <?php echo esc_html( wp_trim_words( $note, 8 ) ); ?></small><?php endif; ?>
				</div>
			</div>
		</td>
		<td class="ipl-contacts">
			<?php if ( $tel ) : ?><a href="<?php echo esc_attr( ip_tel_href( $tel ) ); ?>"><?php echo esc_html( $tel ); ?></a><?php endif; ?>
			<?php if ( is_email( $mail ) ) : ?><a href="mailto:<?php echo esc_attr( $mail ); ?>" class="ipl-mail"><?php echo esc_html( $mail ); ?></a><?php endif; ?>
			<?php if ( $m( 'citta' ) ) : ?><small><?php echo esc_html( $m( 'citta' ) ); ?></small><?php endif; ?>
		</td>
		<td>
			<span class="ip-ch ip-ch-<?php echo esc_attr( sanitize_title( $ch ) ); ?>"><?php echo esc_html( $ch ); ?></span>
			<?php if ( $m( 'utm_campaign' ) ) : ?><small><?php echo esc_html( $m( 'utm_campaign' ) ); ?></small><?php endif; ?>
			<?php if ( $m( 'landing' ) ) : ?><small>Landing: <?php echo esc_html( $m( 'landing' ) ); ?></small><?php elseif ( $m( 'pagina' ) ) : ?><small><?php echo esc_html( ip_lead_short_url( $m( 'pagina' ) ) ); ?></small><?php endif; ?>
		</td>
		<td class="ipl-when"><span title="<?php echo esc_attr( get_the_date( 'j F Y, H:i', $p ) ); ?>"><?php echo esc_html( human_time_diff( get_post_time( 'U', true, $p ), time() ) ); ?> fa</span><small><?php echo esc_html( get_the_date( 'j M, H:i', $p ) ); ?></small></td>
		<td>
			<?php if ( 'trash' === $p->post_status ) : ?>
				<span class="ip-st">Nel cestino</span>
			<?php else : ?>
				<select class="ipa-state" data-lead="<?php echo (int) $p->ID; ?>" aria-label="Stato di <?php echo esc_attr( $name ); ?>">
					<?php foreach ( ip_lead_states() as $k => $l ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $st, $k ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
				</select>
			<?php endif; ?>
		</td>
		<td class="ipl-act">
			<?php if ( $tel ) : ?><a class="ipl-ic" href="<?php echo esc_attr( ip_tel_href( $tel ) ); ?>" title="Chiama"><span class="dashicons dashicons-phone"></span></a><?php endif; ?>
			<?php if ( $wa ) : ?><a class="ipl-ic ipl-ic-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" title="WhatsApp"><span class="dashicons dashicons-whatsapp"></span></a><?php endif; ?>
			<?php if ( is_email( $mail ) ) : ?><a class="ipl-ic" href="mailto:<?php echo esc_attr( $mail ); ?>" title="Email"><span class="dashicons dashicons-email-alt"></span></a><?php endif; ?>
		</td>
	</tr>
	<?php
}

/* ---------------------------------------------------------------------
 * Scheda laterale
 * ------------------------------------------------------------------- */

function ip_lead_log( $id, $text ) {
	$log   = get_post_meta( $id, '_lead_log', true );
	$log   = is_array( $log ) ? $log : array();
	$u     = wp_get_current_user();
	$log[] = array( 't' => current_time( 'mysql' ), 'u' => $u && $u->exists() ? $u->display_name : '', 'x' => $text );
	update_post_meta( $id, '_lead_log', array_slice( $log, -50 ) );
}

add_action( 'wp_ajax_ip_lead_drawer', function () {
	check_ajax_referer( 'ip_lead_state_ajax' );
	$id = absint( $_GET['lead'] ?? 0 );
	$p  = get_post( $id );
	if ( ! $p || 'ip_lead' !== $p->post_type || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error();
	}
	$m     = function ( $k ) use ( $id ) {
		return (string) get_post_meta( $id, '_lead_' . $k, true );
	};
	$name  = trim( $m( 'nome' ) . ' ' . $m( 'cognome' ) );
	$tel   = $m( 'telefono' );
	$wa    = ip_leads_wa( $tel );
	$st    = $m( 'stato' ) ? $m( 'stato' ) : 'nuovo';
	$types = array( 'info' => 'Richiesta informazioni', 'cfu' => 'Prevalutazione CFU', 'callback' => 'Richiesta di richiamata' );
	$log   = get_post_meta( $id, '_lead_log', true );
	ob_start();
	?>
	<div class="ipl-d-head">
		<button type="button" class="ipl-d-x" data-ipl-close aria-label="Chiudi">×</button>
		<p class="ipa-kicker"><?php echo esc_html( $types[ $m( 'tipo' ) ] ?? 'Richiesta' ); ?> · <?php echo esc_html( get_the_date( 'j F Y, H:i', $p ) ); ?></p>
		<h2><?php echo esc_html( $name ? $name : get_the_title( $p ) ); ?></h2>
		<?php if ( $m( 'corso' ) || $m( 'interesse' ) ) : ?><p class="ipl-d-what"><?php echo esc_html( $m( 'corso' ) ? $m( 'corso' ) : $m( 'interesse' ) ); ?></p><?php endif; ?>
		<div class="ipl-d-acts">
			<?php if ( $tel ) : ?><a class="ipa-btn" href="<?php echo esc_attr( ip_tel_href( $tel ) ); ?>"><span class="dashicons dashicons-phone"></span> <?php echo esc_html( $tel ); ?></a><?php endif; ?>
			<?php if ( $wa ) : ?><a class="ipa-btn ipa-btn-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-whatsapp"></span> WhatsApp</a><?php endif; ?>
			<?php if ( is_email( $m( 'email' ) ) ) : ?><a class="ipa-btn ipa-btn-line" href="mailto:<?php echo esc_attr( $m( 'email' ) ); ?>"><span class="dashicons dashicons-email-alt"></span> Email</a><?php endif; ?>
		</div>
	</div>
	<div class="ipl-d-body">
		<?php if ( 'trash' !== $p->post_status ) : ?>
			<div class="ipl-seg" role="group" aria-label="Stato">
				<?php foreach ( ip_lead_states() as $k => $l ) : ?>
					<button type="button" class="ipl-seg-b ipl-seg-<?php echo esc_attr( $k ); ?><?php echo $st === $k ? ' is-on' : ''; ?>" data-ipl-state="<?php echo esc_attr( $k ); ?>" data-lead="<?php echo (int) $id; ?>"><?php echo esc_html( $l ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<section class="ipl-d-sec">
			<h3>Note interne</h3>
			<textarea rows="3" data-ipl-note="<?php echo (int) $id; ?>" placeholder="Es. richiamare giovedì dopo le 15, interessata alla rata mensile…"><?php echo esc_textarea( $m( 'note' ) ); ?></textarea>
			<div class="ipl-d-save"><button type="button" class="button button-primary" data-ipl-save-note="<?php echo (int) $id; ?>">Salva la nota</button> <span data-ipl-note-ok hidden>Salvata ✓</span></div>
		</section>

		<section class="ipl-d-sec">
			<h3>Dati</h3>
			<dl class="ipl-dl">
				<?php
				$rows = array(
					'Telefono'        => $tel ? '<a href="' . esc_attr( ip_tel_href( $tel ) ) . '">' . esc_html( $tel ) . '</a>' : '',
					'Email'           => is_email( $m( 'email' ) ) ? '<a href="mailto:' . esc_attr( $m( 'email' ) ) . '">' . esc_html( $m( 'email' ) ) . '</a>' : '',
					'Città'           => esc_html( $m( 'citta' ) ),
					'Regione'         => esc_html( $m( 'regione' ) ),
					'Anno di nascita' => esc_html( $m( 'nascita' ) ),
					'Quando chiamare' => esc_html( $m( 'fascia' ) ),
					'Messaggio'       => nl2br( esc_html( $m( 'messaggio' ) ) ),
					'Marketing'       => 'sì' === $m( 'marketing' ) ? 'Acconsente agli aggiornamenti' : 'Non acconsente',
					'Consenso'        => esc_html( $m( 'consenso' ) ),
				);
				$files = array_filter( (array) get_post_meta( $id, '_lead_files', true ) );
				if ( $files ) {
					$links = array();
					foreach ( $files as $i => $file ) {
						$links[] = '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ip_lead_file&lead=' . $id . '&n=' . $i ), 'ip_file' ) ) . '">' . esc_html( basename( $file ) ) . '</a>';
					}
					$rows['Allegati'] = implode( '<br>', $links );
				}
				foreach ( $rows as $l => $v ) {
					if ( '' !== trim( wp_strip_all_tags( $v ) ) ) {
						printf( '<dt>%s</dt><dd>%s</dd>', esc_html( $l ), $v ); // phpcs:ignore
					}
				}
				?>
			</dl>
		</section>

		<section class="ipl-d-sec">
			<h3>Da dove arriva</h3>
			<dl class="ipl-dl">
				<?php if ( $m( 'pagina' ) ) : ?><dt>Pagina di invio</dt><dd><a href="<?php echo esc_url( $m( 'pagina' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( ip_lead_short_url( $m( 'pagina' ) ) ); ?></a></dd><?php endif; ?>
				<?php if ( $m( 'landing' ) ) : ?><dt>Landing</dt><dd><?php echo esc_html( $m( 'landing' ) ); ?></dd><?php endif; ?>
				<?php foreach ( ip_lead_attr_rows( $id ) as $l => $v ) : ?><dt><?php echo esc_html( $l ); ?></dt><dd><?php echo $v; // phpcs:ignore ?></dd><?php endforeach; ?>
				<?php if ( ! $m( 'canale' ) && $m( 'origine' ) ) : ?><dt>Origine</dt><dd><?php echo esc_html( $m( 'origine' ) ); ?></dd><?php endif; ?>
			</dl>
		</section>

		<section class="ipl-d-sec">
			<h3>Storico</h3>
			<ul class="ipl-log">
				<?php foreach ( array_reverse( is_array( $log ) ? $log : array() ) as $e ) : ?>
					<li><b><?php echo esc_html( mysql2date( 'j/m/Y H:i', $e['t'] ) ); ?></b> <?php echo esc_html( $e['x'] ); ?><?php echo $e['u'] ? ' · ' . esc_html( $e['u'] ) : ''; ?></li>
				<?php endforeach; ?>
				<?php if ( $m( 'invio' ) ) : ?><li><b>Email</b> <?php echo esc_html( $m( 'invio' ) ); ?></li><?php endif; ?>
				<li><b><?php echo esc_html( get_the_date( 'j/m/Y H:i', $p ) ); ?></b> Richiesta ricevuta dal sito</li>
			</ul>
		</section>

		<p class="ipl-d-foot">
			<a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>">Apri la scheda completa</a>
			<?php if ( 'trash' !== $p->post_status ) : ?> · <a class="ipl-danger-link" href="<?php echo esc_url( get_delete_post_link( $id ) ); ?>">Sposta nel cestino</a><?php endif; ?>
		</p>
	</div>
	<?php
	wp_send_json_success( array( 'html' => ob_get_clean() ) );
} );

add_action( 'wp_ajax_ip_lead_note', function () {
	check_ajax_referer( 'ip_lead_state_ajax' );
	$id = absint( $_POST['lead'] ?? 0 );
	if ( ! $id || 'ip_lead' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error();
	}
	update_post_meta( $id, '_lead_note', sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) ) );
	ip_lead_log( $id, 'Nota aggiornata' );
	wp_send_json_success();
} );

// Storico dei cambi di stato (da elenco, scheda laterale e scheda completa).
add_action( 'updated_post_meta', function ( $mid, $id, $key, $value ) {
	if ( '_lead_stato' === $key && is_admin() ) {
		$s = ip_lead_states();
		ip_lead_log( $id, 'Stato: ' . ( $s[ $value ] ?? $value ) );
	}
}, 10, 4 );

/* ---------------------------------------------------------------------
 * Azioni di gruppo
 * ------------------------------------------------------------------- */

add_action( 'admin_post_ip_leads_bulk', function () {
	check_admin_referer( 'ip_leads_bulk' );
	$ids  = array_filter( array_map( 'absint', (array) ( $_POST['ids'] ?? array() ) ) );
	$do   = sanitize_key( wp_unslash( $_POST['do'] ?? '' ) );
	$back = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['back'] ?? '' ) ), ip_leads_url() );
	$ids  = array_values( array_filter( $ids, function ( $id ) {
		return 'ip_lead' === get_post_type( $id ) && current_user_can( 'edit_post', $id );
	} ) );
	if ( ! $ids ) {
		wp_safe_redirect( $back );
		exit;
	}
	if ( 'export' === $do ) {
		ip_leads_export( $ids );
	}
	$n   = count( $ids );
	$msg = '';
	if ( 'state' === $do ) {
		$s = sanitize_key( wp_unslash( $_POST['state'] ?? '' ) );
		if ( isset( ip_lead_states()[ $s ] ) ) {
			foreach ( $ids as $id ) {
				update_post_meta( $id, '_lead_stato', $s );
			}
			$msg = sprintf( '%d richieste segnate come «%s».', $n, ip_lead_states()[ $s ] );
		}
	} elseif ( 'trash' === $do ) {
		array_map( 'wp_trash_post', $ids );
		$msg = sprintf( '%d richieste spostate nel cestino.', $n );
	} elseif ( 'restore' === $do ) {
		foreach ( $ids as $id ) {
			wp_untrash_post( $id );
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
		$msg = sprintf( '%d richieste ripristinate.', $n );
	} elseif ( 'delete' === $do && current_user_can( 'delete_others_posts' ) ) {
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		$msg = sprintf( '%d richieste eliminate definitivamente.', $n );
	}
	delete_transient( 'ip_new_leads' );
	wp_safe_redirect( add_query_arg( 'ip_done', rawurlencode( $msg ), $back ) );
	exit;
} );

/** CSV delle richieste date. */
function ip_leads_export( $ids ) {
	$keys = array_merge( array( 'nome', 'cognome', 'telefono', 'email', 'interesse', 'corso', 'nascita', 'regione', 'citta', 'fascia', 'messaggio', 'marketing', 'tipo', 'stato', 'note', 'landing', 'pagina' ), array_keys( ip_lead_attr_keys() ), array( 'origine', 'invio' ) );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="richieste-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM: Excel legge correttamente gli accenti.
	fputcsv( $out, array_merge( array( 'data' ), $keys ), ';' );
	foreach ( $ids as $id ) {
		$row = array( get_the_date( 'Y-m-d H:i', $id ) );
		foreach ( $keys as $k ) {
			$v     = (string) get_post_meta( $id, '_lead_' . $k, true );
			$row[] = preg_match( '/^[=+\-@]/', $v ) ? "'" . $v : $v; // Evita che Excel interpreti i valori come formule.
		}
		fputcsv( $out, $row, ';' );
	}
	fclose( $out );
	exit;
}
