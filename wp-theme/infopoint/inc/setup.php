<?php
/**
 * Supporti del tema, asset e rimozione del superfluo di WordPress.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'infopoint', IP_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 320, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/editor.css' );
	// Niente palette e dimensioni arbitrarie nell'editor: chi scrive usa le
	// stesse poche scelte del tema, e il sito resta coerente.
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => 'Verde UniMarconi', 'slug' => 'brand', 'color' => '#225e48' ),
		array( 'name' => 'Rosso UniMarconi', 'slug' => 'accent', 'color' => '#a0300e' ),
		array( 'name' => 'Antracite', 'slug' => 'ink', 'color' => '#373737' ),
		array( 'name' => 'Grigio chiaro', 'slug' => 'soft', 'color' => '#f0f0f0' ),
		array( 'name' => 'Bianco', 'slug' => 'white', 'color' => '#ffffff' ),
	) );
	add_theme_support( 'disable-custom-colors' );
	add_theme_support( 'disable-custom-gradients' );
	add_theme_support( 'editor-gradient-presets', array() );
	add_theme_support( 'disable-custom-font-sizes' );
	remove_theme_support( 'core-block-patterns' );

	register_nav_menus( array(
		'primary' => 'Menu principale',
		'footer'  => 'Menu piè di pagina (offerta)',
		'legal'   => 'Link legali',
	) );

	add_image_size( 'ip-card', 640, 360, true );
	add_image_size( 'ip-logo', 520, 9999, false ); // Logo: basta anche per gli schermi ad alta densità.
} );

add_action( 'init', function () {
	// Intestazioni e script che nessun visitatore usa.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'wp_headers', function ( $h ) {
		unset( $h['X-Pingback'] );
		return $h;
	} );
} );

// Non serve il prefetch DNS verso s.w.org (emoji).
add_filter( 'wp_resource_hints', function ( $urls, $type ) {
	if ( 'dns-prefetch' === $type ) {
		$urls = array_filter( $urls, function ( $u ) {
			return false === strpos( is_array( $u ) ? ( $u['href'] ?? '' ) : $u, 's.w.org' );
		} );
	}
	return $urls;
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function () {
	// Il CSS dei blocchi core (~100 KB) è sostituito dalle poche regole in style.css.
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports' ) as $h ) {
		wp_dequeue_style( $h );
		wp_deregister_style( $h );
	}
	wp_dequeue_script( 'wp-embed' );
	if ( ! is_user_logged_in() ) {
		wp_deregister_script( 'heartbeat' );
	}

	if ( ! ip_opt( 'inline_css' ) ) {
		wp_enqueue_style( 'infopoint', IP_URI . '/style.css', array(), IP_VERSION );
	}
	// Versione compatta (tools/build.sh); il sorgente leggibile con SCRIPT_DEBUG.
	$js = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ! is_readable( IP_DIR . '/assets/app.min.js' ) ? 'app.js' : 'app.min.js';
	wp_enqueue_script( 'infopoint', IP_URI . '/assets/' . $js, array(), IP_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
}, 100 );

remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Tipo di pagina per il CSS critico (assets/critical/<tipo>.css, generato da
 * tools/critical-css.js), '' = pagina non coperta: CSS completo incorporato.
 * Con ?ip_full_css il CSS completo è sempre incorporato (serve allo strumento).
 */
