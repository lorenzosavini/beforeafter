<?php
/**
 * Lettura delle pagine ufficiali di unimarconi.it.
 *
 * Trasforma l'HTML di una scheda corso, di un piano di studio o di una pagina
 * informativa negli stessi dati usati dall'importazione (inc/data/*.json):
 * testo in blocchi Gutenberg, dati del corso, documenti PDF, curricula.
 * È la versione PHP degli script in wp-theme/tools/.
 */

defined( 'ABSPATH' ) || exit;

const IP_SRC_HOST = 'https://www.unimarconi.it/';

function ip_src_categories() {
	return array(
		'Laurea Triennale'                          => 'laurea-triennale',
		'Laurea Magistrale'                         => 'laurea-magistrale',
		'Laurea Magistrale a Ciclo Unico'           => 'laurea-magistrale-a-ciclo-unico',
		'Master di I livello'                       => 'master-di-i-livello',
		'Master di II livello'                      => 'master-di-ii-livello',
		'Master didattica I livello'                => 'master-di-i-livello-discipline-per-la-didattica',
		'Master didattica II livello'               => 'master-di-ii-livello-discipline-per-la-didattica',
		'Formazione iniziale abilitazione docente'  => 'percorsi-abilitanti',
		'Percorsi di specializzazione sul sostegno' => 'specializzazione-sostegno',
		'Corso di formazione'                       => 'corsi-di-formazione',
		'Microcredenziali'                          => 'microcredenziali',
		'Dottorato di ricerca'                      => 'dottorato-di-ricerca',
	);
}

function ip_src_prefix( $tip ) {
	$map = array(
		'laurea-triennale'                => 'Laurea triennale in',
		'laurea-magistrale'               => 'Laurea magistrale in',
		'laurea-magistrale-a-ciclo-unico' => 'Laurea magistrale a ciclo unico in',
		'master-di-i-livello'             => 'Master di I livello in',
		'master-di-ii-livello'            => 'Master di II livello in',
		'master-di-i-livello-discipline-per-la-didattica'  => 'Master di I livello in',
		'master-di-ii-livello-discipline-per-la-didattica' => 'Master di II livello in',
		'corsi-di-formazione'             => 'Corso di formazione in',
		'microcredenziali'                => 'Microcredenziale in',
		'dottorato-di-ricerca'            => 'Dottorato di ricerca in',
	);
	return isset( $map[ $tip ] ) ? $map[ $tip ] : '';
}

function ip_src_slug( $url ) {
	return trim( str_replace( IP_SRC_HOST, '', strtok( $url, '#?' ) ), '/' );
}

function ip_src_text( $html ) {
	return trim( html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES, 'UTF-8' ) );
}

/**
 * Scarica una pagina ufficiale. Restituisce l'HTML o WP_Error.
 */
function ip_src_fetch( $url ) {
	$r = wp_remote_get( $url, array(
		'timeout'    => 25,
		'user-agent' => 'Mozilla/5.0 (compatible; InfopointSync/1.0; +' . home_url( '/' ) . ')',
	) );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	$code = wp_remote_retrieve_response_code( $r );
	if ( 200 !== (int) $code ) {
		return new WP_Error( 'ip_http', 'Risposta HTTP ' . $code . ' da ' . $url, array( 'status' => $code ) );
	}
	return (string) wp_remote_retrieve_body( $r );
}

/* ---------------------------------------------------------------------
 * Pulizia dell'HTML: tiene solo testo, titoli, elenchi, tabelle e link PDF.
 * ------------------------------------------------------------------- */

/**
 * @return array html, pdfs (etichetta, url), links (testo, url).
 */
function ip_src_clean( $fragment ) {
	// Script e stili si tolgono prima: il parser HTML di libxml chiude uno
	// script al primo «</» contenuto nel codice e ne lascerebbe pezzi nel testo.
	$fragment = preg_replace( '#<(script|style|noscript)\b[^>]*>.*?</\1\s*>#is', '', $fragment );
	$doc      = new DOMDocument();
	$prev = libxml_use_internal_errors( true );
	// Il frammento può contenere tag di chiusura orfani (es. «</div>» dopo il
	// titolo): si analizza come corpo di pagina, dove vengono ignorati.
	$doc->loadHTML( '<?xml encoding="UTF-8"><html><body>' . $fragment . '</body></html>', LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );
	$root = $doc->getElementsByTagName( 'body' )->item( 0 );
	$ctx  = array( 'pdfs' => array(), 'links' => array() );
	$out  = $root ? ip_src_walk( $root, $ctx ) : '';

	$x = str_replace( array( "\xEF\xBB\xBF", "\xE2\x80\x8B" ), '', $out );
	$x = preg_replace( '/\s+/u', ' ', $x );
	$x = preg_replace( '#<h2>\s*<h3>(.*?)</h3>\s*</h2>#u', '<h2>$1</h2>', $x );
	for ( $i = 0; $i < 3; $i++ ) {
		$x = preg_replace( '#<(p|li|h2|h3|h4|strong|em|td|th)>\s*</\1>#u', '', $x );
		$x = preg_replace( '#<(ul|ol)>\s*</\1>#u', '', $x );
	}
	$x = preg_replace( '#\s*(</?(?:h2|h3|h4|p|ul|ol|li|table|thead|tbody|tfoot|tr)>)\s*#u', '$1', $x );
	return array( 'html' => trim( $x ), 'pdfs' => $ctx['pdfs'], 'links' => $ctx['links'] );
}

