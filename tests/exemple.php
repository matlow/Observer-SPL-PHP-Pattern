<?php
spl_autoload_register(function ($class) {
    require_once str_replace('\\','/',$class) . '.php';
}, true, true);

$errorHandler = Observer\ErrorHandler::getInstance();

$errorHandler->attach(new Observer\Listeners\File(__DIR__ . '/log.txt'))
             ->attach(new Observer\Listeners\Db(realpath('./errordb.sq3'), 'error', 'nom'))
             ->attachFile('php://output')
             ->attach(new Observer\Listeners\Mail(new Observer\Listeners\Mail\Adapter\Mock('foo@foo.com')))
             ->attach($mock = new Observer\Listeners\Mock());

$errorHandler->start();

//iterating over Observer objects
foreach ($errorHandler as $writer) {
    printf("%s \n", $writer); 
}

// Generating a PHP error
echo $arr[0];

// Displaying mock observer error
echo "<strong>$mock</strong>";

$errorHandler->stop();