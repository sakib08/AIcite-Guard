<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete options, post meta, and transients created by this plugin.
 *
 * @return void
 */
function spg_by_ppros_uninstall() {
	$spg_by_ppros_option_keys = array(
		'spg_by_ppros_options',
		'spg_by_ppros_ai_key',
		'spg_by_ppros_llms_cache',
		'spg_by_ppros_llms_full_cache',
		'spg_by_ppros_llms_generated_at',
		'spg_by_ppros_a11y_report',
		'spg_by_ppros_alt_suggestions',
		'spg_by_ppros_health_report',
		'spg_by_ppros_ai_usage',
		'spg_by_ppros_do_activation_redirect',
		'spg_by_ppros_scores',
		'spg_by_ppros_physical_files',
		'sitepulse_guard_by_plugin_pros_options',
		'sitepulse_guard_by_plugin_pros_ai_key',
		'sitepulse_guard_by_plugin_pros_llms_cache',
		'sitepulse_guard_by_plugin_pros_llms_full_cache',
		'sitepulse_guard_by_plugin_pros_llms_generated_at',
		'sitepulse_guard_by_plugin_pros_a11y_report',
		'sitepulse_guard_by_plugin_pros_alt_suggestions',
		'sitepulse_guard_by_plugin_pros_health_report',
		'sitepulse_guard_by_plugin_pros_ai_usage',
		'sitepulse_guard_by_plugin_pros_do_activation_redirect',
		'sitepulse_guard_by_plugin_pros_scores',
		'sitepulse_guard_by_plugin_pros_physical_files',
	);

	foreach ( $spg_by_ppros_option_keys as $spg_by_ppros_key ) {
		delete_option( $spg_by_ppros_key );
	}

	delete_metadata( 'post', 0, '_spg_by_ppros_a11y_score', '', true );
	delete_metadata( 'post', 0, '_spg_by_ppros_a11y_issues', '', true );
	delete_metadata( 'post', 0, '_sitepulse_guard_by_plugin_pros_a11y_score', '', true );
	delete_metadata( 'post', 0, '_sitepulse_guard_by_plugin_pros_a11y_issues', '', true );

	delete_transient( 'spg_by_ppros_health_cache' );
	delete_transient( 'spg_by_ppros_ai_score_cache' );
	delete_transient( 'sitepulse_guard_by_plugin_pros_health_cache' );
	delete_transient( 'sitepulse_guard_by_plugin_pros_ai_score_cache' );
}
spg_by_ppros_uninstall();
