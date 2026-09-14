<?php
/**
 * Front-end accessibility widget.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads a tiny visitor widget when enabled.
 */
class Aicite_Guard_Public {

	/**
	 * Enqueue widget assets only when needed.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( is_admin() || ! $this->widget_enabled() ) {
			return;
		}

		wp_enqueue_style(
			'aicite-guard-public',
			AICITE_GUARD_URL . 'public/css/aicite-guard-public.css',
			array(),
			AICITE_GUARD_VERSION
		);

		wp_enqueue_script(
			'aicite-guard-public',
			AICITE_GUARD_URL . 'public/js/aicite-guard-public.js',
			array(),
			AICITE_GUARD_VERSION,
			true
		);
	}

	/**
	 * Print the widget markup in the footer.
	 *
	 * @return void
	 */
	public function render_widget() {
		if ( is_admin() || ! $this->widget_enabled() ) {
			return;
		}

		$position = Aicite_Guard_Settings::get_path( 'accessibility.widget_position', 'bottom-right' );
		$page_id  = (int) Aicite_Guard_Settings::get_path( 'accessibility.statement_page', 0 );
		$link     = $page_id ? get_permalink( $page_id ) : '';

		include AICITE_GUARD_PATH . 'public/views/widget.php';
	}

	/**
	 * Whether the visitor widget should load.
	 *
	 * @return bool
	 */
	private function widget_enabled() {
		return Aicite_Guard_Settings::get_path( 'accessibility.enabled', true )
			&& Aicite_Guard_Settings::get_path( 'accessibility.widget_enabled', true );
	}
}
