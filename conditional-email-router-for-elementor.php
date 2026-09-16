<?php
/**
 * Plugin Name: Conditional Email Router for Elementor
 * Plugin URI: https://github.com/HeySaminu/elementor-email-router
 * Description: Adds conditional email variants to Elementor Pro forms without creating separate form actions.
 * Version: 2.2.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: elementor
 * Author: Saminu
 * Author URI: https://github.com/HeySaminu
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: conditional-email-router-for-elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CEER_VERSION', '2.2.0' );

add_action( 'plugins_loaded', 'ceer_initialize_plugin', 20 );

/**
 * Start the router after Elementor Pro has loaded.
 */
function ceer_initialize_plugin() {
	if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) || ! class_exists( '\\ElementorPro\\Plugin' ) ) {
		add_action( 'admin_notices', 'ceer_missing_elementor_pro_notice' );
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ceer-native-router.php';

	CEER_Native_Router::boot();
}

/**
 * Explain why the plugin is inactive when Elementor Pro is unavailable.
 */
function ceer_missing_elementor_pro_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: plugin name, 2: Elementor Pro. */
		esc_html__( '%1$s requires %2$s to be installed and active before conditional email routing can be configured.', 'conditional-email-router-for-elementor' ),
		'<strong>' . esc_html__( 'Conditional Email Router for Elementor', 'conditional-email-router-for-elementor' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor Pro', 'conditional-email-router-for-elementor' ) . '</strong>'
	);

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		wp_kses_post( $message )
	);
}
