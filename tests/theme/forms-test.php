<?php
/**
 * Exercises every form handler. Usage: DH_THEME_DIR=<theme> php forms-test.php
 */
require __DIR__ . '/wp-stubs.php';
require getenv( 'DH_THEME_DIR' ) . '/functions.php';

$fails = 0;
function check( $label, $cond ) {
	global $fails;
	echo ( $cond ? '  ok   ' : '  FAIL ' ) . $label . "\n";
	if ( ! $cond ) { $fails++; }
}
/** Emails sent to the clinic (not the patient's thank-you copy). */
function clinic_mail() { return array_values( array_filter( $GLOBALS['dh_mail'], function ( $m ) { return 0 !== strpos( $m['subject'], '[The Dental Hauz] Thank you' ); } ) ); }
function patient_mail() { return array_values( array_filter( $GLOBALS['dh_mail'], function ( $m ) { return 0 === strpos( $m['subject'], '[The Dental Hauz] Thank you' ); } ) ); }
function submit( $post, $ip = '1.1.1.1' ) {
	$_POST = $post;
	$_SERVER['REMOTE_ADDR'] = $ip;
	$GLOBALS['dh_mail'] = array();
	$GLOBALS['dh_posts'] = array();
	try { dh_handle_form(); } catch ( DH_Redirect $r ) { return $r->getMessage(); }
	return null;
}
/** A complete, valid submission for a form, built from its definition. */
function valid_post( $id ) {
	$post = array( 'dh_form' => $id, 'dh_return' => 'https://clinic.test/some-page/' );
	foreach ( dh_forms()[ $id ]['fields'] as $f ) {
		$k = 'dh_' . $f['name'];
		switch ( $f['type'] ) {
			case 'heading': case 'note': break;
			case 'email': $post[ $k ] = 'patient@example.test'; break;
			case 'tel': $post[ $k ] = '905-555-0100'; break;
			case 'date': $post[ $k ] = '2026-10-12'; break;
			case 'select': case 'radio': $post[ $k ] = $f['options'][0]; break;
			case 'checkboxes': $post[ $k ] = array( $f['options'][0], $f['options'][1] ); break;
			case 'checkbox': $post[ $k ] = '1'; break;
			default: $post[ $k ] = 'Sample ' . $f['name'];
		}
	}
	return $post;
}

$ids = array_keys( dh_forms() );
echo "forms defined: " . implode( ', ', $ids ) . "\n";
check( 'all five forms exist', $ids === array( 'booking', 'request-for-records', 'dental-treatment-payment-agreement', 'payment-agreement', 'medical-history-form' ) );

