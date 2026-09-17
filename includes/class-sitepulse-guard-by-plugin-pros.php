<?php
/**
 * Core plugin orchestrator.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads only the files needed for the current request.
 */
class Sitepulse_Guard_By_Plugin_Pros {

	/**
	 * Hook loader.
	 *
	 * @var Sitepulse_Guard_By_Plugin_Pros_Loader
	 */
	protected $loader;

	/**
	 * Bootstrap the plugin.
	 */
	public function __construct() {
		$this->load_dependencies();
		Sitepulse_Guard_By_Plugin_Pros_Settings::migrate_legacy_keys();
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
		require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-loader.php';
		require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-settings.php';
		require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-llms.php';
		require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-crawler.php';
		require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-schema.php';

		if ( is_admin() ) {
			require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-ai-score.php';
			require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-ai.php';
			require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-accessibility.php';
			require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-health.php';
			require_once SPG_BY_PPROS_PATH . 'admin/class-sitepulse-guard-by-plugin-pros-admin.php';
		}

		require_once SPG_BY_PPROS_PATH . 'public/class-sitepulse-guard-by-plugin-pros-public.php';

		$this->loader = new Sitepulse_Guard_By_Plugin_Pros_Loader();
	}

	/**
	 * Front-end + shared hooks (kept tiny).
	 *
	 * @return void
	 */
	private function define_shared_hooks() {
		$llms    = new Sitepulse_Guard_By_Plugin_Pros_Llms();
		$crawler = new Sitepulse_Guard_By_Plugin_Pros_Crawler();
		$schema  = new Sitepulse_Guard_By_Plugin_Pros_Schema();

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
		$admin = new Sitepulse_Guard_By_Plugin_Pros_Admin();

		$this->loader->add_action( 'admin_menu', $admin, 'add_menu' );
		$this->loader->add_action( 'admin_init', $admin, 'register_settings' );
		$this->loader->add_action( 'admin_init', $admin, 'maybe_redirect_wizard' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
		$this->loader->add_filter( 'plugin_action_links_' . SPG_BY_PPROS_BASENAME, $admin, 'action_links' );

		$this->loader->add_action( 'wp_ajax_spg_by_ppros_regenerate_llms', $admin, 'ajax_regenerate_llms' );
		$this->loader->add_action( 'wp_ajax_spg_by_ppros_scan_a11y', $admin, 'ajax_scan_a11y' );
		$this->loader->add_action( 'wp_ajax_spg_by_ppros_generate_alt', $admin, 'ajax_generate_alt' );
		$this->loader->add_action( 'wp_ajax_spg_by_ppros_apply_alt', $admin, 'ajax_apply_alt' );
		$this->loader->add_action( 'wp_ajax_spg_by_ppros_statement', $admin, 'ajax_statement' );
		$this->loader->add_action( 'wp_ajax_spg_by_ppros_refresh_health', $admin, 'ajax_refresh_health' );
		$this->loader->add_action( 'wp_ajax_spg_by_ppros_save_wizard', $admin, 'ajax_save_wizard' );
	}

	/**
	 * Public widget hooks (assets only when the widget is on).
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		$public = new Sitepulse_Guard_By_Plugin_Pros_Public();

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
