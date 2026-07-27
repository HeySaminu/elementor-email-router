<?php
/**
 * Plugin Name: Elementor Email Router
 * Description: Routes Elementor Pro form autoresponse emails to different HTML templates based on submitted field values.
 * Version: 1.0.0
 * Author: Saminu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'elementor_pro/forms/record/actions_before', 'sterling_elementor_email_router_route_email_two', 20, 2 );

function sterling_elementor_email_router_route_email_two( $record, $ajax_handler ) {
	$settings = $record->get( 'form_settings' );

	if ( empty( $settings['form_name'] ) || 'Sterling Kids & Teens Saving Boost' !== $settings['form_name'] ) {
		return $record;
	}

	$fields = $record->get( 'fields' );

	$campaign_category = '';

	if ( isset( $fields['field_bef6e91']['value'] ) ) {
		$campaign_category = trim( (string) $fields['field_bef6e91']['value'] );
	}

	$template_file = '';

	if ( 'Branded School Gift' === $campaign_category ) {
		$template_file = __DIR__ . '/email1.html';
	} elseif ( '5% Cash Reward' === $campaign_category ) {
		$template_file = __DIR__ . '/email2.html';
	}

	if ( ! $template_file || ! is_readable( $template_file ) ) {
		return $record;
	}

	$email_content = file_get_contents( $template_file );

	if ( false === $email_content || '' === trim( $email_content ) ) {
		return $record;
	}

	$settings['email_to_2'] = '[field id="parent_Gaurdian_email"]';
	$settings['email_subject_2'] = "We've Received Your Kids & Teens Savings Boost Opt-In Submission";
	$settings['email_content_2'] = $email_content;
	$settings['email_content_type_2'] = 'html';

	$record->set( 'form_settings', $settings );

	return $record;
}
