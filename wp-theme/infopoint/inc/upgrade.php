<?php
/**
 * Aggiornamenti dei dati quando cambia la versione del tema: porta i siti
 * già installati alle novità senza toccare ciò che è stato modificato a mano.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'ip_upgrade' );
add_action( 'ip_sync_check', 'ip_upgrade', 1 );

function ip_upgrade() {
	$v = (string) get_option( 'ip_version', '' );
	if ( version_compare( $v, '2.4.0', '<' ) ) {
		ip_upgrade_240();
	}
	if ( version_compare( $v, '2.6.0', '<' ) ) {
		// Nuove sezioni delle landing (mappa delle sedi, chi ti segue): attive anche su quelle già create.
		foreach ( get_posts( array( 'post_type' => 'ip_landing', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
			$sez = get_post_meta( $id, '_lp_sezioni', true );
			if ( is_array( $sez ) ) {
				update_post_meta( $id, '_lp_sezioni', array_values( array_unique( array_merge( $sez, array( 'sedi', 'team' ) ) ) ) );
			}
		}
	}
	if ( version_compare( $v, '2.7.0', '<' ) ) {
		ip_upgrade_270();
	}
	if ( version_compare( $v, '2.7.1', '<' ) && function_exists( 'ip_sync_block' ) ) {
		// La pagina dei trasferimenti, tolta su richiesta dell'Ateneo, non va più importata.
		ip_sync_block( 'https://www.unimarconi.it/immatricolazione-con-trasferimento-da-altro-ateneo/' );
	}
	if ( version_compare( $v, '2.7.2', '<' ) ) {
		ip_upgrade_272();
	}
	if ( version_compare( $v, '2.7.4', '<' ) ) {
		ip_upgrade_274();
	}
	if ( version_compare( $v, '2.7.5', '<' ) ) {
		// Hero della home: titolo con evidenziazione e sottotitolo breve, se mai modificati.
		$o   = get_option( 'ip_settings', array() );
		$old = array(
			'hero_title' => 'Iscriviti all’Università Marconi con un orientatore al tuo fianco',
			'hero_text'  => 'Lauree triennali, magistrali e master online con esami in presenza. Ti aiutiamo a scegliere il corso, verifichiamo gratis i crediti già maturati e seguiamo con te l’immatricolazione.',
		);
		if ( is_array( $o ) ) {
			foreach ( $old as $k => $t ) {
				if ( isset( $o[ $k ] ) && trim( $o[ $k ] ) === $t ) {
					unset( $o[ $k ] );
				}
			}
			update_option( 'ip_settings', $o );
		}
	}
	if ( version_compare( $v, '3.0.0', '<' ) ) {
		// Piè di pagina nel verde scuro del marchio, se era ancora il grigio predefinito.
		$o = get_option( 'ip_settings', array() );
		if ( is_array( $o ) && isset( $o['color_dark'] ) && in_array( strtolower( $o['color_dark'] ), array( '#373737', '' ), true ) ) {
			unset( $o['color_dark'] );
			update_option( 'ip_settings', $o );
		}
	}
	if ( version_compare( $v, '2.9.9', '<' ) ) {
		ip_upgrade_299();
	}
	if ( version_compare( $v, '2.8.7', '<' ) ) {
		// Barra di trasparenza in cima tolta su richiesta: l'identità resta in testata, piè di pagina e Chi siamo.
		$o = get_option( 'ip_settings', array() );
		$o = is_array( $o ) ? $o : array();
		$o['disclosure_bar'] = '';
		update_option( 'ip_settings', $o );
	}
	if ( version_compare( $v, '2.7.9', '<' ) ) {
		ip_upgrade_279();
		// Avviso in testata: tolta la frase «Non è l’Università: l’iscrizione si completa…».
		$o = get_option( 'ip_settings', array() );
		if ( is_array( $o ) && ! empty( $o['disclosure'] ) ) {
			$o['disclosure'] = trim( preg_replace( '/\s*Non (?:è|siamo) l[’\']Università:[^.]*\./u', '', $o['disclosure'] ) );
			update_option( 'ip_settings', $o );
		}
	}
	if ( IP_VERSION !== $v ) {
		update_option( 'ip_version', IP_VERSION );
		flush_rewrite_rules(); // Nuovi indirizzi, es. /lp/ delle landing.
	}
}

/**
 * 2.4: nessuna cifra scritta a mano. Importi dai dati ufficiali, pagine con
 * il testo ufficiale incorporato, piani di studio collegati alla fonte.
 */
