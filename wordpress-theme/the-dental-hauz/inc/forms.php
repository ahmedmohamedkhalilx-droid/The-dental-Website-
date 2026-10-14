<?php
/**
 * Forms: booking request, medical history, request for records and the two payment agreements.
 * Each form is defined once below, rendered by dh_render_form() and handled by dh_handle_form().
 * Submissions are emailed to the clinic and (optionally) kept as private entries in the dashboard.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yes / No / Not sure options used throughout the medical history.
 */
function dh_ynm() {
	return array( 'Yes', 'No', 'Not sure / Maybe' );
}

/**
 * Field helper.
 */
function dh_f( $name, $label, $type = 'text', $extra = array() ) {
	return array_merge( array( 'name' => $name, 'label' => $label, 'type' => $type, 'required' => false, 'half' => false ), $extra );
}

/**
 * Everything a patient can book: the visit types from the old form plus every treatment the clinic lists.
 *
 * @return string[]
 */
function dh_booking_services() {
	$c        = dh_content();
	$services = array( 'New Patient Exam' );
	foreach ( $c['services'] as $s ) {
		$services[] = $c['service_pages'][ $s['slug'] ]['title'];
	}
	$services[] = 'Emergency Exam';
	$services[] = 'Other';
	return $services;
}

/**
 * Form definitions.
 *
 * Types: text, tel, email, date, textarea, select, radio, checkbox (single), checkboxes (several),
 * heading (section title), note (paragraph).
 *
 * @return array<string, array>
 */