function ip_critical_type() {
	if ( isset( $_GET['ip_full_css'] ) || ! ip_opt( 'inline_css' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return '';
	}
	if ( is_singular( 'ip_landing' ) ) {
		$t = 'landing';
	} elseif ( is_front_page() ) {
		$t = 'home';
	} elseif ( is_singular( 'corso' ) ) {
		$t = 'corso';
	} elseif ( is_page() && ! is_page_template( 'page-templates/landing.php' ) || is_404() ) {
		$t = 'pagina';
	} elseif ( is_post_type_archive( 'corso' ) || is_tax() || is_search() || is_home() || is_category() || is_tag() ) {
		$t = 'elenco';
	} else {
		return '';
	}
	return is_readable( IP_DIR . '/assets/critical/' . $t . '.css' ) ? $t : '';
}

/** Foglio di stile completo caricato senza bloccare la prima visualizzazione. */
function ip_async_css( $url, $id ) {
	printf(
		'<link rel="preload" id="%1$s" href="%2$s" as="style" fetchpriority="low" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="%2$s"></noscript>' . "\n",
		esc_attr( $id ),
		esc_url( $url )
	);
}

// CSS: nella pagina solo quello che serve all'apertura (CSS critico del tipo di
// pagina), il foglio completo arriva subito dopo e resta in cache per le altre
// pagine. Va prima dei colori scelti nel pannello (ip-vars), che così vincono.
add_action( 'wp_head', function () {
	if ( ! ip_opt( 'inline_css' ) ) {
		return;
	}
	$type = ip_critical_type();
	if ( $type ) {
		$crit = get_transient( 'ip_crit_' . $type . '_' . IP_VERSION );
		if ( false === $crit || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			$crit = trim( preg_replace( '#/\*.*?\*/#s', '', (string) file_get_contents( IP_DIR . '/assets/critical/' . $type . '.css' ) ) );
			set_transient( 'ip_crit_' . $type . '_' . IP_VERSION, $crit, WEEK_IN_SECONDS );
		}
		echo '<style id="ip-css-crit">' . str_replace( 'url("assets/', 'url("' . IP_URI . '/assets/', $crit ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		ip_async_css( IP_URI . ( is_readable( IP_DIR . '/assets/style.min.css' ) ? '/assets/style.min.css' : '/style.css' ) . '?ver=' . IP_VERSION, 'ip-css-full' );
		return;
	}
	$css = get_transient( 'ip_css_' . IP_VERSION );
	if ( false === $css || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
		$css = (string) file_get_contents( IP_DIR . '/style.css' );
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = preg_replace( '/\s+/', ' ', $css );
		$css = str_replace( array( ' {', '{ ', ' }', '; ', ': ', ', ', ' >' ), array( '{', '{', '}', ';', ':', ',', '>' ), $css );
		set_transient( 'ip_css_' . IP_VERSION, $css, WEEK_IN_SECONDS );
	}
	$css = str_replace( 'url("assets/', 'url("' . IP_URI . '/assets/', $css );
	echo '<style id="ip-css">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}, 7 );

/**
 * Colori e font scelti in Infopoint → Impostazioni → Aspetto.
 */
function ip_custom_vars() {
	$map  = array(
		'color_brand'       => '--brand',
		'color_brand_dark'  => '--brand-dark',
		'color_brand_deep'  => '--brand-deep',
		'color_accent'      => '--accent',
		'color_accent_dark' => '--accent-dark',
		'color_soft'        => '--soft',
		'color_dark'        => '--dark',
	);
	$vars = '';
	foreach ( $map as $opt => $var ) {
		$c = sanitize_hex_color( ip_opt( $opt ) );
		if ( $c ) {
			$vars .= $var . ':' . $c . ';';
		}
	}
	$brand = sanitize_hex_color( ip_opt( 'color_brand' ) );
	if ( $brand ) {
		// Tinta chiara derivata dal colore principale (8% su bianco).
		list( $r, $g, $b ) = sscanf( $brand, '#%02x%02x%02x' );
		$vars .= sprintf( '--brand-tint:#%02x%02x%02x;--ok:%s;--ok-tint:#%02x%02x%02x;', 255 - ( 255 - $r ) * .1, 255 - ( 255 - $g ) * .1, 255 - ( 255 - $b ) * .1, $brand, 255 - ( 255 - $r ) * .1, 255 - ( 255 - $g ) * .1, 255 - ( 255 - $b ) * .1 );
	}
	if ( ! ip_opt( 'font_brand' ) ) {
		$vars .= '--font:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;';
	}
	return $vars ? ':root{' . $vars . '}' : '';
}
add_action( 'wp_head', function () {
	$v = ip_custom_vars();
	if ( $v ) {
		echo '<style id="ip-vars">' . $v . '</style>' . "\n"; // phpcs:ignore
	}
}, 8 );

// Commenti: un info point non ne ha bisogno.
add_action( 'init', function () {
	if ( ! ip_opt( 'disable_comments' ) ) {
		return;
	}
	foreach ( get_post_types() as $pt ) {
		if ( post_type_supports( $pt, 'comments' ) ) {
			remove_post_type_support( $pt, 'comments' );
			remove_post_type_support( $pt, 'trackbacks' );
		}
	}
	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open', '__return_false', 20 );
	add_filter( 'comments_array', '__return_empty_array', 10 );
	add_action( 'admin_menu', function () {
		remove_menu_page( 'edit-comments.php' );
	} );
	add_action( 'wp_before_admin_bar_render', function () {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'comments' );
	} );
}, 100 );

add_filter( 'excerpt_length', function () {
	return 28;
} );
add_filter( 'excerpt_more', function () {
	return '…';
} );

// Le pagine di ricerca e i ringraziamenti non vanno indicizzati.
add_filter( 'wp_robots', function ( $robots ) {
	$thanks = (int) ip_opt( 'thanks_page' );
	if ( is_search() || is_404() || ( $thanks && is_page( $thanks ) ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
} );

add_filter( 'body_class', function ( $c ) {
	if ( is_page_template( 'page-templates/landing.php' ) ) {
		$c[] = 'is-landing';
	}
	return $c;
} );

// I link interni scritti come «/pagina/» seguono l'indirizzo del sito,
// anche quando WordPress è in una sottocartella (es. localhost/infopoint-site).
add_filter( 'the_content', function ( $html ) {
	$base = untrailingslashit( (string) wp_parse_url( home_url(), PHP_URL_PATH ) );
	if ( '' === $base ) {
		return $html;
	}
	return preg_replace( '#href="/(?!/)(?!' . preg_quote( ltrim( $base, '/' ), '#' ) . '/)#', 'href="' . $base . '/', $html );
}, 20 );

// Pagine tolte dal sito: il vecchio indirizzo porta a una pagina utile, non a un errore.
add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH );
	if ( false !== strpos( $path, 'trasferimento-da-altro-ateneo' ) ) {
		$to = ip_page_url( 'iscriversi' );
		wp_safe_redirect( $to ? $to : home_url( '/' ), 301 );
		exit;
	}
} );

/* ---------------------------------------------------------------------
 * Velocità di caricamento
 * ------------------------------------------------------------------- */

// Le immagini fuori vista cedono la banda a quella principale (anche nel testo).
add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
	if ( isset( $attr['loading'] ) && 'lazy' === $attr['loading'] && empty( $attr['fetchpriority'] ) ) {
		$attr['fetchpriority'] = 'low';
	}
	return $attr;
} );
add_filter( 'wp_content_img_tag', function ( $img ) {
	return false !== strpos( $img, 'loading="lazy"' ) && false === strpos( $img, 'fetchpriority' ) ? str_replace( '<img ', '<img fetchpriority="low" ', $img ) : $img;
} );

