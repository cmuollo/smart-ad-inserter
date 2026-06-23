<?php
/**
 * File di bootstrap principale del plugin
 *
 * Questo file viene letto da WordPress per generare le informazioni del plugin
 * all'interno dell'area amministrativa. Include tutte le dipendenze utilizzate,
 * registra le funzioni di attivazione e disattivazione e avvia l'esecuzione del plugin.
 *
 * @link              https://github.com/cmuollo/smart-ad-inserter
 * @since             1.0.0
 * @package           Smart_Ad_Inserter
 *
 * @wordpress-plugin
 * Plugin Name:       Smart Ad Inserter
 * Plugin URI:        https://github.com/cmuollo/smart-ad-inserter
 * Description:       Plugin WordPress server-side per l'ottimizzazione del Cumulative Layout Shift (CLS).
 * Version:           1.0.0
 * Author:            Carmine Muollo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smart-ad-inserter
 * Domain Path:       /languages
 */

// Se questo file viene chiamato direttamente, interrompi l'esecuzione.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Inclusione dell'Autoloader generato da Composer per caricare le classi automaticamente.
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

/**
 * Logica eseguita durante l'attivazione del plugin.
 */
function activate_smart_ad_inserter() {
	SmartAdInserter\SmartAdInserterActivator::activate();
}

/**
 * Logica eseguita durante la disattivazione del plugin.
 */
function deactivate_smart_ad_inserter() {
	SmartAdInserter\SmartAdInserterDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smart_ad_inserter' );
register_deactivation_hook( __FILE__, 'deactivate_smart_ad_inserter' );

/**
 * Avvia il ciclo di vita e l'esecuzione del plugin.
 */
function run_smart_ad_inserter() {
	if ( class_exists( 'SmartAdInserter\\SmartAdInserter' ) ) {
		$plugin = new SmartAdInserter\SmartAdInserter();
		$plugin->run();
	}
}
run_smart_ad_inserter();
