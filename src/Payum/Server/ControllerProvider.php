<?php
namespace Payum\Server;

use Payum\Core\Bridge\Symfony\Request\ResponseInteractiveRequest;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\InteractiveRequestInterface;
use Payum\Core\Request\RedirectUrlInteractiveRequest;
use Payum\Server\Controller\ApiOrderController;
use Payum\Server\Controller\ApiPaymentController;
use Payum\Server\Controller\IndexController;
use Payum\Server\Controller\NotifyController;
use Payum\Server\Controller\PurchaseController;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
                $app['payum.security.http_request_verifier'],
                $app['payum'],
                $app['payum.model.payment_details_class']
            );
        });

        $app['controller.api_order'] = $app->share(function() use ($app) {
            return new ApiOrderController(
                $app['payum.security.token_factory'],
                $app['payum.security.http_request_verifier'],
                $app['payum'],
                $app['payum.model.order_class']
            );
        });

        $app['controller.purchase'] = $app->share(function() use ($app) {
            return new PurchaseController(
                $app['payum.security.token_factory'],
                $app['payum.security.http_request_verifier'],
                $app['payum']
            );
        });

        $app['controller.notify'] = $app->share(function() use ($app) {
            return new NotifyController(
                $app['payum.security.http_request_verifier'],
                $app['payum']
            );
        });

        $app->get('/', 'controller.index:indexAction');
        $app->get('/purchase/{payum_token}', 'controller.purchase:doAction')->bind('purchase');
        $app->get('/notify/{payum_token}', 'controller.notify:doAction')->bind('notify');
        $app->post('/api/payment', 'controller.api_payment:createAction')->bind('payment_create');
        $app->get('/api/payment/{payum_token}', 'controller.api_payment:getAction')->bind('payment_get');
        $app->post('/api/order', 'controller.api_order:createAction')->bind('order_create');
        $app->get('/api/order/{payum_token}', 'controller.api_order:getAction')->bind('order_get');

        $app->error(function (\Exception $e, $code) {
            if (false == $e instanceof InteractiveRequestInterface) {
                return;
            }

            if ($e instanceof RedirectUrlInteractiveRequest) {
                return new RedirectResponse($e->getUrl());
            } else if ($e instanceof ResponseInteractiveRequest) {
                return $e->getResponse();
            }

            $ro = new \ReflectionObject($e);
            throw new LogicException(
                sprintf('Cannot convert payum\'s interactive request %s to symfony\'s response.', $ro->getShortName()),
                null,
                $e
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
    }
}