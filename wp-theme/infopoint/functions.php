<?php
/**
 * Infopoint — tema leggero per info point universitari.
 *
 * Tutto ciò che di solito richiede plugin (campi personalizzati, moduli di
 * contatto, raccolta richieste, SEO di base, banner cookie) è qui, in poche
 * centinaia di righe e senza dipendenze.
 */

defined( 'ABSPATH' ) || exit;

define( 'IP_VERSION', '2.3.0' );
define( 'IP_DIR', get_template_directory() );
define( 'IP_URI', get_template_directory_uri() );

require IP_DIR . '/inc/settings.php';
require IP_DIR . '/inc/setup.php';
require IP_DIR . '/inc/content-types.php';
require IP_DIR . '/inc/template-tags.php';
require IP_DIR . '/inc/forms.php';
require IP_DIR . '/inc/leads-admin.php';
require IP_DIR . '/inc/shortcodes.php';
require IP_DIR . '/inc/seo.php';
require IP_DIR . '/inc/consent.php';
require IP_DIR . '/inc/importer.php';
require IP_DIR . '/inc/sync-extract.php';
require IP_DIR . '/inc/sync.php';
