<?php
/**
 * Plugin Name: Readme Check
 * Description: Downloads a list of themes in the Theme Review Trac queue and checks the readme file against the readme validator.
 * Author: Poena
 * Version: 1.0
 * Text Domain: readme-check
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

require plugin_dir_path( __FILE__ ) . 'themes.php';
require plugin_dir_path( __FILE__ ) . 'class-parser.php';

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
	<br>
	<?php
	esc_html_e( 'Downloads a list of themes in the Theme Review Trac queue and checks the readme file against the readme validator.', 'readme-check' );
	?>
	<br><b>
	<?php
	esc_html_e( 'This check will take some time, depending on the number of themes in the queue.', 'readme-check' );
	?>
	</b>
	<br><br>
	<?php
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'report_2.csv' ) ) {
		esc_html_e( 'A .csv file for the New queue already exists. ', 'readme-check' );
		esc_html_e( 'The file was last updated: ', 'readme-check' );

		$file_last_updated = date( 'Y-m-d H:i:s.', filemtime( plugin_dir_path( __FILE__ ) . 'report_2.csv' ) );
		$date              = new DateTime( $file_last_updated );
		$now               = new DateTime();
		echo esc_html( $date->diff( $now )->format( '%d days, %h hours and %i minutes ago.' ) );
		echo '<br>';
		esc_html_e( 'Files that has not been updated in the last 24 hours will be updated when you run the check.', 'readme-check' );
		echo '<br><br>';
	}

	if ( file_exists( plugin_dir_path( __FILE__ ) . 'report_24.csv' ) ) {
		esc_html_e( 'A .csv file for the Approved queue already exists. ', 'readme-check' );
		esc_html_e( 'The file was last updated: ', 'readme-check' );

		$file_last_updated = date( 'Y-m-d H:i:s.', filemtime( plugin_dir_path( __FILE__ ) . 'report_24.csv' ) );
		$date              = new DateTime( $file_last_updated );
		$now               = new DateTime();
		echo esc_html( $date->diff( $now )->format( '%d days, %h hours and %i minutes ago.' ) );
		echo '<br>';
		esc_html_e( 'Files that has not been updated in the last 24 hours will be updated when you run the check.', 'readme-check' );
	}
	?>
	<br><br>
	<?php
	echo '<form action="themes.php?page=readme_check" method="post">';
	echo '<label>' . esc_html__( 'Select a queue', 'readme-check' ) . '</label><br>';
	echo '<select name="queue">';
	echo '<option value="new" selected="selected">' . esc_html__( 'New', 'readme-check' ) . '</option>';
	echo '<option value="approved">' . esc_html__( 'Approved but not live', 'readme-check' ) . '</option>';
	echo '</select><br><br>';
	echo '<input class="button" type="submit" value="' . __( 'List themes with invalid readme files', 'readme-check' ) . '" />';
	echo '<input type="hidden" name="start">';
	wp_nonce_field( 'readme-check-nonce' );
	echo '</form><br><hr>';

	if ( isset( $_POST['queue'] ) ) {
		check_admin_referer( 'readme-check-nonce' );
		readme_check_themes( $_POST['queue'] );
	}

	?>
	<br>
	</div>
	</div>
	<?php
}
