<?php
namespace Payum\Server\Test;

use Payum\Server\Application;
use Silex\WebTestCase as SilexWebTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class WebTestCase extends SilexWebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->copy(__DIR__.'/payum.yml.dist', __DIR__.'/payum.yml', true);
        $fs->remove(__DIR__.'/storage');
        $fs->mkdir(__DIR__.'/storage');
    }

    public function createApplication()
    {
        $app = new Application();
        $app['payum.root_dir'] = __DIR__;
        $app['exception_handler']->disable();

        return $app;
    }
}