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
		// Recupera le impostazioni correnti già salvate a database per preservarle
		$existing_settings = get_option( $this->option_key, [] );
		if ( ! is_array( $existing_settings ) ) {
			$existing_settings = [];
		}

		$sanitized_settings = $existing_settings;

		// Head Scripts — solo se esplicitamente passato nel payload
		if ( isset( $settings['global_scripts'] ) ) {
			$raw_scripts = $settings['global_scripts'];
			$sanitized_settings['global_scripts'] = current_user_can( 'unfiltered_html' )
				? wp_unslash( $raw_scripts )
				: wp_kses( wp_unslash( $raw_scripts ), [] );
		}

		if ( isset( $settings['contexts'] ) && is_array( $settings['contexts'] ) ) {
			if ( ! isset( $sanitized_settings['contexts'] ) || ! is_array( $sanitized_settings['contexts'] ) ) {
				$sanitized_settings['contexts'] = [];
			}

			foreach ( $settings['contexts'] as $context_id => $context_data ) {
				if ( ! isset( $sanitized_settings['contexts'][ $context_id ] ) || ! is_array( $sanitized_settings['contexts'][ $context_id ] ) ) {
					$sanitized_settings['contexts'][ $context_id ] = [ 'positions' => [] ];
				}

				if ( isset( $context_data['positions'] ) && is_array( $context_data['positions'] ) ) {
					if ( ! isset( $sanitized_settings['contexts'][ $context_id ]['positions'] ) || ! is_array( $sanitized_settings['contexts'][ $context_id ]['positions'] ) ) {
						$sanitized_settings['contexts'][ $context_id ]['positions'] = [];
					}

					foreach ( $context_data['positions'] as $position_id => $data ) {
						if ( ! is_array( $data ) ) {
							continue;
						}

						// Se non esiste ancora questa posizione, creala
						if ( ! isset( $sanitized_settings['contexts'][ $context_id ]['positions'][ $position_id ] ) || ! is_array( $sanitized_settings['contexts'][ $context_id ]['positions'][ $position_id ] ) ) {
							$sanitized_settings['contexts'][ $context_id ]['positions'][ $position_id ] = [];
						}

						$current_pos_settings = $sanitized_settings['contexts'][ $context_id ]['positions'][ $position_id ];

						// Sanitizza ed unisci solo le chiavi presenti nel payload
						if ( isset( $data['active'] ) ) {
							$current_pos_settings['active'] = (bool) $data['active'];
						}
						if ( isset( $data['code'] ) ) {
							$current_pos_settings['code'] = wp_kses( wp_unslash( $data['code'] ), self::allowed_html() );
						}
						if ( isset( $data['min_height_desktop'] ) ) {
							$current_pos_settings['min_height_desktop'] = absint( $data['min_height_desktop'] );
						}
						if ( isset( $data['min_height_mobile'] ) ) {
							$current_pos_settings['min_height_mobile'] = absint( $data['min_height_mobile'] );
						}
						if ( isset( $data['custom_selector'] ) ) {
							$current_pos_settings['custom_selector'] = sanitize_text_field( wp_unslash( $data['custom_selector'] ) );
						}
						if ( isset( $data['use_default_placement'] ) ) {
							$current_pos_settings['use_default_placement'] = (bool) $data['use_default_placement'];
						}
						if ( isset( $data['override_css'] ) ) {
							$current_pos_settings['override_css'] = self::sanitize_css( wp_unslash( $data['override_css'] ) );
						}
						if ( isset( $data['target_element'] ) ) {
							$current_pos_settings['target_element'] = sanitize_text_field( wp_unslash( $data['target_element'] ) );
						}
						if ( isset( $data['frequency'] ) ) {
							$current_pos_settings['frequency'] = absint( $data['frequency'] );
						}
						if ( isset( $data['footer_position'] ) ) {
							$current_pos_settings['footer_position'] = in_array( $data['footer_position'], [ 'before_footer', 'after_footer' ], true ) ? $data['footer_position'] : 'before_footer';
						}
						if ( isset( $data['use_global_config'] ) ) {
							$current_pos_settings['use_global_config'] = (bool) $data['use_global_config'];
						}
						if ( isset( $data['max_insertions'] ) ) {
							$current_pos_settings['max_insertions'] = absint( $data['max_insertions'] );
						}
						if ( isset( $data['words_interval'] ) ) {
							$current_pos_settings['words_interval'] = absint( $data['words_interval'] );
						}
						if ( isset( $data['avoid_btf_single_block'] ) ) {
							$current_pos_settings['avoid_btf_single_block'] = (bool) $data['avoid_btf_single_block'];
						}
						if ( isset( $data['exclude_blockquote'] ) ) {
							$current_pos_settings['exclude_blockquote'] = (bool) $data['exclude_blockquote'];
						}
						if ( isset( $data['excluded_container_tokens'] ) ) {
							$current_pos_settings['excluded_container_tokens'] = implode( ', ', self::sanitizeSelectorTokens( $data['excluded_container_tokens'] ) );
						}

						$sanitized_settings['contexts'][ $context_id ]['positions'][ $position_id ] = $current_pos_settings;
					}
				}
			}
		}

		// Pulisce la cache delle posizioni strutturali (Transients)
		delete_transient( 'sai_structural_ads_locations' );

		$updated = update_option( $this->option_key, $sanitized_settings );
		return $updated || get_option( $this->option_key ) === $sanitized_settings;
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
		// Migrazione/Backward compatibility: se ci sono impostazioni vecchie tracciate come 'positions' al primo livello,
		// spostiamole all'interno del contesto 'global' per non perdere i dati esistenti.
		if ( ! isset( $settings['contexts'] ) && isset( $settings['positions'] ) ) {
			$settings['contexts'] = [
				'global' => [
					'positions' => $settings['positions']
				]
			];
			unset( $settings['positions'] );
		}

		$position_default = [
			'active'                    => false,
			'code'                      => '',
			'min_height_desktop'        => 250,
			'min_height_mobile'         => 100,
			'custom_selector'           => '',
			'use_default_placement'     => true,
			'override_css'              => '',
			'target_element'            => '',
			'frequency'                 => 0,
			'footer_position'           => 'before_footer',
			'use_global_config'         => true,
			'max_insertions'            => 3,
			'words_interval'            => 150,
			'avoid_btf_single_block'    => true,
			'exclude_blockquote'        => true,
			'excluded_container_tokens' => '',
		];

		$defaults = [
			'global_scripts' => '',
			'contexts'       => [
				'global'  => [
					'positions' => [
						'masthead'       => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => false ] ),
						'footer'         => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => false ] ),
						'sidebar_top'    => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 0, 'use_global_config' => false ] ),
						'sidebar_sticky' => array_merge( $position_default, [ 'min_height_desktop' => 600, 'min_height_mobile' => 0, 'use_global_config' => false ] ),
					]
				],
				'home'    => [
					'positions' => [
						'masthead'       => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => true ] ),
						'footer'         => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => true ] ),
						'sidebar_top'    => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 0, 'use_global_config' => true ] ),
						'sidebar_sticky' => array_merge( $position_default, [ 'min_height_desktop' => 600, 'min_height_mobile' => 0, 'use_global_config' => true ] ),
						'grid_home'      => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 250, 'use_global_config' => false, 'target_element' => '.post-card', 'frequency' => 3 ] ),
					]
				],
				'single'  => [
					'positions' => [
						'masthead'       => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => true ] ),
						'footer'         => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => true ] ),
						'sidebar_top'    => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 0, 'use_global_config' => true ] ),
						'sidebar_sticky' => array_merge( $position_default, [ 'min_height_desktop' => 600, 'min_height_mobile' => 0, 'use_global_config' => true ] ),
						'atf'            => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 250, 'use_global_config' => false ] ),
						'btf'            => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 250, 'use_global_config' => false ] ),
						'in_text'        => array_merge( $position_default, [
							'min_height_desktop'     => 250,
							'min_height_mobile'      => 250,
							'use_global_config'      => false,
							'max_insertions'         => 3,
							'words_interval'         => 150,
							'avoid_btf_single_block' => true,
							'exclude_blockquote'     => true,
						] ),
					]
				],
				'archive' => [
					'positions' => [
						'masthead'     => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => true ] ),
						'footer'       => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 100, 'use_global_config' => true ] ),
						'sidebar_top'    => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 0, 'use_global_config' => true ] ),
						'sidebar_sticky' => array_merge( $position_default, [ 'min_height_desktop' => 600, 'min_height_mobile' => 0, 'use_global_config' => true ] ),
						'grid_archive' => array_merge( $position_default, [ 'min_height_desktop' => 250, 'min_height_mobile' => 250, 'use_global_config' => false, 'target_element' => '.post-card', 'frequency' => 3 ] ),
					]
				],
			]
		];

		return array_replace_recursive( $defaults, $settings );
	}

	/**
	 * Sanitizza una lista di selettori esclusi (Classi, ID o Tag semplici).
	 *
	 * Accetta classi semplici (.classe), ID semplici (#id) o tag semplici (blockquote, aside, nav).
	 * Scarta silenziosamente combinatori, pseudo-classi, pseudo-elementi ed attributi CSS.
	 *
	 * @since    1.0.0
	 * @param    string $input La stringa inserita dall'utente.
	 * @return   array         Elenco di token validati e sanitizzati.
	 */
	public static function sanitizeSelectorTokens( string $input ): array {
		$raw_tokens = explode( ',', $input );
		$valid_tokens = [];

		foreach ( $raw_tokens as $raw_token ) {
			$token = trim( $raw_token );
			if ( $token === '' ) {
				continue;
			}

			// Se inizia con . o #, si tratta di una classe o di un ID
			$first_char = $token[0];
			if ( $first_char === '.' || $first_char === '#' ) {
				$name = substr( $token, 1 );

				// Consente solo caratteri alfanumerici, trattini e underscores
				if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $name ) ) {
					continue;
				}

				if ( $first_char === '.' ) {
					$sanitized_name = function_exists( 'sanitize_html_class' ) 
						? sanitize_html_class( $name ) 
						: preg_replace( '/[^a-zA-Z0-9\-_]/', '', $name );
					
					if ( ! empty( $sanitized_name ) ) {
						$valid_tokens[] = '.' . $sanitized_name;
					}
				} else {
					$sanitized_name = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $name );
					if ( ! empty( $sanitized_name ) ) {
						$valid_tokens[] = '#' . $sanitized_name;
					}
				}
			} else {
				// Altrimenti, deve essere un tag semplice (solo lettere e numeri, es. blockquote, aside, nav, div)
				if ( preg_match( '/^[a-zA-Z0-9]+$/', $token ) ) {
					$valid_tokens[] = strtolower( $token );
				}
			}
		}

		return $valid_tokens;
	}

	/**
	 * Mantiene retrocompatibilità per la sanitizzazione dei token.
	 *
	 * @since    1.0.0
	 * @param    string $input La stringa di input.
	 * @return   array
	 */
	public static function sanitize_exclusion_tokens( string $input ): array {
		return self::sanitizeSelectorTokens( $input );
	}
}
