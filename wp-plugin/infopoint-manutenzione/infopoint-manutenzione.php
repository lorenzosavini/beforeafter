<?php
/**
 * Plugin Name: Infopoint Manutenzione
 * Description: Pagina di manutenzione con logo, contatti, pulsanti per chiamare e dati legali. Gli amministratori collegati vedono il sito normalmente.
 * Version: 1.1.0
 * Author: ZDM S.r.l.
 * Requires PHP: 7.4
 * Text Domain: infopoint-manutenzione
 */

defined( 'ABSPATH' ) || exit;

define( 'IPM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Impostazioni con i dati di Infopoint Lanciano già compilati.
 * I campi vuoti prendono il valore dalle impostazioni del tema Infopoint, se attivo.
 */
function ipm_defaults() {
	return array(
		'on'        => '',
		'title'     => 'Stiamo aggiornando il sito',
		'text'      => 'Torniamo online a breve. Nel frattempo siamo a tua disposizione: chiamaci o scrivici su WhatsApp, ti rispondiamo subito.',
		'logo'      => '',
		'brand'     => 'Infopoint Lanciano',
		'role'      => 'Centro di orientamento accreditato dall’Università degli Studi Guglielmo Marconi',
		'phone1'    => '0872 676609',
		'phone2'    => '375 637 3330',
		'whatsapp'  => '375 637 3330',
		'email'     => 'info@unimarconilanciano.it',
		'hours'     => "Lunedì–venerdì\n10:00–13:00 e 15:00–18:00",
		'address'   => "Via Salita della Posta, 13\n66034 Lanciano (CH)",
		'company'   => 'ZDM S.r.l.',
		'vat'       => '02365510698',
		'legal'     => 'Via Venezia, 10, Pescara (PE)',
		'pec'       => 'ZDMSRL@pec-legal.com',
		'retry'     => '2',
		'pass'      => '',
		'pass_days' => '7',
	);
}

function ipm_opt( $k ) {
	$o = get_option( 'ipm_settings', array() );
	$o = array_merge( ipm_defaults(), is_array( $o ) ? $o : array() );
	return isset( $o[ $k ] ) ? $o[ $k ] : '';
}

function ipm_tel( $n ) {
	$n = preg_replace( '/[^0-9+]/', '', (string) $n );
	if ( $n && '+' !== $n[0] ) {
		$n = '+39' . $n;
	}
	return 'tel:' . $n;
}

function ipm_wa( $n ) {
	$n = preg_replace( '/[^0-9]/', '', (string) $n );
	if ( ! $n ) {
		return '';
	}
	if ( 10 === strlen( $n ) && '3' === $n[0] ) {
		$n = '39' . $n;
	}
	return 'https://wa.me/' . $n . '?text=' . rawurlencode( 'Buongiorno, vorrei informazioni sui corsi.' );
}

/** Colore dal tema Infopoint, se presente. */
function ipm_color( $key, $fallback ) {
	$t = get_option( 'ip_settings', array() );
	return is_array( $t ) && ! empty( $t[ $key ] ) && sanitize_hex_color( $t[ $key ] ) ? $t[ $key ] : $fallback;
}

function ipm_logo_html() {
	$id = (int) ipm_opt( 'logo' );
	if ( ! $id && has_custom_logo() ) {
		$id = (int) get_theme_mod( 'custom_logo' );
	}
	if ( $id ) {
		return wp_get_attachment_image( $id, 'medium', false, array( 'class' => 'ipm-logo', 'alt' => esc_attr( ipm_opt( 'brand' ) ) ) );
	}
	return '<span class="ipm-mark" aria-hidden="true">' . esc_html( mb_strtoupper( mb_substr( ipm_opt( 'brand' ), 0, 1 ) ) ) . '</span>';
}

/* ---------------------------------------------------------------------
 * Pagina di manutenzione
 * ------------------------------------------------------------------- */

add_action( 'template_redirect', function () {
	if ( ! ipm_opt( 'on' ) || current_user_can( 'edit_posts' ) || is_robots() || ( function_exists( 'is_favicon' ) && is_favicon() ) ) {
		return;
	}
	if ( ipm_has_access() ) {
		return;
	}
	$err = '';
	// Codice dal modulo della pagina o dal link condiviso (?accesso=CODICE).
	$try = isset( $_POST['ipm_pass'] ) ? (string) wp_unslash( $_POST['ipm_pass'] ) : ( isset( $_GET['accesso'] ) ? (string) wp_unslash( $_GET['accesso'] ) : null );
	if ( null !== $try && '' !== ipm_opt( 'pass' ) ) {
		$ip   = substr( hash( 'sha256', ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) . wp_salt() ), 0, 16 );
		$hits = (int) get_transient( 'ipm_try_' . $ip );
		if ( $hits >= 10 ) {
			$err = 'Troppi tentativi. Riprova tra qualche minuto.';
		} elseif ( hash_equals( (string) ipm_opt( 'pass' ), trim( $try ) ) ) {
			ipm_grant_access();
			wp_safe_redirect( remove_query_arg( 'accesso' ) );
			exit;
		} else {
			set_transient( 'ipm_try_' . $ip, $hits + 1, 15 * MINUTE_IN_SECONDS );
			$err = 'Codice non valido.';
		}
	}
	ipm_render( $err );
	exit;
}, 0 );

