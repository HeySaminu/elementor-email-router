<?php
/**
 * Plugin Name: Elementor Email Router
 * Description: Routes Elementor Pro form emails to different HTML templates based on submitted field values.
 * Version: 1.1.0
 * Author: Saminu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EER_OPTION_ROUTES = 'elementor_email_router_routes';
const EER_ADMIN_SLUG    = 'elementor-email-router';

register_activation_hook( __FILE__, 'eer_seed_default_routes' );

add_action( 'admin_menu', 'eer_register_admin_page' );
add_action( 'admin_post_eer_save_routes', 'eer_save_routes' );
add_filter( 'elementor_pro/forms/record/actions_before', 'eer_route_elementor_email', 20, 2 );

function eer_seed_default_routes() {
	if ( false !== get_option( EER_OPTION_ROUTES, false ) ) {
		return;
	}

	update_option( EER_OPTION_ROUTES, eer_default_routes(), false );
}

function eer_default_routes() {
	$subject = "We've Received Your Kids & Teens Savings Boost Opt-In Submission";

	return array(
		array(
			'id'            => 'kidsboost-branded-school-gift',
			'enabled'       => true,
			'label'         => 'KidsBoost - Branded School Gift',
			'form_name'     => 'Sterling Kids & Teens Saving Boost',
			'field_id'      => 'field_bef6e91',
			'match_type'    => 'exact',
			'match_value'   => 'Branded School Gift',
			'email_action'  => 'email_2',
			'email_to'      => '[field id="parent_Gaurdian_email"]',
			'email_subject' => $subject,
			'template_file' => 'email1.html',
			'email_content' => '',
		),
		array(
			'id'            => 'kidsboost-cash-reward',
			'enabled'       => true,
			'label'         => 'KidsBoost - 5% Cash Reward',
			'form_name'     => 'Sterling Kids & Teens Saving Boost',
			'field_id'      => 'field_bef6e91',
			'match_type'    => 'exact',
			'match_value'   => '5% Cash Reward',
			'email_action'  => 'email_2',
			'email_to'      => '[field id="parent_Gaurdian_email"]',
			'email_subject' => $subject,
			'template_file' => 'email2.html',
			'email_content' => '',
		),
	);
}

function eer_get_routes() {
	$routes = get_option( EER_OPTION_ROUTES, false );

	if ( false === $routes || ! is_array( $routes ) ) {
		return eer_default_routes();
	}

	return array_values( array_filter( array_map( 'eer_normalize_route', $routes ) ) );
}

function eer_normalize_route( $route ) {
	if ( ! is_array( $route ) ) {
		return null;
	}

	return array(
		'id'            => sanitize_key( $route['id'] ?? wp_generate_uuid4() ),
		'enabled'       => ! empty( $route['enabled'] ),
		'label'         => sanitize_text_field( $route['label'] ?? '' ),
		'form_name'     => sanitize_text_field( $route['form_name'] ?? '' ),
		'field_id'      => sanitize_text_field( $route['field_id'] ?? '' ),
		'match_type'    => in_array( $route['match_type'] ?? 'exact', array( 'exact', 'contains' ), true ) ? $route['match_type'] : 'exact',
		'match_value'   => sanitize_text_field( $route['match_value'] ?? '' ),
		'email_action'  => in_array( $route['email_action'] ?? 'email_2', array( 'email', 'email_2' ), true ) ? $route['email_action'] : 'email_2',
		'email_to'      => sanitize_text_field( $route['email_to'] ?? '' ),
		'email_subject' => sanitize_text_field( $route['email_subject'] ?? '' ),
		'template_file' => sanitize_file_name( $route['template_file'] ?? '' ),
		'email_content' => (string) ( $route['email_content'] ?? '' ),
	);
}

function eer_register_admin_page() {
	add_options_page(
		'Elementor Email Router',
		'Elementor Email Router',
		'manage_options',
		EER_ADMIN_SLUG,
		'eer_render_admin_page'
	);
}

function eer_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$routes = eer_get_routes();
	$routes[] = array(
		'id'            => '',
		'enabled'       => false,
		'label'         => '',
		'form_name'     => '',
		'field_id'      => '',
		'match_type'    => 'exact',
		'match_value'   => '',
		'email_action'  => 'email_2',
		'email_to'      => '',
		'email_subject' => '',
		'template_file' => '',
		'email_content' => '',
	);

	$template_files = eer_get_template_files();
	?>
	<div class="wrap eer-wrap">
		<h1>Elementor Email Router</h1>
		<p>Route Elementor Pro form emails based on a submitted field value. The first matching enabled route wins.</p>

		<?php if ( isset( $_GET['updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Email routes saved.</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="eer_save_routes">
			<?php wp_nonce_field( 'eer_save_routes' ); ?>

			<?php foreach ( $routes as $index => $route ) : ?>
				<div class="eer-route-card">
					<div class="eer-route-head">
						<h2><?php echo $route['label'] ? esc_html( $route['label'] ) : 'New route'; ?></h2>
						<label>
							<input type="checkbox" name="routes[<?php echo esc_attr( $index ); ?>][enabled]" value="1" <?php checked( $route['enabled'] ); ?>>
							Enabled
						</label>
					</div>

					<input type="hidden" name="routes[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $route['id'] ); ?>">

					<div class="eer-grid">
						<label>
							Route label
							<input type="text" name="routes[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $route['label'] ); ?>" placeholder="KidsBoost - Branded School Gift">
						</label>

						<label>
							Elementor form name
							<input type="text" name="routes[<?php echo esc_attr( $index ); ?>][form_name]" value="<?php echo esc_attr( $route['form_name'] ); ?>" placeholder="Sterling Kids & Teens Saving Boost">
						</label>

						<label>
							Field ID to check
							<input type="text" name="routes[<?php echo esc_attr( $index ); ?>][field_id]" value="<?php echo esc_attr( $route['field_id'] ); ?>" placeholder="field_bef6e91">
						</label>

						<label>
							Match type
							<select name="routes[<?php echo esc_attr( $index ); ?>][match_type]">
								<option value="exact" <?php selected( $route['match_type'], 'exact' ); ?>>Exact match</option>
								<option value="contains" <?php selected( $route['match_type'], 'contains' ); ?>>Contains</option>
							</select>
						</label>

						<label>
							Field value to match
							<input type="text" name="routes[<?php echo esc_attr( $index ); ?>][match_value]" value="<?php echo esc_attr( $route['match_value'] ); ?>" placeholder="Branded School Gift">
						</label>

						<label>
							Email action to update
							<select name="routes[<?php echo esc_attr( $index ); ?>][email_action]">
								<option value="email" <?php selected( $route['email_action'], 'email' ); ?>>Email</option>
								<option value="email_2" <?php selected( $route['email_action'], 'email_2' ); ?>>Email 2</option>
							</select>
						</label>

						<label>
							Recipient
							<input type="text" name="routes[<?php echo esc_attr( $index ); ?>][email_to]" value="<?php echo esc_attr( $route['email_to'] ); ?>" placeholder='[field id="email"]'>
						</label>

						<label>
							Subject
							<input type="text" name="routes[<?php echo esc_attr( $index ); ?>][email_subject]" value="<?php echo esc_attr( $route['email_subject'] ); ?>">
						</label>

						<label>
							Template file
							<select name="routes[<?php echo esc_attr( $index ); ?>][template_file]">
								<option value="">Use custom HTML below</option>
								<?php foreach ( $template_files as $file ) : ?>
									<option value="<?php echo esc_attr( $file ); ?>" <?php selected( $route['template_file'], $file ); ?>><?php echo esc_html( $file ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>

					<label class="eer-template">
						Custom HTML template
						<textarea name="routes[<?php echo esc_attr( $index ); ?>][email_content]" rows="10" placeholder="Paste an HTML email template here. Leave empty when using a template file."><?php echo esc_textarea( $route['email_content'] ); ?></textarea>
					</label>
				</div>
			<?php endforeach; ?>

			<?php submit_button( 'Save Email Routes' ); ?>
		</form>
	</div>

	<style>
		.eer-wrap .eer-route-card {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 8px;
			margin: 20px 0;
			padding: 20px;
		}

		.eer-route-head {
			align-items: center;
			display: flex;
			justify-content: space-between;
			gap: 16px;
			margin-bottom: 16px;
		}

		.eer-route-head h2 {
			margin: 0;
		}

		.eer-grid {
			display: grid;
			gap: 16px;
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}

		.eer-grid label,
		.eer-template {
			display: flex;
			flex-direction: column;
			font-weight: 600;
			gap: 6px;
		}

		.eer-grid input,
		.eer-grid select,
		.eer-template textarea {
			width: 100%;
		}

		.eer-template {
			margin-top: 16px;
		}

		@media (max-width: 1100px) {
			.eer-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}

		@media (max-width: 782px) {
			.eer-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>
	<?php
}

function eer_save_routes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage email routes.', 'elementor-email-router' ) );
	}

	check_admin_referer( 'eer_save_routes' );

	$raw_routes = isset( $_POST['routes'] ) && is_array( $_POST['routes'] ) ? wp_unslash( $_POST['routes'] ) : array();
	$routes     = array();

	foreach ( $raw_routes as $raw_route ) {
		if ( ! is_array( $raw_route ) ) {
			continue;
		}

		$route = eer_normalize_route(
			array(
				'id'            => $raw_route['id'] ?? '',
				'enabled'       => ! empty( $raw_route['enabled'] ),
				'label'         => $raw_route['label'] ?? '',
				'form_name'     => $raw_route['form_name'] ?? '',
				'field_id'      => $raw_route['field_id'] ?? '',
				'match_type'    => $raw_route['match_type'] ?? 'exact',
				'match_value'   => $raw_route['match_value'] ?? '',
				'email_action'  => $raw_route['email_action'] ?? 'email_2',
				'email_to'      => $raw_route['email_to'] ?? '',
				'email_subject' => $raw_route['email_subject'] ?? '',
				'template_file' => $raw_route['template_file'] ?? '',
				'email_content' => current_user_can( 'unfiltered_html' ) ? ( $raw_route['email_content'] ?? '' ) : wp_kses_post( $raw_route['email_content'] ?? '' ),
			)
		);

		if ( ! $route || '' === $route['form_name'] || '' === $route['field_id'] || '' === $route['match_value'] ) {
			continue;
		}

		if ( '' === $route['id'] ) {
			$route['id'] = sanitize_key( $route['form_name'] . '-' . $route['field_id'] . '-' . $route['match_value'] . '-' . wp_generate_uuid4() );
		}

		$routes[] = $route;
	}

	update_option( EER_OPTION_ROUTES, $routes, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => EER_ADMIN_SLUG,
				'updated' => '1',
			),
			admin_url( 'options-general.php' )
		)
	);
	exit;
}

function eer_get_template_files() {
	$files = glob( plugin_dir_path( __FILE__ ) . '*.html' );

	if ( ! $files ) {
		return array();
	}

	return array_map( 'basename', $files );
}

function eer_route_elementor_email( $record, $ajax_handler ) {
	$settings  = $record->get( 'form_settings' );
	$form_name = trim( (string) ( $settings['form_name'] ?? '' ) );

	if ( '' === $form_name ) {
		return $record;
	}

	$fields = $record->get( 'fields' );

	foreach ( eer_get_routes() as $route ) {
		if ( empty( $route['enabled'] ) || $form_name !== $route['form_name'] ) {
			continue;
		}

		$field_value = eer_get_field_value( $fields, $route['field_id'] );

		if ( ! eer_route_matches( $field_value, $route ) ) {
			continue;
		}

		$email_content = eer_get_email_content( $route );

		if ( '' === trim( $email_content ) ) {
			continue;
		}

		$settings = eer_apply_route_to_form_settings( $settings, $route, $email_content );
		$record->set( 'form_settings', $settings );

		return $record;
	}

	return $record;
}

function eer_get_field_value( $fields, $field_id ) {
	if ( isset( $fields[ $field_id ]['value'] ) ) {
		$value = $fields[ $field_id ]['value'];
	} else {
		$value = '';
	}

	if ( is_array( $value ) ) {
		$value = implode( ', ', array_map( 'strval', $value ) );
	}

	return trim( (string) $value );
}

function eer_route_matches( $field_value, $route ) {
	$expected = trim( (string) $route['match_value'] );

	if ( '' === $expected ) {
		return false;
	}

	if ( 'contains' === $route['match_type'] ) {
		return false !== stripos( $field_value, $expected );
	}

	return $field_value === $expected;
}

function eer_get_email_content( $route ) {
	if ( '' !== trim( $route['email_content'] ) ) {
		return $route['email_content'];
	}

	if ( '' === $route['template_file'] ) {
		return '';
	}

	$template_path = realpath( plugin_dir_path( __FILE__ ) . $route['template_file'] );
	$plugin_path   = realpath( plugin_dir_path( __FILE__ ) );

	if ( ! $template_path || ! $plugin_path || 0 !== strpos( $template_path, $plugin_path ) || ! is_readable( $template_path ) ) {
		return '';
	}

	$content = file_get_contents( $template_path );

	return false === $content ? '' : $content;
}

function eer_apply_route_to_form_settings( $settings, $route, $email_content ) {
	$suffix = 'email_2' === $route['email_action'] ? '_2' : '';

	if ( '' !== $route['email_to'] ) {
		$settings[ 'email_to' . $suffix ] = $route['email_to'];
	}

	if ( '' !== $route['email_subject'] ) {
		$settings[ 'email_subject' . $suffix ] = $route['email_subject'];
	}

	$settings[ 'email_content' . $suffix ]      = $email_content;
	$settings[ 'email_content_type' . $suffix ] = 'html';

	return $settings;
}
