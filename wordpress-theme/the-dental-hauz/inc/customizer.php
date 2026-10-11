<?php
/**
 * Customizer: clinic details used across the theme.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dh_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'dh_clinic',
		array(
			'title'       => __( 'Clinic details', 'the-dental-hauz' ),
			'description' => __( 'Shown in the top bar, the booking section and the footer. Leave a field empty to hide it.', 'the-dental-hauz' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'dh_phone'         => array( __( 'Phone number', 'the-dental-hauz' ), 'text', 'sanitize_text_field' ),
		'dh_email'         => array( __( 'Public email address', 'the-dental-hauz' ), 'text', 'sanitize_email' ),
		'dh_address'       => array( __( 'Clinic address', 'the-dental-hauz' ), 'textarea', 'sanitize_textarea_field' ),
		'dh_hours'         => array( __( 'Opening hours (one per line, as: Day | Time)', 'the-dental-hauz' ), 'textarea', 'sanitize_textarea_field' ),
		'dh_booking_email' => array( __( 'Send booking requests to (defaults to the site admin email)', 'the-dental-hauz' ), 'text', 'sanitize_email' ),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => $field[2] ) );
		$wp_customize->add_control( $id, array( 'label' => $field[0], 'section' => 'dh_clinic', 'type' => $field[1] ) );
	}
}
add_action( 'customize_register', 'dh_customize_register' );
