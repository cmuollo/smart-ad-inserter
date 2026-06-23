<?php
namespace SmartAdInserter\PublicModule;

use SmartAdInserter\Injection\ContentInjector;
use SmartAdInserter\Injection\StructuralInjector;

/**
 * Gestisce tutte le funzionalità pubbliche del frontend del sito.
 *
 * Registra gli script, i fogli di stile ed avvia i motori di iniezione
 * pubblicitaria basati sul pattern Strategy.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/public
 * @author     Carmine Muollo
 */
class SmartAdInserterPublic {

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
	 * Registra ed accoda i fogli di stile per il frontend.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name . '-public',
			plugin_dir_url( __FILE__ ) . 'css/smart-ad-inserter-public.css',
			[],
			$this->version,
			'all'
		);
	}

	/**
	 * Registra ed accoda gli script Javascript per il frontend.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-public',
			plugin_dir_url( __FILE__ ) . 'js/smart-ad-inserter-public.js',
			[],
			$this->version,
			false
		);
	}

	/**
	 * Inserisce gli script globali configurati all'interno della sezione <head> della pagina.
	 *
	 * @since    1.0.0
	 */
	public function insert_global_head_scripts() {
		$settings = get_option( 'smart_ad_inserter_settings', [] );
		if ( ! empty( $settings['global_scripts'] ) ) {
			echo $settings['global_scripts'] . "\n";
		}
	}

	/**
	 * Intercetta il contenuto dell'articolo e vi inserisce gli annunci (strategia ATF e BTF).
	 *
	 * @since    1.0.0
	 * @param    string    $content    L'HTML del contenuto dell'articolo corrente.
	 * @return   string                Il contenuto modificato con gli annunci pubblicitari.
	 */
	public function inject_content_ads( $content ) {
		// Esegui la logica solo per gli articoli singoli nel ciclo principale (Main Loop) di WordPress
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$settings = get_option( 'smart_ad_inserter_settings', [] );
		if ( empty( $settings['positions'] ) ) {
			return $content;
		}

		if ( class_exists( 'SmartAdInserter\\Injection\\ContentInjector' ) ) {
			$injector = new ContentInjector( $settings['positions'] );
			return $injector->inject( $content );
		}

		return $content;
	}

	/**
	 * Avvia l'Output Buffering per intercettare l'intero HTML prima dell'invio.
	 * Utilizzato per iniettare banner strutturali fuori dal the_content (es. Masthead, Sidebar).
	 *
	 * @since    1.0.0
	 */
	public function setup_structural_ads_buffer() {
		// Non intercettare richieste di backend, AJAX o feed non-HTML
		if ( is_admin() || wp_doing_ajax() || is_feed() ) {
			return;
		}

		ob_start( [ $this, 'process_structural_ads' ] );
	}

	/**
	 * Callback dell'Output Buffering. Esegue il parsing completo per l'iniezione strutturale.
	 *
	 * @since    1.0.0
	 * @param    string    $html    L'HTML completo della pagina.
	 * @return   string             L'HTML modificato.
	 */
	public function process_structural_ads( $html ) {
		if ( empty( $html ) || stripos( $html, '<html' ) === false ) {
			return $html;
		}

		$settings = get_option( 'smart_ad_inserter_settings', [] );
		if ( empty( $settings['positions'] ) ) {
			return $html;
		}

		if ( class_exists( 'SmartAdInserter\\Injection\\StructuralInjector' ) ) {
			$injector = new StructuralInjector( $settings['positions'] );
			return $injector->inject( $html );
		}

		return $html;
	}
}
// Alias di compatibilità per autoloading PSR-4
class_alias( 'SmartAdInserter\\PublicModule\\SmartAdInserterPublic', 'SmartAdInserterPublic' );
