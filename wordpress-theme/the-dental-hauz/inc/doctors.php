<?php
/**
 * The team shown on the Our Team page (address /doctors/), in this order.
 *
 * Dentists have a 'slug': they also get a profile page of their own (created when the theme is activated
 * or updated). Team members without a slug are listed on the team page only.
 * No photos on purpose: the team prefers not to show them. 'photo' stays 0 unless that changes.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'slug'        => 'dr-sarah-assaya',
		'name'        => 'Dr. Sarah Assaya',
		'role'        => 'Principal Dentist',
		'photo'       => 0,
		'summary'     => '',
		'bio'         => array( 'A compassionate general dentist with a DDS from Alexandria University and licensure in Ontario since 2024. Passionate about family and pediatric dentistry, she takes a gentle, patient-centered approach to help patients of all ages feel comfortable while building healthy habits and healthy smiles.' ),
		'credentials' => array(),
	),
	array(
		'slug'        => 'dr-sherief-mohamed',
		'name'        => 'Dr. Sherief Mohamed',
		'role'        => 'General Dentist',
		'photo'       => 0,
		'summary'     => '',
		'bio'         => array( 'An internationally trained general dentist with experience across Egypt, Saudi Arabia, the UAE and Canada. With expertise in implants, oral surgery, root canal therapy and pediatric dentistry, he provides personalized, compassionate care that helps every patient feel comfortable and confident in their smile.' ),
		'credentials' => array(),
	),
	array(
		'slug'        => 'dr-rosemarie-sia',
		'name'        => 'Dr. Rosemarie Sia',
		'role'        => 'Certified Dentist',
		'photo'       => 0,
		'summary'     => '',
		'bio'         => array( 'Dr. Rosemarie is committed to high-quality, patient-centered dental care in a welcoming, comfortable environment. With a focus on prevention, personalized treatment and patient education, she helps patients of all ages maintain healthy teeth and confident smiles.' ),
		'credentials' => array(),
	),
	array(
		'slug'        => '',
		'name'        => 'Agnes Manuz',
		'role'        => 'Registered Dental Hygienist',
		'photo'       => 0,
		'summary'     => '',
		'bio'         => array( 'A Registered Dental Hygienist since 2007, dedicated to healthy smiles through preventive care and oral health education. She takes the time to understand each patient’s needs, offering practical guidance and a comfortable experience while helping them build lasting habits.' ),
		'credentials' => array(),
	),
	array(
		'slug'        => '',
		'name'        => 'Tahura',
		'role'        => 'Dental Assistant',
		'photo'       => 0,
		'summary'     => '',
		'bio'         => array( 'A valued member of our team who keeps each visit running smoothly, supporting patients and clinicians throughout their appointments. From preparing treatment rooms to assisting chairside, she helps create a positive, organized and reassuring experience.' ),
		'credentials' => array(),
	),
	array(
		'slug'        => '',
		'name'        => 'Diane',
		'role'        => 'Front Desk',
		'photo'       => 0,
		'summary'     => '',
		'bio'         => array( 'One of the friendly faces who welcomes patients to The Dental Hauz. She coordinates appointments, answers questions and keeps everything organized behind the scenes, so patients feel welcomed and cared for from their very first interaction with us.' ),
		'credentials' => array(),
	),
);
