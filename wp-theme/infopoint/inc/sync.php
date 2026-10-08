<?php
/**
 * Aggiornamenti automatici dall'offerta ufficiale UniMarconi.
 *
 * Ogni giorno (o settimana) legge le sitemap di unimarconi.it, individua le
 * pagine cambiate grazie alla data di ultima modifica e le rilegge a piccoli
 * gruppi, senza appesantire né il loro server né il vostro. Ogni differenza
 * diventa una proposta da approvare in «Infopoint → Aggiornamenti», oppure
 * viene applicata subito in modalità automatica.
 *
 * Cosa viene controllato:
 * - corsi (dati, testo, documenti, stato iscrizioni) e loro piani di studio;
 * - corsi nuovi e corsi tolti dall'offerta;
 * - pagine informative importate (tasse, immatricolazione…);
 * - pagina delle agevolazioni (segnalazione, la modifica resta manuale);
 * - sedi d'esame.
 */

defined( 'ABSPATH' ) || exit;

const IP_SYNC_BATCH = 4;
const IP_SYNC_AGEV  = 'https://www.unimarconi.it/agevolazioni-e-riduzioni-tasse-di-iscrizione/';
const IP_SYNC_SEDI  = 'https://www.unimarconi.it/sedi-esami-e-poli-di-orientamento/';

/* ---------------------------------------------------------------------
 * Registrazione: tipo di contenuto per le proposte, pianificazione.
 * ------------------------------------------------------------------- */

add_action( 'init', function () {
	register_post_type( 'ip_update', array(
		'labels'          => array( 'name' => 'Aggiornamenti ufficiali', 'singular_name' => 'Aggiornamento' ),
		'public'          => false,
		'show_ui'         => false,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
	) );
} );

function ip_sync_state( $set = null ) {
	if ( null !== $set ) {
		update_option( 'ip_sync_state', $set, false );
		return $set;
	}
	$s = get_option( 'ip_sync_state', array() );
	return wp_parse_args( is_array( $s ) ? $s : array(), array(
		'lastmod'    => array(),
		'hash'       => array(),
		'last_check' => 0,
		'last_done'  => 0,
		'baseline'   => false,
		'errors'     => array(),
		'run_new'    => 0,
		'running'    => false,
		'skip'       => array(),
		'piano'      => array(),
	) );
}

function ip_sync_queue( $set = null ) {
	if ( null !== $set ) {
		update_option( 'ip_sync_queue', array_values( $set ), false );
		return $set;
	}
	$q = get_option( 'ip_sync_queue', array() );
	return is_array( $q ) ? $q : array();
}

/**
 * Allinea la pianificazione alle impostazioni.
 */
function ip_sync_reschedule() {
	$next = wp_next_scheduled( 'ip_sync_check' );
	$freq = 'weekly' === ip_opt( 'sync_freq' ) ? 'weekly' : 'daily';
	if ( ! ip_opt( 'sync_enabled' ) ) {
		if ( $next ) {
			wp_clear_scheduled_hook( 'ip_sync_check' );
		}
		return;
	}
	$event = wp_get_scheduled_event( 'ip_sync_check' );
	if ( $event && $event->schedule !== $freq ) {
		wp_clear_scheduled_hook( 'ip_sync_check' );
		$event = null;
	}
	if ( ! $event ) {
		wp_schedule_event( time() + 300, $freq, 'ip_sync_check' );
	}
}
add_action( 'init', function () {
	if ( wp_doing_ajax() ) {
		return;
	}
	if ( ip_opt( 'sync_enabled' ) xor (bool) wp_next_scheduled( 'ip_sync_check' ) ) {
		ip_sync_reschedule();
	}
} );
add_action( 'update_option_ip_settings', 'ip_sync_reschedule', 20 );
add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( 'ip_sync_check' );
	wp_clear_scheduled_hook( 'ip_sync_batch' );
} );

add_action( 'ip_sync_check', 'ip_sync_check' );
add_action( 'ip_sync_batch', 'ip_sync_batch' );

/* ---------------------------------------------------------------------
 * 1. Controllo delle sitemap
 * ------------------------------------------------------------------- */

/**
 * @return array|WP_Error url => lastmod, per gruppo (formazione, piano, pagine).
 */
