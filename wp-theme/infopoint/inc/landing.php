<?php
/**
 * Landing di conversione per le campagne.
 *
 * Una landing nasce da un corso, una tipologia, un'agevolazione o in forma
 * generica: titolo, punti di forza, dati, costi e testi vengono letti al
 * momento dai contenuti del sito, che si aggiornano dal sito ufficiale.
 * Ogni testo si può comunque riscrivere a mano.
 *
 * La pagina è chiusa: nessun menu, nessun collegamento verso altre pagine o
 * siti; chi siamo, privacy e cookie si aprono in finestre sulla pagina
 * stessa. Restano solo telefono, WhatsApp e moduli.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'ip_landing', array(
		'labels'              => array(
			'name'          => 'Landing',
			'singular_name' => 'Landing',
			'menu_name'     => 'Landing',
			'add_new'       => 'Nuova landing',
			'add_new_item'  => 'Nuova landing',
			'edit_item'     => 'Modifica landing',
			'all_items'     => 'Tutte le landing',
			'search_items'  => 'Cerca landing',
			'not_found'     => 'Nessuna landing',
		),
		'public'              => true,
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => false,
		'has_archive'         => false,
		'menu_position'       => 26,
		'menu_icon'           => 'dashicons-megaphone',
		'supports'            => array( 'title', 'thumbnail', 'revisions' ),
		'rewrite'             => array( 'slug' => 'lp', 'with_front' => false ),
	) );
} );

/* ---------------------------------------------------------------------
 * Campi
 * ------------------------------------------------------------------- */

function ip_lp_sections() {
	return array(
		'dati'       => 'Striscia dati (classe, CFU, durata, costo)',
		'scheda'     => 'Contenuto: scheda del corso, elenco corsi o condizioni dell’agevolazione',
		'piani'      => 'Piani di studio (solo corso)',
		'costi'      => 'Costi e agevolazioni',
		'sedi'       => 'Mappa delle sedi d’esame',
		'passi'      => 'Come funziona con noi',
		'team'       => 'Chi ti segue (testi e foto in Infopoint → Impostazioni → Landing)',
		'recensioni' => 'Recensioni',
		'faq'        => 'Domande frequenti',
		'finale'     => 'Modulo finale «ti richiamiamo»',
	);
}

function ip_lp_defaults() {
	return array(
		'fonte_tipo'  => 'generica',
		'fonte_id'    => 0,
		'kicker'      => '',
		'titolo'      => '',
		'sottotitolo' => '',
		'punti'       => '',
		'modulo'      => 'info',
		'mod_titolo'  => '',
		'mod_testo'   => '',
		'pulsante'    => '',
		'sezioni'     => array_keys( ip_lp_sections() ),
		'uscita'      => '1',
		'indicizza'   => '',
	);
}

function ip_lp_get( $id ) {
	$out = ip_lp_defaults();
	foreach ( $out as $k => $v ) {
		if ( metadata_exists( 'post', $id, '_lp_' . $k ) ) {
			$out[ $k ] = get_post_meta( $id, '_lp_' . $k, true );
		}
	}
	$out['sezioni'] = (array) $out['sezioni'];
	return $out;
}

/* ---------------------------------------------------------------------
 * Contenuti: tutto ciò che non è stato riscritto si legge dai dati del sito
 * ------------------------------------------------------------------- */

/**
 * Toglie i collegamenti che porterebbero fuori dalla landing.
 */
function ip_lp_unlink( $html ) {
	return preg_replace( '#<a\b(?![^>]*href="(?:tel:|\#))[^>]*>(.*?)</a>#is', '$1', (string) $html );
}

function ip_lp_lines( $t ) {
	return array_values( array_filter( array_map( 'trim', explode( "\n", (string) $t ) ) ) );
}

/**
 * Prima data futura citata in un testo («entro il 30 novembre 2026»).
 */
