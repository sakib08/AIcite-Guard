<?php
/**
 * Admin menus, settings, and AJAX.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress admin integration.
 */
class Aicite_Guard_Admin {

	/**
	 * Register the top-level menu and screens.
	 *
	 * @return void
	 */
	public function add_menu() {
		$cap = 'manage_options';

		add_menu_page(
			__( 'AIcite Guard', 'aicite-guard' ),
			__( 'AIcite Guard', 'aicite-guard' ),
			$cap,
			'aicite-guard',
			array( $this, 'render_dashboard' ),
			'dashicons-shield-alt',
			58
		);

		add_submenu_page( 'aicite-guard', __( 'Dashboard', 'aicite-guard' ), __( 'Dashboard', 'aicite-guard' ), $cap, 'aicite-guard', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'aicite-guard', __( 'AI Visibility', 'aicite-guard' ), __( 'AI Visibility', 'aicite-guard' ), $cap, 'aicite-guard-ai', array( $this, 'render_ai' ) );
		add_submenu_page( 'aicite-guard', __( 'Accessibility', 'aicite-guard' ), __( 'Accessibility', 'aicite-guard' ), $cap, 'aicite-guard-a11y', array( $this, 'render_a11y' ) );
		add_submenu_page( 'aicite-guard', __( 'Site Health', 'aicite-guard' ), __( 'Site Health', 'aicite-guard' ), $cap, 'aicite-guard-health', array( $this, 'render_health' ) );
		add_submenu_page( 'aicite-guard', __( 'Settings', 'aicite-guard' ), __( 'Settings', 'aicite-guard' ), $cap, 'aicite-guard-settings', array( $this, 'render_settings' ) );
		add_submenu_page( 'aicite-guard', __( 'Setup Wizard', 'aicite-guard' ), __( 'Setup Wizard', 'aicite-guard' ), $cap, 'aicite-guard-wizard', array( $this, 'render_wizard' ) );
		add_submenu_page( 'aicite-guard', __( 'Go Pro', 'aicite-guard' ), __( 'Go Pro', 'aicite-guard' ), $cap, 'aicite-guard-pro', array( $this, 'render_pro' ) );
	}

