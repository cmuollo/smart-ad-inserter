<?php
namespace SmartAdInserter\Injection;

use DOMDocument;

/**
 * Gestisce l'iniezione degli annunci posizionati all'interno dell'articolo (Content-based).
 *
 * Questa classe implementa la strategia concreta per il posizionamento dei banner
 * legati al flusso del testo dell'articolo (es. ATF - Above The Fold, BTF - Below The Fold).
 * Utilizza DOMDocument applicato al solo frammento dell'articolo per identificare il tag <p>.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
class ContentInjector implements AdInjectorInterface {

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
	 * Esegue l'iniezione in base alle strategie configurate sul contenuto.
	 *
	 * @since    1.0.0
	 */
	public function inject( string $content ): string {
		// Iniezione ATF (Subito prima del primo paragrafo dell'articolo)
		if ( ! empty( $this->settings['atf']['active'] ) && ! empty( $this->settings['atf']['code'] ) ) {
			$content = $this->inject_atf( $content );
		}

		// Iniezione BTF (Al fondo del testo dell'articolo)
		if ( ! empty( $this->settings['btf']['active'] ) && ! empty( $this->settings['btf']['code'] ) ) {
			$content = $this->inject_btf( $content );
		}

		return $content;
	}

	/**
	 * Inietta il wrapper pubblicitario ATF prima del primo paragrafo del testo.
	 *
	 * Utilizza DOMDocument sul solo frammento di testo per garantire leggerezza e velocità
	 * di parsing lato server, azzerando il CLS grazie alle altezze minime pre-allocate.
	 *
	 * @since    1.0.0
	 */
	private function inject_atf( string $content ): string {
		$ad_code       = $this->settings['atf']['code'];
		$min_h_desktop = $this->settings['atf']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['atf']['min_height_mobile'] ?? 250;

		// Wrapper protettivo anti-CLS con altezze minime inline
		$wrapper = sprintf(
			'<div class="sai-ad-wrapper sai-atf" style="min-height:%dpx; --min-h-mobile:%dpx;">%s</div>',
			$min_h_desktop,
			$min_h_mobile,
			$ad_code
		);

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Forza il caricamento in UTF-8 per evitare la corruzione degli accenti italiani
		$dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $content . '</div>', LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$p_elements = $dom->getElementsByTagName( 'p' );
		if ( $p_elements->length > 0 ) {
			$first_p  = $p_elements->item( 0 );
			$fragment = $dom->createDocumentFragment();
			$fragment->appendXML( $wrapper );
			$first_p->parentNode->insertBefore( $fragment, $first_p );
		}

		// Estrae l'HTML interno privo del wrapper div inserito per il parsing
		$root_div    = $dom->getElementsByTagName( 'div' )->item( 0 );
		$new_content = '';
		if ( $root_div ) {
			foreach ( $root_div->childNodes as $child ) {
				$new_content .= $dom->saveHTML( $child );
			}
		}

		return ! empty( $new_content ) ? $new_content : $content;
	}

	/**
	 * Inietta il wrapper pubblicitario BTF concatenandolo al fondo del testo.
	 *
	 * @since    1.0.0
	 */
	private function inject_btf( string $content ): string {
		$ad_code       = $this->settings['btf']['code'];
		$min_h_desktop = $this->settings['btf']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['btf']['min_height_mobile'] ?? 250;

		$wrapper = sprintf(
			'<div class="sai-ad-wrapper sai-btf" style="min-height:%dpx; --min-h-mobile:%dpx;">%s</div>',
			$min_h_desktop,
			$min_h_mobile,
			$ad_code
		);

		return $content . $wrapper;
	}
}
