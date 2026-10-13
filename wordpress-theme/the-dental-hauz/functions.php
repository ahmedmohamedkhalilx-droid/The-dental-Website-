<?php
/**
 * The Dental Hauz theme functions.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DH_VERSION', '2.1.0' );

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
 * The doctors (inc/doctors.php).
 *
 * @return array<int, array>
 */
function dh_doctors() {
	static $d = null;
	if ( null === $d ) {
		$d = require get_template_directory() . '/inc/doctors.php';
	}
	return $d;
}

/**
 * Titles of every page the theme renders itself, by slug.
 *
 * @return array<string,string>
 */
function dh_page_titles() {
	$titles = dh_content( 'titles' );
	$titles['doctors'] = __( 'Doctors', 'the-dental-hauz' );
	foreach ( dh_doctors() as $doctor ) {
		$titles[ $doctor['slug'] ] = $doctor['name'];
	}
	return $titles;
}

/**
 * Create the Doctors page and one page per doctor if they do not exist yet.
 * Runs when the theme is activated and, once per theme version, in the dashboard.
 */
function dh_ensure_pages() {
	$titles = dh_page_titles();
	$wanted = array( 'doctors' );
	foreach ( dh_doctors() as $doctor ) {
		$wanted[] = $doctor['slug'];
	}
	foreach ( $wanted as $slug ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $titles[ $slug ],
				'post_name'   => $slug,
			)
		);
	}
}
add_action( 'after_switch_theme', 'dh_ensure_pages' );

