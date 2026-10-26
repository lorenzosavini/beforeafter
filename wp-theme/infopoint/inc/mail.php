<?php
/**
 * Email: SMTP senza plugin, notifica delle richieste, conferma a chi scrive,
 * esito di ogni invio ed email di prova.
 */

defined( 'ABSPATH' ) || exit;

function ip_mail_from() {
	$from = trim( (string) ip_opt( 'mail_from' ) );
	if ( ! is_email( $from ) ) {
		$from = trim( (string) ip_opt( 'email' ) );
	}
	if ( ! is_email( $from ) && ip_opt( 'smtp_on' ) && is_email( ip_opt( 'smtp_user' ) ) ) {
		$from = ip_opt( 'smtp_user' );
	}
	return is_email( $from ) ? $from : '';
}

function ip_mail_from_name() {
	$n = trim( (string) ip_opt( 'mail_from_name' ) );
	return $n ? $n : ip_opt( 'brand' );
}

add_filter( 'wp_mail_from', function ( $from ) {
	return ip_mail_from() ? ip_mail_from() : $from;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
	return ip_mail_from() ? ip_mail_from_name() : $name;
} );

add_action( 'phpmailer_init', function ( $m ) {
	if ( ! ip_opt( 'smtp_on' ) || ! ip_opt( 'smtp_host' ) ) {
		return;
	}
	$m->isSMTP();
	$m->Host       = trim( (string) ip_opt( 'smtp_host' ) );
	$m->Port       = (int) ip_opt( 'smtp_port' ) ? (int) ip_opt( 'smtp_port' ) : 587;
	$m->SMTPSecure = (string) ip_opt( 'smtp_secure' );
	$m->SMTPAutoTLS = '' !== $m->SMTPSecure;
	$m->Timeout    = 15;
	if ( ip_opt( 'smtp_user' ) ) {
		$m->SMTPAuth = true;
		$m->Username = trim( (string) ip_opt( 'smtp_user' ) );
		$m->Password = defined( 'IP_SMTP_PASS' ) ? IP_SMTP_PASS : (string) ip_opt( 'smtp_pass' );
	}
	if ( ip_mail_from() ) {
		$m->Sender = ip_mail_from(); // Return-Path coerente con il mittente.
	}
} );

/**
 * Invia e restituisce true oppure il messaggio d'errore.
 */
function ip_send( $to, $subject, $html, $headers = array(), $attach = array() ) {
	$err  = '';
	$grab = function ( $e ) use ( &$err ) {
		$err = $e->get_error_message();
	};
	$alt  = function ( $m ) use ( $html ) {
		$m->AltBody = ip_mail_plain( $html );
	};
	add_action( 'wp_mail_failed', $grab );
	add_action( 'phpmailer_init', $alt );
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$ok        = wp_mail( $to, $subject, $html, $headers, $attach );
	remove_action( 'wp_mail_failed', $grab );
	remove_action( 'phpmailer_init', $alt );
	if ( ! $ok ) {
		$err = $err ? $err : 'invio non riuscito';
		update_option( 'ip_mail_error', array( 'time' => current_time( 'mysql' ), 'error' => $err ), false );
		return $err;
	}
	delete_option( 'ip_mail_error' );
	return true;
}

function ip_mail_plain( $html ) {
	$t = preg_replace( '#<style.*?</style>|<head.*?</head>#is', '', $html );
	$t = preg_replace( '#</td>\s*<td[^>]*>#i', ': ', $t );
	$t = preg_replace( '#<(br|/p|/tr|/h[1-6]|/div|/a)[^>]*>#i', "$0\n", $t );
	$t = preg_replace( '#<a [^>]*href="([^"]+)"[^>]*>(.*?)</a>#i', '$2 ($1)', $t );
	$t = html_entity_decode( wp_strip_all_tags( $t ), ENT_QUOTES, 'UTF-8' );
	$t = preg_replace( '/[ \t]+/', ' ', $t );
	$t = preg_replace( '/^ +| +$/m', '', $t );
	return trim( preg_replace( "/\n{3,}/", "\n\n", $t ) );
}

