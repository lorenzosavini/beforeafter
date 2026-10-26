<?php
/**
 * Versione essenziale dei corsi: solo le sezioni che servono a decidere,
 * ciascuna accorciata, con l'invito a chiedere il dettaglio completo.
 * Il testo completo resta salvato e continua ad aggiornarsi dal sito ufficiale.
 */

defined( 'ABSPATH' ) || exit;

/** Gruppi di sezioni riconosciuti dal titolo. */
function ip_smart_groups() {
	return array(
		'presentazione' => array( 'Presentazione', '/presentazione|in breve|cosa offre|perch[eéè] scegliere|contesto di riferimento|il corso\b/u' ),
		'destinatari'   => array( 'A chi è rivolto e requisiti', '/destinatari|a chi [eè] rivolt|requisit|ammission|modalit[aà] di accesso|accesso al corso|titolo di accesso|classe di concorso/u' ),
		'programma'     => array( 'Programma', '/programma|piano di stud|insegnament|articolazione|attivit[aà] formative|struttura del corso/u' ),
		'sbocchi'       => array( 'Sbocchi professionali', '/sbocch|profili professional|opportunit[aà]|mondo del lavoro/u' ),
		'costi'         => array( 'Costi e iscrizione', '/cost|quota|retta|tasse|iscrizion/u' ),
		'obiettivi'     => array( 'Obiettivi', '/obiettiv|focus|punti chiave|competenze/u' ),
		'frequenza'     => array( 'Durata e frequenza', '/frequenza|durata|calendario|erogazione|cicli attivi/u' ),
		'titolo'        => array( 'Titolo rilasciato', '/conseguimento|titolo rilasciato|prova finale|attestato/u' ),
	);
}

function ip_smart_defaults() {
	return array(
		'on'     => '',
		'groups' => array( 'presentazione', 'destinatari', 'programma', 'sbocchi', 'costi' ),
		'paras'  => 2,
		'items'  => 6,
		'parts'  => array( 'faq', 'steps', 'related' ),
		'docs'   => 2,
		'pages_on'   => '',
		'pages_full' => array(),
	);
}

function ip_smart() {
	$v = ip_vis();
	return wp_parse_args( isset( $v['smart'] ) && is_array( $v['smart'] ) ? $v['smart'] : array(), ip_smart_defaults() );
}

/** Versione essenziale attiva per questo corso? */
function ip_smart_on( $post_id = null ) {
	$s = ip_smart();
	if ( ! $s['on'] ) {
		return false;
	}
	return ! get_post_meta( $post_id ? $post_id : get_the_ID(), '_ip_full', true );
}

function ip_smart_group_of( $title ) {
	$k = ip_vis_key( $title );
	foreach ( ip_smart_groups() as $g => $d ) {
		if ( preg_match( $d[1], $k ) ) {
			return $g;
		}
	}
	return '';
}

/**
 * Accorcia un blocco di HTML: al massimo $paras paragrafi (o tabelle),
 * elenchi fino a $items voci. Restituisce [html, accorciato?].
 */
