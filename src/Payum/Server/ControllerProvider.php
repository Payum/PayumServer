<?php
namespace Payum\Server;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Payum\Server\Controller\ApiPaymentConfigController;
use Payum\Server\Controller\ApiOrderController;
use Payum\Server\Controller\ApiPaymentMetaController;
use Payum\Server\Controller\ApiStorageConfigController;
use Payum\Server\Controller\ApiStorageMetaController;
use Payum\Server\Controller\IndexController;
use Payum\Server\Controller\PayumController;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

        $app['controller.api_payment_config'] = $app->share(function() use ($app) {
            return new ApiPaymentConfigController(
                $app['form.factory'],
                $app['url_generator'],
                $app['payum.payment_factories'],
                $app['payum.config'],
                $app['payum.config_file']
            );
        });

        $app['controller.api_payment_factory'] = $app->share(function() use ($app) {
            return new ApiPaymentMetaController(
                $app['form.factory'],
                $app['payum.payment_factories']
            );
        });

        $app['controller.api_storage_factory'] = $app->share(function() use ($app) {
            return new ApiStorageMetaController(
                $app['form.factory'],
                $app['payum.storage_factories']
            );
        });

        $app['controller.api_storage_config'] = $app->share(function() use ($app) {
            return new ApiStorageConfigController(
                $app['form.factory'],
                $app['url_generator'],
                $app['payum.storage_factories'],
                $app['payum.config'],
                $app['payum.config_file']
            );
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

        $app->get('/api/orders/{payum_token}', 'controller.api_order:getAction')->bind('order_get');
        $app->post('/api/orders', 'controller.api_order:createAction')->bind('order_create');

        $app->get('/api/configs/payments/metas', 'controller.api_payment_factory:getAllAction')->bind('payment_factory_get_all');
        $app->get('/api/configs/payments', 'controller.api_payment_config:getAllAction')->bind('payment_config_get_all');
        $app->get('/api/configs/payments/{name}', 'controller.api_payment_config:getAction')->bind('payment_config_get');
        $app->post('/api/configs/payments', 'controller.api_payment_config:createAction')->bind('payment_config_create');

        $app->get('/api/configs/storages/metas', 'controller.api_storage_factory:getAllAction')->bind('storage_factory_get_all');
        $app->get('/api/configs/storages', 'controller.api_storage_config:getAllAction')->bind('storage_config_get_all');
        $app->put('/api/configs/storages/order', 'controller.api_storage_config:updateOrderAction')->bind('storage_order_config_update');
        $app->put('/api/configs/storages/security_token', 'controller.api_storage_config:updateTokenAction')->bind('storage_token_config_update');
        $app->get('/api/configs/storages/{name}', 'controller.api_storage_config:getAction')->bind('storage_config_get');

        $app->before(function (Request $request, Application $app) {
            if (0 !== strpos($request->getPathInfo(), '/api')) {
                return;
            }
            if ($request->getMethod() == 'GET') {
                return;
            }

            if ('json' !== $request->getContentType()) {
                throw new BadRequestHttpException('The request content type is invalid. It must be application/json');
            }

            $decodedContent = json_decode($request->getContent(), true);
            if (null ===  $decodedContent) {
                throw new BadRequestHttpException('The request content is not valid json.');
            }

            $request->attributes->set('content', $decodedContent);
        });

        $app->after(function (Request $request, Response $response) use ($app) {
            if($response instanceof JsonResponse && $app['debug']) {
                $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
            }
        });

        $app->after(function (Request $request, Response $response) use ($app) {
            if (0 === strpos($request->getPathInfo(), '/api')) {
                $response->headers->set('Access-Control-Allow-Origin', '*');
            }
        });

        $app->error(function (\Exception $e, $code) use ($app) {
            if (false == $e instanceof ReplyInterface) {
                return;
            }

            /** @var ReplyToSymfonyResponseConverter $converter */
            $converter = $app['payum.reply_to_symfony_response_converter'];

            return $converter->convert($e);
        }, $priority = 100);

        $app->error(function (\Exception $e, $code) use ($app) {
            if ('json' !== $app['request']->getContentType()) {
                return;
            }

            return new JsonResponse(array(
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ));
        }, $priority = -100);
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
    }
}