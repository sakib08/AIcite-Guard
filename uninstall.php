<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'aicite_guard_options' );
delete_option( 'aicite_guard_ai_key' );
delete_option( 'aicite_guard_llms_cache' );
delete_option( 'aicite_guard_llms_full_cache' );
delete_option( 'aicite_guard_llms_generated_at' );
delete_option( 'aicite_guard_a11y_report' );
delete_option( 'aicite_guard_alt_suggestions' );
delete_option( 'aicite_guard_health_report' );
delete_option( 'aicite_guard_ai_usage' );
delete_option( 'aicite_guard_do_activation_redirect' );
delete_option( 'aicite_guard_scores' );
delete_option( 'aicite_guard_physical_files' );

delete_metadata( 'post', 0, '_aicite_guard_a11y_score', '', true );
delete_metadata( 'post', 0, '_aicite_guard_a11y_issues', '', true );

delete_transient( 'aicite_guard_health_cache' );
delete_transient( 'aicite_guard_ai_score_cache' );
