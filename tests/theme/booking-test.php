<?php
/**
 * Exercises the booking handler. Usage: DH_THEME_DIR=<theme> php booking-test.php
 */
require __DIR__ . '/wp-stubs.php';
require getenv( 'DH_THEME_DIR' ) . '/functions.php';

$fails = 0;
function check( $label, $cond ) {
	global $fails;
	echo ( $cond ? '  ok   ' : '  FAIL ' ) . $label . "\n";
	if ( ! $cond ) { $fails++; }
}
function submit( $post, $ip = '1.1.1.1' ) {
	$_POST = $post;
	$_SERVER['REMOTE_ADDR'] = $ip;
	$GLOBALS['dh_mail'] = array();
	try { dh_handle_booking(); } catch ( DH_Redirect $r ) { return $r->getMessage(); }
	return null;
}

$good = array( 'dh_name' => 'Test Patient', 'dh_phone' => '+20 100 000 0000', 'dh_email' => 'patient@example.test', 'dh_treatment' => 'Dental implants', 'dh_message' => 'Hello' );

$r = submit( $good );
check( 'valid request redirects to #book with booking=sent', $r === 'https://clinic.test/?booking=sent#book' );
check( 'valid request sends exactly one email', count( $GLOBALS['dh_mail'] ) === 1 );
$m = $GLOBALS['dh_mail'][0] ?? array();
check( 'email goes to the site admin by default', ( $m['to'] ?? '' ) === 'admin@example.test' );
check( 'email body has name, phone and treatment', strpos( $m['body'] ?? '', 'Test Patient' ) !== false && strpos( $m['body'], '+20 100 000 0000' ) !== false && strpos( $m['body'], 'Dental implants' ) !== false );
check( 'Reply-To header is the patient', in_array( 'Reply-To: Test Patient <patient@example.test>', $m['headers'] ?? array(), true ) );

$GLOBALS['dh_mods']['dh_booking_email'] = 'frontdesk@example.test';
submit( $good, '2.2.2.2' );
check( 'Customizer booking email overrides the admin email', ( $GLOBALS['dh_mail'][0]['to'] ?? '' ) === 'frontdesk@example.test' );
$GLOBALS['dh_mods'] = array();

$r = submit( array( 'dh_name' => '', 'dh_phone' => '' ), '3.3.3.3' );
check( 'missing name/phone redirects with booking=invalid', $r === 'https://clinic.test/?booking=invalid#book' );
check( 'missing name/phone sends no email', count( $GLOBALS['dh_mail'] ) === 0 );

$r = submit( $good + array( 'dh_website' => 'http://spam.test' ), '4.4.4.4' );
check( 'honeypot filled: looks successful but sends nothing', $r === 'https://clinic.test/?booking=sent#book' && count( $GLOBALS['dh_mail'] ) === 0 );

submit( array_merge( $good, array( 'dh_name' => '<script>alert(1)</script>Bob' ) ), '5.5.5.5' );
check( 'HTML is stripped from the name', strpos( $GLOBALS['dh_mail'][0]['body'] ?? '', '<script>' ) === false );

submit( array_merge( $good, array( 'dh_treatment' => 'Free iPhone' ) ), '6.6.6.6' );
check( 'unknown treatment falls back to "Not sure yet"', strpos( $GLOBALS['dh_mail'][0]['body'] ?? '', 'Treatment: Not sure yet' ) !== false );

$last = null;
for ( $i = 0; $i < 7; $i++ ) { $last = submit( $good, '7.7.7.7' ); }
check( 'more than 5 requests/hour from one visitor is refused', $last === 'https://clinic.test/?booking=error#book' && count( $GLOBALS['dh_mail'] ) === 0 );

check( 'handler is wired to admin-post for visitors and logged-in users', isset( $GLOBALS['dh_hooks']['admin_post_nopriv_dh_booking'] ) && isset( $GLOBALS['dh_hooks']['admin_post_dh_booking'] ) );

echo $fails ? "\n$fails FAILED\n" : "\nall booking checks passed\n";
exit( $fails ? 1 : 0 );
