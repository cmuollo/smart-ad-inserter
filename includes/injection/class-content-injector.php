<?php
namespace SmartAdInserter\Injection;

use DOMDocument;

/**
 * Class ContentInjector
 *
 * Concrete strategy to handle in-content ad placement (Above The Fold, Below The Fold).
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
class ContentInjector implements AdInjectorInterface {

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
	 * Inject ads based on the content strategy.
	 *
	 * @since    1.0.0
	 */
	public function inject( string $content ): string {
		// ATF (Above The Fold) injection
		if ( ! empty( $this->settings['atf']['active'] ) && ! empty( $this->settings['atf']['code'] ) ) {
			$content = $this->inject_atf( $content );
		}

		// BTF (Below The Fold) injection
		if ( ! empty( $this->settings['btf']['active'] ) && ! empty( $this->settings['btf']['code'] ) ) {
			$content = $this->inject_btf( $content );
		}

		return $content;
	}

	/**
	 * Inject ATF ad wrapper right before the first paragraph.
	 *
	 * @since    1.0.0
	 */
	private function inject_atf( string $content ): string {
		$ad_code       = $this->settings['atf']['code'];
		$min_h_desktop = $this->settings['atf']['min_height_desktop'] ?? 250;
		$min_h_mobile  = $this->settings['atf']['min_height_mobile'] ?? 250;

		$wrapper = sprintf(
			'<div class="sai-ad-wrapper sai-atf" style="min-height:%dpx; --min-h-mobile:%dpx;">%s</div>',
			$min_h_desktop,
			$min_h_mobile,
			$ad_code
		);

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Load as UTF-8 wrapped inside a div to avoid parsing errors
		$dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $content . '</div>', LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$p_elements = $dom->getElementsByTagName( 'p' );
		if ( $p_elements->length > 0 ) {
			$first_p  = $p_elements->item( 0 );
			$fragment = $dom->createDocumentFragment();
			$fragment->appendXML( $wrapper );
			$first_p->parentNode->insertBefore( $fragment, $first_p );
		}

		// Extract content back from outer wrapper div
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
	 * Concat BTF ad wrapper at the end of the content.
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
