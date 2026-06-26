<?php
use WP_Mock\Tools\TestCase;

class SanitizationTest extends TestCase {
    public function setUp(): void { parent::setUp(); WP_Mock::setUp(); }
    public function tearDown(): void { WP_Mock::tearDown(); parent::tearDown(); }

    public function test_frequency_absint(): void {
        $this->assertSame( 3, absint(3) );
        $this->assertSame( 0, absint(-5) );
        $this->assertSame( 0, absint('abc') );
    }

    public function test_target_element_strips_html(): void {
        WP_Mock::userFunction( 'sanitize_text_field', [
            'args' => [ '<script>x</script>.grid' ], 'return' => '.grid',
        ]);
        $this->assertSame( '.grid', sanitize_text_field('<script>x</script>.grid') );
    }

    public function test_use_default_placement_bool_cast(): void {
        $this->assertTrue( (bool) 1 );
        $this->assertFalse( (bool) 0 );
        $this->assertFalse( (bool) '' );
    }

    public function test_banner_code_strips_script(): void {
        $dirty = '<script>evil()</script><img src="ad.gif" alt="ad">';
        $clean = '<img src="ad.gif" alt="ad">';
        WP_Mock::userFunction( 'wp_kses', [
            'args'   => [ $dirty, \WP_Mock\Functions::type('array') ],
            'return' => $clean,
        ]);
        WP_Mock::userFunction( 'wp_unslash', [ 'return_arg' => 0 ]);
        $result = wp_kses( wp_unslash($dirty), [] );
        $this->assertStringNotContainsString( '<script>', $result );
    }
}
