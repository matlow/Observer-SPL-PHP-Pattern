<?php
/**
* Observer-SPL-PHP-Pattern
*
* Copyright (c) 2010, Julien Pauli <jpauli@php.net>.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
*
* * Redistributions of source code must retain the above copyright
* notice, this list of conditions and the following disclaimer.
*
* * Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in
* the documentation and/or other materials provided with the
* distribution.
*
* * Neither the name of Julien Pauli nor the names of his
* contributors may be used to endorse or promote products derived
* from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
* FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
* COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
* BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
* ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* @package Observer
* @subpackage Listeners
* @author Julien Pauli <jpauli@php.net>
* @copyright 2010 Julien Pauli <jpauli@php.net>
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://julien-pauli.developpez.com/tutoriels/php/observer-spl/
*/
namespace Observer\Listeners;
use Observer\Pattern;

/**
* Listener implementing PDO Sqlite backend
*
* @package Observer
* @subpackage Listeners
* @author Julien Pauli <jpauli@php.net>
* @copyright 2010 Julien Pauli <jpauli@php.net>
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://julien-pauli.developpez.com/tutoriels/php/observer-spl/
* @version Release: @package_version@
*/
class Db implements Pattern\Observer
{
    /**
     * @var PDO
     */
    private $_pdo;
    
    /**
     * @var string
     */
    private $_table;
    
    /**
     * @var string
     */
    private $_col;

    /**
     * Simple static PDO:SQLite backend
     * 
     * @param string $file
     * @param string $table
     * @param string $col
     */
    public function __construct($file, $table, $col)
    {
        if (!file_exists($file) || !is_writable($file)) {
            throw new \DomainException("File $file invalid");
        }
        $this->_pdo = new \PDO("sqlite:$file");
        $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_col   = (string)$col;
        $this->_table = (string)$table;
    }
    
    /**
     * Returns the PDO object used
     * 
     * @return PDO
     */
    public function getPDO()
    {
        return $this->_pdo;
    }

    /**
     * Observer
     * 
     * @param Pattern\Subject $errorHandler
     */
    public function update(Pattern\Subject $errorHandler)
    {
        $this->_pdo->exec("INSERT INTO $this->_table (`$this->_col`) VALUES('{$errorHandler->getError()}')");
    }
    
    /**
     * Observer
     * 
     * @return string
     */
    public function __toString()
    {
        return sprintf("class %s for table '%s' on col '%s'", __CLASS__, $this->_table, $this->_col); 
    }
}
