<?php
/**
 * Corsi, piani di studio, tipologie, dipartimenti, aree e agevolazioni.
 * Campi nativi, nessun ACF: tutto si modifica e si aggiunge dal pannello.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'corso', array(
		'labels'        => array(
			'name'          => 'Corsi',
			'singular_name' => 'Corso',
			'add_new'       => 'Aggiungi corso',
			'add_new_item'  => 'Nuovo corso',
			'edit_item'     => 'Modifica corso',
			'all_items'     => 'Tutti i corsi',
			'search_items'  => 'Cerca corsi',
			'not_found'     => 'Nessun corso',
		),
		'public'        => true,
		'has_archive'   => 'corsi',
		'rewrite'       => array( 'slug' => 'corsi', 'with_front' => false ),
		'menu_icon'     => 'dashicons-welcome-learn-more',
		'menu_position' => 5,
		'show_in_rest'  => true,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions', 'custom-fields' ),
	) );

	register_taxonomy( 'tipologia', 'corso', array(
		'labels'            => array( 'name' => 'Tipologie', 'singular_name' => 'Tipologia', 'add_new_item' => 'Nuova tipologia' ),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'tipologia', 'with_front' => false ),
	) );

	register_taxonomy( 'dipartimento', 'corso', array(
		'labels'            => array( 'name' => 'Dipartimenti', 'singular_name' => 'Dipartimento', 'add_new_item' => 'Nuovo dipartimento' ),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'dipartimento', 'with_front' => false ),
	) );

	register_taxonomy( 'area', 'corso', array(
		'labels'            => array( 'name' => 'Aree tematiche', 'singular_name' => 'Area tematica', 'add_new_item' => 'Nuova area' ),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'area', 'with_front' => false ),
	) );

	register_post_type( 'agevolazione', array(
		'labels'             => array(
			'name'          => 'Agevolazioni',
			'singular_name' => 'Agevolazione',
			'add_new'       => 'Aggiungi agevolazione',
			'add_new_item'  => 'Nuova agevolazione',
			'edit_item'     => 'Modifica agevolazione',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=corso',
		'publicly_queryable' => false,
		'supports'           => array( 'title', 'page-attributes' ),
	) );

	register_post_type( 'curriculum', array(
		'labels'             => array(
			'name'          => 'Piani di studio',
			'singular_name' => 'Piano di studio',
			'add_new'       => 'Aggiungi piano di studio',
			'add_new_item'  => 'Nuovo piano di studio (curriculum)',
			'edit_item'     => 'Modifica piano di studio',
			'all_items'     => 'Piani di studio',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=corso',
		'show_in_rest'       => true,
		'publicly_queryable' => false,
		'supports'           => array( 'title', 'editor', 'page-attributes', 'revisions' ),
	) );

	foreach ( array_keys( ip_course_fields() ) as $k ) {
		register_post_meta( 'corso', '_ip_' . $k, array(
			'single'        => true,
			'type'          => 'string',
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
} );

function ip_course_fields() {
	return array(
		'short'    => array( 'Nome breve', 'text', 'Usato negli elenchi e nel modulo. Es. «Economia Aziendale e Management».' ),
		'code'     => array( 'Classe / codice', 'text', 'Es. L-18, LM-51, 1MO.' ),
		'cfu'      => array( 'CFU', 'text', '' ),
		'durata'   => array( 'Durata', 'text', 'Es. 3 anni, 12 mesi, 1500 ore.' ),
		'stato'    => array( 'Iscrizioni', 'select', '', array( 'aperte' => 'Aperte', 'presto' => 'In apertura', 'chiuse' => 'Chiuse' ) ),
		'accesso'  => array( 'Accesso', 'text', 'Es. Libero, senza test d’ingresso.' ),
		'lingua'   => array( 'Lingua', 'text', 'Vuoto = italiano.' ),
		'retta'    => array( 'Costo', 'text', 'Es. € 2.200. Vuoto per le lauree = retta standard e agevolazioni.' ),
		'evidenza' => array( 'Nota in evidenza', 'text', 'Es. Abilitante alla professione di Psicologo.' ),
		'fonte'    => array( 'Pagina ufficiale UniMarconi', 'text', 'Riferimento interno, non mostrato ai visitatori.' ),
		'featured' => array( 'In evidenza in home', 'checkbox', '' ),
		'sync'     => array( 'Aggiornamento automatico', 'select', 'Dal sito ufficiale UniMarconi.', array( '' => 'Aggiorna tutto', 'dati' => 'Solo dati, piani e documenti (non il testo)', 'no' => 'Non aggiornare' ) ),
	);
}

function ip_meta( $key, $post_id = null ) {
	return (string) get_post_meta( $post_id ? $post_id : get_the_ID(), '_ip_' . $key, true );
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ip-course', 'Scheda corso', 'ip_course_box', 'corso', 'side', 'high' );
	add_meta_box( 'ip-agev', 'Dettagli agevolazione', 'ip_agev_box', 'agevolazione', 'normal', 'high' );
	add_meta_box( 'ip-curricula', 'Piani di studio (curricula)', 'ip_curricula_box', 'corso', 'normal', 'high' );
	add_meta_box( 'ip-docs', 'Documenti scaricabili', 'ip_docs_box', 'corso', 'normal', 'default' );
	add_meta_box( 'ip-faq', 'Domande frequenti sul corso', 'ip_faq_box', 'corso', 'normal', 'default' );
	add_meta_box( 'ip-curr-course', 'Corso di appartenenza', 'ip_curr_course_box', 'curriculum', 'side', 'high' );
} );

function ip_docs_box( $post ) {
	echo '<p class="description">Brochure, regolamento didattico, «il corso in breve», bandi… Carica il PDF dalla Libreria media o incolla un link.</p>';
	ip_repeater( 'ip_docs', array( 'label' => array( 'Titolo', 'text' ), 'url' => array( 'File o link', 'media' ) ), ip_meta_rows( 'docs', $post->ID ) );
}

function ip_faq_box( $post ) {
	echo '<p class="description">Mostrate in fondo alla scheda del corso.</p>';
	ip_repeater( 'ip_faq', array( 'domanda' => array( 'Domanda', 'text' ), 'risposta' => array( 'Risposta', 'textarea' ) ), ip_meta_rows( 'faq', $post->ID ) );
}

function ip_meta_rows( $key, $id ) {
	$v = get_post_meta( $id, '_ip_' . $key, true );
	return is_array( $v ) ? $v : array();
}

function ip_course_curricula( $course_id ) {
	return get_posts( array(
		'post_type'      => 'curriculum',
		'posts_per_page' => 50,
		'post_status'    => 'publish',
		'meta_key'       => '_ip_course',
		'meta_value'     => (int) $course_id,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
}

function ip_curricula_box( $post ) {
	$list = get_posts( array( 'post_type' => 'curriculum', 'posts_per_page' => 50, 'post_status' => 'any', 'meta_key' => '_ip_course', 'meta_value' => $post->ID, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
	echo '<p class="description">Ogni curriculum ha il suo piano di studi (tabelle per anno, esami a scelta). Nella scheda pubblica vengono mostrati come elenco apribile.</p>';
	if ( $list ) {
		echo '<ul class="ip-curr-list">';
		foreach ( $list as $c ) {
			printf( '<li><a href="%s"><strong>%s</strong></a> <span>%s · <a href="%s">Modifica</a></span></li>', esc_url( get_edit_post_link( $c->ID ) ), esc_html( $c->post_title ), 'publish' === $c->post_status ? 'pubblicato' : esc_html( $c->post_status ), esc_url( get_edit_post_link( $c->ID ) ) );
		}
		echo '</ul>';
	}
	if ( 'auto-draft' !== $post->post_status ) {
		printf( '<p><a class="button" href="%s">+ Aggiungi curriculum</a></p>', esc_url( admin_url( 'post-new.php?post_type=curriculum&ip_course=' . $post->ID ) ) );
	} else {
		echo '<p>Salva il corso per aggiungere i piani di studio.</p>';
	}
}

function ip_curr_course_box( $post ) {
	wp_nonce_field( 'ip_meta', 'ip_meta_nonce' );
	$cur = (int) get_post_meta( $post->ID, '_ip_course', true );
	if ( ! $cur && isset( $_GET['ip_course'] ) ) {
		$cur = absint( $_GET['ip_course'] );
	}
	$courses = get_posts( array( 'post_type' => 'corso', 'posts_per_page' => 500, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'any' ) );
	echo '<select name="ip_course" style="width:100%"><option value="">— Scegli il corso —</option>';
	foreach ( $courses as $c ) {
		printf( '<option value="%d" %s>%s</option>', (int) $c->ID, selected( $cur, $c->ID, false ), esc_html( $c->post_title ) );
	}
	echo '</select><p class="description">L’ordine tra più curricula si imposta in «Attributi» → Ordine.</p>';
	if ( $cur ) {
		printf( '<p><a href="%s">← Torna al corso</a></p>', esc_url( get_edit_post_link( $cur ) ) );
	}
}

function ip_course_box( $post ) {
	wp_nonce_field( 'ip_meta', 'ip_meta_nonce' );
	echo '<style>.ip-f{margin:0 0 12px}.ip-f label{display:block;font-weight:600;margin-bottom:3px}.ip-f input[type=text],.ip-f select{width:100%}.ip-f small{color:#646970}</style>';
	foreach ( ip_course_fields() as $k => $f ) {
		$v = ip_meta( $k, $post->ID );
		echo '<p class="ip-f">';
		if ( 'checkbox' === $f[1] ) {
			printf( '<label><input type="checkbox" name="ip[%s]" value="1" %s> %s</label>', esc_attr( $k ), checked( $v, '1', false ), esc_html( $f[0] ) );
		} elseif ( 'select' === $f[1] ) {
			printf( '<label for="ip-%1$s">%2$s</label><select id="ip-%1$s" name="ip[%1$s]">', esc_attr( $k ), esc_html( $f[0] ) );
			foreach ( $f[3] as $ok => $ol ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $ok ), selected( $v ? $v : ( 'stato' === $k ? 'aperte' : '' ), $ok, false ), esc_html( $ol ) );
			}
			echo '</select>';
		} else {
			printf( '<label for="ip-%1$s">%2$s</label><input type="text" id="ip-%1$s" name="ip[%1$s]" value="%3$s">', esc_attr( $k ), esc_html( $f[0] ), esc_attr( $v ) );
		}
		if ( $f[2] ) {
			echo '<small>' . esc_html( $f[2] ) . '</small>';
		}
		echo '</p>';
	}
}

function ip_agev_fields() {
	return array(
		'dest'  => array( 'A chi è rivolta', 'text' ),
		'retta' => array( 'Retta annua (€, solo numero)', 'text' ),
		'rata'  => array( 'Rata mensile (€, solo numero)', 'text' ),
		'cond'  => array( 'Condizioni (una per riga)', 'textarea' ),
		'det'   => array( 'Dettagli aggiuntivi (es. elenco enti)', 'textarea' ),
	);
}

function ip_agev_box( $post ) {
	wp_nonce_field( 'ip_meta', 'ip_meta_nonce' );
	echo '<table class="form-table">';
	foreach ( ip_agev_fields() as $k => $f ) {
		$v = ip_meta( $k, $post->ID );
		echo '<tr><th><label for="ip-' . esc_attr( $k ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		if ( 'textarea' === $f[1] ) {
			printf( '<textarea id="ip-%1$s" name="ip[%1$s]" rows="6" class="large-text">%2$s</textarea>', esc_attr( $k ), esc_textarea( $v ) );
		} else {
			printf( '<input type="text" id="ip-%1$s" name="ip[%1$s]" value="%2$s" class="regular-text">', esc_attr( $k ), esc_attr( $v ) );
		}
		echo '</td></tr>';
	}
	echo '</table><p class="description">L’ordine di visualizzazione si imposta da «Attributi» → Ordine.</p>';
}

add_action( 'save_post', function ( $post_id, $post ) {
	if ( ! isset( $_POST['ip_meta_nonce'] ) || ! wp_verify_nonce( $_POST['ip_meta_nonce'], 'ip_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'curriculum' === $post->post_type ) {
		$c = isset( $_POST['ip_course'] ) ? absint( $_POST['ip_course'] ) : 0;
		$c ? update_post_meta( $post_id, '_ip_course', $c ) : delete_post_meta( $post_id, '_ip_course' );
		return;
	}
	if ( 'corso' === $post->post_type ) {
		foreach ( array( 'docs' => array( 'label', 'url' ), 'faq' => array( 'domanda', 'risposta' ) ) as $key => $cols ) {
			$rows = array();
			foreach ( (array) ( $_POST[ 'ip_' . $key ] ?? array() ) as $r ) {
				$r = wp_unslash( (array) $r );
				$clean = array(
					$cols[0] => sanitize_text_field( $r[ $cols[0] ] ?? '' ),
					$cols[1] => 'url' === $cols[1] ? esc_url_raw( $r[ $cols[1] ] ?? '' ) : sanitize_textarea_field( $r[ $cols[1] ] ?? '' ),
				);
				if ( implode( '', $clean ) !== '' ) {
					$rows[] = $clean;
				}
			}
			$rows ? update_post_meta( $post_id, '_ip_' . $key, $rows ) : delete_post_meta( $post_id, '_ip_' . $key );
		}
	}
	$fields = 'corso' === $post->post_type ? ip_course_fields() : ( 'agevolazione' === $post->post_type ? ip_agev_fields() : array() );
	$in     = isset( $_POST['ip'] ) ? wp_unslash( (array) $_POST['ip'] ) : array();
	foreach ( $fields as $k => $f ) {
		$v = isset( $in[ $k ] ) ? $in[ $k ] : '';
		$v = 'textarea' === $f[1] ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
		if ( '' === $v ) {
			delete_post_meta( $post_id, '_ip_' . $k );
		} else {
			update_post_meta( $post_id, '_ip_' . $k, $v );
		}
	}
}, 10, 2 );

// Colonne utili nell'elenco corsi.
add_filter( 'manage_corso_posts_columns', function ( $c ) {
	$new = array();
	foreach ( $c as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['ip_code']  = 'Classe';
			$new['ip_cfu']   = 'CFU';
			$new['ip_stato'] = 'Iscrizioni';
		}
	}
	unset( $new['date'] );
	return $new;
} );
add_action( 'manage_corso_posts_custom_column', function ( $col, $id ) {
	if ( 'ip_code' === $col ) {
		echo esc_html( ip_meta( 'code', $id ) );
	} elseif ( 'ip_cfu' === $col ) {
		echo esc_html( ip_meta( 'cfu', $id ) );
	} elseif ( 'ip_stato' === $col ) {
		echo esc_html( ip_status_label( ip_meta( 'stato', $id ) ) . ( ip_meta( 'featured', $id ) ? ' · in evidenza' : '' ) );
	}
}, 10, 2 );

// Ordine delle tipologie: un numero nel termine, così l'elenco dei corsi
// segue il percorso naturale (triennali → magistrali → master …).
add_action( 'tipologia_add_form_fields', function () {
	echo '<div class="form-field"><label for="ip-ordine">Ordine</label><input type="number" name="ip_ordine" id="ip-ordine" value="10"><p>Numero più basso = mostrata prima.</p></div>';
	echo '<div class="form-field"><label>Immagine</label>';
	ip_image_field( 'ip_immagine', 0 );
	echo '<p>Usata nei riquadri dell’offerta formativa. Vuoto = copertina del primo corso.</p></div>';
} );
add_action( 'tipologia_edit_form_fields', function ( $term ) {
	printf( '<tr class="form-field"><th><label for="ip-ordine">Ordine</label></th><td><input type="number" name="ip_ordine" id="ip-ordine" value="%d"></td></tr>', (int) get_term_meta( $term->term_id, 'ordine', true ) );
	echo '<tr class="form-field"><th>Immagine</th><td>';
	ip_image_field( 'ip_immagine', (int) get_term_meta( $term->term_id, 'immagine', true ) );
	echo '<p class="description">Usata nei riquadri dell’offerta formativa. Vuoto = copertina del primo corso della tipologia.</p></td></tr>';
} );
foreach ( array( 'created_tipologia', 'edited_tipologia' ) as $hook ) {
	add_action( $hook, function ( $term_id ) {
		if ( isset( $_POST['ip_ordine'] ) && current_user_can( 'manage_categories' ) ) {
			update_term_meta( $term_id, 'ordine', (int) $_POST['ip_ordine'] );
			$img = absint( $_POST['ip_immagine'] ?? 0 );
			$img ? update_term_meta( $term_id, 'immagine', $img ) : delete_term_meta( $term_id, 'immagine' );
		}
	} );
}

/**
 * Tipologie ordinate, con il numero di corsi pubblicati.
 */
