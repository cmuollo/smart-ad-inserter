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
				$header = $xpath->query( $xpath_selector )->item( 0 );
			}
		}

		if ( ! $header ) {
			$header = $xpath->query( '//header' )->item( 0 ) ?? $xpath->query( '//*[contains(@class, "site-header")]' )->item( 0 );
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
				$sidebar = $xpath->query( $xpath_selector )->item( 0 );
			}
		}

		if ( ! $sidebar ) {
			$sidebar = $xpath->query( '//aside' )->item( 0 ) ?? $xpath->query( '//*[contains(@class, "sidebar")]' )->item( 0 );
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


