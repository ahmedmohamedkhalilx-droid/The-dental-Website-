<?php
/** Sets (or removes) a theme setting in the e2e site. Usage: php set-mod.php <wp-dir> <name> [value] */
$wp_e2e_dir = $argv[1];
$_SERVER['HTTP_HOST'] = '127.0.0.1:8099';
require $wp_e2e_dir . '/wp-load.php';
if ( isset( $argv[3] ) ) { set_theme_mod( $argv[2], $argv[3] ); } else { remove_theme_mod( $argv[2] ); }
echo 'ok';