function ip_src_walk( $node, &$ctx ) {
	static $keep = array( 'h2', 'h3', 'h4', 'p', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'strong', 'b', 'em' );
	static $drop = array( 'script', 'style', 'noscript', 'svg', 'video', 'form', 'iframe', 'select', 'textarea', 'nav', 'object', 'img', 'source', 'input', 'hr', 'meta', 'link' );
	$out = '';
	foreach ( $node->childNodes as $n ) {
		if ( XML_TEXT_NODE === $n->nodeType ) {
			$out .= esc_html( $n->nodeValue );
			continue;
		}
		if ( XML_ELEMENT_NODE !== $n->nodeType ) {
			continue;
		}
		$tag = strtolower( $n->nodeName );
		$cls = ' ' . $n->getAttribute( 'class' ) . ' ';
		if ( 'br' === $tag ) {
			$out .= '<br>';
			continue;
		}
		if ( 'button' === $tag && false !== strpos( $cls, 'accordion-button' ) ) {
			$out .= '<h3>' . ip_src_walk( $n, $ctx ) . '</h3>';
			continue;
		}
		if ( in_array( $tag, $drop, true )
			|| ( 'ul' === $tag && ( 0 === strpos( $n->getAttribute( 'id' ), 'menu-' ) || false !== strpos( $cls, 'menu-formativa' ) ) )
			|| ( 'div' === $tag && false !== strpos( $cls, 'wh-video' ) ) ) {
			continue;
		}
		if ( 'div' === $tag && false !== strpos( $cls, ' card-header' ) ) {
			$out .= '<h3>' . ip_src_walk( $n, $ctx ) . '</h3>';
			continue;
		}
		if ( 'a' === $tag ) {
			$href  = trim( $n->getAttribute( 'href' ) );
			$inner = ip_src_walk( $n, $ctx );
			$label = trim( $n->textContent );
			if ( preg_match( '#\.pdf($|\?)#i', $href ) ) {
				$ctx['pdfs'][] = array( $label, $href );
				$out          .= '<a href="' . esc_url( $href ) . '">' . $inner . '</a>';
			} else {
				$ctx['links'][] = array( $label, $href );
				$out           .= $inner;
			}
			continue;
		}
		if ( in_array( $tag, $keep, true ) ) {
			$t    = 'b' === $tag ? 'strong' : $tag;
			$out .= '<' . $t . '>' . ip_src_walk( $n, $ctx ) . '</' . $t . '>';
			continue;
		}
		$out .= ip_src_walk( $n, $ctx );
	}
	return $out;
}

/**
 * Liste usate solo come impaginazione (li che contengono titoli): toglie
 * il livello esterno di ul/li e conserva le liste vere annidate.
 */
function ip_src_unwrap_lists( $x ) {
	$out = '';
	$pos = 0;
	while ( preg_match( '#<(ul|ol)>#', $x, $m, PREG_OFFSET_CAPTURE, $pos ) ) {
		$start = $m[0][1];
		$out  .= substr( $x, $pos, $start - $pos );
		$depth = 0;
		$end   = strlen( $x );
		preg_match_all( '#<(/?)(?:ul|ol)>#', $x, $mm, PREG_OFFSET_CAPTURE, $start );
		foreach ( $mm[0] as $k => $t ) {
			$depth += '/' === $mm[1][ $k ][0] ? -1 : 1;
			if ( 0 === $depth ) {
				$end = $t[1] + strlen( $t[0] );
				break;
			}
		}
		$blk = substr( $x, $start, $end - $start );
		if ( preg_match( '#<h[23]>#', $blk ) ) {
			$res  = '';
			$d    = 0;
			$last = 0;
			preg_match_all( '#<(/?)(ul|ol|li)>#', $blk, $tm, PREG_OFFSET_CAPTURE );
			foreach ( $tm[0] as $k => $t ) {
				$close = '/' === $tm[1][ $k ][0];
				$tag   = $tm[2][ $k ][0];
				if ( 'li' !== $tag && ! $close ) {
					$d++;
				}
				$lvl = $d;
				if ( 'li' !== $tag && $close ) {
					$d--;
				}
				if ( 1 === $lvl ) {
					$res .= substr( $blk, $last, $t[1] - $last );
					$last = $t[1] + strlen( $t[0] );
				}
			}
			$blk = ip_src_unwrap_lists( $res . substr( $blk, $last ) );
		}
		$out .= $blk;
		$pos  = $end;
	}
	return $out . substr( $x, $pos );
}

/**
 * Divide in sezioni sui titoli h2. @return array intro, sections [titolo, html].
 */
