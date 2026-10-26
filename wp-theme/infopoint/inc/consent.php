<?php
/**
 * Tag di Google (Tag Manager, Analytics 4, Ads) con modalità di consenso v2,
 * banner cookie a categorie e registro dei consensi.
 * Nessun cookie di statistica o marketing finché il visitatore non accetta.
 */

defined( 'ABSPATH' ) || exit;

function ip_gtm_id() {
	$id = strtoupper( trim( (string) ip_opt( 'gtm_id' ) ) );
	return preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ? $id : '';
}

function ip_ga4_id() {
	$id = strtoupper( trim( (string) ip_opt( 'ga4_id' ) ) );
	return preg_match( '/^G-[A-Z0-9]+$/', $id ) ? $id : '';
}

function ip_ads_id() {
	$id = strtoupper( trim( (string) ip_opt( 'ads_id' ) ) );
	return preg_match( '/^AW-\d+$/', $id ) ? $id : '';
}

/** C'è almeno un tag che usa cookie: serve il banner. */
function ip_tracking_on() {
	return ip_gtm_id() || ip_ga4_id() || ip_ads_id();
}

function ip_consent_on() {
	return ip_tracking_on() && ip_opt( 'consent_banner' );
}

/** Cambia quando cambia la cookie policy: il banner viene riproposto. */
function ip_consent_version() {
	return substr( md5( (string) ip_opt( 'legal_updated' ) ), 0, 8 );
}

function ip_consent_days() {
	$m = (int) ip_opt( 'consent_months' );
	return ( $m > 0 ? min( $m, 12 ) : 6 ) * 30;
}

add_action( 'wp_head', function () {
	$gtm = ip_gtm_id();
	$ga4 = ip_ga4_id();
	$ads = ip_ads_id();
	if ( $gtm || $ga4 || $ads ) {
		$cfg = array(
			'v'    => ip_consent_version(),
			'days' => ip_consent_days(),
			'ext'  => ip_opt( 'consent_banner' ) ? 0 : 1, // Banner esterno: le scelte le gestisce lui.
		);
		$track = array(
			'ga4'   => $ga4,
			'ads'   => $ads,
			'lead'  => trim( (string) ip_opt( 'ads_lead_label' ) ),
			'call'  => trim( (string) ip_opt( 'ads_call_label' ) ),
			'enh'   => ip_opt( 'ads_enhanced' ) ? 1 : 0,
			'value' => (float) str_replace( ',', '.', (string) ip_opt( 'ads_value' ) ),
		);
		?>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}window.ipTrack=<?php echo wp_json_encode( $track ); ?>;(function(o){var c=null,a=false,m=false;try{c=JSON.parse(localStorage.getItem('ip_consent'))}catch(e){}var ok=!!(c&&typeof c==='object'&&c.v===o.v&&Date.now()-c.t<o.days*864e5);if(ok){a=!!c.a;m=!!c.m}var g=function(x){return x?'granted':'denied'};if(!o.ext){gtag('consent','default',{ad_storage:g(m),ad_user_data:g(m),ad_personalization:g(m),analytics_storage:g(a),functionality_storage:'granted',security_storage:'granted',wait_for_update:500})}gtag('set','ads_data_redaction',true);gtag('set','url_passthrough',true);window.ipConsent={ok:ok,a:a,m:m,v:o.v,days:o.days,id:c&&c.id};dataLayer.push({event:'ip_consent_ready',consent_analytics:g(a),consent_marketing:g(m)})})(<?php echo wp_json_encode( $cfg ); ?>);</script>
		<?php
		if ( $gtm ) {
			?>
<script>(function(w,d,s,l,i){w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f)})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
			<?php
		}
		if ( $ga4 || $ads ) {
			?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ? $ga4 : $ads ); ?>"></script>
<script>gtag('js',new Date());<?php if ( $ga4 ) : ?>gtag('config','<?php echo esc_js( $ga4 ); ?>');<?php endif; ?><?php if ( $ads ) : ?>gtag('config','<?php echo esc_js( $ads ); ?>'<?php echo ip_opt( 'ads_enhanced' ) ? ",{allow_enhanced_conversions:true}" : ''; ?>);<?php endif; ?></script>
			<?php
		}
	}
	$code = ip_opt( 'head_code' );
	if ( $code ) {
		echo $code . "\n"; // phpcs:ignore — inserito dall'amministratore.
	}
}, 1 );

add_action( 'wp_body_open', function () {
	$gtm = ip_gtm_id();
	if ( $gtm ) {
		printf( '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>', esc_attr( $gtm ) );
	}
} );

