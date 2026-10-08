<?php
/**
 * Corsi, tipologie, aree e agevolazioni. Campi nativi: nessun ACF.
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

	register_taxonomy( 'area', 'corso', array(
		'labels'            => array( 'name' => 'Aree / Dipartimenti', 'singular_name' => 'Area', 'add_new_item' => 'Nuova area' ),
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
		'retta'    => array( 'Costo', 'text', 'Es. € 2.760/anno oppure € 1.800. Vuoto = rimanda alle agevolazioni.' ),
		'featured' => array( 'In evidenza in home', 'checkbox', '' ),
	);
}

function ip_meta( $key, $post_id = null ) {
	return (string) get_post_meta( $post_id ? $post_id : get_the_ID(), '_ip_' . $key, true );
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ip-course', 'Scheda corso', 'ip_course_box', 'corso', 'side', 'high' );
	add_meta_box( 'ip-agev', 'Dettagli agevolazione', 'ip_agev_box', 'agevolazione', 'normal', 'high' );
} );

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
				printf( '<option value="%s" %s>%s</option>', esc_attr( $ok ), selected( $v ? $v : 'aperte', $ok, false ), esc_html( $ol ) );
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
} );
add_action( 'tipologia_edit_form_fields', function ( $term ) {
	printf( '<tr class="form-field"><th><label for="ip-ordine">Ordine</label></th><td><input type="number" name="ip_ordine" id="ip-ordine" value="%d"></td></tr>', (int) get_term_meta( $term->term_id, 'ordine', true ) );
} );
foreach ( array( 'created_tipologia', 'edited_tipologia' ) as $hook ) {
	add_action( $hook, function ( $term_id ) {
		if ( isset( $_POST['ip_ordine'] ) && current_user_can( 'manage_categories' ) ) {
			update_term_meta( $term_id, 'ordine', (int) $_POST['ip_ordine'] );
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
	if ( $q->is_post_type_archive( 'corso' ) || $q->is_tax( array( 'tipologia', 'area' ) ) ) {
		$q->set( 'posts_per_page', 300 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$q->set( 'no_found_rows', true );
	}
} );