function ip_smart_trim( $html, $paras, $items ) {
	if ( '' === trim( $html ) ) {
		return array( $html, false );
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="ipw">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	$root = $doc->getElementById( 'ipw' );
	if ( ! $root ) {
		return array( $html, false );
	}
	$cut   = false;
	$count = 0;
	foreach ( iterator_to_array( $root->childNodes ) as $n ) {
		if ( XML_ELEMENT_NODE !== $n->nodeType ) {
			if ( $count >= $paras && trim( $n->textContent ) !== '' ) {
				$root->removeChild( $n );
			}
			continue;
		}
		$tag = strtolower( $n->nodeName );
		if ( $count >= $paras ) {
			$root->removeChild( $n );
			$cut = true;
			continue;
		}
		if ( in_array( $tag, array( 'ul', 'ol' ), true ) ) {
			$li = array();
			foreach ( $n->childNodes as $c ) {
				if ( XML_ELEMENT_NODE === $c->nodeType && 'li' === strtolower( $c->nodeName ) ) {
					$li[] = $c;
				}
			}
			foreach ( array_slice( $li, $items ) as $c ) {
				$n->removeChild( $c );
				$cut = true;
			}
			$count++;
		} elseif ( 'table' === $tag ) {
			$rows = $n->getElementsByTagName( 'tr' );
			for ( $i = $rows->length - 1; $i >= $items + 1; $i-- ) {
				$r = $rows->item( $i );
				$r->parentNode->removeChild( $r );
				$cut = true;
			}
			$count++;
		} elseif ( in_array( $tag, array( 'h3', 'h4', 'h5' ), true ) ) {
			continue; // I sottotitoli non contano, ma seguono il loro testo.
		} else {
			$count++;
		}
	}
	// Un sottotitolo rimasto in fondo senza testo non serve.
	while ( $root->lastChild && XML_ELEMENT_NODE === $root->lastChild->nodeType && preg_match( '/^h[3-5]$/i', $root->lastChild->nodeName ) ) {
		$root->removeChild( $root->lastChild );
		$cut = true;
	}
	$out = '';
	foreach ( $root->childNodes as $c ) {
		$out .= $doc->saveHTML( $c );
	}
	return array( $out, $cut );
}

// Testo del corso: sezioni essenziali, accorciate. Dopo i filtri di Visibilità.
add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_singular( 'corso' ) || ! in_the_loop() || ! ip_smart_on() ) {
		return $html;
	}
	$s    = ip_smart();
	$keep = (array) $s['groups'];
	$p    = max( 1, (int) $s['paras'] );
	$it   = max( 2, (int) $s['items'] );
	$more = '<p class="smart-more"><a href="#richiedi" data-scroll-form>Vuoi il dettaglio completo? Te lo inviamo noi</a></p>';
	if ( ! preg_match_all( '#<h2\b[^>]*>(.*?)</h2>#is', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		list( $t, $cut ) = ip_smart_trim( $html, $p + 1, $it );
		return $t . ( $cut ? $more : '' );
	}
	// Testo prima del primo titolo: un breve «in breve».
	list( $out ) = ip_smart_trim( substr( $html, 0, $m[0][0][1] ), 1, $it );
	$seen   = array();
	$n      = count( $m[0] );
	$groups = array_map( 'ip_smart_group_of', wp_list_pluck( $m[1], 0 ) );
	// Senza una «Presentazione», la prima sezione presenta il corso (spesso «Obiettivi»).
	if ( in_array( 'presentazione', $keep, true ) && ! in_array( 'presentazione', $groups, true ) && ( '' === $groups[0] || ! in_array( $groups[0], $keep, true ) ) ) {
		$groups[0] = 'presentazione';
	}
	for ( $i = 0; $i < $n; $i++ ) {
		$g = $groups[ $i ];
		if ( ! $g || ! in_array( $g, $keep, true ) || isset( $seen[ $g ] ) ) {
			continue; // Sezione non essenziale, o secondo titolo dello stesso argomento.
		}
		$seen[ $g ] = true;
		$start      = $m[0][ $i ][1] + strlen( $m[0][ $i ][0] );
		$end        = $i + 1 < $n ? $m[0][ $i + 1 ][1] : strlen( $html );
		list( $body, $cut ) = ip_smart_trim( substr( $html, $start, $end - $start ), $p, $it );
		$out       .= $m[0][ $i ][0] . $body . ( $cut ? $more : '' );
	}
	return $out;
}, 21 );

// Parti della scheda che la versione essenziale toglie.
add_filter( 'ip_part_on', function ( $on, $key, $post_id ) {
	if ( $on && ip_vis_front() && ip_smart_on( $post_id ) && in_array( $key, (array) ip_smart()['parts'], true ) ) {
		return false;
	}
	return $on;
}, 10, 3 );

// Documenti: solo i primi (di solito brochure e «il corso in breve»).
add_filter( 'ip_course_docs', function ( $docs, $post_id ) {
	if ( ip_vis_front() && ip_smart_on( $post_id ) ) {
		return array_slice( $docs, 0, max( 0, (int) ip_smart()['docs'] ) );
	}
	return $docs;
}, 10, 2 );

add_filter( 'body_class', function ( $c ) {
	if ( is_singular( 'corso' ) && ip_smart_on( get_queried_object_id() ) ) {
		$c[] = 'is-smart';
	}
	return $c;
} );

/* ---------------------------------------------------------------------
 * Pagine informative
 * ------------------------------------------------------------------- */

/** Pagine che non si accorciano mai: legali, contatti, ringraziamento, home. */
function ip_smart_locked_pages() {
	$ids = array( (int) get_option( 'page_on_front' ), (int) ip_opt( 'privacy_page' ), (int) ip_opt( 'cookie_page' ), (int) ip_opt( 'thanks_page' ) );
	foreach ( array( 'contatti', 'grazie' ) as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			$ids[] = (int) $p->ID;
		}
	}
	return array_values( array_filter( $ids ) );
}

function ip_smart_page_on( $id ) {
	$s = ip_smart();
	return ! empty( $s['pages_on'] ) && ! in_array( (int) $id, ip_smart_locked_pages(), true ) && ! in_array( (int) $id, array_map( 'intval', (array) $s['pages_full'] ), true ) && ! get_post_meta( $id, '_ip_full', true ) && ! is_page_template( 'page-templates/landing.php' );
}

