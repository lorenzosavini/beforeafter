<?php
/**
 * Versioni compatte dei fogli di stile per il caricamento differito:
 * style.css → assets/style.min.css, assets/landing.css → assets/landing.min.css.
 * Uso: php tools/minify-css.php /percorso/del/tema
 */
$theme = rtrim( $argv[1] ?? __DIR__ . '/../infopoint', '/' );
$min   = function ( $css ) {
	$css = preg_replace( '#/\*.*?\*/#s', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	return trim( str_replace( array( ' {', '{ ', ' }', '; ', ': ', ', ', ' >', ';}' ), array( '{', '{', '}', ';', ':', ',', '>', '}' ), $css ) );
};
// Da style.css (radice del tema) i font sono in assets/fonts: nella copia in assets/ diventano fonts/.
file_put_contents( "$theme/assets/style.min.css", str_replace( 'url("assets/', 'url("', $min( file_get_contents( "$theme/style.css" ) ) ) . "\n" );
file_put_contents( "$theme/assets/landing.min.css", $min( file_get_contents( "$theme/assets/landing.css" ) ) . "\n" );
foreach ( array( 'style.min.css', 'landing.min.css' ) as $f ) {
	echo "assets/$f: " . filesize( "$theme/assets/$f" ) . " byte\n";
}
