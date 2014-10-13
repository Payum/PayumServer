<?php
namespace Payum\Server;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Payum\Server\Controller\ApiConfigController;
use Payum\Server\Controller\ApiOrderController;
use Payum\Server\Controller\IndexController;
use Payum\Server\Controller\PayumController;
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

        $app['controller.api_order'] = $app->share(function() use ($app) {
            return new ApiOrderController(
                $app['payum.security.token_factory'],
                $app['payum.security.http_request_verifier'],
                $app['payum'],
                $app['payum.model.order_class']
            );
        });

        $app['controller.api_config'] = $app->share(function() use ($app) {
            return new ApiConfigController($app['payum.config'], $app['payum.config_file']);
        });

        $app['controller.payum'] = $app->share(function() use ($app) {
            return new PayumController(
                $app['payum.security.token_factory'],
                $app['payum.security.http_request_verifier'],
                $app['payum']
            );
        });

        $app->get('/', 'controller.index:indexAction');
        $app->get('/capture/{payum_token}', 'controller.payum:captureAction')->bind('capture');
        $app->get('/authorize/{payum_token}', 'controller.payum:authorizeAction')->bind('authorize');
        $app->get('/notify/{payum_token}', 'controller.payum:notifyAction')->bind('notify');
        $app->get('/api/order/{payum_token}', 'controller.api_order:getAction')->bind('order_get');
        $app->post('/api/order', 'controller.api_order:createAction')->bind('order_create');
        $app->get('/api/config', 'controller.api_config:getAction')->bind('config_get');
        $app->post('/api/config', 'controller.api_config:createAction')->bind('config_create');


        $app->error(function (\Exception $e, $code) use ($app) {
            if (false == $e instanceof ReplyInterface) {
                return;
            }

            /** @var ReplyToSymfonyResponseConverter $converter */
            $converter = $app['payum.reply_to_symfony_response_converter'];

            return $converter->convert($e);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
    }
}