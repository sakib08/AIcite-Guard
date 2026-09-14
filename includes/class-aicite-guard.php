<?php
/**
 * Core plugin orchestrator.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads only the files needed for the current request.
 */
class Aicite_Guard {

	/**
	 * Hook loader.
	 *
	 * @var Aicite_Guard_Loader
	 */
	protected $loader;

	/**
	 * Bootstrap the plugin.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->define_shared_hooks();

		if ( is_admin() ) {
			$this->define_admin_hooks();
		}

		$this->define_public_hooks();
	}

	/**
	 * Include class files. Admin-only classes stay out of the front end.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-loader.php';
		require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-settings.php';
		require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-llms.php';
		require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-crawler.php';
		require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-schema.php';

		if ( is_admin() ) {
			require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-ai-score.php';
			require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-ai.php';
			require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-accessibility.php';
			require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-health.php';
			require_once AICITE_GUARD_PATH . 'admin/class-aicite-guard-admin.php';
		}

		require_once AICITE_GUARD_PATH . 'public/class-aicite-guard-public.php';

		$this->loader = new Aicite_Guard_Loader();
	}

	/**
	 * Front-end + shared hooks (kept tiny).
	 *
	 * @return void
	 */
	private function define_shared_hooks() {
		$llms    = new Aicite_Guard_Llms();
		$crawler = new Aicite_Guard_Crawler();
		$schema  = new Aicite_Guard_Schema();

		$this->loader->add_action( 'init', $llms, 'register_rewrites' );
		$this->loader->add_filter( 'query_vars', $llms, 'query_vars' );
		$this->loader->add_action( 'template_redirect', $llms, 'serve' );
		$this->loader->add_action( 'save_post', $llms, 'maybe_invalidate', 10, 2 );
		$this->loader->add_filter( 'robots_txt', $crawler, 'filter_robots', 20, 2 );
		$this->loader->add_action( 'wp_head', $schema, 'output', 20 );
	}

	/**
	 * wp-admin hooks.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		$admin = new Aicite_Guard_Admin();

		$this->loader->add_action( 'admin_menu', $admin, 'add_menu' );
		$this->loader->add_action( 'admin_init', $admin, 'register_settings' );
		$this->loader->add_action( 'admin_init', $admin, 'maybe_redirect_wizard' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
		$this->loader->add_filter( 'plugin_action_links_' . AICITE_GUARD_BASENAME, $admin, 'action_links' );

		$this->loader->add_action( 'wp_ajax_aicite_guard_regenerate_llms', $admin, 'ajax_regenerate_llms' );
		$this->loader->add_action( 'wp_ajax_aicite_guard_scan_a11y', $admin, 'ajax_scan_a11y' );
		$this->loader->add_action( 'wp_ajax_aicite_guard_generate_alt', $admin, 'ajax_generate_alt' );
		$this->loader->add_action( 'wp_ajax_aicite_guard_apply_alt', $admin, 'ajax_apply_alt' );
		$this->loader->add_action( 'wp_ajax_aicite_guard_statement', $admin, 'ajax_statement' );
		$this->loader->add_action( 'wp_ajax_aicite_guard_refresh_health', $admin, 'ajax_refresh_health' );
		$this->loader->add_action( 'wp_ajax_aicite_guard_save_wizard', $admin, 'ajax_save_wizard' );
	}

	/**
	 * Public widget hooks (assets only when the widget is on).
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		$public = new Aicite_Guard_Public();

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_assets' );
		$this->loader->add_action( 'wp_footer', $public, 'render_widget' );
	}

	/**
	 * Run the loader.
	 *
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}
}
