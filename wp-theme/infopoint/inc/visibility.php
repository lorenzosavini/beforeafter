<?php
/**
 * Visibilità: nascondere dal sito corsi, tipologie, sezioni dei corsi,
 * parti della scheda, agevolazioni, pagine ed elementi comuni.
 * Nulla viene cancellato: si nasconde e si rimostra con un clic.
 */

defined( 'ABSPATH' ) || exit;

function ip_vis() {
	static $v = null;
	if ( null === $v || doing_action( 'update_option_ip_visibility' ) ) {
		$v = wp_parse_args( get_option( 'ip_visibility', array() ), array(
			'courses'   => array(),
			'tipologie' => array(),
			'agev'      => array(),
			'pages'     => array(),
			'sections'  => array(),
			'parts'     => array(),
			'site'      => array(),
		) );
	}
	return $v;
}

/** Titolo di sezione normalizzato: «Sbocchi professionali» = «sbocchi professionali ». */
function ip_vis_key( $title ) {
	$t = html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES, 'UTF-8' );
	$t = mb_strtolower( trim( preg_replace( '/\s+/u', ' ', $t ) ) );
	return rtrim( $t, ' :.' );
}

/** Parti fisse della scheda corso. */
function ip_vis_parts() {
	return array(
		'photo'     => array( 'Foto nella testata', 'L’immagine del corso in alto.' ),
		'facts'     => array( 'Scheda dati', 'Classe, CFU, durata, accesso, lingua.' ),
		'price'     => array( 'Retta e riquadro costo', 'L’importo nella testata e il riquadro costo accanto al modulo.' ),
		'status'    => array( 'Stato delle iscrizioni', 'Etichetta «Iscrizioni aperte / chiuse».' ),
		'curricula' => array( 'Piani di studio', '' ),
		'docs'      => array( 'Documenti', 'Brochure, regolamento e altri PDF.' ),
		'faq'       => array( 'Domande frequenti del corso', '' ),
		'steps'     => array( 'Come iscriversi con noi', 'I passi in fondo alla scheda.' ),
		'related'   => array( 'Altri corsi', 'I corsi della stessa tipologia in fondo alla pagina.' ),
	);
}

/** Elementi comuni a tutto il sito. */
function ip_vis_site() {
	return array(
		'mbar'        => array( 'Barra di contatto da telefono', 'Chiama · WhatsApp · Richiedi info, fissa in basso.' ),
		'whatsapp'    => array( 'Pulsanti WhatsApp', 'In tutto il sito: testata, schede, landing, email.' ),
		'nav_phone'   => array( 'Telefono nel menu', '' ),
		'nav_cta'     => array( 'Pulsante «Richiedi informazioni» nel menu', '' ),
		'endform'     => array( 'Modulo in fondo alle pagine', 'Sulle pagine che non hanno già un modulo.' ),
		'cta'         => array( 'Fascia «Parla con un orientatore»', 'La fascia di contatto prima del piè di pagina.' ),
		'breadcrumbs' => array( 'Percorso di navigazione', '«Home / Corsi / …» sopra i titoli.' ),
		'exit'        => array( 'Banner d’uscita', 'L’invito a lasciare il numero quando si sta per uscire.' ),
	);
}

/** Front-end vero: non pannello, non cron, non riga di comando. */
function ip_vis_front() {
	return ! is_admin() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

function ip_site_on( $key ) {
	return ! in_array( $key, ip_vis()['site'], true );
}

/** Una parte della scheda corso è visibile? (regola generale + eccezione del corso) */
function ip_part_on( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$own     = get_post_meta( $post_id, '_ip_vis_parts', true );
	$on      = ! in_array( $key, ip_vis()['parts'], true ) && ! ( is_array( $own ) && in_array( $key, $own, true ) );
	return (bool) apply_filters( 'ip_part_on', $on, $key, $post_id );
}

/** ID dei corsi nascosti, compresi quelli delle tipologie nascoste. */
function ip_hidden_courses() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$v   = ip_vis();
	$ids = array_map( 'intval', $v['courses'] );
	if ( $v['tipologie'] ) {
		$cache = get_transient( 'ip_vis_tipcourses' );
		if ( false === $cache ) {
			global $wpdb;
			$in    = implode( ',', array_map( 'intval', $v['tipologie'] ) );
			$cache = array_map( 'intval', $wpdb->get_col( "SELECT tr.object_id FROM {$wpdb->term_relationships} tr JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = 'tipologia' AND tt.term_id IN ($in)" ) ); // phpcs:ignore
			// Un corso con più tipologie resta visibile se almeno una è visibile.
			$keep  = $wpdb->get_col( "SELECT tr.object_id FROM {$wpdb->term_relationships} tr JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = 'tipologia' AND tt.term_id NOT IN ($in)" ); // phpcs:ignore
			$cache = array_values( array_diff( $cache, array_map( 'intval', $keep ) ) );
			set_transient( 'ip_vis_tipcourses', $cache, DAY_IN_SECONDS );
		}
		$ids = array_merge( $ids, $cache );
	}
	return $ids = array_values( array_unique( $ids ) );
}