function ip_src_sections( $x ) {
	$x     = ip_src_unwrap_lists( $x );
	$parts = preg_split( '#<h2>(.*?)</h2>#u', $x, -1, PREG_SPLIT_DELIM_CAPTURE );
	$intro = trim( array_shift( $parts ) );
	$secs  = array();
	for ( $i = 0; $i + 1 < count( $parts ); $i += 2 ) {
		$secs[] = array( ip_src_text( $parts[ $i ] ), trim( $parts[ $i + 1 ] ) );
	}
	return array( $intro, $secs );
}

/**
 * Pulizia finale: toglie link non PDF, contatti dell'Ateneo, duplicati.
 */
function ip_src_tidy( $c ) {
	$c = preg_replace( '#<h3>\s*Piani di Studio\s*</h3>\s*<ul>.*?</ul>#isu', '', $c );
	$c = preg_replace( '#<a href="[^"]+\.pdf">\s*Visualizza la brochure\s*</a>#iu', '', $c );
	$c = str_replace( 'Riproduci video', '', $c );
	$c = preg_replace( '#<p>\s*Presentazione del Corso di Laurea[^<]*</p>#u', '', $c );
	$c = preg_replace( '#<h3>\s*<p>(.*?)</p>\s*</h3>#u', '<h3>$1</h3>', $c );
	$c = preg_replace( '#<(p|li)>[^<]*(@unimarconi\.it|06-377|\+39-06|\+39 06|tel\.)[^<]*</\1>#iu', '', $c );
	return $c;
}

/* ---------------------------------------------------------------------
 * HTML pulito → blocchi Gutenberg
 * ------------------------------------------------------------------- */

function ip_src_blocks( $x ) {
	$res = array();
	$pos = 0;
	$len = strlen( $x );
	while ( $pos < $len ) {
		if ( ! preg_match( '#<(p|h2|h3|h4|ul|ol|table)>#', $x, $m, PREG_OFFSET_CAPTURE, $pos ) ) {
			$rest = trim( substr( $x, $pos ) );
			if ( '' !== ip_src_text( $rest ) ) {
				$res[] = array( 'p', $rest );
			}
			break;
		}
		$pre = trim( substr( $x, $pos, $m[0][1] - $pos ) );
		if ( '' !== ip_src_text( $pre ) ) {
			$res[] = array( 'p', $pre );
		}
		$tag   = $m[1][0];
		$depth = 0;
		$end   = $len;
		preg_match_all( '#<(/?)' . $tag . '>#', $x, $mm, PREG_OFFSET_CAPTURE, $m[0][1] );
		foreach ( $mm[0] as $k => $t ) {
			$depth += '/' === $mm[1][ $k ][0] ? -1 : 1;
			if ( 0 === $depth ) {
				$end = $t[1] + strlen( $t[0] );
				break;
			}
		}
		$inner = substr( $x, $m[0][1] + strlen( $m[0][0] ), $end - $m[0][1] - strlen( $m[0][0] ) - strlen( '</' . $tag . '>' ) );
		$res[] = array( $tag, trim( $inner ) );
		$pos   = $end;
	}

	$out = array();
	foreach ( $res as $r ) {
		list( $tag, $inner ) = $r;
		if ( '' === ip_src_text( $inner ) ) {
			continue;
		}
		// Etichette di pulsanti rimaste senza pulsante (moduli dell'Ateneo).
		if ( in_array( $tag, array( 'p', 'h2', 'h3', 'h4' ), true ) && preg_match( '/^(?:\s*(?:compila il modulo|richiedi informazioni|scopri di più|clicca qui)\s*)+$/iu', ip_src_text( $inner ) ) ) {
			continue;
		}
		if ( 'p' === $tag ) {
			$inner = trim( preg_replace( '#</?(?:p|h[2-4]|ul|ol|li|table|tr|td|th|thead|tbody|tfoot)>#', ' ', $inner ) );
			$out[] = "<!-- wp:paragraph -->\n<p>" . $inner . "</p>\n<!-- /wp:paragraph -->";
		} elseif ( in_array( $tag, array( 'h2', 'h3', 'h4' ), true ) ) {
			$lv    = (int) $tag[1];
			$inner = trim( preg_replace( '#<(?!/?(strong|em)>)[^>]+>#', '', $inner ) );
			$out[] = '<!-- wp:heading' . ( 2 === $lv ? '' : ' {"level":' . $lv . '}' ) . " -->\n<$tag class=\"wp-block-heading\">" . $inner . "</$tag>\n<!-- /wp:heading -->";
		} elseif ( 'ul' === $tag || 'ol' === $tag ) {
			if ( preg_match( '#<(ul|ol|table|h\d|p)>#', $inner ) ) {
				$out[] = "<!-- wp:html -->\n<$tag>" . $inner . "</$tag>\n<!-- /wp:html -->";
				continue;
			}
			preg_match_all( '#<li>(.*?)</li>#s', $inner, $li );
			$items = '';
			foreach ( $li[1] as $it ) {
				$items .= "<!-- wp:list-item -->\n<li>" . trim( $it ) . "</li>\n<!-- /wp:list-item -->\n";
			}
			$out[] = '<!-- wp:list ' . ( 'ol' === $tag ? '{"ordered":true} ' : '' ) . "-->\n<$tag class=\"wp-block-list\">" . $items . "</$tag>\n<!-- /wp:list -->";
		} elseif ( 'table' === $tag ) {
			$out[] = "<!-- wp:table -->\n<figure class=\"wp-block-table\"><table class=\"has-fixed-layout\">" . str_replace( "\n", '', $inner ) . "</table></figure>\n<!-- /wp:table -->";
		}
	}
	return implode( "\n\n", $out );
}

