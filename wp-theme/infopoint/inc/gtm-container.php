<?php
/**
 * Contenitore Google Tag Manager pronto da importare (Amministrazione → Importa
 * contenitore). Lo genera il tema con gli ID inseriti in Impostazioni →
 * Tracciamento: GA4, Google Ads con conversioni avanzate, tutti gli eventi del
 * sito come tag, attivatori e variabili. Gli ID mancanti restano segnaposto.
 */

defined( 'ABSPATH' ) || exit;

/** Eventi del sito, a gruppi: nome del gruppo => [eventi, parametri]. */
function ip_gtm_event_groups() {
	$form = array( 'form_id', 'form_type', 'form_location', 'form_name', 'form_course', 'form_progress', 'fields_completed' );
	return array(
		'Modulo' => array(
			array( 'form_view', 'form_start', 'form_field_complete', 'form_progress', 'form_field_error', 'form_submit_attempt', 'form_submit_error', 'form_abandon', 'form_file_added' ),
			array_merge( $form, array( 'first_field', 'field_name', 'field_position', 'field_required', 'selected_course', 'selected_option', 'newsletter_optin', 'progress_step', 'error_type', 'last_field', 'time_spent', 'file_count', 'file_types' ) ),
		),
		'Contatti' => array(
			array( 'contact_click' ),
			array( 'contact_method', 'link_location', 'link_text', 'link_url' ),
		),
		'Interazioni' => array(
			array( 'cta_click', 'file_download', 'menu_click', 'outbound_click', 'section_open', 'scroll_depth', 'engaged_time', 'search', 'course_filter', 'exit_intent_shown', 'exit_intent_close', 'notice_click', 'notice_close', 'lead_thank_you', 'page_not_found' ),
			array( 'cta_text', 'cta_type', 'cta_location', 'cta_topic', 'file_name', 'file_extension', 'link_text', 'link_url', 'link_domain', 'link_location', 'menu_item', 'section_name', 'section_type', 'percent_scrolled', 'engaged_seconds', 'search_term', 'search_type', 'search_results', 'filter_type', 'filter_value', 'exit_trigger', 'exit_converted', 'notice_text', 'lead_type' ),
		),
	);
}

/** Parametri della richiesta inviata (generate_lead). */
function ip_gtm_lead_params() {
	// Al massimo 25 parametri per evento in GA4: questi più quelli della pagina restano sotto.
	return array( 'lead_type', 'transaction_id', 'form_id', 'form_location', 'lead_course', 'fields_completed', 'form_progress', 'time_to_submit', 'newsletter_optin', 'has_message', 'has_files', 'callback_time', 'traffic_source', 'value', 'currency' );
}

/** Parametri della pagina, inviati con ogni evento GA4. */
function ip_gtm_page_params() {
	return array( 'page_type', 'course_name', 'course_tipologia', 'course_price', 'course_stato', 'landing_name', 'versione_essenziale' );
}

