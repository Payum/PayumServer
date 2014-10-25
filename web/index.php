<?php
require_once __DIR__.'/../vendor/autoload.php';

//$client = new \Raven_Client;
//$errorHandler = new \Raven_ErrorHandler($client);
//$errorHandler->registerExceptionHandler();
//$errorHandler->registerErrorHandler();
//$errorHandler->registerShutdownFunction();

$app = new \Payum\Server\Application;
$app->run();