/* ---------------------------------------------------------------------
 * Schede
 * ------------------------------------------------------------------- */

function ip_src_section_blacklist() {
	return '#gruppi aq|elenco documenti|archivio cicli|dicono di noi|modulistica|^documenti$|pubblicazioni|dottori di ricerca|opinioni|sei interessat|per informazioni|segreteria|ufficio|informazioni utili|testimonianz|sala studio|seminari|^eventi$|docenti|comitato|assicurazione|gruppo aq|pagamento|scegli come|corso in breve|regolamento|quota di iscri|perch[eé] scegliere|open day|pagopa|addebito|prestito|iscriviti ora|contatt|perch[eé]|^$#iu';
}

function ip_src_doc_label( $label, $url ) {
	$f = strtolower( basename( wp_parse_url( $url, PHP_URL_PATH ) ) );
	if ( false !== strpos( $f, 'in_breve' ) ) {
		return 'Il corso in breve';
	}
	if ( false !== strpos( $f, 'regolamento_didattico' ) || false !== stripos( $label, 'regolamento didattico' ) ) {
		return 'Regolamento didattico';
	}
	if ( false !== strpos( $f, 'brochure' ) || false !== stripos( $label, 'brochure' ) ) {
		return 'Brochure';
	}
	if ( preg_match( '/_b6_|cpds|gaq/', $f ) ) {
		return '';
	}
	if ( '' === $label || in_array( strtolower( $label ), array( 'download', 'scarica', 'qui' ), true ) ) {
		$label = str_replace( array( '_', '-' ), ' ', preg_replace( '/\.pdf$/i', '', $f ) );
	}
	return mb_strtoupper( mb_substr( $label, 0, 1 ) ) . mb_substr( $label, 1 );
}

function ip_src_department( $s ) {
	$k = strtolower( trim( preg_replace( '/\(.*?\)/', '', $s ) ) );
	$k = preg_replace( '/^dipartimento (di )?/', '', $k );
	$map = array(
		'scienze economico-aziendali, giuridiche e politiche' => 'Dipartimento di Scienze Economico-Aziendali, Giuridiche e Politiche',
		'scienze umane'                                       => 'Dipartimento di Scienze Umane',
		'scienze ingegneristiche'                             => 'Dipartimento di Scienze Ingegneristiche',
	);
	return isset( $map[ $k ] ) ? $map[ $k ] : trim( $s );
}

/**
 * Scheda corso ufficiale → dati del corso.
 *
 * @param string $url        Indirizzo della scheda.
 * @param string $html       HTML della pagina.
 * @param array  $piano_urls Indirizzi dei piani di studio (per riconoscere i curricula).
 * @return array|null Null se la pagina non è un corso riconosciuto.
 */
