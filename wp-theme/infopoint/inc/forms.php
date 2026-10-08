<?php
/**
 * Moduli di contatto nativi: rendering, validazione, salvataggio, notifica.
 * Sostituiscono Elementor Forms / Contact Form 7 + Flamingo + plugin antispam.
 */

defined( 'ABSPATH' ) || exit;

function ip_regions() {
	return array( 'Abruzzo', 'Basilicata', 'Calabria', 'Campania', 'Emilia-Romagna', 'Friuli-Venezia Giulia', 'Lazio', 'Liguria', 'Lombardia', 'Marche', 'Molise', 'Piemonte', 'Puglia', 'Sardegna', 'Sicilia', 'Toscana', 'Trentino-Alto Adige', 'Umbria', "Valle d'Aosta", 'Veneto', 'Estero' );
}

function ip_form_token() {
	$t = time();
	return $t . '.' . substr( hash_hmac( 'sha256', (string) $t, wp_salt( 'nonce' ) ), 0, 20 );
}

/**
 * Stampa un modulo.
 *
 * @param array $a type: info|cfu|callback, course: ID corso, title, text, id.
 */
function ip_form( $a = array() ) {
	static $count = 0;
	$count++;
	$a = wp_parse_args( $a, array(
		'type'   => 'info',
		'course' => 0,
		'title'  => '',
		'text'   => '',
		'button' => '',
	) );
	$GLOBALS['ip_has_form'] = true;

	$type   = in_array( $a['type'], array( 'info', 'cfu', 'callback' ), true ) ? $a['type'] : 'info';
	$course = $a['course'] ? get_post( (int) $a['course'] ) : null;
	$uid    = 'f' . $count;
	$titles = array(
		'info'     => array( 'Ricevi costi e piano di studi', 'Ti rispondiamo entro un giorno lavorativo, anche su WhatsApp se preferisci.', 'Invia la richiesta' ),
		'cfu'      => array( 'Richiedi la prevalutazione gratuita dei CFU', 'Allega il piano di studi o l’autocertificazione degli esami: ti diciamo quanti crediti puoi farti riconoscere.', 'Invia per la valutazione' ),
		'callback' => array( 'Ti richiamiamo noi', 'Lascia il numero: un orientatore ti chiama nella fascia oraria che preferisci.', 'Richiamami' ),
	);
	$title  = $a['title'] ? $a['title'] : $titles[ $type ][0];
	$text   = '' !== $a['text'] ? $a['text'] : $titles[ $type ][1];
	$button = $a['button'] ? $a['button'] : $titles[ $type ][2];
	$priv   = ip_privacy_url();

	$attrs = 1 === $count ? ' id="richiedi"' : '';
	?>
	<div class="lead lead-<?php echo esc_attr( $type ); ?>"<?php echo $attrs; // phpcs:ignore ?>>
		<p class="lead-title"><?php echo esc_html( $title ); ?></p>
		<?php if ( $text ) : ?>
			<p class="lead-text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
		<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" <?php echo 'cfu' === $type ? 'enctype="multipart/form-data"' : ''; ?> data-ip-form novalidate>
			<input type="hidden" name="action" value="ip_lead">
			<input type="hidden" name="ip_type" value="<?php echo esc_attr( $type ); ?>">
			<input type="hidden" name="ip_tk" value="<?php echo esc_attr( ip_form_token() ); ?>">
			<input type="hidden" name="ip_page" value="">
			<input type="hidden" name="ip_attr" value="">
			<?php if ( $course ) : ?>
				<input type="hidden" name="ip_course" value="<?php echo (int) $course->ID; ?>">
			<?php endif; ?>
			<div class="hp" aria-hidden="true"><label>Lascia vuoto <input type="text" name="ip_website" tabindex="-1" autocomplete="off"></label></div>

			<?php if ( 'callback' === $type ) : ?>
				<?php ip_field( $uid, 'nome', 'Nome', 'text', true, array( 'autocomplete' => 'given-name' ) ); ?>
				<?php ip_field( $uid, 'telefono', 'Telefono', 'tel', true, array( 'autocomplete' => 'tel', 'inputmode' => 'tel' ) ); ?>
				<?php ip_select( $uid, 'fascia', 'Quando preferisci essere chiamato?', array( 'Mattina (9–13)', 'Pomeriggio (14–18)', 'Sera (18–20)', 'Indifferente' ), false ); ?>
			<?php else : ?>
				<div class="row2">
					<?php ip_field( $uid, 'nome', 'Nome', 'text', true, array( 'autocomplete' => 'given-name' ) ); ?>
					<?php ip_field( $uid, 'cognome', 'Cognome', 'text', true, array( 'autocomplete' => 'family-name' ) ); ?>
				</div>
				<div class="row2">
					<?php ip_field( $uid, 'telefono', 'Telefono', 'tel', true, array( 'autocomplete' => 'tel', 'inputmode' => 'tel' ) ); ?>
					<?php ip_field( $uid, 'email', 'Email', 'email', true, array( 'autocomplete' => 'email' ) ); ?>
				</div>
				<?php if ( 'cfu' === $type ) : ?>
					<div class="row2">
						<?php ip_field( $uid, 'nascita', 'Data di nascita', 'date', true ); ?>
						<?php ip_select( $uid, 'regione', 'Regione di residenza', ip_regions(), true ); ?>
					</div>
					<?php ip_select( $uid, 'corso_sel', 'Corso a cui vuoi iscriverti', ip_course_options( $course ), true ); ?>
					<div class="field">
						<label for="<?php echo esc_attr( $uid ); ?>-files">Documenti <span class="opt">PDF, JPG, PNG o Word · max 3 file da 5 MB</span></label>
						<input id="<?php echo esc_attr( $uid ); ?>-files" type="file" name="ip_files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
						<small>Piano di studi, certificato o autocertificazione degli esami, eventuali titoli professionali.</small>
					</div>
					<?php ip_textarea( $uid, 'messaggio', 'Note', false, 'Esami sostenuti, università di provenienza, anno di iscrizione…' ); ?>
				<?php else : ?>
					<?php if ( $course ) : ?>
						<p class="lead-course"><?php echo ip_icon( 'doc', 16 ); // phpcs:ignore ?> <span><?php echo esc_html( ip_course_name( $course->ID ) ); ?></span></p>
					<?php else : ?>
						<?php ip_select( $uid, 'interesse', 'Ti interessa', array_filter( array_map( 'trim', explode( "\n", (string) ip_opt( 'form_courses' ) ) ) ), true ); ?>
					<?php endif; ?>
					<details class="more">
						<summary>Aggiungi un messaggio</summary>
						<?php ip_textarea( $uid, 'messaggio', 'Messaggio', false, 'Es. ho già sostenuto alcuni esami, lavoro e studio la sera…' ); ?>
					</details>
				<?php endif; ?>
			<?php endif; ?>

			<div class="check">
				<input type="checkbox" id="<?php echo esc_attr( $uid ); ?>-privacy" name="ip_privacy" value="1" required>
				<label for="<?php echo esc_attr( $uid ); ?>-privacy">Ho letto l’<?php echo $priv ? '<a href="' . esc_url( $priv ) . '" target="_blank" rel="noopener">informativa privacy</a>' : 'informativa privacy'; // phpcs:ignore ?> e acconsento al trattamento dei dati per ricevere le informazioni richieste.</label>
			</div>
			<div class="check">
				<input type="checkbox" id="<?php echo esc_attr( $uid ); ?>-mkt" name="ip_marketing" value="1">
				<label for="<?php echo esc_attr( $uid ); ?>-mkt">Voglio ricevere aggiornamenti su corsi, agevolazioni e scadenze (facoltativo).</label>
			</div>
			<?php $err = 1 === $count && isset( $_GET['ip_err'] ) ? sanitize_text_field( wp_unslash( $_GET['ip_err'] ) ) : ''; ?>
			<div class="form-msg" role="alert"<?php echo $err ? '' : ' hidden'; ?>><?php echo esc_html( $err ); ?></div>
			<button type="submit" class="btn btn-accent btn-block"><?php echo esc_html( $button ); ?></button>
		</form>
		<?php if ( 'callback' !== $type && ip_opt( 'phone1' ) ) : ?>
			<p class="lead-alt">Preferisci parlarne subito? <a href="<?php echo esc_attr( ip_tel_href( ip_opt( 'phone1' ) ) ); ?>" data-track="call"><?php echo esc_html( ip_opt( 'phone1' ) ); ?></a></p>
		<?php endif; ?>
	</div>
	<?php
}

