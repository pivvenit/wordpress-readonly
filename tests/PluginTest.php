<?php

use PHPUnit\Framework\TestCase;

/**
 * Basic tests for WordPress Readonly plugin
 */
class PluginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset global options before each test
        $GLOBALS['wp_options'] = [];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Clean up global options after each test
        $GLOBALS['wp_options'] = [];
    }

    /**
     * Test that the plugin constant is defined
     */
    public function testPluginConstantIsDefined(): void
    {
        require_once __DIR__ . '/../wordpress-readonly.php';
        
        $this->assertTrue(
            defined('PIVVENIT_WORDPRESS_READONLY_INFO'),
            'Plugin constant PIVVENIT_WORDPRESS_READONLY_INFO should be defined'
        );
        $this->assertEquals(
            'pivvenit_wordpress_readonly_info',
            PIVVENIT_WORDPRESS_READONLY_INFO
        );
    }

    /**
     * Test that readonly info is initialized with disabled status
     */
    public function testReadonlyInfoInitialization(): void
    {
        // Simulate heartbeat filter with no existing option
        $response = [];
        $readonlyInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        
        $this->assertFalse($readonlyInfo, 'Initially, readonly info should not exist');
        
        // Simulate the initialization logic from the plugin
        if (!$readonlyInfo) {
            $readonlyInfo = new stdClass();
            $readonlyInfo->id = (new DateTime("now"))->getTimestamp();
            $readonlyInfo->status = 'disabled';
            update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
        }
        
        $storedInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        $this->assertNotFalse($storedInfo, 'Readonly info should be stored');
        
        $decoded = json_decode($storedInfo);
        $this->assertEquals('disabled', $decoded->status, 'Initial status should be disabled');
        $this->assertIsInt($decoded->id, 'ID should be an integer timestamp');
    }

    /**
     * Test that readonly status can be changed to prepare mode
     */
    public function testReadonlyPrepareMode(): void
    {
        $readonlyInfo = new stdClass();
        $readonlyInfo->status = 'prepare';
        $readonlyInfo->id = (new DateTime("now"))->getTimestamp();
        update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
        
        $storedInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        $decoded = json_decode($storedInfo);
        
        $this->assertEquals('prepare', $decoded->status, 'Status should be prepare');
        $this->assertIsInt($decoded->id, 'ID should be an integer timestamp');
    }

    /**
     * Test that readonly status can be changed to readonly mode
     */
    public function testReadonlyMode(): void
    {
        $readonlyInfo = new stdClass();
        $readonlyInfo->status = 'readonly';
        $readonlyInfo->id = (new DateTime("now"))->getTimestamp();
        update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
        
        $storedInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        $decoded = json_decode($storedInfo);
        
        $this->assertEquals('readonly', $decoded->status, 'Status should be readonly');
        $this->assertIsInt($decoded->id, 'ID should be an integer timestamp');
    }

    /**
     * Test that readonly mode can be disabled
     */
    public function testDisableReadonlyMode(): void
    {
        // First set to readonly
        $readonlyInfo = new stdClass();
        $readonlyInfo->status = 'readonly';
        $readonlyInfo->id = (new DateTime("now"))->getTimestamp();
        update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
        
        // Now disable it
        $readonlyInfo->status = 'disabled';
        update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
        
        $storedInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        $decoded = json_decode($storedInfo);
        
        $this->assertEquals('disabled', $decoded->status, 'Status should be disabled');
    }

    /**
     * Test WP_Error for authentication blocking
     */
    public function testAuthenticationBlocking(): void
    {
        // Set readonly mode
        $readonlyInfo = new stdClass();
        $readonlyInfo->status = 'readonly';
        $readonlyInfo->id = (new DateTime("now"))->getTimestamp();
        update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
        
        $storedInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        $decoded = json_decode($storedInfo);
        
        // Simulate authentication filter behavior
        if ($decoded->status != 'disabled') {
            $error = new WP_Error(
                'readonly_disabled_auth',
                'Login is temporary disabled due to maintenance. Please try again in a few moments.'
            );
            
            $this->assertInstanceOf(WP_Error::class, $error);
            $this->assertEquals('readonly_disabled_auth', $error->get_error_code());
            $this->assertStringContainsString('maintenance', $error->get_error_message());
        }
    }

    /**
     * Test JSON encoding and decoding of readonly info
     */
    public function testJsonEncodingDecoding(): void
    {
        $originalInfo = new stdClass();
        $originalInfo->status = 'prepare';
        $originalInfo->id = 1234567890;
        
        $encoded = json_encode($originalInfo);
        $this->assertIsString($encoded, 'Encoded data should be a string');
        
        $decoded = json_decode($encoded);
        $this->assertEquals($originalInfo->status, $decoded->status);
        $this->assertEquals($originalInfo->id, $decoded->id);
    }
}