/** Home, privacy, cookie policy e ringraziamento: sempre visibili. */
function ip_vis_locked_pages() {
	return array_values( array_filter( array( (int) get_option( 'page_on_front' ), (int) get_option( 'page_for_posts' ), (int) ip_opt( 'privacy_page' ), (int) ip_opt( 'cookie_page' ), (int) ip_opt( 'thanks_page' ) ) ) );
}

function ip_hidden_posts() {
	static $ids = null;
	if ( null === $ids ) {
		$v     = ip_vis();
		$pages = array_diff( array_map( 'intval', $v['pages'] ), ip_vis_locked_pages() );
		$ids   = array_values( array_unique( array_merge( ip_hidden_courses(), array_map( 'intval', $v['agev'] ), $pages ) ) );
	}
	return $ids;
}

add_action( 'save_post_corso', function () {
	delete_transient( 'ip_vis_tipcourses' );
} );
add_action( 'set_object_terms', function () {
	delete_transient( 'ip_vis_tipcourses' );
} );

/* ---------------------------------------------------------------------
 * Applicazione sul sito
 * ------------------------------------------------------------------- */

// Tutte le liste del sito (elenchi, ricerca, correlati, landing, sitemap…).
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! ip_vis_front() || $q->get( 'ip_all' ) ) {
		return;
	}
	$hide = ip_hidden_posts();
	if ( ! $hide ) {
		return;
	}
	$pt = (array) $q->get( 'post_type' );
	if ( $q->is_singular() && $q->is_main_query() ) {
		return; // La singola pagina nascosta viene reindirizzata sotto.
	}
	if ( array_intersect( $pt, array( 'corso', 'agevolazione', 'page', 'any' ) ) || ( $q->is_search() && $q->is_main_query() ) ) {
		$q->set( 'post__not_in', array_merge( (array) $q->get( 'post__not_in' ), $hide ) );
	}
} );

// Tipologie nascoste spariscono da elenchi, filtri e menu dei corsi.
add_filter( 'get_terms_args', function ( $args, $tax ) {
	// Le ricerche di un termine preciso (indirizzo della tipologia) restano libere: il reindirizzamento ci pensa dopo.
	$exact = ! empty( $args['slug'] ) || ! empty( $args['name'] ) || ! empty( $args['include'] ) || ! empty( $args['term_taxonomy_id'] ) || ! empty( $args['object_ids'] );
	if ( ip_vis_front() && ! $exact && in_array( 'tipologia', (array) $tax, true ) && ip_vis()['tipologie'] ) {
		$args['exclude'] = array_merge( wp_parse_id_list( $args['exclude'] ?? array() ), array_map( 'intval', ip_vis()['tipologie'] ) );
	}
	return $args;
}, 10, 2 );

// Chi arriva su un contenuto nascosto viene portato al livello superiore.
add_action( 'template_redirect', function () {
	if ( current_user_can( 'edit_posts' ) && isset( $_GET['preview'] ) ) {
		return;
	}
	$v = ip_vis();
	if ( is_singular( array( 'corso', 'agevolazione', 'page' ) ) && in_array( get_queried_object_id(), ip_hidden_posts(), true ) ) {
		$to = home_url( '/' );
		if ( is_singular( 'corso' ) ) {
			$tip = function_exists( 'ip_course_tipologia' ) ? ip_course_tipologia() : null;
			$to  = $tip && ! in_array( (int) $tip->term_id, array_map( 'intval', $v['tipologie'] ), true ) ? get_term_link( $tip ) : ( get_post_type_archive_link( 'corso' ) ? get_post_type_archive_link( 'corso' ) : home_url( '/' ) );
		}
		$to = is_wp_error( $to ) ? home_url( '/' ) : $to;
		if ( ! is_front_page() ) { // La home non rimanda mai a se stessa.
			wp_safe_redirect( $to, 302 );
			exit;
		}
	}
	if ( is_tax( 'tipologia' ) && in_array( get_queried_object_id(), array_map( 'intval', $v['tipologie'] ), true ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'corso' ) ? get_post_type_archive_link( 'corso' ) : home_url( '/' ), 302 );
		exit;
	}
}, 5 );

// Menu: niente voci verso contenuti nascosti (e i loro sottomenu).
add_filter( 'wp_nav_menu_objects', function ( $items ) {
	$posts = ip_hidden_posts();
	$terms = array_map( 'intval', ip_vis()['tipologie'] );
	if ( ! $posts && ! $terms ) {
		return $items;
	}
	$gone = array();
	foreach ( $items as $i => $it ) {
		$dead = ( 'post_type' === $it->type && in_array( (int) $it->object_id, $posts, true ) )
			|| ( 'taxonomy' === $it->type && 'tipologia' === $it->object && in_array( (int) $it->object_id, $terms, true ) )
			|| in_array( (int) $it->menu_item_parent, $gone, true );
		if ( $dead ) {
			$gone[] = (int) $it->ID;
			unset( $items[ $i ] );
		}
	}
	return $items;
} );

add_filter( 'get_pages', function ( $pages ) {
	if ( ! ip_vis_front() || ! ip_vis()['pages'] ) {
		return $pages;
	}
	$hide = array_map( 'intval', ip_vis()['pages'] );
	return array_values( array_filter( $pages, function ( $p ) use ( $hide ) {
		return ! in_array( (int) $p->ID, $hide, true );
	} ) );
} );

