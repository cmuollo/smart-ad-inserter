<?php
use WP_Mock\Tools\TestCase;
use SmartAdInserter\Injection\StructuralInjector;

/**
 * Suite di test unitari per la classe StructuralInjector.
 *
 * @since    1.0.0
 */
class StructuralInjectorTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Verifica che tutti gli hook di iniezione vengano registrati correttamente.
	 */
	public function test_register_hooks(): void {
		$injector = new StructuralInjector();

		WP_Mock::expectActionAdded( 'wp_body_open', [ $injector, 'inject_masthead_action' ] );
		WP_Mock::expectFilterAdded( 'the_content', [ $injector, 'inject_masthead_content_filter' ], 9 );
		WP_Mock::expectActionAdded( 'wp_footer', [ $injector, 'inject_masthead_footer_fallback' ] );
		WP_Mock::expectActionAdded( 'dynamic_sidebar_before', [ $injector, 'inject_sidebar_top' ], 10, 2 );
		WP_Mock::expectActionAdded( 'dynamic_sidebar_after', [ $injector, 'inject_sidebar_sticky' ], 10, 2 );
		WP_Mock::expectActionAdded( 'the_post', [ $injector, 'check_grid_injection' ] );
		WP_Mock::expectFilterAdded( 'the_content', [ $injector, 'inject_content_ads' ], 11 );

		$injector->register_hooks();

		$this->assertConditionsMet();
	}

	/**
	 * Verifica che il metodo inject restituisca il markup originario immutato.
	 */
	public function test_inject_returns_unmodified_subject(): void {
		$injector = new StructuralInjector();
		$subject = '<html><body>Test</body></html>';
		$this->assertEquals( $subject, $injector->inject( $subject ) );
	}
}
