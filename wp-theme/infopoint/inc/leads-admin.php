<?php
/**
 * Archivio richieste nel pannello: elenco, stato, allegati, esportazione CSV.
 */

defined( 'ABSPATH' ) || exit;

function ip_lead_states() {
	return array(
		'nuovo'       => 'Da contattare',
		'contattato'  => 'Contattato',
		'richiamare'  => 'Da richiamare',
		'iscritto'    => 'Iscritto',
		'perso'       => 'Non interessato',
	);
}

add_action( 'init', function () {
	register_post_type( 'ip_lead', array(
		'labels'              => array(
			'name'          => 'Richieste',
			'singular_name' => 'Richiesta',
			'edit_item'     => 'Richiesta',
			'search_items'  => 'Cerca richieste',
			'not_found'     => 'Ancora nessuna richiesta',
			'all_items'     => 'Tutte le richieste',
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_rest'        => false,
		'exclude_from_search' => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 3,
		'supports'            => array( 'title' ),
		'capability_type'     => 'post',
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'        => true,
	) );
} );

// Contatore delle richieste da gestire accanto alla voce di menu.
add_action( 'admin_menu', function () {
	global $menu;
	$n = get_transient( 'ip_new_leads' );
	if ( false === $n ) {
		$q = new WP_Query( array(
			'post_type'      => 'ip_lead',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_lead_stato',
			'meta_value'     => 'nuovo',
		) );
		$n = (int) $q->found_posts;
		set_transient( 'ip_new_leads', $n, 5 * MINUTE_IN_SECONDS );
	}
	if ( ! $n ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( 'edit.php?post_type=ip_lead' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $n . '</span></span>';
		}
	}
}, 99 );
add_action( 'added_post_meta', 'ip_flush_lead_count' );
add_action( 'updated_post_meta', 'ip_flush_lead_count' );
function ip_flush_lead_count() {
	delete_transient( 'ip_new_leads' );
}

add_filter( 'manage_ip_lead_posts_columns', function () {
	return array(
		'cb'        => '<input type="checkbox">',
		'title'     => 'Nome e interesse',
		'ip_tel'    => 'Telefono',
		'ip_email'  => 'Email',
		'ip_tipo'   => 'Tipo',
		'ip_stato'  => 'Stato',
		'ip_origin' => 'Origine',
		'date'      => 'Ricevuta',
	);
} );

add_action( 'manage_ip_lead_posts_custom_column', function ( $col, $id ) {
	$m = function ( $k ) use ( $id ) {
		return (string) get_post_meta( $id, '_lead_' . $k, true );
	};
	switch ( $col ) {
		case 'ip_tel':
			printf( '<a href="%s">%s</a>', esc_attr( ip_tel_href( $m( 'telefono' ) ) ), esc_html( $m( 'telefono' ) ) );
			if ( $m( 'fascia' ) ) {
				echo '<br><small>' . esc_html( $m( 'fascia' ) ) . '</small>';
			}
			break;
		case 'ip_email':
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $m( 'email' ) ) );
			break;
		case 'ip_tipo':
			$t = array( 'info' => 'Informazioni', 'cfu' => 'Valutazione CFU', 'callback' => 'Richiamata' );
			echo esc_html( isset( $t[ $m( 'tipo' ) ] ) ? $t[ $m( 'tipo' ) ] : $m( 'tipo' ) );
			if ( $m( 'files' ) || get_post_meta( $id, '_lead_files', true ) ) {
				echo ' <span class="dashicons dashicons-paperclip" title="Con allegati"></span>';
			}
			break;
		case 'ip_stato':
			$s = ip_lead_states();
			$v = $m( 'stato' ) ? $m( 'stato' ) : 'nuovo';
			printf( '<span class="ip-st ip-st-%s">%s</span>', esc_attr( $v ), esc_html( isset( $s[ $v ] ) ? $s[ $v ] : $v ) );
			break;
		case 'ip_origin':
			$p = $m( 'pagina' );
			echo $p ? '<a href="' . esc_url( $p ) . '" target="_blank">' . esc_html( wp_parse_url( $p, PHP_URL_PATH ) ) . '</a>' : '';
			if ( $m( 'landing' ) ) {
				echo '<br><small>Landing: ' . esc_html( $m( 'landing' ) ) . '</small>';
			}
			if ( $m( 'origine' ) ) {
				echo '<br><small>' . esc_html( $m( 'origine' ) ) . '</small>';
			}
			break;
	}
}, 10, 2 );

