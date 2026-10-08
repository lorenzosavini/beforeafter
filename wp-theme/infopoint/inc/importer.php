<?php
/**
 * Contenuti iniziali e aggiornamento dall'offerta ufficiale UniMarconi.
 *
 * I dati in inc/data/ provengono da unimarconi.it (ottobre 2026): schede
 * corso, piani di studio, documenti, agevolazioni, pagine di iscrizione.
 * L'importazione è ripetibile: si può scegliere se creare solo ciò che manca,
 * completare i campi vuoti o sovrascrivere con i dati ufficiali.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_submenu_page( 'ip-settings', 'Contenuti e aggiornamenti', 'Contenuti ufficiali', 'manage_options', 'ip-import', 'ip_import_page' );
} );

function ip_import_data( $file ) {
	$p = IP_DIR . '/inc/data/' . $file . '.json';
	return file_exists( $p ) ? (array) json_decode( (string) file_get_contents( $p ), true ) : array();
}

function ip_import_page() {
	$done = null;
	if ( isset( $_POST['ip_import'] ) && check_admin_referer( 'ip_import' ) && current_user_can( 'manage_options' ) ) {
		$done = ip_run_import( array(
			'mode'      => in_array( $_POST['ip_mode'] ?? '', array( 'new', 'fill', 'overwrite' ), true ) ? $_POST['ip_mode'] : 'fill',
			'retire'    => ! empty( $_POST['ip_retire'] ),
			'pages'     => ! empty( $_POST['ip_pages'] ),
			'menus'     => ! empty( $_POST['ip_menus'] ),
		) );
	}
	if ( isset( $_POST['ip_images'] ) && check_admin_referer( 'ip_import' ) && current_user_can( 'upload_files' ) ) {
		$done = ip_import_images( 20 );
	}
	$courses  = ip_import_data( 'corsi' );
	$existing = (int) wp_count_posts( 'corso' )->publish;
	$no_img   = count( get_posts( array( 'post_type' => 'corso', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_ip_image_src', 'compare' => 'EXISTS' ), array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ) ) ) ) );
	$counts   = array();
	foreach ( $courses as $c ) {
		$counts[ $c['tipologia'] ] = ( $counts[ $c['tipologia'] ] ?? 0 ) + 1;
	}
	$tips = ip_import_tipologie();
	?>
	<div class="wrap ip-admin">
		<h1>Contenuti ufficiali UniMarconi</h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( implode( ' · ', $done ) ); ?></p></div>
		<?php endif; ?>
		<p>Il tema contiene l’offerta ufficiale presa da <strong>unimarconi.it</strong>: <strong><?php echo count( $courses ); ?> corsi</strong> con presentazione, obiettivi, sbocchi, modalità di accesso, piani di studio per curriculum e documenti (brochure, regolamento, «il corso in breve»), le agevolazioni sulla retta e le pagine su tasse, immatricolazione, trasferimenti e servizi. Sul sito ci sono ora <?php echo (int) $existing; ?> corsi pubblicati.</p>
		<table class="widefat striped" style="max-width:640px;margin:16px 0">
			<thead><tr><th>Tipologia</th><th>Corsi nei dati ufficiali</th></tr></thead>
			<tbody>
			<?php foreach ( $tips as $slug => $t ) : ?>
				<?php if ( ! empty( $counts[ $slug ] ) ) : ?>
					<tr><td><?php echo esc_html( $t[0] ); ?></td><td><?php echo (int) $counts[ $slug ]; ?></td></tr>
				<?php endif; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
		<form method="post">
			<?php wp_nonce_field( 'ip_import' ); ?>
			<h2>Importa o aggiorna</h2>
			<fieldset>
				<label style="display:block;margin:6px 0"><input type="radio" name="ip_mode" value="new"> <strong>Solo ciò che manca</strong> — crea i corsi nuovi, non tocca quelli esistenti</label>
				<label style="display:block;margin:6px 0"><input type="radio" name="ip_mode" value="fill" checked> <strong>Completa</strong> — crea i nuovi e riempie solo i campi vuoti di quelli esistenti (consigliato)</label>
				<label style="display:block;margin:6px 0"><input type="radio" name="ip_mode" value="overwrite"> <strong>Sovrascrivi</strong> — riporta testi, dati, piani di studio e documenti ai valori ufficiali (le modifiche fatte a mano su quei campi vanno perse; le revisioni restano)</label>
			</fieldset>
			<p><label><input type="checkbox" name="ip_pages" value="1" checked> Crea le pagine mancanti (iscrizione, tasse, trasferimenti, contatti…)</label></p>
			<p><label><input type="checkbox" name="ip_menus" value="1" checked> Crea i menu se non ne è assegnato nessuno</label></p>
			<p><label><input type="checkbox" name="ip_retire" value="1"> Metti in bozza i corsi che non sono più nell’offerta ufficiale</label></p>
			<?php submit_button( 'Avvia', 'primary', 'ip_import' ); ?>
			<h2>Immagini di copertina</h2>
			<p>Scarica nella Libreria media le immagini ufficiali dei corsi e le imposta come immagine in evidenza, 20 alla volta. Da scaricare: <strong><?php echo (int) $no_img; ?></strong>.</p>
			<?php submit_button( 'Scarica 20 immagini', 'secondary', 'ip_images', false ); ?>
		</form>
		<p class="description" style="margin-top:24px">Contenuti ufficiali dell’Università degli Studi Guglielmo Marconi, aggiornati a ottobre 2026. Verifica sempre importi e scadenze prima della pubblicazione: l’Ateneo li aggiorna nel corso dell’anno.</p>
	</div>
	<?php
}

function ip_import_tipologie() {
	return array(
		'laurea-triennale'                => array( 'Laurea triennale', 10, '180 CFU in tre anni. Si accede con il diploma di scuola superiore, senza test d’ingresso.' ),
		'laurea-magistrale'               => array( 'Laurea magistrale', 20, '120 CFU in due anni. Si accede con una laurea triennale e la verifica dei requisiti curriculari.' ),
		'laurea-magistrale-a-ciclo-unico' => array( 'Laurea magistrale a ciclo unico', 30, 'Percorso unico di cinque anni, 300 CFU. Si accede con il diploma di scuola superiore.' ),
		'master-di-i-livello'             => array( 'Master di I livello', 40, 'Formazione specialistica di 60 CFU per chi ha una laurea triennale.' ),
		'master-di-ii-livello'            => array( 'Master di II livello', 50, 'Formazione avanzata di 60 CFU per chi ha una laurea magistrale o a ciclo unico.' ),
		'master-di-i-livello-discipline-per-la-didattica'  => array( 'Master per la didattica di I livello', 60, 'Master annuali da 60 CFU per docenti, utili per aggiornamento e graduatorie.' ),
		'master-di-ii-livello-discipline-per-la-didattica' => array( 'Master per la didattica di II livello', 70, 'Master annuali da 60 CFU per docenti in possesso di laurea magistrale.' ),
		'percorsi-abilitanti'             => array( 'Abilitazione all’insegnamento', 80, 'Percorsi di formazione iniziale da 30, 36 e 60 CFU (DPCM 4 agosto 2023).' ),
		'specializzazione-sostegno'       => array( 'Specializzazione sul sostegno', 85, 'Percorsi da 40 CFU per docenti con esperienza sul sostegno.' ),
		'corsi-di-formazione'             => array( 'Corsi di formazione', 90, 'Corsi professionalizzanti, di aggiornamento e di preparazione ai concorsi.' ),
		'microcredenziali'                => array( 'Microcredenziali', 95, 'Percorsi brevi certificati da 4–6 CFU.' ),
		'dottorato-di-ricerca'            => array( 'Dottorato di ricerca', 100, 'Dottorati triennali dei dipartimenti dell’Ateneo.' ),
	);
}

function ip_import_featured() {
	return array( 'psicologia-lm-51', 'giurisprudenza-lmg-01', 'economia-aziendale-e-management-l-18', 'scienze-e-tecniche-psicologiche-l24', 'scienze-giuridiche-l-14', 'scienze-motorie-e-sportive-l-22', 'ingegneria-informatica-l-8', 'scienze-delleducazione-e-della-formazione-l-19' );
}

/**
 * Trova un corso già presente: per pagina ufficiale, poi per indirizzo.
 */
