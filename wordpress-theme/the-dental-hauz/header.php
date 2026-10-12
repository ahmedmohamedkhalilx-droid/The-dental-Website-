<?php
/**
 * Site header.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_addr = dh_address_lines();
$dh_mail = dh_clinic( 'email' );
$dh_fb   = dh_clinic( 'facebook' );
$dh_ig   = dh_clinic( 'instagram' );
$dh_tel  = dh_tel_href();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#top"><?php esc_html_e( 'Skip to content', 'the-dental-hauz' ); ?></a>

<div class="topbar">
  <div class="wrap">
    <span class="topbar__contact">
      <?php if ( $dh_addr ) : ?>
        <a href="<?php echo esc_url( dh_clinic( 'maps_url' ) ); ?>"><?php echo esc_html( implode( ', ', $dh_addr ) ); ?></a>
      <?php endif; ?>
      <?php if ( $dh_mail ) : ?>
        <a href="mailto:<?php echo esc_attr( antispambot( $dh_mail ) ); ?>"><?php echo esc_html( antispambot( $dh_mail ) ); ?></a>
      <?php endif; ?>
    </span>
    <span class="topbar__social">
      <?php if ( $dh_fb ) : ?><a href="<?php echo esc_url( $dh_fb ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Facebook', 'the-dental-hauz' ); ?></a><?php endif; ?>
      <?php if ( $dh_ig ) : ?><a href="<?php echo esc_url( $dh_ig ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Instagram', 'the-dental-hauz' ); ?></a><?php endif; ?>
    </span>
  </div>
</div>

<header class="site-header">
  <div class="wrap">
    <?php dh_logo( 'dark' ); ?>
    <button class="menu-toggle" id="menu-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'the-dental-hauz' ); ?>"><span></span><span></span><span></span></button>
    <nav class="nav" id="site-nav" data-open="false" aria-label="<?php esc_attr_e( 'Main', 'the-dental-hauz' ); ?>">
      <?php
      wp_nav_menu(
        array(
          'theme_location' => 'primary',
          'container'      => false,
          'items_wrap'     => '<ul>%3$s</ul>',
          'fallback_cb'    => 'dh_fallback_menu',
          'depth'          => 2,
        )
      );
      ?>
      <?php if ( $dh_tel ) : ?>
        <a class="btn" href="<?php echo esc_url( $dh_tel ); ?>"><?php echo esc_html( dh_clinic( 'phone' ) ); ?></a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="top">
