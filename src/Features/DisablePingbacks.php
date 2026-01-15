<?php
/**
 * Disable Pingbacks Feature
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
 * Class DisablePingbacks
 *
 * Disables XML-RPC pingbacks and trackbacks.
 */
final class DisablePingbacks extends AbstractFeature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'Disable Pingbacks';
		$this->description = 'Disable XML-RPC pingbacks and trackbacks for security';
		$this->settingsKey = 'disable_pingbacks';

		parent::__construct();
	}

	/**
	 * Get translated feature name.
	 *
	 * @return string
	 */
	protected function getTranslatedName(): string {
		return __( 'Disable Pingbacks', 'coreline' );
	}

	/**
	 * Get translated feature description.
	 *
	 * @return string
	 */
	protected function getTranslatedDescription(): string {
		return __( 'Disable XML-RPC pingbacks and trackbacks for security', 'coreline' );
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'wp_headers', array( $this, 'removeXPingbackHeader' ) );
		add_filter( 'pings_open', '__return_false', PHP_INT_MAX );
		add_filter( 'xmlrpc_methods', array( $this, 'removePingbackMethods' ) );
	}

	/**
	 * Remove X-Pingback header.
	 *
	 * @param array $headers HTTP headers.
	 * @return array
	 */
	public function removeXPingbackHeader( array $headers ): array {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	/**
	 * Remove pingback methods from XML-RPC.
	 *
	 * @param array $methods XML-RPC methods.
	 * @return array
	 */
	public function removePingbackMethods( array $methods ): array {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}
}