// Sitemap: via le tipologie nascoste e ogni categoria rimasta senza corsi visibili.
add_filter( 'wp_sitemaps_taxonomies_query_args', function ( $args, $tax ) {
	$skip = 'tipologia' === $tax ? array_map( 'intval', ip_vis()['tipologie'] ) : array();
	$hide = ip_hidden_posts();
	if ( $hide && in_array( $tax, array( 'tipologia', 'area', 'dipartimento' ), true ) ) {
		foreach ( get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true, 'include' => array() ) ) as $t ) {
			$left = get_posts( array( 'post_type' => 'corso', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'ip_all' => 1, 'post__not_in' => $hide, 'tax_query' => array( array( 'taxonomy' => $tax, 'terms' => $t->term_id ) ) ) );
			if ( ! $left ) {
				$skip[] = (int) $t->term_id;
			}
		}
	}
	if ( $skip ) {
		$args['exclude'] = array_values( array_unique( array_merge( wp_parse_id_list( $args['exclude'] ?? array() ), $skip ) ) );
	}
	return $args;
}, 10, 2 );

// Sezioni del testo dei corsi (le tendine): via quelle nascoste.
add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_singular( 'corso' ) || ! in_the_loop() || false === stripos( $html, '<h2' ) ) {
		return $html;
	}
	$own  = get_post_meta( get_the_ID(), '_ip_vis_sections', true );
	$hide = array_merge( ip_vis()['sections'], is_array( $own ) ? $own : array() );
	if ( ! $hide ) {
		return $html;
	}
	if ( ! preg_match_all( '#<h2\b[^>]*>(.*?)</h2>#is', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}
	$out  = substr( $html, 0, $m[0][0][1] );
	$n    = count( $m[0] );
	for ( $i = 0; $i < $n; $i++ ) {
		$start = $m[0][ $i ][1];
		$end   = $i + 1 < $n ? $m[0][ $i + 1 ][1] : strlen( $html );
		if ( ! in_array( ip_vis_key( $m[1][ $i ][0] ), $hide, true ) ) {
			$out .= substr( $html, $start, $end - $start );
		}
	}
	return $out;
}, 20 );

// WhatsApp spento: niente link, il resto del sito si adatta da solo.
add_filter( 'ip_wa_href', function ( $href ) {
	return ip_site_on( 'whatsapp' ) ? $href : '';
} );

/* ---------------------------------------------------------------------
 * Elenco delle sezioni dei corsi (con quanti corsi le hanno)
 * ------------------------------------------------------------------- */

function ip_vis_course_sections() {
	$c = get_transient( 'ip_vis_sections_list' );
	if ( false !== $c ) {
		return $c;
	}
	$c = array();
	foreach ( get_posts( array( 'post_type' => 'corso', 'post_status' => 'publish', 'posts_per_page' => -1, 'ip_all' => true ) ) as $p ) {
		if ( preg_match_all( '#<h2\b[^>]*>(.*?)</h2>#is', $p->post_content, $m ) ) {
			foreach ( array_unique( array_map( 'ip_vis_key', $m[1] ) ) as $i => $k ) {
				if ( '' === $k ) {
					continue;
				}
				if ( ! isset( $c[ $k ] ) ) {
					$c[ $k ] = array( 'label' => trim( wp_strip_all_tags( html_entity_decode( $m[1][ $i ], ENT_QUOTES, 'UTF-8' ) ) ), 'n' => 0 );
				}
				$c[ $k ]['n']++;
			}
		}
	}
	uasort( $c, function ( $a, $b ) {
		return $b['n'] <=> $a['n'] ?: strcasecmp( $a['label'], $b['label'] );
	} );
	set_transient( 'ip_vis_sections_list', $c, HOUR_IN_SECONDS );
	return $c;
}
add_action( 'save_post_corso', function () {
	delete_transient( 'ip_vis_sections_list' );
} );

