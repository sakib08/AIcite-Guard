<?php
/**
 * Fired during plugin activation.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation routines.
 */
class Aicite_Guard_Activator {

	/**
	 * Create defaults, register rewrite rules, and flag the setup wizard.
	 *
	 * @return void
	 */
	public static function activate() {
		require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-settings.php';
		require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-llms.php';

		if ( false === get_option( 'aicite_guard_options', false ) ) {
			add_option( 'aicite_guard_options', Aicite_Guard_Settings::defaults() );
		}

		Aicite_Guard_Llms::add_rewrite_rules();
		flush_rewrite_rules( false );

		$options = Aicite_Guard_Settings::get();
		if ( empty( $options['wizard_complete'] ) ) {
			update_option( 'aicite_guard_do_activation_redirect', true, false );
		}
	}
}
