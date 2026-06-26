<?php
use WP_Mock\Tools\TestCase;

class AdminMenuTest extends TestCase {
    public function setUp(): void { parent::setUp(); WP_Mock::setUp(); }
    public function tearDown(): void { WP_Mock::tearDown(); parent::tearDown(); }

    public function test_admin_menu_hook_registered(): void {
        WP_Mock::expectActionAdded( 'admin_menu', 'sai_register_admin_menu' );
        add_action( 'admin_menu', 'sai_register_admin_menu' );
        $this->assertConditionsMet();
    }

    public function test_render_page_dies_for_non_admin(): void {
        WP_Mock::userFunction( 'current_user_can', [
            'args' => ['manage_options'], 'return' => false,
        ]);
        WP_Mock::userFunction( 'wp_die', [ 'times' => 1 ]);
        WP_Mock::userFunction( 'esc_html__', [ 'return_arg' => 0 ]);
        \SmartAdInserter\Admin\SmartAdInserterAdmin::render_admin_page();
        $this->assertConditionsMet();
    }
}