function ip_field( $uid, $name, $label, $type = 'text', $req = false, $extra = array() ) {
	$attrs = '';
	foreach ( $extra as $k => $v ) {
		$attrs .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
	}
	printf(
		'<div class="field"><label for="%1$s-%2$s">%3$s%4$s</label><input id="%1$s-%2$s" type="%5$s" name="ip_%2$s"%6$s%7$s></div>',
		esc_attr( $uid ),
		esc_attr( $name ),
		esc_html( $label ),
		$req ? '' : ' <span class="opt">facoltativo</span>',
		esc_attr( $type ),
		$req ? ' required' : '',
		$attrs // phpcs:ignore
	);
}

function ip_select( $uid, $name, $label, $options, $req = false ) {
	printf( '<div class="field"><label for="%1$s-%2$s">%3$s</label><select id="%1$s-%2$s" name="ip_%2$s"%4$s><option value="">Seleziona…</option>', esc_attr( $uid ), esc_attr( $name ), esc_html( $label ), $req ? ' required' : '' );
	foreach ( $options as $k => $o ) {
		if ( is_array( $o ) ) {
			printf( '<optgroup label="%s">', esc_attr( $k ) );
			foreach ( $o as $oo ) {
				printf( '<option>%s</option>', esc_html( $oo ) );
			}
			echo '</optgroup>';
		} else {
			printf( '<option>%s</option>', esc_html( $o ) );
		}
	}
	echo '</select></div>';
}

