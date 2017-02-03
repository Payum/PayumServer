<?php
namespace Payum\Server\Test;

use MongoDB\Database;
use Payum\Server\Application;
use Silex\WebTestCase as SilexWebTestCase;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class WebTestCase extends SilexWebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['session.storage'] = new MockArraySessionStorage();

        $this->app->boot();


        /** @var Database $db */
        $db = $this->app['mongodb.database'];
        $db->selectCollection('gateway_configs')->deleteMany([]);
        $db->selectCollection('security_tokens')->deleteMany([]);
        $db->selectCollection('payments')->deleteMany([]);
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