$n = 0;
foreach ( $ids as $id ) {
	$n++;
	$ip = "10.0.0.$n";
	echo "\n[$id]\n";
	$form = dh_forms()[ $id ];

	$r = submit( valid_post( $id ), $ip );
	check( 'valid submission redirects back to the form with status=sent', $r === "https://clinic.test/some-page/?dh_form=$id&dh_status=sent#form-$id" );
	check( 'one email is sent to the clinic address', count( clinic_mail() ) === 1 && clinic_mail()[0]['to'] === 'thedentalhauz@gmail.com' );
	$body = clinic_mail()[0]['body'] ?? '';
	check( 'clinic email is branded HTML (brand colours, table of answers)', false !== strpos( $body, '#657568' ) && false !== strpos( $body, '<table' ) && in_array( 'Content-Type: text/html; charset=UTF-8', clinic_mail()[0]['headers'], true ) );
	$has_email = (bool) array_filter( $form['fields'], function ( $f ) { return 'email' === $f['type']; } );
	if ( $has_email ) {
		$pm = patient_mail();
		check( 'the patient gets a thank-you email with the clinic contact details', 1 === count( $pm ) && 'patient@example.test' === $pm[0]['to'] && false !== strpos( $pm[0]['body'], 'your registration is done' ) && false !== strpos( $pm[0]['body'], 'thedentalhauz@gmail.com' ) );
		check( 'the thank-you email repeats no free text the visitor typed', false === strpos( $pm[0]['body'], 'Sample ' ) );
		check( 'replies to the thank-you email go to the clinic', in_array( 'Reply-To: The Dental Hauz <thedentalhauz@gmail.com>', $pm[0]['headers'], true ) );
	}
	$allin = true;
	foreach ( $form['fields'] as $f ) {
		if ( in_array( $f['type'], array( 'note' ), true ) ) { continue; }
		if ( false === strpos( $body, $f['label'] ) && false === strpos( $body, strtoupper( $f['label'] ) ) ) { $allin = false; echo "      missing in email: {$f['label']}\n"; }
	}
	check( 'email lists every field of the form', $allin );
	check( 'a private copy is kept in the dashboard', count( $GLOBALS['dh_posts'] ) === 1 && 'dh_submission' === $GLOBALS['dh_posts'][0]['post_type'] && 'private' === $GLOBALS['dh_posts'][0]['post_status'] );

	// Each required field, left empty, stops the submission.
	$allreq = true;
	foreach ( $form['fields'] as $f ) {
		if ( empty( $f['required'] ) ) { continue; }
		$p = valid_post( $id );
		unset( $p[ 'dh_' . $f['name'] ] );
		$r = submit( $p, $ip );
		if ( $r !== "https://clinic.test/some-page/?dh_form=$id&dh_status=invalid#form-$id" || count( $GLOBALS['dh_mail'] ) !== 0 ) { $allreq = false; echo "      not enforced: {$f['label']}\n"; }
	}
	check( 'every required field is enforced (no email when one is missing)', $allreq );

	$r = submit( valid_post( $id ) + array( 'dh_website' => 'http://spam.test' ), $ip );
	check( 'honeypot filled: looks successful but sends nothing', strpos( (string) $r, 'dh_status=sent' ) !== false && count( $GLOBALS['dh_mail'] ) === 0 );

	$p = valid_post( $id );
	foreach ( $form['fields'] as $f ) { if ( 'text' === $f['type'] ) { $p[ 'dh_' . $f['name'] ] = '<script>alert(1)</script>Bob'; break; } }
	submit( $p, $ip );
	check( 'HTML is stripped from text fields', strpos( clinic_mail()[0]['body'] ?? '', '<script>' ) === false && count( clinic_mail() ) === 1 );

	$r = submit( valid_post( $id ) + array( 'dh_ajax' => '1' ), $ip );
	check( 'background send answers with JSON for the thank-you pop-up', 0 === strpos( (string) $r, 'json:' ) && true === json_decode( substr( $r, 5 ), true )['ok'] );
	$p = valid_post( $id ) + array( 'dh_ajax' => '1' );
	foreach ( $form['fields'] as $f ) { if ( ! empty( $f['required'] ) ) { unset( $p[ 'dh_' . $f['name'] ] ); break; } }
	$j = json_decode( substr( (string) submit( $p, $ip ), 5 ), true );
	check( 'background send reports a missing field without sending', false === $j['ok'] && 'invalid' === $j['status'] && 0 === count( $GLOBALS['dh_mail'] ) );
}

echo "\n[shared behaviour]\n";
$r = submit( array( 'dh_form' => 'nope', 'dh_return' => 'https://clinic.test/x/' ), '9.9.9.9' );
check( 'unknown form id goes home and sends nothing', $r === 'https://clinic.test/' && count( $GLOBALS['dh_mail'] ) === 0 );

$r = submit( array_merge( valid_post( 'booking' ), array( 'dh_return' => 'https://evil.test/phish' ) ), '9.9.9.8' );
check( 'return address on another site is ignored', strpos( (string) $r, 'https://clinic.test/' ) === 0 && strpos( (string) $r, 'evil.test' ) === false );

$b = valid_post( 'booking' ); $b['dh_service'] = 'Free iPhone';
check( 'booking: a service that is not on the list is rejected', strpos( (string) submit( $b, '9.9.9.7' ), 'invalid' ) !== false );
$b = valid_post( 'booking' ); $b['dh_email'] = 'not-an-email';
check( 'booking: an invalid email address is rejected', strpos( (string) submit( $b, '9.9.9.6' ), 'invalid' ) !== false );
$b = valid_post( 'booking' ); $b['dh_time'] = 'Midnight';
check( 'booking: a time that is not on the list is rejected', strpos( (string) submit( $b, '9.9.9.5' ), 'invalid' ) !== false );

submit( valid_post( 'booking' ), '9.9.9.4' );
$m = $GLOBALS['dh_mail'][0];
check( 'booking: Reply-To is the patient', in_array( 'Reply-To: Sample first_name Sample last_name <patient@example.test>', $m['headers'], true ) );
check( 'booking: subject names the form and the patient', $m['subject'] === '[The Dental Hauz] Appointment request from Sample first_name Sample last_name' );

$GLOBALS['dh_mods']['dh_form_email'] = 'frontdesk@example.test';
submit( valid_post( 'booking' ), '9.9.9.3' );
check( 'Customizer "send form submissions to" overrides the default', $GLOBALS['dh_mail'][0]['to'] === 'frontdesk@example.test' );
$GLOBALS['dh_mods']['dh_form_email'] = '';
submit( valid_post( 'booking' ), '9.9.9.2' );
check( 'empty recipient falls back to the site admin email', $GLOBALS['dh_mail'][0]['to'] === 'admin@example.test' );
$GLOBALS['dh_mods'] = array( 'dh_store_submissions' => false );
submit( valid_post( 'booking' ), '9.9.9.1' );
check( 'dashboard copies can be switched off', count( $GLOBALS['dh_posts'] ) === 0 && count( clinic_mail() ) === 1 );
$GLOBALS['dh_mods'] = array();