function dh_forms() {
	static $forms = null;
	if ( null !== $forms ) {
		return $forms;
	}

	$forms = array();

	$forms['booking'] = array(
		'title'  => 'Appointment request',
		'submit' => 'Book Appointment',
		'fields' => array(
			dh_f( 'first_name', 'First Name', 'text', array( 'required' => true, 'half' => true, 'autocomplete' => 'given-name' ) ),
			dh_f( 'last_name', 'Last Name', 'text', array( 'half' => true, 'autocomplete' => 'family-name' ) ),
			dh_f( 'phone', 'Phone', 'tel', array( 'required' => true, 'half' => true, 'autocomplete' => 'tel' ) ),
			dh_f( 'email', 'Your Email', 'email', array( 'required' => true, 'half' => true, 'autocomplete' => 'email' ) ),
			dh_f( 'service', 'Service You Would Like to Book', 'select', array(
				'required' => true,
				'options'  => dh_booking_services(),
			) ),
			dh_f( 'day', 'Preferred Day', 'select', array( 'required' => true, 'half' => true, 'options' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ) ) ),
			dh_f( 'time', 'Preferred Time', 'select', array( 'required' => true, 'half' => true, 'options' => array( 'Morning', 'Afternoon', 'Evening', 'No Preference' ) ) ),
		),
	);

	$forms['request-for-records'] = array(
		'title'  => 'Request for records',
		'submit' => 'Send',
		'fields' => array(
			dh_f( 'dr_name', 'Dr Name', 'text', array( 'required' => true ) ),
			dh_f( 'patient_name', 'Patient Name', 'text', array( 'required' => true, 'help' => 'has/have recently become a patient/patients in my practice' ) ),
			dh_f( 'records', 'Would you kindly forward the following records', 'checkboxes', array(
				'required' => true,
				'options'  => array( 'Radiographs', 'Panorex', 'Charting of existing oral/dental conditions', 'Notes of your treatment', 'Any notes from referring specialists', 'Misc.' ),
			) ),
			dh_f( 'message', 'Message', 'textarea' ),
			dh_f( 'ack', 'All of the above will be kept indefinitely should you require them in the future. Thank you.', 'checkbox', array( 'required' => true ) ),
			dh_f( 'date', 'Date', 'date', array( 'required' => true ) ),
		),
	);

	$forms['dental-treatment-payment-agreement'] = array(
		'title'  => 'Dental treatment payment agreement',
		'submit' => 'Send',
		'fields' => array(
			dh_f( 'patient_name', 'Patient Name', 'text', array( 'half' => true ) ),
			dh_f( 'treatment', 'Treatment', 'text', array( 'half' => true ) ),
			dh_f( 'appointment_date', 'Appointment date', 'date', array( 'half' => true ) ),
			dh_f( 'primary_insurance', 'Primary Insurance Contribution', 'text', array( 'half' => true ) ),
			dh_f( 'secondary_insurance', 'Secondary Insurance Contribution', 'text', array( 'half' => true ) ),
			dh_f( 'patient_portion', 'Patient Portion', 'text', array( 'half' => true ) ),
			dh_f( 'deposit', 'Deposit Due Prior to Appointment', 'text', array( 'half' => true ) ),
			dh_f( 'balance', 'Remaining Balance Due at Appointment', 'text', array( 'half' => true ) ),
			dh_f( 'terms_heading', 'Additional Terms', 'heading' ),
			dh_f( 'terms', 'A 3% additional charge applies if payment is made using any card other than debit/e-transfer. Patients are responsible for full payment of services regardless of insurance coverage. If insurance does not cover the estimated amount, the patient agrees to pay any remaining balance.', 'note' ),
			dh_f( 'ack_heading', 'Patient Acknowledgment', 'heading' ),
			dh_f( 'ack_name', 'Full name of patient', 'text', array( 'half' => true ) ),
			dh_f( 'ack', 'I acknowledge that I have read and understand the above payment agreement. I agree to the terms stated and accept financial responsibility for the services provided.', 'checkbox', array( 'required' => true ) ),
			dh_f( 'date', 'Date', 'date', array( 'half' => true ) ),
			dh_f( 'office_rep', 'Dental Office Representative', 'text', array( 'half' => true, 'help' => 'Type the name of the representative who signs for the office.' ) ),
		),
	);

	$forms['payment-agreement'] = array(
		'title'  => 'Payment agreement',
		'submit' => 'Submit',
		'fields' => array(
			dh_f( 'patient_name', 'Patient Name', 'text', array( 'half' => true ) ),
			dh_f( 'insurance', 'Insurance coverage', 'text', array( 'half' => true ) ),
			dh_f( 'terms_heading', 'Payment Agreement Terms', 'heading' ),
			dh_f( 'terms', 'Patients are responsible for full payment of services regardless of insurance coverage. If insurance does not cover the estimated amount, the patient agrees to pay any remaining balance. A 3% additional charge may apply if payment is made using any card other than debit/e-transfer. Your credit card will be securely kept on file for any uncovered treatment costs.', 'note' ),
			dh_f( 'ack_heading', 'Patient Acknowledgment', 'heading' ),
			dh_f( 'ack_name', 'Full Name', 'text', array( 'required' => true ) ),
			dh_f( 'ack', 'I acknowledge that I have read and understand the above payment agreement. I agree to the terms stated and accept financial responsibility for the services provided.', 'checkbox', array( 'required' => true ) ),
			dh_f( 'signature', 'Patient Signature', 'text', array( 'half' => true, 'help' => 'Type your full name to sign.' ) ),
			dh_f( 'date', 'Date', 'date', array( 'half' => true ) ),
		),
	);

	// Medical history. "Yes / No / Not sure" questions each get an optional follow-up.
	$q = function ( $name, $label, $follow ) {
		return array(
			dh_f( $name, $label, 'radio', array( 'required' => true, 'options' => dh_ynm() ) ),
			dh_f( $name . '_more', $follow, 'text' ),
		);
	};
	$med = array_merge(
		array(
			dh_f( 'h_you', 'About you', 'heading' ),
			dh_f( 'full_name', 'Full name', 'text', array( 'half' => true, 'autocomplete' => 'name' ) ),
			dh_f( 'dob', 'Date of birth (day/month/year)', 'date', array( 'half' => true, 'autocomplete' => 'bday' ) ),
			dh_f( 'address', 'Address (home)', 'text', array( 'half' => true, 'autocomplete' => 'street-address' ) ),
			dh_f( 'postal', 'Postal code', 'text', array( 'half' => true, 'autocomplete' => 'postal-code' ) ),
			dh_f( 'city', 'City', 'text', array( 'half' => true, 'autocomplete' => 'address-level2' ) ),
			dh_f( 'phone', 'Phone', 'tel', array( 'half' => true, 'autocomplete' => 'tel' ) ),
			dh_f( 'occupation', 'Occupation', 'text', array( 'half' => true ) ),
			dh_f( 'business_phone', 'Business phone', 'tel', array( 'half' => true ) ),
			dh_f( 'cell_phone', 'Cell phone', 'tel', array( 'half' => true ) ),
			dh_f( 'email', 'E-mail', 'email', array( 'required' => true, 'half' => true, 'autocomplete' => 'email' ) ),
			dh_f( 'referred_by', 'Who referred you to our office?', 'text', array( 'required' => true, 'half' => true ) ),
			dh_f( 'h_emergency', 'In case of emergency, we should notify', 'heading' ),
			dh_f( 'em_name', 'Name', 'text', array( 'required' => true, 'half' => true ) ),
			dh_f( 'em_relationship', 'Relationship', 'text', array( 'required' => true, 'half' => true ) ),
			dh_f( 'em_phone', 'Day-time phone', 'tel', array( 'required' => true, 'half' => true ) ),
			dh_f( 'h_care', 'Your doctors and insurance', 'heading' ),
			dh_f( 'family_doctor', 'Name of family doctor', 'text', array( 'required' => true, 'half' => true ) ),
			dh_f( 'family_doctor_phone', 'Phone', 'tel', array( 'required' => true, 'half' => true ) ),
			dh_f( 'ohip', 'OHIP #', 'text', array( 'required' => true, 'half' => true ) ),
			dh_f( 'specialist', 'Name of medical specialist', 'text', array( 'half' => true ) ),
			dh_f( 'specialty', 'Area of speciality', 'text', array( 'half' => true ) ),
			dh_f( 'specialist_contact', 'Phone or address', 'text', array( 'half' => true ) ),
			dh_f( 'insurance', 'Insurance information', 'text', array( 'required' => true ) ),
			dh_f( 'h_health', 'Your health', 'heading' ),
		),
		$q( 'treated', 'Are you being treated for any medical condition at the present or have you been treated within the past year?', 'If so, why?' ),
		array( dh_f( 'last_checkup', 'When was your last medical checkup?', 'text', array( 'required' => true ) ) ),
		$q( 'health_change', 'Has there been any change in your general health in the past year?', 'If yes, please explain.' ),
		$q( 'allergies', 'Do you have any allergies? If you answered yes, please list using the categories below: a) medications b) latex/rubber products c) other (e.g., hayfever, foods)', 'If yes, please list.' ),
		$q( 'medications', 'Are you taking any medications, non-prescription drugs or herbal supplements of any kind?', 'If yes, please list.' ),
		$q( 'reaction', 'Have you ever had a peculiar or adverse reaction to any medications or injections?', 'If yes, please explain.' ),
		$q( 'heart_bp', 'Do you have or have you ever had any heart or blood pressure problems?', 'If yes, please explain.' ),
		$q( 'asthma', 'Do you have or have you ever had asthma?', 'If yes, please explain.' ),
		$q( 'valve', 'Do you have or have you ever had an artificial heart valve, an infection of the heart (i.e., infective endocarditis), a heart condition from birth (i.e., congenital heart disease) or a heart transplant?', 'If yes, please explain.' ),
		$q( 'joint', 'Do you have a prosthetic or artificial joint?', 'If yes, please explain.' ),
		$q( 'immune', 'Do you have any conditions or therapies that could affect your immune system, e.g., leukemia, AIDs, HIV infection, radiotherapy, chemotherapy?', 'If yes, please explain.' ),
		$q( 'liver', 'Have you ever had hepatitis, jaundice or liver disease?', 'If yes, please explain.' ),
		$q( 'bleeding', 'Do you have a bleeding problem or bleeding disorder?', 'If yes, please explain.' ),
		$q( 'hospital', 'Have you ever been hospitalized for any illnesses or operations?', 'If yes, please explain.' ),
		array(
			dh_f( 'conditions', 'Do you have or have you ever had any of the following? Please check.', 'checkboxes', array(
				'required' => true,
				'options'  => array( 'chest pain, angina', 'rheumatic fever', 'lung disease', 'diabetes', 'kidney disease', 'osteoporosis', 'heart attack', 'mitral valve prolapse', 'tuberculosis', 'stomach', 'stomach ulcers', 'thyroid disease', 'stroke', 'heart murmur', 'cancer', 'arthritis', 'drug/alcohol dependency', 'shortness of breath', 'pacemaker', 'steroid therapy', 'seizures (epilepsy)' ),
			) ),
		),
		$q( 'other_conditions', 'Are there any conditions or diseases not listed above that you have or have had?', 'If so, what?' ),
		$q( 'family_history', 'Are there any diseases or medical problems that run in your family? (e.g., diabetes, cancer or heart disease)', 'If yes, please explain.' ),
		$q( 'pregnant', 'For women only: Are you breastfeeding or pregnant?', 'If pregnant, what is the expected delivery date?' ),
		array(
			dh_f( 'h_consent', 'Consent', 'heading' ),
			dh_f( 'consent', 'I, the undersigned, acknowledge that I have provided an accurate personal medical/dental history. I will inform you if there are any changes in my health or medications at future appointments. The Dental Hauz dentists or their staff may contact my physician, if necessary, to discuss any relevant medical information. I, the undersigned, consent to the performing of dental procedures, x-rays, and diagnostic tests agreed to be necessary or advisable for this case.', 'checkbox', array( 'required' => true ) ),
			dh_f( 'date', 'Date', 'date', array( 'half' => true ) ),
		)
	);
	$forms['medical-history-form'] = array(
		'title'  => 'Medical history form',
		'submit' => 'Submit',
		'fields' => $med,
	);

	return $forms;
}

