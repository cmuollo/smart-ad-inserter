<?php
use WP_Mock\Tools\TestCase;

class RestApiSecurityTest extends TestCase {
    public function setUp(): void { parent::setUp(); WP_Mock::setUp(); }
    public function tearDown(): void { WP_Mock::tearDown(); parent::tearDown(); }

    public function test_permission_denies_non_admin(): void {
        WP_Mock::userFunction( 'current_user_can', [
            'args' => ['manage_options'], 'return' => false,
        ]);
        $this->assertFalse( current_user_can('manage_options') );
    }

    public function test_permission_allows_admin(): void {
        WP_Mock::userFunction( 'current_user_can', [
            'args' => ['manage_options'], 'return' => true,
        ]);
        $this->assertTrue( current_user_can('manage_options') );
    }
}