function ip_lp_deadline( $text ) {
	$mesi = array( 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre' );
	if ( preg_match( '/entro il (\d{1,2}) (' . implode( '|', $mesi ) . ') (\d{4})/iu', (string) $text, $m ) ) {
		$ts = mktime( 23, 59, 59, array_search( mb_strtolower( $m[2] ), $mesi, true ) + 1, (int) $m[1], (int) $m[3] );
		if ( $ts > time() ) {
			return $m[1] . ' ' . mb_strtolower( $m[2] ) . ' ' . $m[3];
		}
	}
	return '';
}

/**
 * Tutto quello che serve alla pagina, con i valori automatici dove i campi
 * sono vuoti.
 */
function ip_lp_data( $id ) {
	$o    = ip_lp_get( $id );
	$type = $o['fonte_tipo'];
	$src  = (int) $o['fonte_id'];
	$f    = ip_fees();
	$from = ip_price_from();
	$d    = array(
		'type'     => $type,
		'kicker'   => ip_opt( 'brand' ) . ' · Agenzia partner UniMarconi',
		'title'    => ip_opt( 'hero_title' ),
		'sub'      => ip_opt( 'hero_text' ),
		'points'   => array(),
		'facts'    => array(),
		'price'    => $from ? 'Retta da <strong>' . esc_html( $from ) . ' al mese</strong> con le agevolazioni dell’Ateneo' : '',
		'deadline' => '',
		'image'    => ip_hero_image_id(),
		'course'   => 0,
		'interest' => '',
		'courses'  => array(),
		'agev'     => 0,
		'content'  => '',
		'curricula' => array(),
		'name'     => '',
	);

	if ( 'corso' === $type && 'corso' === get_post_type( $src ) && 'publish' === get_post_status( $src ) ) {
		$tip   = ip_course_tipologia( $src );
		$stato = ip_meta( 'stato', $src ) ? ip_meta( 'stato', $src ) : 'aperte';
		$cost  = ip_meta( 'retta', $src );
		$d['name']     = ip_course_name( $src );
		$d['course']   = $src;
		$d['kicker']   = ( $tip ? $tip->name . ' online · ' : '' ) . ip_status_label( $stato );
		$d['title']    = html_entity_decode( get_the_title( $src ), ENT_QUOTES, 'UTF-8' );
		$d['sub']      = 'Studi online con lezioni sempre disponibili e sostieni gli esami in presenza. Ti aiutiamo a scegliere, verifichiamo gratis i crediti che hai già e ti seguiamo fino all’immatricolazione.';
		$d['facts']    = array_filter( array(
			'Classe'     => ip_meta( 'code', $src ),
			'CFU'        => ip_meta( 'cfu', $src ),
			'Durata'     => ip_meta( 'durata', $src ),
			'Lingua'     => ip_meta( 'lingua', $src ),
			'Iscrizioni' => ip_status_label( $stato ),
		) );
		$d['points'] = array_filter( array(
			ip_meta( 'evidenza', $src ),
			'Lezioni online sempre disponibili, esami in presenza',
			ip_course_curricula( $src ) ? count( ip_course_curricula( $src ) ) . ' piani di studio tra cui scegliere' : '',
			'Prevalutazione dei crediti già maturati, gratuita',
		) );
		if ( $cost ) {
			$d['facts']['Costo'] = $cost;
			$d['price']          = 'Costo: <strong>' . esc_html( $cost ) . '</strong>';
		} elseif ( $tip && 0 === strpos( $tip->slug, 'laurea' ) && ip_retta_std() ) {
			$d['facts']['Retta'] = ip_retta_std();
		} else {
			$d['price'] = '';
		}
		$d['image']     = has_post_thumbnail( $src ) ? (int) get_post_thumbnail_id( $src ) : $d['image'];
		$d['content']   = ip_lp_unlink( ip_fill( do_blocks( get_post_field( 'post_content', $src ) ) ) );
		$d['curricula'] = ip_course_curricula( $src );
	} elseif ( 'tipologia' === $type && ( $term = get_term( $src, 'tipologia' ) ) && ! is_wp_error( $term ) ) {
		$d['name']     = $term->name;
		$d['interest'] = $term->name;
		$d['kicker']   = 'Università degli Studi Guglielmo Marconi · iscrizioni con ' . ip_opt( 'brand' );
		$d['title']    = $term->name . ' online all’Università Marconi';
		$d['sub']      = $term->description ? wp_strip_all_tags( $term->description ) : 'Scegli il corso, studia online e sostieni gli esami in presenza. Un orientatore ti spiega piani di studio, costi e agevolazioni e ti segue nell’iscrizione.';
		$d['courses']  = get_posts( array(
			'post_type'      => 'corso',
			'posts_per_page' => 60,
			'fields'         => 'ids',
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'tax_query'      => array( array( 'taxonomy' => 'tipologia', 'terms' => $term->term_id ) ),
		) );
		$d['points']   = array( count( $d['courses'] ) . ' corsi tra cui scegliere', 'Lezioni online sempre disponibili, esami in presenza', 'Prevalutazione dei crediti già maturati, gratuita' );
		$d['image']    = ip_term_image_id( $term ) ? ip_term_image_id( $term ) : $d['image'];
		if ( 0 !== strpos( $term->slug, 'laurea' ) ) {
			$d['price'] = '';
		}
	} elseif ( 'agevolazione' === $type && 'agevolazione' === get_post_type( $src ) && 'publish' === get_post_status( $src ) ) {
		$title         = html_entity_decode( get_the_title( $src ), ENT_QUOTES, 'UTF-8' );
		$retta         = ip_meta( 'retta', $src );
		$rata          = ip_meta( 'rata', $src );
		$cond          = ip_lp_lines( ip_meta( 'cond', $src ) );
		$d['name']     = $title;
		$d['agev']     = $src;
		$d['interest'] = 'Agevolazione «' . $title . '»';
		$d['kicker']   = 'Agevolazione dell’Università Marconi';
		$d['title']    = $title . ( $retta ? ': retta ' . ip_eur( $retta ) . ' l’anno' : '' );
		$d['sub']      = ip_meta( 'dest', $src ) ? rtrim( ip_meta( 'dest', $src ), '.…' ) . '. Verifichiamo con te se ne hai diritto e ti seguiamo nell’iscrizione.' : 'Verifichiamo con te se ne hai diritto e ti seguiamo nell’iscrizione.';
		$d['points']   = array_slice( $cond, 0, 4 );
		$d['price']    = $rata ? 'Rata mensile <strong>' . esc_html( ip_eur( $rata ) ) . '</strong>' . ( $retta ? ' · ' . esc_html( ip_eur( $retta ) ) . ' l’anno' : '' ) : '';
		$d['deadline'] = ip_lp_deadline( ip_meta( 'cond', $src ) . "\n" . ip_meta( 'det', $src ) );
		$d['facts']    = array_filter( array(
			'Retta annua'  => ip_eur( $retta ),
			'Rata mensile' => ip_eur( $rata ),
			'Standard'     => ip_retta_std(),
		) );
	} else {
		$d['type'] = 'generica';
	}

	// Testi riscritti a mano: hanno la precedenza.
	foreach ( array( 'kicker' => 'kicker', 'titolo' => 'title', 'sottotitolo' => 'sub' ) as $k => $to ) {
		if ( '' !== trim( (string) $o[ $k ] ) ) {
			$d[ $to ] = ip_fill( $o[ $k ] );
		}
	}
	if ( '' !== trim( (string) $o['punti'] ) ) {
		$d['points'] = array_map( 'ip_fill', ip_lp_lines( $o['punti'] ) );
	}
	if ( has_post_thumbnail( $id ) ) {
		$d['image'] = (int) get_post_thumbnail_id( $id );
	}
	$d['o'] = $o;
	return $d;
}

/* ---------------------------------------------------------------------
 * Pagina pubblica
 * ------------------------------------------------------------------- */

add_filter( 'body_class', function ( $c ) {
	if ( is_singular( 'ip_landing' ) ) {
		$c[] = 'lp';
	}
	return $c;
} );

// Le landing delle campagne non servono nei motori di ricerca (salvo scelta diversa).
add_filter( 'wp_robots', function ( $r ) {
	if ( is_singular( 'ip_landing' ) && ! get_post_meta( get_queried_object_id(), '_lp_indicizza', true ) ) {
		$r['noindex'] = true;
		$r['follow']  = false;
	}
	return $r;
} );

/**
 * Finestra con il contenuto di una pagina del sito (chi siamo, privacy…).
 */
function ip_lp_dialog( $key, $title, $html, $url = '' ) {
	if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
		return;
	}
	printf(
		'<dialog class="lp-dialog" id="lp-%1$s"%2$s aria-label="%3$s"><div class="lp-dialog-in"><button type="button" class="lp-x" data-lp-close aria-label="Chiudi">%4$s</button><h2>%3$s</h2><div class="prose">%5$s</div></div></dialog>',
		esc_attr( $key ),
		$url ? ' data-url="' . esc_url( $url ) . '"' : '',
		esc_html( $title ),
		ip_icon( 'close', 22 ), // phpcs:ignore
		ip_lp_unlink( $html ) // phpcs:ignore
	);
}

