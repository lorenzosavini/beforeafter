<?php
/**
 * dataLayer: contesto di ogni pagina (tipo, corso, tipologia, prezzo, landing…)
 * ed eventi di visualizzazione (view_item, view_item_list, search), scritti
 * prima di Google Tag Manager così ogni tag li trova già pronti.
 * Nessun dato personale: quelli del modulo partono solo con la richiesta,
 * solo con il consenso marketing (vedi assets/app.js).
 */

defined( 'ABSPATH' ) || exit;

/** Tipo di pagina, uguale per GA4 (content group) e per le regole di GTM. */
function ip_dl_page_type() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_singular( 'corso' ) ) {
		return 'corso';
	}
	if ( is_singular( 'ip_landing' ) || is_page_template( 'page-templates/landing.php' ) ) {
		return 'landing';
	}
	if ( is_post_type_archive( 'corso' ) ) {
		return 'elenco_corsi';
	}
	if ( is_tax( 'tipologia' ) ) {
		return 'tipologia';
	}
	if ( is_tax( 'area' ) ) {
		return 'area';
	}
	if ( is_tax( 'dipartimento' ) ) {
		return 'dipartimento';
	}
	if ( is_search() ) {
		return 'ricerca';
	}
	if ( is_404() ) {
		return 'pagina_non_trovata';
	}
	$thanks = (int) ip_opt( 'thanks_page' );
	if ( $thanks && is_page( $thanks ) ) {
		return 'ringraziamento';
	}
	if ( is_page() ) {
		return 'pagina';
	}
	if ( is_singular( 'post' ) ) {
		return 'articolo';
	}
	if ( is_home() || is_archive() ) {
		return 'blog';
	}
	return 'altro';
}

/** Prezzo del corso come numero (es. «€ 2.200» → 2200), 0 se non indicato. */
function ip_dl_price( $post_id ) {
	$r = ip_meta( 'retta', $post_id );
	if ( ! preg_match( '/([\d.]+(?:,\d+)?)/', $r, $m ) ) {
		return 0;
	}
	return (float) str_replace( array( '.', ',' ), array( '', '.' ), $m[1] );
}

/** Primo termine di una tassonomia, nome o ''. */
function ip_dl_term( $post_id, $tax ) {
	$t = get_the_terms( $post_id, $tax );
	return $t && ! is_wp_error( $t ) ? html_entity_decode( $t[0]->name, ENT_QUOTES, 'UTF-8' ) : '';
}

/** Un corso come «item» dell'ecommerce di GA4 (offerta formativa = catalogo). */
function ip_dl_item( $post_id, $index = 0, $list = '' ) {
	$name = ip_meta( 'short', $post_id ) ? ip_meta( 'short', $post_id ) : get_the_title( $post_id );
	$item = array(
		'item_id'        => get_post_field( 'post_name', $post_id ),
		'item_name'      => html_entity_decode( wp_strip_all_tags( $name ), ENT_QUOTES, 'UTF-8' ),
		'item_brand'     => 'Università Guglielmo Marconi',
		'item_category'  => ip_dl_term( $post_id, 'tipologia' ),
		'item_category2' => ip_dl_term( $post_id, 'area' ),
		'item_category3' => ip_dl_term( $post_id, 'dipartimento' ),
		'price'          => ip_dl_price( $post_id ),
		'quantity'       => 1,
	);
	if ( $list ) {
		$item['item_list_name'] = $list;
		$item['index']          = $index;
	}
	return array_filter( $item, function ( $v ) {
		return '' !== $v;
	} );
}

