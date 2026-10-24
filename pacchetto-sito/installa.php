<?php
/**
 * Installazione guidata del sito Infopoint.
 *
 * Carica tutti i file sull'hosting, apri questo indirizzo nel browser
 * (https://tuodominio.it/installa.php), inserisci i dati del database e
 * premi «Installa». Lo script:
 *  - scrive i dati del database in wp-config.php e rigenera le chiavi;
 *  - importa database.sql;
 *  - sostituisce l'indirizzo di prova con quello del sito, anche nei dati serializzati;
 *  - imposta la nuova password dell'amministratore;
 *  - cancella database.sql e se stesso.
 */

@set_time_limit( 600 );
@ini_set( 'memory_limit', '512M' );
error_reporting( E_ALL & ~E_DEPRECATED & ~E_NOTICE );

const IP_OLD_URLS = array( 'http://localhost/infopoint-site', 'http://localhost:8090/infopoint-site' );
const IP_PREFIX   = 'wp_';

$dir  = __DIR__;
$sql  = $dir . '/database.sql';
$conf = $dir . '/wp-config.php';

function h( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}

function ip_guess_url() {
	$https = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) || ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] );
	$path  = rtrim( str_replace( '\\', '/', dirname( $_SERVER['SCRIPT_NAME'] ) ), '/' );
	return ( $https ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $path;
}

/** Sostituzione che non rompe i dati serializzati di WordPress. */
function ip_replace( $value, $from, $to ) {
	if ( is_string( $value ) ) {
		$hit = false;
		foreach ( $from as $x ) {
			$hit = $hit || false !== strpos( $value, $x );
		}
		if ( ! $hit ) {
			return $value;
		}
		$un = @unserialize( $value, array( 'allowed_classes' => false ) );
		if ( false !== $un || 'b:0;' === $value ) {
			if ( ip_has_object( $un ) ) {
				return $value; // Oggetti non ricostruibili: meglio lasciarli com'erano.
			}
			return serialize( ip_replace( $un, $from, $to ) );
		}
		return str_replace( $from, $to, $value );
	}
	if ( is_array( $value ) ) {
		$out = array();
		foreach ( $value as $k => $v ) {
			$out[ $k ] = ip_replace( $v, $from, $to );
		}
		return $out;
	}
	return $value;
}

function ip_has_object( $v ) {
	if ( is_object( $v ) ) {
		return true;
	}
	if ( is_array( $v ) ) {
		foreach ( $v as $x ) {
			if ( ip_has_object( $x ) ) {
				return true;
			}
		}
	}
	return false;
}

function ip_import( mysqli $db, $file ) {
	$fh = fopen( $file, 'r' );
	if ( ! $fh ) {
		throw new Exception( 'Non riesco a leggere database.sql.' );
	}
	$buf = '';
	$n   = 0;
	while ( false !== ( $line = fgets( $fh ) ) ) {
		$t = ltrim( $line );
		if ( '' === $buf && ( '' === trim( $t ) || 0 === strpos( $t, '--' ) ) ) {
			continue;
		}
		$buf .= $line;
		if ( ';' === substr( rtrim( $line ), -1 ) ) {
			if ( ! $db->query( $buf ) ) {
				throw new Exception( 'Errore durante l’importazione: ' . $db->error );
			}
			$buf = '';
			$n++;
		}
	}
	fclose( $fh );
	return $n;
}

