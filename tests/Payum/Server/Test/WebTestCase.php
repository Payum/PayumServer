<?php
namespace Payum\Server\Test;

use Doctrine\MongoDB\Database;
use Payum\Server\Application;
use Silex\WebTestCase as SilexWebTestCase;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class WebTestCase extends SilexWebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->boot();

        $this->app['session.storage'] = new MockArraySessionStorage();

        /** @var Database $db */
        $db = $this->app['doctrine.mongo.database'];
        $db->selectCollection('gateway_configs')->remove([]);
        $db->selectCollection('security_tokens')->remove([]);
        $db->selectCollection('payments')->remove([]);
    }

    public function createApplication()
    {
        $app = new Application();
        $app['payum.root_dir'] = __DIR__;
        $app['exception_handler']->disable();
        $app['mongo.database'] = 'payum_server_test';

        return $app;
    }
}