/* ---------------------------------------------------------------------
 * Pannello: Infopoint → Visibilità
 * ------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_submenu_page( 'ip-settings', 'Visibilità', 'Visibilità', 'manage_options', 'ip-visibility', 'ip_vis_page' );
}, 11 );

function ip_vis_switch( $name, $value, $checked, $label, $desc = '', $meta = '' ) {
	?>
	<label class="ipv-row" data-ipv-text="<?php echo esc_attr( mb_strtolower( $label . ' ' . $meta ) ); ?>">
		<span class="ipv-t"><strong><?php echo esc_html( $label ); ?></strong><?php if ( $desc ) : ?><small><?php echo wp_kses_post( $desc ); ?></small><?php endif; ?></span>
		<?php if ( $meta ) : ?><span class="ipv-meta"><?php echo esc_html( $meta ); ?></span><?php endif; ?>
		<input type="checkbox" class="ipv-sw" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $checked ); ?> aria-label="<?php echo esc_attr( 'Mostra ' . $label ); ?>">
	</label>
	<?php
}

function ip_vis_page() {
	$v        = ip_vis();
	$tab      = sanitize_key( $_GET['vis'] ?? 'smart' );
	$tabs     = array(
		'smart'    => array( 'Versione essenziale', 'editor-contract' ),
		'corsi'    => array( 'Corsi e tipologie', 'welcome-learn-more' ),
		'sezioni'  => array( 'Sezioni dei corsi', 'list-view' ),
		'scheda'   => array( 'Scheda corso', 'id-alt' ),
		'agev'     => array( 'Agevolazioni e convenzioni', 'tickets-alt' ),
		'pagine'   => array( 'Pagine', 'admin-page' ),
		'home'     => array( 'Home page', 'admin-home' ),
		'sito'     => array( 'Elementi del sito', 'admin-site-alt3' ),
	);
	$tab      = isset( $tabs[ $tab ] ) ? $tab : 'smart';
	$hidden_n = array(
		'smart'   => 0,
		'corsi'   => count( $v['courses'] ) + count( $v['tipologie'] ),
		'sezioni' => count( $v['sections'] ),
		'scheda'  => count( $v['parts'] ),
		'agev'    => count( $v['agev'] ),
		'pagine'  => count( $v['pages'] ),
		'home'    => 0,
		'sito'    => count( $v['site'] ) + ( ip_opt( 'exit_on' ) ? 0 : 1 ),
	);
	?>
	<div class="wrap ip-admin ipa ipv">
		<?php ip_admin_header( 'Visibilità', 'Mostra o nascondi qualsiasi parte del sito con un clic. Niente viene cancellato: ciò che nascondi resta nel pannello e torna visibile quando vuoi.', array( array( 'Vedi il sito', home_url( '/' ), 'ipa-btn-line', true ) ) ); ?>
		<?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success is-dismissible"><p>Visibilità aggiornata. Le modifiche sono già sul sito.</p></div><?php endif; ?>
		<div class="ipa-set">
			<nav class="ipa-side" aria-label="Cosa mostrare">
				<?php foreach ( $tabs as $id => $t ) : ?>
					<a class="ipa-side-a<?php echo $id === $tab ? ' is-on' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ip-visibility&vis=' . $id ) ); ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( $t[1] ); ?>"></span>
						<span class="ipa-side-t"><?php echo esc_html( $t[0] ); ?></span>
						<?php if ( $hidden_n[ $id ] ) : ?><span class="ipv-badge" title="Elementi nascosti"><?php echo (int) $hidden_n[ $id ]; ?></span><?php endif; ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<div class="ipa-main">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ipa-form" data-ipv>
					<input type="hidden" name="action" value="ip_vis_save">
					<input type="hidden" name="vis" value="<?php echo esc_attr( $tab ); ?>">
					<?php wp_nonce_field( 'ip_vis_save' ); ?>
					<?php
					switch ( $tab ) {
						case 'smart':
							ip_vis_tab_smart();
							break;
						case 'corsi':
							ip_vis_tab_corsi( $v );
							break;
						case 'sezioni':
							ip_vis_tab_sezioni( $v );
							break;
						case 'scheda':
							ip_vis_tab_scheda( $v );
							break;
						case 'agev':
							ip_vis_tab_agev( $v );
							break;
						case 'pagine':
							ip_vis_tab_pagine( $v );
							break;
						case 'home':
							ip_vis_tab_home();
							break;
						case 'sito':
							ip_vis_tab_sito( $v );
							break;
					}
					?>
					<div class="ipa-save">
						<span class="ipa-save-t" data-ipa-dirty hidden>Ci sono modifiche non salvate</span>
						<?php submit_button( 'Salva', 'primary large', 'submit', false ); ?>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/** Barra con ricerca e «mostra / nascondi tutto» per una lista. */
function ip_vis_tools( $placeholder ) {
	?>
	<div class="ipv-tools">
		<input type="search" placeholder="<?php echo esc_attr( $placeholder ); ?>" data-ipv-filter>
		<button type="button" class="button" data-ipv-all="1">Mostra tutto</button>
		<button type="button" class="button" data-ipv-all="0">Nascondi tutto</button>
		<label class="ipv-only"><input type="checkbox" data-ipv-only> Solo i nascosti</label>
	</div>
	<?php
}