	/**
	 * Register the settings API group.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'aicite_guard_settings',
			Aicite_Guard_Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_posted_settings' ),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Sanitize the settings form, including the separate API key field.
	 *
	 * @param mixed $input Raw option.
	 * @return array<string, mixed>
	 */
	public function sanitize_posted_settings( $input ) {
		$current = Aicite_Guard_Settings::get();
		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$merged = $current;

		$merged['ai_visibility']['enabled']          = ! empty( $input['ai_visibility']['enabled'] );
		$merged['ai_visibility']['include_excerpts'] = ! empty( $input['ai_visibility']['include_excerpts'] );
		$merged['ai_visibility']['auto_update']      = ! empty( $input['ai_visibility']['auto_update'] );
		$merged['ai_visibility']['max_items']        = isset( $input['ai_visibility']['max_items'] ) ? absint( $input['ai_visibility']['max_items'] ) : 50;
		$merged['ai_visibility']['post_types']       = isset( $input['ai_visibility']['post_types'] ) ? $input['ai_visibility']['post_types'] : array();

		$merged['crawler']['robots_integration']   = ! empty( $input['crawler']['robots_integration'] );
		$merged['crawler']['allow_answer_engines'] = ! empty( $input['crawler']['allow_answer_engines'] );
		$merged['crawler']['block_training']       = ! empty( $input['crawler']['block_training'] );

		$merged['schema']['enabled'] = ! empty( $input['schema']['enabled'] );

		$merged['accessibility']['enabled']         = ! empty( $input['accessibility']['enabled'] );
		$merged['accessibility']['widget_enabled']  = ! empty( $input['accessibility']['widget_enabled'] );
		$merged['accessibility']['widget_position'] = isset( $input['accessibility']['widget_position'] ) ? $input['accessibility']['widget_position'] : 'bottom-right';
		$merged['accessibility']['statement_page']  = isset( $current['accessibility']['statement_page'] ) ? $current['accessibility']['statement_page'] : 0;

		$merged['health']['enabled'] = ! empty( $input['health']['enabled'] );
		$merged['ai']['provider']    = isset( $input['ai']['provider'] ) ? $input['ai']['provider'] : 'none';
		$merged['wizard_complete']   = ! empty( $current['wizard_complete'] );

		if ( isset( $_POST['aicite_guard_ai_key'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'aicite_guard_settings-options' ) ) {
			$key = sanitize_text_field( wp_unslash( $_POST['aicite_guard_ai_key'] ) );
			if ( '********' !== $key ) {
				Aicite_Guard_Settings::set_ai_key( $key );
			}
		}

		add_settings_error( 'aicite_guard_settings', 'aicite_guard_saved', __( 'Settings saved.', 'aicite-guard' ), 'updated' );

		return Aicite_Guard_Settings::sanitize( $merged );
	}

	/**
	 * First-run redirect to the wizard.
	 *
	 * @return void
	 */
	public function maybe_redirect_wizard() {
		if ( ! get_option( 'aicite_guard_do_activation_redirect' ) ) {
			return;
		}

		delete_option( 'aicite_guard_do_activation_redirect' );

		if ( wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=aicite-guard-wizard' ) );
		exit;
	}

	/**
	 * Admin assets on plugin screens only.
	 *
	 * @param string $hook Current hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'aicite-guard' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'aicite-guard-admin',
			AICITE_GUARD_URL . 'admin/css/aicite-guard-admin.css',
			array(),
			AICITE_GUARD_VERSION
		);

		wp_enqueue_script(
			'aicite-guard-admin',
			AICITE_GUARD_URL . 'admin/js/aicite-guard-admin.js',
			array(),
			AICITE_GUARD_VERSION,
			true
		);

		wp_localize_script(
			'aicite-guard-admin',
			'aiciteGuardAdmin',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'aicite_guard_admin' ),
				'i18n'  => array(
					'working'   => __( 'Working…', 'aicite-guard' ),
					'done'      => __( 'Done.', 'aicite-guard' ),
					'error'     => __( 'Something went wrong. Please try again.', 'aicite-guard' ),
					'applied'   => __( 'Applied.', 'aicite-guard' ),
					'confirm'   => __( 'Apply this alt text to the media library?', 'aicite-guard' ),
				),
			)
		);
	}

	/**
	 * Plugin row links.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public function action_links( $links ) {
		$custom = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=aicite-guard' ) ) . '">' . esc_html__( 'Dashboard', 'aicite-guard' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=aicite-guard-settings' ) ) . '">' . esc_html__( 'Settings', 'aicite-guard' ) . '</a>',
		);

		return array_merge( $custom, $links );
	}

	/**
	 * Dashboard.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$this->render_view( 'dashboard' );
	}

	/**
	 * AI Visibility.
	 *
	 * @return void
	 */
	public function render_ai() {
		$this->render_view( 'ai-visibility' );
	}

	/**
	 * Accessibility.
	 *
	 * @return void
	 */
	public function render_a11y() {
		$this->render_view( 'accessibility' );
	}

	/**
	 * Site health.
	 *
	 * @return void
	 */
	public function render_health() {
		$this->render_view( 'site-health' );
	}

	/**
	 * Settings.
	 *
	 * @return void
	 */
	public function render_settings() {
		$this->render_view( 'settings' );
	}

	/**
	 * Wizard.
	 *
	 * @return void
	 */
	public function render_wizard() {
		$this->render_view( 'wizard' );
	}

	/**
	 * Pro upsell.
	 *
	 * @return void
	 */
	public function render_pro() {
		$this->render_view( 'go-pro' );
	}

	/**
	 * Shared view bootstrap.
	 *
	 * @param string $view View name.
	 * @return void
	 */
	private function render_view( $view ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'aicite-guard' ) );
		}

		$settings = Aicite_Guard_Settings::get();
		$scores   = get_option( 'aicite_guard_scores', array() );
		$scores   = is_array( $scores ) ? $scores : array();
		$file     = AICITE_GUARD_PATH . 'admin/views/' . $view . '.php';

		if ( ! is_readable( $file ) ) {
			return;
		}