function ip_src_course( $url, $html, $piano_urls = array() ) {
	$i = strpos( $html, '<h1' );
	$b = strpos( $html, '</main>' );
	if ( false === $i || false === $b ) {
		return null;
	}
	if ( ! preg_match( '#<h1[^>]*>(.*?)</h1>#s', substr( $html, $i ), $h1 ) ) {
		return null;
	}
	$cat  = preg_match( '#<small[^>]*>(.*?)</small>#s', $h1[1], $cm ) ? ip_src_text( $cm[1] ) : '';
	$cats = ip_src_categories();
	if ( ! isset( $cats[ $cat ] ) ) {
		return null;
	}
	$tip  = $cats[ $cat ];
	$name = preg_replace( '/\s+/u', ' ', ip_src_text( preg_replace( '#<small[^>]*>.*?</small>#s', '', $h1[1] ) ) );
	$pre  = substr( $html, max( 0, $i - 1500 ), min( 1500, $i ) );
	$dep  = preg_match( '#<span>\s*(Dipartimento[^<]*)</span>#', $pre, $dm ) ? trim( $dm[1] ) : '';
	$seg  = substr( $html, $i, $b - $i );
	$img  = preg_match( '#<img[^>]*class="[^"]*wp-post-image[^"]*"[^>]*src="([^"]+)"#', $seg, $im ) || preg_match( '#<img[^>]*src="([^"]+)"[^>]*class="[^"]*wp-post-image#', $seg, $im ) ? $im[1] : '';

	$f = array();
	if ( preg_match_all( '#<strong>\s*(Classe|Titolo|Durata|CFU|Dipartimento|Area|Ore)\s*</strong>\s*(?:<br\s*/?>)?\s*<(?:span|p)[^>]*>(.*?)</(?:span|p)>#s', $seg, $fm, PREG_SET_ORDER ) ) {
		foreach ( $fm as $x ) {
			$k = strtolower( $x[1] );
			if ( ! isset( $f[ $k ] ) ) {
				$f[ $k ] = ip_src_text( $x[2] );
			}
		}
	}
	$a     = strpos( $html, 'CONTENUTO STANDARD', $i );
	$start = false !== $a && $a < $b ? $a + strlen( 'CONTENUTO STANDARD -->' ) : $i + strlen( $h1[0] );
	$clean = ip_src_clean( substr( $html, $start, $b - $start ) );
	$x     = $clean['html'];
	if ( preg_match_all( '#<strong>\s*(Dipartimento|Area)\s*</strong>\s*<p>(.*?)</p>#', $x, $am, PREG_SET_ORDER ) ) {
		foreach ( $am as $m ) {
			$k = strtolower( $m[1] );
			if ( empty( $f[ $k ] ) ) {
				$f[ $k ] = ip_src_text( $m[2] );
			}
		}
	}
	list( $intro, $secs ) = ip_src_sections( $x );

	$bl    = ip_src_section_blacklist();
	$parts = array();
	$quota = '';
	foreach ( $secs as $s ) {
		if ( ! $quota && preg_match( '/quota di (iscri|partec)|^costi?$/iu', $s[0] ) ) {
			// Solo i primi due paragrafi: negli elenchi ci sono contributi accessori.
			preg_match_all( '#<p>(.*?)</p>#s', $s[1], $pp );
			$quota = trim( ip_src_text( implode( ' ', array_slice( $pp[1], 0, 2 ) ) ) );
		}
		if ( preg_match( $bl, $s[0] ) ) {
			continue;
		}
		$body = ip_src_tidy( $s[1] );
		if ( '' === ip_src_text( $body ) ) {
			continue;
		}
		$parts[] = '<h2>' . esc_html( $s[0] ) . '</h2>' . $body;
	}

	// Nota in evidenza (es. «Abilitante alla professione…») e stato iscrizioni.
	$note = array();
	if ( preg_match_all( '#<p>(.*?)</p>#', $intro, $pm ) ) {
		foreach ( $pm[1] as $p ) {
			$t = ip_src_text( $p );
			if ( $t && ! preg_match( '/^(Scienze|Area|Dipartimento|Corso di formazione)/u', $t ) ) {
				$note[] = $t;
			}
		}
	}
	$note  = implode( ' ', $note );
	$evid  = mb_strlen( $note ) < 200 ? trim( $note, '() ' ) : '';
	$stato = preg_match( '/iscrizioni chiuse|bandi sono attualmente chiusi/iu', $evid . ' ' . $note . ' ' . $quota ) ? 'chiuse' : '';
	if ( preg_match( '/^(iscrizioni chiuse|da un.idea di)$/iu', $evid ) ) {
		$evid = '';
	}
	$evid = str_replace( array( 'Periodo apertura Bandi', '.Seguiranno' ), array( 'Periodo apertura bandi: ', '. Seguiranno' ), $evid );

	// Classe / codice e nome breve.
	$code = trim( isset( $f['classe'] ) ? $f['classe'] : '' );
	if ( ! $code && preg_match( '/\(([0-9A-Z]{2,5})\)\s*$/u', $name, $mc ) ) {
		$code = $mc[1];
		$name = trim( substr( $name, 0, -strlen( $mc[0] ) ) );
	}
	$short = ! empty( $f['classe'] ) ? trim( preg_replace( '/\s*\b(L-?\d+|LM-?\d+|LMG-?\d+)\b.*$/u', '', $name ) ) : $name;
	$short = trim( preg_replace( '/^(Corso di formazione in |Master di I+ livello in )/u', '', str_replace( "\xC2\xA0", ' ', $short ) ) );
	$short = preg_replace( '/\s+interclasse$/u', '', $short );
	if ( preg_match( '/^L-7 L-9$/', $code ) ) {
		$code = 'L-7 / L-9';
	}
	$prefix = preg_match( '/^(Corso|Percors)/u', $short ) ? '' : ip_src_prefix( $tip );
	$title  = trim( $prefix ? $prefix . ' ' . $short : $short );
	if ( $code ) {
		$title .= ' (' . $code . ')';
	}

	// Documenti.
	$docs = array();
	foreach ( $clean['pdfs'] as $p ) {
		$l = ip_src_doc_label( $p[0], $p[1] );
		if ( $l && ! in_array( $p[1], wp_list_pluck( $docs, 1 ), true ) ) {
			$docs[] = array( $l, $p[1] );
		}
	}

	// Curricula collegati.
	$curr = array();
	foreach ( $clean['links'] as $l ) {
		$u = strtok( $l[1], '#' );
		if ( in_array( trailingslashit( $u ), $piano_urls, true ) && ! in_array( ip_src_slug( $u ), wp_list_pluck( $curr, 'slug' ), true ) ) {
			$curr[] = array( 'slug' => ip_src_slug( $u ), 'url' => trailingslashit( $u ) );
		}
	}

	$retta = preg_match( '/€\s?([\d\.]+(?:,\d+)?)/u', $quota, $qm ) ? '€ ' . $qm[1] : '';

	return array(
		'slug'         => ip_src_slug( $url ),
		'title'        => $title,
		'short'        => $short,
		'code'         => $code,
		'tipologia'    => $tip,
		'dipartimento' => $dep ? ip_src_department( $dep ) : ( ! empty( $f['dipartimento'] ) ? ip_src_department( $f['dipartimento'] ) : '' ),
		'area'         => isset( $f['area'] ) ? $f['area'] : '',
		'cfu'          => isset( $f['cfu'] ) ? $f['cfu'] : '',
		'durata'       => isset( $f['durata'] ) ? $f['durata'] : ( isset( $f['ore'] ) ? $f['ore'] : '' ),
		'retta'        => $retta,
		'lingua'       => preg_match( '/english|inglese/i', $name ) ? 'Inglese' : '',
		'evidenza'     => $evid,
		'stato'        => $stato,
		'source'       => trailingslashit( strtok( $url, '#?' ) ),
		'image'        => $img,
		'docs'         => $docs,
		'content'      => ip_src_blocks( implode( '', $parts ) ),
		'curricula'    => $curr,
	);
}

