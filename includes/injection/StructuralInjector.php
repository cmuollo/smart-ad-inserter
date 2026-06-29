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
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Esegue l'iniezione in base alle strategie configurate sul layout della pagina.
	 *
	 * @since    1.0.0
	 */
	public function inject( string $html ): string {
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Forza il caricamento in UTF-8 per evitare la corruzione dei caratteri accentati del tema
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$xpath    = new DOMXPath( $dom );
		$modified = false;

		// Iniezione Masthead (Sotto l'header globale del sito)
		if ( ! empty( $this->settings['masthead']['active'] ) && ! empty( trim( $this->settings['masthead']['code'] ?? '' ) ) ) {
			$modified = $this->inject_masthead( $dom, $xpath ) || $modified;
		}

		// Iniezione Sidebar Top (All'inizio della barra laterale)
		if ( ! empty( $this->settings['sidebar_top']['active'] ) && ! empty( trim( $this->settings['sidebar_top']['code'] ?? '' ) ) ) {
			$modified = $this->inject_sidebar_top( $dom, $xpath ) || $modified;
		}

		// Iniezione Footer (Sopra il footer globale del sito)
		if ( ! empty( $this->settings['footer']['active'] ) && ! empty( trim( $this->settings['footer']['code'] ?? '' ) ) ) {
			$modified = $this->inject_footer( $dom, $xpath ) || $modified;
		}

		// Iniezione Griglia Home (Home Page)
		if ( ( is_home() || is_front_page() ) && ! empty( $this->settings['grid_home']['active'] ) && ! empty( trim( $this->settings['grid_home']['code'] ?? '' ) ) ) {
			$modified = $this->inject_grid( $dom, $xpath, 'grid_home' ) || $modified;
		}

		// Iniezione Griglia Archivio (Categorie/Archivi)
		if ( ( is_archive() || is_category() || is_tag() || is_search() || is_author() || is_date() ) && ! empty( $this->settings['grid_archive']['active'] ) && ! empty( trim( $this->settings['grid_archive']['code'] ?? '' ) ) ) {
			$modified = $this->inject_grid( $dom, $xpath, 'grid_archive' ) || $modified;
		}

		return $modified ? $dom->saveHTML() : $html;
	}

	/**
	 * Inietta il contenitore pubblicitario Masthead sotto l'intestazione della pagina.
	 *
	 * Individua l'elemento <header> o classi CSS standard come .site-header.
	 *
	 * @since    1.0.0
	 */
	private function inject_masthead( DOMDocument $dom, DOMXPath $xpath ): bool {
		$use_default = ! isset( $this->settings['masthead']['use_default_placement'] ) || $this->settings['masthead']['use_default_placement'];
		$header      = null;

		if ( ! $use_default && ! empty( $this->settings['masthead']['custom_selector'] ) ) {
			$xpath_selector = $this->css_to_xpath( $this->settings['masthead']['custom_selector'] );
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

		$ad_code = trim( $this->settings['masthead']['code'] ?? '' );
		if ( $ad_code === '' ) {
			return false;
		}

		$min_h_desktop = $this->settings['masthead']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['masthead']['min_height_mobile'] ?? 100;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-masthead' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		if ( ! empty( $this->settings['masthead']['override_css'] ) ) {
			$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $this->settings['masthead']['override_css'] );
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
	 */
	private function inject_sidebar_top( DOMDocument $dom, DOMXPath $xpath ): bool {
		$sidebar = null;
		if ( ! empty( $this->settings['sidebar_top']['custom_selector'] ) ) {
			$xpath_selector = $this->css_to_xpath( $this->settings['sidebar_top']['custom_selector'] );
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

		$ad_code = trim( $this->settings['sidebar_top']['code'] ?? '' );
		if ( $ad_code === '' ) {
			return false;
		}

		$min_h_desktop = $this->settings['sidebar_top']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['sidebar_top']['min_height_mobile'] ?? 0;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-sidebar-top' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		if ( ! empty( $this->settings['sidebar_top']['override_css'] ) ) {
			$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $this->settings['sidebar_top']['override_css'] );
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
	 * Inietta il contenitore pubblicitario Footer sopra l'elemento footer globale del sito.
	 *
	 * @since    1.0.0
	 */
	private function inject_footer( DOMDocument $dom, DOMXPath $xpath ): bool {
		$use_default = ! isset( $this->settings['footer']['use_default_placement'] ) || $this->settings['footer']['use_default_placement'];
		$footer      = null;

		if ( ! $use_default && ! empty( $this->settings['footer']['custom_selector'] ) ) {
			$xpath_selector = $this->css_to_xpath( $this->settings['footer']['custom_selector'] );
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

		$ad_code = trim( $this->settings['footer']['code'] ?? '' );
		if ( $ad_code === '' ) {
			return false;
		}

		$min_h_desktop = $this->settings['footer']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['footer']['min_height_mobile'] ?? 100;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-footer' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		if ( ! empty( $this->settings['footer']['override_css'] ) ) {
			$wrapper->setAttribute( 'style', $wrapper->getAttribute( 'style' ) . ' ' . $this->settings['footer']['override_css'] );
		}

		$this->append_html( $dom, $wrapper, $ad_code );

		$footer_pos = $this->settings['footer']['footer_position'] ?? 'before_footer';

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
	 * Individua l'N-esimo elemento corrispondente al target_element e inserisce il banner subito dopo.
	 *
	 * @since    1.0.0
	 * @param    DOMDocument $dom         Il documento DOM.
	 * @param    DOMXPath    $xpath       Il DOMXPath.
	 * @param    string      $position_id L'identificatore della posizione ('grid_home' o 'grid_archive').
	 * @return   bool                     Vero in caso di iniezione eseguita, falso altrimenti.
	 */
	private function inject_grid( DOMDocument $dom, DOMXPath $xpath, string $position_id ): bool {
		$config = $this->settings[ $position_id ] ?? [];
		if ( empty( $config ) ) {
			return false;
		}

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

		// Se la griglia ha un override CSS, lo applichiamo come stile inline
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
}


