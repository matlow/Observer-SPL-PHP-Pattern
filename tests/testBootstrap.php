<?php
spl_autoload_register(function ($class) { $file = str_replace(array('_',"\\"), "/", ltrim($class, '\\')) . '.php';
  if (!@fopen($file, 'r', true)) { return;} require_once $file;}, true);
define ('LIB_PATH', dirname(__DIR__) . '/library');
define ('_FILES_PATH', __DIR__ . '/_files');

set_include_path(get_include_path() . PATH_SEPARATOR . LIB_PATH 
                                    . PATH_SEPARATOR . _FILES_PATH);
