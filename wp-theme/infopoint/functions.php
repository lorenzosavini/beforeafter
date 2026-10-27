<?php
/**
 * Infopoint — tema leggero per info point universitari.
 *
 * Tutto ciò che di solito richiede plugin (campi personalizzati, moduli di
 * contatto, raccolta richieste, SEO di base, banner cookie) è qui, in poche
 * centinaia di righe e senza dipendenze.
 */

defined( 'ABSPATH' ) || exit;

define( 'IP_VERSION', '3.9.1' );
define( 'IP_DIR', get_template_directory() );
define( 'IP_URI', get_template_directory_uri() );

require IP_DIR . '/inc/settings.php';
require IP_DIR . '/inc/setup.php';
require IP_DIR . '/inc/content-types.php';
require IP_DIR . '/inc/template-tags.php';
require IP_DIR . '/inc/forms.php';
require IP_DIR . '/inc/mail.php';
require IP_DIR . '/inc/leads-admin.php';
require IP_DIR . '/inc/admin-ui.php';
require IP_DIR . '/inc/leads-page.php';
require IP_DIR . '/inc/visibility.php';
require IP_DIR . '/inc/smart.php';
require IP_DIR . '/inc/shortcodes.php';
require IP_DIR . '/inc/seo.php';
require IP_DIR . '/inc/datalayer.php';
require IP_DIR . '/inc/gtm-container.php';
require IP_DIR . '/inc/consent.php';
require IP_DIR . '/inc/legal.php';
require IP_DIR . '/inc/importer.php';
require IP_DIR . '/inc/sync-extract.php';
require IP_DIR . '/inc/sync.php';
require IP_DIR . '/inc/upgrade.php';
require IP_DIR . '/inc/landing.php';
