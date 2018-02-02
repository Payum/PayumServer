<?php
namespace App\Api;

//use App\Api\Controller\GatewayController;
//use App\Api\Controller\PaymentController;
//use App\Api\Controller\RootController;
//use App\Api\Controller\TokenController;
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
//        $app['app.api.controller.root'] = $app->share(function() {
//            return new RootController();
//        });
//
//        $app['app.api.controller.payment'] = $app->share(function() use ($app) {
//            return new PaymentController(
//                $app['api.view.payment_to_json_converter'],
//                $app['url_generator'],
//                $app['payum.payment_storage'],
//                $app['payum.payment_schema_builder'],
//                $app['json_decode']
//            );
//        });
//
//        $app['app.api.controller.token'] = $app->share(function() use ($app) {
//            return new TokenController(
//                $app['payum'],
//                $app['api.view.token_to_json_converter'],
//                $app['payum.token_schema_builder'],
//                $app['json_decode']
//            );
//        });
//
//        $app['app.api.controller.gateway'] = $app->share(function() use ($app) {
//            return new GatewayController(
//                $app['url_generator'],
//                $app['payum.yadm_gateway_config_storage'],
//                $app['api.view.gateway_config_to_json_converter'],
//                $app['payum.gateway_schema_builder'],
//                $app['json_decode']
//            );
//        });
//
//        $app->get('/', 'app.api.controller.root:rootAction')->bind('api_root');
//
//        /** @var ControllerCollection $payments */
//        $payments = $app['controllers_factory'];
//        $payments->get('/{id}', 'app.api.controller.payment:getAction')->bind('payment_get');
//        $payments->delete('/{id}', 'app.api.controller.payment:deleteAction')->bind('payment_delete');
//        $payments->post('/', 'app.api.controller.payment:createAction')->bind('payment_create');
//        $payments->get('/', 'app.api.controller.payment:allAction')->bind('payment_all');
//        $app->mount('/payments', $payments);
//
//        /** @var ControllerCollection $gateways */
//        $gateways = $app['controllers_factory'];
//        $gateways->get('/', 'app.api.controller.gateway:allAction')->bind('gateway_all');
//        $gateways->get('/{name}', 'app.api.controller.gateway:getAction')->bind('gateway_get');
//        $gateways->delete('/{name}', 'app.api.controller.gateway:deleteAction')->bind('gateway_delete');
//        $gateways->post('/', 'app.api.controller.gateway:createAction')->bind('gateway_create');
//        $app->mount('/gateways', $gateways);
//
//        /** @var ControllerCollection $tokens */
//        $tokens = $app['controllers_factory'];
//        $tokens->post('/', 'app.api.controller.token:createAction')->bind('token_create');
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