function ip_import_find_course( $c ) {
	$q = get_posts( array( 'post_type' => 'corso', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ip_fonte', 'meta_value' => $c['source'] ) );
	if ( $q ) {
		return (int) $q[0];
	}
	foreach ( array_filter( array( $c['slug'], $c['old'] ?? '' ) ) as $slug ) {
		$p = get_page_by_path( $slug, OBJECT, 'corso' );
		if ( $p ) {
			return (int) $p->ID;
		}
	}
	return 0;
}

/**
 * Crea o aggiorna un corso dai dati ufficiali (stesso formato di corsi.json).
 * Usata dall'importazione e dagli aggiornamenti automatici.
 *
 * @param array  $c       Dati del corso.
 * @param string $mode    new | fill | overwrite.
 * @param array  $tip_ids Mappa slug tipologia => term_id.
 * @param array  $o       order, featured, status, content (false = non toccare titolo e testo).
 * @return array|null id, new, curricula.
 */
function ip_import_course( $c, $mode, $tip_ids, $o = array() ) {
	$o    = wp_parse_args( $o, array( 'order' => 0, 'featured' => array(), 'status' => 'publish', 'content' => true ) );
	$curr = 0;
	$id     = ip_import_find_course( $c );
	$is_new = ! $id;
	if ( $is_new ) {
		$id = wp_insert_post( array(
			'post_type'    => 'corso',
			'post_title'   => $c['title'],
			'post_name'    => $c['slug'],
			'post_content' => wp_slash( $c['content'] ),
			'menu_order'   => $o['order'],
			'post_status'  => $o['status'],
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			return null;
		}
	} elseif ( 'new' === $mode ) {
		return array( 'id' => $id, 'new' => false, 'curricula' => 0 );
	} else {
		$post   = get_post( $id );
		$update = array( 'ID' => $id );
		if ( $o['content'] && ( 'overwrite' === $mode || ! trim( $post->post_content ) ) ) {
			$update['post_content'] = wp_slash( $c['content'] );
		}
		if ( 'overwrite' === $mode && $o['content'] ) {
			$update['post_title'] = $c['title'];
		}
		if ( count( $update ) > 1 ) {
			wp_update_post( $update );
		}
	}

	$meta = array(
		'short'  => $c['short'] !== $c['title'] ? $c['short'] : '',
		'code'   => $c['code'],
		'cfu'    => $c['cfu'],
		'durata' => $c['durata'],
		'retta'  => $c['retta'],
		'lingua' => $c['lingua'],
		'fonte'  => $c['source'],
		'evidenza' => $c['evidenza'] ?? '',
	);
	if ( 0 === strpos( $c['tipologia'], 'laurea' ) ) {
		$meta['accesso'] = 'laurea-magistrale' === $c['tipologia'] ? 'Libero, con verifica dei requisiti' : 'Libero, senza test';
	}
	if ( ! $is_new && 'overwrite' === $mode ) {
		update_post_meta( $id, '_ip_stato', ! empty( $c['stato'] ) ? $c['stato'] : 'aperte' );
	}
	if ( $is_new ) {
		$meta['stato'] = ! empty( $c['stato'] ) ? $c['stato'] : 'aperte';
		if ( in_array( $c['slug'], $o['featured'], true ) ) {
			$meta['featured'] = '1';
		}
	}
	foreach ( $meta as $k => $v ) {
		if ( '' === (string) $v ) {
			// Nota e costo tolti dalla scheda ufficiale: via anche dal sito.
			if ( ! $is_new && 'overwrite' === $mode && in_array( $k, array( 'evidenza', 'retta' ), true ) ) {
				delete_post_meta( $id, '_ip_' . $k );
			}
			continue;
		}
		if ( $is_new || 'overwrite' === $mode || '' === ip_meta( $k, $id ) ) {
			update_post_meta( $id, '_ip_' . $k, $v );
		}
	}
	if ( $c['image'] ) {
		update_post_meta( $id, '_ip_image_src', esc_url_raw( $c['image'] ) );
	}
	if ( ( $c['docs'] && ( $is_new || ! ip_meta_rows( 'docs', $id ) ) ) || ( ! $is_new && 'overwrite' === $mode ) ) {
		$docs = array_map( function ( $d ) {
			return array( 'label' => $d[0], 'url' => $d[1] );
		}, $c['docs'] );
		// I documenti aggiunti a mano (non dell'Ateneo) restano.
		foreach ( $is_new ? array() : ip_meta_rows( 'docs', $id ) as $d ) {
			if ( ! empty( $d['url'] ) && false === strpos( $d['url'], 'unimarconi.it' ) ) {
				$docs[] = $d;
			}
		}
		$docs ? update_post_meta( $id, '_ip_docs', $docs ) : delete_post_meta( $id, '_ip_docs' );
	}
	if ( isset( $tip_ids[ $c['tipologia'] ] ) && ( $is_new || 'overwrite' === $mode || ! get_the_terms( $id, 'tipologia' ) ) ) {
		wp_set_object_terms( $id, $tip_ids[ $c['tipologia'] ], 'tipologia' );
	}
	foreach ( array( 'dipartimento' => $c['dipartimento'], 'area' => $c['area'] ) as $tax => $name ) {
		if ( $name && ( $is_new || 'overwrite' === $mode || ! get_the_terms( $id, $tax ) ) ) {
			wp_set_object_terms( $id, ip_import_term( $name, $tax ), $tax );
		}
	}

	// Piani di studio.
	if ( ! $is_new && 'overwrite' === $mode && function_exists( 'ip_sync_stale_curricula' ) ) {
		foreach ( ip_sync_stale_curricula( $id, $c ) as $cid ) {
			wp_update_post( array( 'ID' => $cid, 'post_status' => 'draft' ) );
		}
	}
	foreach ( $c['curricula'] as $j => $cu ) {
		$ex = get_page_by_path( $cu['slug'], OBJECT, 'curriculum' );
		if ( $ex ) {
			if ( 'overwrite' === $mode ) {
				wp_update_post( array( 'ID' => $ex->ID, 'post_title' => $cu['name'], 'post_content' => wp_slash( $cu['content'] ), 'menu_order' => $j, 'post_status' => 'publish' ) );
			}
			update_post_meta( $ex->ID, '_ip_course', $id );
			update_post_meta( $ex->ID, '_ip_fonte', $cu['slug'] );
			continue;
		}
		$cid = wp_insert_post( array(
			'post_type'    => 'curriculum',
			'post_status'  => 'publish',
			'post_title'   => $cu['name'],
			'post_name'    => $cu['slug'],
			'post_content' => wp_slash( $cu['content'] ),
			'menu_order'   => $j,
		) );
		if ( $cid && ! is_wp_error( $cid ) ) {
			update_post_meta( $cid, '_ip_course', $id );
			update_post_meta( $cid, '_ip_fonte', $cu['slug'] );
			$curr++;
		}
	}

	return array( 'id' => $id, 'new' => $is_new, 'curricula' => $curr );
}

/**
 * Tipologie ufficiali: le crea se mancano e restituisce slug => term_id.
 */
function ip_import_tip_ids( $mode = 'fill' ) {
	$tip_ids = array();
	foreach ( ip_import_tipologie() as $slug => $t ) {
		$term = get_term_by( 'slug', $slug, 'tipologia' );
		if ( ! $term ) {
			$r = wp_insert_term( $t[0], 'tipologia', array( 'slug' => $slug, 'description' => $t[2] ) );
			if ( is_wp_error( $r ) ) {
				continue;
			}
			update_term_meta( $r['term_id'], 'ordine', $t[1] );
			$tip_ids[ $slug ] = (int) $r['term_id'];
		} else {
			$tip_ids[ $slug ] = (int) $term->term_id;
			if ( 'overwrite' === $mode ) {
				wp_update_term( $term->term_id, 'tipologia', array( 'name' => $t[0], 'description' => $t[2] ) );
				update_term_meta( $term->term_id, 'ordine', $t[1] );
			}
		}
	}

	return $tip_ids;
}

function ip_import_term( $name, $tax ) {
	if ( ! $name ) {
		return 0;
	}
	$t = term_exists( $name, $tax );
	if ( ! $t ) {
		$t = wp_insert_term( $name, $tax );
	}
	return is_wp_error( $t ) ? 0 : (int) $t['term_id'];
}

function ip_run_import( $o ) {
	@set_time_limit( 600 ); // phpcs:ignore
	wp_defer_term_counting( true );
	$mode = $o['mode'];
	$log  = array();

	$tip_ids = ip_import_tip_ids( $mode );

	// Corsi.
	$featured = ip_import_featured();
	$created  = 0;
	$updated  = 0;
	$curr_n   = 0;
	$seen     = array();
	foreach ( ip_import_data( 'corsi' ) as $i => $c ) {
		$r = ip_import_course( $c, $mode, $tip_ids, array( 'order' => $i, 'featured' => $featured ) );
		if ( ! $r ) {
			continue;
		}
		$seen[] = $r['id'];
		$r['new'] ? $created++ : ( 'new' !== $mode ? $updated++ : null );
		$curr_n += $r['curricula'];
	}
	$log[] = $created . ' corsi creati';
	if ( 'new' !== $mode ) {
		$log[] = $updated . ( 'overwrite' === $mode ? ' corsi sovrascritti' : ' corsi completati' );
	}
	$log[] = $curr_n . ' piani di studio creati';

	if ( $o['retire'] ) {
		$old = get_posts( array( 'post_type' => 'corso', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'post__not_in' => $seen ? $seen : array( 0 ) ) );
		foreach ( $old as $oid ) {
			wp_update_post( array( 'ID' => $oid, 'post_status' => 'draft' ) );
		}
		$log[] = count( $old ) . ' corsi non più in offerta messi in bozza';
	}
	wp_defer_term_counting( false );
	delete_transient( 'ip_course_options' );

	// Agevolazioni.
	$n = 0;
	foreach ( ip_import_data( 'agevolazioni' ) as $a ) {
		$ex = get_page_by_path( $a['slug'], OBJECT, 'agevolazione' );
		if ( $ex && 'overwrite' !== $mode ) {
			continue;
		}
		$id = $ex ? $ex->ID : wp_insert_post( array( 'post_type' => 'agevolazione', 'post_status' => 'publish', 'post_title' => $a['title'], 'post_name' => $a['slug'], 'menu_order' => $a['ordine'] ) );
		if ( $ex ) {
			wp_update_post( array( 'ID' => $id, 'post_title' => $a['title'], 'menu_order' => $a['ordine'] ) );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			if ( ! empty( $a['fonte'] ) ) {
				update_post_meta( $id, '_ip_fonte', $a['fonte'] );
			}
			foreach ( array( 'dest' => 'destinatari', 'retta' => 'retta', 'rata' => 'rata', 'cond' => 'condizioni', 'det' => 'dettagli' ) as $k => $src ) {
				$v = isset( $a[ $src ] ) ? $a[ $src ] : '';
				'' !== $v ? update_post_meta( $id, '_ip_' . $k, $v ) : delete_post_meta( $id, '_ip_' . $k );
			}
			$n++;
		}
	}
	$log[] = $n . ' agevolazioni create o aggiornate';

	if ( ! $o['pages'] ) {
		flush_rewrite_rules();
		return $log;
	}

	// Pagine del tema.
	$pages = array();
	$n     = 0;
	foreach ( ip_import_pages() as $slug => $p ) {
		$parent   = ! empty( $p['parent'] ) && isset( $pages[ $p['parent'] ] ) ? $pages[ $p['parent'] ] : 0;
		$path     = $parent ? get_page_uri( $parent ) . '/' . $slug : $slug;
		$existing = get_page_by_path( $path );
		if ( $existing ) {
			$pages[ $slug ] = $existing->ID;
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $slug,
			'post_parent'  => $parent,
			'post_excerpt' => isset( $p['excerpt'] ) ? $p['excerpt'] : '',
			'post_content' => isset( $p['content'] ) ? wp_slash( $p['content'] ) : '',
			'menu_order'   => $n,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			$pages[ $slug ] = $id;
			if ( ! empty( $p['template'] ) ) {
				update_post_meta( $id, '_wp_page_template', $p['template'] );
			}
			if ( ! empty( $p['noindex'] ) ) {
				update_post_meta( $id, '_ip_seo_noindex', '1' );
			}
			$n++;
		}
	}

	// Pagine informative ufficiali, sotto «Iscriversi».
	$parent = $pages['iscriversi'] ?? 0;
	$embed  = ip_embedded_sources();
	foreach ( ip_import_data( 'pagine' ) as $slug => $p ) {
		$path = $parent ? get_page_uri( $parent ) . '/' . $slug : $slug;
		$ex   = get_page_by_path( $path );
		if ( $ex ) {
			if ( 'overwrite' === $mode ) {
				wp_update_post( array( 'ID' => $ex->ID, 'post_content' => wp_slash( $p['content'] ) ) );
			}
			$pages[ $slug ] = $ex->ID;
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $slug,
			'post_parent'  => $parent,
			'post_content' => wp_slash( $p['content'] ),
			'menu_order'   => $n,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', 'page-templates/sidebar-form.php' );
			update_post_meta( $id, '_ip_fonte', $p['source'] );
			// Testo già mostrato dentro una pagina del tema: niente doppioni nei motori di ricerca.
			if ( in_array( trailingslashit( $p['source'] ), $embed, true ) ) {
				update_post_meta( $id, '_ip_seo_noindex', '1' );
			}
			$pages[ $slug ] = $id;
			$n++;
		}
	}
	$log[] = $n . ' pagine create';

	// Home statica, pagina news, pagina di ringraziamento.
	if ( 'page' !== get_option( 'show_on_front' ) && isset( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
		if ( isset( $pages['news'] ) ) {
			update_option( 'page_for_posts', $pages['news'] );
		}
	}
	$opts = get_option( 'ip_settings', array() );
	$opts = is_array( $opts ) ? $opts : array();
	if ( empty( $opts['thanks_page'] ) && isset( $pages['grazie'] ) ) {
		$opts['thanks_page'] = (string) $pages['grazie'];
		update_option( 'ip_settings', $opts );
	}

	if ( $o['menus'] ) {
		$log = array_merge( $log, ip_import_menus( $pages, $tip_ids ) );
	}

	flush_rewrite_rules();
	return $log;
}

function ip_import_menus( $pages, $tip_ids ) {
	$log       = array();
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$add       = function ( $menu, $args, $parent = 0 ) {
		$args = array_merge( array( 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent ), $args );
		return wp_update_nav_menu_item( $menu, 0, $args );
	};
	$page = function ( $slug, $title = '' ) use ( $pages ) {
		return isset( $pages[ $slug ] ) ? array( 'menu-item-title' => $title, 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $slug ], 'menu-item-type' => 'post_type' ) : null;
	};
	$tip = function ( $slug ) use ( $tip_ids ) {
		return isset( $tip_ids[ $slug ] ) ? array( 'menu-item-object' => 'tipologia', 'menu-item-object-id' => $tip_ids[ $slug ], 'menu-item-type' => 'taxonomy' ) : null;
	};
	if ( empty( $locations['primary'] ) ) {
		$menu = wp_create_nav_menu( 'Principale' );
		if ( ! is_wp_error( $menu ) ) {
			$tree = array(
				array( $page( 'corsi-di-laurea', 'Corsi di laurea' ), array( $tip( 'laurea-triennale' ), $tip( 'laurea-magistrale' ), $tip( 'laurea-magistrale-a-ciclo-unico' ), $page( 'corsi-singoli' ), $page( 'doppia-laurea' ) ) ),
				array( $page( 'master', 'Master' ), array( $tip( 'master-di-i-livello' ), $tip( 'master-di-ii-livello' ) ) ),
				array( $page( 'corsi-insegnanti', 'Insegnanti' ), array( $page( 'percorsi-abilitanti' ), $tip( 'specializzazione-sostegno' ), $tip( 'master-di-i-livello-discipline-per-la-didattica' ), $tip( 'master-di-ii-livello-discipline-per-la-didattica' ) ) ),
				array( $page( 'altri-corsi', 'Altri corsi' ), array( $tip( 'corsi-di-formazione' ), $tip( 'microcredenziali' ), $tip( 'dottorato-di-ricerca' ) ) ),
				array( $page( 'convenzioni-e-agevolazioni', 'Costi e agevolazioni' ), array( $page( 'tasse-di-iscrizione' ), $page( 'convenzioni-e-agevolazioni', 'Agevolazioni e convenzioni' ), $page( 'modalita-di-pagamento' ), $page( 'pa-110-e-lode' ) ) ),
				array( $page( 'iscriversi', 'Iscriversi' ), array( $page( 'immatricolazione' ), $page( 'riconoscimento-cfu' ), $page( 'trasferimento-da-altro-ateneo' ), $page( 'area-studenti', 'Requisiti di accesso' ), $page( 'sedi-esame' ), $page( 'studenti-stranieri' ) ) ),
				array( $page( 'contatti' ), array( $page( 'chi-siamo' ) ) ),
			);
			foreach ( $tree as $node ) {
				if ( ! $node[0] ) {
					continue;
				}
				$pid = $add( $menu, $node[0] );
				foreach ( array_filter( $node[1] ) as $child ) {
					$add( $menu, $child, $pid );
				}
			}
			$locations['primary'] = $menu;
			$log[]                = 'menu principale creato';
		}
	}
	if ( empty( $locations['footer'] ) ) {
		$menu = wp_create_nav_menu( 'Offerta (piè di pagina)' );
		if ( ! is_wp_error( $menu ) ) {
			foreach ( array( 'laurea-triennale', 'laurea-magistrale', 'laurea-magistrale-a-ciclo-unico', 'master-di-i-livello', 'master-di-ii-livello', 'percorsi-abilitanti', 'corsi-di-formazione' ) as $t ) {
				if ( $tip( $t ) ) {
					$add( $menu, $tip( $t ) );
				}
			}
			$locations['footer'] = $menu;
		}
	}
	if ( empty( $locations['legal'] ) ) {
		$menu = wp_create_nav_menu( 'Link utili' );
		if ( ! is_wp_error( $menu ) ) {
			foreach ( array( 'chi-siamo', 'iscriversi', 'tasse-di-iscrizione', 'sedi-esame', 'contatti' ) as $s ) {
				if ( $page( $s ) ) {
					$add( $menu, $page( $s ) );
				}
			}
			$locations['legal'] = $menu;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	return $log;
}

/**
 * Scarica le copertine ufficiali, qualche corso alla volta.
 */
function ip_import_images( $limit ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	@set_time_limit( 300 ); // phpcs:ignore
	$ids = get_posts( array(
		'post_type'      => 'corso',
		'posts_per_page' => $limit,
		'fields'         => 'ids',
		'meta_query'     => array(
			array( 'key' => '_ip_image_src', 'compare' => 'EXISTS' ),
			array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ),
		),
	) );
	$ok = 0;
	foreach ( $ids as $id ) {
		$src = get_post_meta( $id, '_ip_image_src', true );
		// Le copertine condivise tra più corsi si scaricano una volta sola.
		$att = get_posts( array( 'post_type' => 'attachment', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_ip_image_src', 'meta_value' => $src ) );
		$att = $att ? $att[0] : media_sideload_image( $src, $id, get_the_title( $id ), 'id' );
		if ( is_wp_error( $att ) ) {
			delete_post_meta( $id, '_ip_image_src' );
			continue;
		}
		update_post_meta( $att, '_ip_image_src', $src );
		set_post_thumbnail( $id, $att );
		$ok++;
	}
	return array( $ok . ' immagini impostate' );
}

function ip_import_pages() {
	$p  = function ( $t ) {
		return "<!-- wp:paragraph -->\n<p>" . $t . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$h  = function ( $t, $l = 2 ) {
		return '<!-- wp:heading' . ( 2 === $l ? '' : ' {"level":' . $l . '}' ) . " -->\n<h$l class=\"wp-block-heading\">" . $t . "</h$l>\n<!-- /wp:heading -->\n\n";
	};
	$ul = function ( $items ) {
		$li = '';
		foreach ( $items as $i ) {
			$li .= "<!-- wp:list-item -->\n<li>" . $i . "</li>\n<!-- /wp:list-item -->\n";
		}
		return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . $li . "</ul>\n<!-- /wp:list -->\n\n";
	};
	$sc = function ( $s ) {
		return "<!-- wp:shortcode -->\n" . $s . "\n<!-- /wp:shortcode -->\n\n";
	};
	$faq = function ( $q, $a ) {
		return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>" . $q . "</summary><!-- wp:paragraph -->\n<p>" . $a . "</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->\n\n";
	};

	return array(
		'home'     => array( 'title' => 'Home' ),
		'news'     => array( 'title' => 'News' ),

		'corsi-di-laurea' => array(
			'title'   => 'Corsi di laurea online',
			'excerpt' => 'Lauree triennali, magistrali e a ciclo unico dell’Università Marconi: studi online, sostieni gli esami in presenza e ti iscrivi in qualsiasi momento dell’anno.',
			'content' => $sc( '[ip_corsi tipologia="laurea-triennale,laurea-magistrale,laurea-magistrale-a-ciclo-unico"]' )
				. $h( 'Chi può iscriversi' )
				. $ul( array(
					'<strong>Laurea triennale e magistrale a ciclo unico</strong>: diploma di scuola secondaria di secondo grado (sono validi anche i titoli quadriennali di licei artistici e istituti magistrali) o titolo estero riconosciuto.',
					'<strong>Laurea magistrale</strong>: laurea triennale o diploma universitario triennale, o titolo estero riconosciuto. I requisiti curriculari vengono verificati prima dell’immatricolazione; eventuali crediti mancanti si integrano con i corsi singoli.',
				) )
				. $p( 'Hai già esami alle spalle? Puoi chiedere il <a href="/riconoscimento-cfu/">riconoscimento dei CFU</a> e accorciare il percorso.' ),
		),

		'master' => array(
			'title'   => 'Master di I e II livello',
			'excerpt' => 'Master online di I e II livello in management, marketing, diritto, sanità, comunicazione e tecnologie.',
			'content' => $sc( '[ip_corsi tipologia="master-di-i-livello,master-di-ii-livello"]' )
				. $h( 'I o II livello: quale scegliere' )
				. $p( 'Il master di I livello è aperto a chi ha una laurea triennale; quello di II livello richiede una laurea magistrale, a ciclo unico o del vecchio ordinamento. Durata, crediti e costo di ciascun master sono nella sua scheda, aggiornata dal sito ufficiale.' )
				. $p( 'Per alcune categorie di iscritti l’Ateneo prevede condizioni agevolate anche sui master: <a href="/convenzioni-e-agevolazioni/">vedi le agevolazioni</a>.' ),
		),

		'corsi-insegnanti' => array(
			'title'   => 'Corsi per insegnanti',
			'excerpt' => 'Percorsi abilitanti, master per la didattica, corsi di perfezionamento e certificazioni informatiche per docenti e aspiranti docenti.',
			'content' => $p( 'Percorsi per ottenere l’abilitazione all’insegnamento e la specializzazione sul sostegno, e master annuali per l’aggiornamento professionale e il punteggio nelle graduatorie.' )
				. $sc( '[ip_corsi tipologia="percorsi-abilitanti,specializzazione-sostegno,master-di-i-livello-discipline-per-la-didattica,master-di-ii-livello-discipline-per-la-didattica"]' ),
		),

		'percorsi-abilitanti' => array(
			'title'    => 'Percorsi abilitanti 30, 36 e 60 CFU',
			'parent'   => 'corsi-insegnanti',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'I percorsi di formazione iniziale per ottenere l’abilitazione all’insegnamento nella scuola secondaria di primo e secondo grado (DPCM 4 agosto 2023).',
			'content'  => $sc( '[ip_corsi tipologia="percorsi-abilitanti,specializzazione-sostegno" filtro="no"]' ) . "<!-- wp:group {\"className\":\"box\"} -->\n<div class=\"wp-block-group box\"><!-- wp:paragraph -->\n<p>Le iscrizioni seguono i bandi dell’Ateneo e restano aperte poche settimane. Lasciaci il tuo contatto: ti avvisiamo all’uscita del prossimo bando e verifichiamo con te quale percorso ti spetta.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->\n\n"
				. $p( 'Requisiti di accesso, posti disponibili, costi e calendario sono fissati da ciascun bando: li trovi aggiornati nella scheda di ogni percorso, presa dal sito ufficiale dell’Ateneo.' ),
		),

		'altri-corsi' => array(
			'title'   => 'Corsi di formazione, microcredenziali e dottorati',
			'excerpt' => 'Corsi professionalizzanti e di aggiornamento, preparazione ai concorsi, microcredenziali certificate e dottorati di ricerca dell’Università Marconi.',
			'content' => $sc( '[ip_corsi tipologia="corsi-di-formazione,microcredenziali,dottorato-di-ricerca"]' ),
		),

		'iscriversi' => array(
			'title'   => 'Iscriversi all’Università Marconi',
			'excerpt' => 'Immatricolazione, tasse, agevolazioni, trasferimenti, riconoscimento crediti: tutto quello che serve sapere, e un orientatore che ti segue passo passo.',
			'content' => $p( 'Ci si iscrive in qualsiasi periodo dell’anno. Scegli l’argomento che ti interessa oppure lasciaci i tuoi dati: un orientatore ti guida nella procedura e nella scelta dell’agevolazione giusta.' )
				. $ul( array(
					'<a href="/iscriversi/immatricolazione/">Come immatricolarsi</a>',
					'<a href="/iscriversi/tasse-di-iscrizione/">Tasse di iscrizione</a>',
					'<a href="/convenzioni-e-agevolazioni/">Agevolazioni e convenzioni</a>',
					'<a href="/iscriversi/modalita-di-pagamento/">Modalità di pagamento</a>',
					'<a href="/riconoscimento-cfu/">Riconoscimento dei CFU</a>',
					'<a href="/iscriversi/trasferimento-da-altro-ateneo/">Trasferimento da un altro ateneo</a>',
					'<a href="/iscriversi/iscrizione-master/">Iscrizione ai master</a>',
					'<a href="/iscriversi/integrazioni-curriculari-ica/">Integrazioni curriculari per le magistrali (ICA)</a>',
					'<a href="/iscriversi/studenti-stranieri/">Studenti stranieri e titoli esteri</a>',
					'<a href="/iscriversi/dual-career/">Dual Career per atleti</a>',
					'<a href="/iscriversi/studenti-con-disabilita-e-dsa/">Studenti con disabilità e DSA</a>',
					'<a href="/iscriversi/carriere-alias/">Carriere Alias</a>',
					'<a href="/sedi-esame/">Sedi d’esame</a>',
				) ),
		),

		'convenzioni-e-agevolazioni' => array(
			'title'   => 'Agevolazioni e convenzioni',
			'excerpt' => 'Retta standard {retta_std}; con le agevolazioni dell’Ateneo si parte da {rata_min} al mese. Ecco a chi spettano e a quali condizioni, aggiornate dal sito ufficiale.',
			'content' => $p( 'Tutte le agevolazioni economiche si applicano al momento dell’immatricolazione e non hanno effetto retroattivo. Ti confermiamo sempre l’importo esatto prima dell’iscrizione.' )
				. $sc( '[ip_agevolazioni]' ),
		),

		'riconoscimento-cfu' => array(
			'title'   => 'Riconoscimento CFU',
			'excerpt' => 'Esami già sostenuti, una prima laurea o titoli professionali possono valere crediti e accorciare il percorso. La prevalutazione è gratuita e non ti impegna.',
			'content' => $p( 'Ci invii il piano di studi o l’autocertificazione degli esami, insieme a eventuali certificazioni e titoli professionali: prepariamo con te la richiesta di prevalutazione all’Ateneo e ti spieghiamo l’esito.' )
				. $h( 'Le regole dell’Ateneo' )
				. $sc( '[ip_ufficiale fonte="https://www.unimarconi.it/riconoscimento-cfu/"]' )
				. $sc( '[ip_modulo tipo="cfu"]' ),
		),

		'area-studenti' => array(
			'title'    => 'Requisiti e iscrizione',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'Titoli di accesso, procedura di immatricolazione e trasferimenti: quello che serve sapere prima di iscriversi.',
			'content'  => $p( 'Requisiti di accesso e procedura di immatricolazione, come li descrive l’Ateneo e sempre aggiornati. Noi ti affianchiamo in ogni passaggio, dalla scelta del corso al caricamento dei documenti.' )
				. $sc( '[ip_ufficiale fonte="https://www.unimarconi.it/immatricolazione-corso-di-laurea/"]' )
				. $h( 'Arrivi da un’altra università?' )
				. $p( 'Con la <a href="/riconoscimento-cfu/">prevalutazione dei CFU</a> sai in anticipo quali esami ti vengono riconosciuti e a quale anno puoi iscriverti. Leggi anche come funziona il <a href="/iscriversi/trasferimento-da-altro-ateneo/">trasferimento da un altro ateneo</a>.' ),
		),

		'doppia-laurea' => array(
			'title'    => 'Doppia iscrizione',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'Dal 2022 puoi iscriverti contemporaneamente a due corsi universitari (Legge 33/2022, D.M. 930/2022). Ecco quando è possibile.',
			'content'  => $p( 'Puoi iscriverti contemporaneamente a due corsi di studio, anche in atenei diversi, se la combinazione rispetta le condizioni di legge. Verifichiamo con te se quella che ti interessa è consentita e ti seguiamo nelle due iscrizioni.' )
				. $h( 'Cosa prevede la normativa' )
				. $sc( '[ip_ufficiale fonte="https://www.unimarconi.it/iscrizione-contemporanea-a-due-corsi-di-studio-di-istruzione-superiore/"]' ),
		),

		'corsi-singoli' => array(
			'title'    => 'Corsi singoli',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'Sostieni singoli esami senza iscriverti a un corso di laurea: per integrare i requisiti di una magistrale, per un concorso o per aggiornarti.',
			'content'  => $p( 'Con i corsi singoli sostieni uno o più esami senza iscriverti a un intero corso di laurea: servono, per esempio, a raggiungere i requisiti di una laurea magistrale o i crediti richiesti da un concorso. Ti aiutiamo a scegliere gli insegnamenti e a chiedere la conferma dell’Ateneo prima del pagamento.' )
				. $h( 'Insegnamenti e costi' )
				. $sc( '[ip_ufficiale fonte="https://www.unimarconi.it/corsi-singoli/"]' )
				. $h( 'Come ci si iscrive' )
				. $sc( '[ip_ufficiale fonte="https://www.unimarconi.it/iscrizione-ai-corsi-singoli/"]' ),
		),

		'sedi-esame' => array(
			'title'   => 'Sedi d’esame',
			'excerpt' => 'Gli esami UniMarconi si sostengono in presenza nelle sedi autorizzate. Prenoti l’appello dalla piattaforma e scegli la sede più comoda.',
			'content' => $p( 'Gli esami possono essere scritti, orali o misti e sono calendarizzati durante tutto l’anno accademico. L’elenco può cambiare: verifica la disponibilità della sede al momento della prenotazione.' )
				. $sc( '[ip_sedi]' ),
		),

		'contatti' => array(
			'title'   => 'Contatti',
			'excerpt' => 'Chiamaci, scrivici su WhatsApp o lascia i tuoi dati: un orientatore ti risponde entro un giorno lavorativo.',
			'content' => $sc( '[ip_contatti]' ) . $sc( '[ip_modulo]' ) . $h( 'Domande frequenti' )
				. $faq( 'La consulenza è a pagamento?', 'No. Orientamento, prevalutazione dei CFU e assistenza all’iscrizione sono gratuiti.' )
				. $faq( 'Posso venire in sede?', 'Sì, su appuntamento. Chiamaci o scrivici per fissare un orario.' )
				. $faq( 'Quanto tempo serve per iscriversi?', 'Se hai già scelto il corso e hai i documenti pronti, l’immatricolazione si completa in pochi giorni.' ),
		),

		'chi-siamo' => array(
			'title'   => 'Chi siamo',
			'excerpt' => 'Chi gestisce questo sito, che rapporto ha con l’Università Marconi e cosa fa (e non fa) per chi vuole iscriversi.',
			'content' => $sc( '[ip_chi_siamo]' ),
		),

		'grazie' => array(
			'title'   => 'Richiesta ricevuta',
			'noindex' => true,
			'content' => $p( 'Grazie, abbiamo ricevuto la tua richiesta. Un orientatore ti contatterà entro un giorno lavorativo al numero che hai indicato.' )
				. $p( 'Nel frattempo puoi consultare <a href="/corsi/">tutti i corsi</a> o le <a href="/convenzioni-e-agevolazioni/">agevolazioni sulla retta</a>.' )
				. $sc( '[ip_contatti]' ),
		),

		'iscriviti' => array(
			'title'    => 'La tua laurea online inizia da qui',
			'template' => 'page-templates/landing.php',
			'excerpt'  => 'Parla con un orientatore: scegli il corso, scopri costi e agevolazioni e iscriviti senza code né test d’ingresso.',
			'content'  => $sc( '[ip_passi]' ),
		),
	);
}
