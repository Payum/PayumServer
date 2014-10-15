<?php
require_once __DIR__.'/../vendor/autoload.php';

use Payum\Server\ControllerProvider as PayumControllerProvider;
use Payum\Server\ServiceProvider as PayumServiceProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

$client = new Raven_Client;
$errorHandler = new Raven_ErrorHandler($client);
$errorHandler->registerExceptionHandler();
$errorHandler->registerErrorHandler();
$errorHandler->registerShutdownFunction();

$app = new Application;
$app['app.root_dir'] = realpath(__DIR__.'/../');

$app->register(new UrlGeneratorServiceProvider);
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider);
$app->register(new ServiceControllerServiceProvider);
$app->register(new PayumServiceProvider);
$app->register(new PayumControllerProvider);

$app->run();