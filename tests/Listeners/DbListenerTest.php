<?php
use Observer\Listeners\Db;

class DbListenerTest extends PHPUnit_Framework_TestCase
{
    const SQLITE_FILE = "errordb.sq3";
    
    public function setUp()
    {
        $this->dblistener = new Db(_FILES_PATH . '/' . self::SQLITE_FILE, 'error', 'nom');
        $this->dblistener->getPDO()->exec("DELETE FROM error");
    }
    
    public function testUpdate()
    {
        $this->dblistener->update(new MockErrorHandler);
        $this->assertEquals('errortest', $this->dblistener->getPDO()->query("SELECT nom FROM error")->fetch(PDO::FETCH_COLUMN, 1));
    }
    
    public function testTostring()
    {
        $this->assertRegExp("|error|", (string)$this->dblistener);
    }
}
