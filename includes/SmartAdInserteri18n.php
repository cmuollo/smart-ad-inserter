<?php
namespace SmartAdInserter;

/**
 * Gestisce l'internazionalizzazione e la localizzazione del plugin.
 *
 * Si occupa di caricare i file di traduzione per renderlo multilingua.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserteri18n {

	/**
	 * Carica il text domain per le traduzioni.
	 *
	 * @since    1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'smart-ad-inserter',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