function ip_gtm_container() {
	$ga4   = ip_ga4_id() ? ip_ga4_id() : 'G-XXXXXXXXXX';
	$ads   = preg_replace( '/^AW-/i', '', ip_ads_id() ? ip_ads_id() : 'AW-000000000' );
	$lead  = trim( (string) ip_opt( 'ads_lead_label' ) ) ? trim( (string) ip_opt( 'ads_lead_label' ) ) : 'ETICHETTA_RICHIESTA';
	$call  = trim( (string) ip_opt( 'ads_call_label' ) ) ? trim( (string) ip_opt( 'ads_call_label' ) ) : 'ETICHETTA_CHIAMATA';
	$wa    = trim( (string) ip_opt( 'ads_wa_label' ) ) ? trim( (string) ip_opt( 'ads_wa_label' ) ) : $call;
	$value = (float) str_replace( ',', '.', (string) ip_opt( 'ads_value' ) );
	$gtm   = ip_gtm_id() ? ip_gtm_id() : 'GTM-XXXXXXX';

	$ids  = array( 'tag' => 0, 'trigger' => 0, 'variable' => 0 );
	$next = function ( $k ) use ( &$ids ) {
		return (string) ++$ids[ $k ];
	};
	$base = array( 'accountId' => '0', 'containerId' => '0', 'fingerprint' => '0' );
	// Formato dell'API di Tag Manager (tipi in maiuscolo), lo stesso delle esportazioni.
	$t    = function ( $type, $key, $value ) {
		return array( 'type' => strtoupper( $type ), 'key' => $key, 'value' => (string) $value );
	};
	$map  = function ( $pairs ) {
		$out = array();
		foreach ( $pairs as $k => $v ) {
			$out[] = array( 'type' => 'TEMPLATE', 'key' => $k, 'value' => (string) $v );
		}
		return array( 'type' => 'MAP', 'map' => $out );
	};

	/* Variabili ---------------------------------------------------------- */
	$vars = array();
	$var  = function ( $name, $type, $params ) use ( &$vars, $next, $base ) {
		$vars[] = array_merge( $base, array( 'variableId' => $next( 'variable' ), 'name' => $name, 'type' => $type, 'parameter' => $params ) );
		return '{{' . $name . '}}';
	};
	$const = function ( $name, $value ) use ( $var, $t ) {
		return $var( $name, 'c', array( $t( 'template', 'value', $value ) ) );
	};
	$dlv = array();
	$dl  = function ( $key ) use ( &$dlv, $var, $t ) {
		if ( ! isset( $dlv[ $key ] ) ) {
			$dlv[ $key ] = $var( 'DLV - ' . $key, 'v', array(
				$t( 'integer', 'dataLayerVersion', 2 ),
				$t( 'boolean', 'setDefaultValue', 'false' ),
				$t( 'template', 'name', $key ),
			) );
		}
		return $dlv[ $key ];
	};

	$c_ga4   = $const( 'CONST - GA4 ID misurazione', $ga4 );
	$c_ads   = $const( 'CONST - Google Ads ID conversione', $ads );
	$c_lead  = $const( 'CONST - Ads etichetta Richiesta', $lead );
	$c_call  = $const( 'CONST - Ads etichetta Chiamata', $call );
	$c_wa    = $const( 'CONST - Ads etichetta WhatsApp', $wa );
	$c_value = $const( 'CONST - Valore richiesta', $value ? $value : 1 );

	// Dati forniti dall'utente per le conversioni avanzate (arrivano solo con il consenso marketing).
	$upd = $var( 'Dati utente - conversioni avanzate', 'awec', array(
		$t( 'template', 'mode', 'MANUAL' ),
		$t( 'template', 'email', $dl( 'user_data.email' ) ),
		$t( 'template', 'phone_number', $dl( 'user_data.phone_number' ) ),
		$t( 'template', 'first_name', $dl( 'user_data.address.first_name' ) ),
		$t( 'template', 'last_name', $dl( 'user_data.address.last_name' ) ),
		$t( 'template', 'street', $dl( 'user_data.address.street' ) ),
		$t( 'template', 'city', $dl( 'user_data.address.city' ) ),
		$t( 'template', 'region', $dl( 'user_data.address.region' ) ),
		$t( 'template', 'postal_code', $dl( 'user_data.address.postal_code' ) ),
		$t( 'template', 'country', $dl( 'user_data.address.country' ) ),
	) ); // Tag Manager vuole tutti i campi dell'indirizzo: via e CAP restano vuoti (il modulo non li chiede).

	// Valore della conversione: quello della richiesta o, se manca, la costante.
	$lead_value = $dl( 'value' );

	// Parametri della pagina per ogni evento GA4.
	$rows = array();
	foreach ( ip_gtm_page_params() as $p ) {
		$rows[] = $map( array( 'parameter' => $p, 'parameterValue' => $dl( $p ) ) );
	}
	$settings = $var( 'GA4 - Parametri pagina', 'gtes', array(
		array( 'type' => 'LIST', 'key' => 'eventSettingsTable', 'list' => $rows ),
	) );

	/* Attivatori --------------------------------------------------------- */
	$trigs = array();
	$trig  = function ( $name, $events, $filters = array() ) use ( &$trigs, $next, $base, $t ) {
		$regex = count( $events ) > 1;
		$item  = array_merge( $base, array(
			'triggerId'         => $next( 'trigger' ),
			'name'              => $name,
			'type'              => 'CUSTOM_EVENT',
			'customEventFilter' => array(
				array(
					'type'      => $regex ? 'MATCH_REGEX' : 'EQUALS',
					'parameter' => array(
						$t( 'template', 'arg0', '{{_event}}' ),
						$t( 'template', 'arg1', $regex ? '^(' . implode( '|', $events ) . ')$' : $events[0] ),
					),
				),
			),
		) );
		if ( $filters ) {
			$item['filter'] = array();
			foreach ( $filters as $var_name => $val ) {
				$item['filter'][] = array(
					'type'      => 'EQUALS',
					'parameter' => array( $t( 'template', 'arg0', $var_name ), $t( 'template', 'arg1', $val ) ),
				);
			}
		}
		$trigs[] = $item;
		return $item['triggerId'];
	};
	$init = '2147479573'; // Attivatore integrato «Initialization - All Pages».
	$all  = '2147479553'; // Attivatore integrato «All Pages».

	/* Tag ---------------------------------------------------------------- */
	$tags = array();
	$tag  = function ( $name, $type, $params, $firing, $extra = array() ) use ( &$tags, $next, $base ) {
		// Tag Manager rifiuta i campi presenti ma vuoti: si scrivono solo quelli con un valore.
		$params = array_values( array_filter( $params, function ( $p ) {
			return ! isset( $p['value'] ) || '' !== $p['value'];
		} ) );
		$tags[] = array_merge( $base, array(
			'tagId'            => $next( 'tag' ),
			'name'             => $name,
			'type'             => $type,
			'parameter'        => $params,
			'firingTriggerId'  => (array) $firing,
			'tagFiringOption'  => 'ONCE_PER_EVENT',
			'monitoringMetadata' => array( 'type' => 'MAP' ),
			'consentSettings'  => array( 'consentStatus' => 'NOT_SET' ),
		), $extra );
	};
	$ga4_event = function ( $name, $event, $params, $firing, $ecommerce = false ) use ( $tag, $t, $map, $dl, $c_ga4, $settings ) {
		$rows = array();
		foreach ( $params as $p ) {
			$rows[] = $map( array( 'name' => $p, 'value' => $dl( $p ) ) );
		}
		$ps = array(
			$t( 'template', 'eventName', $event ),
			$t( 'template', 'measurementIdOverride', $c_ga4 ),
			$t( 'template', 'eventSettingsVariable', $settings ),
			$t( 'boolean', 'sendEcommerceData', $ecommerce ? 'true' : 'false' ),
		);
		if ( $ecommerce ) {
			$ps[] = $t( 'template', 'getEcommerceDataFrom', 'dataLayer' );
		}
		if ( $rows ) {
			$ps[] = array( 'type' => 'LIST', 'key' => 'eventParameters', 'list' => $rows );
		}
		$tag( $name, 'gaawe', $ps, $firing );
	};
	$ads_conv = function ( $name, $label, $firing, $enhanced = false, $order = '', $paused = false ) use ( $tag, $t, $c_ads, $upd, $lead_value ) {
		$ps = array(
			$t( 'boolean', 'enableNewCustomerReporting', 'false' ),
			$t( 'boolean', 'enableConversionLinker', 'true' ),
			$t( 'boolean', 'enableProductReporting', 'false' ),
			$t( 'boolean', 'enableEnhancedConversion', $enhanced ? 'true' : 'false' ),
			$t( 'template', 'conversionValue', $lead_value ),
			$t( 'template', 'orderId', $order ),
			$t( 'template', 'conversionId', $c_ads ),
			$t( 'template', 'currencyCode', 'EUR' ),
			$t( 'template', 'conversionLabel', $label ),
			$t( 'boolean', 'rdp', 'false' ),
		);
		if ( $enhanced ) {
			$ps[] = $t( 'template', 'cssProvidedEnhancedConversionValue', $upd );
		}
		$tag( $name, 'awct', $ps, $firing, $paused ? array( 'paused' => true ) : array() );
	};

	// Tag di base.
	$tag( 'GA4 - Tag Google', 'googtag', array(
		$t( 'template', 'tagId', $c_ga4 ),
		array( 'type' => 'LIST', 'key' => 'configSettingsTable', 'list' => array(
			$map( array( 'parameter' => 'send_page_view', 'parameterValue' => 'true' ) ),
			$map( array( 'parameter' => 'content_group', 'parameterValue' => $dl( 'content_group' ) ) ),
		) ),
		$t( 'template', 'eventSettingsVariable', $settings ),
	), $init );
	$tag( 'Google Ads - Tag Google (remarketing)', 'googtag', array(
		$t( 'template', 'tagId', 'AW-' . $c_ads ),
		array( 'type' => 'LIST', 'key' => 'configSettingsTable', 'list' => array(
			$map( array( 'parameter' => 'allow_enhanced_conversions', 'parameterValue' => 'true' ) ),
			$map( array( 'parameter' => 'allow_user_data', 'parameterValue' => 'true' ) ),
		) ),
	), $init );
	$tag( 'Google Ads - Linker conversioni', 'gclidw', array(
		$t( 'boolean', 'enableCrossDomain', 'false' ),
		$t( 'boolean', 'enableUrlPassthrough', 'true' ),
		$t( 'boolean', 'enableCookieOverrides', 'false' ),
	), $all );

	// Richiesta inviata: GA4 (evento chiave) e Google Ads con conversioni avanzate.
	$tr_lead = $trig( 'Evento - generate_lead (richiesta inviata)', array( 'generate_lead' ) );
	$ga4_event( 'GA4 - generate_lead (richiesta inviata)', 'generate_lead', ip_gtm_lead_params(), $tr_lead );
	$ads_conv( 'Google Ads - Conversione Richiesta (avanzata)', $c_lead, $tr_lead, true, $dl( 'transaction_id' ) );

	// Ecommerce dei corsi (catalogo dell'offerta formativa).
	$tr_ecom = $trig( 'Evento - ecommerce corsi', array( 'view_item', 'view_item_list', 'select_item' ) );
	$ga4_event( 'GA4 - Ecommerce corsi', '{{Event}}', array(), $tr_ecom, true );

	// Gruppi di eventi.
	foreach ( ip_gtm_event_groups() as $group => $g ) {
		$tr = $trig( 'Evento - ' . strtolower( $group ), $g[0] );
		$ga4_event( 'GA4 - ' . $group, '{{Event}}', $g[1], $tr );
	}

	// Contatti diretti come conversioni Google Ads.
	$tr_call = $trig( 'Evento - clic su telefono', array( 'contact_click' ), array( $dl( 'contact_method' ) => 'call' ) );
	$tr_wa   = $trig( 'Evento - clic su WhatsApp', array( 'contact_click' ), array( $dl( 'contact_method' ) => 'whatsapp' ) );
	$ads_conv( 'Google Ads - Conversione Chiamata', $c_call, $tr_call );
	$ads_conv( 'Google Ads - Conversione WhatsApp', $c_wa, $tr_wa );

	// Conversioni secondarie per ottimizzare (in pausa: attivale con un'etichetta dedicata).
	$tr_start = $trig( 'Evento - inizio compilazione modulo', array( 'form_start' ) );
	$tr_prog  = $trig( 'Evento - modulo compilato al 100%', array( 'form_progress' ), array( $dl( 'progress_step' ) => '100' ) );
	$ads_conv( 'Google Ads - Secondaria Inizio compilazione (in pausa)', 'ETICHETTA_INIZIO_COMPILAZIONE', $tr_start, false, '', true );
	$ads_conv( 'Google Ads - Secondaria Modulo completo (in pausa)', 'ETICHETTA_MODULO_COMPLETO', $tr_prog, false, '', true );

	// Il valore della costante serve se la richiesta arriva senza valore.
	foreach ( $vars as &$v ) {
		if ( 'DLV - value' === $v['name'] ) {
			$v['parameter'][1] = $t( 'boolean', 'setDefaultValue', 'true' );
			$v['parameter'][]  = $t( 'template', 'defaultValue', $c_value );
		}
	}
	unset( $v );

	$built = array();
	foreach ( array( 'EVENT' => 'Event', 'PAGE_URL' => 'Page URL', 'PAGE_HOSTNAME' => 'Page Hostname', 'PAGE_PATH' => 'Page Path', 'REFERRER' => 'Referrer', 'CLICK_URL' => 'Click URL', 'CLICK_TEXT' => 'Click Text' ) as $type => $name ) {
		$built[] = array( 'accountId' => '0', 'containerId' => '0', 'type' => $type, 'name' => $name );
	}

	return array(
		'exportFormatVersion' => 2,
		'exportTime'          => gmdate( 'Y-m-d H:i:s' ),
		'containerVersion'    => array(
			'path'               => 'accounts/0/containers/0/versions/0',
			'accountId'          => '0',
			'containerId'        => '0',
			'containerVersionId' => '0',
			'name'               => 'Infopoint – misurazione completa (tema ' . IP_VERSION . ')',
			'description'        => 'GA4 con tutti gli eventi del sito ed ecommerce dei corsi; Google Ads con conversione Richiesta (conversioni avanzate), Chiamata, WhatsApp e conversioni secondarie in pausa; modalità di consenso v2 gestita dal sito.',
			'container'          => array(
				'path'         => 'accounts/0/containers/0',
				'accountId'    => '0',
				'containerId'  => '0',
				'name'         => ip_opt( 'brand' ) ? ip_opt( 'brand' ) : get_bloginfo( 'name' ),
				'publicId'     => $gtm,
				'usageContext' => array( 'WEB' ),
				'fingerprint'  => '0',
			),
			'tag'                => $tags,
			'trigger'            => $trigs,
			'variable'           => $vars,
			'builtInVariable'    => $built,
			'fingerprint'        => '0',
		),
	);
}

// Download dal pannello: Impostazioni → Tracciamento → «Scarica il contenitore».
add_action( 'admin_post_ip_gtm_container', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ip_gtm_container' ) ) {
		wp_die( 'Non autorizzato.' );
	}
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="infopoint-gtm-contenitore.json"' );
	echo wp_json_encode( ip_gtm_container(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore
	exit;
} );