/**
 * Inizio del contenuto: dopo il titolo h1 o, se manca, dall'apertura di <main>.
 */
function ip_src_body_start( $html ) {
	$a = strpos( $html, '</h1>' );
	if ( false === $a ) {
		$a = strpos( $html, '<main' );
	}
	return $a;
}

/**
 * Pagina di un piano di studio → nome e contenuto.
 */
function ip_src_curriculum( $url, $html ) {
	$a = ip_src_body_start( $html );
	$b = strpos( $html, '</main>' );
	if ( false === $a || false === $b ) {
		return null;
	}
	$clean = ip_src_clean( substr( $html, $a, $b - $a ) );
	list( , $secs ) = ip_src_sections( $clean['html'] );
	$name = '';
	$body = array();
	$bl   = ip_src_section_blacklist();
	foreach ( $secs as $s ) {
		if ( ! $name && preg_match( '/^(curriculum|orientamento|piano)/iu', $s[0] ) ) {
			$name   = $s[0];
			$body[] = $s[1];
			continue;
		}
		if ( $name && ! preg_match( $bl, $s[0] ) ) {
			$body[] = '<h2>' . esc_html( $s[0] ) . '</h2>' . $s[1];
		}
	}
	if ( ! $name ) {
		$name = preg_match( '#<h1[^>]*>(.*?)</h1>#s', $html, $m ) ? ip_src_text( $m[1] ) : ip_src_slug( $url );
	}
	return array(
		'slug'    => ip_src_slug( $url ),
		'name'    => trim( preg_replace( '/\s*\((?:L|LM|LMG)-?[\d \/L-]+\)$/u', '', $name ) ),
		'content' => ip_src_blocks( ip_src_tidy( implode( '', $body ) ) ),
	);
}

/**
 * Pagina informativa (tasse, immatricolazione…) → contenuto in blocchi.
 */
function ip_src_page( $html ) {
	$a = ip_src_body_start( $html );
	$b = strpos( $html, '</main>' );
	if ( false === $a || false === $b ) {
		return null;
	}
	$clean = ip_src_clean( substr( $html, $a, $b - $a ) );
	list( $intro, $secs ) = ip_src_sections( $clean['html'] );
	$parts = '' !== ip_src_text( $intro ) ? array( ip_src_tidy( $intro ) ) : array();
	foreach ( $secs as $s ) {
		if ( preg_match( '/contatt|per informazioni|informazioni$|link utili|orari|segreteria|ufficio|^$/iu', $s[0] ) ) {
			continue;
		}
		$parts[] = '<h2>' . esc_html( $s[0] ) . '</h2>' . ip_src_tidy( $s[1] );
	}
	return ip_src_blocks( implode( '', $parts ) );
}

/**
 * Pagina «Sedi esami e poli di orientamento» → elenco sedi d'esame.
 */
function ip_src_exam_sites( $html ) {
	$a = strpos( $html, '</h1>' );
	$b = strpos( $html, '</main>' );
	if ( false === $a || false === $b ) {
		return array();
	}
	$x     = substr( $html, $a, $b - $a );
	$x     = preg_replace( '#<(br|/p|/li|/div|/h\d)[^>]*>#i', "\n", $x );
	$x     = preg_replace( '#<h2[^>]*>#i', "\n##", $x );
	$lines = array_values( array_filter( array_map( 'trim', explode( "\n", ip_src_text( preg_replace( '#<(?!/?h2)[^>]+>#', '', str_replace( '##', "\n##", $x ) ) ) ) ) ) );
	$out   = array();
	$reg   = '';
	$n     = count( $lines );
	for ( $k = 0; $k < $n; $k++ ) {
		$l = $lines[ $k ];
		if ( 0 === strpos( $l, '##' ) ) {
			$reg = trim( substr( $l, 2 ) );
			if ( preg_match( '/altri poli/i', $reg ) ) {
				break;
			}
			continue;
		}
		if ( $k + 1 < $n && preg_match( '/^(Sede esam|Polo)/i', $lines[ $k + 1 ] ) && $reg ) {
			$city = $l;
			$k   += 2;
			$addr = array();
			while ( $k < $n && 0 !== strpos( $lines[ $k ], '##' ) && ! ( $k + 1 < $n && preg_match( '/^(Sede esam|Polo)/i', $lines[ $k + 1 ] ) ) ) {
				if ( ! preg_match( '/^(Tel|\+39)|@|Compila il modulo|verrà aggiornata/i', $lines[ $k ] ) ) {
					$addr[] = rtrim( $lines[ $k ], '.' );
				}
				$k++;
			}
			$k--;
			$out[] = array( 'regione' => $reg, 'citta' => $city, 'indirizzo' => implode( ', ', $addr ) );
		}
	}
	return $out;
}

