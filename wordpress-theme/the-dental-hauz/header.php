<?php
/**
 * Site header.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
      <div class="nav-extra">
        <?php echo dh_social_links( 'social' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <a class="btn" href="<?php echo esc_url( dh_book_href() ); ?>"><?php esc_html_e( 'Book Appointment', 'the-dental-hauz' ); ?></a>
      </div>
    </nav>
  </div>
</header>

<main id="top">