function ip_upgrade_240() {
	// Impostazioni rimaste ai valori predefiniti: ora sono automatiche.
	$o = get_option( 'ip_settings', null );
	if ( is_array( $o ) ) {
		if ( isset( $o['price_from'] ) && '135' === trim( $o['price_from'] ) ) {
			$o['price_from'] = '';
		}
		if ( isset( $o['retta_std'] ) && '€ 2.760/anno (€ 230/mese)' === trim( $o['retta_std'] ) ) {
			$o['retta_std'] = '';
		}
		if ( ! empty( $o['faq'] ) && is_array( $o['faq'] ) ) {
			$new = array();
			foreach ( ip_default_faq() as $r ) {
				$new[ $r['domanda'] ] = $r['risposta'];
			}
			$old = array(
				'La retta standard dei corsi di laurea è di € 2.760 l’anno (€ 230 al mese), comprensiva di diritti di segreteria e tasse d’esame; per i curriculum in lingua inglese è di € 3.000. Con le agevolazioni si parte da € 135 al mese. Tassa regionale e tassa di laurea sono a parte.',
				'Videolezioni e materiali sono disponibili sulla piattaforma 24 ore su 24 e non c’è obbligo di frequenza. È possibile anche l’iscrizione a tempo parziale, con retta al 50%.',
			);
			foreach ( $o['faq'] as $i => $r ) {
				if ( isset( $r['risposta'], $new[ $r['domanda'] ] ) && in_array( $r['risposta'], $old, true ) ) {
					$o['faq'][ $i ]['risposta'] = $new[ $r['domanda'] ];
				}
			}
		}
		update_option( 'ip_settings', $o );
	}

	// Pagine del tema mai modificate: si sostituiscono con la nuova versione.
	$pristine = array(
		'corsi-singoli'              => '8de5607f27ebddb3fa62bbb667850d21',
		'riconoscimento-cfu'         => '039b1eba26186cb23288a72323c7ea87',
		'doppia-laurea'              => '81a321215e93eb0c6e06c4b5e94783f9',
		'percorsi-abilitanti'        => '627bc666b32e3ba6f033fd02b4c1e94d',
		'area-studenti'              => '7d8b2a0a2c500852f958ad4d45a24efb',
		'master'                     => '88f75ecc878635909de033b77e73ea90',
	);
	$excerpts = array(
		'master'                     => '057cc367b93e0de5250f67951f68e413',
		'area-studenti'              => '1bbe0a63cb3e8574b14afdb34fd2fe38',
		'convenzioni-e-agevolazioni' => '9b6eb1f834e5c15b29a83dd12a334bb5',
	);
	$defs = ip_import_pages();
	foreach ( array_unique( array_merge( array_keys( $pristine ), array_keys( $excerpts ) ) ) as $slug ) {
		$pg = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'posts_per_page' => 1 ) );
		if ( ! $pg ) {
			continue;
		}
		$pg = $pg[0];
		$up = array( 'ID' => $pg->ID );
		if ( isset( $pristine[ $slug ] ) && md5( ip_sync_norm( $pg->post_content ) ) === $pristine[ $slug ] ) {
			$up['post_content'] = wp_slash( $defs[ $slug ]['content'] );
		}
		if ( isset( $excerpts[ $slug ] ) && md5( ip_sync_norm( $pg->post_excerpt ) ) === $excerpts[ $slug ] ) {
			$up['post_excerpt'] = wp_slash( $defs[ $slug ]['excerpt'] );
		}
		if ( count( $up ) > 1 ) {
			wp_update_post( $up );
		}
	}

	// Pagine ufficiali aggiunte in questa versione (es. costi dei corsi singoli).
	$parent = get_page_by_path( 'iscriversi' );
	foreach ( ip_import_data( 'pagine' ) as $slug => $p ) {
		if ( function_exists( 'ip_sync_blocked' ) && in_array( trailingslashit( $p['source'] ), ip_sync_blocked(), true ) ) {
			continue;
		}
		if ( $parent && ! ip_official_page( $p['source'] ) && ! get_page_by_path( 'iscriversi/' . $slug ) ) {
			$id = wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $slug,
				'post_parent'  => $parent->ID,
				'post_content' => wp_slash( $p['content'] ),
				'menu_order'   => 50,
			) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_page_template', 'page-templates/sidebar-form.php' );
				update_post_meta( $id, '_ip_fonte', $p['source'] );
			}
		}
	}

	// Pagine ufficiali ora incorporate: fuori dai motori di ricerca.
	foreach ( ip_embedded_sources() as $url ) {
		$id = ip_official_page( $url );
		if ( $id ) {
			update_post_meta( $id, '_ip_seo_noindex', '1' );
		}
	}

	// Piani di studio presi dal sito ufficiale: collegati alla fonte, così
	// quelli tolti dall'Ateneo possono essere ritirati.
	$slugs = array();
	foreach ( ip_import_data( 'corsi' ) as $c ) {
		foreach ( (array) ( $c['curricula'] ?? array() ) as $cu ) {
			$slugs[] = $cu['slug'];
		}
	}
	foreach ( get_posts( array( 'post_type' => 'curriculum', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $cu ) {
		if ( in_array( $cu->post_name, $slugs, true ) && ! get_post_meta( $cu->ID, '_ip_fonte', true ) ) {
			update_post_meta( $cu->ID, '_ip_fonte', $cu->post_name );
		}
	}

	// Agevolazioni importate: collegate alla sezione ufficiale corrispondente.
	list( $map ) = ip_sync_agev_posts();
	foreach ( $map as $key => $id ) {
		if ( ! get_post_meta( $id, '_ip_fonte', true ) && $key !== get_post_field( 'post_name', $id ) ) {
			update_post_meta( $id, '_ip_fonte', $key );
		}
	}
}

/**
 * 2.7: revisione dell'Ateneo.
 * - Il polo si chiama per nome e territorio («Infopoint Lanciano»): è un
 *   «centro di orientamento accreditato», non un'«agenzia partner»; la società
 *   che lo gestisce resta indicata ma non lo sostituisce.
 * - «Come immatricolarsi» mostra solo i requisiti di accesso; niente rimandi
 *   alla doppia iscrizione fuori dalla sua pagina.
 * - Niente pagina sui trasferimenti da altro ateneo.
 */
function ip_upgrade_270() {
	$role_old = 'agenzia partner dell’Università degli Studi Guglielmo Marconi per l’orientamento e l’assistenza alle iscrizioni';
	$role_new = 'centro di orientamento accreditato dall’Università degli Studi Guglielmo Marconi';
	$o        = get_option( 'ip_settings', null );
	if ( is_array( $o ) ) {
		// Testi rimasti ai valori predefiniti: si torna ai nuovi predefiniti.
		$old = array(
			'disclaimer'   => '{brand} è un servizio di {azienda}, agenzia partner dell’Università degli Studi Guglielmo Marconi per l’orientamento e l’assistenza alle iscrizioni. Questo non è il sito ufficiale dell’Ateneo: il sito ufficiale è www.unimarconi.it.',
			'disclosure'   => '{brand} è un servizio di {azienda} (P.IVA {piva}), {ruolo}. Non siamo l’Università: l’iscrizione si completa sui canali ufficiali dell’Ateneo e le tasse si pagano direttamente all’Università.',
			'form_notice'  => 'Stai contattando {azienda}, agenzia partner. La consulenza è gratuita; i tuoi dati non vengono inviati all’Università senza il tuo consenso.',
			'lp_team_text' => 'Siamo {azienda}, {ruolo}. Ti seguiamo al telefono, su WhatsApp o di persona in sede, dalla scelta del corso all’immatricolazione. La consulenza è gratuita.',
			'partner_role' => $role_old,
		);
		foreach ( $old as $k => $v ) {
			if ( isset( $o[ $k ] ) && trim( $o[ $k ] ) === $v ) {
				unset( $o[ $k ] );
			}
		}
		// Testi personalizzati: si cambia solo la dicitura.
		foreach ( $o as $k => $v ) {
			if ( is_string( $v ) ) {
				$o[ $k ] = str_replace( array( $role_old, 'agenzia partner', 'Agenzia partner' ), array( $role_new, 'centro di orientamento accreditato', 'Centro di orientamento accreditato' ), $v );
			}
		}
		// Nome del polo dei dati di esempio.
		if ( isset( $o['brand'] ) && 'ZDM Orientamento' === $o['brand'] ) {
			$o['brand'] = 'Infopoint Lanciano';
			if ( empty( $o['city'] ) || 'Pescara' === $o['city'] ) {
				$o['city'] = 'Lanciano';
			}
		}
		if ( ! empty( $o['faq'] ) && is_array( $o['faq'] ) ) {
			foreach ( $o['faq'] as $i => $r ) {
				if ( isset( $r['risposta'] ) && false !== strpos( $r['risposta'], 'si parte da {rata_min} al mese. Tassa regionale' ) ) {
					$o['faq'][ $i ]['risposta'] = str_replace( 'si parte da {rata_min} al mese. Tassa regionale', 'si parte da {rata_min} al mese. {scadenze} Tassa regionale', $r['risposta'] );
				}
			}
		}
		update_option( 'ip_settings', $o );
	}

	$data = ip_import_data( 'pagine' );

	// «Come immatricolarsi»: solo i requisiti di accesso, anche dopo gli aggiornamenti.
	foreach ( array( 'immatricolazione', 'iscrizione-master' ) as $slug ) {
		if ( empty( $data[ $slug ] ) ) {
			continue;
		}
		$id = ip_official_page( $data[ $slug ]['source'] );
		if ( ! $id ) {
			continue;
		}
		$up = array( 'ID' => $id );
		if ( ! empty( $data[ $slug ]['sezioni'] ) ) {
			update_post_meta( $id, '_ip_sezioni', $data[ $slug ]['sezioni'] );
			$up['post_title'] = $data[ $slug ]['title'];
		}
		if ( 'no' !== get_post_meta( $id, '_ip_sync', true ) ) {
			$up['post_content'] = wp_slash( $data[ $slug ]['content'] );
		}
		wp_update_post( $up );
	}

	// Trasferimento da altro ateneo: pagina tolta dal sito.
	$gone = array();
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => '_ip_fonte', 'meta_value' => 'https://www.unimarconi.it/immatricolazione-con-trasferimento-da-altro-ateneo/' ) ) as $p ) {
		wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'draft' ) );
		$gone[] = $p->ID;
	}
	$immatricolazione = ! empty( $data['immatricolazione'] ) ? ip_official_page( $data['immatricolazione']['source'] ) : 0;
	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'any' ) ) as $item ) {
			if ( 'post_type' === $item->type && ( in_array( (int) $item->object_id, $gone, true ) || ( $immatricolazione && (int) $item->object_id === $immatricolazione ) ) ) {
				wp_delete_post( $item->ID, true );
			}
		}
	}

	// Pagine del tema: «Iscriversi» e «Requisiti di accesso».
	$defs = ip_import_pages();
	$pg   = get_posts( array( 'post_type' => 'page', 'name' => 'iscriversi', 'post_status' => 'any', 'posts_per_page' => 1 ) );
	if ( $pg ) {
		$c = $pg[0]->post_content;
		$c = preg_replace( '#<!-- wp:list-item -->\s*<li><a href="[^"]*trasferimento-da-altro-ateneo/?">[^<]*</a></li>\s*<!-- /wp:list-item -->\s*#u', '', $c );
		$c = preg_replace( '#<a href="[^"]*/iscriversi/immatricolazione/?">Come immatricolarsi</a>#u', '<a href="/area-studenti/">Requisiti di accesso</a>', $c );
		$up = array( 'ID' => $pg[0]->ID, 'post_content' => wp_slash( $c ) );
		if ( 'f6e4c380ebbc92e24cab7174be759f8b' === md5( ip_sync_norm( $pg[0]->post_excerpt ) ) ) {
			$up['post_excerpt'] = wp_slash( $defs['iscriversi']['excerpt'] );
		}
		wp_update_post( $up );
	}
	$pg = get_posts( array( 'post_type' => 'page', 'name' => 'area-studenti', 'post_status' => 'any', 'posts_per_page' => 1 ) );
	if ( $pg ) {
		$p  = $pg[0];
		$up = array( 'ID' => $p->ID );
		if ( '28b62ec1178d3cbc36c26c340792168a' === md5( ip_sync_norm( $p->post_content ) ) ) {
			$up['post_content'] = wp_slash( $defs['area-studenti']['content'] );
		} else {
			$up['post_content'] = wp_slash( preg_replace( '#\s*Leggi anche come funziona il <a href="[^"]*trasferimento-da-altro-ateneo/?">[^<]*</a>\.#u', '', $p->post_content ) );
		}
		if ( 'Requisiti e iscrizione' === $p->post_title ) {
			$up['post_title'] = $defs['area-studenti']['title'];
		}
		if ( 'ca0b1ccc3b7f87997d23f595cb874a4d' === md5( ip_sync_norm( $p->post_excerpt ) ) ) {
			$up['post_excerpt'] = wp_slash( $defs['area-studenti']['excerpt'] );
		}
		wp_update_post( $up );
	}
}