function ip_search_replace( mysqli $db, array $from, array $to ) {
	$changed = 0;
	$tables  = $db->query( "SHOW TABLES LIKE '" . $db->real_escape_string( str_replace( '_', '\_', IP_PREFIX ) ) . "%'" );
	while ( $t = $tables->fetch_row() ) {
		$table = $t[0];
		$pk    = null;
		$cols  = array();
		$res   = $db->query( "SHOW COLUMNS FROM `$table`" );
		while ( $c = $res->fetch_assoc() ) {
			if ( 'PRI' === $c['Key'] && null === $pk ) {
				$pk = $c['Field'];
			}
			if ( preg_match( '/char|text/i', $c['Type'] ) ) {
				$cols[] = $c['Field'];
			}
		}
		if ( ! $pk || ! $cols ) {
			continue;
		}
		$where = array();
		foreach ( $cols as $c ) {
			$where[] = "`$c` LIKE '%localhost%'";
		}
		$rows = $db->query( "SELECT `$pk`, `" . implode( '`, `', $cols ) . "` FROM `$table` WHERE " . implode( ' OR ', $where ) );
		while ( $r = $rows->fetch_assoc() ) {
			$set = array();
			foreach ( $cols as $c ) {
				if ( null === $r[ $c ] ) {
					continue;
				}
				$new = ip_replace( $r[ $c ], $from, $to );
				if ( $new !== $r[ $c ] ) {
					$set[] = "`$c` = '" . $db->real_escape_string( $new ) . "'";
				}
			}
			if ( $set ) {
				$db->query( "UPDATE `$table` SET " . implode( ', ', $set ) . " WHERE `$pk` = '" . $db->real_escape_string( $r[ $pk ] ) . "'" );
				$changed++;
			}
		}
	}
	return $changed;
}

function ip_write_config( $file, $d ) {
	$c = file_get_contents( $file );
	foreach ( array( 'DB_NAME' => $d['name'], 'DB_USER' => $d['user'], 'DB_PASSWORD' => $d['pass'], 'DB_HOST' => $d['host'] ) as $k => $v ) {
		$c = preg_replace( "/define\(\s*'$k'\s*,\s*'(?:[^'\\\\]|\\\\.)*'\s*\);[^\n]*/", 'define( \'' . $k . '\', ' . var_export( $v, true ) . ' );', $c, 1 );
	}
	foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ) as $k ) {
		$salt = '';
		$abc  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%^&*()-_[]{}<>~+=,.;:/?|';
		for ( $i = 0; $i < 64; $i++ ) {
			$salt .= $abc[ random_int( 0, strlen( $abc ) - 1 ) ];
		}
		$c = preg_replace( "/define\(\s*'$k'\s*,\s*'(?:[^'\\\\]|\\\\.)*'\s*\);/", 'define( \'' . $k . '\', ' . var_export( $salt, true ) . ' );', $c, 1 );
	}
	if ( $d['production'] ) {
		$c = str_replace( "define( 'WP_ENVIRONMENT_TYPE', 'local' );", "define( 'WP_ENVIRONMENT_TYPE', 'production' );", $c );
	}
	if ( false === file_put_contents( $file, $c ) ) {
		throw new Exception( 'Non riesco a scrivere wp-config.php: controlla i permessi della cartella.' );
	}
}

$errors = array();
$done   = null;
$f      = array(
	'host' => 'localhost',
	'name' => '',
	'user' => '',
	'pass' => '',
	'url'  => ip_guess_url(),
	'adm'  => '',
	'over' => '',
);

