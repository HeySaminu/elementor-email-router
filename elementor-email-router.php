<?php
/**
 * Plugin Name: Elementor Email Router
 * Description: Adds conditional routing directly to Elementor Pro Form Email and Email 2 actions.
 * Version: 2.1.0
 * Author: Saminu
 * Text Domain: elementor-email-router
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EER_VERSION = '2.1.0';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-eer-native-router.php';

EER_Native_Router::boot();

add_action( 'plugins_loaded', 'eer_upgrade_plugin' );

/**
 * Remove the retired v1.x global routing data once after an upgrade.
 */
function eer_upgrade_plugin() {
	if ( EER_VERSION === get_option( 'elementor_email_router_version' ) ) {
		return;
	}

	delete_option( 'elementor_email_router_routes' );
	update_option( 'elementor_email_router_version', EER_VERSION, false );
}
