<?php
namespace SmartAdInserter;

/**
 * Gestisce la logica di disattivazione del plugin.
 *
 * Si occupa di ripulire transients o stati volatili quando il plugin viene disattivato.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserterDeactivator {

	/**
	 * Logica eseguita alla disattivazione.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Pulisce la cache delle posizioni dei banner per evitare disallineamenti futuri
		delete_transient( 'sai_structural_ads_locations' );
	}
}
