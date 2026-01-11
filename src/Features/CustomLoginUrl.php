<?php
/**
 * Custom Login URL Feature
 *
 * Lightweight, secure implementation.
 * Changes wp-login.php URL without modifying WordPress core files.
 *
 * @package Coreline
 */

declare(strict_types=1);

namespace Coreline\Features;

use Coreline\Abstracts\AbstractFeature;
use Coreline\Admin\Settings;


if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class CustomLoginUrl
 *
 * Security-focused login URL customization that:
 * - Blocks direct access to wp-login.php
 * - Rewrites all WordPress-generated login URLs
 * - Enforces access control on wp-admin
 * - Handles edge cases (AJAX, cron, WP-CLI, etc.)
 */
final class CustomLoginUrl extends AbstractFeature {

	/**
	 * Custom login slug.
	 *
	 * @var string
	 */
	private string $loginSlug;

	/**
	 * Option name for storing custom slug.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'coreline_login_slug';

	/**
	 * Default login slug.
	 *
	 * @var string
	 */
	private const DEFAULT_SLUG = 'secure-login';

	/**
	 * Flag indicating wp-login.php was accessed directly.
	 *
	 * @var bool
	 */
	private bool $wpLoginAccessed = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = 'Custom Login URL';
		$this->description = 'Change wp-login.php URL to prevent automated brute-force attacks';
		$this->settingsKey = 'custom_login_url_enabled';
		$this->loginSlug = $this->getLoginSlug();

		parent::__construct();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		// PHASE 1: Request interception (early hook)
		add_action( 'plugins_loaded', array( $this, 'interceptRequest' ), 9999 );

		// PHASE 2: Access control enforcement
		add_action( 'wp_loaded', array( $this, 'enforceAccessControl' ) );

		// PHASE 3: URL rewriting filters
		add_filter( 'site_url', array( $this, 'rewriteLoginUrl' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'rewriteLoginUrl' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'rewriteRedirectUrl' ), 10, 2 );
		add_filter( 'login_url', array( $this, 'filterLoginUrl' ), 10, 3 );

