<?php
/**
 * Front-end accessibility widget.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads a tiny visitor widget when enabled.
 */
class Sitepulse_Guard_By_Plugin_Pros_Public {

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
			'spg-by-ppros-public',
			SPG_BY_PPROS_URL . 'public/css/spg-by-ppros-public.css',
			array(),
			SPG_BY_PPROS_VERSION
		);

		wp_enqueue_script(
			'spg-by-ppros-public',
			SPG_BY_PPROS_URL . 'public/js/spg-by-ppros-public.js',
			array(),
			SPG_BY_PPROS_VERSION,
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

		$position = Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'accessibility.widget_position', 'bottom-right' );
		$page_id  = (int) Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'accessibility.statement_page', 0 );
		$link     = $page_id ? get_permalink( $page_id ) : '';

		include SPG_BY_PPROS_PATH . 'public/views/widget.php';
	}

	/**
	 * Whether the visitor widget should load.
	 *
	 * @return bool
	 */
	private function widget_enabled() {
		return Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'accessibility.enabled', true )
			&& Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'accessibility.widget_enabled', true );
	}
}