function ip_textarea( $uid, $name, $label, $req = false, $ph = '' ) {
	printf( '<div class="field"><label for="%1$s-%2$s">%3$s%4$s</label><textarea id="%1$s-%2$s" name="ip_%2$s" rows="3" placeholder="%5$s"%6$s></textarea></div>', esc_attr( $uid ), esc_attr( $name ), esc_html( $label ), $req ? '' : ' <span class="opt">facoltativo</span>', esc_attr( $ph ), $req ? ' required' : '' );
}

/**
 * Corsi di laurea raggruppati per tipologia (per il modulo CFU).
 */
function ip_course_options( $current = null ) {
	$cache = get_transient( 'ip_course_options' );
	if ( false === $cache ) {
		$cache = array();
		foreach ( ip_tipologie() as $t ) {
			if ( 0 !== strpos( $t->slug, 'laurea' ) && 0 !== strpos( $t->slug, 'master' ) ) {
				continue;
			}
			$ids = get_posts( array(
				'post_type'      => 'corso',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'tax_query'      => array( array( 'taxonomy' => 'tipologia', 'terms' => $t->term_id ) ),
			) );
			foreach ( $ids as $id ) {
				$code                = ip_meta( 'code', $id );
				$cache[ $t->name ][] = ip_course_name( $id ) . ( $code ? ' (' . $code . ')' : '' );
			}
		}
		set_transient( 'ip_course_options', $cache, DAY_IN_SECONDS );
	}
	return $cache ? $cache : array( 'Laurea triennale', 'Laurea magistrale', 'Master' );
}
add_action( 'save_post_corso', function () {
	delete_transient( 'ip_course_options' );
} );

/* ---------------------------------------------------------------------
 * Ricezione
 * ------------------------------------------------------------------- */

add_action( 'admin_post_nopriv_ip_lead', 'ip_handle_lead' );
add_action( 'admin_post_ip_lead', 'ip_handle_lead' );

