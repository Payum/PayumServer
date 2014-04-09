<?php
require_once __DIR__.'/../vendor/autoload.php';

use Payum\Server\ControllerProvider as PayumControllerProvider;
use Payum\Server\ServiceProvider as PayumServiceProvider;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application;
$app['app.root_dir'] = realpath(__DIR__.'/../');

$app->register(new UrlGeneratorServiceProvider);
$app->register(new ServiceControllerServiceProvider);
$app->register(new PayumServiceProvider);
$app->register(new PayumControllerProvider);

$app->run();