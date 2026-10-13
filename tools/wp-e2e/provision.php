<?php
/**
 * Installs WordPress into the SQLite database, activates the theme and creates the clinic's pages like the old site had.
 * Usage: php provision.php <wp-dir>
 */
$wp_e2e_dir = $argv[1];
$_SERVER['HTTP_HOST']   = '127.0.0.1:' . ( getenv( 'WP_E2E_PORT' ) ?: 8099 );
$_SERVER['REQUEST_URI'] = '/';
define( 'WP_INSTALLING', true );
require $wp_e2e_dir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
if ( ! is_blog_installed() ) {
	wp_install( 'The Dental Hauz', 'admin', 'admin@example.test', true, '', 'e2e-password' );
}
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'timezone_string', 'America/Toronto' );

$content = require $wp_e2e_dir . '/wp-content/themes/the-dental-hauz/inc/content.php';
$ids = array();
foreach ( $content['titles'] as $slug => $title ) {
	$ids[ $slug ] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => html_entity_decode( $title ), 'post_name' => $slug, 'post_content' => 'Old Elementor content would be here.' ) );
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['home'] );

switch_theme( 'the-dental-hauz' ); // runs the theme's activation (creates the Doctors pages).
flush_rewrite_rules();
echo 'theme: ' . get_stylesheet() . ' | pages: ' . count( get_pages() ) . "\n";
