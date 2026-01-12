<?php
/**
 * Hide PHP Version Feature
 *
 * @package Coreline
 */

declare(strict_types=1);

namespace Coreline\Features;

use Coreline\Abstracts\AbstractFeature;


if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class HidePHPVersion
 *
 * Removes PHP version information from HTTP headers to improve security.
 */
final class HidePHPVersion extends AbstractFeature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'Hide PHP Version';
		$this->description = 'Remove PHP version from HTTP headers for security';
		$this->settingsKey = 'hide_php_version';

		parent::__construct();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		add_action( 'send_headers', array( $this, 'removePhpVersionHeader' ) );
		add_filter( 'wp_headers', array( $this, 'removePhpVersionFromHeaders' ) );
	}

	/**
	 * Remove PHP version from HTTP headers.
	 *
	 * @return void
	 */
	public function removePhpVersionHeader(): void {
		header_remove( 'X-Powered-By' );
	}

	/**
	 * Remove PHP version from WordPress headers.
	 *
	 * @param array $headers The array of headers.
	 * @return array
	 */
	public function removePhpVersionFromHeaders( array $headers ): array {
		unset( $headers['X-Powered-By'] );
		return $headers;
	}
}
