<?php
/**
 * Feature Interface
 *
 * @package Coreline
 */

declare(strict_types=1);

namespace Coreline\Contracts;

/**
 * Interface FeatureInterface
 *
 * Defines the contract for all plugin features.
 */
interface FeatureInterface {

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public function init(): void;

	/**
	 * Check if the feature is enabled.
	 *
	 * @return bool
	 */
	public function isEnabled(): bool;

	/**
	 * Get the feature name.
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Get the feature description.
	 *
	 * @return string
	 */
	public function getDescription(): string;
}
