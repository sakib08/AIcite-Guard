<?php
/**
 * AIcite Guard
 *
 * @package           Aicite_Guard
 * @author            sakibbd08
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       AIcite Guard
 * Plugin URI:        https://pluginpros.co
 * Description:       Lightweight AI search visibility (GEO), real accessibility (EAA/WCAG), and site health. Works alongside Yoast and Rank Math.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      7.1
 * Author:            sakibbd08
 * Author URI:        https://profiles.wordpress.org/sakibbd08/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aicite-guard
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AICITE_GUARD_VERSION', '1.0.0' );
define( 'AICITE_GUARD_FILE', __FILE__ );
define( 'AICITE_GUARD_PATH', plugin_dir_path( __FILE__ ) );
define( 'AICITE_GUARD_URL', plugin_dir_url( __FILE__ ) );
define( 'AICITE_GUARD_BASENAME', plugin_basename( __FILE__ ) );
define( 'AICITE_GUARD_SLUG', 'aicite-guard' );
define( 'AICITE_GUARD_REST_NAMESPACE', 'aicite-guard/v1' );

/**
 * Free-tier monthly AI generation cap when a BYOK key is present.
 */
define( 'AICITE_GUARD_FREE_AI_LIMIT', 20 );

require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-activator.php';
require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard-deactivator.php';

register_activation_hook( __FILE__, array( 'Aicite_Guard_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Aicite_Guard_Deactivator', 'deactivate' ) );

require_once AICITE_GUARD_PATH . 'includes/class-aicite-guard.php';

/**
 * Begins plugin execution.
 *
 * @return void
 */
function aicite_guard_run() {
	$plugin = new Aicite_Guard();
	$plugin->run();
}
aicite_guard_run();
