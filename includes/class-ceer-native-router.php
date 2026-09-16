<?php
/**
 * Native Elementor controls and runtime routing.
 *
 * @package ElementorEmailRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CEER_Native_Router {
	const ENABLE_CONTROL   = 'ceer_conditional_routing';
	const ROUTES_CONTROL   = 'ceer_conditional_routes';
	const NO_MATCH_CONTROL = 'ceer_no_match';
	const SKIP_SETTING     = '_ceer_skip_actions';

	/**
	 * Register Elementor editor and form submission hooks.
	 */
	public static function boot() {
		add_action( 'elementor/element/form/section_email/before_section_end', array( __CLASS__, 'register_email_controls' ), 10, 2 );
		add_action( 'elementor/element/form/section_email_2/before_section_end', array( __CLASS__, 'register_email_2_controls' ), 10, 2 );
		add_filter( 'elementor_pro/forms/record/actions_before', array( __CLASS__, 'route_form_emails' ), 10, 2 );
		add_filter( 'elementor_pro/forms/submit_actions', array( __CLASS__, 'filter_submit_actions' ), 10, 3 );
	}

	/**
	 * Add routing controls to Elementor's Email action.
	 *
	 * @param object $widget Elementor Form widget.
	 */
	public static function register_email_controls( $widget ) {
		self::register_controls( $widget, '' );
	}

	/**
	 * Add routing controls to Elementor's Email 2 action.
	 *
	 * @param object $widget Elementor Form widget.
	 */
	public static function register_email_2_controls( $widget ) {
		self::register_controls( $widget, '_2' );
	}

	/**
	 * Register a route repeater inside an Elementor email action.
	 *
	 * @param object $widget Elementor Form widget.
	 * @param string $suffix Elementor Email 2 control suffix.
	 */
	private static function register_controls( $widget, $suffix ) {
		if ( ! class_exists( '\\Elementor\\Controls_Manager' ) || ! class_exists( '\\Elementor\\Repeater' ) ) {
			return;
		}

		$enable_id   = self::ENABLE_CONTROL . $suffix;
		$routes_id   = self::ROUTES_CONTROL . $suffix;
		$no_match_id = self::NO_MATCH_CONTROL . $suffix;
		$email_label = '_2' === $suffix ? __( 'Email 2', 'conditional-email-router-for-elementor' ) : __( 'Email', 'conditional-email-router-for-elementor' );

		$widget->add_control(
			'ceer_conditional_heading' . $suffix,
			array(
				'label'     => __( 'Conditional Email Routing', 'conditional-email-router-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$widget->add_control(
			$enable_id,
			array(
				/* translators: %s: Elementor email action name. */
				'label'        => sprintf( __( 'Enable routing for %s', 'conditional-email-router-for-elementor' ), $email_label ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'conditional-email-router-for-elementor' ),
				'label_off'    => __( 'No', 'conditional-email-router-for-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'render_type'  => 'none',
			)
		);

		$widget->add_control(
			'ceer_conditional_help' . $suffix,
			array(
				'type'      => \Elementor\Controls_Manager::RAW_HTML,
				'raw'       => wp_kses_post( __( 'Add one or more email variants. The first enabled condition that matches is used. Leave a variant email field blank to inherit the normal Email settings above. Field shortcodes such as <code>[field id="email"]</code> are supported.', 'conditional-email-router-for-elementor' ) ),
				'condition' => array( $enable_id => 'yes' ),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'enabled',
			array(
				'label'        => __( 'Enabled', 'conditional-email-router-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'conditional-email-router-for-elementor' ),
				'label_off'    => __( 'No', 'conditional-email-router-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$repeater->add_control(
			'route_label',
			array(
				'label'       => __( 'Variant label', 'conditional-email-router-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'e.g. Complaint notification', 'conditional-email-router-for-elementor' ),
			)
		);

		$repeater->add_control(
			'condition_heading',
			array(
				'label'     => __( 'Condition', 'conditional-email-router-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'field_id',
			array(
				'label'       => __( 'Form field ID', 'conditional-email-router-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'field_bef6e91',
				'description' => __( 'Enter the Elementor field ID without form_fields[] or form-field-.', 'conditional-email-router-for-elementor' ),
			)
		);

		$repeater->add_control(
			'operator',
			array(
				'label'   => __( 'Operator', 'conditional-email-router-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'equals',
				'options' => array(
					'equals'       => __( 'Equals', 'conditional-email-router-for-elementor' ),
					'not_equals'   => __( 'Does not equal', 'conditional-email-router-for-elementor' ),
					'contains'     => __( 'Contains', 'conditional-email-router-for-elementor' ),
					'not_contains' => __( 'Does not contain', 'conditional-email-router-for-elementor' ),
					'starts_with'  => __( 'Starts with', 'conditional-email-router-for-elementor' ),
					'ends_with'    => __( 'Ends with', 'conditional-email-router-for-elementor' ),
					'empty'        => __( 'Is empty', 'conditional-email-router-for-elementor' ),
					'not_empty'    => __( 'Is not empty', 'conditional-email-router-for-elementor' ),
				),
			)
		);

		$repeater->add_control(
			'match_value',
			array(
				'label'       => __( 'Value', 'conditional-email-router-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'Ignored for the Is empty and Is not empty operators.', 'conditional-email-router-for-elementor' ),
			)
		);

		$repeater->add_control(
			'case_sensitive',
			array(
				'label'        => __( 'Case-sensitive match', 'conditional-email-router-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'conditional-email-router-for-elementor' ),
				'label_off'    => __( 'No', 'conditional-email-router-for-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$repeater->add_control(
			'email_heading',
			array(
				'label'     => __( 'Email variant', 'conditional-email-router-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		self::add_repeater_text_control( $repeater, 'email_to', __( 'To', 'conditional-email-router-for-elementor' ), '[field id="email"]' );
		self::add_repeater_text_control( $repeater, 'email_subject', __( 'Subject', 'conditional-email-router-for-elementor' ), __( 'Inherit the normal email subject', 'conditional-email-router-for-elementor' ) );

		$repeater->add_control(
			'email_content',
			array(
				'label'       => __( 'Message', 'conditional-email-router-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 10,
				'label_block' => true,
				'placeholder' => '[all-fields]',
			)
		);

		self::add_repeater_text_control(
			$repeater,
			'email_from',
			__( 'From Email', 'conditional-email-router-for-elementor' ),
			__( 'Inherit the normal From Email', 'conditional-email-router-for-elementor' )
		);
		self::add_repeater_text_control( $repeater, 'email_from_name', __( 'From Name', 'conditional-email-router-for-elementor' ), __( 'Inherit the normal From Name', 'conditional-email-router-for-elementor' ) );

		$repeater->add_control(
			'email_reply_to',
			array(
				'label'       => __( 'Reply-To', 'conditional-email-router-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '_2' === $suffix ? '[field id="email"]' : 'email',
				'description' => '_2' === $suffix
					? __( 'Enter an email address or a field shortcode.', 'conditional-email-router-for-elementor' )
					: __( 'Enter the ID of an email form field.', 'conditional-email-router-for-elementor' ),
			)
		);

		self::add_repeater_text_control( $repeater, 'email_to_cc', __( 'Cc', 'conditional-email-router-for-elementor' ) );
		self::add_repeater_text_control( $repeater, 'email_to_bcc', __( 'Bcc', 'conditional-email-router-for-elementor' ) );

		$repeater->add_control(
			'email_content_type',
			array(
				'label'   => __( 'Send As', 'conditional-email-router-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'inherit',
				'options' => array(
					'inherit' => __( 'Inherit normal setting', 'conditional-email-router-for-elementor' ),
					'html'    => __( 'HTML', 'conditional-email-router-for-elementor' ),
					'plain'   => __( 'Plain', 'conditional-email-router-for-elementor' ),
				),
			)
		);

		$widget->add_control(
			$routes_id,
			array(
				'label'         => __( 'Conditional email variants', 'conditional-email-router-for-elementor' ),
				'type'          => \Elementor\Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'title_field'   => '{{{ route_label }}}',
				'prevent_empty' => false,
				'button_text'   => __( 'Add conditional email', 'conditional-email-router-for-elementor' ),
				'condition'     => array( $enable_id => 'yes' ),
				'render_type'   => 'none',
			)
		);

		$widget->add_control(
			$no_match_id,
			array(
				'label'       => __( 'When no condition matches', 'conditional-email-router-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'default',
				'options'     => array(
					/* translators: %s: Elementor email action name. */
					'default' => sprintf( __( 'Send the normal %s', 'conditional-email-router-for-elementor' ), $email_label ),
					/* translators: %s: Elementor email action name. */
					'skip'    => sprintf( __( 'Do not send %s', 'conditional-email-router-for-elementor' ), $email_label ),
				),
				'condition'   => array( $enable_id => 'yes' ),
				'render_type' => 'none',
			)
		);
	}

	/**
	 * Add a reusable text field to the route repeater.
	 *
	 * @param \Elementor\Repeater $repeater Repeater instance.
	 * @param string              $id Control ID.
	 * @param string              $label Control label.
	 * @param string              $placeholder Optional placeholder.
	 */
	private static function add_repeater_text_control( $repeater, $id, $label, $placeholder = '' ) {
		$repeater->add_control(
			$id,
			array(
				'label'       => $label,
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => $placeholder,
			)
		);
	}

	/**
	 * Apply the first matching route for Email and Email 2.
	 *
	 * @param object $record Elementor form record.
	 * @param object $ajax_handler Elementor AJAX handler.
	 * @return object
	 */
	public static function route_form_emails( $record, $ajax_handler ) {
		unset( $ajax_handler );

		if ( ! is_object( $record ) || ! method_exists( $record, 'get' ) || ! method_exists( $record, 'set' ) ) {
			return $record;
		}

		$settings = $record->get( 'form_settings' );
		$fields   = $record->get( 'fields' );
		$skipped  = array();

		if ( ! is_array( $settings ) || ! is_array( $fields ) ) {
			return $record;
		}

		foreach ( self::email_actions() as $action ) {
			if ( ! self::is_enabled_for_action( $settings, $action['name'] ) ) {
				continue;
			}

			$routes  = $settings[ self::ROUTES_CONTROL . $action['suffix'] ] ?? array();
			$matched = false;

			if ( is_array( $routes ) ) {
				foreach ( $routes as $route ) {
					if ( ! is_array( $route ) || 'yes' !== ( $route['enabled'] ?? 'yes' ) ) {
						continue;
					}

					if ( ! self::route_matches( $fields, $route ) ) {
						continue;
					}

					$settings = self::apply_route( $settings, $route, $action['suffix'], $record );
					$matched  = true;
					break;
				}
			}

			if ( ! $matched && 'skip' === ( $settings[ self::NO_MATCH_CONTROL . $action['suffix'] ] ?? 'default' ) ) {
				$skipped[] = $action['name'];
			}
		}

		if ( $skipped ) {
			$settings[ self::SKIP_SETTING ] = array_values( array_unique( $skipped ) );
		} else {
			unset( $settings[ self::SKIP_SETTING ] );
		}

		$record->set( 'form_settings', $settings );

		return $record;
	}

	/**
	 * Remove routed email actions when no condition matched and skip was selected.
	 *
	 * @param array  $actions Submit action names.
	 * @param object $record Elementor form record.
	 * @param object $ajax_handler Elementor AJAX handler.
	 * @return array
	 */
	public static function filter_submit_actions( $actions, $record, $ajax_handler ) {
		unset( $ajax_handler );

		if ( ! is_array( $actions ) || ! is_object( $record ) || ! method_exists( $record, 'get' ) ) {
			return $actions;
		}

		$settings = $record->get( 'form_settings' );

		if ( ! is_array( $settings ) ) {
			return $actions;
		}

		$skipped  = isset( $settings[ self::SKIP_SETTING ] ) && is_array( $settings[ self::SKIP_SETTING ] )
			? $settings[ self::SKIP_SETTING ]
			: array();

		if ( ! $skipped ) {
			return $actions;
		}

		return array_values( array_diff( $actions, $skipped ) );
	}

	/**
	 * Check whether native routing is enabled for an email action.
	 *
	 * @param array  $settings Form settings.
	 * @param string $action Email action name.
	 * @return bool
	 */
	public static function is_enabled_for_action( $settings, $action ) {
		if ( ! is_array( $settings ) || ! is_string( $action ) ) {
			return false;
		}

		$suffix = in_array( $action, array( 'email2', 'email_2' ), true ) ? '_2' : '';

		return 'yes' === ( $settings[ self::ENABLE_CONTROL . $suffix ] ?? '' );
	}

	/**
	 * Compare a submitted form field with a route condition.
	 *
	 * @param array $fields Submitted Elementor fields.
	 * @param array $route Route settings.
	 * @return bool
	 */
	public static function route_matches( $fields, $route ) {
		if ( ! is_array( $fields ) || ! is_array( $route ) ) {
			return false;
		}

		$field_id = self::normalize_field_id( $route['field_id'] ?? '' );

		if ( '' === $field_id ) {
			return false;
		}

		$actual   = self::field_value( $fields, $field_id );
		$expected = trim( (string) ( $route['match_value'] ?? '' ) );
		$operators = array( 'equals', 'not_equals', 'contains', 'not_contains', 'starts_with', 'ends_with', 'empty', 'not_empty' );
		$operator  = isset( $route['operator'] ) && in_array( $route['operator'], $operators, true ) ? $route['operator'] : 'equals';

		if ( 'empty' === $operator ) {
			return '' === $actual;
		}

		if ( 'not_empty' === $operator ) {
			return '' !== $actual;
		}

		if ( 'yes' !== ( $route['case_sensitive'] ?? '' ) ) {
			$actual   = self::lowercase( $actual );
			$expected = self::lowercase( $expected );
		}

		switch ( $operator ) {
			case 'not_equals':
				return $actual !== $expected;
			case 'contains':
				return '' !== $expected && false !== strpos( $actual, $expected );
			case 'not_contains':
				return '' !== $expected && false === strpos( $actual, $expected );
			case 'starts_with':
				return '' !== $expected && 0 === strpos( $actual, $expected );
			case 'ends_with':
				return '' !== $expected && substr( $actual, -strlen( $expected ) ) === $expected;
			case 'equals':
			default:
				return $actual === $expected;
		}
	}

	/**
	 * Apply non-empty route email values over Elementor's native email settings.
	 *
	 * @param array  $settings Form settings.
	 * @param array  $route Matched route.
	 * @param string $suffix Email 2 suffix.
	 * @param object $record Elementor form record.
	 * @return array
	 */
	public static function apply_route( $settings, $route, $suffix = '', $record = null ) {
		if ( ! is_array( $settings ) || ! is_array( $route ) ) {
			return is_array( $settings ) ? $settings : array();
		}

		$setting_keys = array(
			'email_to',
			'email_subject',
			'email_content',
			'email_from',
			'email_from_name',
			'email_reply_to',
			'email_to_cc',
			'email_to_bcc',
		);

		foreach ( $setting_keys as $key ) {
			if ( ! isset( $route[ $key ] ) || ! is_scalar( $route[ $key ] ) ) {
				continue;
			}

			$value = trim( (string) $route[ $key ] );

			if ( '' === $value ) {
				continue;
			}

			$value = self::prepare_email_setting( $key, $value, $suffix, $record );

			if ( null !== $value && '' !== $value ) {
				$settings[ $key . $suffix ] = $value;
			}
		}

		if ( isset( $route['email_content_type'] ) && in_array( $route['email_content_type'], array( 'html', 'plain' ), true ) ) {
			$settings[ 'email_content_type' . $suffix ] = $route['email_content_type'];
		}

		return $settings;
	}

	/**
	 * Resolve shortcodes and sanitize values used in email headers.
	 *
	 * @param string $key Email setting key.
	 * @param string $value Configured value.
	 * @param string $suffix Email 2 suffix.
	 * @param object $record Elementor form record.
	 * @return string|null
	 */
	private static function prepare_email_setting( $key, $value, $suffix, $record ) {
		if ( 'email_content' === $key ) {
			return $value;
		}

		if ( 'email_reply_to' === $key && '' === $suffix ) {
			$field_id = self::normalize_field_id( $value );
			$fields   = is_object( $record ) && method_exists( $record, 'get' ) ? $record->get( 'fields' ) : array();

			return $field_id && is_array( $fields ) && isset( $fields[ $field_id ] ) ? $field_id : null;
		}

		if ( is_object( $record ) && method_exists( $record, 'replace_setting_shortcodes' ) ) {
			$value = $record->replace_setting_shortcodes( $value );
		}

		switch ( $key ) {
			case 'email_subject':
			case 'email_from_name':
				return self::sanitize_header_text( $value );
			case 'email_from':
			case 'email_reply_to':
				return self::sanitize_single_address( $value );
			case 'email_to':
			case 'email_to_cc':
			case 'email_to_bcc':
				return self::sanitize_address_list( $value );
			default:
				return null;
		}
	}

	/**
	 * Remove line breaks, tags, and control characters from a mail header value.
	 *
	 * @param string $value Header value.
	 * @return string
	 */
	private static function sanitize_header_text( $value ) {
		$value = wp_strip_all_tags( (string) $value, true );
		$value = preg_replace( '/[\r\n\t]+/', ' ', $value );
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value );

		return trim( (string) $value );
	}

	/**
	 * Validate a single email address.
	 *
	 * @param string $value Address value.
	 * @return string|null
	 */
	private static function sanitize_single_address( $value ) {
		$value = self::sanitize_header_text( $value );

		if ( preg_match( '/<([^<>]+)>/', $value, $matches ) ) {
			$value = $matches[1];
		}

		$value = trim( $value );

		return is_email( $value ) ? sanitize_email( $value ) : null;
	}

	/**
	 * Validate a comma-separated email address list while preserving safe names.
	 *
	 * @param string $value Address list.
	 * @return string|null
	 */
	private static function sanitize_address_list( $value ) {
		$value     = self::sanitize_header_text( $value );
		$addresses = array();

		foreach ( explode( ',', $value ) as $address ) {
			$address = trim( $address );

			if ( '' === $address ) {
				continue;
			}

			$name = '';

			if ( preg_match( '/^([^<>]*)<([^<>]+)>$/', $address, $matches ) ) {
				$name    = self::sanitize_header_text( $matches[1] );
				$address = trim( $matches[2] );
			}

			if ( ! is_email( $address ) ) {
				continue;
			}

			$email = sanitize_email( $address );

			$addresses[] = '' !== $name ? $name . ' <' . $email . '>' : $email;
		}

		return $addresses ? implode( ', ', $addresses ) : null;
	}

	/**
	 * Return the action metadata for Email and Email 2.
	 *
	 * @return array
	 */
	private static function email_actions() {
		return array(
			array(
				'name'   => 'email',
				'suffix' => '',
			),
			array(
				'name'   => 'email2',
				'suffix' => '_2',
			),
		);
	}

	/**
	 * Normalize common Elementor field ID formats.
	 *
	 * @param string $field_id Field ID.
	 * @return string
	 */
	private static function normalize_field_id( $field_id ) {
		if ( ! is_scalar( $field_id ) ) {
			return '';
		}

		$field_id = trim( (string) $field_id );

		if ( preg_match( '/form_fields\\[([^\\]]+)\\]/', $field_id, $matches ) ) {
			$field_id = $matches[1];
		}

		if ( 0 === strpos( $field_id, 'form-field-' ) ) {
			$field_id = substr( $field_id, strlen( 'form-field-' ) );
		}

		$field_id = trim( $field_id );

		return strlen( $field_id ) <= 128 && preg_match( '/^[A-Za-z0-9_-]+$/', $field_id ) ? $field_id : '';
	}

	/**
	 * Get a scalar submitted field value.
	 *
	 * @param array  $fields Submitted fields.
	 * @param string $field_id Field ID.
	 * @return string
	 */
	private static function field_value( $fields, $field_id ) {
		if ( ! is_array( $fields ) || ! isset( $fields[ $field_id ] ) || ! is_array( $fields[ $field_id ] ) ) {
			return '';
		}

		$value = $fields[ $field_id ]['value'] ?? '';

		if ( is_array( $value ) ) {
			$value = implode( ', ', array_map( 'strval', $value ) );
		}

		return is_scalar( $value ) ? trim( (string) $value ) : '';
	}

	/**
	 * Lowercase a value with multibyte support when available.
	 *
	 * @param string $value Value to lowercase.
	 * @return string
	 */
	private static function lowercase( $value ) {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
	}
}