/**
 * Private entries kept in the dashboard so a failed email never loses a submission.
 */
function dh_register_submissions() {
	register_post_type(
		'dh_submission',
		array(
			'labels'              => array( 'name' => 'Form entries', 'singular_name' => 'Form entry', 'menu_name' => 'Form entries' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title', 'editor' ),
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'dh_register_submissions' );

/**
 * Where submissions are sent.
 */
function dh_form_recipient() {
	$to = sanitize_email( (string) get_theme_mod( 'dh_form_email', dh_clinic_defaults()['email'] ) );
	return is_email( $to ) ? $to : get_option( 'admin_email' );
}

/**
 * Redirect back to the form with a status. When the page sent the form in the background
 * (so it can show the thank-you pop-up without leaving the page), answer with JSON instead.
 */
function dh_form_redirect( $return, $id, $status ) {
	if ( ! empty( $_POST['dh_ajax'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- public form, same checks as the redirect path.
		$msg = dh_form_message( $status );
		wp_send_json(
			array(
				'ok'      => 'sent' === $status,
				'status'  => $status,
				'message' => $msg ? $msg[1] : '',
			)
		);
	}
	$url = add_query_arg( array( 'dh_form' => $id, 'dh_status' => $status ), $return );
	wp_safe_redirect( $url . '#form-' . $id );
	exit;
}

/**
 * admin-post handler for every form.
 */
function dh_handle_form() {
	$forms  = dh_forms();
	$id     = isset( $_POST['dh_form'] ) ? sanitize_key( wp_unslash( $_POST['dh_form'] ) ) : '';
	$return = isset( $_POST['dh_return'] ) ? esc_url_raw( wp_unslash( $_POST['dh_return'] ) ) : '';
	$return = wp_validate_redirect( $return, home_url( '/' ) );

	if ( ! isset( $forms[ $id ] ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	$form = $forms[ $id ];

	// Honeypot: real visitors never fill this in.
	if ( ! empty( $_POST['dh_website'] ) ) {
		dh_form_redirect( $return, $id, 'sent' );
	}

	$values  = array();
	$missing = false;
	foreach ( $form['fields'] as $f ) {
		if ( in_array( $f['type'], array( 'heading', 'note' ), true ) ) {
			continue;
		}
		$key = 'dh_' . $f['name'];
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security -- sanitized per type below.
		$val = '';
		switch ( $f['type'] ) {
			case 'email':
				$val = is_string( $raw ) ? sanitize_email( $raw ) : '';
				if ( '' !== trim( (string) $raw ) && '' === $val ) {
					$missing = true; // Entered but not a valid address.
				}
				break;
			case 'textarea':
				$val = is_string( $raw ) ? sanitize_textarea_field( $raw ) : '';
				break;
			case 'date':
				$val = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
				if ( '' !== $val && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $val ) ) {
					$val = '';
					$missing = true;
				}
				break;
			case 'select':
			case 'radio':
				$val = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
				if ( '' !== $val && ! in_array( $val, $f['options'], true ) ) {
					$val = '';
					$missing = true;
				}
				break;
			case 'checkboxes':
				$picked = is_array( $raw ) ? array_map( 'sanitize_text_field', $raw ) : array();
				$val    = implode( ', ', array_values( array_intersect( $f['options'], $picked ) ) );
				break;
			case 'checkbox':
				$val = ! empty( $raw ) ? 'Yes' : '';
				break;
			default:
				$val = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
		}
		if ( ! empty( $f['required'] ) && '' === $val ) {
			$missing = true;
		}
		$values[ $f['name'] ] = $val;
	}
	if ( $missing ) {
		dh_form_redirect( $return, $id, 'invalid' );
	}

	// Limit submissions per hour per address. Generous, because patients may share the clinic's own device.
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key  = 'dh_form_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 30 ) {
		dh_form_redirect( $return, $id, 'error' );
	}
	set_transient( $key, $hits + 1, HOUR_IN_SECONDS );

	// Build a readable message, grouped by the form's headings (plain text, plus the same rows for the HTML email).
	$body     = $form['title'] . "\n" . str_repeat( '=', strlen( $form['title'] ) ) . "\n";
	$sections = array( array( 'heading' => '', 'rows' => array() ) );
	foreach ( $form['fields'] as $f ) {
		if ( 'heading' === $f['type'] ) {
			$body      .= "\n" . strtoupper( $f['label'] ) . "\n";
			$sections[] = array( 'heading' => $f['label'], 'rows' => array() );
			continue;
		}
		if ( 'note' === $f['type'] ) {
			continue;
		}
		$v     = $values[ $f['name'] ];
		$body .= $f['label'] . ': ' . ( '' !== $v ? $v : '-' ) . "\n";
		$sections[ count( $sections ) - 1 ]['rows'][] = array( $f['label'], $v, $f['type'] );
	}

	$who = '';
	foreach ( array( 'first_name', 'full_name', 'patient_name', 'ack_name', 'dr_name' ) as $n ) {
		if ( ! empty( $values[ $n ] ) ) {
			$who = $values[ $n ] . ( ! empty( $values['last_name'] ) && 'first_name' === $n ? ' ' . $values['last_name'] : '' );
			break;
		}
	}
	$subject = sprintf( '[%s] %s%s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $form['title'], $who ? ' from ' . $who : '' );

	if ( apply_filters( 'dh_store_submissions', (bool) get_theme_mod( 'dh_store_submissions', true ) ) ) {
		wp_insert_post(
			array(
				'post_type'    => 'dh_submission',
				'post_status'  => 'private',
				'post_title'   => $subject,
				'post_content' => $body,
			)
		);
	}

	// Sent as "The Dental Hauz <wordpress@yoursite>", the address WordPress uses by default, with the clinic's name.
	$host    = wp_parse_url( home_url(), PHP_URL_HOST );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( $host ) {
		$headers[] = 'From: ' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . ' <wordpress@' . preg_replace( '/^www\./', '', $host ) . '>';
	}
	if ( ! empty( $values['email'] ) && is_email( $values['email'] ) ) {
		$headers[] = 'Reply-To: ' . ( $who ? $who : 'Patient' ) . ' <' . $values['email'] . '>';
	}
	$html = dh_email_layout(
		$form['title'],
		sprintf(
			/* translators: %s: date and time */
			esc_html__( 'A new submission arrived from the website on %s.', 'the-dental-hauz' ),
			esc_html( wp_date( 'l, F j, Y \a\t g:i A' ) )
		),
		dh_email_rows_html( $sections )
	);
	$sent = dh_mail_html( dh_form_recipient(), $subject, $html, $body, $headers );

	// A thank-you copy for the patient, when they gave an email address. It repeats only the
	// choices picked from the form's own lists (never free text), so it cannot be used to relay messages.
	if ( $sent && ! empty( $values['email'] ) && is_email( $values['email'] ) ) {
		dh_send_confirmation( $values['email'], $form, $sections, $headers );
	}
	dh_form_redirect( $return, $id, $sent ? 'sent' : 'error' );
}
add_action( 'admin_post_nopriv_dh_form', 'dh_handle_form' );
add_action( 'admin_post_dh_form', 'dh_handle_form' );

/**
 * Status message for a form after the redirect back.
 *
 * @return array{0:string,1:string}|null
 */
function dh_form_notice( $id ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display flags.
	$which  = isset( $_GET['dh_form'] ) ? sanitize_key( wp_unslash( $_GET['dh_form'] ) ) : '';
	$status = isset( $_GET['dh_status'] ) ? sanitize_key( wp_unslash( $_GET['dh_status'] ) ) : '';
	// phpcs:enable
	if ( $which !== $id ) {
		return null;
	}
	return dh_form_message( $status );
}

/**
 * Message shown for a submission status.
 *
 * @return array{0:string,1:string}|null
 */
function dh_form_message( $status ) {
	switch ( $status ) {
		case 'sent':
			return array( 'is-ok', __( 'Thank you. Your submission was successful and the team will be in touch.', 'the-dental-hauz' ) );
		case 'invalid':
			return array( 'is-error', __( 'Your submission failed because the form is invalid. Please fill in every required field and try again.', 'the-dental-hauz' ) );
		case 'error':
			return array( 'is-error', __( 'Your submission failed because of an error. Please try again in a little while or call the clinic.', 'the-dental-hauz' ) );
	}
	return null;
}

/**
 * Render a form.
 */
function dh_render_form( $id, $args = array() ) {
	$forms = dh_forms();
	if ( ! isset( $forms[ $id ] ) ) {
		return;
	}
	$form   = $forms[ $id ];
	$notice = dh_form_notice( $id );
	$uid    = 'f-' . $id . ( isset( $args['suffix'] ) ? '-' . $args['suffix'] : '' );
	$class  = 'form' . ( ! empty( $args['wide'] ) ? ' form--wide' : '' );
	?>
	<form class="<?php echo esc_attr( $class ); ?>" id="form-<?php echo esc_attr( $id ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="dh_form">
		<input type="hidden" name="dh_form" value="<?php echo esc_attr( $id ); ?>">
		<input type="hidden" name="dh_return" value="<?php echo esc_url( dh_current_url() ); ?>">
		<div class="hp-field" aria-hidden="true"><label for="<?php echo esc_attr( $uid ); ?>-website">Website</label><input id="<?php echo esc_attr( $uid ); ?>-website" type="text" name="dh_website" tabindex="-1" autocomplete="off"></div>
		<?php if ( $notice ) : ?>
			<p class="form-note <?php echo 'is-error' === $notice[0] ? 'is-error' : 'is-ok'; ?>" role="status"><?php echo esc_html( $notice[1] ); ?></p>
		<?php endif; ?>
		<div class="fields">
		<?php foreach ( $form['fields'] as $f ) : ?>
			<?php
			$fid  = $uid . '-' . $f['name'];
			$name = 'dh_' . $f['name'];
			$req  = ! empty( $f['required'] );
			$cls  = 'field' . ( ! empty( $f['half'] ) ? ' field--half' : '' );
			switch ( $f['type'] ) :
				case 'heading':
					?>
					<h3 class="form-heading"><?php echo esc_html( $f['label'] ); ?></h3>
					<?php
					break;
				case 'note':
					?>
					<p class="form-terms"><?php echo esc_html( $f['label'] ); ?></p>
					<?php
					break;
				case 'select':
					?>
					<div class="<?php echo esc_attr( $cls ); ?>">
						<label for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $f['label'] ); ?></label>
						<select id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $name ); ?>"<?php echo $req ? ' required' : ''; ?>>
							<option value=""><?php esc_html_e( 'Select', 'the-dental-hauz' ); ?></option>
							<?php foreach ( $f['options'] as $o ) : ?>
								<option value="<?php echo esc_attr( $o ); ?>"><?php echo esc_html( $o ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php
					break;
				case 'radio':
				case 'checkboxes':
					$is_radio = 'radio' === $f['type'];
					?>
					<fieldset class="field field--choices">
						<legend><?php echo esc_html( $f['label'] ); ?></legend>
						<div class="choices">
						<?php foreach ( $f['options'] as $n => $o ) : ?>
							<label class="choice" for="<?php echo esc_attr( $fid . '-' . $n ); ?>">
								<input id="<?php echo esc_attr( $fid . '-' . $n ); ?>" type="<?php echo $is_radio ? 'radio' : 'checkbox'; ?>" name="<?php echo esc_attr( $name . ( $is_radio ? '' : '[]' ) ); ?>" value="<?php echo esc_attr( $o ); ?>"<?php echo ( $req && $is_radio && 0 === $n ) ? ' required' : ''; ?>>
								<span><?php echo esc_html( $o ); ?></span>
							</label>
						<?php endforeach; ?>
						</div>
					</fieldset>
					<?php
					break;
				case 'checkbox':
					?>
					<div class="field field--agree">
						<label class="choice" for="<?php echo esc_attr( $fid ); ?>">
							<input id="<?php echo esc_attr( $fid ); ?>" type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"<?php echo $req ? ' required' : ''; ?>>
							<span><?php echo esc_html( $f['label'] ); ?></span>
						</label>
					</div>
					<?php
					break;
				case 'textarea':
					?>
					<div class="<?php echo esc_attr( $cls ); ?>">
						<label for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $f['label'] ); ?></label>
						<textarea id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" maxlength="2000"<?php echo $req ? ' required' : ''; ?>></textarea>
					</div>
					<?php
					break;
				default:
					?>
					<div class="<?php echo esc_attr( $cls ); ?>">
						<label for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $f['label'] ); ?></label>
						<input id="<?php echo esc_attr( $fid ); ?>" type="<?php echo esc_attr( $f['type'] ); ?>" name="<?php echo esc_attr( $name ); ?>" maxlength="200"<?php echo $req ? ' required' : ''; ?><?php echo ! empty( $f['autocomplete'] ) ? ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"' : ''; ?>>
						<?php if ( ! empty( $f['help'] ) ) : ?>
							<span class="help"><?php echo esc_html( $f['help'] ); ?></span>
						<?php endif; ?>
					</div>
					<?php
			endswitch;
			?>
		<?php endforeach; ?>
		</div>
		<button class="btn" type="submit"><?php echo esc_html( $form['submit'] ); ?></button>
	</form>
	<?php
}

/**
 * Current page URL without the status arguments.
 */
function dh_current_url() {
	global $wp;
	$path = isset( $wp->request ) ? $wp->request : '';
	return remove_query_arg( array( 'dh_form', 'dh_status' ), home_url( user_trailingslashit( $path ) ) );
}

/**
 * Send an HTML email with a plain-text alternative for mail apps that do not show HTML.
 *
 * @param string   $to      Recipient.
 * @param string   $subject Subject.
 * @param string   $html    HTML body.
 * @param string   $text    Plain-text body.
 * @param string[] $headers Headers (any Content-Type is replaced).
 * @return bool
 */
function dh_mail_html( $to, $subject, $html, $text, $headers ) {
	$headers   = array_values( array_filter( $headers, function ( $h ) { return 0 !== stripos( $h, 'Content-Type:' ); } ) );
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$alt       = function ( $mailer ) use ( $text ) {
		$mailer->AltBody = $text; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	};
	add_action( 'phpmailer_init', $alt );
	$ok = wp_mail( $to, $subject, $html, $headers );
	remove_action( 'phpmailer_init', $alt );
	return (bool) $ok;
}

/**
 * Brand-styled email shell (inline styles only, table layout, so it renders the same in Gmail, Outlook and phones).
 *
 * @param string $title   Heading (plain text).
 * @param string $intro   Intro line (already escaped HTML).
 * @param string $content Body HTML (already escaped).
 * @return string
 */
function dh_email_layout( $title, $intro, $content ) {
	$umber = '#514A43';
	$sage  = '#657568';
	$light = '#A8B6A5';
	$blush = '#D8B8B2';
	$cream = '#F4F0E8';
	$serif = "Georgia, 'Times New Roman', serif";
	$sans  = "'Helvetica Neue', Arial, sans-serif";
	$logo  = get_theme_file_uri( 'assets/img/logo-light.png' );
	$name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$contact = array();
	if ( dh_clinic( 'phone' ) ) {
		$contact[] = '<a href="' . esc_url( dh_tel_href() ) . '" style="color:' . $umber . ';text-decoration:none;">' . esc_html( dh_clinic( 'phone' ) ) . '</a>';
	}
	if ( dh_clinic( 'email' ) ) {
		$contact[] = '<a href="mailto:' . esc_attr( dh_clinic( 'email' ) ) . '" style="color:' . $umber . ';text-decoration:none;">' . esc_html( dh_clinic( 'email' ) ) . '</a>';
	}
	if ( dh_address_lines() ) {
		$contact[] = esc_html( implode( ', ', dh_address_lines() ) );
	}

	return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html( $title ) . '</title></head>'
		. '<body style="margin:0;padding:0;background:' . $cream . ';">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:' . $cream . ';"><tr><td align="center" style="padding:24px 12px;">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid ' . $light . ';">'
		// Header band.
		. '<tr><td style="background:' . $sage . ';padding:26px 28px;" align="left">'
		. '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $name ) . '" width="170" style="display:block;width:170px;height:auto;border:0;">'
		. '</td></tr>'
		. '<tr><td style="height:6px;background:' . $blush . ';font-size:0;line-height:0;">&nbsp;</td></tr>'
		// Title + intro.
		. '<tr><td style="padding:28px 28px 8px 28px;">'
		. '<h1 style="margin:0 0 8px 0;font-family:' . $serif . ';font-weight:normal;font-size:26px;line-height:1.2;color:' . $umber . ';">' . esc_html( $title ) . '</h1>'
		. '<p style="margin:0;font-family:' . $sans . ';font-size:14px;line-height:1.6;color:' . $sage . ';">' . $intro . '</p>'
		. '</td></tr>'
		// Content.
		. '<tr><td style="padding:12px 28px 28px 28px;">' . $content . '</td></tr>'
		// Footer.
		. '<tr><td style="background:' . $cream . ';padding:20px 28px;border-top:1px solid ' . $light . ';font-family:' . $sans . ';font-size:13px;line-height:1.7;color:' . $umber . ';">'
		. '<strong style="font-family:' . $serif . ';font-weight:normal;font-size:16px;">' . esc_html( $name ) . '</strong><br>'
		. implode( '<br>', $contact )
		. '<br><span style="letter-spacing:2px;font-size:11px;color:' . $sage . ';text-transform:uppercase;">Your smile &middot; Your experience &middot; Your Hauz</span>'
		. '</td></tr>'
		. '</table></td></tr></table></body></html>';
}

/**
 * The answers as a tidy table, section by section, like a form response sheet.
 *
 * @param array $sections Each: heading, rows of [label, value, type].
 * @return string
 */
function dh_email_rows_html( $sections ) {
	$out = '';
	foreach ( $sections as $sec ) {
		if ( ! $sec['rows'] && '' === $sec['heading'] ) {
			continue;
		}
		if ( '' !== $sec['heading'] ) {
			$out .= '<p style="margin:22px 0 8px 0;font-family:\'Helvetica Neue\',Arial,sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#657568;">' . esc_html( $sec['heading'] ) . '</p>';
		}
		if ( ! $sec['rows'] ) {
			continue;
		}
		$out .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border-spacing:0;border:1px solid #A8B6A5;border-radius:10px;overflow:hidden;">';
		$i    = 0;
		foreach ( $sec['rows'] as $row ) {
			$bg    = ( $i++ % 2 ) ? '#ffffff' : '#F4F0E8';
			$value = '' !== (string) $row[1] ? nl2br( esc_html( $row[1] ) ) : '<span style="color:#A8B6A5;">&mdash;</span>';
			if ( 'email' === $row[2] && '' !== (string) $row[1] ) {
				$value = '<a href="mailto:' . esc_attr( $row[1] ) . '" style="color:#514A43;">' . esc_html( $row[1] ) . '</a>';
			} elseif ( 'tel' === $row[2] && '' !== (string) $row[1] ) {
				$value = '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $row[1] ) ) . '" style="color:#514A43;">' . esc_html( $row[1] ) . '</a>';
			}
			$out .= '<tr>'
				. '<td valign="top" style="background:' . $bg . ';padding:11px 14px;width:42%;font-family:\'Helvetica Neue\',Arial,sans-serif;font-size:13px;line-height:1.5;color:#657568;border-bottom:1px solid #EDE6DA;">' . esc_html( $row[0] ) . '</td>'
				. '<td valign="top" style="background:' . $bg . ';padding:11px 14px;font-family:\'Helvetica Neue\',Arial,sans-serif;font-size:14px;line-height:1.5;color:#514A43;border-bottom:1px solid #EDE6DA;">' . $value . '</td>'
				. '</tr>';
		}
		$out .= '</table>';
	}
	return $out;
}

/**
 * Thank-you email to the person who filled in the form.
 *
 * @param string $to       Patient email.
 * @param array  $form     Form definition.
 * @param array  $sections Answers by section.
 * @param array  $headers  Base headers (From).
 */
function dh_send_confirmation( $to, $form, $sections, $headers ) {
	$picked = array();
	foreach ( $sections as $sec ) {
		foreach ( $sec['rows'] as $row ) {
			if ( in_array( $row[2], array( 'select', 'radio' ), true ) && '' !== (string) $row[1] ) {
				$picked[] = $row;
			}
		}
	}
	$name    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject = sprintf( '[%s] Thank you, we received your %s', $name, strtolower( $form['title'] ) );
	$intro   = esc_html__( 'Thank you, your registration is done. Our team will review it and contact you shortly to confirm. If anything changes, simply reply to this email or call us.', 'the-dental-hauz' );
	$content = $picked ? dh_email_rows_html( array( array( 'heading' => __( 'Your request', 'the-dental-hauz' ), 'rows' => $picked ) ) ) : '';
	$hours   = dh_hours();
	if ( $hours ) {
		$rows = array();
		foreach ( $hours as $h ) {
			$rows[] = array( $h[0], $h[1], 'text' );
		}
		$content .= dh_email_rows_html( array( array( 'heading' => __( 'Opening hours', 'the-dental-hauz' ), 'rows' => $rows ) ) );
	}
	$text = $form['title'] . "\n\n" . wp_strip_all_tags( $intro ) . "\n";
	foreach ( $picked as $row ) {
		$text .= $row[0] . ': ' . $row[1] . "\n";
	}
	$text .= "\n" . $name . "\n" . dh_clinic( 'phone' ) . "\n" . dh_clinic( 'email' ) . "\n" . implode( ', ', dh_address_lines() ) . "\n";

	$headers   = array_values( array_filter( $headers, function ( $h ) { return 0 !== stripos( $h, 'Reply-To:' ); } ) );
	$clinic    = dh_clinic( 'email' );
	if ( $clinic && is_email( $clinic ) ) {
		$headers[] = 'Reply-To: ' . $name . ' <' . $clinic . '>';
	}
	return dh_mail_html( $to, $subject, dh_email_layout( __( 'Thank you!', 'the-dental-hauz' ), $intro, $content ), $text, $headers );
}

