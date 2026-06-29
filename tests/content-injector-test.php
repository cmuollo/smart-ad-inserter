<?php
namespace SmartAdInserter\Tests;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use SmartAdInserter\Injection\ContentInjector;
use SmartAdInserter\SmartAdInserterSettings;

/**
 * Classe di test per l'iniettore di contenuto (ContentInjector) e l'algoritmo In-Text.
 *
 * @group content
 */
class ContentInjectorTest extends TestCase {

	/**
	 * Configura l'ambiente mock prima di ciascun test.
	 */
	public function setUp(): void {
		parent::setUp();

		WP_Mock::userFunction( 'absint', [
			'return' => function( $val ) {
				return (int) $val;
			}
		] );

		WP_Mock::userFunction( 'sanitize_text_field', [
			'return' => function( $val ) {
				return trim( $val );
			}
		] );

		WP_Mock::userFunction( 'wp_kses', [
			'return' => function( $val, $allowed ) {
				return $val;
			}
		] );

		WP_Mock::userFunction( 'wp_unslash', [
			'return' => function( $val ) {
				return is_string( $val ) ? stripslashes( $val ) : $val;
			}
		] );
	}

	/**
	 * Test Scenario 1: In text attivo con contenuto lungo e paragrafi multipli -> inserisce fino al massimo consentito.
	 */
	public function test_in_text_inserts_up_to_max_limit() {
		$html = '
		<p>Uno due tre quattro cinque sei sette otto nove dieci.</p>
		<p>Uno due tre quattro cinque sei sette otto nove dieci.</p>
		<p>Uno due tre quattro cinque sei sette otto nove dieci.</p>
		<p>Uno due tre quattro cinque sei sette otto nove dieci.</p>
		<p>Uno due tre quattro cinque sei sette otto nove dieci.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 15,
				'max_insertions'         => 2,
				'avoid_btf_single_block' => false,
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		// Deve contenere al massimo 2 banner (max_insertions = 2)
		$count = substr_count( $result, '[BANNER]' );
		$this->assertEquals( 2, $count );
	}

	/**
	 * Test Scenario 2: In text con soglia raggiunta dentro un blockquote -> salta il blockquote e inserisce dopo.
	 */
	public function test_in_text_skips_blockquote_for_insertion_and_counting() {
		$html = '
		<p>Uno due tre quattro cinque sei sette otto nove dieci.</p>
		<blockquote>Questo è un blockquote lungo che contiene molte parole da ignorare nel conteggio.</blockquote>
		<p>Uno due tre quattro cinque sei sette otto nove dieci.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 15,
				'max_insertions'         => 1,
				'avoid_btf_single_block' => false,
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		// Le parole nel blockquote (14 parole) non devono essere contate.
		// Quindi la soglia di 15 si raggiunge solo dopo le prime 5 parole del secondo paragrafo.
		// L'annuncio deve finire dopo il secondo paragrafo (tipo = 'p').
		// Il blockquote non deve contenere il banner al suo interno o subito prima.
		$this->assertStringNotContainsString( '<blockquote>[BANNER]', $result );
		$this->assertStringContainsString( '</p><div class="sai-ad-wrapper sai-in-text"', $result );
	}

	/**
	 * Test Scenario 3: Paragrafo lungo con tag <br> multipli -> inserisce dopo il primo <br> utile oltre la soglia.
	 */
	public function test_in_text_splits_at_br_useful_beyond_threshold() {
		$html = '
		<p class="custom-p" style="color:red;">Uno due tre quattro cinque sei sette otto nove dieci. <br/> Undici dodici tredici quattordici quindici sedici. <br/> Diciassette diciotto diciannove venti.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 12,
				'max_insertions'         => 1,
				'avoid_btf_single_block' => false,
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		// La soglia (12) viene superata nel secondo frammento di testo (10 + 6 = 16 parole).
		// Il primo <br> utile *oltre la soglia* è il secondo <br>.
		// Quindi il paragrafo deve essere scisso dopo il secondo <br>.
		// Il wrapper dell'ad deve essere inserito tra le due metà del paragrafo scisso.
		$this->assertStringContainsString( 'quattordici quindici sedici. </p><div class="sai-ad-wrapper sai-in-text"', $result );
	}