// Pagina successiva caricata in anticipo quando il visitatore si ferma su un link:
// il clic apre la pagina quasi all'istante (solo pagine del sito, mai i moduli).
add_filter( 'wp_speculation_rules_configuration', function ( $config ) {
	return is_array( $config ) ? array( 'mode' => 'prefetch', 'eagerness' => 'moderate' ) : $config;
} );

/*
 * Immagini fuori vista caricate solo quando si avvicinano allo schermo (600 px):
 * il browser altrimenti scarica in anticipo le immagini «lazy» entro ~2.500 px e
 * su rete lenta ruba banda a quella principale. Spazio riservato (larghezza e
 * altezza restano), copia in <noscript> per chi non ha JavaScript.
 */
add_filter( 'wp_get_attachment_image', function ( $html, $id, $size, $icon, $attr ) {
	if ( is_admin() || wp_doing_ajax() || is_feed() || empty( $attr['loading'] ) || 'lazy' !== $attr['loading'] || false !== strpos( $html, 'data-src=' ) ) {
		return $html;
	}
	$lazy = str_replace(
		array( ' src="', ' srcset="', ' sizes="', ' loading="lazy"' ),
		array( ' src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="', ' data-srcset="', ' data-sizes="', '' ),
		$html
	);
	$lazy = preg_match( '/ class="/', $lazy ) ? preg_replace( '/ class="/', ' class="lz ', $lazy, 1 ) : str_replace( '<img ', '<img class="lz" ', $lazy );
	return $lazy . '<noscript>' . $html . '</noscript>';
}, 10, 5 );

add_action( 'wp_footer', function () {
	?>
<script>(function(d,w){var a=[].slice.call(d.querySelectorAll('img.lz'));if(!a.length)return;function s(i){if(i.dataset.sizes)i.sizes=i.dataset.sizes;if(i.dataset.srcset)i.srcset=i.dataset.srcset;i.src=i.dataset.src;i.classList.remove('lz')}if(!('IntersectionObserver'in w)){a.forEach(s);return}var o=new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting){o.unobserve(x.target);s(x.target)}})},{rootMargin:'600px 0px'});a.forEach(function(i){o.observe(i)});w.addEventListener('beforeprint',function(){a.forEach(function(i){if(i.classList.contains('lz'))s(i)})})})(document,window)</script>
	<?php
}, 1 );

/**
 * Logo del sito nella misura giusta: il file caricato può essere enorme
 * (es. 2560 px) ma si vede al massimo a 260 px. La versione da 520 px viene
 * ricavata una sola volta dal logo caricato, se manca.
 *
 * @param string $class   Classe dell'immagine.
 * @param string $loading eager|lazy.
 * @param string $sizes   Larghezze mostrate.
 */
