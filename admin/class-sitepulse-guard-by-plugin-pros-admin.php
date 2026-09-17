<?php
/**
 * Admin menus, settings, and AJAX.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress admin integration.
 */
class Sitepulse_Guard_By_Plugin_Pros_Admin {

	/**
	 * Register the top-level menu and screens.
	 *
	 * @return void
	 */
	public function add_menu() {
		$cap  = 'manage_options';
		$slug = SPG_BY_PPROS_SLUG;

		add_menu_page(
			__( 'SitePulse Guard', 'spg-by-ppros' ),
			__( 'SitePulse Guard', 'spg-by-ppros' ),
			$cap,
			$slug,
			array( $this, 'render_dashboard' ),
			'dashicons-shield-alt',
			58
		);

		add_submenu_page( $slug, __( 'Dashboard', 'spg-by-ppros' ), __( 'Dashboard', 'spg-by-ppros' ), $cap, $slug, array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, __( 'AI Visibility', 'spg-by-ppros' ), __( 'AI Visibility', 'spg-by-ppros' ), $cap, $slug . '-ai', array( $this, 'render_ai' ) );
		add_submenu_page( $slug, __( 'Accessibility', 'spg-by-ppros' ), __( 'Accessibility', 'spg-by-ppros' ), $cap, $slug . '-a11y', array( $this, 'render_a11y' ) );
		add_submenu_page( $slug, __( 'Site Health', 'spg-by-ppros' ), __( 'Site Health', 'spg-by-ppros' ), $cap, $slug . '-health', array( $this, 'render_health' ) );
		add_submenu_page( $slug, __( 'Settings', 'spg-by-ppros' ), __( 'Settings', 'spg-by-ppros' ), $cap, $slug . '-settings', array( $this, 'render_settings' ) );
		add_submenu_page( $slug, __( 'Setup Wizard', 'spg-by-ppros' ), __( 'Setup Wizard', 'spg-by-ppros' ), $cap, $slug . '-wizard', array( $this, 'render_wizard' ) );
		add_submenu_page( $slug, __( 'Go Pro', 'spg-by-ppros' ), __( 'Go Pro', 'spg-by-ppros' ), $cap, $slug . '-pro', array( $this, 'render_pro' ) );
	}

	/**
	 * Register the settings API group.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'spg_by_ppros_settings',
			Sitepulse_Guard_By_Plugin_Pros_Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_posted_settings' ),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Sanitize the settings form.
	 *
	 * @param mixed $input Raw option.
	 * @return array<string, mixed>
	 */
	public function sanitize_posted_settings( $input ) {
		$current = Sitepulse_Guard_By_Plugin_Pros_Settings::get();
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
		$merged['ai']['enabled']     = ! empty( $input['ai']['enabled'] );
		$merged['wizard_complete']   = ! empty( $current['wizard_complete'] );

		add_settings_error( 'spg_by_ppros_settings', 'spg_by_ppros_saved', __( 'Settings saved.', 'spg-by-ppros' ), 'updated' );

		return Sitepulse_Guard_By_Plugin_Pros_Settings::sanitize( $merged );
	}

	/**
	 * First-run redirect to the wizard.
	 *
	 * @return void
	 */
	public function maybe_redirect_wizard() {
		if ( ! get_option( 'spg_by_ppros_do_activation_redirect' ) ) {
			return;
		}

		delete_option( 'spg_by_ppros_do_activation_redirect' );

		if ( wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=spg-by-ppros-wizard' ) );
		exit;
	}

	/**
	 * Admin assets on plugin screens only.
	 *
	 * @param string $hook Current hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, SPG_BY_PPROS_SLUG ) === false ) {
			return;
		}

		wp_enqueue_style(
			'spg-by-ppros-admin',
			SPG_BY_PPROS_URL . 'admin/css/spg-by-ppros-admin.css',
			array(),
			SPG_BY_PPROS_VERSION
		);

		wp_enqueue_script(
			'spg-by-ppros-admin',
			SPG_BY_PPROS_URL . 'admin/js/spg-by-ppros-admin.js',
			array(),
			SPG_BY_PPROS_VERSION,
			true
		);

		wp_localize_script(
			'spg-by-ppros-admin',
			'spgByPprosAdmin',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'spg_by_ppros_admin' ),
				'i18n'  => array(
					'working'   => __( 'Working…', 'spg-by-ppros' ),
					'done'      => __( 'Done.', 'spg-by-ppros' ),
					'error'     => __( 'Something went wrong. Please try again.', 'spg-by-ppros' ),
					'applied'   => __( 'Applied.', 'spg-by-ppros' ),
					'confirm'   => __( 'Apply this alt text to the media library?', 'spg-by-ppros' ),
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
			'<a href="' . esc_url( admin_url( 'admin.php?page=spg-by-ppros' ) ) . '">' . esc_html__( 'Dashboard', 'spg-by-ppros' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=spg-by-ppros-settings' ) ) . '">' . esc_html__( 'Settings', 'spg-by-ppros' ) . '</a>',
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
			wp_die( esc_html__( 'You do not have permission to access this page.', 'spg-by-ppros' ) );
		}

		$settings = Sitepulse_Guard_By_Plugin_Pros_Settings::get();
		$scores   = get_option( 'spg_by_ppros_scores', array() );
		$scores   = is_array( $scores ) ? $scores : array();
		$file     = SPG_BY_PPROS_PATH . 'admin/views/' . $view . '.php';

		if ( ! is_readable( $file ) ) {
			return;
		}

		include SPG_BY_PPROS_PATH . 'admin/views/partials-header.php';
		include $file;
		include SPG_BY_PPROS_PATH . 'admin/views/partials-footer.php';
	}

	/**
	 * AJAX: regenerate llms files and AI score.
	 *
	 * @return void
	 */
	public function ajax_regenerate_llms() {
		$this->assert_ajax();

		$llms   = new Sitepulse_Guard_By_Plugin_Pros_Llms();
		$files  = $llms->regenerate();
		$scorer = new Sitepulse_Guard_By_Plugin_Pros_Ai_Score();
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

		$scanner = new Sitepulse_Guard_By_Plugin_Pros_Accessibility();
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
		$scanner = new Sitepulse_Guard_By_Plugin_Pros_Accessibility();
		$result  = $scanner->generate_alt( $id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'alt'       => $result,
				'remaining' => Sitepulse_Guard_By_Plugin_Pros_Ai::remaining(),
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

		$scanner = new Sitepulse_Guard_By_Plugin_Pros_Accessibility();
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

		$scanner = new Sitepulse_Guard_By_Plugin_Pros_Accessibility();
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

		$health = new Sitepulse_Guard_By_Plugin_Pros_Health();
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
		Sitepulse_Guard_By_Plugin_Pros_Settings::update( $payload );

		$llms = new Sitepulse_Guard_By_Plugin_Pros_Llms();
		$llms->regenerate();

		$score = new Sitepulse_Guard_By_Plugin_Pros_Ai_Score();
		$score->calculate();

		wp_send_json_success(
			array(
				'redirect' => admin_url( 'admin.php?page=spg-by-ppros' ),
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
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'spg-by-ppros' ) ), 403 );
		}

		check_ajax_referer( 'spg_by_ppros_admin', 'nonce' );
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
