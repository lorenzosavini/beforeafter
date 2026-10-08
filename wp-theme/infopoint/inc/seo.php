<?php
/**
 * SEO essenziale: titolo e descrizione per pagina, Open Graph, dati
 * strutturati (LocalBusiness, Course, BreadcrumbList, Article).
 * La sitemap XML è quella nativa di WordPress (/wp-sitemap.xml).
 * Si disattiva da solo in presenza di Yoast, Rank Math o SEOPress.
 */

defined( 'ABSPATH' ) || exit;

function ip_seo_enabled() {
	return ! ip_opt( 'disable_seo' ) && ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'RankMath' ) && ! defined( 'SEOPRESS_VERSION' );
}

// Sitemap nativa: niente archivio autori.
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

add_action( 'add_meta_boxes', function () {
	if ( ! ip_seo_enabled() ) {
		return;
	}
	add_meta_box( 'ip-seo', 'Anteprima su Google', 'ip_seo_box', array( 'post', 'page', 'corso' ), 'normal', 'low' );
} );

function ip_seo_box( $post ) {
	wp_nonce_field( 'ip_seo', 'ip_seo_nonce' );
	$t = get_post_meta( $post->ID, '_ip_seo_title', true );
	$d = get_post_meta( $post->ID, '_ip_seo_desc', true );
	$n = get_post_meta( $post->ID, '_ip_seo_noindex', true );
	?>
	<p><label for="ip-seo-title"><strong>Titolo</strong> (consigliati 50–60 caratteri)</label><br>
	<input type="text" id="ip-seo-title" name="ip_seo_title" value="<?php echo esc_attr( $t ); ?>" class="widefat" placeholder="<?php echo esc_attr( ip_seo_title_for( $post->ID, false ) ); ?>"></p>
	<p><label for="ip-seo-desc"><strong>Descrizione</strong> (consigliati 140–160 caratteri)</label><br>
	<textarea id="ip-seo-desc" name="ip_seo_desc" rows="2" class="widefat" placeholder="<?php echo esc_attr( ip_seo_desc_for( $post->ID, false ) ); ?>"><?php echo esc_textarea( $d ); ?></textarea></p>
	<p><label><input type="checkbox" name="ip_seo_noindex" value="1" <?php checked( $n, '1' ); ?>> Non mostrare questa pagina nei motori di ricerca</label></p>
	<p class="description">Se lasci i campi vuoti vengono generati automaticamente (il testo grigio è l’anteprima).</p>
	<?php
}