		// PHASE 4: Special case handling
		add_filter( 'site_option_welcome_email', array( $this, 'rewriteWelcomeEmail' ) );
	}

	/**
	 * Get the custom login slug.
	 *
	 * @return string
	 */
	private function getLoginSlug(): string {
		// Get from settings first, then fallback to option, then default
		$slug = Settings::get( 'custom_login_slug', self::DEFAULT_SLUG );

		// Sanitize and validate
		$slug = sanitize_title_with_dashes( $slug );

		// Ensure slug is valid
		if ( empty( $slug ) || $this->isReservedSlug( $slug ) ) {
			$slug = self::DEFAULT_SLUG;
		}

		return apply_filters( 'coreline_login_slug', $slug );
	}

	/**
	 * Check if slug is reserved by WordPress.
	 *
	 * @param string $slug Slug to check.
	 * @return bool
	 */
	private function isReservedSlug( string $slug ): bool {
		// WordPress core reserved slugs
		$reserved = array(
			'wp-admin',
			'wp-content',
			'wp-includes',
			'admin',
			'login',
			'wp-login',
			'xmlrpc',
			'wp-cron',
		);

		// Get WordPress public query vars
		global $wp;
		if ( $wp && isset( $wp->public_query_vars ) ) {
			$reserved = array_merge( $reserved, $wp->public_query_vars );
		}

		return in_array( $slug, $reserved, true );
	}

	/**
	 * PHASE 1: Intercept incoming requests.
	 *
	 * This runs at plugins_loaded:9999 to catch requests early.
	 *
	 * @return void
	 */
	public function interceptRequest(): void {
		global $pagenow;

		// Decode URL to prevent encoding bypasses (e.g., wp%2Dlogin.php)
		$requestUri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$requestUri = rawurldecode( $requestUri );
		$parsed     = wp_parse_url( $requestUri );
		$path       = $parsed['path'] ?? '';

		// CASE 1: Direct access to wp-login.php or wp-register.php
		if ( strpos( $requestUri, 'wp-login.php' ) !== false || strpos( $requestUri, 'wp-register.php' ) !== false ) {
			// Don't block password-protected post forms
			if ( strpos( $requestUri, 'action=postpass' ) !== false ) {
				return;
			}

			// Mark for blocking
			$this->wpLoginAccessed = true;

			// Rewrite to fake path to trigger 404
			$_SERVER['REQUEST_URI'] = $this->userTrailingSlashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';

			return;
		}

		// CASE 2: Custom login slug accessed
		$customPath  = $this->userTrailingSlashit( '/' . $this->loginSlug );
		$requestPath = untrailingslashit( $path );

		if ( $requestPath === untrailingslashit( home_url( $this->loginSlug, 'relative' ) ) ) {
			// Set pagenow so WordPress thinks we're on wp-login.php
			$pagenow = 'wp-login.php';

			// Update SCRIPT_NAME for proper WordPress routing
			$_SERVER['SCRIPT_NAME'] = $this->loginSlug;
		}
	}

	/**
	 * PHASE 2: Enforce access control.
	 *
	 * Blocks non-authenticated users from wp-admin and handles 404 for old login URL.
	 *
	 * @return void
	 */
	public function enforceAccessControl(): void {
		global $pagenow;

		// Handle old wp-login.php access - show 404
		if ( $this->wpLoginAccessed ) {
			$this->show404Page();
			return;
		}

		// Block wp-admin access for non-logged-in users
		if ( is_admin() && ! is_user_logged_in() && ! $this->isExemptedRequest() ) {
			$this->blockAdminAccess();
			return;
		}

		// If custom slug was accessed, load the real wp-login.php
		if ( $pagenow === 'wp-login.php' ) {
			$this->loadLoginPage();
		}
	}

	/**
	 * Check if current request should be exempted from blocking.
	 *
	 * @return bool
	 */
	private function isExemptedRequest(): bool {
		global $pagenow;

		// Allow AJAX requests
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return true;
		}

		// Allow cron jobs
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		// Allow WP-CLI
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		// Allow REST API requests
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		// Allow admin-post.php (for public form submissions)
		if ( $pagenow === 'admin-post.php' ) {
			return true;
		}

		// Allow WooCommerce AJAX
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking if parameter exists, not processing data.
		if ( isset( $_GET['wc-ajax'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Show 404 page for blocked wp-login.php access.
	 *
	 * @return void
	 */
	private function show404Page(): void {
		// Set 404 headers
		status_header( 404 );
		nocache_headers();

		// Load WordPress 404 template
		global $wp_query;
		$wp_query->set_404();

		// Try to load theme's 404 template
		if ( function_exists( 'get_404_template' ) ) {
			$template = get_404_template();
			if ( $template && file_exists( $template ) ) {
				include $template;
				exit;
			}
		}

		// Fallback 404 message
		wp_die(
			esc_html__( 'Page not found.', 'coreline' ),
			esc_html__( '404 Not Found', 'coreline' ),
			array( 'response' => 404 )
		);
	}

	/**
	 * Block wp-admin access for non-authenticated users.
	 *
	 * @return void
	 */
	private function blockAdminAccess(): void {
		// Redirect to 404 or custom page
		wp_safe_redirect( home_url( '/404/' ), 302 );
		exit;
	}

	/**
	 * Load the actual wp-login.php file.
	 *
	 * @return void
	 */
	private function loadLoginPage(): void {
		// Prevent caching
		nocache_headers();

		// Declare global variables that wp-login.php expects
		// These must be in global scope before wp-login.php is loaded
		global $error, $interim_login, $action, $user_login;

		// Load WordPress login page
		require_once ABSPATH . 'wp-login.php';
		exit;
	}

	/**
	 * PHASE 3: Rewrite wp-login.php in URLs.
	 *
	 * @param string      $url     The URL.
	 * @param string      $path    Path relative to URL.
	 * @param string|null $scheme  URL scheme.
	 * @param mixed       $blog_id Blog ID (site_url only).
	 * @return string
	 */
	public function rewriteLoginUrl( string $url, string $path = '', ?string $scheme = null, $blog_id = null ): string {
		// Don't rewrite password-protected post forms
		if ( strpos( $url, 'action=postpass' ) !== false ) {
			return $url;
		}

		// Don't rewrite external WordPress.com URLs (Jetpack compatibility)
		if ( strpos( $url, 'wordpress.com/wp-login.php' ) !== false ) {
			return $url;
		}

		// Replace wp-login.php with custom slug
		if ( strpos( $url, 'wp-login.php' ) !== false ) {
			$args = array();

			// Parse existing query parameters
			if ( strpos( $url, '?' ) !== false ) {
				$parts = explode( '?', $url, 2 );
				parse_str( $parts[1], $args );

				// Build new URL with custom slug
				$newUrl = $this->getNewLoginUrl( $scheme );
				if ( ! empty( $args ) ) {
					$url = add_query_arg( $args, $newUrl );
				} else {
					$url = $newUrl;
				}
			} else {
				$url = $this->getNewLoginUrl( $scheme );
			}
		}

		return $url;
	}

	/**
	 * Rewrite redirect URLs.
	 *
	 * @param string $location Redirect location.
	 * @param int    $status   HTTP status code.
	 * @return string
	 */
	public function rewriteRedirectUrl( string $location, int $status ): string {
		return $this->rewriteLoginUrl( $location );
	}

	/**
	 * Filter login URL.
	 *
	 * @param string $login_url    The login URL.
	 * @param string $redirect     The redirect URL.
	 * @param bool   $force_reauth Whether to force re-authentication.
	 * @return string
	 */
	public function filterLoginUrl( string $login_url, string $redirect = '', bool $force_reauth = false ): string {
		if ( strpos( $login_url, 'wp-login.php' ) !== false ) {
			$login_url = $this->rewriteLoginUrl( $login_url );

			if ( ! empty( $redirect ) ) {
				$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
			}

			if ( $force_reauth ) {
				$login_url = add_query_arg( 'reauth', '1', $login_url );
			}
		}

		return $login_url;
	}

	/**
	 * Rewrite welcome email URLs (multisite).
	 *
	 * @param string $value Welcome email content.
	 * @return string
	 */
	public function rewriteWelcomeEmail( string $value ): string {
		return str_replace(
			'wp-login.php',
			trailingslashit( $this->loginSlug ),
			$value
		);
	}

	/**
	 * Get the new login URL.
	 *
	 * @param string|null $scheme URL scheme.
	 * @return string
	 */
	private function getNewLoginUrl( ?string $scheme = null ): string {
		$url = home_url( '/', $scheme );

		// Handle permalink structure
		if ( get_option( 'permalink_structure' ) ) {
			// Pretty permalinks enabled
			$url = $this->userTrailingSlashit( $url . $this->loginSlug );
		} else {
			// Default permalinks (query string)
			$url = $url . '?' . $this->loginSlug;
		}

		return $url;
	}

	/**
	 * Check if site uses trailing slashes.
	 *
	 * @return bool
	 */
	private function useTrailingSlashes(): bool {
		$structure = get_option( 'permalink_structure' );
		return ! empty( $structure ) && '/' === substr( $structure, -1, 1 );
	}

	/**
	 * Add or remove trailing slash based on site settings.
	 *
	 * @param string $string URL or path.
	 * @return string
	 */
	private function userTrailingSlashit( string $string ): string {
		return $this->useTrailingSlashes()
			? trailingslashit( $string )
			: untrailingslashit( $string );
	}

	/**
	 * Get the custom login URL (public method for display).
	 *
	 * @return string
	 */
	public function getCustomLoginUrl(): string {
		return $this->getNewLoginUrl();
	}

	/**
	 * Update login slug (for admin settings).
	 *
	 * @param string $slug New slug.
	 * @return bool Success status.
	 */
	public static function updateLoginSlug( string $slug ): bool {
		// Sanitize input
		$slug = sanitize_title_with_dashes( $slug );

		if ( empty( $slug ) ) {
			return false;
		}

		// Create temporary instance to check if reserved
		$temp = new self();
		if ( $temp->isReservedSlug( $slug ) ) {
			return false;
		}

		return update_option( self::OPTION_NAME, $slug );
	}

	/**
	 * Get current login slug (for admin display).
	 *
	 * @return string
	 */
	public static function getCurrentSlug(): string {
		return get_option( self::OPTION_NAME, self::DEFAULT_SLUG );
	}
}