function ip_sync_sitemaps() {
	$index = ip_src_fetch( IP_SRC_HOST . 'sitemap_index.xml' );
	if ( is_wp_error( $index ) ) {
		return $index;
	}
	$out = array( 'formazione' => array(), 'piano' => array(), 'pagine' => array() );
	preg_match_all( '#<loc>([^<]+)</loc>#', $index, $m );
	foreach ( $m[1] as $map ) {
		$name  = basename( $map, '.xml' );
		$group = 'formazione-sitemap' === $name ? 'formazione' : ( 'piano-sitemap' === $name ? 'piano' : ( in_array( $name, array( 'page-sitemap', 'iscriviti-sitemap', 'servizio-sitemap' ), true ) ? 'pagine' : '' ) );
		if ( ! $group ) {
			continue;
		}
		$xml = ip_src_fetch( trim( $map ) );
		if ( is_wp_error( $xml ) ) {
			return $xml;
		}
		preg_match_all( '#<url>\s*<loc>([^<]+)</loc>(?:\s*<lastmod>([^<]+)</lastmod>)?#', $xml, $u, PREG_SET_ORDER );
		foreach ( $u as $row ) {
			$url = trailingslashit( trim( $row[1] ) );
			if ( false !== strpos( $url, '/en/' ) || IP_SRC_HOST . 'formazione/' === $url || IP_SRC_HOST . 'piano/' === $url ) {
				continue;
			}
			$out[ $group ][ $url ] = isset( $row[2] ) ? trim( $row[2] ) : '';
		}
	}
	return $out;
}

/**
 * Controllo periodico: confronta le date di modifica e mette in coda ciò
 * che va riletto. Al primo avvio registra lo stato di partenza.
 *
 * @param bool $full Rilegge tutti i corsi, non solo quelli cambiati.
 */
function ip_sync_check( $full = false ) {
	$state = ip_sync_state();
	$maps  = ip_sync_sitemaps();
	$state['last_check'] = time();
	if ( is_wp_error( $maps ) ) {
		$state['errors'] = array( current_time( 'mysql' ) . ' · ' . $maps->get_error_message() );
		ip_sync_state( $state );
		return;
	}
	$state['errors'] = array();
	$queue           = ip_sync_queue();
	$queued          = wp_list_pluck( $queue, 1 );
	$add             = function ( $type, $url ) use ( &$queue, &$queued ) {
		if ( ! in_array( $url, $queued, true ) ) {
			$queue[]  = array( $type, $url );
			$queued[] = $url;
		}
	};
	$known = ip_sync_known_courses();
	$scope = (array) ip_opt( 'sync_scope' );

	if ( in_array( 'corsi', $scope, true ) ) {
		foreach ( $maps['formazione'] as $url => $lastmod ) {
			$changed = ! isset( $state['lastmod'][ $url ] ) || $state['lastmod'][ $url ] !== $lastmod;
			$skip    = isset( $state['skip'][ $url ] ) && $state['skip'][ $url ] === $lastmod;
			// Corso mai visto sul sito: va esaminato (potrebbe essere nuovo).
			if ( ! $skip && ( $full || ! isset( $known[ $url ] ) || ( $state['baseline'] && $changed ) ) ) {
				$add( 'corso', $url );
			}
			$state['lastmod'][ $url ] = $lastmod;
		}
		// Piani di studio cambiati: si rilegge il corso a cui appartengono.
		$state['piano'] = array_keys( $maps['piano'] );
		foreach ( $maps['piano'] as $url => $lastmod ) {
			$changed = ! isset( $state['lastmod'][ $url ] ) || $state['lastmod'][ $url ] !== $lastmod;
			$state['lastmod'][ $url ] = $lastmod;
			if ( ! $state['baseline'] || ! $changed ) {
				continue;
			}
			$cu = get_page_by_path( ip_src_slug( $url ), OBJECT, 'curriculum' );
			$course = $cu ? (int) get_post_meta( $cu->ID, '_ip_course', true ) : 0;
			$src    = $course ? ip_meta( 'fonte', $course ) : '';
			if ( $src ) {
				$add( 'corso', trailingslashit( $src ) );
			}
		}
		// Corsi tolti dall'offerta ufficiale.
		foreach ( $known as $url => $post_id ) {
			if ( ! isset( $maps['formazione'][ $url ] ) && 'publish' === get_post_status( $post_id ) ) {
				ip_sync_propose( 'rimosso', $post_id, $url, array(), array( 'Il corso non compare più nell’offerta formativa ufficiale.' ) );
			}
		}
	}

	if ( in_array( 'pagine', $scope, true ) ) {
		foreach ( ip_sync_known_pages() as $url => $page_id ) {
			$lm = isset( $maps['pagine'][ $url ] ) ? $maps['pagine'][ $url ] : '';
			if ( $full || ( $state['baseline'] && ( ! isset( $state['lastmod'][ $url ] ) || $state['lastmod'][ $url ] !== $lm ) ) ) {
				$add( 'pagina', $url );
			}
			$state['lastmod'][ $url ] = $lm;
		}
	}
	foreach ( array( 'agevolazioni' => IP_SYNC_AGEV, 'sedi' => IP_SYNC_SEDI ) as $type => $url ) {
		if ( ! in_array( $type, $scope, true ) ) {
			continue;
		}
		$lm = isset( $maps['pagine'][ $url ] ) ? $maps['pagine'][ $url ] : '';
		if ( $full || ! isset( $state['hash'][ $url ] ) || ( isset( $state['lastmod'][ $url ] ) && $state['lastmod'][ $url ] !== $lm ) ) {
			$add( $type, $url );
		}
		$state['lastmod'][ $url ] = $lm;
	}

	$state['baseline'] = true;
	$state['run_new']  = 0;
	$state['running']  = (bool) $queue;
	ip_sync_state( $state );
	ip_sync_queue( $queue );
	if ( $queue && ! wp_next_scheduled( 'ip_sync_batch' ) ) {
		wp_schedule_single_event( time() + 5, 'ip_sync_batch' );
	}
}

