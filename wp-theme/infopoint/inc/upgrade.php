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
	if ( IP_VERSION !== $v ) {
		update_option( 'ip_version', IP_VERSION );
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
