<?php
namespace SmartAdInserter;

/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserterDeactivator {

	/**
	 * Deactivation tasks.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Clear transients
		delete_transient( 'sai_structural_ads_locations' );
	}
}
