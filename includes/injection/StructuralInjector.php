<?php
namespace SmartAdInserter\Injection;

use DOMDocument;
use DOMNode;
use DOMXPath;

/**
 * Gestisce l'iniezione degli annunci posizionati all'esterno dell'articolo (Strutturali).
 *
 * Questa classe implementa la strategia concreta per il posizionamento dei banner
 * legati all'interfaccia ed al layout del tema (es. Masthead, Sidebar Top).
 * Utilizza DOMDocument + DOMXPath per manipolare l'HTML completo della pagina web
 * catturato tramite buffering dell'output (Output Buffering).
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
class StructuralInjector implements AdInjectorInterface {

	/**
	 * Mappa delle opzioni di configurazione delle posizioni.
	 *
	 * @since    1.0.0
	 * @var      array
	 */
	protected $settings;

	/**
	 * Inizializza la classe con le opzioni configurate.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $settings = [] ) {
		$this->settings = $settings;
	}

	/**
	 * Esegue l'iniezione in base alle strategie configurate sul layout della pagina.
	 *
	 * @since    1.0.0
	 */
	public function inject( string $html ): string {
		$placeholders = [];
		$placeholder_prefix = '<!--SAI_SCRIPT_TEMPLATE_PLACEHOLDER_';
		$placeholder_suffix = '-->';

		// Estrae temporaneamente i tag script di tipo template (html o template) per evitare la corruzione del parser DOMDocument
		$html_clean = preg_replace_callback(
			'/<script\b[^>]*type=["\'](?:text\/html|text\/template)["\'][^>]*>([\s\S]*?)<\/script>/i',
			function ( $matches ) use ( &$placeholders, $placeholder_prefix, $placeholder_suffix ) {
				$index = count( $placeholders );
				$placeholders[] = $matches[0];
				return $placeholder_prefix . $index . $placeholder_suffix;
			},
			$html
		);

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Forza il caricamento in UTF-8 per evitare la corruzione dei caratteri accentati del tema
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html_clean, LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$xpath    = new DOMXPath( $dom );
		$modified = false;
		$context  = $this->get_current_context();

		// Iniezione Masthead
		$masthead_config = $this->resolve_setting( 'masthead', $context );
		if ( $masthead_config && ! empty( $masthead_config['active'] ) && trim( $masthead_config['code'] ?? '' ) !== '' ) {
			$modified = $this->inject_masthead( $dom, $xpath, $masthead_config ) || $modified;
		}

		// Iniezione Sidebar Top
		$sidebar_top_config = $this->resolve_setting( 'sidebar_top', $context );
		if ( $sidebar_top_config && ! empty( $sidebar_top_config['active'] ) && trim( $sidebar_top_config['code'] ?? '' ) !== '' ) {
			$modified = $this->inject_sidebar_top( $dom, $xpath, $sidebar_top_config ) || $modified;
		}

		// Iniezione Footer
		$footer_config = $this->resolve_setting( 'footer', $context );
		if ( $footer_config && ! empty( $footer_config['active'] ) && trim( $footer_config['code'] ?? '' ) !== '' ) {
			$modified = $this->inject_footer( $dom, $xpath, $footer_config ) || $modified;
		}

		// Iniezione Griglia Home
		$grid_home_config = $this->resolve_setting( 'grid_home', $context );
		if ( $context === 'home' && $grid_home_config && ! empty( $grid_home_config['active'] ) && trim( $grid_home_config['code'] ?? '' ) !== '' ) {
			$modified = $this->inject_grid( $dom, $xpath, 'grid_home', $grid_home_config ) || $modified;
		}

		// Iniezione Griglia Archivio
		$grid_archive_config = $this->resolve_setting( 'grid_archive', $context );
		if ( $context === 'archive' && $grid_archive_config && ! empty( $grid_archive_config['active'] ) && trim( $grid_archive_config['code'] ?? '' ) !== '' ) {
			$modified = $this->inject_grid( $dom, $xpath, 'grid_archive', $grid_archive_config ) || $modified;
		}

		$output = $modified ? $dom->saveHTML() : $html_clean;

		// Ripristina i tag script originali estratti
		foreach ( $placeholders as $index => $original_script ) {
			$output = str_replace( $placeholder_prefix . $index . $placeholder_suffix, $original_script, $output );
		}

		return $output;
	}

	/**
	 * Inietta il contenitore pubblicitario Masthead sotto l'intestazione della pagina.
	 *
	 * Individua l'elemento <header> o classi CSS standard come .site-header.
	 *
	 * @since    1.0.0
	 * @param    DOMDocument $dom    Il documento DOM.
	 * @param    DOMXPath    $xpath  Il DOMXPath.
	 * @param    array       $config La configurazione risolta per la posizione.
	 * @return   bool                Vero in caso di iniezione eseguita, falso altrimenti.
	 */
	private function inject_masthead( DOMDocument $dom, DOMXPath $xpath, array $config ): bool {
		$use_default = ! isset( $config['use_default_placement'] ) || $config['use_default_placement'];
		$header      = null;

		if ( ! $use_default && ! empty( $config['custom_selector'] ) ) {
			$xpath_selector = $this->css_to_xpath( $config['custom_selector'] );
			if ( ! empty( $xpath_selector ) ) {
				$query_result = @$xpath->query( $xpath_selector );
				if ( $query_result !== false ) {
					$header = $query_result->item( 0 );
				}
			}
		}

		if ( ! $header ) {
			$header_result = @$xpath->query( '//header' );
			$header = ( $header_result !== false ) ? $header_result->item( 0 ) : null;
			if ( ! $header ) {
				$header_class_result = @$xpath->query( '//*[contains(@class, "site-header")]' );
				$header = ( $header_class_result !== false ) ? $header_class_result->item( 0 ) : null;
			}
		}

		if ( ! $header || ! $header->parentNode ) {
			return false;
		}

		$ad_code = trim( $config['code'] ?? '' );
		if ( $ad_code === '' ) {
			return false;
		}

		$min_h_desktop = $config['min_height_desktop'] ?? 250;
		$min_h_mobile  = $config['min_height_mobile'] ?? 100;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-masthead' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		if ( ! empty( $config['override_css'] ) ) {
			$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $config['override_css'] );
		}

		$this->append_html( $dom, $wrapper, $ad_code );

		// Inserisce il banner subito dopo il nodo header (come elemento fratello successivo)
		$header->parentNode->insertBefore( $wrapper, $header->nextSibling );
		return true;
	}

	/**
	 * Inietta il contenitore pubblicitario Sidebar Top in cima alla barra laterale.
	 *
	 * Utilizza selettori XPath personalizzati configurabili dall'admin per temi particolari.
	 *
	 * @since    1.0.0
	 * @param    DOMDocument $dom    Il documento DOM.
	 * @param    DOMXPath    $xpath  Il DOMXPath.
	 * @param    array       $config La configurazione risolta per la posizione.
	 * @return   bool                Vero in caso di iniezione eseguita, falso altrimenti.
	 */
	private function inject_sidebar_top( DOMDocument $dom, DOMXPath $xpath, array $config ): bool {
		$sidebar = null;
		if ( ! empty( $config['custom_selector'] ) ) {
			$xpath_selector = $this->css_to_xpath( $config['custom_selector'] );
			if ( ! empty( $xpath_selector ) ) {
				$query_result = @$xpath->query( $xpath_selector );
				if ( $query_result !== false ) {
					$sidebar = $query_result->item( 0 );
				}
			}
		}

		if ( ! $sidebar ) {
			$aside_result = @$xpath->query( '//aside' );
			$sidebar = ( $aside_result !== false ) ? $aside_result->item( 0 ) : null;
			if ( ! $sidebar ) {
				$sidebar_class_result = @$xpath->query( '//*[contains(@class, "sidebar")]' );
				$sidebar = ( $sidebar_class_result !== false ) ? $sidebar_class_result->item( 0 ) : null;
			}
		}

		if ( ! $sidebar ) {
			return false;
		}

		$ad_code = trim( $config['code'] ?? '' );
		if ( $ad_code === '' ) {
			return false;
		}

		$min_h_desktop = $config['min_height_desktop'] ?? 250;
		$min_h_mobile  = $config['min_height_mobile'] ?? 0;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-sidebar-top' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		if ( ! empty( $config['override_css'] ) ) {
			$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $config['override_css'] );
		}

		$this->append_html( $dom, $wrapper, $ad_code );

		// Previene slittamenti inserendo come primo elemento figlio della barra laterale
		if ( $sidebar->firstChild ) {
			$sidebar->insertBefore( $wrapper, $sidebar->firstChild );
		} else {
			$sidebar->appendChild( $wrapper );
		}
		return true;
	}

	/**
	 * Inietta il contenitore pubblicitario Footer sopra o sotto l'elemento footer globale del sito.
	 *
	 * @since    1.0.0
	 * @param    DOMDocument $dom    Il documento DOM.
	 * @param    DOMXPath    $xpath  Il DOMXPath.
	 * @param    array       $config La configurazione risolta per la posizione.
	 * @return   bool                Vero in caso di iniezione eseguita, falso altrimenti.
	 */
	private function inject_footer( DOMDocument $dom, DOMXPath $xpath, array $config ): bool {
		$use_default = ! isset( $config['use_default_placement'] ) || $config['use_default_placement'];
		$footer      = null;

		if ( ! $use_default && ! empty( $config['custom_selector'] ) ) {
			$xpath_selector = $this->css_to_xpath( $config['custom_selector'] );
			if ( ! empty( $xpath_selector ) ) {
				$query_result = @$xpath->query( $xpath_selector );
				if ( $query_result !== false ) {
					$footer = $query_result->item( 0 );
				}
			}
		}

		if ( ! $footer ) {
			$footer_result = @$xpath->query( '//footer' );
			$footer = ( $footer_result !== false ) ? $footer_result->item( 0 ) : null;
			if ( ! $footer ) {
				$footer_class_result = @$xpath->query( '//*[contains(@class, "site-footer")]' );
				$footer = ( $footer_class_result !== false ) ? $footer_class_result->item( 0 ) : null;
			}
		}

		$ad_code = trim( $config['code'] ?? '' );
		if ( $ad_code === '' ) {
			return false;
		}

		$min_h_desktop = $config['min_height_desktop'] ?? 250;
		$min_h_mobile  = $config['min_height_mobile'] ?? 100;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-footer' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		if ( ! empty( $config['override_css'] ) ) {
			$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $config['override_css'] );
		}

		$this->append_html( $dom, $wrapper, $ad_code );

		$footer_pos = $config['footer_position'] ?? 'before_footer';

		if ( $footer && $footer->parentNode ) {
			if ( $footer_pos === 'after_footer' ) {
				if ( $footer->nextSibling ) {
					$footer->parentNode->insertBefore( $wrapper, $footer->nextSibling );
				} else {
					$footer->parentNode->appendChild( $wrapper );
				}
			} else {
				$footer->parentNode->insertBefore( $wrapper, $footer );
			}
			return true;
		}

		// Fallback: inietta in fondo al body (zona wp_footer)
		$body_result = @$xpath->query( '//body' );
		$body = ( $body_result !== false ) ? $body_result->item( 0 ) : null;
		if ( $body ) {
			$body->appendChild( $wrapper );
			return true;
		}

		return false;
	}

	/**
	 * Inietta un banner all'interno della griglia degli articoli.
	 *
	 * @since    1.0.0
	 * @param    DOMDocument $dom         Il documento DOM.
	 * @param    DOMXPath    $xpath       Il DOMXPath.
	 * @param    string      $position_id L'identificatore della posizione ('grid_home' o 'grid_archive').
	 * @param    array       $config      La configurazione risolta per la posizione.
	 * @return   bool                     Vero in caso di iniezione eseguita, falso altrimenti.
	 */
	private function inject_grid( DOMDocument $dom, DOMXPath $xpath, string $position_id, array $config ): bool {
		$target_selector = empty( $config['target_element'] ) ? '.post-card' : $config['target_element'];
		$xpath_selector  = $this->css_to_xpath( $target_selector );
		if ( empty( $xpath_selector ) ) {
			return false;
		}

		$cards = @$xpath->query( $xpath_selector );
		$frequency = $config['frequency'] ?? 3;

		if ( $cards === false || $cards->length < $frequency || $frequency < 1 ) {
			return false;
		}

		$target_card = $cards->item( $frequency - 1 );
		if ( ! $target_card || ! $target_card->parentNode ) {
			return false;
		}

		$ad_code = trim( $config['code'] ?? '' );
		if ( $ad_code === '' ) {
			return false;
		}

		$min_h_desktop = $config['min_height_desktop'] ?? 250;
		$min_h_mobile  = $config['min_height_mobile'] ?? 250;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-' . str_replace( '_', '-', $position_id ) );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		if ( ! empty( $config['override_css'] ) ) {
			$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $config['override_css'] );
		}

		$this->append_html( $dom, $wrapper, $ad_code );

		// Inserisce il banner subito dopo la card target (come elemento fratello successivo)
		$target_card->parentNode->insertBefore( $wrapper, $target_card->nextSibling );
		return true;
	}

	/**
	 * Converte un selettore CSS semplice in un'espressione XPath.
	 *
	 * @param string $css_selector Il selettore CSS.
	 * @return string L'espressione XPath corrispondente.
	 */
	private function css_to_xpath( string $css_selector ): string {
		$css_selector = trim( $css_selector );
		if ( empty( $css_selector ) ) {
			return '';
		}

		// Se inizia con caratteri tipici di XPath, lo considera già XPath
		if ( str_starts_with( $css_selector, '/' ) ||
			 str_starts_with( $css_selector, './' ) ||
			 str_starts_with( $css_selector, './/' ) ||
			 $css_selector === '.' ||
			 str_starts_with( $css_selector, '*' ) ||
			 str_starts_with( $css_selector, '(' ) ) {
			return $css_selector;
		}

		// Divide il selettore per spazi per gestire discendenti semplici (es. "div .sidebar")
		$parts = preg_split( '/\s+/', $css_selector );
		$xpath_parts = [];

		foreach ( $parts as $part ) {
			if ( preg_match( '/^([a-zA-Z0-9\-_*]+)?(?:#([a-zA-Z0-9\-_]+))?((?:\.[a-zA-Z0-9\-_]+)*)$/', $part, $matches ) ) {
				$tag     = ! empty( $matches[1] ) ? $matches[1] : '*';
				$id      = ! empty( $matches[2] ) ? $matches[2] : '';
				$classes = ! empty( $matches[3] ) ? explode( '.', trim( $matches[3], '.' ) ) : [];

				$conditions = [];
				if ( ! empty( $id ) ) {
					$conditions[] = "@id='$id'";
				}
				foreach ( $classes as $class ) {
					if ( ! empty( $class ) ) {
						$conditions[] = "contains(concat(' ', normalize-space(@class), ' '), ' $class ')";
					}
				}

				$xpath_part = $tag;
				if ( ! empty( $conditions ) ) {
					$xpath_part .= '[' . implode( ' and ', $conditions ) . ']';
				}
				$xpath_parts[] = $xpath_part;
			} else {
				$xpath_parts[] = '*';
			}
		}

		return '//' . implode( '//', $xpath_parts );
	}

	/**
	 * Inserisce in modo sicuro un frammento HTML all'interno di un nodo DOM,
	 * senza causare avvisi di XML malformato per i tipici tag HTML (es. img non chiusi).
	 *
	 * @param DOMDocument $dom  Il documento DOM principale.
	 * @param DOMNode     $node Il nodo di destinazione in cui inserire l'HTML.
	 * @param string      $html L'HTML da inserire.
	 */
	private function append_html( DOMDocument $dom, DOMNode $node, string $html ): void {
		if ( empty( $html ) ) {
			return;
		}

		$temp_dom = new DOMDocument();
		// Carica il frammento come HTML in UTF-8
		libxml_use_internal_errors( true );
		$temp_dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$container = $temp_dom->getElementsByTagName( 'div' )->item( 0 );
		if ( $container ) {
			foreach ( $container->childNodes as $child ) {
				$imported = $dom->importNode( $child, true );
				$node->appendChild( $imported );
			}
		}
	}

	/**
	 * Rileva il contesto WordPress corrente basandosi sui tag condizionali di frontend.
	 *
	 * @since    1.0.0
	 * @return   string Il contesto corrente: 'home', 'single', 'archive' o 'global'.
	 */
	private function get_current_context(): string {
		if ( is_home() || is_front_page() ) {
			return 'home';
		}
		if ( is_singular() ) {
			return 'single';
		}
		if ( is_archive() || is_category() || is_tag() || is_search() || is_author() || is_date() ) {
			return 'archive';
		}
		return 'global';
	}

	/**
	 * Risolve la configurazione effettiva per una data posizione dato il contesto corrente.
	 *
	 * Applica la strategia di fallback esplicita: se use_global_config è abilitato (o ereditato per assenza),
	 * restituisce la configurazione globale. Se use_global_config è false (modalità override),
	 * restituisce la configurazione contestuale (se attiva e con codice ad) oppure null (se inattiva o con codice vuoto).
	 *
	 * @since    1.0.0
	 * @param    string $position_id L'identificatore della posizione (es. 'masthead', 'footer').
	 * @param    string $context     Il contesto corrente.
	 * @return   array|null          La configurazione risolta, o null se non deve essere mostrato alcun banner.
	 */
	private function resolve_setting( string $position_id, string $context ) {
		$contexts = $this->settings['contexts'] ?? [];

		// Se siamo nel contesto globale, usiamo sempre e solo la configurazione globale
		if ( $context === 'global' ) {
			return $contexts['global']['positions'][ $position_id ] ?? null;
		}

		$context_config = $contexts[ $context ]['positions'][ $position_id ] ?? null;

		// Se per questa posizione non esiste alcuna configurazione specifica per il contesto, fallback al globale
		if ( ! $context_config ) {
			return $contexts['global']['positions'][ $position_id ] ?? null;
		}

		$use_global = ! isset( $context_config['use_global_config'] ) || $context_config['use_global_config'];

		if ( $use_global ) {
			// Ereditarietà esplicita/implicita: fallback alla configurazione globale
			return $contexts['global']['positions'][ $position_id ] ?? null;
		}

		// Modalità Override Esplicito (use_global_config === false)
		// Restituiamo la configurazione contestuale se è attiva e con codice non vuoto
		if ( ! empty( $context_config['active'] ) && trim( $context_config['code'] ?? '' ) !== '' ) {
			return $context_config;
		}

		// Altrimenti (override disattivato o codice vuoto), restituiamo null per non emettere alcun wrapper
		return null;
	}
}


