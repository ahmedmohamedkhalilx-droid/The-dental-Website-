<?php
/**
 * Google reviews shown when neither the Trustindex plugin nor a Trustindex widget ID is set up.
 * These are real reviews from the clinic's Google listing, as shown on its old Testimonial page.
 * Text that Google showed cut off ends with an ellipsis and links to the full review on Google.
 * Add or change entries here, and keep the rating and count in step with the Google listing.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'   => 'The Dental Hauz',
	'rating' => 5,
	'count'  => 66,
	'items'  => array(
		array(
			'name'   => 'Kitty Tse',
			'rating' => 5,
			'text'   => 'Staff are always friendly and helpful.',
			'more'   => false,
		),
		array(
			'name'   => 'B M',
			'rating' => 5,
			'text'   => 'I went here to have a consultation for cosmetics for my front teeth. Dr. Sherief explained the pros and cons of veneers, crowns…',
			'more'   => true,
		),
		array(
			'name'   => 'Kelly F',
			'rating' => 5,
			'text'   => 'Dr.Sherief is outstanding! He explains everything so you truly understand your treatment and he is very very gentle! I 100%…',
			'more'   => true,
		),
	),
);
