<?php
namespace SmartAdInserter;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the translation files for this plugin so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserteri18n {

	/**
	 * Load the plugin text domain for translation.
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