/**
 * Corsi del sito collegati a una scheda ufficiale: url => post_id.
 */
function ip_sync_known_courses() {
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_ip_fonte' AND p.post_type = 'corso' AND p.post_status IN ('publish','draft','pending')" ); // phpcs:ignore
	$out  = array();
	foreach ( $rows as $r ) {
		$out[ trailingslashit( $r->meta_value ) ] = (int) $r->post_id;
	}
	return $out;
}

function ip_sync_known_pages() {
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_ip_fonte' AND p.post_type = 'page' AND p.post_status = 'publish'" ); // phpcs:ignore
	$out  = array();
	foreach ( $rows as $r ) {
		$out[ trailingslashit( $r->meta_value ) ] = (int) $r->post_id;
	}
	return $out;
}

/* ---------------------------------------------------------------------
 * 2. Lavorazione della coda, pochi elementi per volta
 * ------------------------------------------------------------------- */

function ip_sync_batch() {
	$queue = ip_sync_queue();
	if ( ! $queue ) {
		return;
	}
	@set_time_limit( 120 ); // phpcs:ignore
	$todo = array_splice( $queue, 0, IP_SYNC_BATCH );
	ip_sync_queue( $queue );
	$errors = array();
	$new    = 0;
	foreach ( $todo as $item ) {
		$r = ip_sync_process( $item[0], $item[1] );
		if ( is_wp_error( $r ) ) {
			$errors[] = current_time( 'mysql' ) . ' · ' . $r->get_error_message();
		} elseif ( $r ) {
			$new++;
		}
	}
	// Lo stato si rilegge ora: durante l'elaborazione può essere stato aggiornato.
	$state            = ip_sync_state();
	$state['errors']  = array_slice( array_merge( $state['errors'], $errors ), -10 );
	$state['run_new'] += $new;
	$queue            = ip_sync_queue();
	if ( $queue ) {
		ip_sync_state( $state );
		wp_schedule_single_event( time() + 20, 'ip_sync_batch' );
		return;
	}
	$state['running']   = false;
	$state['last_done'] = time();
	ip_sync_state( $state );
	ip_sync_notify( $state['run_new'] );
}

/**
 * Esamina un elemento. Restituisce true se ha prodotto una proposta o una
 * modifica, false se non c'era nulla da cambiare.
 */
function ip_sync_process( $type, $url ) {
	$html = ip_src_fetch( $url );
	if ( is_wp_error( $html ) ) {
		return $html;
	}
	switch ( $type ) {
		case 'corso':
			return ip_sync_course( $url, $html );
		case 'pagina':
			return ip_sync_page( $url, $html );
		case 'agevolazioni':
			return ip_sync_watch_page( 'agevolazioni', $url, $html );
		case 'sedi':
			return ip_sync_sites( $url, $html );
	}
	return false;
}

function ip_sync_piano_urls() {
	$state = ip_sync_state();
	return (array) $state['piano'];
}

function ip_sync_course( $url, $html ) {
	$c = ip_src_course( $url, $html, ip_sync_piano_urls() );
	if ( ! $c ) {
		// Non è un corso (es. pagina dei corsi singoli): non rileggerla finché non cambia.
		$state                  = ip_sync_state();
		$state['skip'][ $url ] = isset( $state['lastmod'][ $url ] ) ? $state['lastmod'][ $url ] : '';
		ip_sync_state( $state );
		return false;
	}
	// Piani di studio collegati.
	$curr = array();
	foreach ( $c['curricula'] as $cu ) {
		$h = ip_src_fetch( $cu['url'] );
		if ( is_wp_error( $h ) ) {
			return $h;
		}
		$p = ip_src_curriculum( $cu['url'], $h );
		if ( $p ) {
			$curr[] = $p;
		}
	}
	$c['curricula'] = $curr;
	$c['old']       = '';

	$post_id = ip_import_find_course( $c );
	if ( ! $post_id ) {
		return ip_sync_propose( 'nuovo', 0, $url, $c, array( 'Nuovo corso nell’offerta ufficiale: ' . $c['title'] ) );
	}
	$lock = ip_meta( 'sync', $post_id );
	if ( 'no' === $lock ) {
		return false;
	}
	$changes = ip_sync_course_diff( $post_id, $c, 'dati' !== $lock );
	if ( ! $changes ) {
		ip_sync_close( 'corso', $post_id );
		return false;
	}
	return ip_sync_propose( 'corso', $post_id, $url, $c, $changes );
}

