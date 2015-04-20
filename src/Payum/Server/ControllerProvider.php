<?php
namespace Payum\Server;

use Payum\Server\Controller\IndexController;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;

class ControllerProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['controller.index'] = $app->share(function() use ($app) {
            return new IndexController($app['payum.root_dir']);
        });

        $app->get('/', 'controller.index:indexAction');
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}