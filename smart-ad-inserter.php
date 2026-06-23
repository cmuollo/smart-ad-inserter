<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and starts the plugin.
 *
 * @link              https://github.com/cmuollo/smart-ad-inserter
 * @since             1.0.0
 * @package           Smart_Ad_Inserter
 *
 * @wordpress-plugin
 * Plugin Name:       Smart Ad Inserter
 * Plugin URI:        https://github.com/cmuollo/smart-ad-inserter
 * Description:       WordPress Server-Side plugin for Cumulative Layout Shift (CLS) optimization.
 * Version:           1.0.0
 * Author:            Carmine Muollo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smart-ad-inserter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Include the Composer autoloader if it exists.
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

/**
 * The code that runs during plugin activation.
 */
function activate_smart_ad_inserter() {
	SmartAdInserter\SmartAdInserterActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_smart_ad_inserter() {
	SmartAdInserter\SmartAdInserterDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smart_ad_inserter' );
register_deactivation_hook( __FILE__, 'deactivate_smart_ad_inserter' );

/**
 * Begins execution of the plugin.
 */
function run_smart_ad_inserter() {
	if ( class_exists( 'SmartAdInserter\\SmartAdInserter' ) ) {
		$plugin = new SmartAdInserter\SmartAdInserter();
		$plugin->run();
	}
}
run_smart_ad_inserter();
