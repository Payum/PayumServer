<?php
namespace Payum\Server;

use Payum\Server\Controller\ApiPaymentController;
use Payum\Server\Controller\IndexController;
use Payum\Server\Controller\PurchaseController;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ControllerProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['controller.index'] = $app->share(function() use ($app) {
            return new IndexController($app['app.root_dir']);
        });

        $app['controller.api_payment'] = $app->share(function() use ($app) {
            return new ApiPaymentController(
                $app['payum.security.token_factory'],
                $app['payum.security.token_storage'],
                $app['payum.security.http_request_verifier'],
                $app['payum'],
                $app['payum.model.payment_details_class']
            );
        });

        $app['controller.purchase'] = $app->share(function() use ($app) {
            return new PurchaseController(
                $app['payum.security.token_factory'],
                $app['payum.security.http_request_verifier'],
                $app['payum']
            );
        });

        $app->get('/', 'controller.index:indexAction');
        $app->get('/purchase/{payum_token}', 'controller.purchase:doAction')->bind('purchase');
        $app->post('/api/payment', 'controller.api_payment:createAction')->bind('payment_create');
        $app->get('/api/payment/{payum_token}', 'controller.api_payment:getAction')->bind('payment_get');
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
    }
}