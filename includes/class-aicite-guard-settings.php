<?php
/**
 * Settings storage and sanitization.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single-option settings helper.
 */
class Aicite_Guard_Settings {

	const OPTION_KEY = 'aicite_guard_options';
	const AI_KEY     = 'aicite_guard_ai_key';

	/**
	 * Default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'wizard_complete' => false,
			'ai_visibility'   => array(
				'enabled'          => true,
				'post_types'       => array( 'post', 'page' ),
				'max_items'        => 50,
				'include_excerpts' => true,
				'auto_update'      => true,
				'serve_virtual'    => true,
			),
			'crawler'         => array(
				'allow_answer_engines' => true,
				'block_training'       => true,
				'robots_integration'   => true,
			),
			'schema'          => array(
				'enabled' => true,
			),
			'accessibility'   => array(
				'enabled'         => true,
				'widget_enabled'  => true,
				'widget_position' => 'bottom-right',
				'statement_page'  => 0,
			),
			'health'          => array(
				'enabled' => true,
			),
			'ai'              => array(
				'provider' => 'none',
			),
		);
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get() {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return self::merge_deep( self::defaults(), $stored );
	}

	/**
	 * Get a nested setting by dot path.
	 *
	 * @param string $path    Dot-notated path.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get_path( $path, $default = null ) {
		$value = self::get();
		foreach ( explode( '.', $path ) as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}
			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Persist a full or partial settings array.
	 *
	 * @param array<string, mixed> $settings Settings to merge and save.
	 * @return array<string, mixed>
	 */
	public static function update( $settings ) {
		$merged = self::merge_deep( self::get(), is_array( $settings ) ? $settings : array() );
		$clean  = self::sanitize( $merged );
		update_option( self::OPTION_KEY, $clean, false );

		return $clean;
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array<string, mixed> $input Raw settings.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$out      = $defaults;

		$out['wizard_complete'] = ! empty( $input['wizard_complete'] );

		if ( isset( $input['ai_visibility'] ) && is_array( $input['ai_visibility'] ) ) {
			$ai = $input['ai_visibility'];
			$out['ai_visibility']['enabled']          = ! empty( $ai['enabled'] );
			$out['ai_visibility']['include_excerpts'] = ! empty( $ai['include_excerpts'] );
			$out['ai_visibility']['auto_update']      = ! empty( $ai['auto_update'] );
			$out['ai_visibility']['serve_virtual']    = ! empty( $ai['serve_virtual'] );
			$out['ai_visibility']['max_items']        = max( 5, min( 200, absint( $ai['max_items'] ?? 50 ) ) );
			$out['ai_visibility']['post_types']       = self::sanitize_post_types( $ai['post_types'] ?? array() );
		}

		if ( isset( $input['crawler'] ) && is_array( $input['crawler'] ) ) {
			$crawler = $input['crawler'];
			$out['crawler']['allow_answer_engines'] = ! empty( $crawler['allow_answer_engines'] );
			$out['crawler']['block_training']       = ! empty( $crawler['block_training'] );
			$out['crawler']['robots_integration']   = ! empty( $crawler['robots_integration'] );
		}

		if ( isset( $input['schema'] ) && is_array( $input['schema'] ) ) {
			$out['schema']['enabled'] = ! empty( $input['schema']['enabled'] );
		}

		if ( isset( $input['accessibility'] ) && is_array( $input['accessibility'] ) ) {
			$a11y = $input['accessibility'];
			$out['accessibility']['enabled']        = ! empty( $a11y['enabled'] );
			$out['accessibility']['widget_enabled'] = ! empty( $a11y['widget_enabled'] );
			$position = isset( $a11y['widget_position'] ) ? sanitize_key( $a11y['widget_position'] ) : 'bottom-right';
			$allowed  = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
			$out['accessibility']['widget_position'] = in_array( $position, $allowed, true ) ? $position : 'bottom-right';
			$out['accessibility']['statement_page']  = absint( $a11y['statement_page'] ?? 0 );
		}

		if ( isset( $input['health'] ) && is_array( $input['health'] ) ) {
			$out['health']['enabled'] = ! empty( $input['health']['enabled'] );
		}

		if ( isset( $input['ai'] ) && is_array( $input['ai'] ) ) {
			$provider = isset( $input['ai']['provider'] ) ? sanitize_key( $input['ai']['provider'] ) : 'none';
			$out['ai']['provider'] = in_array( $provider, array( 'none', 'openai' ), true ) ? $provider : 'none';
		}

		return $out;
	}

	/**
	 * Store the optional AI key separately (not autoloaded).
	 *
	 * @param string $key API key.
	 * @return void
	 */
	public static function set_ai_key( $key ) {
		$key = is_string( $key ) ? trim( $key ) : '';
		if ( '' === $key ) {
			delete_option( self::AI_KEY );
			return;
		}
		update_option( self::AI_KEY, sanitize_text_field( $key ), false );
	}

	/**
	 * Retrieve the stored AI key.
	 *
	 * @return string
	 */
	public static function get_ai_key() {
		$key = get_option( self::AI_KEY, '' );
		return is_string( $key ) ? $key : '';
	}

	/**
	 * Whether an API key is configured.
	 *
	 * @return bool
	 */
	public static function has_ai_key() {
		return '' !== self::get_ai_key() && 'none' !== self::get_path( 'ai.provider', 'none' );
	}

	/**
	 * Sanitize a list of public post types.
	 *
	 * @param mixed $types Raw types.
	 * @return string[]
	 */
	public static function sanitize_post_types( $types ) {
		if ( ! is_array( $types ) ) {
			$types = array( 'post', 'page' );
		}

		$public = get_post_types( array( 'public' => true ), 'names' );
		$clean  = array();

		foreach ( $types as $type ) {
			$type = sanitize_key( (string) $type );
			if ( isset( $public[ $type ] ) && 'attachment' !== $type ) {
				$clean[] = $type;
			}
		}

		return $clean ? array_values( array_unique( $clean ) ) : array( 'post', 'page' );
	}

	/**
	 * Recursively merge arrays (settings over defaults).
	 *
	 * @param array<string, mixed> $base  Defaults.
	 * @param array<string, mixed> $extra Overrides.
	 * @return array<string, mixed>
	 */
	private static function merge_deep( $base, $extra ) {
		foreach ( $extra as $key => $value ) {
			if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) && self::is_assoc( $base[ $key ] ) ) {
				$base[ $key ] = self::merge_deep( $base[ $key ], $value );
			} else {
				$base[ $key ] = $value;
			}
		}

		return $base;
	}

	/**
	 * Whether an array is associative.
	 *
	 * @param array<mixed> $array Array to test.
	 * @return bool
	 */
	private static function is_assoc( $array ) {
		if ( array() === $array ) {
			return false;
		}

		return array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}
}
