<?php
/**
 * The doctors shown on the Doctors page, each with a page of their own (like the treatments).
 * Edit this file to add a doctor or to fill in a profile: a page is created for every entry
 * when the theme is activated or updated.
 *
 * Only the name below comes from the clinic's old site (its medical history consent form).
 * Everything else is left empty on purpose: add the photo (media library attachment ID),
 * role, short summary, bio paragraphs and credentials when you have them, and the page fills in.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'slug'        => 'dr-summer-al-maqdassy',
		'name'        => 'Dr. Summer Al Maqdassy',
		'role'        => '',            // e.g. 'General Dentist'.
		'photo'       => 0,             // Media library attachment ID of a portrait.
		'summary'     => '',            // One or two sentences shown on the card.
		'bio'         => array(),       // Paragraphs shown on the profile page.
		'credentials' => array(),       // e.g. array( 'DDS, University of Toronto' ).
	),
);
