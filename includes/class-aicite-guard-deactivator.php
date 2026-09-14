<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation routines.
 */
class Aicite_Guard_Deactivator {

	/**
	 * Flush rewrite rules so /llms.txt no longer resolves here.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules( false );
	}
}