function ip_vis_tab_smart() {
	$s = ip_smart();
	?>
	<section class="ipa-card ipv-smart<?php echo ( $s['on'] || $s['pages_on'] ) ? ' is-on' : ''; ?>">
		<label class="ipv-row ipv-big">
			<span class="ipv-t"><strong>Versione essenziale dei corsi</strong><small>Ogni corso e percorso mostra tutte le sezioni importanti, accorciate e sempre a tendina, senza quelle accessorie scelte qui sotto, con l’invito a chiedere il dettaglio completo. Il testo intero resta salvato e continua ad aggiornarsi dal sito ufficiale: spegnendo l’interruttore torna tutto com’era.</small></span>
			<input type="checkbox" class="ipv-sw" name="smart_on" value="1" <?php checked( $s['on'] ); ?>>
		</label>
		<label class="ipv-row ipv-big">
			<span class="ipv-t"><strong>Versione essenziale delle pagine informative</strong><small>Tasse, corsi singoli, riconoscimento CFU, studenti stranieri e le altre pagine: ogni sezione resta, accorciata con gli stessi limiti dei corsi, e finisce con l’invito a chiedere il dettaglio. Moduli, schede delle agevolazioni ed elenchi dei corsi restano interi. Privacy, cookie, contatti, ringraziamento e home non vengono mai accorciate.</small></span>
			<input type="checkbox" class="ipv-sw" name="smart_pages_on" value="1" <?php checked( $s['pages_on'] ); ?>>
		</label>
		<input type="hidden" name="smart_form" value="1">
	</section>
	<section class="ipa-card">
		<h3>Sezioni accessorie da togliere</h3>
		<p class="ipa-card-d">Presentazione, obiettivi, destinatari e requisiti, programma, sbocchi, stage, titolo, durata e costi restano <strong>sempre</strong>, al massimo accorciati. Acceso = la sezione accessoria viene tolta; il tema la riconosce dal titolo, anche in inglese.</p>
		<div class="ipv-list">
			<?php foreach ( ip_smart_groups() as $k => $g ) : ?>
				<?php ip_vis_switch( 'smart_drop', $k, in_array( $k, (array) $s['drop'], true ), $g[0] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
	<section class="ipa-card">
		<h3>Quanto testo per sezione</h3>
		<table class="form-table" role="presentation">
			<tr><th><label for="ipv-paras">Paragrafi per sezione</label></th><td><input type="number" id="ipv-paras" name="smart_paras" min="1" max="6" value="<?php echo (int) $s['paras']; ?>" class="small-text"> <span class="description">Un elenco o una tabella contano come un paragrafo.</span></td></tr>
			<tr><th><label for="ipv-items">Voci per elenco</label></th><td><input type="number" id="ipv-items" name="smart_items" min="2" max="20" value="<?php echo (int) $s['items']; ?>" class="small-text"> <span class="description">Anche le righe delle tabelle.</span></td></tr>
			<tr><th><label for="ipv-docs">Documenti da mostrare</label></th><td><input type="number" id="ipv-docs" name="smart_docs" min="0" max="10" value="<?php echo (int) $s['docs']; ?>" class="small-text"> <span class="description">I primi della lista, di solito brochure e «il corso in breve». 0 = nessuno.</span></td></tr>
		</table>
	</section>
	<?php
	$locked = ip_smart_locked_pages();
	$pages  = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post__not_in' => $locked ?: array( 0 ), 'ip_all' => 1, 'suppress_filters' => false ) );
	$pages  = array_filter( $pages, function ( $p ) {
		return 'page-templates/landing.php' !== get_page_template_slug( $p );
	} );
	?>
	<section class="ipa-card">
		<h3>Pagine da accorciare</h3>
		<p class="ipa-card-d">Acceso = la pagina segue la versione essenziale. Spegni le pagine che vuoi lasciare sempre complete.</p>
		<input type="hidden" name="all_smart_page" value="<?php echo esc_attr( implode( ',', wp_list_pluck( $pages, 'ID' ) ) ); ?>">
		<div class="ipv-list">
			<?php foreach ( $pages as $p ) : ?>
				<?php ip_vis_switch( 'smart_page', $p->ID, ! in_array( (int) $p->ID, array_map( 'intval', (array) $s['pages_full'] ), true ), get_the_title( $p ), '', '/' . get_page_uri( $p ) ); ?>
			<?php endforeach; ?>
		</div>
	</section>
	<section class="ipa-card">
		<h3>Parti della scheda da togliere nella versione essenziale</h3>
		<p class="ipa-card-d">Spento = tolto. Si aggiungono a quelle già nascoste nella scheda «Scheda corso».</p>
		<div class="ipv-list">
			<?php foreach ( ip_vis_parts() as $k => $p ) : ?>
				<?php ip_vis_switch( 'smart_keep', $k, ! in_array( $k, (array) $s['parts'], true ), $p[0], $p[1] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

function ip_vis_tab_corsi( $v ) {
	$tips = get_terms( array( 'taxonomy' => 'tipologia', 'hide_empty' => false ) );
	?>
	<section class="ipa-card">
		<h3>Tipologie</h3>
		<p class="ipa-card-d">Spegni una tipologia per nascondere in un colpo tutti i suoi corsi, la sua pagina e la sua voce nei menu.</p>
		<div class="ipv-list">
			<?php foreach ( $tips as $t ) : ?>
				<?php ip_vis_switch( 'show_tip', $t->term_id, ! in_array( (int) $t->term_id, array_map( 'intval', $v['tipologie'] ), true ), $t->name, '', $t->count . ' corsi' ); ?>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="all_tip" value="<?php echo esc_attr( implode( ',', wp_list_pluck( $tips, 'term_id' ) ) ); ?>">
	</section>
	<section class="ipa-card">
		<h3>Corsi</h3>
		<p class="ipa-card-d">Un corso spento sparisce da elenchi, ricerca, menu, landing e mappa del sito; chi apre il suo indirizzo arriva alla pagina della tipologia. Si aggiorna comunque dal sito ufficiale.</p>
		<?php ip_vis_tools( 'Cerca un corso…' ); ?>
		<?php $shown = array(); ?>
		<?php foreach ( $tips as $t ) : ?>
			<?php $ids = get_posts( array( 'post_type' => 'corso', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'ip_all' => true, 'tax_query' => array( array( 'taxonomy' => 'tipologia', 'terms' => $t->term_id ) ) ) ); ?>
			<?php if ( ! $ids ) { continue; } ?>
			<details class="ipv-group" data-ipv-group>
				<summary><?php echo esc_html( $t->name ); ?> <span><?php echo count( $ids ); ?></span><em data-ipv-off></em></summary>
				<div class="ipv-list">
					<?php foreach ( $ids as $p ) : ?>
						<?php
						if ( in_array( (int) $p->ID, $shown, true ) ) {
							continue; // Corso con più tipologie: un solo interruttore.
						}
						$shown[] = (int) $p->ID;
						?>
						<?php ip_vis_switch( 'show_course', $p->ID, ! in_array( (int) $p->ID, array_map( 'intval', $v['courses'] ), true ), html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ), '<a href="' . esc_url( get_permalink( $p ) ) . '" target="_blank">vedi</a> · <a href="' . esc_url( get_edit_post_link( $p ) ) . '">modifica</a>' ); ?>
					<?php endforeach; ?>
				</div>
			</details>
		<?php endforeach; ?>
		<input type="hidden" name="all_course" value="<?php echo esc_attr( implode( ',', $shown ) ); ?>">
	</section>
	<?php
}

function ip_vis_tab_sezioni( $v ) {
	$secs = ip_vis_course_sections();
	?>
	<section class="ipa-card">
		<h3>Sezioni a tendina dei corsi</h3>
		<p class="ipa-card-d">Ogni titolo del testo di un corso diventa una tendina. Spegnendo un titolo lo nascondi <strong>in tutti i corsi</strong> che lo hanno. Per nasconderlo in un solo corso usa il riquadro «Visibilità» nella pagina di quel corso.</p>
		<?php ip_vis_tools( 'Cerca una sezione (es. Direttore, Stage, Programma)…' ); ?>
		<div class="ipv-list ipv-cols">
			<?php foreach ( $secs as $k => $s ) : ?>
				<?php ip_vis_switch( 'show_sec', $k, ! in_array( $k, $v['sections'], true ), $s['label'], '', $s['n'] . ( 1 === $s['n'] ? ' corso' : ' corsi' ) ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		// Titoli nascosti che oggi non compaiono in nessun corso: restano nascosti se tornano.
		foreach ( array_diff( $v['sections'], array_keys( $secs ) ) as $k ) :
			?>
			<input type="hidden" name="keep_sec[]" value="<?php echo esc_attr( $k ); ?>">
		<?php endforeach; ?>
		<input type="hidden" name="all_sec" value="<?php echo esc_attr( wp_json_encode( array_keys( $secs ) ) ); ?>">
	</section>
	<?php
}

function ip_vis_tab_scheda( $v ) {
	?>
	<section class="ipa-card">
		<h3>Parti della scheda corso</h3>
		<p class="ipa-card-d">Valgono per tutti i corsi. Il modulo di richiesta resta sempre: è lo scopo della pagina. Le eccezioni per un singolo corso si impostano nella sua pagina.</p>
		<div class="ipv-list">
			<?php foreach ( ip_vis_parts() as $k => $p ) : ?>
				<?php ip_vis_switch( 'show_part', $k, ! in_array( $k, $v['parts'], true ), $p[0], $p[1] ); ?>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="all_part" value="1">
	</section>
	<?php
}

function ip_vis_tab_agev( $v ) {
	$ag = get_posts( array( 'post_type' => 'agevolazione', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), 'ip_all' => true ) );
	?>
	<section class="ipa-card">
		<h3>Agevolazioni e convenzioni</h3>
		<p class="ipa-card-d">Un’agevolazione spenta sparisce da home, pagine, landing e schede corso, e non viene più usata per la cifra «retta da … al mese». La retta standard resta comunque il riferimento per calcolare i risparmi.</p>
		<?php ip_vis_tools( 'Cerca un’agevolazione…' ); ?>
		<div class="ipv-list">
			<?php foreach ( $ag as $p ) : ?>
				<?php
				$rata = ip_meta( 'rata', $p->ID );
				$det  = 'enti-convenzionati' === get_post_meta( $p->ID, '_ip_fonte', true ) ? substr_count( trim( (string) ip_meta( 'det', $p->ID ) ), "\n" ) . ' enti' : '';
				ip_vis_switch( 'show_agev', $p->ID, ! in_array( (int) $p->ID, array_map( 'intval', $v['agev'] ), true ), html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ), ip_meta( 'dest', $p->ID ) ? esc_html( wp_trim_words( ip_meta( 'dest', $p->ID ), 14 ) ) : '', trim( ( $rata ? '€ ' . $rata . '/mese' : '' ) . ( $det ? ' · ' . $det : '' ), ' ·' ) );
				?>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="all_agev" value="<?php echo esc_attr( implode( ',', wp_list_pluck( $ag, 'ID' ) ) ); ?>">
	</section>
	<?php
}

function ip_vis_tab_pagine( $v ) {
	$pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'menu_order,post_title' ) );
	$lock  = ip_vis_locked_pages();
	?>
	<section class="ipa-card">
		<h3>Pagine</h3>
		<p class="ipa-card-d">Una pagina spenta sparisce da menu, ricerca e mappa del sito; chi apre il suo indirizzo arriva alla home. Home, privacy, cookie policy e ringraziamento non si possono spegnere: servono al sito e alla legge.</p>
		<?php ip_vis_tools( 'Cerca una pagina…' ); ?>
		<div class="ipv-list">
			<?php $listed = array(); ?>
			<?php foreach ( $pages as $p ) : ?>
				<?php
				if ( in_array( (int) $p->ID, $lock, true ) ) {
					continue;
				}
				$listed[] = (int) $p->ID;
				?>
				<?php ip_vis_switch( 'show_page', $p->ID, ! in_array( (int) $p->ID, array_map( 'intval', $v['pages'] ), true ), ( $p->post_parent ? '— ' : '' ) . html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ), '<a href="' . esc_url( get_permalink( $p ) ) . '" target="_blank">' . esc_html( wp_parse_url( get_permalink( $p ), PHP_URL_PATH ) ) . '</a>' ); ?>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="all_page" value="<?php echo esc_attr( implode( ',', $listed ) ); ?>">
	</section>
	<?php
}

