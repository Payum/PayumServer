<?php
namespace Payum\Server\Api;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Server\Api\Controller\GatewayController;
use Payum\Server\Api\Controller\GatewayMetaController;
use Payum\Server\Api\Controller\PaymentController;
use Payum\Server\Api\Controller\RootController;
use Payum\Server\Api\Controller\TokenController;
use Payum\Server\ReplyToJsonResponseConverter;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['payum.api.controller.root'] = $app->share(function() {
            return new RootController();
        });

        $app['payum.api.controller.payment'] = $app->share(function() use ($app) {
            return new PaymentController(
                $app['payum'],
                $app['api.view.payment_to_json_converter'],
                $app['form.factory'],
                $app['api.view.form_to_json_converter'],
                $app['url_generator']
            );
        });

        $app['payum.api.controller.token'] = $app->share(function() use ($app) {
            return new TokenController(
                $app['payum'],
                $app['form.factory'],
                $app['api.view.token_to_json_converter'],
                $app['api.view.form_to_json_converter']
            );
        });

        $app['payum.api.controller.gateway'] = $app->share(function() use ($app) {
            return new GatewayController(
                $app['form.factory'],
                $app['url_generator'],
                $app['api.view.form_to_json_converter'],
                $app['payum.gateway_config_storage'],
                $app['api.view.gateway_config_to_json_converter']
            );
        });

        $app['payum.api.controller.gateway_meta'] = $app->share(function() use ($app) {
            return new GatewayMetaController(
                $app['form.factory'],
                $app['api.view.form_to_json_converter'],
                $app['payum']
            );
        });

        $app->get('/', 'payum.api.controller.root:rootAction')->bind('api_root');

        /** @var ControllerCollection $payments */
        $payments = $app['controllers_factory'];
        $payments->get('/meta', 'payum.api.controller.payment:metaAction')->bind('payment_meta');
        $payments->get('/{id}', 'payum.api.controller.payment:getAction')->bind('payment_get');
        $payments->put('/{id}', 'payum.api.controller.payment:updateAction')->bind('payment_update');
        $payments->delete('/{id}', 'payum.api.controller.payment:deleteAction')->bind('payment_delete');
        $payments->post('/', 'payum.api.controller.payment:createAction')->bind('payment_create');
        $payments->get('/', 'payum.api.controller.payment:allAction')->bind('payment_all');
        $app->mount('/payments', $payments);

        /** @var ControllerCollection $gateways */
        $gateways = $app['controllers_factory'];
        $gateways->get('/meta', 'payum.api.controller.gateway_meta:getAllAction')->bind('payment_factory_get_all');
        $gateways->get('/', 'payum.api.controller.gateway:allAction')->bind('gateway_all');
        $gateways->get('/{name}', 'payum.api.controller.gateway:getAction')->bind('gateway_get');
        $gateways->delete('/{name}', 'payum.api.controller.gateway:deleteAction')->bind('gateway_delete');
        $gateways->post('/', 'payum.api.controller.gateway:createAction')->bind('gateway_create');
        $app->mount('/gateways', $gateways);

        /** @var ControllerCollection $tokens */
        $tokens = $app['controllers_factory'];
        $tokens->post('/', 'payum.api.controller.token:createAction')->bind('token_create');
        $app->mount('/tokens', $tokens);


        $gateways->before($app['api.parse_json_request']);
        $payments->before($app['api.parse_json_request']);
        $tokens->before($app['api.parse_json_request']);

        $gateways->before($app['api.parse_post_request']);
        $payments->before($app['api.parse_post_request']);
        $tokens->before($app['api.parse_post_request']);

        if ($app['debug']) {
            $gateways->after($app['api.view.pretty_print_json']);
            $payments->after($app['api.view.pretty_print_json']);
            $tokens->after($app['api.view.pretty_print_json']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}