/**
 * Impaginazione comune: tabella compatibile con tutti i programmi di posta.
 *
 * @param string $title   Titolo.
 * @param string $intro   HTML introduttivo.
 * @param array  $groups  array( 'Titolo gruppo' => array( 'Etichetta' => 'valore HTML' ) ).
 * @param array  $buttons array( array( 'etichetta', 'url', 'colore' ) ).
 * @param string $foot    HTML a piè di pagina.
 */
function ip_mail_html( $title, $intro, $groups = array(), $buttons = array(), $foot = '' ) {
	$brand  = ip_opt( 'color_brand' ) ? ip_opt( 'color_brand' ) : '#225e48';
	$accent = ip_opt( 'color_accent' ) ? ip_opt( 'color_accent' ) : '#a0300e';
	ob_start();
	?>
<!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title><?php echo esc_html( $title ); ?></title></head>
<body style="margin:0;padding:0;background:#eef2f0;font-family:Arial,Helvetica,sans-serif;color:#1b2420">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f0"><tr><td align="center" style="padding:24px 12px">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:10px;overflow:hidden">
	<tr><td style="background:<?php echo esc_attr( $brand ); ?>;padding:18px 26px;color:#ffffff;font-size:15px;font-weight:bold"><?php echo esc_html( ip_opt( 'brand' ) ); ?></td></tr>
	<tr><td style="padding:26px 26px 8px">
		<h1 style="margin:0 0 12px;font-size:21px;line-height:1.3;color:#0e261d"><?php echo esc_html( $title ); ?></h1>
		<div style="font-size:15px;line-height:1.6;color:#33413b"><?php echo $intro; // phpcs:ignore ?></div>
	</td></tr>
	<?php if ( $buttons ) : ?>
	<tr><td style="padding:10px 26px 4px">
		<?php foreach ( $buttons as $b ) : ?>
			<a href="<?php echo esc_attr( $b[1] ); ?>" style="display:inline-block;margin:0 8px 10px 0;padding:11px 18px;border-radius:999px;background:<?php echo esc_attr( isset( $b[2] ) ? $b[2] : $accent ); ?>;color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px"><?php echo esc_html( $b[0] ); ?></a>
		<?php endforeach; ?>
	</td></tr>
	<?php endif; ?>
	<?php foreach ( $groups as $gt => $rows ) : ?>
		<?php
		$rows = array_filter( $rows, function ( $v ) {
			return '' !== trim( wp_strip_all_tags( (string) $v ) );
		} );
		if ( ! $rows ) {
			continue;
		}
		?>
	<tr><td style="padding:14px 26px 0">
		<p style="margin:0 0 6px;font-size:12px;letter-spacing:.06em;text-transform:uppercase;font-weight:bold;color:<?php echo esc_attr( $brand ); ?>"><?php echo esc_html( $gt ); ?></p>
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px">
			<?php foreach ( $rows as $label => $v ) : ?>
			<tr><td style="padding:8px 10px 8px 0;border-top:1px solid #e3e9e6;color:#5b6b64;width:150px;vertical-align:top"><?php echo esc_html( $label ); ?></td><td style="padding:8px 0;border-top:1px solid #e3e9e6;vertical-align:top"><?php echo $v; // phpcs:ignore ?></td></tr>
			<?php endforeach; ?>
		</table>
	</td></tr>
	<?php endforeach; ?>
	<tr><td style="padding:22px 26px 26px;font-size:12px;line-height:1.6;color:#6b7a73"><?php echo $foot; // phpcs:ignore ?></td></tr>
</table>
</td></tr></table>
</body></html>
	<?php
	return ob_get_clean();
}

function ip_mail_legal_foot() {
	$parts = array_filter( array(
		ip_opt( 'brand' ),
		ip_opt( 'address' ) ? preg_replace( '/\s*\n\s*/', ', ', trim( ip_opt( 'address' ) ) ) : '',
		ip_opt( 'phone1' ),
		ip_opt( 'email' ),
	) );
	$legal = function_exists( 'ip_legal_line' ) ? ip_legal_line() : '';
	return esc_html( implode( ' · ', $parts ) ) . ( $legal ? '<br>' . esc_html( $legal ) : '' );
}

