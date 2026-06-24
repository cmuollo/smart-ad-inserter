<?php
namespace SmartAdInserter;

/**
 * La classe core principale del plugin.
 *
 * Questa classe viene utilizzata per impostare l'internazionalizzazione, gli hook di backend
 * per l'amministrazione, e gli hook di frontend per il caricamento delle posizioni pubblicitarie.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes
 * @author     Carmine Muollo
 */
class SmartAdInserter {

	/**
	 * Gestore (Loader) per la registrazione centralizzata di azioni e filtri.
	 *
	 * @since    1.0.0
	 * @var      SmartAdInserterLoader    $loader    Memorizza e registra gli hook.
	 */
	protected $loader;

	/**
	 * L'identificatore univoco del plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    L'ID univoco del plugin.
	 */
	protected $plugin_name;

	/**
	 * La versione corrente del plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    La versione del plugin.
	 */
	protected $version;

	/**
	 * Costruttore e inizializzazione del plugin.
	 *
	 * Imposta il nome, la versione, carica le dipendenze, definisce la lingua locale,
	 * e registra i vari hook per i moduli amministrativi, pubblici e REST API.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'smart-ad-inserter';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_rest_api_hooks();
	}

	/**
	 * Carica le classi di base e le dipendenze interne del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new SmartAdInserterLoader();
	}

	/**
	 * Imposta la lingua locale ed i file di internazionalizzazione.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new SmartAdInserteri18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_textdomain' );
	}

	/**
	 * Registra tutti gli hook associati all'area di amministrazione (backend).
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Admin\SmartAdInserterAdmin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
	}

	/**
	 * Registra tutti gli hook associati all'area pubblica del sito (frontend).
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new PublicModule\SmartAdInserterPublic( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'insert_global_head_scripts' );

		// Aggancio delle strategie di iniezione differita
		$this->loader->add_filter( 'the_content', $plugin_public, 'inject_content_ads' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'setup_structural_ads_buffer' );
	}

	/**
	 * Registra gli hook associati alle API REST personalizzate del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_rest_api_hooks() {
		$plugin_rest = new SmartAdInserterRest();
		$this->loader->add_action( 'rest_api_init', $plugin_rest, 'register_routes' );
	}

	/**
	 * Esegue il loader per agganciare le funzioni registrate a WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Restituisce il nome univoco identificatore del plugin.
	 *
	 * @since     1.0.0
	 * @return    string    Il nome del plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Restituisce la versione del plugin.
	 *
	 * @since     1.0.0
	 * @return    string    La versione del plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
// Alias di compatibilità globale per l'inizializzazione
class_alias( 'SmartAdInserter\\SmartAdInserter', 'SmartAdInserter' );
