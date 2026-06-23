<?php
namespace SmartAdInserter\Injection;

use DOMDocument;
use DOMXPath;

/**
 * Class StructuralInjector
 *
 * Concrete strategy to handle structural layout ad placement (Masthead, Sidebar).
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
class StructuralInjector implements AdInjectorInterface {

	/**
	 * Position configuration settings.
	 *
	 * @since    1.0.0
	 * @var      array
	 */
	protected $settings;

	/**
	 * Initialize with configuration settings.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Inject ads based on the structural strategy.
	 *
	 * @since    1.0.0
	 */
	public function inject( string $html ): string {
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$xpath    = new DOMXPath( $dom );
		$modified = false;

		// Masthead injection
		if ( ! empty( $this->settings['masthead']['active'] ) && ! empty( $this->settings['masthead']['code'] ) ) {
			$modified = $this->inject_masthead( $dom, $xpath ) || $modified;
		}

		// Sidebar Top injection
		if ( ! empty( $this->settings['sidebar_top']['active'] ) && ! empty( $this->settings['sidebar_top']['code'] ) ) {
			$modified = $this->inject_sidebar_top( $dom, $xpath ) || $modified;
		}

		return $modified ? $dom->saveHTML() : $html;
	}

	/**
	 * Inject Masthead ad right after <header> or .site-header.
	 *
	 * @since    1.0.0
	 */
	private function inject_masthead( DOMDocument $dom, DOMXPath $xpath ): bool {
		$header = $xpath->query( '//header' )->item( 0 ) ?? $xpath->query( '//*[contains(@class, "site-header")]' )->item( 0 );
		if ( ! $header || ! $header->parentNode ) {
			return false;
		}

		$ad_code       = $this->settings['masthead']['code'];
		$min_h_desktop = $this->settings['masthead']['min_height_desktop'] ?? 90;
		$min_h_mobile  = $this->settings['masthead']['min_height_mobile'] ?? 90;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-masthead' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		$fragment = $dom->createDocumentFragment();
		$fragment->appendXML( $ad_code );
		$wrapper->appendChild( $fragment );

		// Insert ad container right after header
		$header->parentNode->insertBefore( $wrapper, $header->nextSibling );
		return true;
	}

	/**
	 * Inject Sidebar Top ad prepend to <aside> or .sidebar.
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
		$min_h_mobile  = $this->settings['sidebar_top']['min_height_mobile'] ?? 250;

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'sai-ad-wrapper sai-sidebar-top' );
		$wrapper->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

		$fragment = $dom->createDocumentFragment();
		$fragment->appendXML( $ad_code );
		$wrapper->appendChild( $fragment );

		// Prepend to sidebar (before first child)
		if ( $sidebar->firstChild ) {
			$sidebar->insertBefore( $wrapper, $sidebar->firstChild );
		} else {
			$sidebar->appendChild( $wrapper );
		}
		return true;
	}
}