function ip_handle_lead() {
	$ajax = ! empty( $_SERVER['HTTP_X_IP_AJAX'] );
	$in   = wp_unslash( $_POST );
	$get  = function ( $k ) use ( $in ) {
		return isset( $in[ 'ip_' . $k ] ) ? trim( sanitize_text_field( $in[ 'ip_' . $k ] ) ) : '';
	};

	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$fail = function ( $msg, $code = 400 ) use ( $ajax, $back ) {
		if ( $ajax ) {
			wp_send_json( array( 'ok' => false, 'message' => $msg ), $code );
		}
		wp_safe_redirect( add_query_arg( 'ip_err', rawurlencode( $msg ), $back ) . '#richiedi' );
		exit;
	};

	// Antispam: campo esca, tempo minimo di compilazione, limite per IP.
	if ( '' !== $get( 'website' ) ) {
		$fail( 'Richiesta non valida.' );
	}
	$tk = explode( '.', $get( 'tk' ) );
	if ( 2 !== count( $tk ) || ! hash_equals( substr( hash_hmac( 'sha256', $tk[0], wp_salt( 'nonce' ) ), 0, 20 ), $tk[1] ) || time() - (int) $tk[0] < 3 ) {
		$fail( 'Il modulo è scaduto o è stato inviato troppo in fretta. Ricarica la pagina e riprova.' );
	}
	$ip_hash = substr( hash( 'sha256', ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) . wp_salt() ), 0, 16 );
	$hits    = (int) get_transient( 'ip_rl_' . $ip_hash );
	if ( $hits >= 6 ) {
		$fail( 'Hai inviato troppe richieste. Riprova tra qualche minuto oppure chiamaci.', 429 );
	}
	set_transient( 'ip_rl_' . $ip_hash, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$type  = in_array( $get( 'type' ), array( 'info', 'cfu', 'callback' ), true ) ? $get( 'type' ) : 'info';
	$data  = array(
		'nome'      => $get( 'nome' ),
		'cognome'   => $get( 'cognome' ),
		'telefono'  => $get( 'telefono' ),
		'email'     => sanitize_email( $get( 'email' ) ),
		'interesse' => $get( 'interesse' ),
		'corso'     => $get( 'corso_sel' ),
		'nascita'   => $get( 'nascita' ),
		'regione'   => $get( 'regione' ),
		'fascia'    => $get( 'fascia' ),
		'messaggio' => isset( $in['ip_messaggio'] ) ? sanitize_textarea_field( $in['ip_messaggio'] ) : '',
		'marketing' => $get( 'marketing' ) ? 'sì' : 'no',
		'pagina'    => esc_url_raw( $get( 'page' ) ),
		'origine'   => $get( 'attr' ),
	);
	$course_id = absint( $get( 'course' ) );
	if ( $course_id && 'corso' === get_post_type( $course_id ) ) {
		$data['corso'] = ip_course_name( $course_id );
	}
	if ( ! $data['pagina'] ) {
		$data['pagina'] = esc_url_raw( $back );
	}

	// Validazione.
	if ( strlen( $data['nome'] ) < 2 ) {
		$fail( 'Inserisci il tuo nome.' );
	}
	if ( strlen( preg_replace( '/\D/', '', $data['telefono'] ) ) < 8 ) {
		$fail( 'Inserisci un numero di telefono valido.' );
	}
	if ( 'callback' !== $type && ! is_email( $data['email'] ) ) {
		$fail( 'Inserisci un indirizzo email valido.' );
	}
	if ( ! $get( 'privacy' ) ) {
		$fail( 'Per inviare la richiesta serve il consenso al trattamento dei dati.' );
	}
	if ( preg_match_all( '#https?://#i', $data['messaggio'] ) > 1 ) {
		$fail( 'Il messaggio contiene troppi link.' );
	}

	$files = 'cfu' === $type ? ip_store_uploads( $fail ) : array();

	$title = trim( $data['nome'] . ' ' . $data['cognome'] );
	$what  = $data['corso'] ? $data['corso'] : $data['interesse'];
	$id    = wp_insert_post( array(
		'post_type'   => 'ip_lead',
		'post_status' => 'publish',
		'post_title'  => $title . ( $what ? ' — ' . $what : '' ),
	), true );
	if ( is_wp_error( $id ) ) {
		$fail( 'Non siamo riusciti a registrare la richiesta. Chiamaci, ti rispondiamo subito.', 500 );
	}
	foreach ( $data as $k => $v ) {
		if ( '' !== $v ) {
			update_post_meta( $id, '_lead_' . $k, $v );
		}
	}
	update_post_meta( $id, '_lead_tipo', $type );
	update_post_meta( $id, '_lead_stato', 'nuovo' );
	update_post_meta( $id, '_lead_consenso', current_time( 'mysql' ) . ' · privacy sì · marketing ' . $data['marketing'] );
	if ( $files ) {
		update_post_meta( $id, '_lead_files', $files );
	}

	ip_notify_lead( $id, $type, $data, $files );

	$url = add_query_arg( array( 'grazie' => $type ), ip_thanks_url() );
	if ( $ajax ) {
		wp_send_json( array( 'ok' => true, 'redirect' => $url ) );
	}
	wp_safe_redirect( $url );
	exit;
}

