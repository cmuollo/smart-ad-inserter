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
	 * Restituisce l'allowlist HTML per la sanitizzazione dei campi banner.
	 * Compatibile con wp_kses(). Tag <script> esclusi per design WordPress.
	 *
	 * @since    1.0.0
	 * @return   array<string, array<string, bool>>
	 */
	private static function allowed_html(): array {
		return [
			'a'    => [ 'href' => true, 'target' => true, 'rel' => true ],
			'img'  => [ 'src' => true, 'alt' => true, 'width' => true, 'height' => true ],
			'div'  => [ 'class' => true, 'id' => true, 'style' => true ],
			'span' => [ 'class' => true, 'id' => true, 'style' => true ],
			'ins'  => [ 'class' => true, 'style' => true, 'data-ad-client' => true, 'data-ad-slot' => true ],
		];
	}

	/**
	 * Restituisce l'elenco delle proprietà CSS permesse per il wrapper.
	 *
	 * @since    1.0.0
	 * @return   array<string>
	 */
	private static function get_allowed_css_properties(): array {
		return [
			'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
			'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
			'background', 'background-color', 'background-image', 'background-position', 'background-repeat', 'background-size',
			'border', 'border-top', 'border-bottom', 'border-left', 'border-right', 'border-color', 'border-style', 'border-width', 'border-radius',
			'display', 'visibility', 'opacity',
			'text-align', 'vertical-align',
			'float', 'clear',
			'width', 'min-width', 'max-width',
			'height', 'min-height', 'max-height',
			'position', 'top', 'bottom', 'left', 'right', 'z-index',
			'box-shadow', 'box-sizing', 'overflow',
			'color', 'font-family', 'font-size', 'font-weight', 'line-height'
		];
	}

	/**
	 * Sanitizza una stringa CSS consentendo solo proprietà valide e sicure.
	 *
	 * Scarta silenziosamente le dichiarazioni malformate o non permesse,
	 * garantendo che il CSS risultante sia sintatticamente corretto e sicuro.
	 *
	 * @since    1.0.0
	 * @param    string $css_string La stringa CSS grezza fornita dall'utente.
	 * @return   string             La stringa CSS sanitizzata e formattata.
	 */
	public static function sanitize_css( string $css_string ): string {
		$css_string = trim( $css_string );
		if ( empty( $css_string ) ) {
			return '';
		}

		$allowed_properties = self::get_allowed_css_properties();
		$sanitized_declarations = [];

		// Divide le dichiarazioni per ";"
		$declarations = explode( ';', $css_string );

		foreach ( $declarations as $declaration ) {
			$declaration = trim( $declaration );
			if ( empty( $declaration ) ) {
				continue;
			}

			// Deve contenere esattamente un due punti ":" per essere una dichiarazione valida
			$parts = explode( ':', $declaration, 2 );
			if ( count( $parts ) !== 2 ) {
				continue;
			}

			$property = strtolower( trim( $parts[0] ) );
			$value    = trim( $parts[1] );

			// 1. Verifica che la proprietà sia nell'allowlist
			if ( ! in_array( $property, $allowed_properties, true ) ) {
				continue;
			}

			// 2. Sanitizza e valida il valore per evitare iniezioni e caratteri di rottura
			if ( preg_match( '/[{};\\\\<>]/', $value ) ) {
				continue;
			}

			// Rimuove eventuali tentativi di url javascript o espressioni CSS vecchie
			if ( preg_match( '/\b(expression|javascript|eval|behaviour)\b/i', $value ) ) {
				continue;
			}

			$sanitized_declarations[] = "$property: $value";
		}

		return ! empty( $sanitized_declarations ) ? implode( '; ', $sanitized_declarations ) . ';' : '';
	}

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

		// Head Scripts — solo admin con unfiltered_html possono salvare tag script
		$raw_scripts = isset( $settings['global_scripts'] ) ? $settings['global_scripts'] : '';
		$sanitized_settings['global_scripts'] = current_user_can( 'unfiltered_html' )
			? wp_unslash( $raw_scripts )
			: wp_kses( wp_unslash( $raw_scripts ), [] );

		$sanitized_settings['positions'] = [];
		if ( isset( $settings['positions'] ) && is_array( $settings['positions'] ) ) {
			foreach ( $settings['positions'] as $position_id => $data ) {
				$sanitized_settings['positions'][ $position_id ] = [
					'active'                => isset( $data['active'] ) ? (bool) $data['active'] : false,
					'code'                  => isset( $data['code'] ) ? wp_kses( wp_unslash( $data['code'] ), self::allowed_html() ) : '',
					'min_height_desktop'    => isset( $data['min_height_desktop'] ) ? absint( $data['min_height_desktop'] ) : 0,
					'min_height_mobile'     => isset( $data['min_height_mobile'] ) ? absint( $data['min_height_mobile'] ) : 0,
					'custom_selector'       => isset( $data['custom_selector'] ) ? sanitize_text_field( wp_unslash( $data['custom_selector'] ) ) : '',
					'use_default_placement' => isset( $data['use_default_placement'] ) ? (bool) $data['use_default_placement'] : false,
					'override_css'          => isset( $data['override_css'] ) ? self::sanitize_css( wp_unslash( $data['override_css'] ) ) : '',
					'target_element'        => isset( $data['target_element'] ) ? sanitize_text_field( wp_unslash( $data['target_element'] ) ) : '',
					'frequency'             => isset( $data['frequency'] ) ? absint( $data['frequency'] ) : 0,
					'footer_position'       => isset( $data['footer_position'] ) && in_array( $data['footer_position'], [ 'before_footer', 'after_footer' ], true ) ? $data['footer_position'] : 'before_footer',
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
				'footer'         => [
					'active'                => false,
					'code'                  => '',
					'min_height_desktop'    => 250,
					'min_height_mobile'     => 100,
					'custom_selector'       => '',
					'use_default_placement' => true,
					'override_css'          => '',
					'target_element'        => '',
					'frequency'             => 0,
					'footer_position'       => 'before_footer',
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
}
