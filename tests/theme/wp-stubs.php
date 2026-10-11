<?php
/**
 * Minimal stand-ins for the WordPress functions the theme calls, so templates
 * can be rendered and the booking handler exercised without a WordPress install.
 * Test-only: never shipped in the theme zip.
 */
define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'ENT_QUOTES_', ENT_QUOTES );

$GLOBALS['dh_mods']      = array();
$GLOBALS['dh_options']   = array( 'admin_email' => 'admin@example.test' );
$GLOBALS['dh_mail']      = array();
$GLOBALS['dh_transients'] = array();
$GLOBALS['dh_hooks']     = array();
$GLOBALS['dh_is_admin']  = false;

class DH_Redirect extends Exception {}

function dh_theme_dir() { return getenv( 'DH_THEME_DIR' ); }
function get_template_directory() { return dh_theme_dir(); }
function get_theme_file_uri( $p = '' ) { return $p; }
function get_stylesheet_uri() { return 'style.css'; }
function add_action( $h, $cb ) { $GLOBALS['dh_hooks'][ $h ][] = $cb; }
function add_filter( $h, $cb ) { $GLOBALS['dh_hooks'][ $h ][] = $cb; }
function add_theme_support() {}
function register_nav_menus() {}
function load_theme_textdomain() {}
function wp_enqueue_style() {}
function wp_enqueue_script() {}
function has_site_icon() { return false; }
function has_custom_logo() { return false; }
function wp_get_attachment_image() { return ''; }
function get_theme_mod( $k, $d = '' ) { return isset( $GLOBALS['dh_mods'][ $k ] ) ? $GLOBALS['dh_mods'][ $k ] : $d; }
function get_option( $k ) { return $GLOBALS['dh_options'][ $k ] ?? ''; }
function home_url( $p = '' ) { return 'https://clinic.test' . $p; }
function admin_url( $p = '' ) { return 'https://clinic.test/wp-admin/' . $p; }
function get_bloginfo( $k = '' ) { return 'charset' === $k ? 'UTF-8' : 'The Dental Hauz'; }
function bloginfo( $k = '' ) { echo get_bloginfo( $k ); }
function language_attributes() { echo 'lang="en-US"'; }
function wp_head() { echo '<link rel="stylesheet" href="style.css">'; }
function wp_footer() { echo '<script src="assets/main.js" defer></script>'; }
function wp_body_open() {}
function body_class() { echo 'class="home dh-home"'; }
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
function wp_unslash( $s ) { return $s; }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_email( $s ) { return filter_var( trim( (string) $s ), FILTER_VALIDATE_EMAIL ) ? trim( $s ) : ''; }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function is_email( $s ) { return (bool) filter_var( $s, FILTER_VALIDATE_EMAIL ); }
function wp_specialchars_decode( $s ) { return $s; }
function add_query_arg( $k, $v, $url ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . $k . '=' . $v; }
function wp_safe_redirect( $url ) { throw new DH_Redirect( $url ); }
function get_transient( $k ) { return $GLOBALS['dh_transients'][ $k ] ?? false; }
function set_transient( $k, $v ) { $GLOBALS['dh_transients'][ $k ] = $v; }
function wp_mail( $to, $subject, $body, $headers = array() ) {
	$GLOBALS['dh_mail'][] = compact( 'to', 'subject', 'body', 'headers' );
	return true;
}
function get_header() { include dh_theme_dir() . '/header.php'; }
function get_footer() { include dh_theme_dir() . '/footer.php'; }