$last = null;
for ( $i = 0; $i < 35; $i++ ) { $last = submit( valid_post( 'booking' ), '7.7.7.7' ); }
check( 'more than 30 submissions an hour from one address are refused', strpos( (string) $last, 'dh_status=error' ) !== false && count( $GLOBALS['dh_mail'] ) === 0 );

check( 'handler is wired to admin-post for visitors and logged-in users', isset( $GLOBALS['dh_hooks']['admin_post_nopriv_dh_form'] ) && isset( $GLOBALS['dh_hooks']['admin_post_dh_form'] ) );

echo "\n[medical history specifics]\n";
$med = dh_forms()['medical-history-form']['fields'];
$labels = array_column( $med, 'label' );
foreach ( array( 'chest pain, angina', 'rheumatic fever', 'thyroid disease', 'steroid therapy' ) as $opt ) {
	$f = array_values( array_filter( $med, function ( $x ) { return 'conditions' === $x['name']; } ) )[0];
	check( "condition list includes \"$opt\"", in_array( $opt, $f['options'], true ) );
}
check( 'follow-up boxes are optional so a "No" answer can be submitted', ! array_filter( $med, function ( $x ) { return substr( $x['name'], -5 ) === '_more' && $x['required']; } ) );
check( 'the consent statement names the clinic\'s dentists (no individual doctor)', (bool) array_filter( $labels, function ( $l ) { return false !== strpos( $l, 'The Dental Hauz dentists or their staff' ); } ) && ! array_filter( $labels, function ( $l ) { return false !== stripos( $l, 'Summer' ); } ) );

echo "\n[template override]\n";
$GLOBALS['dh_page'] = array( 'slug' => 'about-us', 'title' => 'About Us' );
check( 'a carried-over page is forced onto the theme page template', substr( dh_force_templates( '/elementor/canvas.php' ), -8 ) === 'page.php' );
$GLOBALS['dh_page'] = array( 'slug' => 'home', 'title' => 'Home' );
check( 'the home page is forced onto front-page.php', substr( dh_force_templates( '/elementor/canvas.php' ), -14 ) === 'front-page.php' );
$GLOBALS['dh_page'] = array( 'slug' => 'privacy-policy', 'title' => 'Privacy' );
check( 'any other page keeps whatever template WordPress chose', dh_force_templates( '/x/custom.php' ) === '/x/custom.php' );
$all = array_keys( dh_page_map() );
sort( $all );
check( 'all 20 inner pages of the old site plus Doctors and each doctor are mapped', count( $all ) === 24 );

echo "\n[doctors pages]\n";
$GLOBALS['dh_existing_pages'] = array();
$GLOBALS['dh_posts'] = array();
dh_ensure_pages();
$made = array_column( $GLOBALS['dh_posts'], 'post_name' );
check( 'activation creates the Doctors page and one page per doctor', $made === array( 'doctors', 'dr-sarah-assaya', 'dr-sherief-mohamed', 'dr-rosemarie-sia' ) );
check( 'created pages are published pages with the right titles', $GLOBALS['dh_posts'][0]['post_title'] === 'Our Team' && $GLOBALS['dh_posts'][2]['post_title'] === 'Dr. Sherief Mohamed' && 'publish' === $GLOBALS['dh_posts'][2]['post_status'] && 'page' === $GLOBALS['dh_posts'][2]['post_type'] );
$GLOBALS['dh_existing_pages'] = array( 'doctors', 'dr-sarah-assaya', 'dr-sherief-mohamed', 'dr-rosemarie-sia' );
$GLOBALS['dh_posts'] = array();
dh_ensure_pages();
check( 'pages that already exist are left alone', count( $GLOBALS['dh_posts'] ) === 0 );
$GLOBALS['dh_existing_pages'] = null;
check( 'doctor pages are routed to the theme templates; no Dr. Summer page', ( dh_page_map()['doctors'] ?? '' ) === 'doctors' && ( dh_page_map()['dr-sherief-mohamed'] ?? '' ) === 'doctor' && ! isset( dh_page_map()['dr-summer-al-maqdassy'] ) );
check( 'every doctor has the fields the templates read', ! array_filter( dh_doctors(), function ( $d ) { return array_diff( array( 'slug', 'name', 'role', 'photo', 'summary', 'bio', 'credentials' ), array_keys( $d ) ); } ) );
check( 'every doctor has the fields the templates read', ! array_filter( dh_doctors(), function ( $d ) { return array_diff( array( 'slug', 'name', 'role', 'photo', 'summary', 'bio', 'credentials' ), array_keys( $d ) ); } ) );

