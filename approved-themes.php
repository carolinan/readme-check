<?php
use WPTRT\Readme\Validator;

include plugin_dir_path( __FILE__ ) . 'class-validator.php';

/**
 * Fetch the list of approved themes
 */
function readme_check_approved_themes() {
	/** 
	 * Fetch the file.
	 * To use the file for the new queue, change this link to:
	 * https://themes.trac.wordpress.org/query?priority=new+theme&priority=previously+reviewed&status=new&status=reviewing&owner=&keywords=!~child-theme&reporter=!catchthemes&reporter=!acalfieri&reporter=!lyrathemes&reporter=!iamdmitrymayorov&reporter=!DannyCooper&reporter=!mahdiyazdani&reporter=!WpGaint&reporter=!ThemeZee&reporter=!themeeverest&reporter=!vowelweb&reporter=!limestreet&reporter=!themepalace&reporter=!sharkthemes&reporter=!bloggingthemes&reporter=!webulous&reporter=!academiathemes&reporter=!thinkupthemes&reporter=!machothemes&reporter=!CrestaProject&reporter=!benlumia007&reporter=!createandcode&reporter=!florianbrinkmann&reporter=!hashthemes&reporter=!Cryout+Creations&reporter=!axlethemes&reporter=!motopress&reporter=!gecodigital&reporter=!automattic&reporter=!creativthemes&reporter=!Mahesh901122&reporter=!dithemes&reporter=!silkalns&reporter=!tishonator&reporter=!uxl&reporter=!athemes&reporter=!raratheme&reporter=!ayatemplates&reporter=!keonthemes&reporter=!themehorse&reporter=!sonalsinha21&reporter=!wpdrizzle&reporter=!aslamnaik&reporter=!ThemeFarmer&reporter=!acmethemes&reporter=!taskotr&reporter=!themehunk&reporter=!salttechno&reporter=!fly2sky&reporter=!scorpionthemes&reporter=!ilovewpcom&reporter=!seosbg&reporter=!zeetheme&reporter=!sophy&reporter=!yogendranegi&reporter=!themeinwp&reporter=!wenthemes&reporter=!acosmin&format=csv&col=id&col=summary&col=status&col=time&col=changetime&col=reporter&report=2&order=time
	*/
	if ( ! file_exists( plugin_dir_path( __FILE__ ) . 'report_24.csv' ) ) {
		copy( 'https://themes.trac.wordpress.org/query?priority=new+theme&priority=previously+reviewed&status=new&status=reviewing&owner=&keywords=!~child-theme&reporter=!catchthemes&reporter=!acalfieri&reporter=!lyrathemes&reporter=!iamdmitrymayorov&reporter=!DannyCooper&reporter=!mahdiyazdani&reporter=!WpGaint&reporter=!ThemeZee&reporter=!themeeverest&reporter=!vowelweb&reporter=!limestreet&reporter=!themepalace&reporter=!sharkthemes&reporter=!bloggingthemes&reporter=!webulous&reporter=!academiathemes&reporter=!thinkupthemes&reporter=!machothemes&reporter=!CrestaProject&reporter=!benlumia007&reporter=!createandcode&reporter=!florianbrinkmann&reporter=!hashthemes&reporter=!Cryout+Creations&reporter=!axlethemes&reporter=!motopress&reporter=!gecodigital&reporter=!automattic&reporter=!creativthemes&reporter=!Mahesh901122&reporter=!dithemes&reporter=!silkalns&reporter=!tishonator&reporter=!uxl&reporter=!athemes&reporter=!raratheme&reporter=!ayatemplates&reporter=!keonthemes&reporter=!themehorse&reporter=!sonalsinha21&reporter=!wpdrizzle&reporter=!aslamnaik&reporter=!ThemeFarmer&reporter=!acmethemes&reporter=!taskotr&reporter=!themehunk&reporter=!salttechno&reporter=!fly2sky&reporter=!scorpionthemes&reporter=!ilovewpcom&reporter=!seosbg&reporter=!zeetheme&reporter=!sophy&reporter=!yogendranegi&reporter=!themeinwp&reporter=!wenthemes&reporter=!acosmin&format=csv&col=id&col=summary&col=status&col=time&col=changetime&col=reporter&report=2&order=time', plugin_dir_path( __FILE__ ) . 'report_24.csv' );
	}
	$themelist = file( plugin_dir_path( __FILE__ ) . 'report_24.csv' );

	foreach ( $themelist as $line ) {
		$theme      = substr( $line, 0, strpos( $line, ',', strpos( $line, ',' ) + 1 ) );
		$theme      = str_replace( 'THEME:', '', $theme );
		$theme      = trim( strtolower( $theme ) );
		$theme      = explode( ',', $theme );
		$ticket     = $theme[0];
		$theme      = $theme[1];
		$theme      = explode( ' – ', $theme );
		$theme_slug = trim( str_replace( ' ', '-', trim( $theme[0] ) ) );

		if ( isset( $theme[1] ) ) {
			$readme       = 'https://themes.svn.wordpress.org/' . $theme_slug . '/' . $theme[1] . '/readme.txt';
			$file_headers = @get_headers($readme);
			if ( $file_headers[0] == 'HTTP/1.1 404 Not Found') {
				$readme = 'https://themes.svn.wordpress.org/' . $theme_slug . '/' . $theme[1] . '/README.txt';
			}
			$file_headers2 = @get_headers($readme);
			if ( $file_headers2[0] == 'HTTP/1.1 404 Not Found') {
				echo '<h2>' . $theme_slug . ' </h2>';
				echo __( 'Hold on - this theme is missing a readme file!', 'readme-check' ) . '<br>';
				echo __( 'Trac Ticket Link:', 'readme-check' ) . ' <a href="https://themes.trac.wordpress.org/ticket/' . $ticket . '">https://themes.trac.wordpress.org/ticket/' . $ticket . '</a><br>';
				echo __( 'SVN:', 'readme-check' ) . '<a href="https://themes.svn.wordpress.org/' . $theme_slug . '/' . $theme[1] . '/">https://themes.svn.wordpress.org/' . $theme_slug . '/' . $theme[1] . '/</a>';
				echo '<br><br>';
				continue;
			}

			$test  = new Validator;
			$array = (array) $test->instance()->validate_url( $readme );

			if ( $array['errors'] || $array['warnings'] ) {
				echo '<h2>' . $theme_slug . ' </h2>';
				echo __( 'Trac Ticket Link:', 'readme-check' ) . ' <a href="https://themes.trac.wordpress.org/ticket/' . $ticket . '">https://themes.trac.wordpress.org/ticket/' . $ticket . '</a><br>';
				echo __( 'Readme: ', 'readme-check' ) . '<a href="'. $readme . '">' . $readme . '</a></br>';
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
}
