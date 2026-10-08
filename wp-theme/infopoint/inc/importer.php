<?php
/**
 * Contenuti iniziali: tipologie, aree, corsi, agevolazioni, pagine, menu.
 * Idempotente: ciò che esiste già (stesso slug) non viene toccato.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_submenu_page( 'ip-settings', 'Contenuti iniziali', 'Contenuti iniziali', 'manage_options', 'ip-import', 'ip_import_page' );
} );

function ip_import_page() {
	$done = null;
	if ( isset( $_POST['ip_import'] ) && check_admin_referer( 'ip_import' ) && current_user_can( 'manage_options' ) ) {
		$done = ip_run_import();
	}
	?>
	<div class="wrap">
		<h1>Contenuti iniziali</h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( implode( ' · ', $done ) ); ?></p></div>
		<?php endif; ?>
		<p>Crea in un clic la struttura del sito: <strong>9 tipologie</strong>, <strong>99 corsi</strong> con classe, CFU e durata, <strong>10 agevolazioni</strong>, le pagine principali (corsi di laurea, master, insegnanti, percorsi abilitanti, agevolazioni, riconoscimento CFU, area studenti, doppia laurea, corsi singoli, sedi d’esame, contatti, grazie, landing), i menu e la home.</p>
		<p>Le schede dei corsi nascono con i soli dati essenziali: aggiungi tu presentazione, sbocchi e piano di studi. Scrivere testi propri, invece di copiarli da altri siti, è anche ciò che premia su Google.</p>
		<p>Gli elementi già presenti con lo stesso indirizzo non vengono modificati: puoi rilanciare l’importazione senza rischi.</p>
		<form method="post">
			<?php wp_nonce_field( 'ip_import' ); ?>
			<?php submit_button( 'Crea i contenuti', 'primary', 'ip_import' ); ?>
		</form>
	</div>
	<?php
}

function ip_import_tipologie() {
	return array(
		'laurea-triennale'                 => array( 'Laurea triennale', 10, '180 CFU in tre anni. Si accede con il diploma di scuola superiore, senza test d’ingresso.' ),
		'laurea-magistrale'                => array( 'Laurea magistrale', 20, '120 CFU in due anni. Si accede con una laurea triennale e la verifica dei requisiti curriculari.' ),
		'laurea-magistrale-a-ciclo-unico'  => array( 'Laurea magistrale a ciclo unico', 30, 'Percorso unico di cinque anni, 300 CFU. Si accede con il diploma di scuola superiore.' ),
		'master-di-i-livello'              => array( 'Master di I livello', 40, 'Formazione specialistica di 60 CFU per chi ha una laurea triennale.' ),
		'master-di-ii-livello'             => array( 'Master di II livello', 50, 'Formazione avanzata di 60 CFU per chi ha una laurea magistrale o a ciclo unico.' ),
		'master-di-i-livello-discipline-per-la-didattica'  => array( 'Master di I livello per la didattica', 60, 'Master annuali per docenti, utili all’aggiornamento e al punteggio nelle graduatorie.' ),
		'master-di-ii-livello-discipline-per-la-didattica' => array( 'Master di II livello per la didattica', 70, 'Master annuali per docenti in possesso di laurea magistrale.' ),
		'corsi-di-perfezionamento'         => array( 'Corsi di perfezionamento', 80, 'Corsi da 1500 ore e 60 CFU per il personale della scuola.' ),
		'certificazioni-informatiche'      => array( 'Certificazioni informatiche', 90, 'Certificazioni sull’uso didattico delle tecnologie.' ),
	);
}

function ip_import_featured() {
	return array( 'psicologia', 'giurisprudenza', 'economia-aziendale-e-management', 'scienze-e-tecniche-psicologiche', 'scienze-giuridiche', 'scienze-motorie-e-sportive', 'ingegneria-informatica', 'scienze-dell-educazione-e-della-formazione' );
}

function ip_run_import() {
	@set_time_limit( 300 ); // phpcs:ignore
	$log = array();

	// Tipologie.
	$tip_ids = array();
	foreach ( ip_import_tipologie() as $slug => $t ) {
		$term = get_term_by( 'slug', $slug, 'tipologia' );
		if ( ! $term ) {
			$r = wp_insert_term( $t[0], 'tipologia', array( 'slug' => $slug, 'description' => $t[2] ) );
			if ( is_wp_error( $r ) ) {
				continue;
			}
			update_term_meta( $r['term_id'], 'ordine', $t[1] );
			$tip_ids[ $slug ] = $r['term_id'];
		} else {
			$tip_ids[ $slug ] = $term->term_id;
		}
	}

	// Corsi.
	$courses  = json_decode( (string) file_get_contents( IP_DIR . '/inc/data/corsi.json' ), true );
	$featured = ip_import_featured();
	$n        = 0;
	foreach ( (array) $courses as $i => $c ) {
		if ( get_page_by_path( $c['slug'], OBJECT, 'corso' ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'   => 'corso',
			'post_status' => 'publish',
			'post_title'  => $c['title'],
			'post_name'   => $c['slug'],
			'menu_order'  => $i,
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$meta = array(
			'short'  => $c['short'] !== $c['title'] ? $c['short'] : '',
			'code'   => $c['code'],
			'cfu'    => $c['cfu'],
			'durata' => $c['durata'],
			'stato'  => 'aperte',
		);
		if ( 0 === strpos( $c['tipologia'], 'laurea' ) ) {
			$meta['accesso'] = 'laurea-magistrale' === $c['tipologia'] ? 'Libero, con verifica dei requisiti' : 'Libero, senza test';
		}
		if ( false !== stripos( $c['title'], 'inglese' ) || false !== stripos( $c['title'], 'english' ) ) {
			$meta['lingua'] = 'Inglese';
		}
		if ( in_array( $c['slug'], $featured, true ) ) {
			$meta['featured'] = '1';
		}
		foreach ( array_filter( $meta ) as $k => $v ) {
			update_post_meta( $id, '_ip_' . $k, $v );
		}
		if ( isset( $tip_ids[ $c['tipologia'] ] ) ) {
			wp_set_object_terms( $id, (int) $tip_ids[ $c['tipologia'] ], 'tipologia' );
		}
		if ( $c['area'] ) {
			wp_set_object_terms( $id, $c['area'], 'area' );
		}
		$n++;
	}
	$log[] = $n . ' corsi creati';
	delete_transient( 'ip_course_options' );

	// Agevolazioni.
	$n = 0;
	foreach ( (array) json_decode( (string) file_get_contents( IP_DIR . '/inc/data/agevolazioni.json' ), true ) as $a ) {
		if ( get_page_by_path( $a['slug'], OBJECT, 'agevolazione' ) ) {
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'agevolazione', 'post_status' => 'publish', 'post_title' => $a['title'], 'post_name' => $a['slug'], 'menu_order' => $a['ordine'] ) );
		if ( $id && ! is_wp_error( $id ) ) {
			foreach ( array( 'dest' => 'destinatari', 'retta' => 'retta', 'rata' => 'rata', 'cond' => 'condizioni' ) as $k => $src ) {
				if ( '' !== $a[ $src ] ) {
					update_post_meta( $id, '_ip_' . $k, $a[ $src ] );
				}
			}
			$n++;
		}
	}
	$log[] = $n . ' agevolazioni create';

	// Pagine.
	$pages = array();
	$n     = 0;
	foreach ( ip_import_pages() as $slug => $p ) {
		$parent   = ! empty( $p['parent'] ) && isset( $pages[ $p['parent'] ] ) ? $pages[ $p['parent'] ] : 0;
		$path     = $parent ? get_post_field( 'post_name', $parent ) . '/' . $slug : $slug;
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
			'post_content' => isset( $p['content'] ) ? $p['content'] : '',
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
	$log[] = $n . ' pagine create';

	// Home statica, pagina news, pagina di ringraziamento.
	if ( 'page' !== get_option( 'show_on_front' ) && isset( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
		if ( isset( $pages['news'] ) ) {
			update_option( 'page_for_posts', $pages['news'] );
		}
	}
	$opts = get_option( 'ip_settings', null );
	if ( ! is_array( $opts ) ) {
		$opts = array();
		foreach ( ip_settings_fields() as $section ) {
			foreach ( $section['fields'] as $k => $f ) {
				$opts[ $k ] = $f[2];
			}
		}
	}
	if ( empty( $opts['thanks_page'] ) && isset( $pages['grazie'] ) ) {
		$opts['thanks_page'] = (string) $pages['grazie'];
		update_option( 'ip_settings', $opts );
	}

	// Menu.
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) ) {
		$menu = wp_create_nav_menu( 'Principale' );
		if ( ! is_wp_error( $menu ) ) {
			$corsi = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Corsi di laurea', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['corsi-di-laurea'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			foreach ( array( 'laurea-triennale', 'laurea-magistrale', 'laurea-magistrale-a-ciclo-unico' ) as $t ) {
				if ( isset( $tip_ids[ $t ] ) ) {
					wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object' => 'tipologia', 'menu-item-object-id' => $tip_ids[ $t ], 'menu-item-type' => 'taxonomy', 'menu-item-parent-id' => $corsi, 'menu-item-status' => 'publish' ) );
				}
			}
			foreach ( array( 'corsi-singoli', 'doppia-laurea' ) as $s ) {
				wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $s ], 'menu-item-type' => 'post_type', 'menu-item-parent-id' => $corsi, 'menu-item-status' => 'publish' ) );
			}
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Master', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['master'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			$ins = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Insegnanti', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['corsi-insegnanti'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['percorsi-abilitanti'], 'menu-item-type' => 'post_type', 'menu-item-parent-id' => $ins, 'menu-item-status' => 'publish' ) );
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Master e corsi per docenti', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['corsi-insegnanti'], 'menu-item-type' => 'post_type', 'menu-item-parent-id' => $ins, 'menu-item-status' => 'publish' ) );
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Agevolazioni', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['convenzioni-e-agevolazioni'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			$stud = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Studenti', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['area-studenti'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			foreach ( array( 'area-studenti' => 'Requisiti e iscrizione', 'riconoscimento-cfu' => '', 'sedi-esame' => '' ) as $s => $title ) {
				wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $title, 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $s ], 'menu-item-type' => 'post_type', 'menu-item-parent-id' => $stud, 'menu-item-status' => 'publish' ) );
			}
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object' => 'page', 'menu-item-object-id' => $pages['contatti'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			$locations['primary'] = $menu;
			$log[]                = 'menu principale creato';
		}
	}
	if ( empty( $locations['footer'] ) ) {
		$menu = wp_create_nav_menu( 'Offerta (piè di pagina)' );
		if ( ! is_wp_error( $menu ) ) {
			foreach ( array( 'laurea-triennale', 'laurea-magistrale', 'laurea-magistrale-a-ciclo-unico', 'master-di-i-livello', 'master-di-ii-livello' ) as $t ) {
				if ( isset( $tip_ids[ $t ] ) ) {
					wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object' => 'tipologia', 'menu-item-object-id' => $tip_ids[ $t ], 'menu-item-type' => 'taxonomy', 'menu-item-status' => 'publish' ) );
				}
			}
			foreach ( array( 'corsi-insegnanti', 'percorsi-abilitanti', 'convenzioni-e-agevolazioni' ) as $s ) {
				wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $s ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			}
			$locations['footer'] = $menu;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	flush_rewrite_rules();
	return $log;
}

/**
 * Pagine con testi originali, in blocchi Gutenberg modificabili.
 */
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
			'excerpt' => 'Master online da 60 CFU in management, marketing, diritto, sanità, comunicazione e tecnologie. Durata di norma 12 mesi.',
			'content' => $sc( '[ip_corsi tipologia="master-di-i-livello,master-di-ii-livello"]' )
				. $h( 'I o II livello: quale scegliere' )
				. $p( 'Il master di I livello è aperto a chi ha una laurea triennale; quello di II livello richiede una laurea magistrale, a ciclo unico o del vecchio ordinamento. Entrambi valgono 60 CFU.' )
				. $p( 'Studenti e laureati UniMarconi, appartenenti alle Forze Armate e dell’Ordine e dipendenti pubblici possono avere condizioni agevolate: <a href="/convenzioni-e-agevolazioni/">vedi le agevolazioni</a>.' ),
		),

		'corsi-insegnanti' => array(
			'title'   => 'Corsi per insegnanti',
			'excerpt' => 'Percorsi abilitanti, master per la didattica, corsi di perfezionamento e certificazioni informatiche per docenti e aspiranti docenti.',
			'content' => $p( 'Per l’abilitazione all’insegnamento nella scuola secondaria trovi tutte le informazioni nella pagina dei <a href="/corsi-insegnanti/percorsi-abilitanti/">percorsi abilitanti 30, 36 e 60 CFU</a>. Qui sotto i corsi annuali per l’aggiornamento professionale.' )
				. $sc( '[ip_corsi tipologia="master-di-i-livello-discipline-per-la-didattica,master-di-ii-livello-discipline-per-la-didattica,corsi-di-perfezionamento,certificazioni-informatiche"]' ),
		),

		'percorsi-abilitanti' => array(
			'title'    => 'Percorsi abilitanti 30, 36 e 60 CFU',
			'parent'   => 'corsi-insegnanti',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'I percorsi di formazione iniziale per ottenere l’abilitazione all’insegnamento nella scuola secondaria di primo e secondo grado (DPCM 4 agosto 2023).',
			'content'  => "<!-- wp:group {\"className\":\"box\"} -->\n<div class=\"wp-block-group box\"><!-- wp:paragraph -->\n<p>Le iscrizioni seguono i bandi dell’Ateneo e restano aperte poche settimane. Lasciaci il tuo contatto: ti avvisiamo all’uscita del prossimo bando e verifichiamo con te quale percorso ti spetta.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->\n\n"
				. $h( 'Percorso da 60 CFU' )
				. $ul( array( 'Laureati con titolo valido per la classe di concorso', 'Iscritti a una laurea magistrale o a ciclo unico con almeno 180 CFU (la prova finale si sostiene dopo la laurea)', 'Laureati con i 24 CFU conseguiti entro il 31 ottobre 2022, che possono chiederne il riconoscimento' ) )
				. $p( 'Accesso a numero programmato, con graduatoria.' )
				. $h( 'Percorsi da 30 CFU' )
				. $ul( array( 'Docenti con almeno 3 anni di servizio negli ultimi 5, di cui almeno uno nella classe di concorso scelta (accesso con graduatoria)', 'Chi ha superato la prova del concorso straordinario (art. 59, c. 9-bis, D.L. 73/2021)', 'Vincitori di concorso senza abilitazione con i requisiti di servizio (accesso libero)', 'Docenti già abilitati su un’altra classe di concorso o grado, o specializzati sul sostegno (art. 13)' ) )
				. $h( 'Percorso da 36 CFU' )
				. $p( 'Riservato ai vincitori di concorso che vi hanno partecipato con i 24 CFU conseguiti entro il 31 ottobre 2022. Accesso libero.' )
				. $h( 'Come si svolgono' )
				. $ul( array( 'Frequenza obbligatoria: almeno il 70% di ogni attività formativa', 'Lezioni online in diretta per non più della metà delle ore, il resto in presenza presso la sede dell’Ateneo a Roma', 'Tirocinio diretto nelle scuole e tirocinio indiretto in presenza', 'Prova finale con prova scritta e lezione simulata' ) )
				. $h( 'Specializzazione sul sostegno' )
				. $p( 'L’Ateneo attiva anche i percorsi di specializzazione sul sostegno da 40 CFU per chi ha almeno tre anni di servizio sul sostegno nello stesso grado. I posti sono limitati e fissati dal Ministero: chiedici le date della prossima edizione.' ),
		),

		'convenzioni-e-agevolazioni' => array(
			'title'   => 'Agevolazioni e convenzioni',
			'excerpt' => 'Rette ridotte per giovani, Forze Armate e dell’Ordine, dipendenti pubblici, sportivi, docenti, famiglie e laureati UniMarconi. Le agevolazioni si applicano all’immatricolazione e non sono retroattive.',
			'content' => $sc( '[ip_agevolazioni]' )
				. $h( 'Enti convenzionati' )
				. $p( 'L’Ateneo ha accordi con aziende, ordini professionali, associazioni e istituti scolastici. Se lavori presso un ente convenzionato puoi avere una retta dedicata: indicaci il tuo datore di lavoro e lo verifichiamo.' )
				. $p( 'Le condizioni sono quelle pubblicate dall’Ateneo e possono cambiare: ti confermiamo sempre l’importo esatto prima dell’iscrizione.' ),
		),

		'riconoscimento-cfu' => array(
			'title'   => 'Riconoscimento CFU',
			'excerpt' => 'Esami già sostenuti, una prima laurea o titoli professionali possono valere crediti e accorciare il percorso. La prevalutazione è gratuita e non ti impegna.',
			'content' => $h( 'Come funziona' )
				. $p( 'Ci invii il piano di studi o l’autocertificazione degli esami con voti e settori disciplinari, insieme a eventuali certificazioni e titoli professionali. La Facoltà valuta quali crediti possono essere riconosciuti sul corso che ti interessa (D.M. 270/04, art. 5, c. 7) e ricevi l’esito via email.' )
				. $h( 'A quale anno puoi iscriverti' )
				. $ul( array( '<strong>II anno</strong>: almeno 30 CFU riconosciuti (tutti i corsi)', '<strong>III anno</strong>: almeno 90 CFU (lauree triennali e ciclo unico)', '<strong>IV anno</strong>: almeno 150 CFU (ciclo unico)', '<strong>V anno</strong>: almeno 210 CFU (ciclo unico)' ) )
				. $p( 'Per le lauree magistrali la stessa procedura serve a verificare i requisiti curriculari: se mancano crediti, si recuperano con i <a href="/corsi-singoli/">corsi singoli</a> prima dell’immatricolazione.' )
				. $sc( '[ip_modulo tipo="cfu"]' ),
		),

		'area-studenti' => array(
			'title'    => 'Requisiti e iscrizione',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'Titoli di accesso, test di ingresso, tempo parziale e trasferimenti: quello che serve sapere prima di immatricolarsi.',
			'content'  => $h( 'Titoli di accesso' )
				. $ul( array( '<strong>Laurea triennale</strong>: diploma di scuola secondaria di secondo grado o titolo estero riconosciuto idoneo', '<strong>Laurea magistrale</strong>: laurea o diploma universitario triennale, o titolo estero riconosciuto idoneo', '<strong>Ciclo unico in Giurisprudenza</strong>: diploma di scuola secondaria di secondo grado' ) )
				. $h( 'Test di ingresso' )
				. $p( 'I corsi sono ad accesso libero. Chi si immatricola senza carriera pregressa sostiene un test non selettivo di verifica delle conoscenze iniziali (art. 6 D.M. 270/04): serve a impostare lo studio, non a escludere.' )
				. $h( 'Studiare a tempo parziale' )
				. $p( 'Se lavori o hai altri impegni puoi chiedere l’iscrizione a tempo parziale, distribuendo gli esami su più anni secondo il regolamento d’Ateneo.' )
				. $h( 'Trasferimenti e passaggi' )
				. $p( 'Arrivi da un’altra università? Con la <a href="/riconoscimento-cfu/">prevalutazione dei CFU</a> sai in anticipo quali esami ti vengono convalidati e a quale anno puoi iscriverti.' )
				. $h( 'Perché UniMarconi' )
				. $ul( array( 'Iscrizioni aperte tutto l’anno', 'Lezioni e materiali online 24 ore su 24', 'Esami in presenza in sedi in tutta Italia', 'Retta rateizzabile fino a 12 mensilità senza interessi', 'Tutor e segreteria durante tutto il percorso', 'Career service e associazione Alumni' ) ),
		),

		'doppia-laurea' => array(
			'title'    => 'Doppia iscrizione',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'Dal 2022 puoi iscriverti contemporaneamente a due corsi universitari (Legge 33/2022, D.M. 930/2022). Ecco quando è possibile.',
			'content'  => $h( 'Combinazioni consentite' )
				. $ul( array( 'Due lauree triennali, due magistrali, oppure una triennale e una magistrale', 'Una laurea e un master, un dottorato o una specializzazione non medica', 'Due master diversi', 'Un corso universitario e un corso AFAM (conservatori, accademie)' ) )
				. $p( 'I due corsi, se di laurea, devono appartenere a classi diverse e differire per almeno due terzi delle attività formative.' )
				. $h( 'Combinazioni non consentite' )
				. $ul( array( 'Due corsi della stessa classe', 'Due corsi di classi diverse ma con meno di due terzi di attività differenti', 'Due corsi entrambi a frequenza obbligatoria', 'Due dottorati o due specializzazioni' ) )
				. $h( 'Come si fa' )
				. $p( 'In fase di pre-immatricolazione si presenta un’autocertificazione dei requisiti, a entrambi gli atenei se diversi. L’immatricolazione resta quella ordinaria. Per i benefici del diritto allo studio si indica una sola delle due iscrizioni.' ),
		),

		'corsi-singoli' => array(
			'title'    => 'Corsi singoli',
			'template' => 'page-templates/sidebar-form.php',
			'excerpt'  => 'Sostieni singoli esami senza iscriverti a un corso di laurea: per integrare i requisiti di una magistrale, per un concorso o per aggiornarti.',
			'content'  => $h( 'A cosa servono' )
				. $ul( array( 'Raggiungere i requisiti curriculari per l’accesso a una laurea magistrale', 'Ottenere i crediti richiesti da concorsi pubblici', 'Aggiornamento culturale e professionale' ) )
				. $h( 'Regole principali' )
				. $ul( array( 'Serve il titolo richiesto dal corso che eroga l’insegnamento', 'L’iscrizione dura un anno e permette di sostenere l’esame negli appelli di quell’anno', 'Massimo 4 esami', 'Non è possibile essere iscritti contemporaneamente a un corso di laurea dello stesso Ateneo', 'Prima del pagamento serve l’autorizzazione dell’ufficio immatricolazioni: ce ne occupiamo noi' ) )
				. $h( 'Costi' )
				. $p( '€ 400 per un insegnamento da 6 CFU, € 450 da 12 CFU. Per Ingegneria € 450 e € 500. Al termine, superato l’esame, puoi chiedere il certificato.' ),
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
