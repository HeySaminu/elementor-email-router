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
	$blank_route = array(
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
		<div class="eer-page-head">
			<div>
				<h1>Elementor Email Router</h1>
				<p>Route Elementor Pro form emails based on a submitted field value. The first matching enabled route wins.</p>
			</div>
			<button type="button" class="button button-primary eer-add-route">Add route</button>
		</div>

		<?php if ( isset( $_GET['updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Email routes saved.</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="eer_save_routes">
			<?php wp_nonce_field( 'eer_save_routes' ); ?>

			<div class="eer-route-list">
				<?php foreach ( $routes as $index => $route ) : ?>
					<?php eer_render_route_card( $route, (string) $index, $template_files, 0 === $index ); ?>
				<?php endforeach; ?>
			</div>

			<template id="eer-route-template">
				<?php eer_render_route_card( $blank_route, '__INDEX__', $template_files, true ); ?>
			</template>

			<?php submit_button( 'Save Email Routes' ); ?>
		</form>
	</div>

	<style>
		.eer-page-head {
			align-items: flex-start;
			display: flex;
			gap: 16px;
			justify-content: space-between;
			margin-bottom: 16px;
		}

		.eer-page-head h1 {
			margin-bottom: 4px;
		}

		.eer-route-card {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 8px;
			margin: 12px 0;
			overflow: hidden;
		}

		.eer-route-card[open] {
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.eer-route-summary {
			align-items: center;
			cursor: pointer;
			display: grid;
			gap: 12px;
			grid-template-columns: minmax(220px, 1.2fr) repeat(4, minmax(120px, 1fr)) auto;
			padding: 14px 16px;
		}

		.eer-route-summary::-webkit-details-marker {
			display: none;
		}

		.eer-route-title {
			font-size: 15px;
			font-weight: 700;
		}

		.eer-route-meta {
			color: #646970;
			font-size: 12px;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		.eer-pill {
			background: #f0f0f1;
			border-radius: 999px;
			display: inline-block;
			font-size: 12px;
			line-height: 1.4;
			padding: 4px 9px;
			white-space: nowrap;
		}

		.eer-pill.is-enabled {
			background: #e8f5e9;
			color: #0a6b20;
		}

		.eer-pill.is-disabled {
			background: #f6f7f7;
			color: #646970;
		}

		.eer-route-actions {
			display: flex;
			gap: 8px;
			justify-content: flex-end;
		}

		.eer-route-body {
			border-top: 1px solid #dcdcde;
			padding: 18px;
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

		.eer-grid .eer-checkbox-field input {
			width: auto;
		}

		.eer-template {
			margin-top: 16px;
		}

		@media (max-width: 1280px) {
			.eer-route-summary {
				grid-template-columns: 1fr 1fr 1fr auto;
			}

			.eer-route-meta:nth-of-type(4),
			.eer-route-meta:nth-of-type(5) {
				display: none;
			}
		}

		@media (max-width: 1100px) {
			.eer-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}

		@media (max-width: 782px) {
			.eer-page-head,
			.eer-route-summary {
				display: block;
			}

			.eer-page-head .button,
			.eer-route-actions {
				margin-top: 10px;
			}

			.eer-route-meta {
				margin-top: 6px;
			}

			.eer-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>

	<script>
		(function () {
			const list = document.querySelector('.eer-route-list');
			const template = document.getElementById('eer-route-template');
			const addButton = document.querySelector('.eer-add-route');

			if (!list || !template || !addButton) return;

			let nextIndex = list.querySelectorAll('.eer-route-card').length;

			function stopSummaryToggle(event) {
				if (event.target.closest('button, input, select, textarea, label')) {
					event.stopPropagation();
				}
			}

			function refreshSummary(card) {
				const label = card.querySelector('[data-eer-field="label"]')?.value || 'New route';
				const formName = card.querySelector('[data-eer-field="form_name"]')?.value || 'No form selected';
				const matchValue = card.querySelector('[data-eer-field="match_value"]')?.value || 'No match value';
				const emailAction = card.querySelector('[data-eer-field="email_action"]')?.value === 'email' ? 'Email' : 'Email 2';
				const templateFile = card.querySelector('[data-eer-field="template_file"]')?.value || 'Custom HTML';
				const enabled = card.querySelector('[data-eer-field="enabled"]')?.checked;

				card.querySelector('[data-eer-summary="label"]').textContent = label;
				card.querySelector('[data-eer-summary="form"]').textContent = formName;
				card.querySelector('[data-eer-summary="match"]').textContent = matchValue;
				card.querySelector('[data-eer-summary="email"]').textContent = emailAction;
				card.querySelector('[data-eer-summary="template"]').textContent = templateFile;

				const status = card.querySelector('[data-eer-summary="status"]');
				status.textContent = enabled ? 'Enabled' : 'Disabled';
				status.classList.toggle('is-enabled', enabled);
				status.classList.toggle('is-disabled', !enabled);
			}

			function bindCard(card) {
				card.addEventListener('click', stopSummaryToggle);

				card.querySelectorAll('input, select, textarea').forEach(function (field) {
					field.addEventListener('input', function () {
						refreshSummary(card);
					});
					field.addEventListener('change', function () {
						refreshSummary(card);
					});
				});

				card.querySelector('.eer-duplicate-route')?.addEventListener('click', function () {
					const clone = card.cloneNode(true);
					const index = nextIndex++;

					clone.open = true;
					clone.querySelector('[data-eer-field="id"]').value = '';

					clone.querySelectorAll('[name]').forEach(function (field) {
						field.name = field.name.replace(/routes\[[^\]]+\]/, 'routes[' + index + ']');
					});

					card.after(clone);
					bindCard(clone);
					refreshSummary(clone);
				});

				card.querySelector('.eer-remove-route')?.addEventListener('click', function () {
					card.remove();
				});

				refreshSummary(card);
			}

			addButton.addEventListener('click', function () {
				const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
				const wrapper = document.createElement('div');
				wrapper.innerHTML = html.trim();

				const card = wrapper.firstElementChild;
				card.open = true;
				list.appendChild(card);
				bindCard(card);
				card.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});

			list.querySelectorAll('.eer-route-card').forEach(bindCard);
		})();
	</script>
	<?php
}

function eer_render_route_card( $route, $index, $template_files, $open = false ) {
	?>
	<details class="eer-route-card" <?php echo $open ? 'open' : ''; ?>>
		<summary class="eer-route-summary">
			<span>
				<span class="eer-route-title" data-eer-summary="label"><?php echo esc_html( $route['label'] ?: 'New route' ); ?></span>
			</span>
			<span class="eer-route-meta" data-eer-summary="form"><?php echo esc_html( $route['form_name'] ?: 'No form selected' ); ?></span>
			<span class="eer-route-meta">When value is <strong data-eer-summary="match"><?php echo esc_html( $route['match_value'] ?: 'No match value' ); ?></strong></span>
			<span class="eer-route-meta" data-eer-summary="email"><?php echo 'email' === $route['email_action'] ? 'Email' : 'Email 2'; ?></span>
			<span class="eer-route-meta" data-eer-summary="template"><?php echo esc_html( $route['template_file'] ?: 'Custom HTML' ); ?></span>
			<span class="eer-route-actions">
				<span class="eer-pill <?php echo $route['enabled'] ? 'is-enabled' : 'is-disabled'; ?>" data-eer-summary="status"><?php echo $route['enabled'] ? 'Enabled' : 'Disabled'; ?></span>
				<button type="button" class="button eer-duplicate-route">Duplicate</button>
				<button type="button" class="button eer-remove-route">Remove</button>
			</span>
		</summary>

		<div class="eer-route-body">
			<input type="hidden" data-eer-field="id" name="routes[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $route['id'] ); ?>">

			<div class="eer-grid">
				<label class="eer-checkbox-field">
					Status
					<span>
						<input type="checkbox" data-eer-field="enabled" name="routes[<?php echo esc_attr( $index ); ?>][enabled]" value="1" <?php checked( $route['enabled'] ); ?>>
						Enabled
					</span>
				</label>

				<label>
					Route label
					<input type="text" data-eer-field="label" name="routes[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $route['label'] ); ?>" placeholder="KidsBoost - Branded School Gift">
				</label>

				<label>
					Elementor form name
					<input type="text" data-eer-field="form_name" name="routes[<?php echo esc_attr( $index ); ?>][form_name]" value="<?php echo esc_attr( $route['form_name'] ); ?>" placeholder="Sterling Kids & Teens Saving Boost">
				</label>

				<label>
					Field ID to check
					<input type="text" data-eer-field="field_id" name="routes[<?php echo esc_attr( $index ); ?>][field_id]" value="<?php echo esc_attr( $route['field_id'] ); ?>" placeholder="field_bef6e91">
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
					<input type="text" data-eer-field="match_value" name="routes[<?php echo esc_attr( $index ); ?>][match_value]" value="<?php echo esc_attr( $route['match_value'] ); ?>" placeholder="Branded School Gift">
				</label>

				<label>
					Email action to update
					<select data-eer-field="email_action" name="routes[<?php echo esc_attr( $index ); ?>][email_action]">
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
					<select data-eer-field="template_file" name="routes[<?php echo esc_attr( $index ); ?>][template_file]">
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
	</details>
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
