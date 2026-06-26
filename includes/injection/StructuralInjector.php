<?php
namespace SmartAdInserter\Injection;

use DOMDocument;
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
		if ( ! empty( $this->settings['masthead']['active'] ) && ! empty( $this->settings['masthead']['code'] ) ) {
			$modified = $this->inject_masthead( $dom, $xpath ) || $modified;
		}

		// Iniezione Sidebar Top (All'inizio della barra laterale)
		if ( ! empty( $this->settings['sidebar_top']['active'] ) && ! empty( $this->settings['sidebar_top']['code'] ) ) {
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
		$header = $xpath->query( '//header' )->item( 0 ) ?? $xpath->query( '//*[contains(@class, "site-header")]' )->item( 0 );
		if ( ! $header || ! $header->parentNode ) {
			return false;
		}

		$ad_code       = $this->settings['masthead']['code'];
		$min_h_desktop = $this->settings['masthead']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['masthead']['min_height_mobile'] ?? 100;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-masthead' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		$fragment = $dom->createDocumentFragment();
		$fragment->appendXML( $ad_code );
		$wrapper->appendChild( $fragment );

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
		$selector = empty( $this->settings['sidebar_top']['custom_selector'] ) ? '//aside' : $this->settings['sidebar_top']['custom_selector'];
		$sidebar  = $xpath->query( $selector )->item( 0 ) ?? $xpath->query( '//*[contains(@class, "sidebar")]' )->item( 0 );
		if ( ! $sidebar ) {
			return false;
		}

		$ad_code       = $this->settings['sidebar_top']['code'];
		$min_h_desktop = $this->settings['sidebar_top']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['sidebar_top']['min_height_mobile'] ?? 0;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-sidebar-top' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		$fragment = $dom->createDocumentFragment();
		$fragment->appendXML( $ad_code );
		$wrapper->appendChild( $fragment );

		// Previene slittamenti inserendo come primo elemento figlio della barra laterale
		if ( $sidebar->firstChild ) {
			$sidebar->insertBefore( $wrapper, $sidebar->firstChild );
		} else {
			$sidebar->appendChild( $wrapper );
		}
		return true;
	}
}
