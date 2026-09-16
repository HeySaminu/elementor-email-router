<?php
/**
 * Native Elementor controls and runtime routing.
 *
 * @package ElementorEmailRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class EER_Native_Router {
	const ENABLE_CONTROL   = 'eer_conditional_routing';
	const ROUTES_CONTROL   = 'eer_conditional_routes';
	const NO_MATCH_CONTROL = 'eer_no_match';
	const SKIP_SETTING     = '_eer_skip_actions';
	const DEFAULT_FROM     = 'web@sterling.ng';

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
		$email_label = '_2' === $suffix ? __( 'Email 2', 'elementor-email-router' ) : __( 'Email', 'elementor-email-router' );

		$widget->update_control(
			'email_from' . $suffix,
			array(
				'default'     => self::DEFAULT_FROM,
				'placeholder' => self::DEFAULT_FROM,
			)
		);

		$widget->add_control(
			'eer_conditional_heading' . $suffix,
			array(
				'label'     => __( 'Conditional Email Routing', 'elementor-email-router' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$widget->add_control(
			$enable_id,
			array(
				'label'        => sprintf( __( 'Enable routing for %s', 'elementor-email-router' ), $email_label ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'elementor-email-router' ),
				'label_off'    => __( 'No', 'elementor-email-router' ),
				'return_value' => 'yes',
				'default'      => '',
				'render_type'  => 'none',
			)
		);

		$widget->add_control(
			'eer_conditional_help' . $suffix,
			array(
				'type'      => \Elementor\Controls_Manager::RAW_HTML,
				'raw'       => __( 'Add one or more email variants. The first enabled condition that matches is used. Leave a variant email field blank to inherit the normal Email settings above. Field shortcodes such as <code>[field id="email"]</code> are supported.', 'elementor-email-router' ),
				'condition' => array( $enable_id => 'yes' ),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'enabled',
			array(
				'label'        => __( 'Enabled', 'elementor-email-router' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'elementor-email-router' ),
				'label_off'    => __( 'No', 'elementor-email-router' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$repeater->add_control(
			'route_label',
			array(
				'label'       => __( 'Variant label', 'elementor-email-router' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'e.g. Complaint notification', 'elementor-email-router' ),
			)
		);

		$repeater->add_control(
			'condition_heading',
			array(
				'label'     => __( 'Condition', 'elementor-email-router' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'field_id',
			array(
				'label'       => __( 'Form field ID', 'elementor-email-router' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'field_bef6e91',
				'description' => __( 'Enter the Elementor field ID without form_fields[] or form-field-.', 'elementor-email-router' ),
			)
		);

		$repeater->add_control(
			'operator',
			array(
				'label'   => __( 'Operator', 'elementor-email-router' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'equals',
				'options' => array(
					'equals'       => __( 'Equals', 'elementor-email-router' ),
					'not_equals'   => __( 'Does not equal', 'elementor-email-router' ),
					'contains'     => __( 'Contains', 'elementor-email-router' ),
					'not_contains' => __( 'Does not contain', 'elementor-email-router' ),
					'starts_with'  => __( 'Starts with', 'elementor-email-router' ),
					'ends_with'    => __( 'Ends with', 'elementor-email-router' ),
					'empty'        => __( 'Is empty', 'elementor-email-router' ),
					'not_empty'    => __( 'Is not empty', 'elementor-email-router' ),
				),
			)
		);

		$repeater->add_control(
			'match_value',
			array(
				'label'       => __( 'Value', 'elementor-email-router' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'Ignored for the Is empty and Is not empty operators.', 'elementor-email-router' ),
			)
		);

		$repeater->add_control(
			'case_sensitive',
			array(
				'label'        => __( 'Case-sensitive match', 'elementor-email-router' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'elementor-email-router' ),
				'label_off'    => __( 'No', 'elementor-email-router' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$repeater->add_control(
			'email_heading',
			array(
				'label'     => __( 'Email variant', 'elementor-email-router' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		self::add_repeater_text_control( $repeater, 'email_to', __( 'To', 'elementor-email-router' ), '[field id="email"]' );
		self::add_repeater_text_control( $repeater, 'email_subject', __( 'Subject', 'elementor-email-router' ), __( 'Inherit the normal email subject', 'elementor-email-router' ) );

		$repeater->add_control(
			'email_content',
			array(
				'label'       => __( 'Message', 'elementor-email-router' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 10,
				'label_block' => true,
				'placeholder' => '[all-fields]',
			)
		);

		self::add_repeater_text_control( $repeater, 'email_from', __( 'From Email', 'elementor-email-router' ), self::DEFAULT_FROM );
		self::add_repeater_text_control( $repeater, 'email_from_name', __( 'From Name', 'elementor-email-router' ), __( 'Inherit the normal From Name', 'elementor-email-router' ) );

		$repeater->add_control(
			'email_reply_to',
			array(
				'label'       => __( 'Reply-To', 'elementor-email-router' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '_2' === $suffix ? '[field id="email"]' : 'email',
				'description' => '_2' === $suffix
					? __( 'Enter an email address or a field shortcode.', 'elementor-email-router' )
					: __( 'Enter the ID of an email form field.', 'elementor-email-router' ),
			)
		);

		self::add_repeater_text_control( $repeater, 'email_to_cc', __( 'Cc', 'elementor-email-router' ) );
		self::add_repeater_text_control( $repeater, 'email_to_bcc', __( 'Bcc', 'elementor-email-router' ) );

		$repeater->add_control(
			'email_content_type',
			array(
				'label'   => __( 'Send As', 'elementor-email-router' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'inherit',
				'options' => array(
					'inherit' => __( 'Inherit normal setting', 'elementor-email-router' ),
					'html'    => __( 'HTML', 'elementor-email-router' ),
					'plain'   => __( 'Plain', 'elementor-email-router' ),
				),
			)
		);

		$widget->add_control(
			$routes_id,
			array(
				'label'         => __( 'Conditional email variants', 'elementor-email-router' ),
				'type'          => \Elementor\Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'title_field'   => '{{{ route_label }}}',
				'prevent_empty' => false,
				'button_text'   => __( 'Add conditional email', 'elementor-email-router' ),
				'condition'     => array( $enable_id => 'yes' ),
				'render_type'   => 'none',
			)
		);

		$widget->add_control(
			$no_match_id,
			array(
				'label'       => __( 'When no condition matches', 'elementor-email-router' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'default',
				'options'     => array(
					'default' => sprintf( __( 'Send the normal %s', 'elementor-email-router' ), $email_label ),
					'skip'    => sprintf( __( 'Do not send %s', 'elementor-email-router' ), $email_label ),
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

		$settings = $record->get( 'form_settings' );
		$fields   = $record->get( 'fields' );
		$skipped  = array();

		foreach ( self::email_actions() as $action ) {
			if ( ! self::is_enabled_for_action( $settings, $action['name'] ) ) {
				continue;
			}

			$from_key = 'email_from' . $action['suffix'];

			if ( empty( $settings[ $from_key ] ) ) {
				$settings[ $from_key ] = self::DEFAULT_FROM;
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

					$settings = self::apply_route( $settings, $route, $action['suffix'] );
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

		$settings = $record->get( 'form_settings' );
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
		$field_id = self::normalize_field_id( $route['field_id'] ?? '' );

		if ( '' === $field_id ) {
			return false;
		}

		$actual   = self::field_value( $fields, $field_id );
		$expected = trim( (string) ( $route['match_value'] ?? '' ) );
		$operator = $route['operator'] ?? 'equals';

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
				return '' === $expected || false === strpos( $actual, $expected );
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
	 * @return array
	 */
	public static function apply_route( $settings, $route, $suffix = '' ) {
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
			if ( isset( $route[ $key ] ) && '' !== trim( (string) $route[ $key ] ) ) {
				$settings[ $key . $suffix ] = $route[ $key ];
			}
		}

		if ( isset( $route['email_content_type'] ) && in_array( $route['email_content_type'], array( 'html', 'plain' ), true ) ) {
			$settings[ 'email_content_type' . $suffix ] = $route['email_content_type'];
		}

		return $settings;
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
		$field_id = trim( (string) $field_id );

		if ( preg_match( '/form_fields\\[([^\\]]+)\\]/', $field_id, $matches ) ) {
			$field_id = $matches[1];
		}

		if ( 0 === strpos( $field_id, 'form-field-' ) ) {
			$field_id = substr( $field_id, strlen( 'form-field-' ) );
		}

		return trim( $field_id );
	}

	/**
	 * Get a scalar submitted field value.
	 *
	 * @param array  $fields Submitted fields.
	 * @param string $field_id Field ID.
	 * @return string
	 */
	private static function field_value( $fields, $field_id ) {
		$value = $fields[ $field_id ]['value'] ?? '';

		if ( is_array( $value ) ) {
			$value = implode( ', ', array_map( 'strval', $value ) );
		}

		return trim( (string) $value );
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
