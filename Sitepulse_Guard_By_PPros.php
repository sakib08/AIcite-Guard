<?php
/**
 * SitePulse Guard by PPros
 *
 * @package           Sitepulse_Guard_By_PPros
 * @author            Plugin Pros
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       SitePulse Guard by PPros
 * Plugin URI:        https://pluginpros.co
 * Description:       Companion for Yoast/Rank Math: AI search visibility, approve-before-write accessibility, and site health.
 * Version:           1.0.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      7.1
 * Author:            Plugin Pros
 * Author URI:        https://pluginpros.co
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       spg-by-ppros
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SPG_BY_PPROS_VERSION', '1.0.2' );
define( 'SPG_BY_PPROS_FILE', __FILE__ );
define( 'SPG_BY_PPROS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SPG_BY_PPROS_URL', plugin_dir_url( __FILE__ ) );
define( 'SPG_BY_PPROS_BASENAME', plugin_basename( __FILE__ ) );
define( 'SPG_BY_PPROS_SLUG', 'spg-by-ppros' );
define( 'SPG_BY_PPROS_REST_NAMESPACE', 'spg-by-ppros/v1' );

/**
 * Free-tier monthly cap for optional AI-assisted alt text.
 */
define( 'SPG_BY_PPROS_FREE_AI_LIMIT', 20 );

require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-activator.php';
require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros-deactivator.php';

register_activation_hook( __FILE__, array( 'Sitepulse_Guard_By_Plugin_Pros_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Sitepulse_Guard_By_Plugin_Pros_Deactivator', 'deactivate' ) );

require_once SPG_BY_PPROS_PATH . 'includes/class-sitepulse-guard-by-plugin-pros.php';

/**
 * Begins plugin execution.
 *
 * @return void
 */
function spg_by_ppros_run() {
	$plugin = new Sitepulse_Guard_By_Plugin_Pros();
	$plugin->run();
}
spg_by_ppros_run();
