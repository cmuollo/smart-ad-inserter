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
	 * @param    array    $settings    Mappa delle opzioni di configurazione.
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Esegue l'iniezione in base alle strategie configurate sul contenuto.
	 *
	 * @since    1.0.0
	 * @param    string    $content     L'HTML originario del contenuto.
	 * @return   string                 L'HTML modificato.
	 */
	public function inject( string $content ): string {
		// Iniezione ATF (Subito prima del primo paragrafo dell'articolo)
		if ( ! empty( $this->settings['atf']['active'] ) && ! empty( trim( $this->settings['atf']['code'] ?? '' ) ) ) {
			$content = $this->inject_atf( $content );
		}

		// Iniezione BTF (Al fondo del testo dell'articolo)
		if ( ! empty( $this->settings['btf']['active'] ) && ! empty( trim( $this->settings['btf']['code'] ?? '' ) ) ) {
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
	 * @param    string    $content     L'HTML originario del contenuto.
	 * @return   string                 L'HTML modificato.
	 */
	private function inject_atf( string $content ): string {
		$ad_code = trim( $this->settings['atf']['code'] ?? '' );
		if ( $ad_code === '' ) {
			return $content;
		}
		$min_h_desktop = $this->settings['atf']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['atf']['min_height_mobile'] ?? 250;

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Forza il caricamento in UTF-8 per evitare la corruzione degli accenti italiani
		$dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $content . '</div>', LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$p_elements = $dom->getElementsByTagName( 'p' );
		if ( $p_elements->length > 0 ) {
			$first_p  = $p_elements->item( 0 );
			
			// Crea il wrapper div direttamente nel DOM di destinazione
			$wrapper_el = $dom->createElement( 'div' );
			$wrapper_el->setAttribute( 'class', 'sai-ad-wrapper sai-atf' );
			$wrapper_el->setAttribute( 'style', sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile ) );

			if ( ! empty( $this->settings['atf']['override_css'] ) ) {
				$wrapper_el->setAttribute( 'style', $wrapper_el->getAttribute( 'style' ) . ' ' . $this->settings['atf']['override_css'] );
			}

			// Carica l'ad_code come HTML e importalo nel wrapper
			$temp_dom = new DOMDocument();
			libxml_use_internal_errors( true );
			$temp_dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $ad_code . '</div>', LIBXML_HTML_NODEFDTD );
			libxml_clear_errors();

			$temp_div = $temp_dom->getElementsByTagName( 'div' )->item( 0 );
			if ( $temp_div ) {
				foreach ( $temp_div->childNodes as $child ) {
					$imported = $dom->importNode( $child, true );
					$wrapper_el->appendChild( $imported );
				}
			}

			$first_p->parentNode->insertBefore( $wrapper_el, $first_p );
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
	 * @param    string    $content     L'HTML originario del contenuto.
	 * @return   string                 L'HTML modificato.
	 */
	private function inject_btf( string $content ): string {
		$ad_code = trim( $this->settings['btf']['code'] ?? '' );
		if ( $ad_code === '' ) {
			return $content;
		}
		$min_h_desktop = $this->settings['btf']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['btf']['min_height_mobile'] ?? 250;

		$style = sprintf( 'min-height:%dpx; --min-h-mobile:%dpx;', $min_h_desktop, $min_h_mobile );
		if ( ! empty( $this->settings['btf']['override_css'] ) ) {
			$style .= ' ' . $this->settings['btf']['override_css'];
		}

		$wrapper = sprintf(
			'<div class="sai-ad-wrapper sai-btf" style="%s">%s</div>',
			$style,
			$ad_code
		);

		return $content . $wrapper;
	}
}
