<?php 

require_once PROJECT_BASE.'includes/lib/GoldSource.php';
require_once 'PHPUnit/Autoload.php';

/**
 * File part of the GoldSource library
 */
class GoldSourceTest extends PHPUnit_Framework_TestCase
{

    protected $_gs      = null;
    protected $_address = '127.0.0.1';
    protected $_port    = 27015;

    public function setUp()
    {
        $this->_gs = new GoldSource($this->_address, $this->_port);
    }

    public function tearDown()
    {
        unset($this->_gs);
    }

    /**
     * test server address validation
     */
    public function test_is_dns()
    {
        $this->assertFalse( $this->_gs->is_dns($this->_address) );

        $this->_address = 'cs.pgl.ro';
        $this->assertTrue( $this->_gs->is_dns($this->_address) );        
    }

    /**
     *
     */
    public function test_validate_address()
    {
        $this->_address = '127.0.0.1';
        $this->assertTrue( $this->_gs->validate_address($this->_address) );

        $this->_address = 'cs.pgl.ro';
        $this->assertTrue( $this->_gs->validate_address($this->_address) );

        $this->_address = 'not.an-address';
        $this->assertFalse( $this->_gs->validate_address($this->_address) );        
    }
}