function ip_sync_norm( $html ) {
	$t = preg_replace( '/<!--.*?-->/s', '', (string) $html );
	$t = html_entity_decode( wp_strip_all_tags( $t ), ENT_QUOTES, 'UTF-8' );
	// Gli spazi non contano: conta solo il testo.
	return preg_replace( '/\s+/u', '', $t );
}

/**
 * Elenco leggibile delle differenze tra il corso sul sito e la scheda ufficiale.
 */
function ip_sync_course_diff( $post_id, $c, $with_text ) {
	$ch    = array();
	$label = array( 'code' => 'Classe', 'cfu' => 'CFU', 'durata' => 'Durata', 'retta' => 'Costo', 'evidenza' => 'Nota in evidenza' );
	foreach ( $label as $k => $l ) {
		$new = trim( (string) $c[ $k ] );
		$old = trim( ip_meta( $k, $post_id ) );
		if ( '' !== $new && $new !== $old ) {
			$ch[] = sprintf( '%s: «%s» → «%s»', $l, '' === $old ? '—' : $old, $new );
		}
	}
	$st_new = $c['stato'] ? $c['stato'] : 'aperte';
	$st_old = ip_meta( 'stato', $post_id ) ? ip_meta( 'stato', $post_id ) : 'aperte';
	if ( $st_new !== $st_old && 'presto' !== $st_old ) {
		$ch[] = sprintf( 'Iscrizioni: %s → %s', ip_status_label( $st_old ), ip_status_label( $st_new ) );
	}
	if ( $with_text ) {
		$old_title = html_entity_decode( get_post_field( 'post_title', $post_id ), ENT_QUOTES, 'UTF-8' );
		if ( html_entity_decode( $c['title'], ENT_QUOTES, 'UTF-8' ) !== $old_title ) {
			$ch[] = sprintf( 'Titolo: «%s» → «%s»', $old_title, $c['title'] );
		}
		$a = ip_sync_norm( get_post_field( 'post_content', $post_id ) );
		$b = ip_sync_norm( $c['content'] );
		if ( $a !== $b ) {
			similar_text( $a, $b, $pct );
			$ch[] = sprintf( 'Testo della scheda modificato (simile al %d%%, %+d caratteri)', (int) $pct, mb_strlen( $b ) - mb_strlen( $a ) );
		}
	}
	$old_docs = wp_list_pluck( ip_meta_rows( 'docs', $post_id ), 'url' );
	$new_docs = wp_list_pluck( $c['docs'], 1 );
	foreach ( array_diff( $new_docs, $old_docs ) as $u ) {
		$ch[] = 'Nuovo documento: ' . basename( wp_parse_url( $u, PHP_URL_PATH ) );
	}
	foreach ( array_diff( $old_docs, $new_docs ) as $u ) {
		if ( false !== strpos( $u, 'unimarconi.it' ) ) {
			$ch[] = 'Documento non più presente: ' . basename( wp_parse_url( $u, PHP_URL_PATH ) );
		}
	}
	foreach ( $c['curricula'] as $cu ) {
		$ex = get_page_by_path( $cu['slug'], OBJECT, 'curriculum' );
		if ( ! $ex ) {
			$ch[] = 'Nuovo piano di studio: ' . $cu['name'];
		} elseif ( ip_sync_norm( $ex->post_content ) !== ip_sync_norm( $cu['content'] ) ) {
			$ch[] = 'Piano di studio aggiornato: ' . $cu['name'];
		}
	}
	return $ch;
}

function ip_sync_page( $url, $html ) {
	$pages = ip_sync_known_pages();
	if ( empty( $pages[ $url ] ) ) {
		return false;
	}
	$page_id = $pages[ $url ];
	if ( 'no' === get_post_meta( $page_id, '_ip_sync', true ) ) {
		return false;
	}
	$content = ip_src_page( $html );
	if ( ! $content || ip_sync_norm( $content ) === ip_sync_norm( get_post_field( 'post_content', $page_id ) ) ) {
		ip_sync_close( 'pagina', $page_id );
		return false;
	}
	similar_text( ip_sync_norm( get_post_field( 'post_content', $page_id ) ), ip_sync_norm( $content ), $pct );
	return ip_sync_propose( 'pagina', $page_id, $url, array( 'content' => $content ), array( sprintf( 'Testo della pagina modificato (simile al %d%%)', (int) $pct ) ) );
}

/**
 * Pagine solo da sorvegliare (es. agevolazioni): segnala le righe cambiate.
 */
