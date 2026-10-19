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
