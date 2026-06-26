<?php
namespace SmartAdInserter;

/**
 * Gestore della persistenza e della validazione delle impostazioni del plugin.
 *
 * Si occupa di leggere, scrivere e sanificare i dati all'interno del database di WordPress (wp_options)
 * rispettando il principio di Single Responsibility (SRP) per il livello dei dati.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserterSettings {

	/**
	 * La chiave del database in wp_options.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private $option_key = 'smart_ad_inserter_settings';

	/**
	 * Recupera le impostazioni correnti dal database con i valori di default per le chiavi mancanti.
	 *
	 * @since    1.0.0
	 * @return   array    Impostazioni sanificate recuperate dal database.
	 */
	public function get_settings(): array {
		$settings = get_option( $this->option_key, [] );
		return $this->merge_defaults( is_array( $settings ) ? $settings : [] );
	}

	/**
	 * Valida, sanifica e memorizza le impostazioni nel database di WordPress.
	 *
	 * Invalida inoltre la cache dei transient associata ai selettori strutturali modificati.
	 *
	 * @since    1.0.0
	 * @param    array    $settings    Le nuove impostazioni da salvare.
	 * @return   bool                  Vero in caso di successo nel salvataggio, falso altrimenti.
	 */
	public function save_settings( array $settings ): bool {
		$sanitized_settings = [];

		// Consente il salvataggio di tag script completi per gli utenti con capability manage_options
		$sanitized_settings['global_scripts'] = isset( $settings['global_scripts'] ) ? wp_kses( trim( $settings['global_scripts'] ), $this->get_allowed_html() ) : '';

		$sanitized_settings['positions'] = [];
		if ( isset( $settings['positions'] ) && is_array( $settings['positions'] ) ) {
			foreach ( $settings['positions'] as $position_id => $data ) {
				$sanitized_settings['positions'][ $position_id ] = [
					'active'                => isset( $data['active'] ) ? (bool) $data['active'] : false,
					'code'                  => isset( $data['code'] ) ? wp_kses( trim( $data['code'] ), $this->get_allowed_html() ) : '',
					'min_height_desktop'    => isset( $data['min_height_desktop'] ) ? absint( $data['min_height_desktop'] ) : 250,
					'min_height_mobile'     => isset( $data['min_height_mobile'] ) ? absint( $data['min_height_mobile'] ) : 250,
					'custom_selector'       => isset( $data['custom_selector'] ) ? sanitize_text_field( $data['custom_selector'] ) : '',
					'use_default_placement' => isset( $data['use_default_placement'] ) ? (bool) $data['use_default_placement'] : true,
					'override_css'          => isset( $data['override_css'] ) ? sanitize_textarea_field( $data['override_css'] ) : '',
					'target_element'        => isset( $data['target_element'] ) ? sanitize_text_field( $data['target_element'] ) : '',
					'frequency'             => isset( $data['frequency'] ) ? absint( $data['frequency'] ) : 0,
				];
			}
		}

		// Pulisce la cache delle posizioni strutturali (Transients)
		delete_transient( 'sai_structural_ads_locations' );

		return update_option( $this->option_key, $sanitized_settings );
	}

	/**
	 * Applica i valori di default alle chiavi mancanti nell'array delle impostazioni.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array    $settings    Le impostazioni da completare.
	 * @return   array                 L'array completo di tutte le chiavi.
	 */
	private function merge_defaults( array $settings ): array {
		$defaults = [
			'global_scripts' => '',
			'positions'      => [
				'atf'            => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 250,
					'min_height_mobile'     => 250,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '',
					'frequency'             => 0,
				],
				'btf'            => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 250,
					'min_height_mobile'     => 250,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '',
					'frequency'             => 0,
				],
				'masthead'       => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 250,
					'min_height_mobile'     => 100,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '',
					'frequency'             => 0,
				],
				'sidebar_top'    => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 250,
					'min_height_mobile'     => 0,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '',
					'frequency'             => 0,
				],
				'sidebar_sticky' => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 600,
					'min_height_mobile'     => 0,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '',
					'frequency'             => 0,
				],
				'grid_home'      => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 250,
					'min_height_mobile'     => 250,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '.post-card',
					'frequency'             => 3,
				],
				'grid_archive'   => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 250,
					'min_height_mobile'     => 250,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '.post-card',
					'frequency'             => 3,
				],
			],
		];

		return array_replace_recursive( $defaults, $settings );
	}

	/**
	 * Restituisce i tag HTML consentiti per i campi degli annunci pubblicitari (allowlist).
	 *
	 * Consente script solo se provvisti di attributo src.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array    Tag ed attributi consentiti.
	 */
	private function get_allowed_html(): array {
		return [
			'a'      => [
				'href'   => true,
				'title'  => true,
				'target' => true,
				'class'  => true,
				'id'     => true,
				'style'  => true,
				'rel'    => true,
			],
			'img'    => [
				'src'    => true,
				'alt'    => true,
				'width'  => true,
				'height' => true,
				'class'  => true,
				'id'     => true,
				'style'  => true,
			],
			'div'    => [
				'class'  => true,
				'id'     => true,
				'style'  => true,
			],
			'span'   => [
				'class'  => true,
				'id'     => true,
				'style'  => true,
			],
			'script' => [
				'src'    => true,
				'async'  => true,
				'defer'  => true,
				'type'   => true,
			],
		];
	}
}