if ( ! is_file( $sql ) ) {
	$errors[] = 'Il file database.sql non c’è: il sito risulta già installato. Per sicurezza cancella installa.php dal server.';
}

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! $errors ) {
	foreach ( $f as $k => $v ) {
		$f[ $k ] = isset( $_POST[ $k ] ) ? trim( (string) $_POST[ $k ] ) : '';
	}
	$f['pass'] = isset( $_POST['pass'] ) ? (string) $_POST['pass'] : '';
	$f['url']  = rtrim( $f['url'], '/' );
	if ( '' === $f['name'] || '' === $f['user'] ) {
		$errors[] = 'Inserisci nome del database e utente.';
	}
	if ( ! preg_match( '#^https?://[^\s/]+#', $f['url'] ) ) {
		$errors[] = 'L’indirizzo del sito deve iniziare con http:// o https://';
	}
	if ( '' !== $f['adm'] && strlen( $f['adm'] ) < 10 ) {
		$errors[] = 'La nuova password dell’amministratore deve avere almeno 10 caratteri.';
	}
	if ( ! $errors ) {
		mysqli_report( MYSQLI_REPORT_OFF );
		$hp   = explode( ':', $f['host'], 2 );
		$port = isset( $hp[1] ) && ctype_digit( $hp[1] ) ? (int) $hp[1] : null;
		$sock = isset( $hp[1] ) && ! ctype_digit( $hp[1] ) ? $hp[1] : null;
		$db   = @new mysqli( $hp[0], $f['user'], $f['pass'], $f['name'], $port, $sock );
		if ( $db->connect_errno ) {
			$errors[] = 'Connessione al database non riuscita: ' . $db->connect_error;
		} else {
			$db->set_charset( 'utf8mb4' );
			$exists = $db->query( "SHOW TABLES LIKE '" . IP_PREFIX . "options'" );
			if ( $exists && $exists->num_rows && ! $f['over'] ) {
				$errors[] = 'Il database contiene già un sito WordPress. Usa un database vuoto, oppure spunta «Sovrascrivi» se vuoi sostituirlo.';
			}
		}
		if ( ! $errors ) {
			try {
				$host       = parse_url( $f['url'], PHP_URL_HOST );
				$production = ! preg_match( '/^(localhost|127\.|\[::1\])|\.(local|test)$/', $host );
				ip_write_config( $conf, $f + array( 'production' => $production ) );
				$n    = ip_import( $db, $sql );
				$from = array();
				$to   = array();
				foreach ( IP_OLD_URLS as $old ) {
					$from[] = $old;
					$to[]   = $f['url'];
					$from[] = str_replace( '/', '\/', $old );
					$to[]   = str_replace( '/', '\/', $f['url'] );
				}
				$rows = ip_search_replace( $db, $from, $to );
				$db->query( "UPDATE `" . IP_PREFIX . "options` SET option_value = '" . $db->real_escape_string( $f['url'] ) . "' WHERE option_name IN ('siteurl','home')" );
				$db->query( "DELETE FROM `" . IP_PREFIX . "options` WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%'" );
				$db->query( "DELETE FROM `" . IP_PREFIX . "options` WHERE option_name = 'rewrite_rules'" );
				if ( '' !== $f['adm'] ) {
					// Hash nel formato di WordPress, calcolato da WordPress stesso.
					define( 'WP_INSTALLING', true );
					$_SERVER['HTTP_HOST'] = parse_url( $f['url'], PHP_URL_HOST ) . ( parse_url( $f['url'], PHP_URL_PORT ) ? ':' . parse_url( $f['url'], PHP_URL_PORT ) : '' );
					require_once $dir . '/wp-load.php';
					$u = get_user_by( 'login', 'admin' );
					if ( $u ) {
						wp_set_password( $f['adm'], $u->ID );
					}
				}
				$left = array();
				if ( ! @unlink( $sql ) ) {
					$left[] = 'database.sql';
				}
				if ( is_file( $dir . '/LEGGIMI.md' ) && ! @unlink( $dir . '/LEGGIMI.md' ) ) {
					$left[] = 'LEGGIMI.md';
				}
				if ( ! @unlink( __FILE__ ) ) {
					$left[] = 'installa.php';
				}
				$done = array( 'n' => $n, 'rows' => $rows, 'left' => $left, 'pwd' => '' !== $f['adm'] );
			} catch ( Exception $e ) {
				$errors[] = $e->getMessage();
			}
		}
	}
}
?><!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Installazione sito Infopoint</title>
<style>
	:root { --g: #174f3a; --a: #a0300e; --l: #d9e2dd; }
	* { box-sizing: border-box; }
	body { margin: 0; background: #f3f6f4; color: #1b2420; font: 16px/1.55 system-ui, -apple-system, "Segoe UI", sans-serif; }
	.w { max-width: 620px; margin: 40px auto; padding: 0 16px; }
	.c { background: #fff; border: 1px solid var(--l); border-radius: 14px; padding: 28px; }
	h1 { margin: 0 0 6px; font-size: 1.5rem; color: var(--g); }
	p.s { margin: 0 0 22px; color: #5b6b64; }
	label { display: block; margin: 0 0 4px; font-weight: 600; font-size: .92rem; }
	input[type=text], input[type=password], input[type=url] { width: 100%; padding: 11px 12px; margin: 0 0 16px; border: 1px solid #c4cfc9; border-radius: 8px; font: inherit; }
	small { display: block; margin: -12px 0 16px; color: #5b6b64; }
	.row { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }
	.ck { display: flex; gap: 8px; align-items: flex-start; margin: 4px 0 18px; font-weight: 400; }
	button { width: 100%; padding: 14px; border: 0; border-radius: 999px; background: var(--a); color: #fff; font: 700 1rem inherit; font-family: inherit; cursor: pointer; }
	.err { margin: 0 0 18px; padding: 12px 14px; border-radius: 8px; background: #fbe9e4; color: #8a2a0c; }
	.ok { margin: 0 0 18px; padding: 14px; border-radius: 8px; background: #e6f1ec; color: var(--g); }
	a.b { display: inline-block; margin: 6px 10px 0 0; padding: 11px 18px; border-radius: 999px; background: var(--g); color: #fff; text-decoration: none; font-weight: 700; }
	@media (max-width: 560px) { .row { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="w"><div class="c">
<?php if ( $done ) : ?>
	<h1>Sito installato</h1>
	<div class="ok">Database importato (<?php echo (int) $done['n']; ?> istruzioni), indirizzi aggiornati in <?php echo (int) $done['rows']; ?> righe.</div>
	<p>Accedi con utente <strong>admin</strong> e <?php echo $done['pwd'] ? 'la password che hai scelto' : 'la password indicata in LEGGIMI.md (cambiala subito da Utenti → Profilo)'; ?>.</p>
	<?php if ( $done['left'] ) : ?>
		<div class="err">Non sono riuscito a cancellare: <strong><?php echo h( implode( ', ', $done['left'] ) ); ?></strong>. Cancellali tu via FTP: contengono i dati del sito.</div>
	<?php endif; ?>
	<p>Poi, nel pannello: <em>Impostazioni → Permalink → Salva</em> una volta.</p>
	<a class="b" href="<?php echo h( $f['url'] ); ?>/">Apri il sito</a>
	<a class="b" href="<?php echo h( $f['url'] ); ?>/wp-admin/">Pannello</a>
<?php else : ?>
	<h1>Installazione sito Infopoint</h1>
	<p class="s">Crea un database vuoto dal pannello dell’hosting, inserisci qui i dati e premi Installa. Ci vuole meno di un minuto.</p>
	<?php foreach ( $errors as $e ) : ?><div class="err"><?php echo h( $e ); ?></div><?php endforeach; ?>
	<?php if ( is_file( $sql ) ) : ?>
	<form method="post" autocomplete="off">
		<div class="row">
			<div><label for="name">Nome database</label><input type="text" id="name" name="name" value="<?php echo h( $f['name'] ); ?>" required></div>
			<div><label for="host">Server database</label><input type="text" id="host" name="host" value="<?php echo h( $f['host'] ); ?>" required></div>
		</div>
		<div class="row">
			<div><label for="user">Utente database</label><input type="text" id="user" name="user" value="<?php echo h( $f['user'] ); ?>" required></div>
			<div><label for="pass">Password database</label><input type="password" id="pass" name="pass" value=""></div>
		</div>
		<label for="url">Indirizzo del sito</label>
		<input type="url" id="url" name="url" value="<?php echo h( $f['url'] ); ?>" required>
		<small>Riconosciuto in automatico. Se il sito deve rispondere con https, scrivi https://</small>
		<label for="adm">Nuova password per l’utente admin</label>
		<input type="password" id="adm" name="adm" value="" minlength="10" placeholder="Almeno 10 caratteri (consigliato)">
		<small>Vuoto = resta la password del pacchetto, da cambiare subito.</small>
		<label class="ck"><input type="checkbox" name="over" value="1"<?php echo $f['over'] ? ' checked' : ''; ?>> Sovrascrivi se nel database c’è già un sito WordPress</label>
		<button type="submit">Installa</button>
	</form>
	<?php endif; ?>
<?php endif; ?>
</div></div>
</body>
</html>
