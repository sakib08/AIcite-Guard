<?php
/**
 * Fired during plugin activation.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation routines.
 */
class Sitepulse_Guard_By_Plugin_Pros_Activator {

	/**
	 * Create defaults, register rewrite rules, and flag the setup wizard.
	 *
	 * @return void
	 */
	public static function activate() {
		require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-settings.php';
		require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-llms.php';

		Sitepulse_Guard_By_Plugin_Pros_Settings::migrate_legacy_keys();

		if ( false === get_option( 'spg_by_ppros_options', false ) ) {
			add_option( 'spg_by_ppros_options', Sitepulse_Guard_By_Plugin_Pros_Settings::defaults() );
		}

		Sitepulse_Guard_By_Plugin_Pros_Llms::add_rewrite_rules();
		flush_rewrite_rules( false );

		$options = Sitepulse_Guard_By_Plugin_Pros_Settings::get();
		if ( empty( $options['wizard_complete'] ) ) {
			update_option( 'spg_by_ppros_do_activation_redirect', true, false );
		}
	}
}
