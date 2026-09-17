<?php
/**
 * Optional AI helper via the WordPress AI Client.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provider-agnostic AI calls with a free monthly cap.
 */
class Sitepulse_Guard_By_Plugin_Pros_Ai {

	/**
	 * Remaining free generations this month.
	 *
	 * @return int
	 */
	public static function remaining() {
		$usage = self::usage();
		return max( 0, SPG_BY_PPROS_FREE_AI_LIMIT - (int) $usage['count'] );
	}

	/**
	 * Current month usage bucket.
	 *
	 * @return array{month:string,count:int}
	 */
	public static function usage() {
		$month = gmdate( 'Y-m' );
		$usage = get_option( 'spg_by_ppros_ai_usage', array() );
		if ( ! is_array( $usage ) || ( $usage['month'] ?? '' ) !== $month ) {
			$usage = array(
				'month' => $month,
				'count' => 0,
			);
			update_option( 'spg_by_ppros_ai_usage', $usage, false );
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
		update_option( 'spg_by_ppros_ai_usage', $usage, false );
	}

	/**
	 * Whether the WordPress AI Client can generate text on this site.
	 *
	 * @return bool
	 */
	public static function is_available() {
		if ( ! Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'ai.enabled', true ) ) {
			return false;
		}

		if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
			return false;
		}

		if ( function_exists( 'wp_supports_ai' ) && ! wp_supports_ai() ) {
			return false;
		}

		$builder = wp_ai_client_prompt( 'availability-check' );
		if ( ! is_object( $builder ) || ! method_exists( $builder, 'is_supported_for_text_generation' ) ) {
			return false;
		}

		return (bool) $builder->is_supported_for_text_generation();
	}

	/**
	 * Human-readable AI Client status for settings screens.
	 *
	 * @return string
	 */
	public static function status_message() {
		if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
			return __( 'This site is on a WordPress version without the AI Client. Alt text uses local, rule-based suggestions. On WordPress 7.0 or later, optional AI alt text uses the provider configured under Settings → Connectors.', 'spg-by-ppros' );
		}

		if ( function_exists( 'wp_supports_ai' ) && ! wp_supports_ai() ) {
			return __( 'AI features are disabled in this environment. Alt text stays rule-based.', 'spg-by-ppros' );
		}

		if ( self::is_available() ) {
			return __( 'Using the WordPress AI Client and the provider configured under Settings → Connectors. This plugin does not store an API key.', 'spg-by-ppros' );
		}

		return __( 'No AI provider is ready yet. Add one under Settings → Connectors (for example the official OpenAI, Anthropic, or Google provider plugin). Until then, alt text uses local, rule-based suggestions.', 'spg-by-ppros' );
	}

	/**
	 * Whether an AI-backed call is allowed.
	 *
	 * @return true|WP_Error
	 */
	public static function can_generate() {
		if ( ! self::is_available() ) {
			return new WP_Error(
				'spg_by_ppros_unavailable',
				__( 'AI generation needs WordPress 7.0+ and a provider under Settings → Connectors. Rule-based suggestions still work.', 'spg-by-ppros' )
			);
		}

		if ( self::remaining() < 1 ) {
			return new WP_Error(
				'spg_by_ppros_limit',
				__( 'The free plan includes 20 AI generations per month. Upgrade to Pro for unlimited generations.', 'spg-by-ppros' )
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

		$result = self::generate_text( $prompt, $input, $image );
		if ( is_wp_error( $result ) && '' !== $image ) {
			$result = self::generate_text( $prompt, $input, '' );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$text = is_string( $result ) ? trim( $result ) : '';
		if ( '' === $text ) {
			return new WP_Error( 'spg_by_ppros_ai_empty', __( 'The AI provider returned an empty response.', 'spg-by-ppros' ) );
		}

		self::bump_usage();

		return $text;
	}

	/**
	 * Send a prompt through the WordPress AI Client.
	 *
	 * @param string $prompt System instruction.
	 * @param string $input  User text.
	 * @param string $image  Optional public image URL.
	 * @return string|WP_Error
	 */
	private static function generate_text( $prompt, $input, $image = '' ) {
		$builder = wp_ai_client_prompt( $input )
			->using_system_instruction( $prompt )
			->using_temperature( 0.3 )
			->using_max_tokens( 400 );

		if ( '' !== $image ) {
			$builder = $builder->with_file( $image );
		}

		return $builder->generate_text();
	}
}