/**
 * 2.7.2: dati legali e di contatto dell'Infopoint Lanciano (ZDM S.r.l.).
 * Si compilano solo i campi vuoti o rimasti ai valori di esempio.
 */
function ip_upgrade_272() {
	$o = get_option( 'ip_settings', array() );
	$o = is_array( $o ) ? $o : array();
	$demo = array( '', '085 000 0000', '0872 000 000', '+39 333 000 0000', 'info@esempio.it', "Via Esempio 1\n65100 Pescara", "Via Esempio 1\n66034 Lanciano (CH)", "Lun–Ven 9:00–13:00 / 15:00–19:00\nSabato su appuntamento" );
	$set  = array(
		'company_name'  => 'ZDM S.r.l.',
		'vat'           => '02365510698',
		'legal_address' => "Via Venezia, 10\nPescara (PE)",
		'address'       => "Via Salita della Posta, 13\n66034 Lanciano (CH)",
		'phone1'        => '0872 676609',
		'phone1_label'  => 'Telefono',
		'phone2'        => '375 637 3330',
		'phone2_label'  => 'Cellulare',
		'pec'           => 'ZDMSRL@pec-legal.com',
	);
	foreach ( $set as $k => $v ) {
		$cur = isset( $o[ $k ] ) ? trim( (string) $o[ $k ] ) : '';
		if ( in_array( $cur, $demo, true ) || in_array( $cur, array( 'Orientamento', 'Segreteria' ), true ) ) {
			$o[ $k ] = $v;
		}
	}
	// Orari di esempio: meglio nessun orario che uno sbagliato.
	if ( isset( $o['hours'] ) && in_array( trim( $o['hours'] ), $demo, true ) ) {
		$o['hours'] = '';
	} elseif ( ! isset( $o['hours'] ) ) {
		$o['hours'] = '';
	}
	update_option( 'ip_settings', $o );
}

