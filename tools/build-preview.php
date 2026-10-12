<?php
/**
 * Builds a static preview of the whole WordPress theme (every page) so it can be browsed on GitHub Pages
 * before the theme is installed on the real site.
 *
 * Usage: DH_PREVIEW=1 DH_BASE=/The-dental-Website- DH_ATTACHMENTS=tools/preview/attachments.json \
 *        DH_THEME_DIR=wordpress-theme/the-dental-hauz php tools/build-preview.php <outdir>
 * Writes <outdir>/index.html, <outdir>/<page-slug>/index.html and <outdir>/404.html.
 */
require __DIR__ . '/../tests/theme/wp-stubs.php';
$theme = getenv( 'DH_THEME_DIR' );
$out   = rtrim( $argv[1], '/' );
require $theme . '/functions.php';
foreach ( $GLOBALS['dh_hooks']['wp_enqueue_scripts'] ?? array() as $cb ) { call_user_func( $cb ); }

function dh_build( $theme, $file ) {
	$GLOBALS['dh_loop'] = 0;
	ob_start();
	include $theme . '/' . $file;
	return ob_get_clean();
}
function dh_write( $path, $html ) {
	if ( ! is_dir( dirname( $path ) ) ) { mkdir( dirname( $path ), 0777, true ); }
	file_put_contents( $path, $html );
}

$titles = dh_known_titles();
$n      = 0;
foreach ( dh_page_map() as $slug => $part ) {
	$GLOBALS['dh_page'] = array( 'slug' => $slug, 'title' => html_entity_decode( $titles[ $slug ] ) );
	$GLOBALS['wp']->request = $slug;
	dh_write( "$out/$slug/index.html", dh_build( $theme, 'page.php' ) );
	$n++;
}
$GLOBALS['dh_page'] = array( 'slug' => 'home', 'title' => 'Home' );
$GLOBALS['wp']->request = '';
dh_write( "$out/index.html", dh_build( $theme, 'front-page.php' ) );
dh_write( "$out/404.html", dh_build( $theme, '404.php' ) );
echo 'built ' . ( $n + 2 ) . " pages\n";