/**
 * Salva gli allegati in una cartella non elencabile, con nome casuale.
 */
function ip_store_uploads( $fail ) {
	if ( empty( $_FILES['ip_files'] ) || ! is_array( $_FILES['ip_files']['name'] ) ) {
		return array();
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$f       = $_FILES['ip_files'];
	$allowed = array(
		'pdf'  => 'application/pdf',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'doc'  => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	);
	$up  = wp_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'ip-leads';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" );
		file_put_contents( $dir . '/index.php', '<?php // Silence.' );
	}
	$sub = $dir . '/' . wp_generate_password( 24, false );
	$out = array();
	$n   = 0;
	foreach ( $f['name'] as $i => $name ) {
		if ( UPLOAD_ERR_NO_FILE === $f['error'][ $i ] ) {
			continue;
		}
		if ( ++$n > 3 ) {
			$fail( 'Puoi allegare al massimo 3 file.' );
		}
		if ( UPLOAD_ERR_OK !== $f['error'][ $i ] || $f['size'][ $i ] > 5 * MB_IN_BYTES ) {
			$fail( 'Ogni file deve pesare al massimo 5 MB.' );
		}
		$check = wp_check_filetype_and_ext( $f['tmp_name'][ $i ], $name, $allowed );
		if ( ! $check['ext'] ) {
			$fail( 'Formato non ammesso: carica PDF, JPG, PNG o Word.' );
		}
		wp_mkdir_p( $sub );
		$target = $sub . '/' . sanitize_file_name( $name );
		if ( ! is_uploaded_file( $f['tmp_name'][ $i ] ) || ! move_uploaded_file( $f['tmp_name'][ $i ], $target ) ) {
			$fail( 'Caricamento non riuscito. Riprova o inviaci i documenti via email.' );
		}
		$out[] = str_replace( trailingslashit( $up['basedir'] ), '', $target );
	}
	return $out;
}

function ip_notify_lead( $id, $type, $data, $files ) {
	$labels = array( 'info' => 'Richiesta informazioni', 'cfu' => 'Prevalutazione CFU', 'callback' => 'Richiesta di richiamata' );
	$to     = array_filter( array_map( 'trim', explode( ',', (string) ip_opt( 'lead_to' ) ) ), 'is_email' );
	if ( ! $to ) {
		$to = array( get_option( 'admin_email' ) );
	}
	$lines = array();
	foreach ( $data as $k => $v ) {
		if ( '' !== $v ) {
			$lines[] = str_pad( ucfirst( $k ) . ':', 12 ) . ' ' . $v;
		}
	}
	$lines[] = '';
	$lines[] = 'Apri nel pannello: ' . admin_url( 'post.php?action=edit&post=' . $id );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $data['email'] ) ) {
		$headers[] = 'Reply-To: ' . trim( $data['nome'] . ' ' . $data['cognome'] ) . ' <' . $data['email'] . '>';
	}
	$up     = wp_upload_dir();
	$attach = array_map( function ( $f ) use ( $up ) {
		return trailingslashit( $up['basedir'] ) . $f;
	}, $files );

	$subject = sprintf( '[%s] %s — %s', $labels[ $type ], trim( $data['nome'] . ' ' . $data['cognome'] ), $data['corso'] ? $data['corso'] : ( $data['interesse'] ? $data['interesse'] : $data['telefono'] ) );
	wp_mail( $to, $subject, implode( "\n", $lines ), $headers, $attach );

	$hook = ip_opt( 'webhook_url' );
	if ( $hook ) {
		wp_remote_post( $hook, array(
			'timeout'  => 3,
			'blocking' => false,
			'headers'  => array( 'Content-Type' => 'application/json' ),
			'body'     => wp_json_encode( array_merge( array( 'id' => $id, 'tipo' => $type, 'data' => current_time( 'c' ) ), $data ) ),
		) );
	}
}