/**
 * 2.7.3–2.7.4: WhatsApp, email e orari della sede; consensi brevi e niente
 * avviso nei moduli; pagine «Informativa privacy» e «Cookie policy».
 */
function ip_upgrade_274() {
	$o = get_option( 'ip_settings', array() );
	$o = is_array( $o ) ? $o : array();
	$set = array(
		'whatsapp' => '+39 375 637 3330',
		'email'    => 'info@unimarconilanciano.it',
		'lead_to'  => 'info@unimarconilanciano.it',
		'hours'    => "Lunedì–venerdì\n10:00–13:00 e 15:00–18:00",
	);
	foreach ( $set as $k => $v ) {
		$cur = isset( $o[ $k ] ) ? trim( (string) $o[ $k ] ) : '';
		if ( in_array( $cur, array( '', '+39 333 000 0000', 'info@esempio.it' ), true ) ) {
			$o[ $k ] = $v;
		}
	}
	// Consensi: sostituiti solo se ancora quelli predefiniti.
	$old = array(
		'privacy_text' => array( 'Ho letto l’informativa privacy e acconsento al trattamento dei miei dati da parte di {azienda} per ricevere le informazioni richieste.', 'Ho letto l’informativa privacy: i miei dati saranno trattati da {azienda} per rispondere alla mia richiesta.' ),
		'mkt_text'     => array( 'Acconsento a ricevere da {azienda} aggiornamenti su corsi, agevolazioni e scadenze via email, telefono o WhatsApp (facoltativo).' ),
		'form_notice'  => array( 'Stai contattando {brand}, gestito da {azienda}. La consulenza è gratuita; i tuoi dati non vengono inviati all’Università senza il tuo consenso.', 'Stai contattando {azienda}, agenzia partner. La consulenza è gratuita; i tuoi dati non vengono inviati all’Università senza il tuo consenso.' ),
	);
	// Moduli essenziali: consensi brevi (il dettaglio è nell'informativa), nessun avviso.
	foreach ( $old as $k => $texts ) {
		if ( isset( $o[ $k ] ) && in_array( trim( $o[ $k ] ), $texts, true ) ) {
			unset( $o[ $k ] ); // Torna al testo predefinito.
		}
	}
	update_option( 'ip_settings', $o );
	ip_legal_pages();
}