function ip_tipologie() {
	$terms = get_terms( array( 'taxonomy' => 'tipologia', 'hide_empty' => true ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort( $terms, function ( $a, $b ) {
		return (int) get_term_meta( $a->term_id, 'ordine', true ) <=> (int) get_term_meta( $b->term_id, 'ordine', true );
	} );
	return $terms;
}

function ip_status_label( $s ) {
	$map = array( 'aperte' => 'Iscrizioni aperte', 'presto' => 'In apertura', 'chiuse' => 'Iscrizioni chiuse' );
	return isset( $map[ $s ] ) ? $map[ $s ] : $map['aperte'];
}

// Archivio corsi: tutti in una pagina, ordinati. Il filtro lato client fa il resto.
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'corso' ) || $q->is_tax( array( 'tipologia', 'area', 'dipartimento' ) ) ) {
		$q->set( 'posts_per_page', 300 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$q->set( 'no_found_rows', true );
	}
} );

add_filter( 'manage_curriculum_posts_columns', function ( $c ) {
	$c['ip_course'] = 'Corso';
	return $c;
} );
add_action( 'manage_curriculum_posts_custom_column', function ( $col, $id ) {
	if ( 'ip_course' === $col ) {
		$c = (int) get_post_meta( $id, '_ip_course', true );
		echo $c ? '<a href="' . esc_url( get_edit_post_link( $c ) ) . '">' . esc_html( get_the_title( $c ) ) . '</a>' : '—';
	}
}, 10, 2 );

// Filtro per corso nell'elenco dei piani di studio.
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() && $q->is_main_query() && 'curriculum' === $q->get( 'post_type' ) && ! empty( $_GET['ip_course'] ) ) {
		$q->set( 'meta_key', '_ip_course' );
		$q->set( 'meta_value', absint( $_GET['ip_course'] ) );
	}
} );

// Ordine delle tipologie anche nella schermata dei termini.
add_filter( 'manage_edit-tipologia_columns', function ( $c ) {
	$c['ip_ordine'] = 'Ordine';
	return $c;
} );
add_filter( 'manage_tipologia_custom_column', function ( $out, $col, $term_id ) {
	return 'ip_ordine' === $col ? (string) (int) get_term_meta( $term_id, 'ordine', true ) : $out;
}, 10, 3 );