function dh_maybe_ensure_pages() {
	if ( get_option( 'dh_pages_version' ) !== DH_VERSION ) {
		dh_ensure_pages();
		update_option( 'dh_pages_version', DH_VERSION );
	}
}
add_action( 'admin_init', 'dh_maybe_ensure_pages' );

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
	$map['doctors'] = 'doctors';
	foreach ( dh_doctors() as $doctor ) {
		$map[ $doctor['slug'] ] = 'doctor';
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
	printf( '<li><a href="%s">%s</a></li>', esc_url( dh_page_url( 'doctors' ) ), esc_html__( 'Doctors', 'the-dental-hauz' ) );
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
 * Google reviews: the Trustindex plugin's widget, or the widget embedded by its ID. $where is "full" or "home".
 */
function dh_reviews_html( $where = 'full' ) {
	if ( shortcode_exists( 'trustindex' ) ) {
		$key  = ( 'home' === $where ) ? 'dh_reviews_shortcode_home' : 'dh_reviews_shortcode';
		$code = dh_sanitize_reviews_shortcode( get_theme_mod( $key, '[trustindex no-registration=google]' ) );
		return do_shortcode( $code );
	}
	// Without the plugin, the widget's own embed code works from its ID.
	$id = dh_sanitize_trustindex_id( get_theme_mod( 'dh_trustindex_id', '' ) );
	if ( $id ) {
		return '<script defer async src="' . esc_url( 'https://cdn.trustindex.io/loader.js?' . $id ) . '"></script>';
	}
	return dh_reviews_static();
}

/**
 * The clinic's Google reviews (inc/reviews.php).
 */
function dh_reviews_data() {
	static $d = null;
	if ( null === $d ) {
		$d = require get_template_directory() . '/inc/reviews.php';
	}
	return $d;
}

/**
 * Five stars, filled up to $rating.
 */
function dh_stars( $rating ) {
	$out = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= '<svg class="rv__star' . ( $i <= $rating ? ' is-on' : '' ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.5l2.95 6.2 6.8.85-5 4.7 1.3 6.75L12 17.6l-6.05 3.4 1.3-6.75-5-4.7 6.8-.85L12 2.5z"/></svg>';
	}
	return $out;
}

/**
 * Built-in version of the Google reviews block: rating summary and a swipeable row of review cards.
 * Used when the Trustindex plugin and widget ID are not set up.
 */
function dh_reviews_static() {
	$d = dh_reviews_data();
	if ( empty( $d['items'] ) ) {
		return '';
	}
	$url = dh_clinic( 'maps_url' );
	ob_start();
	?>
	<div class="rv" data-rv>
	  <div class="rv__summary">
	    <strong class="rv__name"><?php echo esc_html( $d['name'] ); ?></strong>
	    <span class="rv__stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating out of 5 */ __( 'Rated %d out of 5', 'the-dental-hauz' ), $d['rating'] ) ); ?>"><?php echo dh_stars( $d['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	    <span class="rv__count"><?php echo esc_html( sprintf( /* translators: %d: number of reviews */ __( '%d Google reviews', 'the-dental-hauz' ), $d['count'] ) ); ?></span>
	    <?php if ( $url ) : ?>
	      <a class="btn btn--ghost" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Write a review', 'the-dental-hauz' ); ?></a>
	    <?php endif; ?>
	  </div>
	  <div class="rv__carousel">
	    <button class="rv__nav rv__nav--prev" type="button" aria-label="<?php esc_attr_e( 'Previous reviews', 'the-dental-hauz' ); ?>" hidden>&#8249;</button>
	    <div class="rv__track" tabindex="0" role="group" aria-label="<?php esc_attr_e( 'Google reviews', 'the-dental-hauz' ); ?>">
	      <?php foreach ( $d['items'] as $n => $r ) : ?>
	        <article class="rv__card">
	          <header>
	            <span class="rv__avatar rv__avatar--<?php echo (int) ( $n % 3 ); ?>" aria-hidden="true"><?php echo esc_html( strtoupper( mb_substr( $r['name'], 0, 1 ) ) ); ?></span>
	            <span class="rv__who"><strong><?php echo esc_html( $r['name'] ); ?></strong><span class="rv__src"><?php esc_html_e( 'Google review', 'the-dental-hauz' ); ?></span></span>
	          </header>
	          <span class="rv__stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %d out of 5', 'the-dental-hauz' ), $r['rating'] ) ); ?>"><?php echo dh_stars( $r['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	          <p><?php echo esc_html( $r['text'] ); ?></p>
	          <?php if ( ! empty( $r['more'] ) && $url ) : ?>
	            <a class="rv__more" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Read more', 'the-dental-hauz' ); ?><span class="screen-reader-text"> <?php echo esc_html( $r['name'] ); ?></span></a>
	          <?php endif; ?>
	        </article>
	      <?php endforeach; ?>
	    </div>
	    <button class="rv__nav rv__nav--next" type="button" aria-label="<?php esc_attr_e( 'Next reviews', 'the-dental-hauz' ); ?>" hidden>&#8250;</button>
	  </div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Where the header "Book Appointment" button goes: the booking form on this page if it has one,
 * otherwise the booking form on the home page.
 */
function dh_book_href() {
	if ( is_front_page() ) {
		return '#book';
	}
	if ( is_page() ) {
		$map  = dh_page_map();
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $map[ $slug ] ) && ! in_array( $map[ $slug ], array( 'form', 'forms' ), true ) ) {
			return '#book';
		}
	}
	return home_url( '/#book' );
}

/**
 * Inline social icons (Facebook, Instagram).
 */
function dh_social_icon( $name ) {
	if ( 'facebook' === $name ) {
		return '<svg viewBox="0 0 320 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>';
	}
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.6" r=".6" fill="currentColor"/></svg>';
}

/**
 * Social links as round icon buttons.
 */
function dh_social_links( $class = 'social' ) {
	$items = array(
		'facebook'  => array( dh_clinic( 'facebook' ), __( 'Facebook', 'the-dental-hauz' ) ),
		'instagram' => array( dh_clinic( 'instagram' ), __( 'Instagram', 'the-dental-hauz' ) ),
	);
	$out = '';
	foreach ( $items as $key => $item ) {
		if ( $item[0] ) {
			$out .= '<li><a class="social__link social__link--' . esc_attr( $key ) . '" href="' . esc_url( $item[0] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $item[1] ) . '">' . dh_social_icon( $key ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
	return $out ? '<ul class="' . esc_attr( $class ) . '">' . $out . '</ul>' : '';
}

/**
 * A doctor's portrait, or a brand-coloured panel with the logo mark when there is no photo yet.
 */
function dh_doctor_photo( $doctor, $size = 'large' ) {
	$img = $doctor['photo'] ? dh_img( $doctor['photo'], $size, array( 'alt' => $doctor['name'] ) ) : '';
	if ( $img ) {
		return $img;
	}
	return '<span class="doc-placeholder" role="img" aria-label="' . esc_attr( $doctor['name'] ) . '"><img src="' . esc_url( get_theme_file_uri( 'assets/img/mark-light.png' ) ) . '" alt="" width="300" height="299"></span>';
}