/**
 * 2.7.9: nessun rimando al sito ufficiale. Via la frase sul sito ufficiale
 * dal piè di pagina e i «aggiornato dal sito ufficiale» dalle pagine.
 */
function ip_upgrade_279() {
	$o = get_option( 'ip_settings', array() );
	if ( is_array( $o ) && ! empty( $o['disclaimer'] ) ) {
		$o['disclaimer'] = trim( preg_replace( '/\s*Questo non è il sito ufficiale.*?unimarconi\.it\.?/us', ' Marchi e denominazioni dell’Università appartengono ai rispettivi titolari.', $o['disclaimer'] ) );
		update_option( 'ip_settings', $o );
	}
	$swap = array(
		', aggiornata dal sito ufficiale.'      => '.',
		', presa dal sito ufficiale dell’Ateneo.' => '.',
		', aggiornate dal sito ufficiale.'      => '.',
	);
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $p ) {
		$c = strtr( $p->post_content, $swap );
		$e = strtr( $p->post_excerpt, $swap );
		if ( $c !== $p->post_content || $e !== $p->post_excerpt ) {
			wp_update_post( wp_slash( array( 'ID' => $p->ID, 'post_content' => $c, 'post_excerpt' => $e ) ) );
		}
	}
}

/**
 * 2.9.9: niente coordinate bancarie dell'Ateneo (IBAN, intestazione,
 * causale). Le frasi entrano tra quelle da non importare e i testi già
 * presenti vengono ripuliti.
 */
