<?php
/**
 * Render the theme's front page through stubbed WordPress into static HTML for browser tests.
 * Usage: DH_THEME_DIR=<theme> php render.php <outdir>
 */
require __DIR__ . '/wp-stubs.php';
$theme = getenv( 'DH_THEME_DIR' );
$out   = $argv[1];
require $theme . '/functions.php';

function dh_render( $theme, $file ) {
	ob_start();
	include $theme . '/' . $file;
	return ob_get_clean();
}

$scenarios = array(
	'index'   => array( array(), array(), false ),
	'sent'    => array( array(), array( 'booking' => 'sent' ), false ),
	'invalid' => array( array(), array( 'booking' => 'invalid' ), false ),
	'details' => array(
		array(
			'dh_phone'   => '+20 100 000 0000',
			'dh_email'   => 'hello@example.test',
			'dh_address' => "12 Example St\nCairo",
			'dh_hours'   => "Sat – Thu | 10:00 – 21:00\nFriday | Closed",
		),
		array(),
		false,
	),
	'admin'   => array( array(), array(), true ),
);

foreach ( $scenarios as $name => $s ) {
	$GLOBALS['dh_mods']     = $s[0];
	$_GET                   = $s[1];
	$GLOBALS['dh_is_admin'] = $s[2];
	file_put_contents( "$out/$name.html", dh_render( $theme, 'front-page.php' ) );
}
$GLOBALS['dh_mods'] = array();
file_put_contents( "$out/404.html", dh_render( $theme, '404.php' ) );
echo "rendered " . ( count( $scenarios ) + 1 ) . " pages\n";
