<?php
/**
 * Booking form: validates the request and emails it to the clinic.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatments offered in the form.
 */
function dh_treatments() {
	return array(
		'Not sure yet',
		'Dental implants',
		'Wisdom teeth',
		'Clear aligners',
		'Veneers & crowns',
		'Teeth whitening',
		'General check-up',
	);
}

/**
 * Redirect back to the booking section with a status.
 */
function dh_booking_redirect( $status ) {
	wp_safe_redirect( add_query_arg( 'booking', $status, home_url( '/' ) ) . '#book' );
	exit;
}

function dh_handle_booking() {
	// Honeypot: real visitors never fill this in.
	if ( ! empty( $_POST['dh_website'] ) ) {
		dh_booking_redirect( 'sent' );
	}

	$name      = isset( $_POST['dh_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dh_name'] ) ) : '';
	$phone     = isset( $_POST['dh_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['dh_phone'] ) ) : '';
	$email     = isset( $_POST['dh_email'] ) ? sanitize_email( wp_unslash( $_POST['dh_email'] ) ) : '';
	$treatment = isset( $_POST['dh_treatment'] ) ? sanitize_text_field( wp_unslash( $_POST['dh_treatment'] ) ) : '';
	$message   = isset( $_POST['dh_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dh_message'] ) ) : '';

	if ( '' === $name || '' === $phone ) {
		dh_booking_redirect( 'invalid' );
	}
	if ( ! in_array( $treatment, dh_treatments(), true ) ) {
		$treatment = 'Not sure yet';
	}

	// Limit to 5 requests per hour per visitor.
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key  = 'dh_book_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		dh_booking_redirect( 'error' );
	}
	set_transient( $key, $hits + 1, HOUR_IN_SECONDS );

	$to = sanitize_email( (string) get_theme_mod( 'dh_booking_email', '' ) );
	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}

	$subject = sprintf( '[%s] Appointment request from %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $name );
	$body    = "New appointment request\n\n"
		. "Name: {$name}\n"
		. "Phone: {$phone}\n"
		. 'Email: ' . ( $email ? $email : '(not given)' ) . "\n"
		. "Treatment: {$treatment}\n\n"
		. "Message:\n" . ( '' !== $message ? $message : '(none)' ) . "\n";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $email ) ) {
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
	}

	$sent = wp_mail( $to, $subject, $body, $headers );
	dh_booking_redirect( $sent ? 'sent' : 'error' );
}
add_action( 'admin_post_nopriv_dh_booking', 'dh_handle_booking' );
add_action( 'admin_post_dh_booking', 'dh_handle_booking' );

/**
 * Message to show above the form after a redirect.
 *
 * @return array{0:string,1:string}|null Class and text.
 */
function dh_booking_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
	$status = isset( $_GET['booking'] ) ? sanitize_key( wp_unslash( $_GET['booking'] ) ) : '';
	switch ( $status ) {
		case 'sent':
			return array( 'is-ok', __( 'Thank you. Your request has been sent and the team will call you to confirm a time.', 'the-dental-hauz' ) );
		case 'invalid':
			return array( 'is-error', __( 'Please add your name and phone number so we can call you back.', 'the-dental-hauz' ) );
		case 'error':
			return array( 'is-error', __( 'Sorry, your request could not be sent. Please try again in a little while or call the clinic.', 'the-dental-hauz' ) );
	}
	return null;
}