echo "\n[old page builder protection]\n";
$dh_locs = new class { public $l = array(); public function register_core_location( $x ) { $this->l[] = $x; } };
dh_elementor_locations( $dh_locs );
$dh_el = array( 'elementor-frontend', 'elementor-post-29', 'elementor-webpack-runtime', 'elementor-frontend-modules', 'e-animation-fadeInUp', 'e-shapes', 'widget-heading', 'base-desktop', 'default-desktop' );
$dh_keep = array( 'dh-style', 'dh-fonts', 'dh-main', 'ti-widget-css-google', 'trustindex-loader', 'rank-math', 'wp-block-library', 'jquery' );
check( 'Elementor front-end files are recognised', count( array_filter( $dh_el, 'dh_is_elementor_asset' ) ) === count( $dh_el ) );
check( 'theme, reviews and SEO files are kept', ! array_filter( $dh_keep, 'dh_is_elementor_asset' ) );
check( 'Elementor style kit class is removed so brand headings apply', array( 'home', 'page' ) === dh_drop_elementor_kit_class( array( 'home', 'elementor-kit-29', 'page' ) ) );
check( 'theme claims the header and footer locations so Elementor Pro / PRO Elements cannot replace them', array( 'header', 'footer' ) === $dh_locs->l );
check( 'Elementor Pro is told not to override the header and footer', isset( $GLOBALS['dh_hooks']['elementor/theme/need_override_location'] ) && '__return_false' === $GLOBALS['dh_hooks']['elementor/theme/need_override_location'][0] );
$GLOBALS['dh_is_admin'] = true;
ob_start(); dh_elementor_notice(); $quiet = ob_get_clean();
check( 'no dashboard notice when Elementor is not active', '' === $quiet );
define( 'ELEMENTOR_PRO_VERSION', '3.0' );
ob_start(); dh_elementor_notice(); $notice = ob_get_clean();
check( 'dashboard notice appears when Elementor is active, naming the plugins to deactivate', false !== strpos( $notice, 'deactivate' ) && false !== strpos( $notice, 'Elementor Pro' ) );
$GLOBALS['dh_is_admin'] = false;
ob_start(); dh_elementor_notice(); $visitor = ob_get_clean();
check( 'visitors never see the notice', '' === $visitor );

// Cache busting: the stylesheet and script links change whenever the files change.
$css_v = dh_asset_version( 'style.css' );
check( 'stylesheet version follows the file (not a fixed number)', $css_v === DH_VERSION . '.' . filemtime( getenv( 'DH_THEME_DIR' ) . '/style.css' ) );
check( 'script version follows the file', dh_asset_version( 'assets/main.js' ) === DH_VERSION . '.' . filemtime( getenv( 'DH_THEME_DIR' ) . '/assets/main.js' ) );
check( 'theme version was bumped so old copies are replaced', preg_match( '/^Version: ' . preg_quote( DH_VERSION, '/' ) . '$/m', file_get_contents( getenv( 'DH_THEME_DIR' ) . '/style.css' ) ) === 1 );

echo "\n[search and sharing image]\n";
$GLOBALS['dh_page'] = array( 'slug' => 'home', 'title' => 'Home' );
check( 'home page shares the clinic storefront photo', 'clinic-storefront.jpg' === basename( dh_share_image()['url'] ) && dh_share_image()['width'] > 0 );
$GLOBALS['dh_page'] = array( 'slug' => 'dental-implants', 'title' => 'Dental Implants' );
check( 'a treatment page shares its own cover photo', 'svc-dental-implants.jpg' === basename( dh_share_image()['url'] ) );
$GLOBALS['dh_page'] = array( 'slug' => 'about-us', 'title' => 'About Us' );
check( 'About Us shares its cover photo', 'about-hero.jpg' === basename( dh_share_image()['url'] ) );
$dh_rm = new class { public $got = array(); public function add_image( $a ) { $this->got[] = $a; } };
dh_rank_math_images( $dh_rm );
check( 'Rank Math is handed the photo with size and description', 1 === count( $dh_rm->got ) && $dh_rm->got[0]['height'] > 0 && '' !== $dh_rm->got[0]['alt'] );
check( 'hooked into Rank Math for Facebook and X/Twitter previews', isset( $GLOBALS['dh_hooks']['rank_math/opengraph/facebook/add_images'], $GLOBALS['dh_hooks']['rank_math/opengraph/twitter/add_images'] ) );
$GLOBALS['dh_page'] = null;

echo $fails ? "\n$fails FAILED\n" : "\nall form and routing checks passed\n";
exit( $fails ? 1 : 0 );
