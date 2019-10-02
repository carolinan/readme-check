<?php
/**
 * Fetch a list of themes from the Trac queue.
 *
 * @package Readme Check
 */

use WPTRT\Readme\Validator;

require plugin_dir_path( __FILE__ ) . 'class-validator.php';

/**
 * Fetch the list of themes.
 *
 * @param var $queue New or Approved queue.
 */
function readme_check_themes( $queue ) {
	/**
	 * Fetch the csv file from themes.trac.
	 * To use the file for the new queue, change this link to:
	 * https://themes.trac.wordpress.org/query?priority=new+theme&priority=previously+reviewed&status=new&status=reviewing&owner=&keywords=!~child-theme&reporter=!catchthemes&reporter=!acalfieri&reporter=!lyrathemes&reporter=!iamdmitrymayorov&reporter=!DannyCooper&reporter=!mahdiyazdani&reporter=!WpGaint&reporter=!ThemeZee&reporter=!themeeverest&reporter=!vowelweb&reporter=!limestreet&reporter=!themepalace&reporter=!sharkthemes&reporter=!bloggingthemes&reporter=!webulous&reporter=!academiathemes&reporter=!thinkupthemes&reporter=!machothemes&reporter=!CrestaProject&reporter=!benlumia007&reporter=!createandcode&reporter=!florianbrinkmann&reporter=!hashthemes&reporter=!Cryout+Creations&reporter=!axlethemes&reporter=!motopress&reporter=!gecodigital&reporter=!automattic&reporter=!creativthemes&reporter=!Mahesh901122&reporter=!dithemes&reporter=!silkalns&reporter=!tishonator&reporter=!uxl&reporter=!athemes&reporter=!raratheme&reporter=!ayatemplates&reporter=!keonthemes&reporter=!themehorse&reporter=!sonalsinha21&reporter=!wpdrizzle&reporter=!aslamnaik&reporter=!ThemeFarmer&reporter=!acmethemes&reporter=!taskotr&reporter=!themehunk&reporter=!salttechno&reporter=!fly2sky&reporter=!scorpionthemes&reporter=!ilovewpcom&reporter=!seosbg&reporter=!zeetheme&reporter=!sophy&reporter=!yogendranegi&reporter=!themeinwp&reporter=!wenthemes&reporter=!acosmin&format=csv&col=id&col=summary&col=status&col=time&col=changetime&col=reporter&report=2&order=time
	*/
	if ( 'new' == $queue ) {
		// Download the csv file for the New queue (2).
		echo '<h2>' . esc_html__( 'New themes:', 'readme-check' ) . '</h2><br>';

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'report_2.csv' ) ) {
			$file_last_updated = date( 'Y-m-d H:i:s.', filemtime( plugin_dir_path( __FILE__ ) . 'report_2.csv' ) );
			$date = new DateTime( $file_last_updated );
			$now  = new DateTime();
			$date = $date->diff( $now )->format( '%d' );

			if ( $date >= '1' ) {
				copy( 'https://themes.trac.wordpress.org/query?priority=new+theme&priority=previously+reviewed&status=new&status=reviewing&owner=&keywords=!~child-theme&format=csv&col=id&col=summary&col=status&col=time&col=changetime&col=reporter&report=2&order=time', plugin_dir_path( __FILE__ ) . 'report_2.csv' );
			}
		} elseif ( ! file_exists( plugin_dir_path( __FILE__ ) . 'report_2.csv' ) ) {
			copy( 'https://themes.trac.wordpress.org/query?priority=new+theme&priority=previously+reviewed&status=new&status=reviewing&owner=&keywords=!~child-theme&format=csv&col=id&col=summary&col=status&col=time&col=changetime&col=reporter&report=2&order=time', plugin_dir_path( __FILE__ ) . 'report_2.csv' );
		}

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'report_2.csv' ) ) {
			$themelist = file( plugin_dir_path( __FILE__ ) . 'report_2.csv' );
		} else {
			esc_html_e( 'The file could not be downloaded or saved correctly.', 'readme-check' );
			exit;
		}
	} else {
		// Download the csv file for the Approved queue (24).
		echo '<h2>' . esc_html__( 'Approved but not live:', 'readme-check' ) . '</h2><br>';

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'report_24.csv' ) ) {
			$file_last_updated = date( 'Y-m-d H:i:s.', filemtime( plugin_dir_path( __FILE__ ) . 'report_24.csv' ) );
			$date = new DateTime( $file_last_updated );
			$now  = new DateTime();
			$date = $date->diff( $now )->format( '%d' );

			if ( $date >= '1' ) {
				copy( 'https://themes.trac.wordpress.org/report/24?asc=1&format=csv', plugin_dir_path( __FILE__ ) . 'report_24.csv' );
			}
		} elseif ( ! file_exists( plugin_dir_path( __FILE__ ) . 'report_24.csv' )) {
			copy( 'https://themes.trac.wordpress.org/report/24?asc=1&format=csv', plugin_dir_path( __FILE__ ) . 'report_24.csv' );
		}

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'report_24.csv' ) ) {
			$themelist = file( plugin_dir_path( __FILE__ ) . 'report_24.csv' );
		} else {
			esc_html_e( 'The file could not be downloaded or saved correctly.', 'readme-check' );
			exit;
		}
	}

	/**
	 * Find the readme file and run the check.
	 */
	function run_check( $theme_slug, $theme_version, $ticketid, $submitter ) {
		$readme       = 'https://themes.svn.wordpress.org/' . trim( strtolower( $theme_slug ) ) . '/' . $theme_version . '/readme.txt';
		$file_headers = get_headers( $readme );

		if ( $file_headers[0] == 'HTTP/1.1 404 Not Found' ) {
			$readme = 'https://themes.svn.wordpress.org/' . trim( strtolower( $theme_slug ) ) . '/' . $theme_version . '/README.txt';
		}

		$file_headers2 = get_headers( $readme );
		if ( $file_headers2[0] == 'HTTP/1.1 404 Not Found' ) {
			echo '<h2>' . esc_html( $theme_slug ) . ' </h2>';
			esc_html_e( 'Hold on - this theme is missing a readme file!', 'readme-check' ) . '<br>';
			echo esc_html__( 'Trac Ticket Link:', 'readme-check' ) . ' <a href="https://themes.trac.wordpress.org/ticket/' . $ticketid . '">https://themes.trac.wordpress.org/ticket/' . $ticketid . '</a><br>';
			echo esc_html__( 'SVN:', 'readme-check' ) . ' <a href="https://themes.svn.wordpress.org/' . $theme_slug . '/' . $theme_version . '/">https://themes.svn.wordpress.org/' . $theme_slug . '/' . $theme_version . '/</a>';
			echo '<br><br>';
		} else {
			$check = new Validator( $submitter );
			$array = (array) $check->instance()->validate_url( $readme );

			if ( $array['errors'] || $array['warnings'] ) {
				echo '<h2>' . $theme_slug . ' </h2>';
				echo esc_html__( 'Trac Ticket Link:', 'readme-check' ) . ' <a href="https://themes.trac.wordpress.org/ticket/' . $ticketid . '">https://themes.trac.wordpress.org/ticket/' . $ticketid . '</a><br>';
				echo esc_html__( 'Readme:', 'readme-check' ) . ' <a href="' . $readme . '">' . $readme . '</a></br>';
				foreach ( $array['errors'] as $result ) {
					echo $result . '<br>';
				}
				foreach ( $array['warnings'] as $result ) {
					echo $result . '<br>';
				}
				echo '<br><br>';
			}
		}
	}

	if ( 'new' == $queue ) {
		foreach ( $themelist as $line ) {
			$theme         = explode( ',', $line );
			$ticketid      = $theme[0];
			$theme_summary = $theme[1];
			$theme_slug    = str_replace( 'THEME:', '', $theme_summary );
			$theme_slug    = explode( ' – ', $theme_slug );
			$theme_version = @$theme_slug[1];
			$theme_slug    = trim( str_replace( ' ', '-', trim( $theme_slug[0] ) ) );
			$submitter     = $theme[5];

			if ( isset( $theme_summary ) && $theme_slug !== 'Summary' ) {
				run_check( $theme_slug, $theme_version, $ticketid, $submitter );
			}
		}
	} else {
		foreach ( $themelist as $line ) {
			$theme         = explode( ',', $line );
			$ticketid      = $theme[0];
			$submitter     = $theme[2];
			$theme         = substr( $line, 0, strpos( $line, ',', strpos( $line, ',' ) + 1 ) );
			$theme         = str_replace( 'THEME:', '', $theme );
			$theme         = trim( strtolower( $theme ) );
			$theme         = explode( ',', $theme );
			$theme         = $theme[1];
			$theme         = explode( ' – ', $theme );
			$theme_version = @$theme[1];
			$theme_slug    = trim( str_replace( ' ', '-', trim( $theme[0] ) ) );

			if ( $theme_slug !== 'summary' ) {
				run_check( $theme_slug, $theme_version, $ticketid, $submitter );
			}
		}
	}
}
