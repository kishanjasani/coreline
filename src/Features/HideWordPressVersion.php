<?php
/**
 * Hide WordPress Version Feature
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
 * Class HideWordPressVersion
 *
 * Removes WordPress version number from the site to improve security.
 */
final class HideWordPressVersion extends AbstractFeature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = 'Hide WordPress Version';
		$this->description = 'Remove WordPress version numbers from HTML and RSS feeds for security';
		$this->settingsKey = 'hide_wp_version';

		parent::__construct();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
		add_filter( 'style_loader_src', array( $this, 'removeVersionQuery' ), 15 );
		add_filter( 'script_loader_src', array( $this, 'removeVersionQuery' ), 15 );
	}

	/**
	 * Remove version query strings from scripts and styles.
	 *
	 * @param string $src The source URL.
	 * @return string
	 */
	public function removeVersionQuery( string $src ): string {
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}
}