function ip_sync_watch_page( $type, $url, $html ) {
	$state = ip_sync_state();
	$text  = ip_src_main_text( $html );
	$hash  = md5( $text );
	$prev  = isset( $state['hash'][ $url ] ) ? $state['hash'][ $url ] : '';
	$old   = (string) get_option( 'ip_sync_text_' . md5( $url ), '' );
	$state['hash'][ $url ] = $hash;
	ip_sync_state( $state );
	update_option( 'ip_sync_text_' . md5( $url ), $text, false );
	if ( ! $prev || $prev === $hash ) {
		return false;
	}
	$a   = array_filter( array_map( 'trim', explode( "\n", $old ) ) );
	$b   = array_filter( array_map( 'trim', explode( "\n", $text ) ) );
	$ch  = array();
	foreach ( array_slice( array_diff( $b, $a ), 0, 12 ) as $l ) {
		$ch[] = '+ ' . wp_trim_words( $l, 30 );
	}
	foreach ( array_slice( array_diff( $a, $b ), 0, 12 ) as $l ) {
		$ch[] = '− ' . wp_trim_words( $l, 30 );
	}
	return ip_sync_propose( $type, 0, $url, array(), $ch ? $ch : array( 'La pagina ufficiale è cambiata.' ) );
}

function ip_sync_sites( $url, $html ) {
	$sites = ip_src_exam_sites( $html );
	if ( count( $sites ) < 10 ) {
		return new WP_Error( 'ip_sedi', 'Elenco sedi d’esame non riconosciuto: nessuna modifica proposta.' );
	}
	$state = ip_sync_state();
	$prev  = isset( $state['hash'][ $url ] ) ? $state['hash'][ $url ] : '';
	$hash  = md5( wp_json_encode( $sites ) );
	$state['hash'][ $url ] = $hash;
	ip_sync_state( $state );
	// Al primo controllo si registra solo il punto di partenza.
	if ( ! $prev || $prev === $hash ) {
		return false;
	}
	$key = function ( $r ) {
		return mb_strtolower( $r['citta'] . '|' . preg_replace( '/\W+/u', '', $r['indirizzo'] ) );
	};
	$old = array_map( $key, ip_rows( 'exam_sites' ) );
	$new = array_map( $key, $sites );
	$ch  = array();
	foreach ( $sites as $r ) {
		if ( ! in_array( $key( $r ), $old, true ) ) {
			$ch[] = '+ ' . $r['citta'] . ' – ' . $r['indirizzo'];
		}
	}
	foreach ( ip_rows( 'exam_sites' ) as $r ) {
		if ( ! in_array( $key( $r ), $new, true ) ) {
			$ch[] = '− ' . $r['citta'] . ' – ' . $r['indirizzo'];
		}
	}
	if ( ! $ch ) {
		ip_sync_close( 'sedi', 0 );
		return false;
	}
	return ip_sync_propose( 'sedi', 0, $url, array( 'sites' => $sites ), $ch );
}

/* ---------------------------------------------------------------------
 * 3. Proposte: registrazione, applicazione, chiusura
 * ------------------------------------------------------------------- */

function ip_sync_find_open( $type, $target, $url ) {
	$q = get_posts( array(
		'post_type'      => 'ip_update',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array( 'key' => '_type', 'value' => $type ),
			array( 'key' => '_state', 'value' => 'aperta' ),
			$target ? array( 'key' => '_target', 'value' => (int) $target ) : array( 'key' => '_url', 'value' => $url ),
		),
	) );
	return $q ? (int) $q[0] : 0;
}

/**
 * Registra (o aggiorna) una proposta. In modalità automatica la applica subito.
 */
