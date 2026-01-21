<?php
/**
 * Plugin Name: Coreline
 * Plugin URI: https://github.com/kishanjasani/coreline
 * Description: Essential hardening and cleanup for every WordPress site.
 * Version: 0.1.2
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Tested up to: 6.9
 * Author: Kishan Jasani
 * Author URI: https://kishanjasani.in
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coreline
 *
 * @package Coreline
 */

declare(strict_types=1);

namespace Coreline;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'CORELINE_VERSION', '0.1.2' );
define( 'CORELINE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CORELINE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CORELINE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require Composer autoloader.
if ( file_exists( CORELINE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once CORELINE_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function coreline_init(): void {
	$plugin = new Plugin();
	$plugin->run();
}

// Initialize plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\coreline_init' );