/**
 * Notifica a voi e conferma a chi ha scritto. Salva l'esito nella richiesta.
 */
function ip_lead_emails( $id, $type, $data, $files ) {
	$labels = array( 'info' => 'Richiesta informazioni', 'cfu' => 'Prevalutazione CFU', 'callback' => 'Richiesta di richiamata' );
	$name   = trim( $data['nome'] . ' ' . $data['cognome'] );
	$what   = $data['corso'] ? $data['corso'] : $data['interesse'];
	$log    = array();

	if ( ip_opt( 'notify_admin' ) ) {
		$to = array_filter( array_map( 'trim', explode( ',', (string) ip_opt( 'lead_to' ) ) ), 'is_email' );
		if ( ! $to ) {
			$to = array( get_option( 'admin_email' ) );
		}
		$e    = function ( $k ) use ( $data ) {
			return isset( $data[ $k ] ) ? esc_html( $data[ $k ] ) : '';
		};
		$tel  = $data['telefono'] ? '<a href="' . esc_attr( ip_tel_href( $data['telefono'] ) ) . '" style="color:#0e261d;font-weight:bold">' . $e( 'telefono' ) . '</a>' : '';
		$mail = is_email( $data['email'] ) ? '<a href="mailto:' . esc_attr( $data['email'] ) . '" style="color:#0e261d">' . $e( 'email' ) . '</a>' : '';
		$page = $data['pagina'] ? '<a href="' . esc_url( $data['pagina'] ) . '" style="color:#0e261d">' . esc_html( ip_lead_short_url( $data['pagina'] ) ) . '</a>' : '';
		$attr = ip_lead_attr_rows( $id );
		$wa   = preg_replace( '/\D/', '', $data['telefono'] );
		$wa   = 10 === strlen( $wa ) && '3' === $wa[0] ? '39' . $wa : $wa;

		$buttons = array();
		if ( $data['telefono'] ) {
			$buttons[] = array( 'Chiama ' . $data['telefono'], ip_tel_href( $data['telefono'] ) );
		}
		if ( $wa && '3' === substr( $wa, 2, 1 ) ) {
			$buttons[] = array( 'WhatsApp', 'https://wa.me/' . $wa, '#1fa855' );
		}
		$buttons[] = array( 'Apri nel pannello', admin_url( 'admin.php?page=ip-leads&lead=' . $id ), '#0e261d' );

		$groups = array(
			'Contatto'   => array(
				'Nome'        => esc_html( $name ),
				'Telefono'    => $tel,
				'Email'       => $mail,
				'Città'       => $e( 'citta' ),
				'Regione'     => $e( 'regione' ),
				'Anno di nascita' => $e( 'nascita' ),
				'Quando chiamare' => $e( 'fascia' ),
			),
			'Richiesta'  => array(
				'Tipo'        => esc_html( $labels[ $type ] ),
				'Corso'       => $e( 'corso' ),
				'Interesse'   => $data['corso'] ? '' : $e( 'interesse' ),
				'Messaggio'   => nl2br( $e( 'messaggio' ) ),
				'Allegati'    => $files ? esc_html( count( $files ) . ' file (nel pannello e in allegato)' ) : '',
				'Marketing'   => 'sì' === $data['marketing'] ? 'Acconsente agli aggiornamenti' : 'Non acconsente',
			),
			'Da dove arriva' => array_merge( array(
				'Pagina di invio' => $page,
				'Landing'         => $e( 'landing' ),
			), $attr ),
		);
		$headers = array();
		if ( is_email( $data['email'] ) ) {
			$headers[] = 'Reply-To: ' . $name . ' <' . $data['email'] . '>';
		}
		$up     = wp_upload_dir();
		$attach = array_map( function ( $f ) use ( $up ) {
			return trailingslashit( $up['basedir'] ) . $f;
		}, $files );
		$source  = get_post_meta( $id, '_lead_utm_source', true );
		$subject = sprintf( '[%s] %s%s%s', $labels[ $type ], $name, $what ? ' · ' . $what : '', $source ? ' · ' . $source : '' );
		$html    = ip_mail_html(
			'Nuova richiesta da ' . $name,
			'<p style="margin:0">Ricevuta il ' . esc_html( wp_date( 'j F Y \a\l\l\e H:i' ) ) . '. ' . ( $data['fascia'] ? 'Preferisce essere chiamato: <strong>' . esc_html( $data['fascia'] ) . '</strong>.' : 'Ricontattalo il prima possibile.' ) . '</p>',
			$groups,
			$buttons,
			'Email automatica del sito ' . esc_html( home_url() ) . '. Rispondendo scrivi direttamente a ' . ( $mail ? esc_html( $data['email'] ) : 'voi stessi: la persona non ha lasciato l’email' ) . '.'
		);
		$r     = ip_send( $to, $subject, $html, $headers, $attach );
		$log[] = true === $r ? 'Notifica inviata a ' . implode( ', ', $to ) : 'Notifica NON inviata: ' . $r;
	}

	if ( ip_opt( 'autoreply_on' ) && is_email( $data['email'] ) ) {
		$repl = array(
			'{nome}'     => $data['nome'],
			'{corso}'    => $what ? $what : 'il percorso che ti interessa',
			'{brand}'    => ip_opt( 'brand' ),
			'{telefono}' => ip_opt( 'phone1' ),
			'{orari}'    => preg_replace( '/\s*\n\s*/', ', ', trim( (string) ip_opt( 'hours' ) ) ),
		);
		$body = strtr( (string) ip_opt( 'autoreply_text' ), $repl );
		$body = '<p style="margin:0 0 14px">' . nl2br( esc_html( trim( $body ) ) ) . '</p>';
		$body = str_replace( "\n<br />\n", '</p><p style="margin:0 0 14px">', preg_replace( '#(<br />\s*){2,}#', "\n<br />\n", $body ) );

		$buttons = array();
		if ( ip_opt( 'phone1' ) ) {
			$buttons[] = array( 'Chiama ' . ip_opt( 'phone1' ), ip_tel_href( ip_opt( 'phone1' ) ) );
		}
		if ( ip_wa_href() ) {
			$buttons[] = array( 'Scrivici su WhatsApp', ip_wa_href(), '#1fa855' );
		}
		$recap = array(
			'La tua richiesta' => array(
				'Nome'      => esc_html( $name ),
				'Telefono'  => esc_html( $data['telefono'] ),
				'Corso'     => esc_html( $what ),
				'Messaggio' => nl2br( esc_html( $data['messaggio'] ) ),
			),
			'Dove trovarci' => array(
				'Sede'   => nl2br( esc_html( (string) ip_opt( 'address' ) ) ),
				'Orari'  => nl2br( esc_html( (string) ip_opt( 'hours' ) ) ),
				'Email'  => esc_html( (string) ip_opt( 'email' ) ),
			),
		);
		$privacy = function_exists( 'ip_privacy_url' ) ? ip_privacy_url() : '';
		$foot    = ip_mail_legal_foot() . '<br>Ricevi questa email perché hai compilato un modulo sul nostro sito.' . ( $privacy ? ' <a href="' . esc_url( $privacy ) . '" style="color:#6b7a73">Informativa privacy</a>' : '' );
		$subject = strtr( (string) ip_opt( 'autoreply_subject' ), $repl );
		$headers = array();
		$reply   = array_filter( array_map( 'trim', explode( ',', (string) ip_opt( 'lead_to' ) ) ), 'is_email' );
		if ( is_email( ip_opt( 'email' ) ) ) {
			$headers[] = 'Reply-To: ' . ip_opt( 'brand' ) . ' <' . ip_opt( 'email' ) . '>';
		} elseif ( $reply ) {
			$headers[] = 'Reply-To: ' . reset( $reply );
		}
		$r     = ip_send( $data['email'], $subject, ip_mail_html( $subject, $body, $recap, $buttons, $foot ), $headers );
		$log[] = true === $r ? 'Conferma inviata a ' . $data['email'] : 'Conferma NON inviata: ' . $r;
	} elseif ( ip_opt( 'autoreply_on' ) ) {
		$log[] = 'Conferma non inviata: la persona non ha lasciato l’email';
	}

	if ( $log ) {
		update_post_meta( $id, '_lead_invio', wp_date( 'j/m/Y H:i' ) . ' · ' . implode( ' · ', $log ) );
	}
}

