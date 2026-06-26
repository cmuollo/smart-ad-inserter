<?php
namespace SmartAdInserter;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Controller delle API REST personalizzate per il plugin Smart Ad Inserter.
 *
 * Registra le rotte dell'API per il recupero ed il salvataggio asincrono delle impostazioni
 * e delega la persistenza dei dati alla classe SmartAdInserterSettings.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserterRest {

	/**
	 * Il namespace delle API del plugin.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $namespace = 'smart-ad-inserter/v1';

	/**
	 * Il nome della risorsa REST.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $rest_base = 'settings';

	/**
	 * Istanza del gestore delle impostazioni.
	 *
	 * @since    1.0.0
	 * @var      SmartAdInserterSettings
	 */
	protected $settings_manager;

	/**
	 * Inizializza il controller REST configurando il settings manager.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->settings_manager = new SmartAdInserterSettings();
	}

	/**
	 * Registra le rotte REST API personalizzate per il plugin.
	 *
	 * Swagger Spec Mapping:
	 * - GET  /settings -> get_settings()
	 * - POST /settings -> save_settings()
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => function (): bool {
						return current_user_can( 'manage_options' );
					},
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_settings' ],
					'permission_callback' => function (): bool {
						return current_user_can( 'manage_options' );
					},
				],
			]
		);
	}

	/**
	 * Recupera le impostazioni correnti del plugin.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Dati della richiesta REST.
	 * @return   WP_REST_Response               Oggetto risposta REST contenente l'array di impostazioni.
	 */
	public function get_settings( WP_REST_Request $request ) {
		$settings = $this->settings_manager->get_settings();
		return rest_ensure_response( $settings );
	}

	/**
	 * Salva le nuove impostazioni fornite nella richiesta.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Dati della richiesta REST contenenti il payload JSON delle impostazioni.
	 * @return   WP_REST_Response               Oggetto risposta REST indicante il successo dell'operazione.
	 */
	public function save_settings( WP_REST_Request $request ) {
		$params = $request->get_json_params();
		$settings = is_array( $params ) ? $params : [];

		$success = $this->settings_manager->save_settings( $settings );

		return rest_ensure_response( [ 'success' => $success ] );
	}
}