/**
 * Testo principale di una pagina (per accorgersi che è cambiata).
 */
/* ---------------------------------------------------------------------
 * Agevolazioni: una per sezione della pagina ufficiale
 * ------------------------------------------------------------------- */

function ip_src_agev_key( $title ) {
	return sanitize_title( remove_accents( $title ) );
}

/**
 * Righe di testo (paragrafi e voci di elenco) di un frammento pulito, senza
 * i recapiti dell'Ateneo: chi legge deve contattare voi.
 *
 * @return array[] [ 'p'|'li', testo ]
 */
function ip_src_agev_lines( $x ) {
	$out = array();
	$x   = preg_replace( '#</?(ul|ol)>#', '', str_replace( '<br>', "\x01P\x02", (string) $x ) );
	$x   = preg_replace( array( '#<li>#', '#<p>#', '#</(li|p)>#' ), array( "\x01LI\x02", "\x01P\x02", "\x01" ), $x );
	$tag = 'p';
	foreach ( explode( "\x01", $x ) as $part ) {
		if ( preg_match( '/^(LI|P)\x02/', $part, $m ) ) {
			$tag  = strtolower( $m[1] );
			$part = substr( $part, strlen( $m[0] ) );
		}
		$t = trim( preg_replace( '/\s+/u', ' ', ip_src_text( $part ) ) );
		$t = trim( str_replace( '*', '', $t ), " \t\n\r\0\x0B;" );
		if ( '' === $t || preg_match( '/@|tel\.|^\+?39|^per (maggiori )?informazioni|^gli studenti (già|non ancora)|pagina dedicata\.?$|^informazioni$/iu', $t ) ) {
			continue;
		}
		$out[] = array( $tag, mb_strtoupper( mb_substr( $t, 0, 1 ) ) . mb_substr( $t, 1 ) );
	}
	return $out;
}

function ip_src_agev_amount( $n, $decimals = false ) {
	$n = str_replace( '.', '', $n );
	if ( ! $decimals ) {
		return (string) (int) round( (float) str_replace( ',', '.', $n ) );
	}
	return preg_replace( '/,00$/', '', $n );
}

/**
 * Retta annua e rata mensile citate in un testo.
 */
function ip_src_agev_prices( $text ) {
	$retta = '';
	$rata  = '';
	if ( preg_match( '/(?:retta|quota)[^€\n]{0,60}?€\s*([\d.]+(?:,\d{1,2})?)/iu', $text, $m ) || preg_match( '/(?:retta|quota)[^€\n]{0,60}?([\d.]{3,})\s*€/iu', $text, $m ) ) {
		$retta = ip_src_agev_amount( $m[1] );
	}
	if ( preg_match( '/mensil\w*[^€\n]{0,30}?€\s*([\d.]+(?:,\d{1,2})?)/iu', $text, $m ) || preg_match( '/([\d.]+(?:,\d{1,2})?)\s*€\s*al mese/iu', $text, $m ) ) {
		$rata = ip_src_agev_amount( $m[1], true );
	}
	return array( $retta, $rata );
}

/**
 * «A chi è rivolta»: la frase ufficiale, accorciata per stare in tabella.
 */
function ip_src_agev_dest( $t ) {
	if ( preg_match( '/^(.{40,}?[a-z\)])\.\s/u', $t . ' ', $m ) ) {
		$t = $m[1];
	}
	$t = preg_replace( '/^.{0,40}?è un[’\']agevolazione dedicat([ao])/u', 'Dedicat$1', $t );
	$t = preg_replace( '/^L[’\']agevolazione è dedicat([ao])/u', 'Dedicat$1', $t );
	if ( mb_strlen( $t ) > 110 ) {
		$t = trim( preg_replace( '/\s*\([^)]*\)/u', '', $t ) );
	}
	return mb_strlen( $t ) > 160 ? wp_trim_words( $t, 22, '…' ) : rtrim( $t, '.' );
}

/**
 * Legge la pagina ufficiale delle agevolazioni.
 *
 * @param string        $html  Pagina.
 * @param callable|null $fetch Per leggere le pagine dedicate citate nelle sezioni vuote.
 * @return array[] key, title, dest, retta, rata, cond, det, link
 */
