<?php
namespace Payum\Server\Test;

use Makasim\Values\HookStorage;
use Makasim\Yadm\Storage;
use Payum\Server\Application;
use Silex\WebTestCase as SilexWebTestCase;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class WebTestCase extends SilexWebTestCase
{
    public function setUp()
    {
        HookStorage::clearAll();

        parent::setUp();

        $this->app->boot();

        /** @var Storage $storage */

        $storage = $this->app['payum.gateway_config_storage'];
        $storage->getCollection()->drop();

        $storage = $this->app['payum.payment_storage'];
        $storage->getCollection()->drop();

        $storage = $this->app['payum.token_storage'];
        $storage->getCollection()->drop();
    }

    public function createApplication()
    {
        $app = new Application();
        $app['payum.root_dir'] = __DIR__;
        $app['exception_handler']->disable();
        $app['mongo.database'] = 'payum_server_test';
        $app['session.storage'] = new MockArraySessionStorage();

        return $app;
    }
}