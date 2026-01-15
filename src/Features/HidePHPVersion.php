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
	 * Get translated feature name.
	 *
	 * @return string
	 */
	protected function getTranslatedName(): string {
		return __( 'Hide PHP Version', 'coreline' );
	}

	/**
	 * Get translated feature description.
	 *
	 * @return string
	 */
	protected function getTranslatedDescription(): string {
		return __( 'Remove PHP version from HTTP headers for security', 'coreline' );
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		add_action( 'send_headers', array( $this, 'removePhpVersionHeader' ) );
	}

	/**
	 * Remove PHP version from HTTP headers.
	 *
	 * @return void
	 */
	public function removePhpVersionHeader(): void {
		if ( ! headers_sent() ) {
			header_remove( 'X-Powered-By' );
		}
	}
}