	/**
	 * Test Scenario 4: Paragrafo lungo senza <br> -> inserisce alla fine del <p>.
	 */
	public function test_in_text_inserts_at_p_end_when_no_br_exists() {
		$html = '
		<p>Uno due tre quattro cinque sei sette otto nove dieci undici dodici tredici quattordici quindici sedici.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 12,
				'max_insertions'         => 1,
				'avoid_btf_single_block' => false,
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '</p><div class="sai-ad-wrapper sai-in-text"', $result );
	}

	/**
	 * Test Scenario 5: Scissione paragrafo preserva classi CSS e stili inline.
	 */
	public function test_in_text_split_preserves_attributes() {
		$html = '
		<p class="my-class-name" style="margin:10px;">Uno due tre quattro cinque sei sette otto nove dieci. <br/> Undici dodici tredici quattordici quindici sedici.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 5,
				'max_insertions'         => 1,
				'avoid_btf_single_block' => false,
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		// Deve esserci la scissione al <br>.
		// Entrambi i paragrafi risultanti devono avere class="my-class-name" e style="margin:10px;".
		$this->assertStringContainsString( '<p class="my-class-name" style="margin:10px;">Uno due tre quattro cinque sei sette otto nove dieci. </p>', $result );
		$this->assertStringContainsString( '<p class="my-class-name" style="margin:10px;"> Undici dodici tredici quattordici quindici sedici.</p>', $result );
	}

	/**
	 * Test Scenario 6: Accumulo parole su paragrafi brevi.
	 */
	public function test_in_text_accumulates_words_over_short_paragraphs() {
		$html = '
		<p>Uno due tre.</p>
		<p>Quattro cinque sei.</p>
		<p>Sette otto nove dieci.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 8,
				'max_insertions'         => 1,
				'avoid_btf_single_block' => false,
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		// La soglia (8) si raggiunge al terzo paragrafo (3 + 3 + 4 = 10 parole).
		// L'inserimento deve avvenire dopo il terzo paragrafo.
		$this->assertStringContainsString( 'dieci.</p><div class="sai-ad-wrapper sai-in-text"', $result );
	}

	/**
	 * Test Scenario 7: Contenuto troppo povero -> nessun banner.
	 */
	public function test_in_text_poor_content_no_banner() {
		$html = '<p>Solo tre parole.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 10,
				'max_insertions'         => 1,
				'avoid_btf_single_block' => true, // Evita se c'è un solo blocco utile
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringNotContainsString( 'sai-in-text', $result );
	}

	/**
	 * Test Scenario 8: Singolo paragrafo ed evitare conflitto con BTF (avoid_btf_single_block = true).
	 */
	public function test_in_text_avoid_btf_on_single_block() {
		$html = '<p>Uno due tre quattro cinque sei sette otto nove dieci undici dodici tredici quattordici quindici.</p>';

		$settings = [
			'in_text' => [
				'active'                 => true,
				'code'                   => '[BANNER]',
				'min_height_desktop'     => 250,
				'min_height_mobile'      => 250,
				'words_interval'         => 5,
				'max_insertions'         => 1,
				'avoid_btf_single_block' => true,
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		// Anche se supera la soglia (15 > 5), c'è un solo blocco e avoid_btf_single_block è attivo -> nessun banner.
		$this->assertStringNotContainsString( 'sai-in-text', $result );
	}

	/**
	 * Test Scenario 9: Contenitori esclusi -> conta le parole ma non inserisce all'interno.
	 */
	public function test_in_text_excluded_containers() {
		$html = '
		<div class="toc-box">Uno due tre quattro cinque sei sette otto nove dieci.</div>
		<p>Uno due tre quattro cinque.</p>';

		$settings = [
			'in_text' => [
				'active'                    => true,
				'code'                      => '[BANNER]',
				'min_height_desktop'        => 250,
				'min_height_mobile'         => 250,
				'words_interval'            => 12,
				'max_insertions'            => 1,
				'avoid_btf_single_block'    => false,
				'excluded_container_tokens' => '.toc-box',
			]
		];

		$injector = new ContentInjector( $settings );
		$result   = $injector->inject( $html );

		// Le parole in .toc-box (10 parole) devono essere conteggiate.
		// Quindi la soglia di 12 si raggiunge al paragrafo successivo (10 + 5 = 15).
		// L'inserimento deve avvenire alla fine del paragrafo <p>, NON dentro .toc-box.
		$this->assertStringNotContainsString( 'toc-box">[BANNER]', $result );
		$this->assertStringContainsString( 'cinque.</p><div class="sai-ad-wrapper sai-in-text"', $result );
	}

	/**
	 * Test Scenario 10: Sanitizzazione dei token di esclusione.
	 */
	public function test_exclusion_tokens_sanitization() {
		$input = ' .pippo, #toc-box , .widget-inline , invalid_token_without_prefix, .invalid space ';
		$sanitized = SmartAdInserterSettings::sanitize_exclusion_tokens( $input );

		$this->assertCount( 3, $sanitized );
		$this->assertContains( '.pippo', $sanitized );
		$this->assertContains( '#toc-box', $sanitized );
		$this->assertContains( '.widget-inline', $sanitized );
		$this->assertNotContains( 'invalid_token_without_prefix', $sanitized );
	}
}