function ip_lp_page_html( $id ) {
	if ( ! $id || 'publish' !== get_post_status( $id ) ) {
		return '';
	}
	return do_shortcode( do_blocks( get_post_field( 'post_content', $id ) ) );
}

/* ---------------------------------------------------------------------
 * Pannello: campi della landing
 * ------------------------------------------------------------------- */

add_action( 'add_meta_boxes_ip_landing', function () {
	add_meta_box( 'ip-lp', 'Contenuto della landing', 'ip_lp_box', 'ip_landing', 'normal', 'high' );
	add_meta_box( 'ip-lp-link', 'Indirizzo per le campagne', 'ip_lp_link_box', 'ip_landing', 'side', 'high' );
} );

function ip_lp_source_options() {
	$out = array( 'generica' => array( 0 => 'Generica (tutta l’offerta)' ) );
	foreach ( ip_tipologie() as $t ) {
		$out['tipologia'][ $t->term_id ] = $t->name;
	}
	foreach ( get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $p ) {
		$out['agevolazione'][ $p->ID ] = html_entity_decode( $p->post_title, ENT_QUOTES, 'UTF-8' );
	}
	foreach ( ip_tipologie() as $t ) {
		$ids = get_posts( array( 'post_type' => 'corso', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'title', 'order' => 'ASC', 'tax_query' => array( array( 'taxonomy' => 'tipologia', 'terms' => $t->term_id ) ) ) );
		foreach ( $ids as $cid ) {
			$code                       = ip_meta( 'code', $cid );
			$out[ 'corso|' . $t->name ][ $cid ] = ip_course_name( $cid ) . ( $code ? ' (' . $code . ')' : '' );
		}
	}
	return $out;
}

function ip_lp_source_select( $name, $tipo, $id ) {
	$labels = array( 'generica' => 'Generica', 'tipologia' => 'Tipologia di corso', 'agevolazione' => 'Agevolazione' );
	echo '<select name="' . esc_attr( $name ) . '" class="ip-lp-src">';
	foreach ( ip_lp_source_options() as $group => $opts ) {
		list( $t ) = explode( '|', $group );
		$label     = isset( $labels[ $group ] ) ? $labels[ $group ] : 'Corso · ' . substr( $group, 6 );
		echo '<optgroup label="' . esc_attr( $label ) . '">';
		foreach ( $opts as $oid => $ol ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $t . ':' . $oid ), selected( $tipo . ':' . (int) $id, $t . ':' . $oid, false ), esc_html( $ol ) );
		}
		echo '</optgroup>';
	}
	echo '</select>';
}