function ip_sync_propose( $type, $target, $url, $payload, $changes ) {
	$titles = array(
		'nuovo'        => 'Nuovo corso',
		'corso'        => 'Corso aggiornato',
		'rimosso'      => 'Corso tolto dall’offerta',
		'pagina'       => 'Pagina aggiornata',
		'agevolazioni' => 'Agevolazioni da rivedere',
		'sedi'         => 'Sedi d’esame cambiate',
	);
	$name = $target ? html_entity_decode( get_post_field( 'post_title', $target ), ENT_QUOTES, 'UTF-8' ) : ( isset( $payload['title'] ) ? $payload['title'] : ip_src_slug( $url ) );
	$sig  = md5( $type . '|' . $target . '|' . $url . '|' . wp_json_encode( $changes ) );
	// Una differenza già ignorata non viene riproposta finché non cambia ancora.
	if ( get_posts( array( 'post_type' => 'ip_update', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_sig', 'value' => $sig ), array( 'key' => '_state', 'value' => 'ignorata' ) ) ) ) ) {
		return false;
	}
	$id   = ip_sync_find_open( $type, $target, $url );
	$data = array(
		'post_type'   => 'ip_update',
		'post_status' => 'publish',
		'post_title'  => $titles[ $type ] . ': ' . $name,
	);
	if ( $id ) {
		$data['ID'] = $id;
		wp_update_post( $data );
	} else {
		$id = wp_insert_post( $data );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return false;
	}
	update_post_meta( $id, '_type', $type );
	update_post_meta( $id, '_target', (int) $target );
	update_post_meta( $id, '_url', $url );
	update_post_meta( $id, '_payload', wp_slash( $payload ) );
	update_post_meta( $id, '_changes', $changes );
	update_post_meta( $id, '_state', 'aperta' );
	update_post_meta( $id, '_sig', $sig );

	$auto = 'auto' === ip_opt( 'sync_mode' );
	if ( $auto && ( 'rimosso' !== $type || ip_opt( 'sync_retire' ) ) && 'agevolazioni' !== $type ) {
		ip_sync_apply( $id, 'automatica' );
	}
	return true;
}

/**
 * Applica una proposta.
 */
function ip_sync_apply( $id, $state = 'applicata' ) {
	$type    = get_post_meta( $id, '_type', true );
	$target  = (int) get_post_meta( $id, '_target', true );
	$payload = get_post_meta( $id, '_payload', true );
	switch ( $type ) {
		case 'nuovo':
		case 'corso':
			if ( empty( $payload['title'] ) ) {
				return false;
			}
			$lock = $target ? ip_meta( 'sync', $target ) : '';
			// Corso nuovo: pubblicato se approvato a mano; in automatico segue l'impostazione.
			$status = 'automatica' === $state && 'pubblica' !== ip_opt( 'sync_new' ) ? 'draft' : 'publish';
			$r      = ip_import_course( $payload, 'overwrite', ip_import_tip_ids(), array(
				'status'  => $status,
				'content' => 'dati' !== $lock,
				'order'   => 900,
			) );
			if ( ! $r ) {
				return false;
			}
			update_post_meta( $id, '_target', $r['id'] );
			delete_transient( 'ip_course_options' );
			break;
		case 'rimosso':
			if ( $target ) {
				wp_update_post( array( 'ID' => $target, 'post_status' => 'draft' ) );
			}
			break;
		case 'pagina':
			if ( $target && ! empty( $payload['content'] ) ) {
				wp_update_post( array( 'ID' => $target, 'post_content' => wp_slash( $payload['content'] ) ) );
			}
			break;
		case 'sedi':
			if ( ! empty( $payload['sites'] ) ) {
				$o               = get_option( 'ip_settings', array() );
				$o               = is_array( $o ) ? $o : array();
				$o['exam_sites'] = $payload['sites'];
				update_option( 'ip_settings', $o );
			}
			break;
	}
	update_post_meta( $id, '_state', $state );
	update_post_meta( $id, '_done', current_time( 'mysql' ) );
	delete_post_meta( $id, '_payload' ); // Non serve più: risparmia spazio.
	return true;
}

/**
 * Chiude una proposta aperta che non ha più ragione di esistere.
 */
function ip_sync_close( $type, $target ) {
	$id = ip_sync_find_open( $type, $target, '' );
	if ( $id ) {
		update_post_meta( $id, '_state', 'superata' );
	}
}

function ip_sync_open_count() {
	$n = get_transient( 'ip_sync_open' );
	if ( false === $n ) {
		$q = new WP_Query( array( 'post_type' => 'ip_update', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_state', 'meta_value' => 'aperta' ) );
		$n = (int) $q->found_posts;
		set_transient( 'ip_sync_open', $n, 10 * MINUTE_IN_SECONDS );
	}
	return (int) $n;
}
add_action( 'updated_post_meta', function ( $mid, $obj, $key ) {
	if ( '_state' === $key ) {
		delete_transient( 'ip_sync_open' );
	}
}, 10, 3 );
add_action( 'added_post_meta', function ( $mid, $obj, $key ) {
	if ( '_state' === $key ) {
		delete_transient( 'ip_sync_open' );
	}
}, 10, 3 );

function ip_sync_notify( $count ) {
	if ( ! $count ) {
		return;
	}
	$to = ip_opt( 'sync_email' ) ? ip_opt( 'sync_email' ) : get_option( 'admin_email' );
	$q  = get_posts( array( 'post_type' => 'ip_update', 'posts_per_page' => 50, 'meta_key' => '_state', 'meta_value' => array( 'aperta', 'automatica' ), 'meta_compare' => 'IN', 'date_query' => array( array( 'after' => '2 hours ago' ) ) ) );
	$lines = array();
	foreach ( $q as $p ) {
		$lines[] = '• ' . $p->post_title;
	}
	$body = sprintf( "Il controllo dell’offerta ufficiale UniMarconi ha trovato %d novità.\n\n%s\n\nRivedile qui: %s\n", $count, implode( "\n", $lines ), admin_url( 'admin.php?page=ip-sync' ) );
	wp_mail( $to, '[' . ip_opt( 'brand' ) . '] Aggiornamenti dall’offerta ufficiale UniMarconi', $body );
}

/* ---------------------------------------------------------------------
 * 4. Pannello: Infopoint → Aggiornamenti
 * ------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	$n     = ip_sync_open_count();
	$badge = $n ? ' <span class="awaiting-mod"><span class="pending-count">' . $n . '</span></span>' : '';
	add_submenu_page( 'ip-settings', 'Aggiornamenti ufficiali', 'Aggiornamenti' . $badge, 'manage_options', 'ip-sync', 'ip_sync_page_render' );
}, 20 );

add_action( 'admin_post_ip_sync', function () {
	check_admin_referer( 'ip_sync' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	$do  = sanitize_key( $_REQUEST['do'] ?? '' );
	$msg = '';
	if ( 'check' === $do || 'full' === $do ) {
		ip_sync_check( 'full' === $do );
		ip_sync_batch(); // Primo gruppo subito, il resto in background.
		$msg = 'Controllo avviato: ' . count( ip_sync_queue() ) . ' pagine ancora in coda, lavorate in background.';
	} elseif ( 'apply' === $do || 'ignore' === $do ) {
		$id = absint( $_REQUEST['id'] ?? 0 );
		if ( $id && 'ip_update' === get_post_type( $id ) ) {
			if ( 'apply' === $do ) {
				$msg = ip_sync_apply( $id ) ? 'Aggiornamento applicato.' : 'Impossibile applicare: riesegui il controllo.';
			} else {
				update_post_meta( $id, '_state', 'ignorata' );
				$msg = 'Aggiornamento ignorato.';
			}
		}
	} elseif ( 'apply_all' === $do ) {
		$ids = get_posts( array( 'post_type' => 'ip_update', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_state', 'meta_value' => 'aperta' ) );
		$n   = 0;
		foreach ( $ids as $id ) {
			if ( 'agevolazioni' !== get_post_meta( $id, '_type', true ) && ip_sync_apply( $id ) ) {
				$n++;
			}
		}
		$msg = $n . ' aggiornamenti applicati.';
	}
	wp_safe_redirect( add_query_arg( 'ip_msg', rawurlencode( $msg ), admin_url( 'admin.php?page=ip-sync' ) ) );
	exit;
} );

function ip_sync_action_url( $do, $id = 0 ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=ip_sync&do=' . $do . ( $id ? '&id=' . $id : '' ) ), 'ip_sync' );
}

function ip_sync_page_render() {
	$state = ip_sync_state();
	$queue = ip_sync_queue();
	$next  = wp_next_scheduled( 'ip_sync_check' );
	$fmt   = function ( $t ) {
		return $t ? wp_date( 'j F Y, H:i', $t ) : '—';
	};
	$types = array( 'nuovo' => 'Nuovo corso', 'corso' => 'Corso', 'rimosso' => 'Corso tolto', 'pagina' => 'Pagina', 'agevolazioni' => 'Agevolazioni', 'sedi' => 'Sedi d’esame' );
	$open  = get_posts( array( 'post_type' => 'ip_update', 'posts_per_page' => 200, 'meta_key' => '_state', 'meta_value' => 'aperta', 'orderby' => 'modified' ) );
	$done  = get_posts( array( 'post_type' => 'ip_update', 'posts_per_page' => 30, 'meta_key' => '_state', 'meta_value' => array( 'applicata', 'automatica', 'ignorata' ), 'meta_compare' => 'IN', 'orderby' => 'modified' ) );
	?>
	<div class="wrap ip-admin">
		<h1>Aggiornamenti dall’offerta ufficiale UniMarconi</h1>
		<?php if ( ! empty( $_GET['ip_msg'] ) ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( wp_unslash( $_GET['ip_msg'] ) ); ?></p></div>
		<?php endif; ?>
		<div class="ip-sync-status">
			<p><strong>Controllo automatico:</strong> <?php echo ip_opt( 'sync_enabled' ) ? esc_html( ( 'weekly' === ip_opt( 'sync_freq' ) ? 'settimanale' : 'giornaliero' ) . ' · modalità ' . ( 'auto' === ip_opt( 'sync_mode' ) ? 'automatica' : 'con approvazione' ) ) : 'disattivato'; ?> · <a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=sync' ) ); ?>">impostazioni</a></p>
			<p>Ultimo controllo: <?php echo esc_html( $fmt( $state['last_check'] ) ); ?> · Prossimo: <?php echo esc_html( $fmt( $next ) ); ?><?php if ( $queue ) : ?> · <strong><?php echo count( $queue ); ?> pagine in lavorazione</strong> (ricarica tra qualche minuto)<?php endif; ?></p>
			<?php if ( $state['errors'] ) : ?>
				<p class="ip-sync-err"><?php echo esc_html( implode( ' | ', array_slice( $state['errors'], -3 ) ) ); ?></p>
			<?php endif; ?>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( ip_sync_action_url( 'check' ) ); ?>">Controlla ora</a>
				<a class="button" href="<?php echo esc_url( ip_sync_action_url( 'full' ) ); ?>" onclick="return confirm('Rilegge tutte le schede ufficiali (circa 160 pagine, qualche minuto in background). Procedere?');">Verifica completa</a>
			</p>
		</div>

		<h2>Da rivedere <?php echo $open ? '(' . count( $open ) . ')' : ''; ?></h2>
		<?php if ( ! $open ) : ?>
			<p>Nessuna differenza da rivedere: il sito è allineato all’ultimo controllo.</p>
		<?php else : ?>
			<p><a class="button" href="<?php echo esc_url( ip_sync_action_url( 'apply_all' ) ); ?>" onclick="return confirm('Applicare tutti gli aggiornamenti proposti? Le versioni precedenti restano nelle revisioni.');">Applica tutti</a></p>
			<table class="widefat striped ip-sync-table">
				<thead><tr><th style="width:130px">Tipo</th><th>Elemento</th><th>Cosa cambia</th><th style="width:170px">Azioni</th></tr></thead>
				<tbody>
				<?php foreach ( $open as $p ) : ?>
					<?php
					$type   = get_post_meta( $p->ID, '_type', true );
					$target = (int) get_post_meta( $p->ID, '_target', true );
					$url    = get_post_meta( $p->ID, '_url', true );
					?>
					<tr>
						<td><?php echo esc_html( $types[ $type ] ?? $type ); ?><br><small><?php echo esc_html( get_the_modified_date( 'j M Y', $p ) ); ?></small></td>
						<td>
							<strong><?php echo esc_html( preg_replace( '/^[^:]+:\s*/', '', $p->post_title ) ); ?></strong><br>
							<?php if ( $target ) : ?><a href="<?php echo esc_url( get_edit_post_link( $target ) ); ?>">Modifica sul sito</a> · <?php endif; ?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">Pagina ufficiale</a>
						</td>
						<td><ul class="ip-sync-changes"><?php foreach ( (array) get_post_meta( $p->ID, '_changes', true ) as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?></ul></td>
						<td>
							<?php if ( 'agevolazioni' === $type ) : ?>
								<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=agevolazione' ) ); ?>">Aggiorna a mano</a>
								<a class="button-link" href="<?php echo esc_url( ip_sync_action_url( 'ignore', $p->ID ) ); ?>">Fatto</a>
							<?php else : ?>
								<a class="button button-primary" href="<?php echo esc_url( ip_sync_action_url( 'apply', $p->ID ) ); ?>"><?php echo 'rimosso' === $type ? 'Metti in bozza' : 'Applica'; ?></a>
								<a class="button-link" href="<?php echo esc_url( ip_sync_action_url( 'ignore', $p->ID ) ); ?>">Ignora</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( $done ) : ?>
			<h2>Ultime operazioni</h2>
			<table class="widefat striped">
				<tbody>
				<?php foreach ( $done as $p ) : ?>
					<tr><td style="width:170px"><?php echo esc_html( get_the_modified_date( 'j M Y, H:i', $p ) ); ?></td><td><?php echo esc_html( $p->post_title ); ?></td><td style="width:120px"><?php echo esc_html( get_post_meta( $p->ID, '_state', true ) ); ?></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<p class="description" style="margin-top:20px">Ogni modifica applicata a un corso o a una pagina lascia la versione precedente nelle <em>Revisioni</em> di WordPress. Per escludere un corso dagli aggiornamenti usa il campo «Aggiornamento automatico» nella sua scheda.</p>
	</div>
	<?php
}

// Blocco degli aggiornamenti sulle pagine importate dal sito ufficiale.
add_action( 'add_meta_boxes_page', function ( $post ) {
	if ( get_post_meta( $post->ID, '_ip_fonte', true ) ) {
		add_meta_box( 'ip-sync-page', 'Aggiornamento automatico', function ( $post ) {
			wp_nonce_field( 'ip_sync_page', 'ip_sync_page_nonce' );
			$v = get_post_meta( $post->ID, '_ip_sync', true );
			printf( '<p><label><input type="checkbox" name="ip_sync_lock" value="1" %s> Non aggiornare questa pagina dal sito ufficiale</label></p><p class="description">Fonte: <a href="%s" target="_blank">%s</a></p>', checked( $v, 'no', false ), esc_url( get_post_meta( $post->ID, '_ip_fonte', true ) ), esc_html( ip_src_slug( get_post_meta( $post->ID, '_ip_fonte', true ) ) ) );
		}, 'page', 'side' );
	}
} );
add_action( 'save_post_page', function ( $id ) {
	if ( isset( $_POST['ip_sync_page_nonce'] ) && wp_verify_nonce( $_POST['ip_sync_page_nonce'], 'ip_sync_page' ) && current_user_can( 'edit_post', $id ) ) {
		! empty( $_POST['ip_sync_lock'] ) ? update_post_meta( $id, '_ip_sync', 'no' ) : delete_post_meta( $id, '_ip_sync' );
	}
} );