function ip_upgrade_299() {
	$o = get_option( 'ip_settings', array() );
	$o = is_array( $o ) ? $o : array();
	$cur  = isset( $o['sync_drop'] ) ? (string) $o['sync_drop'] : 'Vuoi iscriverti a due corsi';
	$have = array_map( 'mb_strtolower', array_map( 'trim', explode( "\n", $cur ) ) );
	foreach ( array( 'IBAN:', 'effettuato tramite bonifico bancario', 'bonifico bancario in favore di', 'causale di versamento', 'Per chi deve immatricolarsi: nome e cognome', 'matricola (obbligatoria)', '=ATTENZIONE', 'Hai bisogno di chiarimenti o supporto' ) as $ph ) {
		if ( ! in_array( mb_strtolower( $ph ), $have, true ) ) {
			$cur .= "\n" . $ph;
		}
	}
	$o['sync_drop'] = trim( $cur );
	update_option( 'ip_settings', $o );
	ip_opt( 'ip_flush' );
	if ( ! function_exists( 'ip_src_filter' ) ) {
		return;
	}
	global $wpdb;
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('page','corso','curriculum') AND post_status <> 'auto-draft' AND ( post_content LIKE '%IBAN:%' OR post_content LIKE '%ausale%' OR post_content LIKE '%bonifico bancario%' OR post_content LIKE '%ATTENZIONE%' OR post_content LIKE '%chiarimenti o supporto%' )" );
	foreach ( $ids as $id ) {
		$old = get_post_field( 'post_content', $id );
		$new = ip_src_filter( $old );
		if ( $new !== trim( $old ) && '' !== trim( $new ) ) {
			$wpdb->update( $wpdb->posts, array( 'post_content' => $new ), array( 'ID' => $id ) );
			clean_post_cache( $id );
		}
	}
}

