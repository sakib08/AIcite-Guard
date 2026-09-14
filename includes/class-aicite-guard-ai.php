<?php
/**
 * Optional Bring-Your-Own-Key AI helper.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin OpenAI-compatible client with a free monthly cap.
 */
class Aicite_Guard_Ai {

	/**
	 * Remaining free generations this month.
	 *
	 * @return int
	 */
	public static function remaining() {
		$usage = self::usage();
		return max( 0, AICITE_GUARD_FREE_AI_LIMIT - (int) $usage['count'] );
	}

	/**
	 * Current month usage bucket.
	 *
	 * @return array{month:string,count:int}
	 */
	public static function usage() {
		$month = gmdate( 'Y-m' );
		$usage = get_option( 'aicite_guard_ai_usage', array() );
		if ( ! is_array( $usage ) || ( $usage['month'] ?? '' ) !== $month ) {
			$usage = array(
				'month' => $month,
				'count' => 0,
			);
			update_option( 'aicite_guard_ai_usage', $usage, false );
		}

		return $usage;
	}

	/**
	 * Increment usage after a successful call.
	 *
	 * @return void
	 */
	public static function bump_usage() {
		$usage          = self::usage();
		$usage['count'] = (int) $usage['count'] + 1;
		update_option( 'aicite_guard_ai_usage', $usage, false );
	}

	/**
	 * Whether a paid/key-backed call is allowed.
	 *
	 * @return true|WP_Error
	 */
	public static function can_generate() {
		if ( ! Aicite_Guard_Settings::has_ai_key() ) {
			return new WP_Error( 'aicite_guard_no_key', __( 'Add an OpenAI API key in Settings to use AI generation.', 'aicite-guard' ) );
		}

		if ( self::remaining() < 1 ) {
			return new WP_Error(
				'aicite_guard_limit',
				__( 'The free plan includes 20 AI generations per month. Upgrade to Pro for unlimited generations.', 'aicite-guard' )
			);
		}

		return true;
	}

	/**
	 * Improve a text snippet (llms polish or alt text).
	 *
	 * @param string $prompt User/system instruction.
	 * @param string $input  Source text.
	 * @param string $image  Optional image URL for vision.
	 * @return string|WP_Error
	 */
	public static function complete( $prompt, $input, $image = '' ) {
		$allowed = self::can_generate();
		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}

		$key = Aicite_Guard_Settings::get_ai_key();
		$body = array(
			'model'       => 'gpt-4o-mini',
			'temperature' => 0.3,
			'max_tokens'  => 400,
		);

		if ( $image ) {
			$body['messages'] = array(
				array(
					'role'    => 'user',
					'content' => array(
						array(
							'type' => 'text',
							'text' => $prompt . "\n\n" . $input,
						),
						array(
							'type'      => 'image_url',
							'image_url' => array( 'url' => $image ),
						),
					),
				),
			);
		} else {
			$body['messages'] = array(
				array(
					'role'    => 'system',
					'content' => $prompt,
				),
				array(
					'role'    => 'user',
					'content' => $input,
				),
			);
		}

		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions', // phpcs:ignore PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Optional BYOK OpenAI endpoint; no WordPress AI Client dependency required for free tier.
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 || ! is_array( $data ) ) {
			$message = isset( $data['error']['message'] ) ? (string) $data['error']['message'] : __( 'The AI provider returned an error.', 'aicite-guard' );
			return new WP_Error( 'aicite_guard_ai_http', $message );
		}

		$text = '';
		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			$text = trim( (string) $data['choices'][0]['message']['content'] );
		}

		if ( '' === $text ) {
			return new WP_Error( 'aicite_guard_ai_empty', __( 'The AI provider returned an empty response.', 'aicite-guard' ) );
		}

		self::bump_usage();

		return $text;
	}
}
