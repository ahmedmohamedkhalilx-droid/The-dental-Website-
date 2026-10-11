<?php
/**
 * The Dental Hauz theme functions.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DH_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/booking.php';

/**
 * Theme setup.
 */
function dh_setup() {
	load_theme_textdomain( 'the-dental-hauz', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 400, 'flex-width' => true, 'flex-height' => true ) );
	register_nav_menus( array( 'primary' => __( 'Primary menu', 'the-dental-hauz' ) ) );
}
add_action( 'after_setup_theme', 'dh_setup' );

/**
 * Styles and scripts.
 */
function dh_assets() {
	wp_enqueue_style(
		'dh-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'dh-style', get_stylesheet_uri(), array( 'dh-fonts' ), DH_VERSION );
	wp_enqueue_script( 'dh-main', get_theme_file_uri( 'assets/main.js' ), array(), DH_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'dh_assets' );

/**
 * Font preconnect hints.
 */
function dh_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'dh_resource_hints', 10, 2 );

/**
 * Use the brand mark as the favicon when no Site Icon has been set.
 */
function dh_favicon() {
	if ( ! has_site_icon() ) {
		printf( "<link rel=\"icon\" type=\"image/png\" href=\"%s\">\n", esc_url( get_theme_file_uri( 'assets/img/mark-dark.png' ) ) );
	}
}
add_action( 'wp_head', 'dh_favicon' );

/**
 * Add a skip link target class and a body class for the home page.
 */
function dh_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'dh-home';
	}
	return $classes;
}
add_filter( 'body_class', 'dh_body_class' );

/**
 * Header logo: the Site Logo from Customizer if set, otherwise the brand logo.
 */
function dh_logo( $variant = 'dark' ) {
	$home  = esc_url( home_url( '/' ) );
	$name  = esc_attr( get_bloginfo( 'name' ) );
	$class = ( 'light' === $variant ) ? 'footer-logo' : 'brand';
	if ( 'dark' === $variant && has_custom_logo() ) {
		echo '<a class="brand" href="' . $home . '" aria-label="' . $name . '">' . wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'custom-logo' ) ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	$file = ( 'light' === $variant ) ? 'logo-light.png' : 'logo-dark.png';
	$size = ( 'light' === $variant ) ? array( 900, 264 ) : array( 879, 258 );
	printf(
		'<a class="%6$s" href="%1$s" aria-label="%2$s"><img src="%3$s" alt="%2$s" width="%4$d" height="%5$d"></a>',
		$home, // phpcs:ignore WordPress.Security.EscapeOutput
		$name, // phpcs:ignore WordPress.Security.EscapeOutput
		esc_url( get_theme_file_uri( 'assets/img/' . $file ) ),
		(int) $size[0],
		(int) $size[1],
		esc_attr( $class )
	);
}

/**
 * Menu shown until an admin builds one under Appearance > Menus.
 */
function dh_fallback_menu() {
	$items = array(
		'#about'    => __( 'About', 'the-dental-hauz' ),
		'#services' => __( 'Services', 'the-dental-hauz' ),
		'#implants' => __( 'Implants', 'the-dental-hauz' ),
		'#visit'    => __( 'Your Visit', 'the-dental-hauz' ),
		'#faq'      => __( 'FAQ', 'the-dental-hauz' ),
	);
	echo '<ul>';
	foreach ( $items as $hash => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( '/' . $hash ) ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Plain-text list of "Label | Value" opening-hours lines from the Customizer.
 *
 * @return array<int, array{0:string,1:string}>
 */
function dh_hours() {
	$raw   = (string) get_theme_mod( 'dh_hours', '' );
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$out   = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$out[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
	}
	return $out;
}
