<?php
namespace Payum\Server\Test;

use Payum\Server\Application;
use Silex\WebTestCase as SilexWebTestCase;

abstract class WebTestCase extends SilexWebTestCase
{
    public function createApplication()
    {
        $app = new Application();
        $app['payum.root_dir'] = __DIR__;
        $app['exception_handler']->disable();

        return $app;
    }
}