<?php
/**
 * The Dental Hauz theme functions.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DH_VERSION', '2.0.0' );

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/forms.php';

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
 * Page copy carried over from the previous site (see inc/content.php).
 */
function dh_content( $key = null ) {
	static $c = null;
	if ( null === $c ) {
		$c = require get_template_directory() . '/inc/content.php';
	}
	return null === $key ? $c : ( isset( $c[ $key ] ) ? $c[ $key ] : null );
}

/**
 * Which template part renders which page, by page slug.
 *
 * @return array<string,string>
 */
function dh_page_map() {
	$map = array(
		'about-us'                           => 'about',
		'our-services'                       => 'services',
		'contact-us'                         => 'contact',
		'testimonial'                        => 'testimonial',
		'faqs'                               => 'faqs',
		'forms'                              => 'forms',
		'medical-history-form'               => 'form',
		'request-for-records'                => 'form',
		'dental-treatment-payment-agreement' => 'form',
		'payment-agreement'                  => 'form',
	);
	foreach ( array_keys( dh_content( 'service_pages' ) ) as $slug ) {
		$map[ $slug ] = 'service';
	}
	return $map;
}

/**
 * Make sure the theme's own templates are used for these pages, even if a page builder
 * plugin that built them before is still active.
 */
function dh_force_templates( $template ) {
	if ( is_front_page() ) {
		$t = locate_template( 'front-page.php' );
		return $t ? $t : $template;
	}
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( dh_page_map()[ $slug ] ) ) {
			$t = locate_template( 'page.php' );
			return $t ? $t : $template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'dh_force_templates', 99 );

/**
 * URL of a page by slug (falls back to /slug/ if the page does not exist yet).
 */
function dh_page_url( $slug ) {
	if ( '' === $slug || null === $slug ) {
		return home_url( '/' );
	}
	if ( preg_match( '#^(tel|sms|mailto|https?):#', $slug ) || '#' === $slug[0] ) {
		return $slug;
	}
	static $cache = array();
	if ( ! isset( $cache[ $slug ] ) ) {
		$p              = get_page_by_path( $slug );
		$cache[ $slug ] = $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
	}
	return $cache[ $slug ];
}

/**
 * An image from the media library by attachment ID, with an optional brand-photo fallback.
 */
function dh_img( $id, $size = 'large', $attr = array(), $fallback = '' ) {
	$id = (int) $id;
	if ( $id && wp_attachment_is_image( $id ) ) {
		if ( isset( $attr['alt'] ) && '' !== (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
			unset( $attr['alt'] );
		}
		return wp_get_attachment_image( $id, $size, false, $attr );
	}
	if ( $fallback ) {
		return sprintf(
			'<img src="%s" alt="%s" loading="lazy" decoding="async">',
			esc_url( get_theme_file_uri( 'assets/img/' . $fallback ) ),
			esc_attr( isset( $attr['alt'] ) ? $attr['alt'] : '' )
		);
	}
	return '';
}

/**
 * Header and footer logo.
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
 * Menu shown until an admin builds one under Appearance > Menus. Same structure as the old site.
 */
function dh_fallback_menu() {
	$c = dh_content();
	echo '<ul>';
	printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( '/' ) ), esc_html__( 'Home', 'the-dental-hauz' ) );
	printf( '<li><a href="%s">%s</a></li>', esc_url( dh_page_url( 'about-us' ) ), esc_html__( 'About Us', 'the-dental-hauz' ) );
	printf( '<li class="menu-item-has-children"><a href="%s">%s</a><ul class="sub-menu">', esc_url( dh_page_url( 'our-services' ) ), esc_html__( 'Services', 'the-dental-hauz' ) );
	foreach ( $c['services'] as $s ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( dh_page_url( $s['slug'] ) ), esc_html( $c['service_pages'][ $s['slug'] ]['title'] ) );
	}
	echo '</ul></li>';
	printf( '<li><a href="%s">%s</a></li>', esc_url( dh_page_url( 'testimonial' ) ), esc_html__( 'Testimonial', 'the-dental-hauz' ) );
	printf( '<li><a href="%s">%s</a></li>', esc_url( dh_page_url( 'faqs' ) ), esc_html__( "FAQ's", 'the-dental-hauz' ) );
	printf( '<li><a href="%s">%s</a></li>', esc_url( dh_page_url( 'forms' ) ), esc_html__( 'Forms', 'the-dental-hauz' ) );
	printf( '<li><a href="%s">%s</a></li>', esc_url( dh_page_url( 'contact-us' ) ), esc_html__( 'Contact Us', 'the-dental-hauz' ) );
	echo '</ul>';
}

/**
 * Opening hours as [label, time] rows.
 *
 * @return array<int, array{0:string,1:string}>
 */
function dh_hours() {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', dh_clinic( 'hours' ) ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$out[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
	}
	return $out;
}

/**
 * Address as lines.
 *
 * @return string[]
 */
function dh_address_lines() {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', dh_clinic( 'address' ) ) ) ) );
}

/**
 * tel: link for the clinic phone.
 */
function dh_tel_href() {
	$digits = preg_replace( '/[^0-9+]/', '', dh_clinic( 'phone' ) );
	return $digits ? 'tel:' . $digits : '';
}

/**
 * Embedded Google Map URL.
 */
function dh_map_src() {
	$q = dh_clinic( 'map_query' );
	return $q ? 'https://www.google.com/maps?q=' . rawurlencode( $q ) . '&output=embed' : '';
}

/**
 * Reviews block: the Google reviews widget from the Trustindex plugin, if it is active.
 */
function dh_reviews_html() {
	return shortcode_exists( 'trustindex' ) ? do_shortcode( '[trustindex no-registration=google]' ) : '';
}
