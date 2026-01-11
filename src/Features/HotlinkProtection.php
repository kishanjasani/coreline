<?php
/**
 * Hotlink Protection Feature
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
 * Class HotlinkProtection
 *
 * Prevents other sites from hotlinking images.
 * Works on both Apache and Nginx servers.
 */
final class HotlinkProtection extends AbstractFeature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = 'Hotlink Protection';
		$this->description = 'Prevent other sites from hotlinking your images';
		$this->settingsKey = 'hotlink_protection';

		parent::__construct();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		add_action( 'template_redirect', array( $this, 'protectImages' ) );
	}

	/**
	 * Protect images from hotlinking.
	 *
	 * @return void
	 */
	public function protectImages(): void {
		// Only check for image requests
		if ( ! $this->isImageRequest() ) {
			return;
		}

		// Get and sanitize referer.
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		$referer = esc_url_raw( $referer );

		// Allow empty referer (direct access)
		if ( empty( $referer ) ) {
			return;
		}

		// Allow same domain
		if ( $this->isSameDomain( $referer ) ) {
			return;
		}

		// Allow search engines
		if ( $this->isSearchEngine( $referer ) ) {
			return;
		}

		// Block hotlinking
		status_header( 403 );
		exit;
	}

	/**
	 * Check if current request is for an image.
	 *
	 * @return bool
	 */
	private function isImageRequest(): bool {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$extension   = strtolower( pathinfo( $request_uri, PATHINFO_EXTENSION ) );

		return in_array( $extension, array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ), true );
	}

	/**
	 * Check if referer is from same domain.
	 *
	 * @param string $referer Referer URL.
	 * @return bool
	 */
	private function isSameDomain( string $referer ): bool {
		$site_host    = wp_parse_url( home_url(), PHP_URL_HOST );
		$referer_host = wp_parse_url( $referer, PHP_URL_HOST );

		return $site_host === $referer_host;
	}

	/**
	 * Check if referer is from a search engine.
	 *
	 * @param string $referer Referer URL.
	 * @return bool
	 */
	private function isSearchEngine( string $referer ): bool {
		$search_engines = array( 'google.com', 'bing.com', 'yahoo.com', 'duckduckgo.com' );

		foreach ( $search_engines as $engine ) {
			if ( strpos( $referer, $engine ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