/**
 * Accorcia ogni sezione (testo tra due titoli h2) lasciando intatti moduli,
 * schede, elenchi dei corsi e ogni altro blocco del tema. Lavora sulla
 * struttura della pagina, anche quando i titoli sono dentro un contenitore.
 */
function ip_smart_trim_page( $html, $paras, $items, $more ) {
	if ( '' === trim( $html ) ) {
		return $html;
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="ipw">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	$root = $doc->getElementById( 'ipw' );
	if ( ! $root ) {
		return $html;
	}
	$text  = array( 'p', 'ul', 'ol', 'table', 'blockquote', 'pre', 'figure' );
	$state = array( 'count' => 0, 'cut' => false );
	$tpl   = new DOMDocument();
	$tpl->loadHTML( '<?xml encoding="utf-8"?><div>' . $more . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	$tpl   = $tpl->getElementsByTagName( 'div' )->item( 0 )->firstChild;
	$cta   = function ( $before, $parent ) use ( $doc, $tpl, &$state ) {
		if ( ! $state['cut'] ) {
			return;
		}
		$node = $doc->importNode( $tpl, true );
		$before ? $parent->insertBefore( $node, $before ) : $parent->appendChild( $node );
		$state['cut'] = false;
	};
	$walk = function ( $parent ) use ( &$walk, &$state, $text, $paras, $items, $cta ) {
		foreach ( iterator_to_array( $parent->childNodes ) as $n ) {
			if ( XML_ELEMENT_NODE !== $n->nodeType ) {
				continue;
			}
			$tag   = strtolower( $n->nodeName );
			$class = ' ' . $n->getAttribute( 'class' ) . ' ';
			if ( 'h2' === $tag ) {
				$cta( $n, $parent );
				$state['count'] = 0;
				continue;
			}
			// Contenitore dei testi ufficiali: si entra e si continua a contare.
			if ( 'div' === $tag && false !== strpos( $class, ' ufficiale ' ) ) {
				$walk( $n );
				continue;
			}
			if ( in_array( $tag, array( 'h3', 'h4', 'h5' ), true ) ) {
				if ( $state['count'] >= $paras ) {
					$parent->removeChild( $n );
					$state['cut'] = true;
				}
				continue;
			}
			if ( ! in_array( $tag, $text, true ) ) {
				continue; // Moduli, schede, elenchi dei corsi, contatti: intatti.
			}
			if ( $state['count'] >= $paras ) {
				$parent->removeChild( $n );
				$state['cut'] = true;
				continue;
			}
			if ( in_array( $tag, array( 'ul', 'ol' ), true ) ) {
				$li = array();
				foreach ( $n->childNodes as $c ) {
					if ( XML_ELEMENT_NODE === $c->nodeType && 'li' === strtolower( $c->nodeName ) ) {
						$li[] = $c;
					}
				}
				foreach ( array_slice( $li, $items ) as $c ) {
					$n->removeChild( $c );
					$state['cut'] = true;
				}
			} elseif ( 'table' === $tag ) {
				$rows = $n->getElementsByTagName( 'tr' );
				for ( $i = $rows->length - 1; $i >= $items + 1; $i-- ) {
					$r = $rows->item( $i );
					$r->parentNode->removeChild( $r );
					$state['cut'] = true;
				}
			}
			$state['count']++;
		}
		// Fine del contenitore: l'invito va in fondo alla sezione.
		if ( 'ipw' === $parent->getAttribute( 'id' ) || false !== strpos( ' ' . $parent->getAttribute( 'class' ) . ' ', ' ufficiale ' ) ) {
			$cta( null, $parent );
		}
	};
	$walk( $root );
	$out = '';
	foreach ( $root->childNodes as $c ) {
		$out .= $doc->saveHTML( $c );
	}
	return $out;
}

add_filter( 'the_content', function ( $html ) {
	if ( ! ip_vis_front() || ! is_page() || ! in_the_loop() || ! is_main_query() || ! ip_smart_page_on( get_the_ID() ) ) {
		return $html;
	}
	$s   = ip_smart();
	$msg = 'Vorrei ricevere il dettaglio completo su: ' . get_the_title();
	return ip_smart_trim_page( $html, max( 1, (int) $s['paras'] ), max( 2, (int) $s['items'] ), '<p class="smart-more"><a href="#richiedi" data-scroll-form data-msg="' . esc_attr( $msg ) . '">Vuoi il dettaglio completo? Te lo inviamo noi</a></p>' );
}, 21 );
