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
	wp_enqueue_script( 'infopoint', IP_URI . '/assets/app.js', array(), IP_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
}, 100 );

remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

// CSS incorporato: una richiesta in meno, nessun blocco del rendering.
add_action( 'wp_head', function () {
	if ( ! ip_opt( 'inline_css' ) ) {
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
