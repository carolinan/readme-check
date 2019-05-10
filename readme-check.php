<?php
/*
Plugin Name: Readme Check
Description:
Author: Poena
Version: 1.0
Text Domain: readme-check
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

include plugin_dir_path( __FILE__ ) . 'approved-themes.php';
include plugin_dir_path( __FILE__ ) . 'class-parser.php';

/**
 * Register a custom menu page.
 */
function readme_check_register_menu_page() {
	add_theme_page(
		__( 'Readme Check', 'readme-check' ),
		__( 'Readme Check', 'readme-check' ),
		'manage_options',
		'readme_check',
		'readme_check_custom_menu_page',
		6
	);
}
add_action( 'admin_menu', 'readme_check_register_menu_page' );

/**
 * Display a custom menu page
 */
function readme_check_custom_menu_page() {
	?>
	<div class="wrap">
	<div id="welcome-panel" class="welcome-panel">
	<h1><?php esc_html_e( 'Readme Check', 'readme-check' ); ?></h1>
	<h2><?php esc_html_e( 'List themes with invalid Readme files:', 'readme-check' ); ?></h2>
	<hr>
	<br>
	<?php
	readme_check_approved_themes();
	?>
	</div>
	</div>
	<?php
}