function ip_lp_box( $post ) {
	wp_nonce_field( 'ip_lp', 'ip_lp_nonce' );
	$o    = ip_lp_get( $post->ID );
	$auto = ip_lp_data( $post->ID );
	$row  = function ( $label, $html, $help = '' ) {
		echo '<tr><th><label>' . esc_html( $label ) . '</label></th><td>' . $html . ( $help ? '<p class="description">' . esc_html( $help ) . '</p>' : '' ) . '</td></tr>'; // phpcs:ignore
	};
	$text = function ( $k, $ph, $area = false ) use ( $o ) {
		return $area
			? sprintf( '<textarea name="lp[%s]" rows="4" class="large-text" placeholder="%s">%s</textarea>', esc_attr( $k ), esc_attr( $ph ), esc_textarea( $o[ $k ] ) )
			: sprintf( '<input type="text" name="lp[%s]" value="%s" class="large-text" placeholder="%s">', esc_attr( $k ), esc_attr( $o[ $k ] ), esc_attr( $ph ) );
	};
	echo '<p>Lascia vuoti i campi per usare i testi automatici (mostrati in grigio): seguono i dati del corso o dell’agevolazione e si aggiornano con il sito ufficiale. Nei testi puoi usare {retta_std}, {rata_min}, {retta_min}.</p>';
	echo '<table class="form-table">';
	ob_start();
	ip_lp_source_select( 'lp[fonte]', $o['fonte_tipo'], $o['fonte_id'] );
	$row( 'Argomento', ob_get_clean(), 'Da cosa la landing prende dati e testi. Dopo il cambio, salva per vedere i nuovi testi automatici.' );
	$row( 'Occhiello', $text( 'kicker', $auto['kicker'] ) );
	$row( 'Titolo', $text( 'titolo', $auto['title'] ), 'Meglio se riprende le parole dell’annuncio.' );
	$row( 'Sottotitolo', $text( 'sottotitolo', $auto['sub'], true ) );
	$row( 'Punti di forza', $text( 'punti', implode( "\n", $auto['points'] ), true ), 'Uno per riga, 3 o 4. Solo affermazioni verificabili.' );
	$sel = '<select name="lp[modulo]">';
	foreach ( array( 'info' => 'Richiesta informazioni (nome, cognome, telefono, email)', 'callback' => 'Ti richiamiamo (solo nome e telefono)', 'cfu' => 'Prevalutazione CFU (con allegati)' ) as $k => $l ) {
		$sel .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $o['modulo'], $k, false ), esc_html( $l ) );
	}
	$row( 'Modulo in alto', $sel . '</select>', 'Meno campi = più richieste. «Ti richiamiamo» converte di più, «Richiesta informazioni» dà contatti più completi.' );
	$row( 'Titolo del modulo', $text( 'mod_titolo', 'Ricevi piano di studi e costi' ) );
	$row( 'Testo del modulo', $text( 'mod_testo', ip_opt( 'form_text' ) ) );
	$row( 'Pulsante', $text( 'pulsante', ip_opt( 'form_button' ) ) );
	$checks = '';
	foreach ( ip_lp_sections() as $k => $l ) {
		$checks .= sprintf( '<label style="display:block;margin:3px 0"><input type="checkbox" name="lp[sezioni][]" value="%s" %s> %s</label>', esc_attr( $k ), checked( in_array( $k, $o['sezioni'], true ), true, false ), esc_html( $l ) );
	}
	$row( 'Sezioni', $checks );
	$row( 'Invito prima di uscire', sprintf( '<label><input type="checkbox" name="lp[uscita]" value="1" %s> Su computer, quando il mouse esce dalla pagina, una sola volta: «Prima di andare, ti richiamiamo noi?»</label>', checked( $o['uscita'], '1', false ) ) );
	$row( 'Motori di ricerca', sprintf( '<label><input type="checkbox" name="lp[indicizza]" value="1" %s> Mostra nei risultati di Google (di solito no: la landing serve alle campagne)</label>', checked( $o['indicizza'], '1', false ) ) );
	echo '</table><p class="description">Immagine: «Immagine in evidenza» a destra; se manca si usa quella del corso o della tipologia.</p>';
}

function ip_lp_link_box( $post ) {
	if ( 'publish' !== $post->post_status ) {
		echo '<p>Pubblica la landing per avere l’indirizzo.</p>';
		return;
	}
	$url   = get_permalink( $post );
	$leads = (int) get_post_meta( $post->ID, '_lp_leads', true );
	printf( '<p><input type="text" readonly class="widefat" value="%s" onclick="this.select()"></p>', esc_attr( $url ) );
	printf( '<p><a class="button" href="%s" target="_blank">Apri la landing</a></p>', esc_url( $url ) );
	printf( '<p><strong>%d</strong> richieste ricevute da questa landing.</p>', $leads );
	echo '<p class="description">Negli annunci aggiungi i parametri UTM (es. ?utm_source=google&utm_campaign=…): ogni richiesta li registra.</p>';
}

