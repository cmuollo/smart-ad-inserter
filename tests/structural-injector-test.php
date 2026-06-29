<?php
namespace SmartAdInserter\Tests;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use SmartAdInserter\Injection\StructuralInjector;
use SmartAdInserter\SmartAdInserterSettings;

/**
 * Classe di test per l'iniettore strutturale (StructuralInjector) con logiche contestuali.
 *
 * Copre la risoluzione delle impostazioni con ereditarietà/override (use_global_config),
 * il posizionamento del Footer, la sanitizzazione e i requisiti Zero-CLS.
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
	 * Il contesto corrente da simulare per i test.
	 *
	 * @var string
	 */
	public string $current_context = 'global';

	/**
	 * Configura l'ambiente mock prima di ciascun test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->current_context = 'global';

		// Mock dinamico delle funzioni condizionali di WordPress chiamate in StructuralInjector
		WP_Mock::userFunction( 'is_home', [
			'return' => function() {
				return $this->current_context === 'home';
			}
		] );
		WP_Mock::userFunction( 'is_front_page', [
			'return' => function() {
				return $this->current_context === 'home';
			}
		] );
		WP_Mock::userFunction( 'is_singular', [
			'return' => function() {
				return $this->current_context === 'single';
			}
		] );
		WP_Mock::userFunction( 'is_archive', [
			'return' => function() {
				return $this->current_context === 'archive';
			}
		] );
		WP_Mock::userFunction( 'is_category', [
			'return' => function() {
				return $this->current_context === 'archive';
			}
		] );
		WP_Mock::userFunction( 'is_tag', [
			'return' => function() {
				return $this->current_context === 'archive';
			}
		] );
		WP_Mock::userFunction( 'is_search', [
			'return' => function() {
				return $this->current_context === 'archive';
			}
		] );
		WP_Mock::userFunction( 'is_author', [
			'return' => function() {
				return $this->current_context === 'archive';
			}
		] );
		WP_Mock::userFunction( 'is_date', [
			'return' => function() {
				return $this->current_context === 'archive';
			}
		] );

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
	 * Costruisce una struttura di impostazioni di default con la possibilità di applicare override.
	 *
	 * @param array $overrides Array di chiavi da sovrascrivere nella struttura nested.
	 * @return array
	 */
	private function get_default_settings( array $overrides = [] ): array {
		$position_default = [
			'active'                => false,
			'code'                  => '',
			'min_height_desktop'    => 250,
			'min_height_mobile'     => 100,
			'custom_selector'       => '',
			'use_default_placement' => true,
			'override_css'          => '',
			'target_element'        => '',
			'frequency'             => 0,
			'footer_position'       => 'before_footer',
			'use_global_config'     => true,
		];

		$settings = [
			'global_scripts' => '',
			'contexts'       => [
				'global'  => [
					'positions' => [
						'masthead'       => array_merge( $position_default, [ 'use_global_config' => false ] ),
						'footer'         => array_merge( $position_default, [ 'use_global_config' => false ] ),
						'sidebar_top'    => array_merge( $position_default, [ 'use_global_config' => false ] ),
						'sidebar_sticky' => array_merge( $position_default, [ 'use_global_config' => false ] ),
					]
				],
				'home'    => [
					'positions' => [
						'masthead'  => array_merge( $position_default, [ 'use_global_config' => true ] ),
						'footer'    => array_merge( $position_default, [ 'use_global_config' => true ] ),
						'grid_home' => array_merge( $position_default, [ 'use_global_config' => false ] ),
					]
				],
				'single'  => [
					'positions' => [
						'masthead' => array_merge( $position_default, [ 'use_global_config' => true ] ),
						'footer'   => array_merge( $position_default, [ 'use_global_config' => true ] ),
						'atf'      => array_merge( $position_default, [ 'use_global_config' => false ] ),
						'btf'      => array_merge( $position_default, [ 'use_global_config' => false ] ),
					]
				],
				'archive' => [
					'positions' => [
						'masthead'     => array_merge( $position_default, [ 'use_global_config' => true ] ),
						'footer'       => array_merge( $position_default, [ 'use_global_config' => true ] ),
						'grid_archive' => array_merge( $position_default, [ 'use_global_config' => false ] ),
					]
				],
			]
		];

		return array_replace_recursive( $settings, $overrides );
	}

	/**
	 * Scenario 1: Home con use_global_config = true -> deve ereditare il Masthead globale.
	 */
	public function test_home_use_global_config_true_uses_global_masthead() {
		$this->current_context = 'home';

		$html = '
		<div class="site-wrap">
			<header class="site-header">Header</header>
			<main>Contenuto Home</main>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'masthead' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL MASTHEAD]</div>'
						]
					]
				],
				'home'   => [
					'positions' => [
						'masthead' => [
							'use_global_config' => true,
							'active'            => true,
							'code'              => '<div class="ad">[HOME OVERRIDE]</div>'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '[GLOBAL MASTHEAD]', $result );
		$this->assertStringNotContainsString( '[HOME OVERRIDE]', $result );
	}

	/**
	 * Scenario 2: Home con use_global_config = false e codice valido -> deve effettuare override e inserire il Masthead Home.
	 */
	public function test_home_use_global_config_false_uses_home_masthead() {
		$this->current_context = 'home';

		$html = '
		<div class="site-wrap">
			<header class="site-header">Header</header>
			<main>Contenuto Home</main>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'masthead' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL MASTHEAD]</div>'
						]
					]
				],
				'home'   => [
					'positions' => [
						'masthead' => [
							'use_global_config' => false,
							'active'            => true,
							'code'              => '<div class="ad">[HOME OVERRIDE]</div>'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '[HOME OVERRIDE]', $result );
		$this->assertStringNotContainsString( '[GLOBAL MASTHEAD]', $result );
	}

	/**
	 * Scenario 3: Home con use_global_config = false e codice vuoto -> nessun banner inserito (no fallback al globale).
	 */
	public function test_home_use_global_config_false_empty_code_no_output() {
		$this->current_context = 'home';

		$html = '
		<div class="site-wrap">
			<header class="site-header">Header</header>
			<main>Contenuto Home</main>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'masthead' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL MASTHEAD]</div>'
						]
					]
				],
				'home'   => [
					'positions' => [
						'masthead' => [
							'use_global_config' => false,
							'active'            => true,
							'code'              => '   '
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringNotContainsString( 'sai-ad-wrapper', $result );
		$this->assertStringNotContainsString( '[GLOBAL MASTHEAD]', $result );
	}

	/**
	 * Scenario 4: Articolo Singolo con use_global_config = true -> deve ereditare il Footer globale.
	 */
	public function test_single_use_global_config_true_uses_global_footer() {
		$this->current_context = 'single';

		$html = '
		<div class="site-wrap">
			<main>Post Content</main>
			<footer class="site-footer">Footer</footer>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'footer' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL FOOTER]</div>'
						]
					]
				],
				'single' => [
					'positions' => [
						'footer' => [
							'use_global_config' => true,
							'active'            => true,
							'code'              => '<div class="ad">[SINGLE OVERRIDE]</div>'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '[GLOBAL FOOTER]', $result );
		$this->assertStringNotContainsString( '[SINGLE OVERRIDE]', $result );
	}

	/**
	 * Scenario 5: Articolo Singolo con use_global_config = false, before_footer e codice valido -> inserisce il banner prima del footer.
	 */
	public function test_single_use_global_config_false_before_footer_uses_single_footer() {
		$this->current_context = 'single';

		$html = '
		<div class="site-wrap">
			<main>Post Content</main>
			<footer class="site-footer">Footer</footer>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'footer' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL FOOTER]</div>'
						]
					]
				],
				'single' => [
					'positions' => [
						'footer' => [
							'use_global_config' => false,
							'active'            => true,
							'footer_position'   => 'before_footer',
							'code'              => '<div class="ad">[SINGLE OVERRIDE]</div>'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '[SINGLE OVERRIDE]</div></div><footer class="site-footer"', $result );
		$this->assertStringNotContainsString( '[GLOBAL FOOTER]', $result );
	}

	/**
	 * Scenario 6: Articolo Singolo con use_global_config = false, after_footer e codice valido -> inserisce il banner dopo il footer.
	 */
	public function test_single_use_global_config_false_after_footer_uses_single_footer() {
		$this->current_context = 'single';

		$html = '
		<div class="site-wrap">
			<main>Post Content</main>
			<footer class="site-footer">Footer</footer>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'footer' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL FOOTER]</div>'
						]
					]
				],
				'single' => [
					'positions' => [
						'footer' => [
							'use_global_config' => false,
							'active'            => true,
							'footer_position'   => 'after_footer',
							'code'              => '<div class="ad">[SINGLE OVERRIDE]</div>'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '</footer><div class="sai-ad-wrapper sai-footer"', $result );
		$this->assertStringContainsString( '[SINGLE OVERRIDE]', $result );
	}

	/**
	 * Scenario 7: Categorie/Archivi con use_global_config = true -> deve ereditare il Footer globale.
	 */
	public function test_archive_use_global_config_true_uses_global_footer() {
		$this->current_context = 'archive';

		$html = '
		<div class="site-wrap">
			<main>Archive List</main>
			<footer class="site-footer">Footer</footer>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global'  => [
					'positions' => [
						'footer' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL FOOTER]</div>'
						]
					]
				],
				'archive' => [
					'positions' => [
						'footer' => [
							'use_global_config' => true,
							'active'            => true,
							'code'              => '<div class="ad">[ARCHIVE OVERRIDE]</div>'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '[GLOBAL FOOTER]', $result );
		$this->assertStringNotContainsString( '[ARCHIVE OVERRIDE]', $result );
	}

	/**
	 * Scenario 8: Categorie/Archivi con use_global_config = false e codice vuoto -> nessun banner in output (senza fallback).
	 */
	public function test_archive_use_global_config_false_empty_code_no_output() {
		$this->current_context = 'archive';

		$html = '
		<div class="site-wrap">
			<main>Archive List</main>
			<footer class="site-footer">Footer</footer>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global'  => [
					'positions' => [
						'footer' => [
							'active' => true,
							'code'   => '<div class="ad">[GLOBAL FOOTER]</div>'
						]
					]
				],
				'archive' => [
					'positions' => [
						'footer' => [
							'use_global_config' => false,
							'active'            => true,
							'code'              => '   '
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringNotContainsString( 'sai-ad-wrapper', $result );
		$this->assertStringNotContainsString( '[GLOBAL FOOTER]', $result );
	}

	/**
	 * Scenario 9: Sanitizzazione del valore footer_position nei contesti annidati.
	 * Qualsiasi valore non consentito deve essere ricondotto a 'before_footer'.
	 */
	public function test_footer_position_invalid_value_sanitization() {
		$settings_instance = new SmartAdInserterSettings();
		
		$payload = [
			'global_scripts' => '',
			'contexts'       => [
				'global' => [
					'positions' => [
						'footer' => [
							'active'          => true,
							'code'            => 'banner',
							'footer_position' => 'invalid_position_val',
						]
					]
				]
			]
		];
		
		$this->saved_settings = [];
		$settings_instance->save_settings( $payload );
		$this->assertEquals( 'before_footer', $this->saved_settings['contexts']['global']['positions']['footer']['footer_position'] );
	}

	/**
	 * Scenario 10: Pulizia wrapper vuoti in output.
	 * Quando il codice banner è vuoto o solo whitespace-only, nessun wrapper div deve essere stampato.
	 */
	public function test_empty_banner_cleanup() {
		$this->current_context = 'home';

		$html = '
		<div class="site-wrap">
			<header class="site-header">Header</header>
			<main>Contenuto</main>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'masthead' => [
							'active' => true,
							'code'   => "\t \n   "
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringNotContainsString( 'sai-ad-wrapper', $result );
	}

	/**
	 * Scenario 11: Verifica che i tag script di tipo template (es. Elementor templates) vengano preservati.
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

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'footer' => [
							'active' => true,
							'code'   => '<div class="ad-footer">[BANNER]</div>'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '<# if ( \'skip\' === closeType ) { #> Salta <# } #> X', $result );
		$this->assertStringContainsString( '<script type="text/html" id="tmpl-elementor-templates">', $result );
		$this->assertStringContainsString( '</script>', $result );
	}

	/**
	 * Scenario 12: Verifica delle altezze minime inline per il mantenimento di Zero CLS.
	 */
	public function test_min_height_zero_cls() {
		$html = '
		<div class="site-wrap">
			<footer class="site-footer">Footer</footer>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'footer' => [
							'active'             => true,
							'code'               => '[BANNER]',
							'min_height_desktop' => 380,
							'min_height_mobile'  => 120,
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( 'min-height:380px; --min-h-mobile:120px;', $result );
	}

	/**
	 * Test Sidebar Top in Home con use_global_config = true -> deve ereditare dal globale.
	 */
	public function test_sidebar_top_home_use_global_config_true() {
		$this->current_context = 'home';

		$html = '
		<div class="site-wrap">
			<aside class="sidebar">Widget</aside>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'sidebar_top' => [
							'active' => true,
							'code'   => '[GLOBAL SIDEBAR TOP]'
						]
					]
				],
				'home'   => [
					'positions' => [
						'sidebar_top' => [
							'use_global_config' => true,
							'active'            => true,
							'code'              => '[HOME SIDEBAR TOP]'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '[GLOBAL SIDEBAR TOP]', $result );
		$this->assertStringNotContainsString( '[HOME SIDEBAR TOP]', $result );
	}

	/**
	 * Test Sidebar Top in Home con use_global_config = false e codice vuoto -> nessun output.
	 */
	public function test_sidebar_top_home_use_global_config_false_empty() {
		$this->current_context = 'home';

		$html = '
		<div class="site-wrap">
			<aside class="sidebar">Widget</aside>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'sidebar_top' => [
							'active' => true,
							'code'   => '[GLOBAL SIDEBAR TOP]'
						]
					]
				],
				'home'   => [
					'positions' => [
						'sidebar_top' => [
							'use_global_config' => false,
							'active'            => true,
							'code'              => ''
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringNotContainsString( 'sai-sidebar-top', $result );
		$this->assertStringNotContainsString( '[GLOBAL SIDEBAR TOP]', $result );
	}

	/**
	 * Test Sidebar Sticky in Archive con use_global_config = false e codice valido -> usa la configurazione archive.
	 */
	public function test_sidebar_sticky_archive_use_global_config_false_valid() {
		$this->current_context = 'archive';

		$html = '
		<div class="site-wrap">
			<aside class="sidebar">Widget</aside>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global'  => [
					'positions' => [
						'sidebar_sticky' => [
							'active' => true,
							'code'   => '[GLOBAL SIDEBAR STICKY]'
						]
					]
				],
				'archive' => [
					'positions' => [
						'sidebar_sticky' => [
							'use_global_config' => false,
							'active'            => true,
							'code'              => '[ARCHIVE SIDEBAR STICKY]'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		$this->assertStringContainsString( '[ARCHIVE SIDEBAR STICKY]', $result );
		$this->assertStringNotContainsString( '[GLOBAL SIDEBAR STICKY]', $result );
	}

	/**
	 * Test iniezione sidebar in assenza di tag sidebar nel tema -> deve fallire silenziosamente senza warning.
	 */
	public function test_sidebar_target_missing_silent_fail() {
		$this->current_context = 'home';

		$html = '
		<div class="site-wrap">
			<main>Contenuto senza alcuna barra laterale</main>
		</div>';

		$settings = $this->get_default_settings( [
			'contexts' => [
				'global' => [
					'positions' => [
						'sidebar_top' => [
							'active' => true,
							'code'   => '[GLOBAL SIDEBAR TOP]'
						]
					]
				]
			]
		] );

		$injector = new StructuralInjector( $settings );
		$result   = $injector->inject( $html );

		// Nessun warning lanciato, HTML restituito pulito
		$this->assertStringNotContainsString( 'sai-sidebar-top', $result );
		$this->assertStringContainsString( 'Contenuto senza alcuna barra laterale', $result );
	}
}
