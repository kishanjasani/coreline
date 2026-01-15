<?php
/**
 * Disable Emojis Feature
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
 * Class DisableEmojis
 *
 * Removes WordPress emoji scripts and styles to reduce page load.
 */
final class DisableEmojis extends AbstractFeature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'Disable Emojis';
		$this->description = 'Remove WordPress emoji detection scripts to improve performance';
		$this->settingsKey = 'disable_emojis';

		parent::__construct();
	}

	/**
	 * Get translated feature name.
	 *
	 * @return string
	 */
	protected function getTranslatedName(): string {
		return __( 'Disable Emojis', 'coreline' );
	}

	/**
	 * Get translated feature description.
	 *
	 * @return string
	 */
	protected function getTranslatedDescription(): string {
		return __( 'Remove WordPress emoji detection scripts to improve performance', 'coreline' );
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		add_filter( 'tiny_mce_plugins', array( $this, 'disableTinymceEmojis' ) );
		add_filter( 'wp_resource_hints', array( $this, 'removeEmojiDnsPrefetch' ), 10, 2 );
	}

	/**
	 * Remove emoji DNS prefetch.
	 *
	 * @param array  $urls          URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed.
	 * @return array
	 */
	public function removeEmojiDnsPrefetch( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' !== $relation_type ) {
			return $urls;
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using WordPress core filter.
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/' );

		return array_filter(
			$urls,
			function ( $url ) use ( $emoji_svg_url ) {
				return strpos( $url, $emoji_svg_url ) === false;
			}
		);
	}

	/**
	 * Disable TinyMCE emoji plugin.
	 *
	 * @param array $plugins TinyMCE plugins.
	 * @return array
	 */
	public function disableTinymceEmojis( array $plugins ): array {
		return array_diff( $plugins, array( 'wpemoji' ) );
	}
}
