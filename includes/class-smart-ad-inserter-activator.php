<?php
namespace SmartAdInserter;

/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserterActivator {

	/**
	 * Initialize default settings in wp_options if they do not exist.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$default_settings = [
			'global_scripts' => '',
			'positions'      => [
				'atf' => [
					'active'              => false,
					'code'                => '',
					'min_height_desktop' => 250,
					'min_height_mobile'  => 250,
				],
				'btf' => [
					'active'              => false,
					'code'                => '',
					'min_height_desktop' => 250,
					'min_height_mobile'  => 250,
				],
				'masthead' => [
					'active'              => false,
					'code'                => '',
					'min_height_desktop' => 90,
					'min_height_mobile'  => 90,
				],
				'sidebar_top' => [
					'active'              => false,
					'code'                => '',
					'min_height_desktop' => 250,
					'min_height_mobile'  => 250,
					'custom_selector'     => '',
				],
			],
		];

		if ( false === get_option( 'smart_ad_inserter_settings' ) ) {
			add_option( 'smart_ad_inserter_settings', $default_settings );
		}
	}
}
