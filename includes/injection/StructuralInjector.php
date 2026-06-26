<?php
namespace SmartAdInserter\Injection;

use SmartAdInserter\SmartAdInserterSettings;

/**
 * Gestisce l'iniezione degli annunci posizionati lato server nelle diverse posizioni.
 *
 * Questa classe implementa l'interfaccia AdInjectorInterface e si occupa di
 * registrare gli hook di WordPress necessari per inserire i placeholder con altezze minime
 * pre-allocate per azzerare il CLS nel frontend.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
class StructuralInjector implements AdInjectorInterface {

	/**
	 * Flag per evitare la doppia iniezione del Masthead.
	 *
	 * @since    1.0.0
	 * @var      bool
	 */
	private $masthead_injected = false;

	/**
	 * Esegue l'analisi e l'iniezione dei banner pubblicitari.
	 * Nel modello guidato dagli hook di WordPress, questo metodo restituisce
	 * il soggetto originario invariato.
	 *
	 * @since    1.0.0
	 * @param    string    $subject    L'HTML originario.
	 * @return   string                L'HTML originario.
	 */
	public function inject( string $subject ): string {
		return $subject;
	}

	/**
	 * Registra tutti gli hook di WordPress per l'iniezione automatica dei banner.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_hooks() {
		// A. Masthead
		add_action( 'wp_body_open', [ $this, 'inject_masthead_action' ] );
		add_filter( 'the_content', [ $this, 'inject_masthead_content_filter' ], 9 );
		add_action( 'wp_footer', [ $this, 'inject_masthead_footer_fallback' ] );

		// B. Sidebar Top / Sidebar Sticky
		add_action( 'dynamic_sidebar_before', [ $this, 'inject_sidebar_top' ], 10, 2 );
		add_action( 'dynamic_sidebar_after', [ $this, 'inject_sidebar_sticky' ], 10, 2 );

		// C. Home / Archivi — Grid Box
		add_action( 'the_post', [ $this, 'check_grid_injection' ] );

		// D. Articolo Singolo — ATF e BTF
		add_filter( 'the_content', [ $this, 'inject_content_ads' ], 11 );
	}

	/**
	 * Recupera le impostazioni correnti del plugin dal gestore delle impostazioni.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array    Mappa delle impostazioni.
	 */
	private function get_settings(): array {
		$settings_manager = new SmartAdInserterSettings();
		return $settings_manager->get_settings();
	}

	/**
	 * Costruisce il markup HTML del placeholder pubblicitario per azzerare il CLS.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array     $config    Configurazione della singola posizione.
	 * @param    string    $class     Classe CSS specifica della posizione.
	 * @return   string               HTML del placeholder risultante.
	 */
	private function render_placeholder( array $config, string $class = '' ): string {
		$desktop_height = isset( $config['min_height_desktop'] ) ? intval( $config['min_height_desktop'] ) : 0;
		$mobile_height  = isset( $config['min_height_mobile'] ) ? intval( $config['min_height_mobile'] ) : 0;
		$banner_code    = isset( $config['code'] ) ? wp_kses_post( $config['code'] ) : '';

		$class_attr = 'sai-placeholder';
		if ( ! empty( $class ) ) {
			$class_attr .= ' ' . esc_attr( $class );
		}

		$style_attr = sprintf( 'min-height:%dpx;', $desktop_height );
		if ( ! empty( $config['override_css'] ) ) {
			$style_attr .= ' ' . esc_attr( $config['override_css'] );
		}

		$data_attrs = sprintf( 'data-sai-mobile-height="%d"', $mobile_height );
		
		if ( isset( $config['use_default_placement'] ) && $config['use_default_placement'] === false && ! empty( $config['target_element'] ) ) {
			$data_attrs .= sprintf( ' data-sai-target-element="%s"', esc_attr( $config['target_element'] ) );
		}

		return sprintf(
			'<div class="%s" style="%s" %s>%s</div>',
			$class_attr,
			$style_attr,
			$data_attrs,
			$banner_code
		);
	}

	/**
	 * Inietta il Masthead all'inizio del body della pagina.
	 *
	 * @since    1.0.0
	 * @return   void
	 * @hook     wp_body_open
	 */
	public function inject_masthead_action() {
		if ( $this->masthead_injected ) {
			return;
		}

		$settings = $this->get_settings();
		$config   = $settings['positions']['masthead'] ?? [];

		if ( ! empty( $config['active'] ) ) {
			$placeholder = $this->render_placeholder( $config, 'sai-masthead' );
			echo $placeholder;
			$this->masthead_injected = true;
		}
	}

	/**
	 * Filtra il contenuto dell'articolo per inserire il Masthead come prepend se non già iniettato.
	 *
	 * @since    1.0.0
	 * @param    string    $content    HTML del contenuto del post.
	 * @return   string                HTML modificato.
	 * @hook     the_content
	 */
	public function inject_masthead_content_filter( $content ) {
		if ( $this->masthead_injected ) {
			return $content;
		}

		$settings = $this->get_settings();
		$config   = $settings['positions']['masthead'] ?? [];

		if ( ! empty( $config['active'] ) && ! empty( $config['use_default_placement'] ) ) {
			if ( is_singular() && in_the_loop() && is_main_query() ) {
				$placeholder = $this->render_placeholder( $config, 'sai-masthead' );
				$content = $placeholder . $content;
				$this->masthead_injected = true;
			}
		}

		return $content;
	}

	/**
	 * Inietta il Masthead nel footer come fallback se use_default_placement è false e non ancora iniettato.
	 *
	 * @since    1.0.0
	 * @return   void
	 * @hook     wp_footer
	 */
	public function inject_masthead_footer_fallback() {
		if ( $this->masthead_injected ) {
			return;
		}

		$settings = $this->get_settings();
		$config   = $settings['positions']['masthead'] ?? [];

		if ( ! empty( $config['active'] ) && empty( $config['use_default_placement'] ) ) {
			$placeholder = $this->render_placeholder( $config, 'sai-masthead' );
			echo $placeholder;
			$this->masthead_injected = true;
		}
	}

	/**
	 * Inietta la pubblicità in cima alla sidebar dinamica attiva.
	 *
	 * @since    1.0.0
	 * @param    string    $index          ID o nome dell'area widget.
	 * @param    bool      $has_widgets    Indica se l'area contiene widget.
	 * @return   void
	 * @hook     dynamic_sidebar_before
	 */
	public function inject_sidebar_top( $index, $has_widgets ) {
		$settings = $this->get_settings();
		$config   = $settings['positions']['sidebar_top'] ?? [];

		if ( empty( $config['active'] ) ) {
			return;
		}

		$target_sidebar = ! empty( $config['custom_selector'] ) ? $config['custom_selector'] : 'sidebar';
		if ( strpos( strtolower( $index ), strtolower( $target_sidebar ) ) !== false || $index === $target_sidebar ) {
			$placeholder = $this->render_placeholder( $config, 'sai-sidebar-top' );
			echo $placeholder;
		}
	}

	/**
	 * Inietta la pubblicità in fondo alla sidebar dinamica attiva (Sticky).
	 *
	 * @since    1.0.0
	 * @param    string    $index          ID o nome dell'area widget.
	 * @param    bool      $has_widgets    Indica se l'area contiene widget.
	 * @return   void
	 * @hook     dynamic_sidebar_after
	 */
	public function inject_sidebar_sticky( $index, $has_widgets ) {
		$settings = $this->get_settings();
		$config   = $settings['positions']['sidebar_sticky'] ?? [];

		if ( empty( $config['active'] ) ) {
			return;
		}

		$target_sidebar = ! empty( $config['custom_selector'] ) ? $config['custom_selector'] : 'sidebar';
		if ( strpos( strtolower( $index ), strtolower( $target_sidebar ) ) !== false || $index === $target_sidebar ) {
			$placeholder = $this->render_placeholder( $config, 'sai-sidebar-sticky' );
			echo $placeholder;
		}
	}

	/**
	 * Controlla l'indice dei post nel loop principale per iniettare il banner in griglia.
	 *
	 * @since    1.0.0
	 * @param    \WP_Post  $post    Oggetto del post corrente nel loop.
	 * @return   void
	 * @hook     the_post
	 */
	public function check_grid_injection( $post ) {
		global $wp_query;
		if ( ! $wp_query->is_main_query() || ! in_the_loop() ) {
			return;
		}

		if ( is_home() || is_front_page() || is_archive() ) {
			$position_key = ( is_home() || is_front_page() ) ? 'grid_home' : 'grid_archive';
			$settings     = $this->get_settings();
			$config       = $settings['positions'][ $position_key ] ?? [];

			if ( ! empty( $config['active'] ) ) {
				$frequency = isset( $config['frequency'] ) ? intval( $config['frequency'] ) : 3;
				if ( $wp_query->current_post === ( $frequency - 1 ) ) {
					add_filter( 'the_content', [ $this, 'inject_grid_placeholder' ] );
					add_filter( 'the_excerpt', [ $this, 'inject_grid_placeholder' ] );
				}
			}
		}
	}

	/**
	 * Inietta il placeholder Grid Box in coda al contenuto del post target.
	 *
	 * @since    1.0.0
	 * @param    string    $content    HTML del contenuto o del riassunto del post.
	 * @return   string                HTML modificato con il placeholder.
	 * @hook     the_content, the_excerpt
	 */
	public function inject_grid_placeholder( $content ) {
		remove_filter( 'the_content', [ $this, 'inject_grid_placeholder' ] );
		remove_filter( 'the_excerpt', [ $this, 'inject_grid_placeholder' ] );

		if ( is_home() || is_front_page() || is_archive() ) {
			$position_key = ( is_home() || is_front_page() ) ? 'grid_home' : 'grid_archive';
			$settings     = $this->get_settings();
			$config       = $settings['positions'][ $position_key ] ?? [];

			$placeholder = $this->render_placeholder( $config, 'sai-' . str_replace( '_', '-', $position_key ) );
			return $content . $placeholder;
		}

		return $content;
	}

	/**
	 * Inietta ATF e BTF all'inizio ed alla fine del contenuto dell'articolo singolo.
	 *
	 * @since    1.0.0
	 * @param    string    $content    HTML del contenuto dell'articolo.
	 * @return   string                HTML modificato con i placeholder.
	 * @hook     the_content
	 */
	public function inject_content_ads( $content ) {
		if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$settings = $this->get_settings();

		// Iniezione ATF (Above The Fold)
		$atf_config = $settings['positions']['atf'] ?? [];
		if ( ! empty( $atf_config['active'] ) ) {
			$placeholder_atf = $this->render_placeholder( $atf_config, 'sai-atf' );
			$content = $placeholder_atf . $content;
		}

		// Iniezione BTF (Below The Fold)
		$btf_config = $settings['positions']['btf'] ?? [];
		if ( ! empty( $btf_config['active'] ) ) {
			$placeholder_btf = $this->render_placeholder( $btf_config, 'sai-btf' );
			$content = $content . $placeholder_btf;
		}

		return $content;
	}
}
