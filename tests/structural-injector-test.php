<?php
namespace SmartAdInserter\Tests;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use SmartAdInserter\Injection\StructuralInjector;
use SmartAdInserter\SmartAdInserterSettings;

/**
 * Classe di test per l'iniettore strutturale (StructuralInjector) e la gestione del Footer.
 *
 * Copre il posizionamento relativo, i fallback deterministici sul DOM, la sanitizzazione delle
 * impostazioni e i requisiti Zero-CLS.
 *
 * @group structural
 */
class StructuralInjectorTest extends TestCase {

	/**
	 * Array delle impostazioni salvate tramite il mock di update_option.
	 *
	 * @var array
	 */
	public array $saved_settings = [];

	/**
	 * Configura l'ambiente mock prima di ciascun test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Mock delle funzioni condizionali di WordPress chiamate in StructuralInjector
		WP_Mock::userFunction( 'is_home', [ 'return' => false ] );
		WP_Mock::userFunction( 'is_front_page', [ 'return' => false ] );
		WP_Mock::userFunction( 'is_archive', [ 'return' => false ] );
		WP_Mock::userFunction( 'is_category', [ 'return' => false ] );
		WP_Mock::userFunction( 'is_tag', [ 'return' => false ] );
		WP_Mock::userFunction( 'is_search', [ 'return' => false ] );
		WP_Mock::userFunction( 'is_author', [ 'return' => false ] );
		WP_Mock::userFunction( 'is_date', [ 'return' => false ] );

		// Mock di funzioni di WordPress per il salvataggio delle impostazioni
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

		WP_Mock::userFunction( 'current_user_can', [
			'return' => true
		] );

		WP_Mock::userFunction( 'wp_unslash', [
			'return' => function( $val ) {
				return is_string( $val ) ? stripslashes( $val ) : $val;
			}
		] );

		WP_Mock::userFunction( 'delete_transient', [ 'return' => true ] );
		WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );

		WP_Mock::userFunction( 'update_option', [
			'return' => function( $key, $value ) {
				$this->saved_settings = $value;
				return true;
			}
		] );
	}

	/**
	 * Test Scenario 1: Footer in modalità 'before_footer' con elemento footer presente nel DOM.
	 * Il banner deve essere inserito immediatamente prima del tag footer.
	 */
	public function test_footer_before_footer_target_found() {
		$html = '
		<div class="site-wrap">
			<main>Contenuto</main>
			<footer class="my-footer">Footer Content</footer>
		</div>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '<div class="ad-footer">[BANNER FOOTER]</div>',
				'use_default_placement' => true,
				'footer_position'       => 'before_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => 'margin-bottom: 10px;'
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( 'class="sai-ad-wrapper sai-footer"', $result );
		$this->assertStringContainsString( 'style="min-height:250px; --min-h-mobile:100px; margin-bottom: 10px;"', $result );
		$this->assertStringContainsString( '[BANNER FOOTER]</div></div><footer class="my-footer"', $result );
	}

	/**
	 * Test Scenario 2a: Footer in modalità 'after_footer' con elemento footer presente e avente nextSibling.
	 * Il banner deve essere inserito immediatamente dopo il footer (quindi prima di nextSibling).
	 */
	public function test_footer_after_footer_target_found_with_next_sibling() {
		$html = '
		<div class="site-wrap">
			<main>Contenuto</main>
			<footer class="my-footer">Footer Content</footer>
			<div class="post-footer-spacer">Spazio</div>
		</div>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '<div class="ad-footer">[BANNER AFTER FOOTER]</div>',
				'use_default_placement' => true,
				'footer_position'       => 'after_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( 'class="sai-ad-wrapper sai-footer"', $result );
		$this->assertStringContainsString( '</footer><div class="sai-ad-wrapper sai-footer"', $result );
		$this->assertStringContainsString( 'ad-footer">[BANNER AFTER FOOTER]</div></div>', $result );
		$this->assertStringContainsString( '<div class="post-footer-spacer">Spazio</div>', $result );
	}

	/**
	 * Test Scenario 2b: Footer in modalità 'after_footer' con elemento footer presente ma senza nextSibling.
	 * Il banner deve essere inserito come ultimo figlio del parent del footer.
	 */
	public function test_footer_after_footer_target_found_no_next_sibling() {
		$html = '
		<div class="site-wrap">
			<main>Contenuto</main>
			<footer class="my-footer">Footer Content</footer>
		</div>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '<div class="ad-footer">[BANNER AFTER FOOTER]</div>',
				'use_default_placement' => true,
				'footer_position'       => 'after_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( 'class="sai-ad-wrapper sai-footer"', $result );
		$this->assertStringContainsString( '</footer><div class="sai-ad-wrapper sai-footer"', $result );
	}

	/**
	 * Test Scenario 3: Footer in modalità 'before_footer' senza alcun elemento footer nel DOM.
	 * Il plugin deve eseguire il fallback inietta in fondo al body.
	 */
	public function test_footer_before_footer_target_not_found() {
		$html = '
		<html>
		<body>
			<div class="site-wrap">
				<main>Contenuto Senza Footer</main>
			</div>
		</body>
		</html>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '<div class="ad-footer">[BANNER FALLBACK BEFORE]</div>',
				'use_default_placement' => true,
				'footer_position'       => 'before_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( 'class="sai-ad-wrapper sai-footer"', $result );
		$this->assertStringContainsString( '[BANNER FALLBACK BEFORE]</div></div></body>', $result );
	}