/** Contesto della pagina (oggetto senza «event», letto da tutti i tag). */
function ip_dl_context() {
	$type = ip_dl_page_type();
	$c    = array(
		'page_type'          => $type,
		'content_group'      => $type,
		'site_name'          => ip_opt( 'brand' ) ? ip_opt( 'brand' ) : get_bloginfo( 'name' ),
		'versione_essenziale' => 'no',
		'currency'           => 'EUR',
	);
	if ( 'corso' === $type ) {
		$id  = get_queried_object_id();
		$tip = ip_dl_term( $id, 'tipologia' );
		$c   = array_merge( $c, array(
			'content_group'      => 'corso: ' . ( $tip ? $tip : 'altro' ),
			'course_id'          => get_post_field( 'post_name', $id ),
			'course_name'        => html_entity_decode( wp_strip_all_tags( ip_meta( 'short', $id ) ? ip_meta( 'short', $id ) : get_the_title( $id ) ), ENT_QUOTES, 'UTF-8' ),
			'course_tipologia'   => $tip,
			'course_area'        => ip_dl_term( $id, 'area' ),
			'course_dipartimento' => ip_dl_term( $id, 'dipartimento' ),
			'course_price'       => ip_dl_price( $id ),
			'course_cfu'         => ip_meta( 'cfu', $id ),
			'course_durata'      => ip_meta( 'durata', $id ),
			'course_lingua'      => ip_meta( 'lingua', $id ) ? ip_meta( 'lingua', $id ) : 'Italiano',
			'course_stato'       => 'chiuse' === ip_meta( 'stato', $id ) ? 'iscrizioni_chiuse' : 'iscrizioni_aperte',
		) );
		if ( function_exists( 'ip_smart_on' ) && ip_smart_on( $id ) ) {
			$c['versione_essenziale'] = 'si';
		}
	} elseif ( in_array( $type, array( 'tipologia', 'area', 'dipartimento' ), true ) ) {
		$t                    = get_queried_object();
		$c['content_group']   = $type . ': ' . html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' );
		$c['course_tipologia'] = 'tipologia' === $type ? html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' ) : '';
	} elseif ( 'landing' === $type ) {
		$id                = get_queried_object_id();
		$c['landing_id']   = get_post_field( 'post_name', $id );
		$c['landing_name'] = html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' );
	} elseif ( 'pagina' === $type ) {
		$id = get_queried_object_id();
		$p  = wp_get_post_parent_id( $id );
		$c['content_group'] = 'pagina: ' . ( $p ? html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ) : html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ) );
		if ( function_exists( 'ip_smart_page_on' ) && ip_smart_page_on( $id ) ) {
			$c['versione_essenziale'] = 'si';
		}
	}
	return array_filter( $c, function ( $v ) {
		return '' !== $v && null !== $v;
	} );
}

/** Eventi di visualizzazione da inviare all'apertura della pagina. */
function ip_dl_events() {
	$ev   = array();
	$type = ip_dl_page_type();
	if ( 'corso' === $type ) {
		$id   = get_queried_object_id();
		$item = ip_dl_item( $id );
		$ev[] = array(
			'event'     => 'view_item',
			'ecommerce' => array(
				'currency' => 'EUR',
				'value'    => $item['price'] ?? 0,
				'items'    => array( $item ),
			),
		);
	}
	// Gli elenchi di corsi (view_item_list) li misura app.js dalle schede in pagina.
	if ( is_search() ) {
		global $wp_query;
		$ev[] = array(
			'event'          => 'search',
			'search_term'    => get_search_query( false ),
			'search_results' => (int) $wp_query->found_posts,
		);
	}
	if ( is_404() ) {
		$ev[] = array( 'event' => 'page_not_found' );
	}
	return $ev;
}

/** Scrive contesto ed eventi nel dataLayer (prima del codice di GTM). */
function ip_dl_print() {
	$out = 'window.dataLayer=window.dataLayer||[];dataLayer.push(' . wp_json_encode( ip_dl_context() ) . ');';
	foreach ( ip_dl_events() as $e ) {
		if ( isset( $e['ecommerce'] ) ) {
			$out .= 'dataLayer.push({ecommerce:null});';
		}
		$out .= 'dataLayer.push(' . wp_json_encode( $e ) . ');';
	}
	echo '<script>' . $out . "</script>\n"; // phpcs:ignore
}