function ip_src_agevolazioni( $html, $fetch = null ) {
	$a = ip_src_body_start( $html );
	$b = strpos( $html, '</main>' );
	if ( false === $a || false === $b ) {
		return array();
	}
	$raw = substr( $html, $a, $b - $a );

	// Pagine dedicate citate nelle sezioni («vai alla pagina dedicata»).
	$links = array();
	foreach ( preg_split( '#<h2\b#i', $raw ) as $chunk ) {
		if ( preg_match( '#^[^>]*>(.*?)</h2>#is', $chunk, $t ) && preg_match( '#<a[^>]+href="(https://www\.unimarconi\.it/[^"]+)"[^>]*>[^<]*pagina dedicata#iu', $chunk, $l ) ) {
			$links[ ip_src_agev_key( trim( ip_src_text( $t[1] ), " *\t\n" ) ) ] = $l[1];
		}
	}

	$clean            = ip_src_clean( $raw );
	// Il riquadro laterale (contatti, orari, link utili) inizia con un h4.
	list( , $secs )   = ip_src_sections( preg_replace( '#<h4>.*$#s', '', $clean['html'] ) );
	$out              = array();
	$valid            = '/^(valida|retta|rateizz|sono |non |diritti|tass|importo|sconto|pagamento|agevolazione valida)/iu';
	foreach ( $secs as $s ) {
		$title = trim( preg_replace( '/\s*\*+\s*$/u', '', $s[0] ) );
		if ( '' === $title || preg_match( '/^(contatti|informazioni|link utili|orari)/iu', $title ) ) {
			continue;
		}
		$key   = ip_src_agev_key( $title );
		$parts = preg_split( '#<h3>(.*?)</h3>#u', $s[1], -1, PREG_SPLIT_DELIM_CAPTURE );
		$intro = ip_src_agev_lines( array_shift( $parts ) );
		$subs  = array();
		for ( $i = 0; $i + 1 < count( $parts ); $i += 2 ) {
			$subs[] = array( trim( preg_replace( '/\s*\*+\s*$/u', '', ip_src_text( $parts[ $i ] ) ) ), ip_src_agev_lines( $parts[ $i + 1 ] ) );
		}
		// Parte principale: i corsi di laurea; le altre (es. master) vanno nei dettagli.
		$main  = array();
		$other = array();
		foreach ( $subs as $sub ) {
			if ( ! $main && ! preg_match( '/master/iu', $sub[0] ) ) {
				$main = $sub[1];
			} else {
				$other[] = $sub;
			}
		}
		$dest = '';
		$det  = array();
		if ( $subs ) {
			$rest = $intro;
			if ( $rest && 'p' === $rest[0][0] ) {
				$dest = array_shift( $rest )[1];
			}
			if ( $rest ) {
				$det[] = 'Destinatari: ' . implode( ', ', wp_list_pluck( $rest, 1 ) ) . '.';
			}
			if ( ! $main && ! $other ) {
				$main = $intro;
			}
		} else {
			$main = $intro;
		}
		$lis = wp_list_filter( $main, array( 0 => 'li' ) );
		if ( count( $lis ) > 8 && count( $lis ) === count( $main ) && ! $subs ) {
			// Solo un lungo elenco (es. enti convenzionati).
			$det  = array( implode( "\n", wp_list_pluck( $main, 1 ) ) );
			$main = array();
		} elseif ( ! $dest && $main && ! preg_match( $valid, $main[0][1] ) ) {
			$dest = array_shift( $main )[1];
		}
		foreach ( $other as $sub ) {
			if ( $sub[1] ) {
				$det[] = $sub[0] . ': ' . implode( ' · ', wp_list_pluck( $sub[1], 1 ) );
			}
		}
		$cond = wp_list_pluck( $main, 1 );
		$link = isset( $links[ $key ] ) ? $links[ $key ] : '';
		// Sezione che rimanda a una pagina dedicata: i dati sono lì.
		if ( ! $cond && ! $det && $link && $fetch ) {
			$h = call_user_func( $fetch, $link );
			if ( is_string( $h ) && false !== ( $pa = ip_src_body_start( $h ) ) && false !== ( $pb = strpos( $h, '</main>' ) ) ) {
				$c2   = ip_src_clean( substr( $h, $pa, $pb - $pa ) );
				$l2   = ip_src_agev_lines( $c2['html'] );
				$li2  = wp_list_filter( $l2, array( 0 => 'li' ) );
				$cond = wp_list_pluck( $li2 ? $li2 : $l2, 1 );
			}
		}
		list( $retta, $rata ) = ip_src_agev_prices( implode( "\n", $cond ) . "\n" . $dest );
		$out[] = array(
			'key'   => $key,
			'title' => $title,
			'dest'  => $dest ? ip_src_agev_dest( $dest ) : '',
			'retta' => $retta,
			'rata'  => $rata,
			'cond'  => implode( "\n", $cond ),
			'det'   => implode( "\n", $det ),
			'link'  => $link,
		);
	}
	return $out;
}
