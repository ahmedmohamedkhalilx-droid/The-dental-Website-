<?php
/**
 * Site header.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dh_hours  = dh_hours();
$dh_top    = $dh_hours ? $dh_hours[0] : null;
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
    <span><?php esc_html_e( 'Implants · Oral Surgery · Aligners · Cosmetic Dentistry', 'the-dental-hauz' ); ?></span>
    <?php if ( $dh_top ) : ?>
      <span><?php echo esc_html( trim( $dh_top[0] . ' · ' . $dh_top[1], ' ·' ) ); ?></span>
    <?php endif; ?>
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
          'depth'          => 1,
        )
      );
      ?>
      <a class="btn" href="<?php echo esc_url( home_url( '/#book' ) ); ?>"><?php esc_html_e( 'Book a Consultation', 'the-dental-hauz' ); ?></a>
    </nav>
  </div>
</header>

<main id="top">
