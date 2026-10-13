<?php
/**
 * Minimal stand-ins for the WordPress functions the theme calls, so templates can be rendered and
 * the form handler exercised without a WordPress install. Test-only: never shipped in the theme zip.
 */
define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'ENT_QUOTES_', ENT_QUOTES );

$GLOBALS['dh_mods']       = array();
$GLOBALS['dh_options']    = array( 'admin_email' => 'admin@example.test' );
$GLOBALS['dh_mail']       = array();
$GLOBALS['dh_posts']      = array();
$GLOBALS['dh_transients'] = array();
$GLOBALS['dh_hooks']      = array();
$GLOBALS['dh_is_admin']   = false;
$GLOBALS['dh_page']       = array( 'slug' => '', 'title' => '' );
$GLOBALS['dh_shortcode']  = ! getenv( 'DH_PREVIEW' );
$GLOBALS['dh_video']      = true;
$GLOBALS['dh_loop']       = 0;
$GLOBALS['dh_styles']     = array();
$GLOBALS['dh_scripts']    = array();
$GLOBALS['wp']            = (object) array( 'request' => '' );

class DH_Redirect extends Exception {}

function dh_theme_dir() { return getenv( 'DH_THEME_DIR' ); }
function get_template_directory() { return dh_theme_dir(); }
/**
 * Preview mode (DH_PREVIEW=1, DH_BASE=/path): absolute URLs under a base path, real photos from the old
 * site's media library (DH_ATTACHMENTS=json), inert forms and a preview banner. Used by tools/build-preview.php.
 */
