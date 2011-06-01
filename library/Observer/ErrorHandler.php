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
* @author Julien Pauli <jpauli@php.net>
* @copyright 2010 Julien Pauli <jpauli@php.net>
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://julien-pauli.developpez.com/tutoriels/php/observer-spl/
*/
namespace Observer;

/**
* Base class for error handling.

* This class may not be perfect but is a very good implementation
* of the Subject/Observer pattern in PHP.
*
* @package Observer
* @author Julien Pauli <jpauli@php.net>
* @copyright 2010 Julien Pauli <jpauli@php.net>
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://julien-pauli.developpez.com/tutoriels/php/observer-spl/
* @version Release: @package_version@
*/
class ErrorHandler implements Pattern\Subject, \IteratorAggregate, \Countable
{
    /**
     * Singleton instance
     * 
     * @var ErrorHandler
     */
    protected static $_instance;
    
    /**
     * @var array
     */
    private $_error = array();
    
    /**
     * Weither or not fallback to PHP internal
     * error handler
     * 
     * @var bool
     */
    protected $_fallBackToPHPErrorHandler = false;
    
    /**
     * Weither or not clear the last error after
     * sending it to Listeners
     * 
     * @var bool
     */
    protected $_clearErrorAfterSending = true;
    
    /**
     * Listeners classes namespace
     * 
     * @var string
     */
    const LISTENERS_NS = "Listeners";

    /**
     * @var SplObjectStorage
     */
    private $_observers;
    
    /**
     * Retrieves singleton instance
     * 
     * @return ErrorHandler
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self; 
        }
        return self::$_instance;
    }
    
    /**
     * Singleton : can't be cloned
     */
    protected function __clone() { }

    /**
     * Singleton constructor
     */
    protected function __construct()
    {
        // Better prefer dependency injection here 
        $this->_observers = new \SplObjectStorage();
    }
    
    /**
     * Factory to build some Listeners
     * 
     * @param string $listener
     * @param array $args
     * @return object
     */
    public static function factory($listener, array $args = array())
    {
        $class = __NAMESPACE__ . "\\" . self::LISTENERS_NS . "\\" . $listener;
        try {
            $reflect = new \ReflectionClass($class);
            return $reflect->newInstanceArgs($args);
        } catch (\ReflectionException $e) {
            // no implementation yet
        }       
    }

    /**
     * Method run by php's error handler
     * 
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public function error($errno, $errstr, $errfile, $errline)
    {
        if(error_reporting() == 0) { // @ errors ignored
            return;
        }
        $this->_error = array($errno, $errstr, $errfile, $errline);
        $this->notify();
        if ($this->_fallBackToPHPErrorHandler) {
            return false;
        }
    }
    
    /**
     * Weither or not fallback to PHP internal
     * error handler
     * 
     * @param bool $bool
     * @return ErrorHandler
     */
    public function setFallBackToPHPErrorHandler($bool)
    {
        $this->_fallBackToPHPErrorHandler = (bool)$bool;
        return $this;
    }
    
    /**
     * Weither or not fallback to PHP internal
     * error handler
     * 
     * @return bool
     */
    public function getFallBackToPHPErrorHandler()
    {
        return $this->_fallBackToPHPErrorHandler;
    }
    
    /**
     * Weither or not clear the last error after
     * sending it to Listeners
     * 
     * @param bool $bool
     * @return ErrorHandler
     */
    public function setClearErrorAfterSending($bool)
    {
        $this->_clearErrorAfterSending = (bool)$bool;
        return $this;
    }
    
    /**
     * Weither or not clear the last error after
     * sending it to Listeners
     * 
     * @return bool
     */
    public function getClearErrorAfterSending()
    {
        return $this->_clearErrorAfterSending;
    }
    
    /**
     * Starts the ErrorHandler
     * 
     * @return ErrorHandler
     */
    public function start()
    {
        set_error_handler(array($this, 'error'));
        return $this;
    }
    
    /**
     * Stops the ErrorHandler
     * 
     * @return ErrorHandler
     */
    public function stop()
    {
        restore_error_handler();
        return $this;
    }

    /**
     * Observer pattern : shared method
     * to all observers
     * 
     * @return string
     */
    public function getError()
    {
        if (!$this->_error) {
            return false;
        }
        return vsprintf("Error %d: %s, in file %s at line %d", $this->_error);
    }
    
    /**
     * Resets the singleton instance
     * 
     * @return ErrorHandler|void
     */
    public static function resetInstance($andRecreate = false)
    {
        self::$_instance = null;
        return $andRecreate ? self::getInstance() : null;
    }

    /**
     * Observer pattern : attaches observers
     * 
     * @param Pattern\Observer $obs
     * @return ErrorHandler
     */
    public function attach(Pattern\Observer $obs)
    {
        $this->_observers->attach($obs);
        return $this;
    }

    /**
     * Observer pattern : detaches observers
     * 
     * @param Pattern\Observer $obs
     * @return ErrorHandler
     */
    public function detach(Pattern\Observer $obs)
    {
        $this->_observers->detach($obs);
        return $this;
    }

    /**
     * Observer pattern : notify observers
     * 
     * @param Pattern\Observer $obs
     * @return ErrorHandler
     */
    public function notify()
    {
        foreach ($this as $observer) {
            try {
                $observer->update($this);
            } catch(\Exception $e) {
                // we choose to exit here
                exit($e->getMessage());
            }
        }
        if ($this->_clearErrorAfterSending) {
            $this->_error = array();
        }
        return $this;
    }

    /**
     * IteratorAggregate
     * 
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->_observers;
    }
    
    /**
     * Countable
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_observers);
    }
    
    /**
     * Hack for 1.attach('Listener')
     *          2.detach('Listener')
     *          
     * @param string $funct
     * @param array $args
     * @return ErrorHandler
     * @throws \BadMethodCallException
     */
    public function __call($funct, $args)
    {
        if (preg_match('#(?P<prefix>at|de)tach(?P<listener>\w+)#', $funct, $matches)) {
            $meth     = $matches['prefix'] . 'tach';
            $listener = ucfirst(strtolower($matches['listener']));
            return $this->$meth(self::factory($listener, $args));
        }
        throw new \BadMethodCallException("unknown method $funct");
    }
}