/* ---------------------------------------------------------------------
 * Email di prova e avviso di invii non riusciti
 * ------------------------------------------------------------------- */

add_action( 'ip_settings_after', function ( $tab ) {
	if ( 'email' !== $tab ) {
		return;
	}
	$to = array_filter( array_map( 'trim', explode( ',', (string) ip_opt( 'lead_to' ) ) ), 'is_email' );
	$to = $to ? implode( ', ', $to ) : get_option( 'admin_email' );
	?>
	<section class="ipa-card">
		<h3>Prova l’invio</h3>
		<p class="ipa-card-d">Salva prima le impostazioni, poi invia un’email di prova a <strong><?php echo esc_html( $to ); ?></strong>. Se non arriva entro qualche minuto, controlla lo spam e i dati SMTP.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ip_mail_test">
			<?php wp_nonce_field( 'ip_mail_test' ); ?>
			<?php submit_button( 'Invia un’email di prova', 'secondary', 'submit', false ); ?>
		</form>
	</section>
	<?php
} );

add_action( 'admin_post_ip_mail_test', function () {
	check_admin_referer( 'ip_mail_test' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	$to = array_filter( array_map( 'trim', explode( ',', (string) ip_opt( 'lead_to' ) ) ), 'is_email' );
	$to = $to ? $to : array( get_option( 'admin_email' ) );
	$r  = ip_send(
		$to,
		'Prova di invio dal sito ' . ip_opt( 'brand' ),
		ip_mail_html( 'Le email del sito funzionano', '<p style="margin:0">Se leggi questo messaggio, le notifiche delle richieste e le conferme ai contatti arrivano correttamente.</p>', array( 'Configurazione' => array(
			'Mittente' => esc_html( ip_mail_from_name() . ' <' . ( ip_mail_from() ? ip_mail_from() : 'predefinito di WordPress' ) . '>' ),
			'Metodo'   => esc_html( ip_opt( 'smtp_on' ) && ip_opt( 'smtp_host' ) ? 'SMTP ' . ip_opt( 'smtp_host' ) . ':' . ip_opt( 'smtp_port' ) : 'funzione mail del server (senza SMTP)' ),
		) ), array(), ip_mail_legal_foot() )
	);
	$msg = true === $r ? 'ok' : rawurlencode( $r );
	wp_safe_redirect( admin_url( 'admin.php?page=ip-settings&tab=email&ip_mail=' . $msg ) );
	exit;
} );

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_GET['ip_mail'] ) ) {
		$v = sanitize_text_field( wp_unslash( $_GET['ip_mail'] ) );
		if ( 'ok' === $v ) {
			echo '<div class="notice notice-success"><p><strong>Email di prova inviata.</strong> Controlla la casella (e lo spam).</p></div>';
		} else {
			echo '<div class="notice notice-error"><p><strong>Invio non riuscito:</strong> ' . esc_html( $v ) . '</p><p>Controlla server, porta, cifratura, utente e password SMTP.</p></div>';
		}
		return;
	}
	$err    = get_option( 'ip_mail_error' );
	$screen = get_current_screen();
	if ( $err && $screen && ( 'edit-ip_lead' === $screen->id || 'dashboard' === $screen->id ) ) {
		printf(
			'<div class="notice notice-error"><p><strong>Le email del sito non partono</strong> (ultimo errore il %s: %s). Le richieste sono comunque salvate qui. <a href="%s">Configura l’SMTP</a></p></div>',
			esc_html( $err['time'] ),
			esc_html( $err['error'] ),
			esc_url( admin_url( 'admin.php?page=ip-settings&tab=email' ) )
		);
	}
} );
