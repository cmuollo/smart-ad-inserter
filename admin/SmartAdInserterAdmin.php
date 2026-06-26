<?php
namespace SmartAdInserter\Admin;

/**
 * Gestisce tutte le funzionalità specifiche del pannello di amministrazione (backend).
 *
 * Si occupa di caricare i fogli di stile, gli script JavaScript per il pannello amministrativo
 * e di registrare la voce del sottomenu di configurazione in WordPress.
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

		wp_localize_script(
			$this->plugin_name . '-admin',
			'smartAdInserter',
			[
				'restUrl' => esc_url_raw( rest_url( 'smart-ad-inserter/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			]
		);
	}

	/**
	 * Renderizza la pagina admin del plugin caricando la view principale.
	 * Verificata la capability manage_options prima del rendering.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'smart-ad-inserter' ) );
		}
		require_once plugin_dir_path( __FILE__ ) . 'partials/smart-ad-inserter-admin-display.php';
	}
}
