<?php
/**
 * Google Tag Manager con Consent Mode v2 e un banner cookie minimo.
 * Nessun tracciamento finché il visitatore non accetta.
 */

defined( 'ABSPATH' ) || exit;

function ip_gtm_id() {
	$id = strtoupper( trim( (string) ip_opt( 'gtm_id' ) ) );
	return preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ? $id : '';
}

add_action( 'wp_head', function () {
	$gtm = ip_gtm_id();
	if ( $gtm ) {
		?>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}var c=null;try{c=localStorage.getItem('ip_consent')}catch(e){}gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});if(c==='granted'){gtag('consent','update',{ad_storage:'granted',ad_user_data:'granted',ad_personalization:'granted',analytics_storage:'granted'})}
(function(w,d,s,l,i){w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f)})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
		<?php
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
	if ( ip_gtm_id() && ip_opt( 'consent_banner' ) ) {
		$cookie = (int) ip_opt( 'cookie_page' );
		?>
		<div class="consent" data-consent hidden role="dialog" aria-label="Preferenze cookie">
			<p><?php echo esc_html( ip_opt( 'consent_text' ) ); ?><?php if ( $cookie ) : ?> <a href="<?php echo esc_url( get_permalink( $cookie ) ); ?>">Cookie policy</a><?php endif; ?></p>
			<div>
				<button type="button" class="btn btn-line" data-consent-set="denied">Rifiuta</button>
				<button type="button" class="btn btn-primary" data-consent-set="granted">Accetta</button>
			</div>
		</div>
		<?php
	}
	$code = ip_opt( 'footer_code' );
	if ( $code ) {
		echo $code . "\n"; // phpcs:ignore
	}
}, 50 );
