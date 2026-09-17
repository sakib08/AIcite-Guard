<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation routines.
 */
class Sitepulse_Guard_By_Plugin_Pros_Deactivator {

	/**
	 * Flush rewrite rules so /llms.txt no longer resolves here.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules( false );
	}
}
