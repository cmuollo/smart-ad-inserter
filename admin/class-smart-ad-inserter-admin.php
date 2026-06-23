<?php
namespace SmartAdInserter\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for backend styles, scripts,
 * menus, and REST API.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/admin
 * @author     Carmine Muollo
 */
class SmartAdInserterAdmin {

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
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
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
	 * Register the JavaScript for the admin area.
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
	 * Add a menu item to the settings menu in the admin dashboard.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			'Smart Ad Inserter Settings',
			'Smart Ad Inserter',
			'manage_options',
			$this->plugin_name,
			[ $this, 'display_settings_page' ]
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/smart-ad-inserter-admin-display.php';
	}

	/**
	 * Register REST API routes for saving and retrieving settings.
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
	 * Retrieve settings callback.
	 *
	 * @since    1.0.0
	 */
	public function get_settings() {
		$settings = get_option( 'smart_ad_inserter_settings', [] );
		return rest_ensure_response( $settings );
	}

	/**
	 * Save settings callback.
	 *
	 * @since    1.0.0
	 */
	public function save_settings( \WP_REST_Request $request ) {
		$settings = $request->get_json_params();

		// Clean structural cache transients
		delete_transient( 'sai_structural_ads_locations' );

		update_option( 'smart_ad_inserter_settings', $settings );
		return rest_ensure_response( [ 'success' => true ] );
	}

	/**
	 * Check capability of the user to manage settings.
	 *
	 * @since    1.0.0
	 */
	public function check_admin_permissions() {
		return current_user_can( 'manage_options' );
	}
}