		include AICITE_GUARD_PATH . 'admin/views/partials-header.php';
		include $file;
		include AICITE_GUARD_PATH . 'admin/views/partials-footer.php';
	}

	/**
	 * AJAX: regenerate llms files and AI score.
	 *
	 * @return void
	 */
	public function ajax_regenerate_llms() {
		$this->assert_ajax();

		$llms   = new Aicite_Guard_Llms();
		$files  = $llms->regenerate();
		$scorer = new Aicite_Guard_Ai_Score();
		$score  = $scorer->calculate();

		wp_send_json_success(
			array(
				'basic'        => $files['basic'],
				'full'         => $files['full'],
				'generated_at' => $files['generated_at'],
				'score'        => $score,
			)
		);
	}

	/**
	 * AJAX: accessibility scan.
	 *
	 * @return void
	 */
	public function ajax_scan_a11y() {
		$this->assert_ajax();

		$scanner = new Aicite_Guard_Accessibility();
		$report  = $scanner->scan();

		wp_send_json_success( $report );
	}

	/**
	 * AJAX: generate one alt suggestion.
	 *
	 * @return void
	 */
	public function ajax_generate_alt() {
		$this->assert_ajax();

		$id = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in assert_ajax().
		$scanner = new Aicite_Guard_Accessibility();
		$result  = $scanner->generate_alt( $id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'alt'       => $result,
				'remaining' => Aicite_Guard_Ai::remaining(),
			)
		);
	}

	/**
	 * AJAX: apply approved alt text.
	 *
	 * @return void
	 */
	public function ajax_apply_alt() {
		$this->assert_ajax();

		$id  = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in assert_ajax().
		$alt = isset( $_POST['alt'] ) ? sanitize_text_field( wp_unslash( $_POST['alt'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in assert_ajax().

		$scanner = new Aicite_Guard_Accessibility();
		$result  = $scanner->apply_alt( $id, $alt );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success();
	}

	/**
	 * AJAX: generate statement page.
	 *
	 * @return void
	 */
	public function ajax_statement() {
		$this->assert_ajax();

		$scanner = new Aicite_Guard_Accessibility();
		$result  = $scanner->generate_statement_page();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'page_id' => $result,
				'edit'    => get_edit_post_link( $result, 'raw' ),
				'view'    => get_permalink( $result ),
			)
		);
	}

	/**
	 * AJAX: refresh health report.
	 *
	 * @return void
	 */
	public function ajax_refresh_health() {
		$this->assert_ajax();

		$health = new Aicite_Guard_Health();
		wp_send_json_success( $health->report( true ) );
	}

	/**
	 * AJAX: persist wizard answers.
	 *
	 * @return void
	 */
	public function ajax_save_wizard() {
		$this->assert_ajax();

		$raw = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verified in assert_ajax(); JSON decoded then sanitized via Settings::update().
		$payload = is_string( $raw ) ? json_decode( $raw, true ) : array();
		if ( ! is_array( $payload ) ) {
			$payload = array();
		}

		$payload['wizard_complete'] = true;
		Aicite_Guard_Settings::update( $payload );

		$llms = new Aicite_Guard_Llms();
		$llms->regenerate();

		$score = new Aicite_Guard_Ai_Score();
		$score->calculate();

		wp_send_json_success(
			array(
				'redirect' => admin_url( 'admin.php?page=aicite-guard' ),
			)
		);
	}

	/**
	 * Capability + nonce gate for AJAX.
	 *
	 * @return void
	 */
	private function assert_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aicite-guard' ) ), 403 );
		}

		check_ajax_referer( 'aicite_guard_admin', 'nonce' );
	}

	/**
	 * Detect companion SEO plugins for dashboard badges.
	 *
	 * @return array<string, bool>
	 */
	public static function companions() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return array(
			'yoast'     => is_plugin_active( 'wordpress-seo/wp-seo.php' ),
			'rankmath'  => is_plugin_active( 'seo-by-rank-math/rank-math.php' ),
			'elementor' => is_plugin_active( 'elementor/elementor.php' ),
			'bricks'    => defined( 'BRICKS_VERSION' ),
		);
	}
}