add_action( 'save_post', function ( $id ) {
	if ( ! isset( $_POST['ip_seo_nonce'] ) || ! wp_verify_nonce( $_POST['ip_seo_nonce'], 'ip_seo' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	foreach ( array( 'title', 'desc' ) as $k ) {
		$v = sanitize_text_field( wp_unslash( $_POST[ 'ip_seo_' . $k ] ?? '' ) );
		$v ? update_post_meta( $id, '_ip_seo_' . $k, $v ) : delete_post_meta( $id, '_ip_seo_' . $k );
	}
	! empty( $_POST['ip_seo_noindex'] ) ? update_post_meta( $id, '_ip_seo_noindex', '1' ) : delete_post_meta( $id, '_ip_seo_noindex' );
} );

function ip_city_suffix() {
	return ip_opt( 'brand' );
}

function ip_seo_title_for( $id, $custom = true ) {
	$t = $custom ? get_post_meta( $id, '_ip_seo_title', true ) : '';
	if ( $t ) {
		return $t;
	}
	if ( 'ip_landing' === get_post_type( $id ) ) {
		$d = ip_lp_data( $id );
		return $d['title'] . ' | ' . ip_city_suffix();
	}
	if ( 'corso' === get_post_type( $id ) ) {
		$tip  = ip_course_tipologia( $id );
		$code = ip_meta( 'code', $id );
		return sprintf( '%s%s online%s | %s', $tip ? $tip->name . ' in ' : '', ip_course_name( $id ), $code ? ' (' . $code . ')' : '', ip_city_suffix() );
	}
	if ( (int) get_option( 'page_on_front' ) === (int) $id ) {
		return ip_opt( 'brand' ) . ' | Orientamento e iscrizioni all’Università Marconi' . ( ip_opt( 'city' ) ? ' a ' . ip_opt( 'city' ) : '' );
	}
	return get_the_title( $id ) . ' | ' . ip_city_suffix();
}

function ip_seo_desc_for( $id, $custom = true ) {
	$d = $custom ? get_post_meta( $id, '_ip_seo_desc', true ) : '';
	if ( $d ) {
		return $d;
	}
	$p = get_post( $id );
	if ( 'ip_landing' === $p->post_type ) {
		$d = ip_lp_data( $id );
		return wp_strip_all_tags( $d['sub'] );
	}
	if ( 'corso' === $p->post_type && ! $p->post_excerpt ) {
		$tip   = ip_course_tipologia( $id );
		$facts = array_filter( array( ip_meta( 'code', $id ) ? 'classe ' . ip_meta( 'code', $id ) : '', ip_meta( 'cfu', $id ) ? ip_meta( 'cfu', $id ) . ' CFU' : '', ip_meta( 'durata', $id ) ) );
		return sprintf( '%s in %s all’Università Marconi: %s. Piano di studi, costi, agevolazioni e iscrizione con l’aiuto di un orientatore.', $tip ? $tip->name : 'Corso', ip_course_name( $id ), implode( ', ', $facts ) );
	}
	if ( $p->post_excerpt ) {
		return wp_strip_all_tags( ip_fill( $p->post_excerpt ) );
	}
	return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $p->post_content ) ), 26, '…' );
}

add_filter( 'pre_get_document_title', function ( $title ) {
	if ( ! ip_seo_enabled() ) {
		return $title;
	}
	if ( is_singular() ) {
		return ip_seo_title_for( get_queried_object_id() );
	}
	if ( is_post_type_archive( 'corso' ) ) {
		return 'Corsi di laurea e master online UniMarconi | ' . ip_city_suffix();
	}
	if ( is_tax( 'tipologia' ) ) {
		return single_term_title( '', false ) . ' online UniMarconi: tutti i corsi | ' . ip_city_suffix();
	}
	return $title;
} );

add_filter( 'wp_robots', function ( $r ) {
	if ( ip_seo_enabled() && is_singular() && get_post_meta( get_queried_object_id(), '_ip_seo_noindex', true ) ) {
		$r['noindex'] = true;
	}
	return $r;
} );