/** Impronta legata al codice: se cambi il codice, gli accessi già dati scadono. */
function ipm_access_token() {
	return hash_hmac( 'sha256', 'ipm|' . ipm_opt( 'pass' ), wp_salt( 'auth' ) );
}

function ipm_has_access() {
	return '' !== ipm_opt( 'pass' ) && isset( $_COOKIE['ipm_access'] ) && hash_equals( ipm_access_token(), (string) $_COOKIE['ipm_access'] );
}

function ipm_grant_access() {
	$days = max( 1, min( 90, (int) ipm_opt( 'pass_days' ) ) );
	setcookie( 'ipm_access', ipm_access_token(), array(
		'expires'  => time() + $days * DAY_IN_SECONDS,
		'path'     => COOKIEPATH ? COOKIEPATH : '/',
		'domain'   => COOKIE_DOMAIN,
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	) );
}

// Chi ha il codice vede il sito, ma con un promemoria discreto.
add_action( 'wp_footer', function () {
	if ( ipm_opt( 'on' ) && ! current_user_can( 'edit_posts' ) && ipm_has_access() ) {
		echo '<div style="position:fixed;left:12px;bottom:12px;z-index:99999;padding:6px 12px;border-radius:999px;background:#a0300e;color:#fff;font:600 12px/1.4 system-ui,sans-serif;box-shadow:0 6px 18px rgba(0,0,0,.2)">Anteprima riservata: il sito è in manutenzione</div>';
	}
} );

function ipm_icon( $name ) {
	$p = array(
		'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'whatsapp' => '<path d="M3 21l1.7-4.3A9 9 0 1 1 8 19.6L3 21z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8a4 4 0 0 1-1.8-1.8l.8-1-1-2L9 9.5z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
	);
	return '<svg class="ipm-i" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p[ $name ] . '</svg>';
}

