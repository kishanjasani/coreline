<?php
/**
 * Abstract Feature
 *
 * @package Coreline
 */

declare(strict_types=1);

namespace Coreline\Abstracts;

use Coreline\Contracts\FeatureInterface;
use Coreline\Admin\Settings;

/**
 * Abstract class AbstractFeature
 *
 * Base implementation for all features.
 */
abstract class AbstractFeature implements FeatureInterface {

	/**
	 * Feature name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Feature description.
	 *
	 * @var string
	 */
	protected string $description;

	/**
	 * Whether the feature is enabled.
	 *
	 * @var bool
	 */
	protected bool $enabled = true;

	/**
	 * Settings key for this feature.
	 *
	 * @var string|null
	 */
	protected ?string $settingsKey = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->enabled = $this->shouldEnable();
	}

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! $this->isEnabled() ) {
			return;
		}

		$this->registerHooks();
	}

	/**
	 * Check if the feature is enabled.
	 *
	 * @return bool
	 */
	public function isEnabled(): bool {
		return apply_filters( "coreline_{$this->getSlug()}_enabled", $this->enabled );
	}

	/**
	 * Get the feature name.
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Get the feature description.
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Get the feature slug.
	 *
	 * @return string
	 */
	protected function getSlug(): string {
		return strtolower( str_replace( ' ', '_', $this->name ) );
	}

	/**
	 * Determine if the feature should be enabled.
	 *
	 * Checks admin settings first, then falls back to filter.
	 *
	 * @return bool
	 */
	protected function shouldEnable(): bool {
		// If settings key is defined, check settings
		if ( $this->settingsKey !== null ) {
			$settingValue = Settings::get( $this->settingsKey, true );
			return (bool) $settingValue;
		}

		// Fallback to default enabled
		return true;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	abstract protected function registerHooks(): void;
}
