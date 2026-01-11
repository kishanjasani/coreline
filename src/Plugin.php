<?php
/**
 * Main Plugin Class
 *
 * @package Coreline
 */

declare(strict_types=1);

namespace Coreline;

use Coreline\Contracts\FeatureInterface;
use Coreline\Features\DisableEmojis;
use Coreline\Features\DisablePingbacks;
use Coreline\Features\HideWordPressVersion;
use Coreline\Features\HotlinkProtection;
use Coreline\Features\CustomLoginUrl;
use Coreline\Admin\Settings;

/**
 * Class Plugin
 *
 * Main plugin orchestrator.
 */
final class Plugin {

	/**
	 * Array of registered features.
	 *
	 * @var FeatureInterface[]
	 */
	private array $features;

	/**
	 * Admin settings instance.
	 *
	 * @var Settings
	 * @phpstan-ignore-next-line Property is used for dependency injection; initialization happens in constructor
	 */
	private Settings $settings;

	/**
	 * Constructor with dependency injection.
	 *
	 * @param FeatureInterface[] $features Array of feature instances.
	 * @param Settings|null      $settings Admin settings instance.
	 */
	public function __construct( array $features = array(), ?Settings $settings = null ) {
		$this->features = ! empty( $features ) ? $features : $this->getDefaultFeatures();
		$this->settings = $settings ?? new Settings();
	}

	/**
	 * Run the plugin.
	 *
	 * @return void
	 */
	public function run(): void {
		// Initialize features
		foreach ( $this->features as $feature ) {
			$feature->init();
		}
	}

	/**
	 * Get default features.
	 *
	 * @return FeatureInterface[]
	 */
	private function getDefaultFeatures(): array {
		$features = array(
			new DisableEmojis(),
			new HideWordPressVersion(),
			new CustomLoginUrl(),
			new HotlinkProtection(),
			new DisablePingbacks(),
		);

		// Allow developers to add or remove features
		return apply_filters( 'coreline_features', $features );
	}

	/**
	 * Get all registered features.
	 *
	 * @return FeatureInterface[]
	 */
	public function getFeatures(): array {
		return $this->features;
	}
}
