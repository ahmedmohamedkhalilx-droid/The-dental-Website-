<?php
/**
 * Test-only: send every email through the local SMTP sink (127.0.0.1:2525) using WordPress's real wp_mail() and PHPMailer,
 * so the test can read the message that would have gone to the clinic.
 */
add_action(
	'phpmailer_init',
	function ( $mailer ) {
		$mailer->isSMTP();
		$mailer->Host        = '127.0.0.1';
		// A flag file simulates a mail server that is down (to prove submissions are still kept).
		$mailer->Port        = file_exists( __DIR__ . '/../../../out/smtp-down.flag' ) ? 2599 : (int) ( getenv( 'WP_E2E_SMTP_PORT' ) ?: 2525 );
		$mailer->SMTPAuth    = false;
		$mailer->SMTPAutoTLS = false;
		$mailer->SMTPSecure  = '';
		$mailer->Timeout     = 8;
	}
);

// No calls out to wordpress.org (update checks) during the test.
add_filter(
	'pre_http_request',
	function ( $pre, $args, $url ) {
		if ( false !== strpos( $url, 'wordpress.org' ) ) {
			return array( 'headers' => array(), 'body' => '{"offers":[],"themes":[],"plugins":[],"no_update":[],"translations":[]}', 'response' => array( 'code' => 200, 'message' => 'OK' ), 'cookies' => array(), 'filename' => null );
		}
		return $pre;
	},
	10,
	3
);

// Pretend everything is up to date, so the dashboard does not try to read update data.
foreach ( array( 'core' => 'updates', 'plugins' => 'response', 'themes' => 'response' ) as $kind => $key ) {
	add_filter(
		'pre_site_transient_update_' . $kind,
		function () use ( $key ) {
			$o                  = new stdClass();
			$o->$key            = array();
			$o->updates         = array();
			$o->no_update       = array();
			$o->translations    = array();
			$o->last_checked    = time();
			$o->version_checked = get_bloginfo( 'version' );
			return $o;
		}
	);
}
