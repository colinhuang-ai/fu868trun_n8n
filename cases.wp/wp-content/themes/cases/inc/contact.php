<?php
/**
 * Contact form (slide-over panel): AJAX handler.
 * Stores every inquiry as a "cases_inquiry" post and e-mails the sales address.
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_nopriv_cases_contact', 'cases_handle_contact' );
add_action( 'wp_ajax_cases_contact', 'cases_handle_contact' );

function cases_handle_contact(): void {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cases_contact' ) ) {
		wp_send_json_error( array( 'message' => 'invalid_nonce' ), 403 );
	}

	// Honeypot: real users never fill this hidden field.
	if ( ! empty( $_POST['company_website'] ) ) {
		wp_send_json_success(); // Pretend success to bots.
	}

	// Simple rate limit: 5 submissions / hour / IP.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'cases_rl_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 5 ) {
		wp_send_json_error( array( 'message' => 'rate_limited' ), 429 );
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		wp_send_json_error( array( 'message' => 'invalid_fields' ), 422 );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'cases_inquiry',
			'post_status'  => 'private',
			/* translators: 1: sender name, 2: company */
			'post_title'   => sprintf( 'Inquiry from %1$s%2$s', $name, $company ? " ({$company})" : '' ),
			'post_content' => $message,
			'meta_input'   => array(
				'cases_name'    => $name,
				'cases_company' => $company,
				'cases_email'   => $email,
			),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'save_failed' ), 500 );
	}

	$to      = cases_opt( 'email' );
	$subject = sprintf( '[%s] Website inquiry from %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $name );
	$body    = "Name: {$name}\nCompany: {$company}\nEmail: {$email}\n\n{$message}\n";
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	// Inquiry is already saved; a mail failure must not lose the lead.
	wp_mail( $to, $subject, $body, $headers );

	wp_send_json_success();
}
