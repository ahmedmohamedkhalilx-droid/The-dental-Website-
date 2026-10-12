<?php
/**
 * Customizer: clinic details used across the theme.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defaults, taken from the previous site. Anything changed in the Customizer overrides them.
 */
function dh_clinic_defaults() {
	return array(
		'phone'     => '(905) 470-4289',
		'email'     => 'thedentalhauz@gmail.com',
		'address'   => "8321 Kennedy Rd #22\nMarkham, ON L3R 5N4",
		'hours'     => "Monday – Friday | 10:00 AM – 6:00 PM\nSaturday | 9:00 AM – 6:00 PM\nSunday | Closed",
		'maps_url'  => 'https://maps.app.goo.gl/tq5NC39rXTekr9da9',
		'map_query' => 'The Dental Hauz 8321 Kennedy Rd #22, Markham, ON L3R 5N4, Canada',
		'facebook'  => 'https://www.facebook.com/profile.php?id=61588796471226',
		'instagram' => 'https://www.instagram.com/thedental.hauz',
	);
}

/**
 * One clinic detail: the Customizer value, or the default.
 */
function dh_clinic( $key ) {
	$d = dh_clinic_defaults();
	return (string) get_theme_mod( 'dh_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

function dh_customize_register( $wp_customize ) {
	$d = dh_clinic_defaults();
	$wp_customize->add_section(
		'dh_clinic',
		array(
			'title'       => __( 'Clinic details', 'the-dental-hauz' ),
			'description' => __( 'Shown in the top bar, contact page, footer and booking forms. Leave a field empty to hide it.', 'the-dental-hauz' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'phone'      => array( __( 'Phone number', 'the-dental-hauz' ), 'text', 'sanitize_text_field' ),
		'email'      => array( __( 'Public email address', 'the-dental-hauz' ), 'text', 'sanitize_email' ),
		'address'    => array( __( 'Clinic address (one line per row)', 'the-dental-hauz' ), 'textarea', 'sanitize_textarea_field' ),
		'hours'      => array( __( 'Opening hours (one per line, as: Day | Time)', 'the-dental-hauz' ), 'textarea', 'sanitize_textarea_field' ),
		'maps_url'   => array( __( 'Google Maps link', 'the-dental-hauz' ), 'text', 'esc_url_raw' ),
		'map_query'  => array( __( 'Address used for the embedded map', 'the-dental-hauz' ), 'text', 'sanitize_text_field' ),
		'facebook'   => array( __( 'Facebook page link', 'the-dental-hauz' ), 'text', 'esc_url_raw' ),
		'instagram'  => array( __( 'Instagram link', 'the-dental-hauz' ), 'text', 'esc_url_raw' ),
		'form_email' => array( __( 'Send form submissions to', 'the-dental-hauz' ), 'text', 'sanitize_email' ),
	);
	foreach ( $fields as $key => $field ) {
		$default = 'form_email' === $key ? $d['email'] : $d[ $key ];
		$wp_customize->add_setting( 'dh_' . $key, array( 'default' => $default, 'sanitize_callback' => $field[2] ) );
		$wp_customize->add_control( 'dh_' . $key, array( 'label' => $field[0], 'section' => 'dh_clinic', 'type' => $field[1] ) );
	}

	$wp_customize->add_setting( 'dh_store_submissions', array( 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$wp_customize->add_control( 'dh_store_submissions', array( 'label' => __( 'Keep a private copy of form submissions in the dashboard (Form entries)', 'the-dental-hauz' ), 'section' => 'dh_clinic', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'dh_show_stats', array( 'default' => false, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$wp_customize->add_control( 'dh_show_stats', array( 'label' => __( 'Show the "Our Accomplishments" counters on the About and FAQ pages', 'the-dental-hauz' ), 'section' => 'dh_clinic', 'type' => 'checkbox' ) );
}
add_action( 'customize_register', 'dh_customize_register' );
