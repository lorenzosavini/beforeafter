<?php
defined( 'ABSPATH' ) || exit;
$landing = is_page_template( 'page-templates/landing.php' );
$wa      = ip_wa_href();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="<?php echo esc_attr( ip_opt( 'color_brand' ) ? ip_opt( 'color_brand' ) : '#225e48' ); ?>">
<?php if ( ip_opt( 'font_brand' ) ) : ?><link rel="preload" href="<?php echo esc_url( IP_URI . '/assets/fonts/montserrat-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin><?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main">Vai al contenuto</a>

<?php if ( ip_opt( 'disclosure_bar' ) && ip_text( 'disclosure' ) ) : ?>
<div class="disclosure" role="note">
	<div class="wrap"><p><?php echo esc_html( ip_text( 'disclosure' ) ); ?><?php if ( ip_page_url( 'chi-siamo' ) ) : ?> <a href="<?php echo esc_url( ip_page_url( 'chi-siamo' ) ); ?>">Chi siamo</a><?php endif; ?></p></div>
</div>
<?php endif; ?>


<header class="hdr">
	<div class="wrap hdr-in">
		<?php // Nelle landing il logo non porta fuori dalla pagina. ?>
		<<?php echo $landing ? 'span' : 'a'; ?> class="brand"<?php echo $landing ? '' : ' href="' . esc_url( home_url( '/' ) ) . '" rel="home"'; ?>>
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand-logo', 'alt' => esc_attr( ip_opt( 'brand' ) ), 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			<?php else : ?>
				<span class="brand-mark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( ip_opt( 'brand' ), 0, 1 ) ) ); ?></span>
				<span class="brand-text">
					<strong><?php echo esc_html( ip_opt( 'brand' ) ); ?></strong>
					<small><?php echo esc_html( ip_role_label() ); ?></small>
				</span>
			<?php endif; ?>
		</<?php echo $landing ? 'span' : 'a'; ?>>

		<?php if ( $landing ) : ?>
			<?php echo ip_phone_link( 'phone1', 'hdr-phone' ); // phpcs:ignore ?>
		<?php else : ?>
			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav" data-nav-toggle>
				<span class="burger" aria-hidden="true"><i></i><i></i><i></i></span><span class="nav-toggle-t">Menu</span>
			</button>
			<nav id="nav" class="nav" aria-label="Principale">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'ip_menu_fallback',
				) );
				?>
				<?php if ( ip_site_on( 'nav_phone' ) ) : ?><?php echo ip_phone_link( 'phone1', 'nav-phone' ); // phpcs:ignore ?><?php endif; ?>
				<?php if ( ip_site_on( 'nav_cta' ) ) : ?><a class="btn btn-accent nav-cta" href="#richiedi" data-scroll-form><?php echo esc_html( ip_opt( 'nav_cta' ) ); ?></a><?php endif; ?>
			</nav>
		<?php endif; ?>
	</div>
</header>

<main id="main">
