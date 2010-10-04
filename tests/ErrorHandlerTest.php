<?php
use Observer\ErrorHandler;
use Observer\Listeners\Mock;

class ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $handler;
    
    public function setUp()
    {
        $this->handler = ErrorHandler::resetInstance(true); 
    }
    
    public function assertPreConditions()
    {
        $this->assertEquals(0, count($this->handler));
        $this->assertFalse($this->handler->getError());
        $this->assertTrue($this->handler->getClearErrorAfterSending());
    }
    
    protected function _generateError()
    {
        $this->handler->start();
        trigger_error("Foo!", E_USER_WARNING);
        $this->handler->stop();
    }
    
    public function testErrorGetCaught()
    {
        $this->handler->setClearErrorAfterSending(false);
        $this->_generateError();
        $this->assertRegExp("|Foo!|", $this->handler->getError());
    }
    
    public function testSubjectNotifiesObservers()
    {
        $this->handler->attach($mock = new Mock);
        $this->_generateError();
        $this->assertRegExp("|Foo!|", $mock->message);
    }
    
    public function testAggregation()
    {
        $this->handler->attach($mock = new Mock);
        $this->assertContains($mock, $this->handler);
    }
    
    public function testCountAggregation()
    {
        $this->handler->attach($mock = new Mock);
        $this->assertEquals(1, count($this->handler));
    }
    
    public function testClearErrorAfterSending()
    {
        $this->handler->setClearErrorAfterSending(true);
        $this->_generateError();
        $this->assertType('bool', $this->handler->getError());
        $this->handler->setClearErrorAfterSending(false);
        $this->_generateError();
        $this->assertType('string', $this->handler->getError());
    }
}