add_action( 'wp_head', function () {
	if ( ! ip_seo_enabled() ) {
		return;
	}
	$desc  = '';
	$image = '';
	$type  = 'website';
	$url   = '';
	if ( is_singular() ) {
		$id    = get_queried_object_id();
		$desc  = ip_seo_desc_for( $id );
		$url   = get_permalink( $id );
		$image = get_the_post_thumbnail_url( $id, 'large' );
		$type  = is_singular( 'post' ) ? 'article' : 'website';
	} elseif ( is_front_page() || is_home() ) {
		$desc = ip_opt( 'hero_text' );
		$url  = home_url( '/' );
	} elseif ( is_post_type_archive( 'corso' ) ) {
		$desc = 'Tutti i corsi di laurea triennale, magistrale, ciclo unico, master e corsi per insegnanti dell’Università Marconi. Costi, agevolazioni e iscrizioni con un orientatore.';
		$url  = get_post_type_archive_link( 'corso' );
	} elseif ( is_tax() ) {
		$term = get_queried_object();
		$desc = $term->description ? $term->description : sprintf( 'Tutti i corsi UniMarconi della tipologia %s: classe, CFU, durata e iscrizione con l’Infopoint.', $term->name );
		$url  = get_term_link( $term );
	}
	if ( ! $image && has_custom_logo() ) {
		$image = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
	}
	$title = wp_get_document_title();
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:locale" content="it_IT"><meta property="og:type" content="%s"><meta property="og:title" content="%s"><meta property="og:site_name" content="%s">' . "\n", esc_attr( $type ), esc_attr( $title ), esc_attr( ip_opt( 'brand' ) ) );
	if ( $desc ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	if ( $url ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		if ( ! is_singular() ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		}
	}
	if ( $image ) {
		printf( '<meta property="og:image" content="%s"><meta name="twitter:card" content="summary_large_image">' . "\n", esc_url( $image ) );
	}
}, 2 );

// Dati strutturati in un unico grafo JSON-LD.
add_action( 'wp_footer', function () {
	if ( ! ip_seo_enabled() ) {
		return;
	}
	$home  = home_url( '/' );
	$org   = array(
		'@type'     => 'LocalBusiness',
		'@id'       => $home . '#infopoint',
		'name'      => ip_opt( 'brand' ),
		'legalName' => ip_opt( 'company_name' ),
		'vatID'     => ip_opt( 'vat' ),
		'description' => ip_text( 'disclosure' ),
		'url'       => $home,
		'telephone' => ip_opt( 'phone1' ),
		'email'     => ip_opt( 'email' ),
		'address'   => ip_opt( 'address' ) ? array( '@type' => 'PostalAddress', 'streetAddress' => preg_replace( '/\s+/', ' ', ip_opt( 'address' ) ), 'addressCountry' => 'IT' ) : null,
		'sameAs'    => array_values( array_filter( array( ip_opt( 'facebook' ), ip_opt( 'instagram' ), ip_opt( 'linkedin' ), ip_opt( 'youtube' ), ip_opt( 'tiktok' ) ) ) ),
	);
	if ( has_custom_logo() ) {
		$org['logo'] = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
	}
	$graph = array( array_filter( $org ) );

	if ( is_singular( 'corso' ) ) {
		$id      = get_queried_object_id();
		$graph[] = array_filter( array(
			'@type'               => 'Course',
			'name'                => get_the_title( $id ),
			'description'         => ip_seo_desc_for( $id ),
			'url'                 => get_permalink( $id ),
			'courseCode'          => ip_meta( 'code', $id ),
			'inLanguage'          => ip_meta( 'lingua', $id ) && false !== stripos( ip_meta( 'lingua', $id ), 'ingl' ) ? 'en' : 'it',
			'educationalCredentialAwarded' => ip_course_tipologia( $id ) ? ip_course_tipologia( $id )->name : null,
			'numberOfCredits'     => ip_meta( 'cfu', $id ) ? array( '@type' => 'StructuredValue', 'value' => (int) ip_meta( 'cfu', $id ), 'name' => 'CFU' ) : null,
			'provider'            => array( '@type' => 'CollegeOrUniversity', 'name' => 'Università degli Studi Guglielmo Marconi', 'sameAs' => 'https://www.unimarconi.it' ),
			'hasCourseInstance'   => array( '@type' => 'CourseInstance', 'courseMode' => 'online', 'courseWorkload' => ip_meta( 'durata', $id ) ? ip_meta( 'durata', $id ) : null ),
		) );
	} elseif ( is_singular( 'post' ) ) {
		$id      = get_queried_object_id();
		$graph[] = array_filter( array(
			'@type'         => 'Article',
			'headline'      => get_the_title( $id ),
			'datePublished' => get_the_date( 'c', $id ),
			'dateModified'  => get_the_modified_date( 'c', $id ),
			'image'         => get_the_post_thumbnail_url( $id, 'large' ),
			'publisher'     => array( '@id' => $home . '#infopoint' ),
			'mainEntityOfPage' => get_permalink( $id ),
		) );
	}
	if ( ! empty( $GLOBALS['ip_breadcrumbs'] ) ) {
		$list = array();
		foreach ( $GLOBALS['ip_breadcrumbs'] as $i => $b ) {
			$list[] = array_filter( array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $b[1], 'item' => $b[0] ? $b[0] : null ) );
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore
}, 5 );
