<?php
namespace WPTRT\Readme;

//use WordPressdotorg\Plugin_Directory\Tools\Filesystem;

/**
 * A wp-admin interface to validate readme files.
 *
 * @package WordPressdotorg\Plugin_Directory\Readme
 */
class Validator {

	/**
	 * Fetch the instance of the Validator class.
	 *
	 * @static
	 */
	public static function instance() {
		static $instance = null;

		return ! is_null( $instance ) ? $instance : $instance = new Validator();
	}

	/**
	 * Validates a readme by URL.
	 *
	 * @param string $url The URL of the readme to validate.
	 * @return array Array of the readme validation results.
	 */
	public function validate_url( $url ) {
		$url = esc_url_raw( $url );

		if ( strtolower( substr( $url, -10 ) ) != 'readme.txt' ) {
			$error = sprintf(
				/* translators: %s: readme.txt */
				__( 'URL must end in %s!', 'wporg-plugins' ),
				'<code>readme.txt</code>'
			);
			return array(
				'errors' => array( $error ),
			);
		}

		$readme = wp_safe_remote_get( $url );
		if ( ! $readme_text = wp_remote_retrieve_body( $readme ) ) {
			$error = __( 'Invalid readme.txt URL.', 'wporg-plugins' );
			return array(
				'errors' => array( $error ),
			);
		}

		return $this->validate_content( $readme_text );
	}

	/**
	 * Validates readme contents by string.
	 *
	 * @param string $readme The text of the readme.
	 * @return array Array of the readme validation results.
	 */
	public function validate_content( $readme ) {

		$readme = new Parser( 'data:text/plain,' . urlencode( $readme ) );

		$errors = $warnings = array();

		// Fatal errors.
		if ( empty( $readme->name ) ) {
			$errors[] = sprintf(
				/* translators: 1: 'Plugin Name' section title, 2: 'Plugin Name' */
				__( 'We cannot find a plugin name in your readme. Plugin names look like: %1$s. Please change %2$s to reflect the actual name of your plugin.', 'wporg-plugins' ),
				'<code>=== Plugin Name ===</code>',
				'<code>Plugin Name</code>'
			);
		}

		// Warnings.
		if ( isset( $readme->warnings['requires_header_ignored'] ) ) {
			$latest_wordpress_version = defined( 'WP_CORE_STABLE_BRANCH' ) ? WP_CORE_STABLE_BRANCH : '5.0';

			$warnings[] = sprintf(
				/* translators: 1: plugin header tag; 2: Example version 5.0. 3: Example version 4.9. */
				__( 'The %1$s field was ignored. This field should only contain a valid WordPress version such as %2$s or %3$s.', 'wporg-plugins' ),
				'<code>Requires at least</code>',
				'<code>' . number_format( $latest_wordpress_version, 1 ) . '</code>',
				'<code>' . number_format( $latest_wordpress_version - 0.1, 1 ) . '</code>'
			);
		}

		if ( isset( $readme->warnings['tested_header_ignored'] ) ) {
			$latest_wordpress_version = defined( 'WP_CORE_STABLE_BRANCH' ) ? WP_CORE_STABLE_BRANCH : '5.0';

			$warnings[] = sprintf(
				/* translators: 1: plugin header tag; 2: Example version 5.0. 3: Example version 5.1. */
				__( 'The %1$s field was ignored. This field should only contain a valid WordPress version such as %2$s or %3$s.', 'wporg-plugins' ),
				'<code>Tested up to</code>',
				'<code>' . number_format( $latest_wordpress_version, 1 ) . '</code>',
				'<code>' . number_format( $latest_wordpress_version + 0.1, 1 ) . '</code>'
			);
		} elseif ( empty( $readme->tested ) ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'The %s field is missing.', 'wporg-plugins' ),
				'<code>Tested up to</code>'
			);
		}

		if ( isset( $readme->warnings['requires_php_header_ignored'] ) ) {
			$warnings[] = sprintf(
				/* translators: 1: plugin header tag; 2: Example version 5.2.4. 3: Example version 7.0. */
				__( 'The %1$s field was ignored. This field should only contain a PHP version such as %2$s or %3$s.', 'wporg-plugins' ),
				'<code>Requires PHP</code>',
				'<code>5.2.4</code>',
				'<code>7.0</code>'
			);
		}

		if ( isset( $readme->warnings['contributor_ignored'] ) ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'One or more contributors listed were ignored. The %s field should only contain WordPress.org usernames. Remember that usernames are case-sensitive.', 'wporg-plugins' ),
				'<code>Contributors</code>'
			);
		} elseif ( ! count( $readme->contributors ) ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'The %s field is missing.', 'wporg-plugins' ),
				'<code>Contributors</code>'
			);
		}

		if ( isset( $readme->warnings['contributor_automattic'] ) ) {
				$warnings[] = sprintf(
					/* translators: %s: plugin header tag */
					__( 'The %s field should only contain your WordPress.org username. Did you forget to remove automattic?', 'readme-check' ),
					'<code>Contributors</code>'
				);
		} elseif ( ! $readme->contributors ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'The %s field is missing or incorrect. The field should only contain your WordPress.org username.', 'readme-check' ),
				'<code>Contributors</code>'
			);
		}

		if ( empty( $readme->requires ) ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'The %s field is missing', 'wporg-plugins' ),
				'<code>Requires at least</code>'
			);
		}

		if ( empty( $readme->requires_php ) ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'The %s field is missing.', 'wporg-plugins' ),
				'<code>Requires PHP</code>'
			);
		}

		/** Warn about unwanted images  */
		if ( isset( $readme->warnings['unsplash'] ) ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'Images from %s are not GPL compatible and must be removed', 'readme-check' ),
				'<code>Unsplash</code>'
			);
		}

		if ( isset( $readme->warnings['pixabay'] ) ) {
			$warnings[] = sprintf(
				/* translators: %s: plugin header tag */
				__( 'Images from %s are not GPL compatible and must be removed.', 'readme-check' ),
				'<code>Pixabay</code>'
			);
		}

		return compact( 'errors', 'warnings' );

	}

}