add_action( 'admin_head-edit.php', function () {
	if ( 'ip_lead' !== get_current_screen()->post_type ) {
		return;
	}
	echo '<style>.ip-st{display:inline-block;padding:2px 8px;border-radius:2px;background:#f0f0f1;font-size:12px}.ip-st-nuovo{background:#fcf0e3;color:#8a3a00;font-weight:600}.ip-st-iscritto{background:#e6f4ea;color:#1e6b34}.ip-st-richiamare{background:#fff8c5}.column-ip_tipo{width:130px}.column-ip_stato{width:130px}</style>';
} );

// Filtro per stato sopra l'elenco.
add_action( 'restrict_manage_posts', function ( $pt ) {
	if ( 'ip_lead' !== $pt ) {
		return;
	}
	$cur = isset( $_GET['ip_stato'] ) ? sanitize_key( $_GET['ip_stato'] ) : '';
	echo '<select name="ip_stato"><option value="">Tutti gli stati</option>';
	foreach ( ip_lead_states() as $k => $l ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $cur, $k, false ), esc_html( $l ) );
	}
	echo '</select>';
	printf( '<a class="button" href="%s">Esporta CSV</a>', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ip_export_leads' ), 'ip_export' ) ) );
} );
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() && $q->is_main_query() && 'ip_lead' === $q->get( 'post_type' ) && ! empty( $_GET['ip_stato'] ) ) {
		$q->set( 'meta_key', '_lead_stato' );
		$q->set( 'meta_value', sanitize_key( $_GET['ip_stato'] ) );
	}
} );

// Scheda della singola richiesta.
add_action( 'add_meta_boxes_ip_lead', function () {
	remove_meta_box( 'submitdiv', 'ip_lead', 'side' );
	add_meta_box( 'ip-lead-data', 'Dati della richiesta', 'ip_lead_box', 'ip_lead', 'normal', 'high' );
	add_meta_box( 'ip-lead-state', 'Gestione', 'ip_lead_state_box', 'ip_lead', 'side', 'high' );
} );

