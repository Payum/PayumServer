<?php
namespace Payum\Server\Api;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Server\Api\Controller\GatewayController;
use Payum\Server\Api\Controller\GatewayMetaController;
use Payum\Server\Api\Controller\PaymentController;
use Payum\Server\Application;
use Payum\Server\Api\Controller\RootController;
use Payum\Server\ReplyToJsonResponseConverter;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
                $app['api.view.order_to_json_converter'],
                $app['form.factory'],
                $app['api.view.form_to_json_converter'],
                $app['url_generator']
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
        $app->get('/payments/meta', 'payum.api.controller.payment:metaAction')->bind('payment_meta');
        $app->get('/payments/{id}', 'payum.api.controller.payment:getAction')->bind('payment_get');
        $app->put('/payments/{id}', 'payum.api.controller.payment:updateAction')->bind('payment_update');
        $app->delete('/payments/{id}', 'payum.api.controller.payment:deleteAction')->bind('payment_delete');
        $app->post('/payments', 'payum.api.controller.payment:createAction')->bind('payment_create');
        $app->get('/payments', 'payum.api.controller.payment:allAction')->bind('payment_all');

        $app->get('/gateways/meta', 'payum.api.controller.gateway_meta:getAllAction')->bind('payment_factory_get_all');
        $app->get('/gateways', 'payum.api.controller.gateway:allAction')->bind('gateway_all');
        $app->get('/gateways/{name}', 'payum.api.controller.gateway:getAction')->bind('gateway_get');
        $app->delete('/gateways/{name}', 'payum.api.controller.gateway:deleteAction')->bind('gateway_delete');
        $app->post('/gateways', 'payum.api.controller.gateway:createAction')->bind('gateway_create');

        $app->before(function (Request $request, Application $app) {
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
        });

        $app->after(function (Request $request, Response $response) use ($app) {
            if($response instanceof JsonResponse && $app['debug']) {
                $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
            }
        });

        $app->after($app["cors"]);

        $app->after(function (Request $request, Response $response) use ($app) {
            if ('OPTIONS' === $request->getMethod()) {
                return $response;
            }

            if ('application/vnd.payum+json' == $response->headers->get('content-type')) {
                return $response;
            }
            if ('application/json' == $response->headers->get('content-type')) {
                return $response;
            }

            if ('application/vnd.payum+json' == $request->headers->get('content-type')) {
                /** @var ReplyToJsonResponseConverter $converter */
                $converter = $app['payum.reply_to_json_response_converter'];

                return $converter->convert(new HttpResponse($response));
            }

        }, 100);

        $app->error(function (\Exception $e, $code) use ($app) {
            if ('OPTIONS' === $app['request']->getMethod()) {
                return;
            }
            if (false == $e instanceof ReplyInterface) {
                return;
            }

            if ('application/vnd.payum+json' == $app['request']->headers->get('content-type')) {
                /** @var ReplyToJsonResponseConverter $converter */
                $converter = $app['payum.reply_to_json_response_converter'];

                return $converter->convert($e);
            }
        }, $priority = -7);

        $app->error(function (\Exception $e, $code) use ($app) {
            if ('OPTIONS' === $app['request']->getMethod()) {
                return;
            }

            if (
                'json' == $app['request']->getContentType() ||
                'application/vnd.payum+json' == $app['request']->headers->get('content-type')
            ) {
                return new JsonResponse(
                    [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'stackTrace' => $e->getTraceAsString(),
                    ],
                    200,
                    [
                        'content-type' => 'application/vnd.payum+json',
                    ]
                );
            }
        }, $priority = -100);
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}