function ip_vis_tab_home() {
	$f = ip_settings_flat();
	?>
	<section class="ipa-card">
		<h3>Sezioni della home</h3>
		<p class="ipa-card-d">Attiva le sezioni e cambiane l’ordine con le frecce. Testi e immagini si modificano in <a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=home' ) ); ?>">Impostazioni → Home page</a>.</p>
		<?php
		$sec = $f['sections'];
		ip_settings_field( 'sections', $sec );
		?>
		<input type="hidden" name="home_sections" value="1">
	</section>
	<?php
}

function ip_vis_tab_sito( $v ) {
	?>
	<section class="ipa-card">
		<h3>Elementi del sito</h3>
		<p class="ipa-card-d">Pezzi che compaiono su tutte le pagine.</p>
		<div class="ipv-list">
			<?php foreach ( ip_vis_site() as $k => $s ) : ?>
				<?php ip_vis_switch( 'show_site', $k, 'exit' === $k ? (bool) ip_opt( 'exit_on' ) : ! in_array( $k, $v['site'], true ), $s[0], $s[1] ); ?>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="all_site" value="1">
	</section>
	<?php
}

add_action( 'admin_post_ip_vis_save', function () {
	check_admin_referer( 'ip_vis_save' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	$v    = ip_vis();
	$in   = wp_unslash( $_POST );
	$on   = function ( $k ) use ( $in ) {
		return array_map( 'strval', (array) ( $in[ $k ] ?? array() ) );
	};
	if ( isset( $in['smart_form'] ) ) {
		$v['smart'] = array(
			'on'     => empty( $in['smart_on'] ) ? '' : '1',
			'drop'   => array_values( array_intersect( $on( 'smart_drop' ), array_keys( ip_smart_groups() ) ) ),
			'paras'  => max( 1, min( 6, (int) ( $in['smart_paras'] ?? 2 ) ) ),
			'items'  => max( 2, min( 20, (int) ( $in['smart_items'] ?? 6 ) ) ),
			'docs'   => max( 0, min( 10, (int) ( $in['smart_docs'] ?? 2 ) ) ),
			'parts'  => array_values( array_diff( array_keys( ip_vis_parts() ), $on( 'smart_keep' ) ) ),
			'pages_on'   => empty( $in['smart_pages_on'] ) ? '' : '1',
			'pages_full' => array_values( array_diff( wp_parse_id_list( $in['all_smart_page'] ?? '' ), array_map( 'intval', $on( 'smart_page' ) ) ) ),
		);
	}
	if ( isset( $in['all_tip'] ) ) {
		$all            = wp_parse_id_list( $in['all_tip'] );
		$v['tipologie'] = array_values( array_diff( $all, array_map( 'intval', $on( 'show_tip' ) ) ) );
		delete_transient( 'ip_vis_tipcourses' );
	}
	if ( isset( $in['all_course'] ) ) {
		$all          = wp_parse_id_list( $in['all_course'] ); // Solo i corsi elencati nella scheda.
		$keep         = array_diff( array_map( 'intval', $v['courses'] ), $all );
		$v['courses'] = array_values( array_unique( array_merge( $keep, array_diff( $all, array_map( 'intval', $on( 'show_course' ) ) ) ) ) );
	}
	if ( isset( $in['all_sec'] ) ) {
		$all           = (array) json_decode( (string) $in['all_sec'], true );
		$v['sections'] = array_values( array_unique( array_merge( array_diff( $all, $on( 'show_sec' ) ), array_map( 'ip_vis_key', $on( 'keep_sec' ) ) ) ) );
	}
	if ( isset( $in['all_part'] ) ) {
		$v['parts'] = array_values( array_diff( array_keys( ip_vis_parts() ), $on( 'show_part' ) ) );
	}
	if ( isset( $in['all_agev'] ) ) {
		$all       = wp_parse_id_list( $in['all_agev'] );
		$v['agev'] = array_values( array_diff( array_map( 'intval', $all ), array_map( 'intval', $on( 'show_agev' ) ) ) );
	}
	if ( isset( $in['all_page'] ) ) {
		$all        = array_diff( wp_parse_id_list( $in['all_page'] ), ip_vis_locked_pages() );
		$v['pages'] = array_values( array_diff( $all, array_map( 'intval', $on( 'show_page' ) ) ) );
	}
	if ( isset( $in['all_site'] ) ) {
		$shown     = $on( 'show_site' );
		$v['site'] = array_values( array_diff( array_diff( array_keys( ip_vis_site() ), array( 'exit' ) ), $shown ) );
		$o         = get_option( 'ip_settings', array() );
		$o['exit_on'] = in_array( 'exit', $shown, true ) ? '1' : '';
		update_option( 'ip_settings', $o );
	}
	if ( isset( $in['home_sections'] ) ) {
		$f               = ip_settings_flat();
		$o               = get_option( 'ip_settings', array() );
		$o['sections']   = ip_sanitize_field( $f['sections'], $in['ip_settings']['sections'] ?? array() );
		update_option( 'ip_settings', $o );
	}
	update_option( 'ip_visibility', $v, true );
	wp_safe_redirect( admin_url( 'admin.php?page=ip-visibility&saved=1&vis=' . sanitize_key( $in['vis'] ?? '' ) ) );
	exit;
} );

/* ---------------------------------------------------------------------
 * Riquadro «Visibilità» nella pagina del corso
 * ------------------------------------------------------------------- */

add_action( 'add_meta_boxes_corso', function () {
	add_meta_box( 'ip-vis', 'Visibilità di questo corso', 'ip_vis_course_box', 'corso', 'side', 'high' );
} );

function ip_vis_course_box( $post ) {
	wp_nonce_field( 'ip_vis_course', 'ip_vis_nonce' );
	$v      = ip_vis();
	$hidden = in_array( (int) $post->ID, array_map( 'intval', $v['courses'] ), true );
	$own_s  = (array) get_post_meta( $post->ID, '_ip_vis_sections', true );
	$own_p  = (array) get_post_meta( $post->ID, '_ip_vis_parts', true );
	preg_match_all( '#<h2\b[^>]*>(.*?)</h2>#is', $post->post_content, $m );
	?>
	<p><label><input type="checkbox" name="ip_vis_hide" value="1" <?php checked( $hidden ); ?>> <strong>Nascondi questo corso dal sito</strong></label></p>
	<?php if ( ip_smart()['on'] ) : ?>
		<p><label><input type="checkbox" name="ip_full" value="1" <?php checked( (bool) get_post_meta( $post->ID, '_ip_full', true ) ); ?>> Mostra sempre la <strong>versione completa</strong> di questo corso</label></p>
	<?php endif; ?>
	<?php if ( $m[1] ) : ?>
		<p class="ipv-box-h">Sezioni a tendina da mostrare</p>
		<?php foreach ( array_unique( $m[1] ) as $h ) : ?>
			<?php
			$k      = ip_vis_key( $h );
			$global = in_array( $k, $v['sections'], true );
			?>
			<label class="ipv-box-r"><input type="checkbox" name="ip_vis_show_sec[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( ! $global && ! in_array( $k, $own_s, true ) ); ?> <?php disabled( $global ); ?>> <?php echo esc_html( wp_strip_all_tags( html_entity_decode( $h, ENT_QUOTES, 'UTF-8' ) ) ); ?><?php echo $global ? ' <em>(nascosta in tutti i corsi)</em>' : ''; ?></label>
			<input type="hidden" name="ip_vis_all_sec[]" value="<?php echo esc_attr( $k ); ?>">
		<?php endforeach; ?>
	<?php endif; ?>
	<p class="ipv-box-h">Parti della scheda da mostrare</p>
	<?php foreach ( ip_vis_parts() as $k => $p ) : ?>
		<?php $global = in_array( $k, $v['parts'], true ); ?>
		<label class="ipv-box-r"><input type="checkbox" name="ip_vis_show_part[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( ! $global && ! in_array( $k, $own_p, true ) ); ?> <?php disabled( $global ); ?>> <?php echo esc_html( $p[0] ); ?><?php echo $global ? ' <em>(nascosta in tutti i corsi)</em>' : ''; ?></label>
	<?php endforeach; ?>
	<p class="description">Le regole per tutti i corsi sono in <a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-visibility&vis=sezioni' ) ); ?>">Infopoint → Visibilità</a>.</p>
	<?php
}

add_action( 'save_post_corso', function ( $id ) {
	if ( ! isset( $_POST['ip_vis_nonce'] ) || ! wp_verify_nonce( $_POST['ip_vis_nonce'], 'ip_vis_course' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) ) {
		return;
	}
	$in   = wp_unslash( $_POST );
	$v    = ip_vis();
	$list = array_map( 'intval', $v['courses'] );
	$list = ! empty( $in['ip_vis_hide'] ) ? array_unique( array_merge( $list, array( $id ) ) ) : array_diff( $list, array( $id ) );
	$v['courses'] = array_values( $list );
	update_option( 'ip_visibility', $v, true );
	if ( ip_smart()['on'] ) {
		! empty( $in['ip_full'] ) ? update_post_meta( $id, '_ip_full', '1' ) : delete_post_meta( $id, '_ip_full' );
	}
	$all = array_map( 'strval', (array) ( $in['ip_vis_all_sec'] ?? array() ) );
	update_post_meta( $id, '_ip_vis_sections', array_values( array_diff( $all, array_merge( (array) ( $in['ip_vis_show_sec'] ?? array() ), $v['sections'] ) ) ) );
	update_post_meta( $id, '_ip_vis_parts', array_values( array_diff( array_keys( ip_vis_parts() ), (array) ( $in['ip_vis_show_part'] ?? array() ), $v['parts'] ) ) );
} );

// Nell'elenco dei corsi: chi è nascosto si vede subito.
add_filter( 'display_post_states', function ( $states, $post ) {
	if ( in_array( (int) $post->ID, ip_hidden_posts(), true ) ) {
		$states['ip_hidden'] = 'Nascosto dal sito';
	}
	return $states;
}, 10, 2 );