add_action( 'wp_footer', function () {
	if ( ip_consent_on() ) {
		ip_consent_banner();
	}
	$code = ip_opt( 'footer_code' );
	if ( $code ) {
		echo $code . "\n"; // phpcs:ignore
	}
}, 50 );

function ip_consent_banner() {
	$cookie = (int) ip_opt( 'cookie_page' );
	$cookie = $cookie && 'publish' === get_post_status( $cookie ) ? get_permalink( $cookie ) : '';
	$ga     = ip_ga4_id() || ip_gtm_id();
	$ads    = ip_ads_id() || ip_gtm_id();
	?>
	<style>
	.consent{position:fixed;left:16px;bottom:16px;z-index:9990;width:min(440px,calc(100vw - 32px));max-height:calc(100vh - 32px);overflow:auto;padding:20px 20px 18px;background:#fff;color:#1b2420;border:1px solid #dfe7e3;border-radius:14px;box-shadow:0 18px 50px rgba(14,38,29,.22);font:15px/1.5 var(--font,system-ui,sans-serif)}
	.consent[hidden]{display:none}
	.consent .consent-t{margin:0 34px 6px 0;font-weight:700;font-size:1.02rem;color:#0e261d}
	.consent p{margin:0 0 12px;font-size:.9rem;color:#4b5a53}
	.consent a{color:inherit;text-decoration:underline}
	.consent-x{position:absolute;right:10px;top:10px;width:32px;height:32px;border:0;border-radius:50%;background:#f1f4f2;color:#1b2420;font-size:20px;line-height:1;cursor:pointer}
	.consent-prefs{margin:4px 0 12px;border-top:1px solid #e6ece9}
	.consent-prefs[hidden]{display:none}
	.ck-row{display:flex;gap:14px;align-items:flex-start;justify-content:space-between;padding:11px 0;border-bottom:1px solid #e6ece9;cursor:pointer}
	.ck-row strong{display:block;font-size:.92rem;color:#0e261d}
	.ck-row small{display:block;font-size:.8rem;color:#5b6b64;line-height:1.4}
	.ck-row input{flex:none;width:40px;height:22px;margin:2px 0 0;appearance:none;-webkit-appearance:none;border-radius:999px;background:#c9d3ce;position:relative;cursor:pointer;transition:background .15s}
	.ck-row input::after{content:"";position:absolute;left:3px;top:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .15s}
	.ck-row input:checked{background:var(--brand,#225e48)}
	.ck-row input:checked::after{transform:translateX(18px)}
	.ck-row input:disabled{opacity:.55;cursor:default}
	.consent-acts{display:flex;flex-wrap:wrap;gap:8px}
	.consent-acts button{flex:1 1 30%;min-height:42px;padding:8px 12px;border-radius:999px;border:2px solid var(--brand,#225e48);background:var(--brand,#225e48);color:#fff;font:inherit;font-weight:700;font-size:.88rem;cursor:pointer}
	.consent-acts .ck-alt{background:#fff;color:var(--brand,#225e48);border-color:#dfe7e3}
	.consent-acts [hidden]{display:none}
	@media (max-width:760px){.consent{left:8px;right:8px;bottom:8px;width:auto;padding:16px 16px 14px}.consent p{font-size:.84rem}.consent-acts button{flex:1 1 40%;min-height:44px}.consent-acts .ck-alt{flex:1 1 100%;order:3;min-height:38px;border:0;text-decoration:underline}}
	@media print{.consent{display:none!important}}
	</style>
	<div class="consent" data-consent hidden role="dialog" aria-labelledby="ck-t" aria-describedby="ck-d">
		<button type="button" class="consent-x" data-consent-set="reject" aria-label="Chiudi: rifiuta i cookie non necessari">×</button>
		<p class="consent-t" id="ck-t">Cookie e privacy</p>
		<p id="ck-d"><?php echo esc_html( ip_opt( 'consent_text' ) ); ?><?php if ( $cookie ) : ?> <a href="<?php echo esc_url( $cookie ); ?>">Cookie policy</a><?php endif; ?></p>
		<div class="consent-prefs" data-consent-prefs hidden>
			<label class="ck-row"><span><strong>Necessari</strong><small>Fanno funzionare il sito e ricordano le tue scelte. Sempre attivi.</small></span><input type="checkbox" checked disabled></label>
			<?php if ( $ga ) : ?>
				<label class="ck-row"><span><strong>Statistiche</strong><small>Google Analytics: contiamo le visite e le pagine più lette, in forma aggregata.</small></span><input type="checkbox" data-ck="a"></label>
			<?php endif; ?>
			<?php if ( $ads ) : ?>
				<label class="ck-row"><span><strong>Marketing</strong><small>Google Ads: sappiamo quali annunci portano richieste e possiamo mostrarti annunci pertinenti.</small></span><input type="checkbox" data-ck="m"></label>
			<?php endif; ?>
		</div>
		<div class="consent-acts">
			<button type="button" data-consent-set="reject">Rifiuta</button>
			<button type="button" class="ck-alt" data-consent-custom>Personalizza</button>
			<button type="button" class="ck-alt" data-consent-set="save" hidden>Salva le scelte</button>
			<button type="button" data-consent-set="accept">Accetta tutti</button>
		</div>
	</div>
	<script>window.ipConsentLog=<?php echo ip_opt( 'consent_log' ) ? wp_json_encode( admin_url( 'admin-ajax.php' ) ) : 'null'; ?>;</script>
	<?php
}

/* ---------------------------------------------------------------------
 * Registro dei consensi
 * ------------------------------------------------------------------- */

function ip_consent_table() {
	global $wpdb;
	return $wpdb->prefix . 'ip_consents';
}

function ip_consent_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( 'CREATE TABLE ' . ip_consent_table() . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created datetime NOT NULL,
		cid varchar(40) NOT NULL DEFAULT '',
		choice varchar(10) NOT NULL DEFAULT '',
		analytics tinyint(1) NOT NULL DEFAULT 0,
		marketing tinyint(1) NOT NULL DEFAULT 0,
		version varchar(12) NOT NULL DEFAULT '',
		url varchar(255) NOT NULL DEFAULT '',
		ip_hash char(16) NOT NULL DEFAULT '',
		ua varchar(255) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY created (created),
		KEY cid (cid)
	) " . $wpdb->get_charset_collate() . ';' );
	update_option( 'ip_consent_db', '1', false );
}

add_action( 'wp_ajax_nopriv_ip_consent', 'ip_consent_record' );
add_action( 'wp_ajax_ip_consent', 'ip_consent_record' );

function ip_consent_record() {
	global $wpdb;
	if ( ! ip_opt( 'consent_log' ) ) {
		wp_send_json( array( 'ok' => false ), 403 );
	}
	if ( ! get_option( 'ip_consent_db' ) ) {
		ip_consent_install();
	}
	$ip_hash = substr( hash( 'sha256', ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) . wp_salt() ), 0, 16 );
	$hits    = (int) get_transient( 'ip_ck_' . $ip_hash );
	if ( $hits > 30 ) {
		wp_send_json( array( 'ok' => false ), 429 );
	}
	set_transient( 'ip_ck_' . $ip_hash, $hits + 1, HOUR_IN_SECONDS );
	$p      = wp_unslash( $_POST );
	$choice = isset( $p['choice'] ) && in_array( $p['choice'], array( 'accept', 'reject', 'save' ), true ) ? $p['choice'] : '';
	if ( ! $choice ) {
		wp_send_json( array( 'ok' => false ), 400 );
	}
	$wpdb->insert( ip_consent_table(), array(
		'created'   => current_time( 'mysql' ),
		'cid'       => substr( preg_replace( '/[^a-z0-9-]/i', '', (string) ( $p['cid'] ?? '' ) ), 0, 40 ),
		'choice'    => $choice,
		'analytics' => empty( $p['a'] ) ? 0 : 1,
		'marketing' => empty( $p['m'] ) ? 0 : 1,
		'version'   => substr( sanitize_key( (string) ( $p['v'] ?? '' ) ), 0, 12 ),
		'url'       => substr( esc_url_raw( (string) ( $p['url'] ?? '' ) ), 0, 255 ),
		'ip_hash'   => $ip_hash,
		'ua'        => substr( sanitize_text_field( isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '' ), 0, 255 ),
	) );
	// Conservazione: 24 mesi.
	if ( 1 === wp_rand( 1, 50 ) ) {
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . ip_consent_table() . ' WHERE created < %s', gmdate( 'Y-m-d H:i:s', strtotime( '-24 months' ) ) ) ); // phpcs:ignore
	}
	wp_send_json( array( 'ok' => true ) );
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'ip-settings', 'Registro consensi', 'Registro consensi', 'manage_options', 'ip-consents', 'ip_consent_page' );
}, 20 );

function ip_consent_page() {
	global $wpdb;
	if ( ! get_option( 'ip_consent_db' ) ) {
		ip_consent_install();
	}
	$t     = ip_consent_table();
	$since = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
	$stats = $wpdb->get_results( $wpdb->prepare( "SELECT choice, COUNT(*) n, SUM(analytics) a, SUM(marketing) m FROM $t WHERE created >= %s GROUP BY choice", $since ), OBJECT_K ); // phpcs:ignore
	$tot   = 0;
	foreach ( $stats as $s ) {
		$tot += (int) $s->n;
	}
	$pct   = function ( $n ) use ( $tot ) {
		return $tot ? round( 100 * $n / $tot ) . '%' : '—';
	};
	$page  = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t ORDER BY id DESC LIMIT 100 OFFSET %d", ( $page - 1 ) * 100 ) ); // phpcs:ignore
	$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t" ); // phpcs:ignore
	$label = array( 'accept' => 'Accetta tutti', 'reject' => 'Rifiuta', 'save' => 'Scelte personalizzate' );
	?>
	<div class="wrap ipa">
		<?php ip_admin_header( 'Registro consensi', 'Le scelte fatte nel banner dei cookie: servono a dimostrare il consenso.', array( array( 'Esporta CSV', wp_nonce_url( admin_url( 'admin-post.php?action=ip_consent_export' ), 'ip_consent_export' ), 'ipa-btn-line' ), array( 'Impostazioni del banner', admin_url( 'admin.php?page=ip-settings&tab=tracking#ipa-sec-2' ), 'ipa-btn-line' ) ) ); ?>
		<p>Ogni scelta fatta nel banner cookie, con data, versione della cookie policy e un codice anonimo che il browser conserva insieme alla scelta. L’indirizzo IP non viene salvato: solo un’impronta non reversibile. Le righe più vecchie di 24 mesi vengono cancellate.</p>
		<?php if ( ! ip_consent_on() ) : ?>
			<div class="notice notice-warning inline"><p>Il banner non è attivo: inserisci almeno un tag (GTM, GA4 o Google Ads) in <a href="<?php echo esc_url( admin_url( 'admin.php?page=ip-settings&tab=tracking' ) ); ?>">Privacy, cookie e tracciamento</a>.</p></div>
		<?php endif; ?>
		<h2>Ultimi 30 giorni</h2>
		<table class="widefat" style="max-width:640px"><tbody>
			<tr><th>Scelte registrate</th><td><strong><?php echo (int) $tot; ?></strong></td></tr>
			<tr><th>Accetta tutti</th><td><?php echo esc_html( $pct( isset( $stats['accept'] ) ? $stats['accept']->n : 0 ) ); ?></td></tr>
			<tr><th>Rifiuta</th><td><?php echo esc_html( $pct( isset( $stats['reject'] ) ? $stats['reject']->n : 0 ) ); ?></td></tr>
			<tr><th>Scelte personalizzate</th><td><?php echo esc_html( $pct( isset( $stats['save'] ) ? $stats['save']->n : 0 ) ); ?></td></tr>
		</tbody></table>
		<h2>Scelte (<?php echo (int) $count; ?>)</h2>
		<table class="widefat striped">
			<thead><tr><th>Data</th><th>Scelta</th><th>Statistiche</th><th>Marketing</th><th>Versione</th><th>Codice</th><th>Pagina</th></tr></thead>
			<tbody>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="7">Ancora nessuna scelta registrata.</td></tr>
				<?php endif; ?>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( mysql2date( 'j/m/Y H:i', $r->created ) ); ?></td>
						<td><?php echo esc_html( $label[ $r->choice ] ?? $r->choice ); ?></td>
						<td><?php echo $r->analytics ? 'sì' : 'no'; ?></td>
						<td><?php echo $r->marketing ? 'sì' : 'no'; ?></td>
						<td><code><?php echo esc_html( $r->version ); ?></code></td>
						<td><code><?php echo esc_html( $r->cid ); ?></code></td>
						<td><?php echo esc_html( ip_lead_short_url( $r->url ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php if ( $count > 100 ) : ?>
			<p><?php echo paginate_links( array( 'base' => add_query_arg( 'paged', '%#%' ), 'total' => (int) ceil( $count / 100 ), 'current' => $page ) ); // phpcs:ignore ?></p>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'admin_post_ip_consent_export', function () {
	global $wpdb;
	check_admin_referer( 'ip_consent_export' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="consensi-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" );
	fputcsv( $out, array( 'data', 'scelta', 'statistiche', 'marketing', 'versione', 'codice', 'pagina', 'impronta_ip', 'browser' ), ';' );
	$t = ip_consent_table();
	foreach ( (array) $wpdb->get_results( "SELECT * FROM $t ORDER BY id DESC" ) as $r ) { // phpcs:ignore
		fputcsv( $out, array( $r->created, $r->choice, $r->analytics, $r->marketing, $r->version, $r->cid, $r->url, $r->ip_hash, $r->ua ), ';' );
	}
	fclose( $out );
	exit;
} );