function ip_lead_box( $post ) {
	$keys = array( 'nome', 'cognome', 'telefono', 'email', 'interesse', 'corso', 'nascita', 'regione', 'fascia', 'messaggio', 'marketing', 'consenso', 'landing', 'pagina', 'origine' );
	echo '<table class="widefat striped"><tbody>';
	foreach ( $keys as $k ) {
		$v = (string) get_post_meta( $post->ID, '_lead_' . $k, true );
		if ( '' === $v ) {
			continue;
		}
		if ( 'telefono' === $k ) {
			$v = '<a href="' . esc_attr( ip_tel_href( $v ) ) . '">' . esc_html( $v ) . '</a>';
		} elseif ( 'email' === $k ) {
			$v = '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>';
		} elseif ( 'pagina' === $k ) {
			$v = '<a href="' . esc_url( $v ) . '" target="_blank">' . esc_html( $v ) . '</a>';
		} else {
			$v = nl2br( esc_html( $v ) );
		}
		printf( '<tr><th style="width:160px">%s</th><td>%s</td></tr>', esc_html( ucfirst( $k ) ), $v ); // phpcs:ignore
	}
	$files = (array) get_post_meta( $post->ID, '_lead_files', true );
	$files = array_filter( $files );
	if ( $files ) {
		echo '<tr><th>Allegati</th><td>';
		foreach ( $files as $i => $f ) {
			printf( '<a class="button" href="%s">%s</a> ', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ip_lead_file&lead=' . $post->ID . '&n=' . $i ), 'ip_file' ) ), esc_html( basename( $f ) ) );
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

function ip_lead_state_box( $post ) {
	wp_nonce_field( 'ip_lead_state', 'ip_lead_nonce' );
	$cur = get_post_meta( $post->ID, '_lead_stato', true );
	echo '<p><label for="ip-lead-stato"><strong>Stato</strong></label><br><select id="ip-lead-stato" name="ip_lead_stato" style="width:100%">';
	foreach ( ip_lead_states() as $k => $l ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $cur, $k, false ), esc_html( $l ) );
	}
	echo '</select></p>';
	printf( '<p><label for="ip-lead-note"><strong>Note interne</strong></label><textarea id="ip-lead-note" name="ip_lead_note" rows="5" style="width:100%%">%s</textarea></p>', esc_textarea( get_post_meta( $post->ID, '_lead_note', true ) ) );
	echo '<p><button class="button button-primary" type="submit">Salva</button> ';
	printf( '<a class="submitdelete" style="float:right;line-height:30px" href="%s">Cestina</a></p>', esc_url( get_delete_post_link( $post->ID ) ) );
	printf( '<p class="description">Ricevuta il %s</p>', esc_html( get_the_date( 'j F Y, H:i', $post ) ) );
}

add_action( 'save_post_ip_lead', function ( $id ) {
	if ( ! isset( $_POST['ip_lead_nonce'] ) || ! wp_verify_nonce( $_POST['ip_lead_nonce'], 'ip_lead_state' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$s = sanitize_key( wp_unslash( $_POST['ip_lead_stato'] ?? 'nuovo' ) );
	if ( isset( ip_lead_states()[ $s ] ) ) {
		update_post_meta( $id, '_lead_stato', $s );
	}
	update_post_meta( $id, '_lead_note', sanitize_textarea_field( wp_unslash( $_POST['ip_lead_note'] ?? '' ) ) );
} );

// Il titolo è generato: non va modificato a mano.
add_action( 'admin_head-post.php', function () {
	if ( 'ip_lead' === get_current_screen()->post_type ) {
		echo '<style>#titlediv #title{background:transparent;border:0;box-shadow:none;font-weight:600;pointer-events:none}#post-body-content{margin-bottom:0}</style>';
	}
} );

add_action( 'admin_post_ip_lead_file', function () {
	check_admin_referer( 'ip_file' );
	$id = absint( $_GET['lead'] ?? 0 );
	if ( ! current_user_can( 'edit_post', $id ) ) {
		wp_die( 'Non autorizzato.' );
	}
	$files = (array) get_post_meta( $id, '_lead_files', true );
	$n     = absint( $_GET['n'] ?? 0 );
	if ( empty( $files[ $n ] ) ) {
		wp_die( 'File non trovato.' );
	}
	$up   = wp_upload_dir();
	$path = realpath( trailingslashit( $up['basedir'] ) . $files[ $n ] );
	if ( ! $path || 0 !== strpos( $path, realpath( $up['basedir'] ) . DIRECTORY_SEPARATOR . 'ip-leads' ) ) {
		wp_die( 'File non trovato.' );
	}
	nocache_headers();
	header( 'Content-Type: ' . ( wp_check_filetype( $path )['type'] ?: 'application/octet-stream' ) );
	header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
} );

add_action( 'admin_post_ip_export_leads', function () {
	check_admin_referer( 'ip_export' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	$keys = array( 'nome', 'cognome', 'telefono', 'email', 'interesse', 'corso', 'nascita', 'regione', 'fascia', 'messaggio', 'marketing', 'tipo', 'stato', 'note', 'landing', 'pagina', 'origine' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="richieste-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM: Excel legge correttamente gli accenti.
	fputcsv( $out, array_merge( array( 'data' ), $keys ), ';' );
	$paged = 1;
	do {
		$ids = get_posts( array( 'post_type' => 'ip_lead', 'posts_per_page' => 500, 'paged' => $paged++, 'fields' => 'ids' ) );
		foreach ( $ids as $id ) {
			$row = array( get_the_date( 'Y-m-d H:i', $id ) );
			foreach ( $keys as $k ) {
				$v = (string) get_post_meta( $id, '_lead_' . $k, true );
				// Evita che Excel interpreti i valori come formule.
				$row[] = preg_match( '/^[=+\-@]/', $v ) ? "'" . $v : $v;
			}
			fputcsv( $out, $row, ';' );
		}
	} while ( count( $ids ) === 500 );
	fclose( $out );
	exit;
} );
