<?php
/**
 * The doctors shown on the Doctors page, each with a page of their own (like the treatments).
 * Edit this file to add a doctor or to fill in a profile: a page is created for every entry
 * when the theme is activated or updated.
 *
 * Add the photo (media library attachment ID), role, short summary, bio paragraphs and credentials
 * when you have them, and the page fills in. While the list is empty the Doctors page shows a short note.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Add a doctor like this (a page is created for each entry when the theme is activated or updated):
	// array(
	// 	'slug'        => 'dr-first-last',
	// 	'name'        => 'Dr. First Last',
	// 	'role'        => '',      // e.g. 'General Dentist'.
	// 	'photo'       => 0,       // Media library attachment ID of a portrait.
	// 	'summary'     => '',      // One or two sentences shown on the card.
	// 	'bio'         => array(), // Paragraphs shown on the profile page.
	// 	'credentials' => array(), // e.g. array( 'DDS, University of Toronto' ).
	// ),
);