	/**
	 * Test Scenario 4: Footer in modalità 'after_footer' senza alcun elemento footer nel DOM.
	 * Il plugin deve eseguire il fallback inietta in fondo al body.
	 */
	public function test_footer_after_footer_target_not_found() {
		$html = '
		<html>
		<body>
			<div class="site-wrap">
				<main>Contenuto Senza Footer</main>
			</div>
		</body>
		</html>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '<div class="ad-footer">[BANNER FALLBACK AFTER]</div>',
				'use_default_placement' => true,
				'footer_position'       => 'after_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( 'class="sai-ad-wrapper sai-footer"', $result );
		$this->assertStringContainsString( '[BANNER FALLBACK AFTER]</div></div></body>', $result );
	}

	/**
	 * Test Scenario 5: Sanitizzazione del valore footer_position.
	 * Qualsiasi valore non consentito deve essere ricondotto a 'before_footer'.
	 */
	public function test_footer_position_invalid_value_sanitization() {
		$input_valid = 'after_footer';
		$input_invalid = 'invalid_position_value';

		$settings_instance = new SmartAdInserterSettings();
		
		// 1. Valid position value
		$settings_payload_valid = [
			'global_scripts' => '',
			'positions'      => [
				'footer' => [
					'active'                => true,
					'code'                  => 'banner',
					'footer_position'       => $input_valid,
				]
			]
		];
		
		$this->saved_settings = [];
		$settings_instance->save_settings( $settings_payload_valid );
		$this->assertEquals( 'after_footer', $this->saved_settings['positions']['footer']['footer_position'] );

		// 2. Invalid position value
		$settings_payload_invalid = [
			'global_scripts' => '',
			'positions'      => [
				'footer' => [
					'active'                => true,
					'code'                  => 'banner',
					'footer_position'       => $input_invalid,
				]
			]
		];
		
		$this->saved_settings = [];
		$settings_instance->save_settings( $settings_payload_invalid );
		$this->assertEquals( 'before_footer', $this->saved_settings['positions']['footer']['footer_position'] );
	}

	/**
	 * Test Scenario 6: Posizione disattivata.
	 * Nessun wrapper deve essere emesso.
	 */
	public function test_footer_position_disabled() {
		$html = '
		<div class="site-wrap">
			<footer class="my-footer">Footer Content</footer>
		</div>';

		$settings = [
			'footer' => [
				'active'                => false,
				'code'                  => '<div class="ad-footer">[BANNER FOOTER]</div>',
				'use_default_placement' => true,
				'footer_position'       => 'before_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringNotContainsString( 'sai-ad-wrapper', $result );
		$this->assertStringNotContainsString( '[BANNER FOOTER]', $result );
	}

	/**
	 * Test Scenario 7: Codice banner vuoto o solo spazi.
	 * Nessun wrapper deve essere emesso.
	 */
	public function test_footer_code_empty_or_whitespace() {
		$html = '
		<div class="site-wrap">
			<footer class="my-footer">Footer Content</footer>
		</div>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '    ',
				'use_default_placement' => true,
				'footer_position'       => 'before_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringNotContainsString( 'sai-ad-wrapper', $result );
	}

	/**
	 * Test Scenario 8: Verifica delle altezze minime inline per Zero CLS.
	 */
	public function test_footer_min_height_present_in_wrapper() {
		$html = '
		<div class="site-wrap">
			<footer class="my-footer">Footer Content</footer>
		</div>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '[BANNER]',
				'use_default_placement' => true,
				'footer_position'       => 'before_footer',
				'min_height_desktop'    => 350,
				'min_height_mobile'     => 150,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( 'min-height:350px; --min-h-mobile:150px;', $result );
	}

	/**
	 * Test Scenario 9: Verifica che i tag script di tipo template (backbone/underscore) vengano preservati.
	 */
	public function test_script_templates_are_preserved() {
		$html = '
		<html>
		<body>
			<div class="site-wrap">
				<footer class="my-footer">Footer Content</footer>
			</div>
			<script type="text/html" id="tmpl-elementor-templates">
				<# if ( closeType ) { #>
					<button class="close-{{{ closeType }}}">
						<# if ( \'skip\' === closeType ) { #> Salta <# } #> X
					</button>
				<# } #>
			</script>
		</body>
		</html>';

		$settings = [
			'footer' => [
				'active'                => true,
				'code'                  => '<div class="ad-footer">[BANNER]</div>',
				'use_default_placement' => true,
				'footer_position'       => 'before_footer',
				'min_height_desktop'    => 250,
				'min_height_mobile'     => 100,
				'override_css'          => ''
			]
		];

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '<# if ( \'skip\' === closeType ) { #> Salta <# } #> X', $result );
		$this->assertStringContainsString( '<script type="text/html" id="tmpl-elementor-templates">', $result );
		$this->assertStringContainsString( '</script>', $result );
	}
}
