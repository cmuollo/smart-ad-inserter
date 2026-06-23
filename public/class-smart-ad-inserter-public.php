<?php
namespace SmartAdInserter\PublicModule;

use SmartAdInserter\Injection\ContentInjector;
use SmartAdInserter\Injection\StructuralInjector;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for frontend assets,
 * scripts injection, and the ad-injection strategies.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/public
 * @author     Carmine Muollo
 */
class SmartAdInserterPublic {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of the plugin.
	 * @param    string    $version           The version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
	 * Register the JavaScript for the public-facing side of the site.
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
	 * Insert global scripts in the <head> of the site.
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
	 * Inject content-based ads (ATF, BTF) into the article text.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The post content.
	 * @return   string                The modified post content.
	 */
	public function inject_content_ads( $content ) {
		// Only run on main query single posts
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
	 * Start output buffering for structural ad insertion.
	 *
	 * @since    1.0.0
	 */
	public function setup_structural_ads_buffer() {
		if ( is_admin() || wp_doing_ajax() || is_feed() ) {
			return;
		}

		ob_start( [ $this, 'process_structural_ads' ] );
	}

	/**
	 * Callback for output buffering to process structural ads (Masthead, Sidebar).
	 *
	 * @since    1.0.0
	 * @param    string    $html    The full page HTML output.
	 * @return   string             The modified HTML output.
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
// Map alias for PSR-4 compat
class_alias( 'SmartAdInserter\\PublicModule\\SmartAdInserterPublic', 'SmartAdInserterPublic' );