function ipm_render( $err = '' ) {
	$hours = max( 1, (int) ipm_opt( 'retry' ) );
	if ( ! headers_sent() ) {
		status_header( 503 );
		nocache_headers();
		header( 'Retry-After: ' . ( $hours * HOUR_IN_SECONDS ) );
		header( 'Content-Type: text/html; charset=utf-8' );
	}
	$brand  = ipm_color( 'color_brand', '#225e48' );
	$deep   = ipm_color( 'color_dark', '#0e261d' );
	$accent = ipm_color( 'color_accent', '#a0300e' );
	$wa     = ipm_wa( ipm_opt( 'whatsapp' ) );
	$legal  = array_filter( array(
		ipm_opt( 'company' ),
		ipm_opt( 'vat' ) ? 'P.IVA ' . ipm_opt( 'vat' ) : '',
		ipm_opt( 'legal' ) ? 'Sede legale ' . ipm_opt( 'legal' ) : '',
		ipm_opt( 'pec' ) ? 'PEC ' . ipm_opt( 'pec' ) : '',
	) );
	?><!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?php echo esc_html( ipm_opt( 'title' ) . ' · ' . ipm_opt( 'brand' ) ); ?></title>
<?php if ( has_site_icon() ) : ?><link rel="icon" href="<?php echo esc_url( get_site_icon_url( 64 ) ); ?>"><?php endif; ?>
<style>
@font-face { font-family: "Montserrat"; font-weight: 400 800; font-display: swap; src: url("<?php echo esc_url( IPM_URL . 'assets/montserrat-latin-var.woff2' ); ?>") format("woff2"); }
:root { --brand: <?php echo esc_html( $brand ); ?>; --deep: <?php echo esc_html( $deep ); ?>; --accent: <?php echo esc_html( $accent ); ?>; --ink: #1b2420; --muted: #5b6b64; --line: #dfe7e3; }
* { box-sizing: border-box; }
html, body { height: 100%; }
body { margin: 0; display: flex; flex-direction: column; min-height: 100vh; background: var(--deep); color: var(--ink); font: 16px/1.6 Montserrat, system-ui, -apple-system, "Segoe UI", sans-serif; -webkit-font-smoothing: antialiased; }
main { flex: 1; display: grid; place-items: center; padding: 40px 16px; background: radial-gradient(120% 80% at 85% 0%, rgba(255,255,255,.08), transparent 60%), var(--deep); }
.card { width: min(560px, 100%); background: #fff; border-radius: 18px; box-shadow: 0 30px 70px rgba(0,0,0,.28); overflow: hidden; animation: up .5s ease-out both; }
@keyframes up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) { .card { animation: none; } }
.top { padding: 30px 30px 24px; text-align: center; border-bottom: 1px solid var(--line); }
.ipm-logo { display: block; max-width: 220px; max-height: 80px; width: auto; height: auto; margin: 0 auto 14px; }
.ipm-mark { display: inline-grid; place-items: center; width: 56px; height: 56px; margin-bottom: 12px; border-radius: 14px; background: var(--brand); color: #fff; font-weight: 800; font-size: 1.6rem; }
.brand { margin: 0; font-weight: 800; font-size: 1.15rem; color: var(--deep); }
.role { margin: 2px 0 0; color: var(--muted); font-size: .86rem; line-height: 1.45; }
.body { padding: 28px 30px 30px; }
.badge { display: inline-block; margin: 0 0 12px; padding: 3px 12px; border-radius: 999px; background: #fdf0ea; color: var(--accent); font-weight: 700; font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; }
h1 { margin: 0 0 10px; font-size: 1.7rem; line-height: 1.2; color: var(--deep); }
.text { margin: 0 0 22px; color: var(--muted); }
.acts { display: grid; gap: 10px; }
.btn { display: flex; align-items: center; justify-content: center; gap: 10px; min-height: 54px; padding: 12px 18px; border-radius: 999px; font-weight: 700; font-size: 1.02rem; text-decoration: none; transition: transform .15s, box-shadow .15s; }
.btn:hover { transform: translateY(-1px); }
.btn-call { background: var(--accent); color: #fff; box-shadow: 0 10px 22px -10px rgba(160,48,14,.8); }
.btn-wa { background: #1fa855; color: #fff; }
.btn-line { border: 2px solid var(--line); color: var(--deep); }
.btn small { font-weight: 600; opacity: .85; }
.info { display: grid; gap: 12px; margin: 24px 0 0; padding: 18px 0 0; border-top: 1px solid var(--line); font-size: .93rem; }
.info div { display: flex; gap: 12px; align-items: flex-start; }
.info .ipm-i { flex: none; margin-top: 2px; color: var(--brand); }
.info a { color: var(--deep); font-weight: 600; overflow-wrap: anywhere; }
.access { margin: 22px 0 0; padding: 14px 0 0; border-top: 1px solid var(--line); font-size: .9rem; }
.access summary { cursor: pointer; color: var(--muted); font-weight: 600; }
.access form { display: flex; gap: 8px; margin: 12px 0 0; }
.access input { flex: 1; min-width: 0; height: 44px; padding: 0 14px; border: 1px solid #c9d3ce; border-radius: 10px; font: inherit; }
.access input:focus { outline: 2px solid var(--brand); outline-offset: 1px; }
.access button { height: 44px; padding: 0 20px; border: 0; border-radius: 10px; background: var(--brand); color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
.access .err { margin: 8px 0 0; color: var(--accent); font-weight: 600; }
.sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
footer { padding: 18px 16px 22px; color: rgba(255,255,255,.7); text-align: center; font-size: .8rem; line-height: 1.6; }
@media (max-width: 520px) {
	.top { padding: 24px 20px 18px; }
	.body { padding: 22px 20px 24px; }
	h1 { font-size: 1.4rem; }
}
</style>
</head>
<body>
<main>
	<div class="card">
		<div class="top">
			<?php echo ipm_logo_html(); // phpcs:ignore ?>
			<p class="brand"><?php echo esc_html( ipm_opt( 'brand' ) ); ?></p>
			<?php if ( ipm_opt( 'role' ) ) : ?><p class="role"><?php echo esc_html( ipm_opt( 'role' ) ); ?></p><?php endif; ?>
		</div>
		<div class="body">
			<p class="badge">Sito in manutenzione</p>
			<h1><?php echo esc_html( ipm_opt( 'title' ) ); ?></h1>
			<p class="text"><?php echo nl2br( esc_html( ipm_opt( 'text' ) ) ); ?></p>
			<div class="acts">
				<?php if ( ipm_opt( 'phone1' ) ) : ?>
					<a class="btn btn-call" href="<?php echo esc_attr( ipm_tel( ipm_opt( 'phone1' ) ) ); ?>"><?php echo ipm_icon( 'phone' ); // phpcs:ignore ?>Chiama <?php echo esc_html( ipm_opt( 'phone1' ) ); ?></a>
				<?php endif; ?>
				<?php if ( $wa ) : ?>
					<a class="btn btn-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php echo ipm_icon( 'whatsapp' ); // phpcs:ignore ?>Scrivici su WhatsApp</a>
				<?php endif; ?>
				<?php if ( ipm_opt( 'phone2' ) && ipm_opt( 'phone2' ) !== ipm_opt( 'phone1' ) ) : ?>
					<a class="btn btn-line" href="<?php echo esc_attr( ipm_tel( ipm_opt( 'phone2' ) ) ); ?>"><?php echo ipm_icon( 'phone' ); // phpcs:ignore ?>Cellulare <?php echo esc_html( ipm_opt( 'phone2' ) ); ?></a>
				<?php endif; ?>
			</div>
			<div class="info">
				<?php if ( ipm_opt( 'email' ) ) : ?>
					<div><?php echo ipm_icon( 'mail' ); // phpcs:ignore ?><a href="mailto:<?php echo esc_attr( ipm_opt( 'email' ) ); ?>"><?php echo esc_html( ipm_opt( 'email' ) ); ?></a></div>
				<?php endif; ?>
				<?php if ( ipm_opt( 'hours' ) ) : ?>
					<div><?php echo ipm_icon( 'clock' ); // phpcs:ignore ?><span><?php echo nl2br( esc_html( ipm_opt( 'hours' ) ) ); ?></span></div>
				<?php endif; ?>
				<?php if ( ipm_opt( 'address' ) ) : ?>
					<div><?php echo ipm_icon( 'pin' ); // phpcs:ignore ?><span><?php echo nl2br( esc_html( ipm_opt( 'address' ) ) ); ?></span></div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== ipm_opt( 'pass' ) ) : ?>
				<details class="access"<?php echo $err ? ' open' : ''; ?>>
					<summary>Hai un codice di accesso?</summary>
					<form method="post">
						<label for="ipm-pass" class="sr">Codice di accesso</label>
						<input type="password" id="ipm-pass" name="ipm_pass" placeholder="Codice di accesso" autocomplete="current-password" required>
						<button type="submit">Entra</button>
					</form>
					<?php if ( $err ) : ?><p class="err" role="alert"><?php echo esc_html( $err ); ?></p><?php endif; ?>
				</details>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php if ( $legal ) : ?>
	<footer>© <?php echo esc_html( gmdate( 'Y' ) . ' ' . ipm_opt( 'brand' ) ); ?> · <?php echo esc_html( implode( ' · ', $legal ) ); ?></footer>
<?php endif; ?>
</body>
</html>
	<?php
}

/* ---------------------------------------------------------------------
 * Avviso nella barra in alto quando la manutenzione è attiva
 * ------------------------------------------------------------------- */

add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! ipm_opt( 'on' ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$bar->add_node( array(
		'id'    => 'ipm',
		'title' => '<span style="background:#a0300e;color:#fff;padding:2px 8px;border-radius:3px">Manutenzione attiva</span>',
		'href'  => admin_url( 'options-general.php?page=ipm' ),
	) );
}, 100 );

/* ---------------------------------------------------------------------
 * Impostazioni → Manutenzione
 * ------------------------------------------------------------------- */

function ipm_fields() {
	return array(
		'on'       => array( 'Manutenzione attiva', 'checkbox', 'I visitatori vedono la pagina di manutenzione. Tu, da collegato, vedi il sito normalmente.' ),
		'title'    => array( 'Titolo', 'text' ),
		'text'     => array( 'Messaggio', 'textarea' ),
		'logo'     => array( 'Logo', 'image', 'Vuoto = logo impostato in Aspetto → Personalizza.' ),
		'brand'    => array( 'Nome', 'text' ),
		'role'     => array( 'Sottotitolo', 'text' ),
		'phone1'   => array( 'Telefono principale', 'text', 'Pulsante «Chiama».' ),
		'phone2'   => array( 'Secondo telefono', 'text', 'Vuoto = pulsante nascosto.' ),
		'whatsapp' => array( 'WhatsApp', 'text', 'Vuoto = pulsante nascosto.' ),
		'email'    => array( 'Email', 'text' ),
		'hours'    => array( 'Orari', 'textarea' ),
		'address'  => array( 'Indirizzo sede', 'textarea' ),
		'company'  => array( 'Ragione sociale', 'text' ),
		'vat'      => array( 'Partita IVA', 'text' ),
		'legal'    => array( 'Sede legale', 'text' ),
		'pec'      => array( 'PEC', 'text' ),
		'retry'    => array( 'Ritorno previsto tra (ore)', 'text', 'Indicato ai motori di ricerca: il sito non perde posizioni durante la manutenzione.' ),
		'pass'     => array( 'Codice di accesso', 'text', 'Chi lo inserisce nella pagina di manutenzione (o apre il link qui sotto) vede il sito normalmente. Vuoto = nessun accesso riservato. Cambiandolo, chi aveva il vecchio codice dovrà inserire quello nuovo.' ),
		'pass_days' => array( 'L’accesso dura (giorni)', 'text', 'Poi il codice va inserito di nuovo. Da 1 a 90.' ),
	);
}

add_action( 'admin_menu', function () {
	add_options_page( 'Manutenzione', 'Manutenzione', 'manage_options', 'ipm', 'ipm_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'ipm', 'ipm_settings', array(
		'sanitize_callback' => function ( $in ) {
			$out = array();
			foreach ( ipm_fields() as $k => $f ) {
				$v = isset( $in[ $k ] ) ? $in[ $k ] : '';
				if ( 'checkbox' === $f[1] ) {
					$out[ $k ] = $v ? '1' : '';
				} elseif ( 'image' === $f[1] ) {
					$out[ $k ] = $v ? (string) absint( $v ) : '';
				} elseif ( 'textarea' === $f[1] ) {
					$out[ $k ] = sanitize_textarea_field( $v );
				} else {
					$out[ $k ] = sanitize_text_field( $v );
				}
			}
			return $out;
		},
	) );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_ipm' === $hook ) {
		wp_enqueue_media();
	}
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=ipm' ) ) . '">Impostazioni</a>' );
	return $links;
} );

function ipm_page() {
	?>
	<div class="wrap">
		<h1>Manutenzione</h1>
		<p>
			<?php if ( ipm_opt( 'on' ) ) : ?>
				<strong style="color:#a0300e">La pagina di manutenzione è attiva.</strong>
			<?php else : ?>
				Il sito è visibile a tutti.
			<?php endif; ?>
			<a class="button" href="<?php echo esc_url( add_query_arg( 'ipm_anteprima', '1', home_url( '/' ) ) ); ?>" target="_blank">Anteprima della pagina</a>
		</p>
		<?php if ( '' !== ipm_opt( 'pass' ) ) : ?>
			<?php $share = add_query_arg( 'accesso', rawurlencode( ipm_opt( 'pass' ) ), home_url( '/' ) ); ?>
			<div style="max-width:760px;padding:14px 16px;background:#fff;border:1px solid #dcdcde;border-left:4px solid #225e48;border-radius:6px">
				<p style="margin:0 0 8px"><strong>Link di accesso da condividere</strong> · chi lo apre entra subito, senza digitare il codice:</p>
				<p style="margin:0;display:flex;gap:8px"><input type="text" readonly value="<?php echo esc_attr( $share ); ?>" class="large-text code" onclick="this.select()"> <button type="button" class="button" onclick="navigator.clipboard&&navigator.clipboard.writeText(this.previousElementSibling.value);this.textContent='Copiato ✓'">Copia</button></p>
			</div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'ipm' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( ipm_fields() as $k => $f ) : ?>
					<?php
					$name = 'ipm_settings[' . $k . ']';
					$val  = ipm_opt( $k );
					?>
					<tr>
						<th scope="row"><label for="ipm-<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $f[0] ); ?></label></th>
						<td>
							<?php if ( 'checkbox' === $f[1] ) : ?>
								<label><input type="checkbox" id="ipm-<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $val, '1' ); ?>> Attiva</label>
							<?php elseif ( 'textarea' === $f[1] ) : ?>
								<textarea id="ipm-<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $val ); ?></textarea>
							<?php elseif ( 'image' === $f[1] ) : ?>
								<?php $src = $val ? wp_get_attachment_image_url( (int) $val, 'medium' ) : ''; ?>
								<div class="ipm-img">
									<img src="<?php echo esc_url( $src ); ?>" alt="" style="max-height:70px;display:<?php echo $src ? 'block' : 'none'; ?>;margin-bottom:8px">
									<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $val ); ?>">
									<button type="button" class="button" data-ipm-pick>Scegli immagine</button>
									<button type="button" class="button-link" data-ipm-clear>Rimuovi</button>
								</div>
							<?php else : ?>
								<input type="text" id="ipm-<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $val ); ?>" class="regular-text">
							<?php endif; ?>
							<?php if ( ! empty( $f[2] ) ) : ?><p class="description"><?php echo esc_html( $f[2] ); ?></p><?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<script>
	document.addEventListener('click', function (e) {
		var pick = e.target.closest('[data-ipm-pick]'), clear = e.target.closest('[data-ipm-clear]');
		if (!pick && !clear) return;
		var box = e.target.closest('.ipm-img'), img = box.querySelector('img'), input = box.querySelector('input');
		if (clear) { input.value = ''; img.style.display = 'none'; return; }
		var frame = wp.media({ title: 'Logo', multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			var a = frame.state().get('selection').first().toJSON();
			input.value = a.id; img.src = (a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url); img.style.display = 'block';
		});
		frame.open();
	});
	</script>
	<?php
}

// Anteprima per gli amministratori: ?ipm_anteprima=1
add_action( 'template_redirect', function () {
	if ( isset( $_GET['ipm_anteprima'] ) && current_user_can( 'manage_options' ) ) {
		ipm_render();
		exit;
	}
}, 0 );
