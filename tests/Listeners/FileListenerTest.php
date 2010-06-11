<?php
class FileListenerTest extends PHPUnit_Framework_TestCase
{
    const TEST_FILE = "foo.txt";
    
    public function setUp()
    {
        @unlink(_FILES_PATH . '/' . self::TEST_FILE);
        $this->filelistener = new Observer\Listeners\File(_FILES_PATH . '/' . self::TEST_FILE);
    }
    
    public function assertPreConditions()
    {
        $this->assertContains(self::TEST_FILE, (string)$this->filelistener);
    }
    
    public function testUpdateWritesToAFile()
    {
        $this->filelistener->update(new MockErrorHandler);
        $this->assertStringEqualsFile(_FILES_PATH . '/' . self::TEST_FILE, MockErrorHandler::ERRORMSG.PHP_EOL);
    }
}
