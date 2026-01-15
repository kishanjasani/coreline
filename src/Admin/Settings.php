<?php
/**
 * Admin Settings Page
 *
 * @package Coreline
 */

declare(strict_types=1);

namespace Coreline\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Settings
 *
 * Handles the admin settings page and options.
 */
final class Settings {

	/**
	 * Option name for plugin settings.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'coreline_settings';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	private const PAGE_SLUG = 'coreline-settings';

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private const DEFAULTS = array(
		'disable_emojis'           => true,
		'hide_wp_version'          => true,
		'hide_php_version'         => true,
		'custom_login_url_enabled' => true,
		'custom_login_slug'        => 'secure-login',
		'custom_login_logo'        => true,
		'hotlink_protection'       => true,
		'disable_pingbacks'        => true,
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->registerHooks();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function registerHooks(): void {
		add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAssets' ) );
		add_action( 'admin_notices', array( $this, 'displayNotices' ) );
	}

	/**
	 * Add settings page to WordPress admin menu.
	 *
	 * @return void
	 */
	public function addSettingsPage(): void {
		add_options_page(
			__( 'Coreline Settings', 'coreline' ),
			__( 'Coreline', 'coreline' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'renderSettingsPage' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function registerSettings(): void {
		register_setting(
			self::PAGE_SLUG,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitizeSettings' ),
				'default'           => self::DEFAULTS,
			)
		);

		// General Settings Section.
		add_settings_section(
			'coreline_general',
			__( 'Security & Performance Features', 'coreline' ),
			array( $this, 'renderGeneralSection' ),
			self::PAGE_SLUG
		);

		// Disable Emojis.
		add_settings_field(
			'disable_emojis',
			__( 'Disable Emojis', 'coreline' ),
			array( $this, 'renderCheckboxField' ),
			self::PAGE_SLUG,
			'coreline_general',
			array(
				'name'  => 'disable_emojis',
				'label' => __( 'Remove WordPress emoji scripts to improve performance', 'coreline' ),
			)
		);

		// Hide WordPress Version.
		add_settings_field(
			'hide_wp_version',
			__( 'Hide WordPress Version', 'coreline' ),
			array( $this, 'renderCheckboxField' ),
			self::PAGE_SLUG,
			'coreline_general',
			array(
				'name'  => 'hide_wp_version',
				'label' => __( 'Remove WordPress version from HTML and RSS feeds', 'coreline' ),
			)
		);

		// Hide PHP Version.
		add_settings_field(
			'hide_php_version',
			__( 'Hide PHP Version', 'coreline' ),
			array( $this, 'renderCheckboxField' ),
			self::PAGE_SLUG,
			'coreline_general',
			array(
				'name'  => 'hide_php_version',
				'label' => __( 'Remove PHP version from HTTP headers', 'coreline' ),
			)
		);

		// Custom Login URL Section.
		add_settings_section(
			'coreline_login',
			__( 'Custom Login URL', 'coreline' ),
			array( $this, 'renderLoginSection' ),
			self::PAGE_SLUG
		);

		// Enable Custom Login URL.
		add_settings_field(
			'custom_login_url_enabled',
			__( 'Enable Custom Login URL', 'coreline' ),
			array( $this, 'renderCheckboxField' ),
			self::PAGE_SLUG,
			'coreline_login',
			array(
				'name'  => 'custom_login_url_enabled',
				'label' => __( 'Change wp-login.php to a custom URL', 'coreline' ),
			)
		);

		// Custom Login Slug.
		add_settings_field(
			'custom_login_slug',
			__( 'Login Slug', 'coreline' ),
			array( $this, 'renderTextField' ),
			self::PAGE_SLUG,
			'coreline_login',
			array(
				'name'        => 'custom_login_slug',
				'placeholder' => 'secure-login',
				'description' => sprintf(
					/* translators: %s: Custom login URL */
					__( 'Your login URL: %s', 'coreline' ),
					'<code id="coreline-login-url">' . esc_url( home_url( 'secure-login' ) ) . '</code>'
				),
			)
		);

		// Custom Login Logo.
		add_settings_field(
			'custom_login_logo',
			__( 'Custom Login Logo', 'coreline' ),
			array( $this, 'renderCheckboxField' ),
			self::PAGE_SLUG,
			'coreline_login',
			array(
				'name'  => 'custom_login_logo',
				'label' => __( 'Replace WordPress logo with your site logo or title', 'coreline' ),
			)
		);

		// Protection Features Section.
		add_settings_section(
			'coreline_protection',
			__( 'Protection Features', 'coreline' ),
			'__return_empty_string',
			self::PAGE_SLUG
		);

		// Hotlink Protection.
		add_settings_field(
			'hotlink_protection',
			__( 'Hotlink Protection', 'coreline' ),
			array( $this, 'renderCheckboxField' ),
			self::PAGE_SLUG,
			'coreline_protection',
			array(
				'name'  => 'hotlink_protection',
				'label' => __( 'Prevent other sites from hotlinking your images', 'coreline' ),
			)
		);

		// Disable Pingbacks.
		add_settings_field(
			'disable_pingbacks',
			__( 'Disable Pingbacks', 'coreline' ),
			array( $this, 'renderCheckboxField' ),
			self::PAGE_SLUG,
			'coreline_protection',
			array(
				'name'  => 'disable_pingbacks',
				'label' => __( 'Disable XML-RPC pingbacks and trackbacks', 'coreline' ),
			)
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized data.
	 */
	public function sanitizeSettings( array $input ): array {
		$sanitized = array();

		// Checkboxes.
		$checkboxes = array(
			'disable_emojis',
			'hide_wp_version',
			'hide_php_version',
			'custom_login_url_enabled',
			'custom_login_logo',
			'hotlink_protection',
			'disable_pingbacks',
		);

		foreach ( $checkboxes as $checkbox ) {
			$sanitized[ $checkbox ] = ! empty( $input[ $checkbox ] );
		}

		// Custom login slug.
		if ( isset( $input['custom_login_slug'] ) ) {
			$slug = sanitize_title_with_dashes( $input['custom_login_slug'] );

			// Validate slug.
			if ( empty( $slug ) || $this->isReservedSlug( $slug ) ) {
				add_settings_error(
					self::OPTION_NAME,
					'invalid_slug',
					__( 'Invalid login slug. Please use a different slug.', 'coreline' ),
					'error'
				);
				$slug = self::DEFAULTS['custom_login_slug'];
			}

			$sanitized['custom_login_slug'] = $slug;
		}

		return $sanitized;
	}

	/**
	 * Check if slug is reserved.
	 *
	 * @param string $slug Slug to check.
	 * @return bool
	 */
	private function isReservedSlug( string $slug ): bool {
		$reserved = array(
			'wp-admin',
			'wp-content',
			'wp-includes',
			'wp-login',
			'admin',
			'login',
			'xmlrpc',
			'wp-cron',
		);

		return in_array( $slug, $reserved, true );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueueAssets( string $hook ): void {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		// Enqueue admin CSS.
		wp_enqueue_style(
			'coreline-admin',
			CORELINE_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			CORELINE_VERSION
		);

		// Enqueue admin JS.
		wp_enqueue_script(
			'coreline-admin',
			CORELINE_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			CORELINE_VERSION,
			true
		);

		// Pass data to JS.
		wp_localize_script(
			'coreline-admin',
			'corelineAdmin',
			array(
				'homeUrl'     => home_url( '/' ),
				'defaultSlug' => self::DEFAULTS['custom_login_slug'],
			)
		);
	}

	/**
	 * Display admin notices.
	 *
	 * @return void
	 */
	public function displayNotices(): void {
		$screen = get_current_screen();

		if ( null === $screen || 'settings_page_' . self::PAGE_SLUG !== $screen->id ) {
			return;
		}

		// Show warning about custom login URL.
		$settings = $this->getSettings();

		if ( $settings['custom_login_url_enabled'] ) {
			$login_url = home_url( $settings['custom_login_slug'] );
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Important:', 'coreline' ); ?></strong>
					<?php
					printf(
						/* translators: %s: Custom login URL */
						esc_html__( 'Your login URL has been changed to %s. Please bookmark this URL!', 'coreline' ),
						'<code>' . esc_html( $login_url ) . '</code>'
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'If you forget this URL, you can recover it from the database or by deactivating the plugin.', 'coreline' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render general section description.
	 *
	 * @return void
	 */
	public function renderGeneralSection(): void {
		?>
		<p><?php esc_html_e( 'Enable or disable performance and security features.', 'coreline' ); ?></p>
		<?php
	}

	/**
	 * Render login section description.
	 *
	 * @return void
	 */
	public function renderLoginSection(): void {
		?>
		<p>
			<?php esc_html_e( 'Customize your WordPress login URL to prevent automated brute-force attacks.', 'coreline' ); ?>
		</p>
		<?php
	}

	/**
	 * Render checkbox field.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function renderCheckboxField( array $args ): void {
		$settings = $this->getSettings();
		$name     = $args['name'];
		$checked  = ! empty( $settings[ $name ] );
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( self::OPTION_NAME . '[' . $name . ']' ); ?>"
				value="1"
				<?php checked( $checked ); ?>
			/>
			<?php echo esc_html( $args['label'] ); ?>
		</label>
		<?php
	}

	/**
	 * Render text field.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function renderTextField( array $args ): void {
		$settings    = $this->getSettings();
		$name        = $args['name'];
		$value       = $settings[ $name ] ?? '';
		$placeholder = $args['placeholder'] ?? '';
		?>
		<input
			type="text"
			name="<?php echo esc_attr( self::OPTION_NAME . '[' . $name . ']' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="regular-text"
			id="coreline-<?php echo esc_attr( $name ); ?>"
		/>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function renderSettingsPage(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'coreline' ) );
		}
		?>
		<div class="wrap" id="coreline-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE_SLUG );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'coreline' ) );
				?>
			</form>

			<div class="coreline-info-boxes">
				<div class="coreline-info-box">
					<h3><?php esc_html_e( 'Need Help?', 'coreline' ); ?></h3>
					<ul>
						<li>
							<a href="<?php echo esc_url( CORELINE_PLUGIN_DIR . 'README.md' ); ?>" target="_blank">
								<?php esc_html_e( 'Documentation', 'coreline' ); ?>
							</a>
						</li>
						<li>
							<a href="https://github.com/kishanjasani/coreline/issues" target="_blank">
								<?php esc_html_e( 'Report an Issue', 'coreline' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get current settings.
	 *
	 * @return array
	 */
	public function getSettings(): array {
		$settings = get_option( self::OPTION_NAME, self::DEFAULTS );

		// Ensure all defaults are present.
		return wp_parse_args( $settings, self::DEFAULTS );
	}

	/**
	 * Get a specific setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $value Default value.
	 * @return mixed
	 */
	public static function get( string $key, $value = null ) {
		$settings = get_option( self::OPTION_NAME, self::DEFAULTS );

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		return $value ?? self::DEFAULTS[ $key ] ?? null;
	}
}