function ip_logo_image( $class, $loading = 'eager', $sizes = '(max-width: 600px) 200px, 260px' ) {
	$id = (int) get_theme_mod( 'custom_logo' );
	if ( ! $id ) {
		return '';
	}
	$meta = wp_get_attachment_metadata( $id );
	if ( is_array( $meta ) && empty( $meta['sizes']['ip-logo'] ) && ! empty( $meta['width'] ) && $meta['width'] > 520 && ! get_transient( 'ip_logo_try_' . $id ) ) {
		set_transient( 'ip_logo_try_' . $id, 1, DAY_IN_SECONDS ); // Un tentativo al giorno al massimo.
		$file = get_attached_file( $id );
		if ( $file && file_exists( $file ) ) {
			$size = image_make_intermediate_size( $file, 520, 9999, false );
			if ( $size ) {
				$meta['sizes']['ip-logo'] = $size;
				wp_update_attachment_metadata( $id, $meta );
			}
		}
	}
	$size = is_array( $meta ) && ! empty( $meta['sizes']['ip-logo'] ) ? 'ip-logo' : 'medium_large';
	return wp_get_attachment_image( $id, $size, false, array(
		'class'    => $class,
		'alt'      => esc_attr( ip_opt( 'brand' ) ),
		'loading'  => $loading,
		'decoding' => 'async',
		'sizes'    => $sizes,
		// Priorità normale: quella alta spetta all'immagine principale (WordPress
		// altrimenti la dà da sola alla prima immagine della pagina, cioè al logo).
		'fetchpriority' => 'auto',
	) );
}

/* ---------------------------------------------------------------------
 * Cache del browser e compressione (.htaccess, server Apache/LiteSpeed)
 * ------------------------------------------------------------------- */

function ip_htaccess_rules() {
	return array(
		'# Scritto dal tema Infopoint (Impostazioni → Prestazioni): si toglie spegnendo l\'opzione.',
		'<IfModule mod_expires.c>',
		'ExpiresActive On',
		'ExpiresByType text/css "access plus 1 year"',
		'ExpiresByType application/javascript "access plus 1 year"',
		'ExpiresByType text/javascript "access plus 1 year"',
		'ExpiresByType font/woff2 "access plus 1 year"',
		'ExpiresByType image/webp "access plus 6 months"',
		'ExpiresByType image/avif "access plus 6 months"',
		'ExpiresByType image/jpeg "access plus 6 months"',
		'ExpiresByType image/png "access plus 6 months"',
		'ExpiresByType image/gif "access plus 6 months"',
		'ExpiresByType image/svg+xml "access plus 6 months"',
		'ExpiresByType image/x-icon "access plus 6 months"',
		'</IfModule>',
		'<IfModule mod_headers.c>',
		'<FilesMatch "\.(css|js|woff2)$">',
		'Header set Cache-Control "public, max-age=31536000, immutable"',
		'</FilesMatch>',
		'<FilesMatch "\.(webp|avif|jpe?g|png|gif|svg|ico)$">',
		'Header set Cache-Control "public, max-age=15552000"',
		'</FilesMatch>',
		'</IfModule>',
		'<IfModule mod_deflate.c>',
		'AddOutputFilterByType DEFLATE text/html text/plain text/css text/xml application/javascript text/javascript application/json application/xml image/svg+xml',
		'</IfModule>',
	);
}

/** Scrive o toglie le regole; restituisce lo stato per il pannello. */
function ip_htaccess_sync() {
	$server = isset( $_SERVER['SERVER_SOFTWARE'] ) ? strtolower( (string) $_SERVER['SERVER_SOFTWARE'] ) : '';
	if ( false === strpos( $server, 'apache' ) && false === strpos( $server, 'litespeed' ) ) {
		return 'server';
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	$file = get_home_path() . '.htaccess';
	if ( ( file_exists( $file ) && ! wp_is_writable( $file ) ) || ( ! file_exists( $file ) && ! wp_is_writable( dirname( $file ) ) ) ) {
		return 'scrittura';
	}
	$ok = insert_with_markers( $file, 'Infopoint velocità', ip_opt( 'browser_cache' ) ? ip_htaccess_rules() : array() );
	return $ok ? 'ok' : 'scrittura';
}

// Dopo ogni salvataggio delle impostazioni e a ogni aggiornamento del tema.
add_action( 'update_option_ip_settings', function () {
	ip_opt( 'ip_flush' );
	update_option( 'ip_htaccess_state', ip_htaccess_sync(), false );
}, 20 );
add_action( 'admin_init', function () {
	if ( get_option( 'ip_htaccess_ver' ) !== IP_VERSION && current_user_can( 'manage_options' ) ) {
		update_option( 'ip_htaccess_ver', IP_VERSION, false );
		update_option( 'ip_htaccess_state', ip_htaccess_sync(), false );
	}
} );