add_action( 'save_post_ip_landing', function ( $id ) {
	if ( ! isset( $_POST['ip_lp_nonce'] ) || ! wp_verify_nonce( $_POST['ip_lp_nonce'], 'ip_lp' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$in = isset( $_POST['lp'] ) ? wp_unslash( (array) $_POST['lp'] ) : array();
	ip_lp_save( $id, $in );
} );

function ip_lp_save( $id, $in ) {
	if ( isset( $in['fonte'] ) ) {
		list( $t, $sid ) = array_pad( explode( ':', (string) $in['fonte'] ), 2, 0 );
		$in['fonte_tipo'] = in_array( $t, array( 'generica', 'corso', 'tipologia', 'agevolazione' ), true ) ? $t : 'generica';
		$in['fonte_id']   = absint( $sid );
	}
	foreach ( array( 'kicker', 'titolo', 'mod_titolo', 'mod_testo', 'pulsante' ) as $k ) {
		update_post_meta( $id, '_lp_' . $k, isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '' );
	}
	foreach ( array( 'sottotitolo', 'punti' ) as $k ) {
		update_post_meta( $id, '_lp_' . $k, isset( $in[ $k ] ) ? sanitize_textarea_field( $in[ $k ] ) : '' );
	}
	update_post_meta( $id, '_lp_fonte_tipo', $in['fonte_tipo'] ?? 'generica' );
	update_post_meta( $id, '_lp_fonte_id', (int) ( $in['fonte_id'] ?? 0 ) );
	update_post_meta( $id, '_lp_modulo', in_array( $in['modulo'] ?? '', array( 'info', 'callback', 'cfu' ), true ) ? $in['modulo'] : 'info' );
	update_post_meta( $id, '_lp_sezioni', array_values( array_intersect( (array) ( $in['sezioni'] ?? array() ), array_keys( ip_lp_sections() ) ) ) );
	update_post_meta( $id, '_lp_uscita', empty( $in['uscita'] ) ? '' : '1' );
	update_post_meta( $id, '_lp_indicizza', empty( $in['indicizza'] ) ? '' : '1' );
}

/**
 * Crea una landing pubblicata. @return int ID.
 */
function ip_lp_create( $tipo, $sid, $extra = array() ) {
	$names = array( 'generica' => 'Iscrizioni Università Marconi' );
	if ( 'corso' === $tipo ) {
		$name = ip_course_name( $sid ) . ( ip_meta( 'code', $sid ) ? ' ' . ip_meta( 'code', $sid ) : '' );
	} elseif ( 'tipologia' === $tipo ) {
		$t    = get_term( $sid, 'tipologia' );
		$name = $t && ! is_wp_error( $t ) ? $t->name : 'Tipologia';
	} elseif ( 'agevolazione' === $tipo ) {
		$name = html_entity_decode( get_the_title( $sid ), ENT_QUOTES, 'UTF-8' );
	} else {
		$name = $names['generica'];
	}
	$id = wp_insert_post( array(
		'post_type'   => 'ip_landing',
		'post_status' => 'publish',
		'post_title'  => $name,
		'post_name'   => sanitize_title( $name ),
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	ip_lp_save( $id, array_merge( array(
		'fonte_tipo' => $tipo,
		'fonte_id'   => (int) $sid,
		'modulo'     => 'info',
		'sezioni'    => array_keys( ip_lp_sections() ),
		'uscita'     => '1',
	), $extra ) );
	return (int) $id;
}

/* ---------------------------------------------------------------------
 * Generatore: Landing → Crea landing
 * ------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=ip_landing', 'Crea landing', 'Crea landing', 'edit_posts', 'ip-lp-new', 'ip_lp_generator' );
} );

function ip_lp_generator() {
	$tips = ip_tipologie();
	?>
	<div class="wrap ip-admin">
		<h1>Crea landing</h1>
		<?php if ( ! empty( $_GET['ip_msg'] ) ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( wp_unslash( $_GET['ip_msg'] ) ); ?></p></div>
		<?php endif; ?>
		<p>La landing prende titolo, dati, costi e testi dal corso, dalla tipologia o dall’agevolazione scelta e li tiene aggiornati con il sito ufficiale. Dopo la creazione puoi riscrivere ogni testo.</p>

		<h2>Una landing</h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ip_lp_new' ); ?>
			<input type="hidden" name="action" value="ip_lp_new">
			<table class="form-table">
				<tr><th>Argomento</th><td><?php ip_lp_source_select( 'fonte', 'generica', 0 ); ?></td></tr>
				<tr><th>Modulo</th><td><select name="modulo"><option value="info">Richiesta informazioni</option><option value="callback">Ti richiamiamo (solo nome e telefono)</option><option value="cfu">Prevalutazione CFU</option></select></td></tr>
			</table>
			<p><button class="button button-primary">Crea e modifica</button></p>
		</form>

		<h2>Tante landing in un colpo</h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ip_lp_bulk' ); ?>
			<input type="hidden" name="action" value="ip_lp_bulk">
			<p>
				Una landing per ogni corso della tipologia
				<select name="tip">
					<?php foreach ( $tips as $t ) : ?>
						<option value="<?php echo (int) $t->term_id; ?>"><?php echo esc_html( $t->name . ' (' . $t->count . ')' ); ?></option>
					<?php endforeach; ?>
				</select>
				<button class="button">Crea</button>
			</p>
			<p class="description">I corsi che hanno già una landing vengono saltati. Utile per avere un gruppo di annunci per corso.</p>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ip_lp_bulk' ); ?>
			<input type="hidden" name="action" value="ip_lp_bulk">
			<input type="hidden" name="all" value="agevolazioni">
			<p>Una landing per ogni agevolazione <button class="button">Crea</button></p>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ip_lp_bulk' ); ?>
			<input type="hidden" name="action" value="ip_lp_bulk">
			<input type="hidden" name="all" value="tipologie">
			<p>Una landing per ogni tipologia di corso <button class="button">Crea</button></p>
		</form>
	</div>
	<?php
}

function ip_lp_exists( $tipo, $sid ) {
	return (bool) get_posts( array(
		'post_type'      => 'ip_landing',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array( 'key' => '_lp_fonte_tipo', 'value' => $tipo ),
			array( 'key' => '_lp_fonte_id', 'value' => (int) $sid ),
		),
	) );
}

add_action( 'admin_post_ip_lp_new', function () {
	check_admin_referer( 'ip_lp_new' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	list( $t, $sid ) = array_pad( explode( ':', sanitize_text_field( wp_unslash( $_POST['fonte'] ?? 'generica:0' ) ) ), 2, 0 );
	$id = ip_lp_create( $t, absint( $sid ), array( 'modulo' => sanitize_key( $_POST['modulo'] ?? 'info' ) ) );
	wp_safe_redirect( $id ? get_edit_post_link( $id, 'url' ) : admin_url( 'edit.php?post_type=ip_landing' ) );
	exit;
} );

add_action( 'admin_post_ip_lp_bulk', function () {
	check_admin_referer( 'ip_lp_bulk' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	$todo = array();
	$all  = sanitize_key( $_POST['all'] ?? '' );
	if ( 'agevolazioni' === $all ) {
		foreach ( get_posts( array( 'post_type' => 'agevolazione', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $sid ) {
			$todo[] = array( 'agevolazione', $sid );
		}
	} elseif ( 'tipologie' === $all ) {
		foreach ( ip_tipologie() as $t ) {
			$todo[] = array( 'tipologia', $t->term_id );
		}
	} else {
		$ids = get_posts( array( 'post_type' => 'corso', 'posts_per_page' => -1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'tipologia', 'terms' => absint( $_POST['tip'] ?? 0 ) ) ) ) );
		foreach ( $ids as $sid ) {
			$todo[] = array( 'corso', $sid );
		}
	}
	$n = 0;
	foreach ( $todo as $x ) {
		if ( ! ip_lp_exists( $x[0], $x[1] ) && ip_lp_create( $x[0], $x[1] ) ) {
			$n++;
		}
	}
	wp_safe_redirect( add_query_arg( 'ip_msg', rawurlencode( $n . ' landing create.' ), admin_url( 'edit.php?post_type=ip_landing&page=ip-lp-new' ) ) );
	exit;
} );

// «Crea landing» direttamente dall'elenco dei corsi.
add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( in_array( $post->post_type, array( 'corso', 'agevolazione' ), true ) && current_user_can( 'edit_posts' ) ) {
		$url                  = wp_nonce_url( admin_url( 'admin-post.php?action=ip_lp_from&id=' . $post->ID ), 'ip_lp_from' );
		$actions['ip_landing'] = '<a href="' . esc_url( $url ) . '">Crea landing</a>';
	}
	return $actions;
}, 10, 2 );

add_action( 'admin_post_ip_lp_from', function () {
	check_admin_referer( 'ip_lp_from' );
	$sid = absint( $_GET['id'] ?? 0 );
	if ( ! current_user_can( 'edit_posts' ) || ! in_array( get_post_type( $sid ), array( 'corso', 'agevolazione' ), true ) ) {
		wp_die( 'Non autorizzato.' );
	}
	$id = ip_lp_create( get_post_type( $sid ), $sid );
	wp_safe_redirect( $id ? get_edit_post_link( $id, 'url' ) : admin_url( 'edit.php?post_type=ip_landing' ) );
	exit;
} );

// Elenco landing: argomento, indirizzo, richieste.
add_filter( 'manage_ip_landing_posts_columns', function ( $c ) {
	return array(
		'cb'       => $c['cb'],
		'title'    => 'Landing',
		'lp_src'   => 'Argomento',
		'lp_url'   => 'Indirizzo',
		'lp_leads' => 'Richieste',
	);
} );
add_action( 'manage_ip_landing_posts_custom_column', function ( $col, $id ) {
	$o = ip_lp_get( $id );
	if ( 'lp_src' === $col ) {
		$labels = array( 'generica' => 'Generica', 'corso' => 'Corso', 'tipologia' => 'Tipologia', 'agevolazione' => 'Agevolazione' );
		$d      = ip_lp_data( $id );
		echo esc_html( $labels[ $o['fonte_tipo'] ] ?? '' ) . ( $d['name'] ? ': ' . esc_html( $d['name'] ) : '' );
		if ( 'generica' !== $o['fonte_tipo'] && 'generica' === $d['type'] ) {
			echo '<br><strong style="color:#b42318">Argomento non più disponibile: mostra la versione generica</strong>';
		}
	} elseif ( 'lp_url' === $col && 'publish' === get_post_status( $id ) ) {
		printf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( get_permalink( $id ) ), esc_html( wp_parse_url( get_permalink( $id ), PHP_URL_PATH ) ) );
	} elseif ( 'lp_leads' === $col ) {
		echo (int) get_post_meta( $id, '_lp_leads', true );
	}
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Impaginazione
 * ------------------------------------------------------------------- */

/**
 * Titolo diviso per l'impaginazione: «Laurea triennale in» sopra, il nome
 * grande, la classe in evidenza.
 *
 * @return array pre, main, code.
 */
function ip_lp_title_parts( $d ) {
	$t    = trim( $d['title'] );
	$code = '';
	$pre  = '';
	if ( preg_match( '/^(.*\S)\s*\(([A-Z0-9][A-Z0-9\/ -]{0,14})\)$/u', $t, $m ) ) {
		$t    = $m[1];
		$code = $m[2];
	}
	if ( 'corso' === $d['type'] && '' === trim( (string) $d['o']['titolo'] ) && preg_match( '/^(.{6,60}?\s(?:in|di))\s(.+)$/u', $t, $m ) ) {
		$pre = $m[1];
		$t   = $m[2];
	}
	return array( $pre, $t, $code );
}

/**
 * Scheda tecnica sotto il titolo: pochi dati veri, ben leggibili.
 *
 * @return array label => valore.
 */
function ip_lp_spec( $d ) {
	$f = $d['facts'];
	if ( 'corso' === $d['type'] ) {
		$out = array_filter( array(
			'Classe' => $f['Classe'] ?? '',
			'CFU'    => $f['CFU'] ?? '',
			'Durata' => $f['Durata'] ?? '',
		) );
		if ( ! empty( $f['Costo'] ) && mb_strlen( $f['Costo'] ) < 18 ) {
			$out['Costo'] = $f['Costo'];
		} elseif ( count( ip_rows( 'exam_sites' ) ) ) {
			$out['Sedi d’esame'] = count( ip_rows( 'exam_sites' ) );
		}
		return $out;
	}
	if ( 'agevolazione' === $d['type'] ) {
		return array_filter( array(
			'Retta annua' => $f['Retta annua'] ?? '',
			'Al mese'     => $f['Rata mensile'] ?? '',
		) );
	}
	if ( 'tipologia' === $d['type'] ) {
		$fees = ip_fees();
		return array_filter( array(
			'Corsi'        => count( $d['courses'] ),
			'Sedi d’esame' => count( ip_rows( 'exam_sites' ) ),
			'Da'           => $d['price'] && $fees['min_rata'] ? ip_eur( $fees['min_rata'] ) . '/mese' : '',
		) );
	}
	return array();
}

/**
 * Cifre dell'offerta, tutte lette dai dati del sito.
 */
function ip_lp_figures() {
	$fees = ip_fees();
	return array_filter( array(
		array( (int) wp_count_posts( 'corso' )->publish, 'corsi online tra lauree, master e formazione' ),
		array( count( ip_rows( 'exam_sites' ) ), 'sedi d’esame in tutta Italia, scegli la più vicina' ),
		array( (int) wp_count_posts( 'agevolazione' )->publish, 'agevolazioni e convenzioni sulla retta' ),
		$fees['min_rata'] ? array( ip_eur( $fees['min_rata'] ), 'al mese con le agevolazioni, senza interessi' ) : null,
	), function ( $x ) {
		return $x && $x[0];
	} );
}

// Stile della landing: solo su queste pagine, incorporato come il resto.
add_action( 'wp_head', function () {
	if ( ! is_singular( 'ip_landing' ) ) {
		return;
	}
	if ( ! ip_opt( 'inline_css' ) ) {
		printf( '<link rel="stylesheet" href="%s">', esc_url( IP_URI . '/assets/landing.css?ver=' . IP_VERSION ) );
		return;
	}
	$css = get_transient( 'ip_lpcss_' . IP_VERSION );
	if ( false === $css || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
		$css = (string) file_get_contents( IP_DIR . '/assets/landing.css' );
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = preg_replace( '/\s+/', ' ', $css );
		$css = str_replace( array( ' {', '{ ', ' }', '; ', ': ', ', ' ), array( '{', '{', '}', ';', ':', ',' ), $css );
		set_transient( 'ip_lpcss_' . IP_VERSION, $css, WEEK_IN_SECONDS );
	}
	$css = str_replace( 'url("fonts/', 'url("' . IP_URI . '/assets/fonts/', $css );
	echo '<style id="ip-lp-css">' . $css . '</style>' . "\n"; // phpcs:ignore
}, 8 );

/* ---------------------------------------------------------------------
 * Mappa delle sedi d'esame
 * ------------------------------------------------------------------- */

function ip_lp_norm_city( $s ) {
	$s = mb_strtolower( remove_accents( html_entity_decode( (string) $s, ENT_QUOTES, 'UTF-8' ) ) );
	return trim( preg_replace( '/[^a-z]+/', ' ', str_replace( array( '’', "'" ), ' ', $s ) ) );
}

/**
 * Sedi raggruppate per regione, con la posizione sulla mappa.
 *
 * @return array regions: nome => sedi [citta, indirizzo, x, y]; svg data.
 */
function ip_lp_map_data() {
	static $out = null;
	if ( null !== $out ) {
		return $out;
	}
	$map    = ip_import_data( 'italia' );
	$coords = array();
	foreach ( ip_import_data( 'coordinate' ) as $c => $ll ) {
		$coords[ ip_lp_norm_city( $c ) ] = $ll;
	}
	$out = array( 'map' => $map, 'regions' => array(), 'count' => 0 );
	if ( empty( $map['regions'] ) ) {
		return $out;
	}
	$xy = function ( $ll ) use ( $map ) {
		return array( round( ( $ll[1] - $map['lon0'] ) * $map['cx'] * $map['k'], 1 ), round( ( $map['lat0'] - $ll[0] ) * $map['k'], 1 ) );
	};
	foreach ( ip_rows( 'exam_sites' ) as $r ) {
		$reg  = trim( $r['regione'] ) ? trim( $r['regione'] ) : 'Altre sedi';
		$city = trim( $r['citta'] );
		$pos  = null;
		// «Venezia-Mestre», «Reggio nell’Emilia»…: si prova il nome intero, poi le sue parti.
		foreach ( array_merge( array( $city ), preg_split( '/\s*[-\/]\s*/u', $city ) ) as $try ) {
			$k = ip_lp_norm_city( $try );
			if ( isset( $coords[ $k ] ) ) {
				$pos = $xy( $coords[ $k ] );
				break;
			}
		}
		if ( ! $pos && isset( $map['centers'][ $reg ] ) ) {
			$pos = $map['centers'][ $reg ];
		}
		$out['regions'][ $reg ][] = array( 'citta' => $city, 'indirizzo' => $r['indirizzo'], 'pos' => $pos );
		$out['count']++;
	}
	ksort( $out['regions'] );
	return $out;
}

function ip_lp_map_svg( $m ) {
	$map = $m['map'];
	$svg = sprintf( '<svg class="lp-map-svg" viewBox="0 0 %s %s" role="img" aria-label="Mappa delle sedi d’esame in Italia">', esc_attr( $map['w'] ), esc_attr( $map['h'] ) );
	foreach ( $map['regions'] as $name => $d ) {
		$svg .= sprintf( '<path d="%s" data-region="%s" class="%s"><title>%s</title></path>', esc_attr( $d ), esc_attr( $name ), isset( $m['regions'][ $name ] ) ? 'on' : '', esc_html( $name . ( isset( $m['regions'][ $name ] ) ? ' · ' . count( $m['regions'][ $name ] ) . ' sedi' : '' ) ) );
	}
	foreach ( $m['regions'] as $name => $sites ) {
		foreach ( $sites as $s ) {
			if ( $s['pos'] ) {
				$svg .= sprintf( '<circle cx="%s" cy="%s" r="5" data-region="%s"><title>%s</title></circle>', esc_attr( $s['pos'][0] ), esc_attr( $s['pos'][1] ), esc_attr( $name ), esc_html( $s['citta'] . ' – ' . $s['indirizzo'] ) );
			}
		}
	}
	return $svg . '</svg>';
}

/* ---------------------------------------------------------------------
 * Scheda del corso divisa in schede (obiettivi, sbocchi, accesso…)
 * ------------------------------------------------------------------- */

/**
 * @return array[] title, html, chips (voci brevi di un elenco, es. sbocchi).
 */
function ip_lp_tabs( $html ) {
	$parts = preg_split( '#<h2[^>]*>(.*?)</h2>#su', (string) $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$intro = trim( array_shift( $parts ) );
	$tabs  = array();
	if ( '' !== trim( wp_strip_all_tags( $intro ) ) ) {
		$tabs[] = array( 'title' => 'Presentazione', 'html' => $intro );
	}
	for ( $i = 0; $i + 1 < count( $parts ); $i += 2 ) {
		$title = trim( wp_strip_all_tags( $parts[ $i ] ) );
		$body  = trim( $parts[ $i + 1 ] );
		if ( '' === $title || '' === trim( wp_strip_all_tags( $body ) ) ) {
			continue;
		}
		$tabs[] = array( 'title' => $title, 'html' => $body );
	}
	foreach ( $tabs as &$t ) {
		$t['long'] = mb_strlen( wp_strip_all_tags( $t['html'] ) ) > 2200;
		$t['key']  = sanitize_title( $t['title'] );
	}
	unset( $t );
	return $tabs;
}

/**
 * Chi ti segue: le persone e la sede dell'agenzia.
 */
function ip_lp_team() {
	return array(
		'title'  => ip_opt( 'lp_team_title' ),
		'text'   => ip_text( 'lp_team_text' ),
		'photo'  => (int) ip_opt( 'lp_team_photo' ),
		'people' => ip_rows( 'lp_team' ),
	);
}
