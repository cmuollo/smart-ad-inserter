<?php
namespace SmartAdInserter\Admin;

/**
 * Gestisce tutte le funzionalità specifiche del pannello di amministrazione (backend).
 *
 * Registra i fogli di stile, gli script JavaScript, crea la pagina delle impostazioni
 * ed inizializza gli endpoint della REST API personalizzata.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/admin
 * @author     Carmine Muollo
 */
class SmartAdInserterAdmin {

	/**
	 * L'ID di questo plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    L'ID del plugin.
	 */
	protected $plugin_name;

	/**
	 * La versione di questo plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    La versione del plugin.
	 */
	protected $version;

	/**
	 * Inizializza le proprietà della classe.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       Il nome univoco del plugin.
	 * @param    string    $version           La versione del plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Registra ed accoda i fogli di stile per l'area amministrativa.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name . '-admin',
			plugin_dir_url( __FILE__ ) . 'css/smart-ad-inserter-admin.css',
			[],
			$this->version,
			'all'
		);
	}

	/**
	 * Registra ed accoda i file Javascript per l'area amministrativa.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-admin',
			plugin_dir_url( __FILE__ ) . 'js/smart-ad-inserter-admin.js',
			[ 'jquery' ],
			$this->version,
			false
		);
	}

	/**
	 * Aggiunge la voce di menu all'interno delle impostazioni di WordPress.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			'Smart Ad Inserter Impostazioni',
			'Smart Ad Inserter',
			'manage_options',
			$this->plugin_name,
			[ $this, 'display_settings_page' ]
		);
	}

	/**
	 * Esegue il rendering della pagina di configurazione.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/smart-ad-inserter-admin-display.php';
	}

	/**
	 * Registra le rotte della REST API personalizzata per il recupero ed il salvataggio asincrono.
	 *
	 * @since    1.0.0
	 */
	public function register_rest_routes() {
		register_rest_route(
			'smart-ad-inserter/v1',
			'/settings',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'check_admin_permissions' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_settings' ],
					'permission_callback' => [ $this, 'check_admin_permissions' ],
				],
			]
		);
	}

	/**
	 * Callback REST per recuperare le impostazioni attuali.
	 *
	 * @since    1.0.0
	 */
	public function get_settings() {
		$settings = get_option( 'smart_ad_inserter_settings', [] );
		return rest_ensure_response( $settings );
	}

	/**
	 * Callback REST per salvare le nuove impostazioni configurate.
	 *
	 * @since    1.0.0
	 */
	public function save_settings( \WP_REST_Request $request ) {
		$settings = $request->get_json_params();

		// Pulisce i transient della cache delle posizioni ad ogni modifica
		delete_transient( 'sai_structural_ads_locations' );

		update_option( 'smart_ad_inserter_settings', $settings );
		return rest_ensure_response( [ 'success' => true ] );
	}

	/**
	 * Verifica che l'utente corrente abbia le autorizzazioni di amministratore.
	 *
	 * @since    1.0.0
	 */
	public function check_admin_permissions() {
		return current_user_can( 'manage_options' );
	}
}
