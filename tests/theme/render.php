<?php
/**
 * Render every page of the theme through stubbed WordPress into static HTML for browser tests.
 * Usage: DH_THEME_DIR=<theme> php render.php <outdir>
 */
require __DIR__ . '/wp-stubs.php';
$theme = getenv( 'DH_THEME_DIR' );
$out   = $argv[1];
require $theme . '/functions.php';
foreach ( $GLOBALS['dh_hooks']['wp_enqueue_scripts'] ?? array() as $cb ) { call_user_func( $cb ); }

function dh_render( $theme, $file ) {
	$GLOBALS['dh_loop'] = 0;
	ob_start();
	include $theme . '/' . $file;
	return ob_get_clean();
}

$titles = dh_page_titles();
$map    = dh_page_map();
$n      = 0;

// Every page, in its default state, with the optional blocks switched on so all copy is present.
$GLOBALS['dh_mods'] = array( 'dh_show_stats' => true );
foreach ( $map as $slug => $part ) {
	$GLOBALS['dh_page'] = array( 'slug' => $slug, 'title' => html_entity_decode( $titles[ $slug ] ) );
	file_put_contents( "$out/$slug.html", dh_render( $theme, 'page.php' ) );
	$n++;
}
$GLOBALS['dh_page'] = array( 'slug' => 'home', 'title' => 'Home' );
file_put_contents( "$out/index.html", dh_render( $theme, 'front-page.php' ) );
$n++;

// Variants.
$GLOBALS['dh_mods'] = array();
file_put_contents( "$out/home-default.html", dh_render( $theme, 'front-page.php' ) );
file_put_contents( "$out/about-default.html", ( function () use ( $theme ) { $GLOBALS['dh_page'] = array( 'slug' => 'about-us', 'title' => 'About Us' ); return dh_render( $theme, 'page.php' ); } )() );

$GLOBALS['dh_page'] = array( 'slug' => 'home', 'title' => 'Home' );
$_GET = array( 'dh_form' => 'booking', 'dh_status' => 'sent' );
file_put_contents( "$out/home-sent.html", dh_render( $theme, 'front-page.php' ) );
$_GET = array( 'dh_form' => 'booking', 'dh_status' => 'invalid' );
file_put_contents( "$out/home-invalid.html", dh_render( $theme, 'front-page.php' ) );
$_GET = array();

$GLOBALS['dh_mods'] = array( 'dh_phone' => '(416) 555-0100', 'dh_email' => 'front@example.test', 'dh_address' => "1 Test St\nToronto, ON", 'dh_hours' => "Mon | 9-5\nTue | Closed", 'dh_reviews_shortcode_home' => '[trustindex data-widget-id=compact123]', 'dh_reviews_shortcode' => '[evil-shortcode]', 'dh_trustindex_id' => 'abc123abc123abc123' );
file_put_contents( "$out/home-custom.html", dh_render( $theme, 'front-page.php' ) );
$GLOBALS['dh_page'] = array( 'slug' => 'testimonial', 'title' => 'Testimonial' );
file_put_contents( "$out/testimonial-custom.html", dh_render( $theme, 'page.php' ) );
$GLOBALS['dh_page'] = array( 'slug' => 'home', 'title' => 'Home' );
$GLOBALS['dh_mods'] = array( 'dh_phone' => '', 'dh_email' => '', 'dh_address' => '', 'dh_hours' => '', 'dh_facebook' => '', 'dh_instagram' => '' );
file_put_contents( "$out/home-empty.html", dh_render( $theme, 'front-page.php' ) );

$GLOBALS['dh_mods'] = array( 'dh_trustindex_id' => 'abc123abc123abc123' ); $GLOBALS['dh_shortcode'] = false;
$GLOBALS['dh_page'] = array( 'slug' => 'testimonial', 'title' => 'Testimonial' );
file_put_contents( "$out/testimonial-custom-noplugin.html", dh_render( $theme, 'page.php' ) );
$GLOBALS['dh_mods'] = array( 'dh_trustindex_id' => 'bad id!' );
file_put_contents( "$out/testimonial-badid.html", dh_render( $theme, 'page.php' ) );
$GLOBALS['dh_mods'] = array(); $GLOBALS['dh_shortcode'] = false; $GLOBALS['dh_video'] = false; $GLOBALS['dh_is_admin'] = true;
$GLOBALS['dh_page'] = array( 'slug' => 'testimonial', 'title' => 'Testimonial' );
file_put_contents( "$out/testimonial-noplugin-admin.html", dh_render( $theme, 'page.php' ) );
$GLOBALS['dh_page'] = array( 'slug' => 'home', 'title' => 'Home' );
file_put_contents( "$out/home-novideo.html", dh_render( $theme, 'front-page.php' ) );
$GLOBALS['dh_is_admin'] = false; $GLOBALS['dh_shortcode'] = true; $GLOBALS['dh_video'] = true;

$GLOBALS['dh_page'] = array( 'slug' => 'some-other-page', 'title' => 'Some Other Page' );
file_put_contents( "$out/generic.html", dh_render( $theme, 'page.php' ) );
file_put_contents( "$out/404.html", dh_render( $theme, '404.php' ) );

echo "rendered $n pages + variants\n";
