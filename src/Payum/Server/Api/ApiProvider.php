<?php
namespace Payum\Server\Api;

use Payum\Server\Api\View\GatewayConfigToJsonConverter;
use Payum\Server\Api\View\PaymentToJsonConverter;
use Payum\Server\Api\View\TokenToJsonConverter;
use Payum\Server\Application;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['api.view.payment_to_json_converter'] = function() use ($app) {
            return new PaymentToJsonConverter($app['payum']);
        };

        $app['api.view.token_to_json_converter'] = function() use ($app) {
            return new TokenToJsonConverter();
        };

        $app['api.view.gateway_config_to_json_converter'] = function() {
            return new GatewayConfigToJsonConverter();
        };

        $app['api.parse_json_request'] = function() {
            return function (Request $request, Application $app) {
                if ('json' == $request->getContentType()) {
                    $decodedContent = [];
                    if ($request->getContent()) {
                        $decodedContent = json_decode($request->getContent(), true);
                        if (null === $decodedContent) {
                            throw new BadRequestHttpException('The request content is not valid json.');
                        }
                    }

                    $request->attributes->set('content', $decodedContent);
                }
            };
        };

        $app['api.parse_post_request'] = function() {
            return function (Request $request, Application $app) {
                if ('form' == $request->getContentType()) {
                    $request->attributes->set('content', $request->request->all());
                }
            };
        };

        $app['api.view.pretty_print_json'] = function() {
            return function (Request $request, Response $response) {
                if ($response instanceof JsonResponse) {
                    $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
                }
            };
        };
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
