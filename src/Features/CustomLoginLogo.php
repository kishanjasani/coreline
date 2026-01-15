<?php
/**
 * Custom Login Logo Feature
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
 * Class CustomLoginLogo
 *
 * Replaces the default WordPress logo on the login page with a custom logo or site title.
 */
final class CustomLoginLogo extends AbstractFeature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'Custom Login Logo';
		$this->description = 'Replace the WordPress logo on the login page with your site logo or title';
		$this->settingsKey = 'custom_login_logo';

		parent::__construct();
	}

	/**
	 * Get translated feature name.
	 *
	 * @return string
	 */
	protected function getTranslatedName(): string {
		return __( 'Custom Login Logo', 'coreline' );
	}

	/**
	 * Get translated feature description.
	 *
	 * @return string
	 */
	protected function getTranslatedDescription(): string {
		return __( 'Replace the WordPress logo on the login page with your site logo or title', 'coreline' );
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	protected function registerHooks(): void {
		add_action( 'login_enqueue_scripts', array( $this, 'customizeLoginLogo' ) );
		add_filter( 'login_headerurl', array( $this, 'customizeLoginLogoUrl' ) );
		add_filter( 'login_headertext', array( $this, 'customizeLoginLogoTitle' ) );
	}

	/**
	 * Customize the login logo.
	 *
	 * This method checks if a custom logo is set. If yes, it displays the custom logo.
	 * If not, it displays the site title as text.
	 *
	 * @return void
	 */
	public function customizeLoginLogo(): void {
		$custom_logo_id = get_theme_mod( 'custom_logo' );

		if ( $custom_logo_id ) {
			// Use custom logo if set.
			$logo_image = wp_get_attachment_image_src( $custom_logo_id, 'full' );

			if ( $logo_image ) {
				?>
				<style type="text/css">
					#login h1 a, .login h1 a {
						background-image: url(<?php echo esc_url( $logo_image[0] ); ?>);
						background-size: contain;
						background-position: center center;
						background-repeat: no-repeat;
						width: 100%;
						max-width: 320px;
						height: 84px;
						padding: 0;
						margin-bottom: 20px;
						overflow: hidden;
					}
				</style>
				<?php
			}
		} else {
			// Use site title as text if no custom logo.
			$site_name = get_bloginfo( 'name' );
			?>
			<style type="text/css">
				#login h1 a, .login h1 a {
					background-image: none;
					width: auto;
					height: auto;
					text-indent: 0;
					font-size: 32px;
					font-weight: bold;
					line-height: 1.3;
					text-align: center;
					color: #3c434a;
					padding: 20px 0;
					margin-bottom: 20px;
				}
			</style>
			<?php
		}
	}

	/**
	 * Customize the login logo URL.
	 *
	 * Changes the link from wordpress.org to the site's home URL.
	 *
	 * @return string
	 */
	public function customizeLoginLogoUrl(): string {
		return home_url();
	}

	/**
	 * Customize the login logo title attribute.
	 *
	 * Changes the title from "Powered by WordPress" to the site name.
	 *
	 * @return string
	 */
	public function customizeLoginLogoTitle(): string {
		return get_bloginfo( 'name' );
	}
}