function dh_base() { return getenv( 'DH_BASE' ) ? rtrim( getenv( 'DH_BASE' ), '/' ) : 'https://clinic.test'; }
function dh_preview() { return (bool) getenv( 'DH_PREVIEW' ); }
function dh_attachments() { static $a = null; if ( null === $a ) { $f = getenv( 'DH_ATTACHMENTS' ); $a = $f ? json_decode( file_get_contents( $f ), true ) : array(); } return $a; }
function get_theme_file_uri( $p = '' ) { return getenv( 'DH_BASE' ) ? dh_base() . '/' . $p : $p; }
function get_stylesheet_uri() { return getenv( 'DH_BASE' ) ? dh_base() . '/style.css' : 'style.css'; }
function add_action( $h, $cb ) { $GLOBALS['dh_hooks'][ $h ][] = $cb; }
function add_filter( $h, $cb ) { $GLOBALS['dh_hooks'][ $h ][] = $cb; }
function apply_filters( $h, $v ) { return $v; }
function add_theme_support() {}
function register_nav_menus() {}
function register_post_type() {}
function load_theme_textdomain() {}
function wp_enqueue_style( $h, $src ) { $GLOBALS['dh_styles'][ $h ] = $src; }
function wp_enqueue_script( $h, $src ) { $GLOBALS['dh_scripts'][ $h ] = $src; }
function has_site_icon() { return false; }
function has_custom_logo() { return false; }
function get_theme_mod( $k, $d = '' ) { return array_key_exists( $k, $GLOBALS['dh_mods'] ) ? $GLOBALS['dh_mods'][ $k ] : $d; }
function get_option( $k ) { return $GLOBALS['dh_options'][ $k ] ?? ''; }
function update_option( $k, $v ) { $GLOBALS['dh_options'][ $k ] = $v; }
function home_url( $p = '' ) { return dh_base() . $p; }
function admin_url( $p = '' ) { return dh_preview() ? '#preview' : 'https://clinic.test/wp-admin/' . $p; }
function user_trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function get_bloginfo( $k = '' ) { return 'charset' === $k ? 'UTF-8' : 'The Dental Hauz'; }
function bloginfo( $k = '' ) { echo get_bloginfo( $k ); }
function language_attributes() { echo 'lang="en-US"'; }
function wp_head() {
	foreach ( $GLOBALS['dh_styles'] as $h => $src ) { echo '<link rel="stylesheet" id="' . esc_attr( $h ) . '" href="' . esc_attr( $src ) . '">' . "\n"; }
	foreach ( $GLOBALS['dh_hooks']['wp_head'] ?? array() as $cb ) { call_user_func( $cb ); }
	if ( dh_preview() ) { echo '<meta name="robots" content="noindex, nofollow">' . "\n"; }
}
function wp_footer() {
	foreach ( $GLOBALS['dh_scripts'] as $src ) { echo '<script src="' . esc_attr( $src ) . '" defer></script>' . "\n"; }
	if ( dh_preview() ) { echo '<script>document.querySelectorAll("form.form").forEach(function(f){f.addEventListener("submit",function(e){e.preventDefault();var n=f.querySelector(".form-note");if(!n){n=document.createElement("p");n.className="form-note";n.setAttribute("role","status");f.insertBefore(n,f.querySelector(".fields"));}n.textContent="This is a preview. The form sends an email to the clinic once the theme is installed on thedentalhauz.com.";n.scrollIntoView({block:"center"});});});</script>' . "\n"; }
}
function wp_body_open() {}
function body_class() { echo 'class="page"'; }
function post_class( $c = '' ) { echo 'class="' . esc_attr( $c ) . '"'; }
function wp_date( $f ) { return gmdate( $f ); }
function antispambot( $e ) { return $e; }
function current_user_can() { return $GLOBALS['dh_is_admin']; }
function wp_nav_menu( $a ) { if ( ! empty( $a['fallback_cb'] ) ) { call_user_func( $a['fallback_cb'] ); } }
function __( $s ) { return $s; }
function esc_html__( $s ) { return $s; }
function esc_html_e( $s ) { echo htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr_e( $s ) { echo htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_url_raw( $s ) { return (string) $s; }
function wp_validate_redirect( $url, $fallback ) { return 0 === strpos( $url, dh_base() ) ? $url : $fallback; }
function wp_unslash( $s ) { return $s; }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_email( $s ) { return filter_var( trim( (string) $s ), FILTER_VALIDATE_EMAIL ) ? trim( $s ) : ''; }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function is_email( $s ) { return (bool) filter_var( $s, FILTER_VALIDATE_EMAIL ); }
function rest_sanitize_boolean( $v ) { return (bool) $v; }
function number_format_i18n( $n ) { return number_format( $n ); }
function wp_specialchars_decode( $s ) { return $s; }
function add_query_arg( $k, $v = null, $url = null ) {
	if ( is_array( $k ) ) { $url = $v; $pairs = $k; } else { $pairs = array( $k => $v ); }
	return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $pairs );
}
function remove_query_arg( $keys, $url ) { return preg_replace( '/\?.*$/', '', $url ); }
function wp_safe_redirect( $url ) { throw new DH_Redirect( $url ); }
function get_transient( $k ) { return $GLOBALS['dh_transients'][ $k ] ?? false; }
function set_transient( $k, $v ) { $GLOBALS['dh_transients'][ $k ] = $v; }
function wp_insert_post( $a ) { $GLOBALS['dh_posts'][] = $a; return count( $GLOBALS['dh_posts'] ); }
function wp_mail( $to, $subject, $body, $headers = array() ) {
	$GLOBALS['dh_mail'][] = compact( 'to', 'subject', 'body', 'headers' );
	return true;
}

// Media library: any attachment ID counts as an image and renders as a stand-in photo.
function wp_attachment_is_image( $id ) { return true; }
function get_post_meta() { return ''; }
function wp_get_attachment_image( $id, $size = 'large', $icon = false, $attr = array() ) {
	$attrs = '';
	foreach ( $attr as $k => $v ) { $attrs .= ' ' . $k . '="' . esc_attr( $v ) . '"'; }
	$map = dh_attachments();
	if ( $map ) {
		$attrs .= ( isset( $attr['loading'] ) ? '' : ' loading="lazy"' ) . ( isset( $attr['alt'] ) ? '' : ' alt=""' );
		return '<img src="' . esc_attr( $map[ $id ] ?? '' ) . '" data-attachment="' . (int) $id . '"' . $attrs . '>';
	}
	return '<img src="assets/img/aligners.jpg" width="736" height="1104" data-attachment="' . (int) $id . '"' . $attrs . '>';
}
function wp_get_attachment_image_url( $id ) { $map = dh_attachments(); return $map ? ( $map[ $id ] ?? '' ) : 'assets/img/aligners.jpg'; }
function wp_get_attachment_url( $id ) { $map = dh_attachments(); if ( $map ) { return $map[ $id ] ?? false; } return $GLOBALS['dh_video'] ? 'assets/video.mp4' : false; }

// Pages, loop and templates.
function dh_known_titles() { return dh_page_titles(); }
function get_page_by_path( $slug ) { $ex = $GLOBALS['dh_existing_pages'] ?? null; if ( is_array( $ex ) ) { return in_array( $slug, $ex, true ) ? (object) array( 'slug' => $slug ) : null; } return isset( dh_known_titles()[ $slug ] ) ? (object) array( 'slug' => $slug ) : null; }
function get_permalink( $p ) { return dh_base() . '/' . $p->slug . '/'; }
function get_the_title() { return $GLOBALS['dh_page']['title']; }
function the_title() { echo esc_html( get_the_title() ); }
function the_content() { echo '<p>Editor content</p>'; }
function get_the_ID() { return 1; }
function get_queried_object_id() { return 1; }
function get_post_field( $f, $id ) { return $GLOBALS['dh_page']['slug']; }
function is_page() { return '' !== $GLOBALS['dh_page']['slug'] && 'home' !== $GLOBALS['dh_page']['slug']; }
function is_front_page() { return 'home' === $GLOBALS['dh_page']['slug']; }
function locate_template( $f ) { return dh_theme_dir() . '/' . $f; }
function have_posts() { return 0 === $GLOBALS['dh_loop']; }
function the_post() { $GLOBALS['dh_loop']++; }
function shortcode_exists() { return $GLOBALS['dh_shortcode']; }
function do_shortcode( $code = '' ) { return '<div class="ti-widget" data-shortcode="' . esc_attr( $code ) . '">Google reviews widget</div>'; }
function get_header() { include dh_theme_dir() . '/header.php'; }
function get_footer() { include dh_theme_dir() . '/footer.php'; }
function get_template_part( $slug, $name = null, $args = array() ) {
	$file = dh_theme_dir() . '/' . $slug . ( $name ? '-' . $name : '' ) . '.php';
	( function () use ( $file, $args ) { include $file; } )();
}
