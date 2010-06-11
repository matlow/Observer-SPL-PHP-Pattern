<?php
use Observer\Listeners\Mail;
use Observer\Listeners\Mail\Adapter\Mock;

class MailListenerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->maillistener = new Mail($this->mock = new Mock('foo@foo.bar'));
    }
    
    public function testUpdate()
    {
        $this->maillistener->update(new MockErrorHandler);
        $this->assertContains('errortest', $this->mock->sent);
    }
    
    public function testInvalidEmailThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid Email address');
        new Mock('foo');
    }
}
