<?php
namespace Payum\Server\Api;

//use Payum\Server\Api\Controller\GatewayController;
//use Payum\Server\Api\Controller\PaymentController;
//use Payum\Server\Api\Controller\RootController;
//use Payum\Server\Api\Controller\TokenController;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;

class ApiControllerProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
//        $app['payum.api.controller.root'] = $app->share(function() {
//            return new RootController();
//        });
//
//        $app['payum.api.controller.payment'] = $app->share(function() use ($app) {
//            return new PaymentController(
//                $app['api.view.payment_to_json_converter'],
//                $app['url_generator'],
//                $app['payum.payment_storage'],
//                $app['payum.payment_schema_builder'],
//                $app['json_decode']
//            );
//        });
//
//        $app['payum.api.controller.token'] = $app->share(function() use ($app) {
//            return new TokenController(
//                $app['payum'],
//                $app['api.view.token_to_json_converter'],
//                $app['payum.token_schema_builder'],
//                $app['json_decode']
//            );
//        });
//
//        $app['payum.api.controller.gateway'] = $app->share(function() use ($app) {
//            return new GatewayController(
//                $app['url_generator'],
//                $app['payum.yadm_gateway_config_storage'],
//                $app['api.view.gateway_config_to_json_converter'],
//                $app['payum.gateway_schema_builder'],
//                $app['json_decode']
//            );
//        });
//
//        $app->get('/', 'payum.api.controller.root:rootAction')->bind('api_root');
//
//        /** @var ControllerCollection $payments */
//        $payments = $app['controllers_factory'];
//        $payments->get('/{id}', 'payum.api.controller.payment:getAction')->bind('payment_get');
//        $payments->delete('/{id}', 'payum.api.controller.payment:deleteAction')->bind('payment_delete');
//        $payments->post('/', 'payum.api.controller.payment:createAction')->bind('payment_create');
//        $payments->get('/', 'payum.api.controller.payment:allAction')->bind('payment_all');
//        $app->mount('/payments', $payments);
//
//        /** @var ControllerCollection $gateways */
//        $gateways = $app['controllers_factory'];
//        $gateways->get('/', 'payum.api.controller.gateway:allAction')->bind('gateway_all');
//        $gateways->get('/{name}', 'payum.api.controller.gateway:getAction')->bind('gateway_get');
//        $gateways->delete('/{name}', 'payum.api.controller.gateway:deleteAction')->bind('gateway_delete');
//        $gateways->post('/', 'payum.api.controller.gateway:createAction')->bind('gateway_create');
//        $app->mount('/gateways', $gateways);
//
//        /** @var ControllerCollection $tokens */
//        $tokens = $app['controllers_factory'];
//        $tokens->post('/', 'payum.api.controller.token:createAction')->bind('token_create');
//        $app->mount('/tokens', $tokens);


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
//
//    /**
//     * {@inheritDoc}
//     */
//    public function boot(SilexApplication $app)
//    {
//    }
}