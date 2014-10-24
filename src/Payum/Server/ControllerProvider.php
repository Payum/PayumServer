<?php
namespace Payum\Server;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Payum\Server\Controller\ApiHealthController;
use Payum\Server\Controller\ApiPaymentConfigController;
use Payum\Server\Controller\ApiOrderController;
use Payum\Server\Controller\ApiPaymentMetaController;
use Payum\Server\Controller\ApiStorageConfigController;
use Payum\Server\Controller\ApiStorageMetaController;
use Payum\Server\Controller\IndexController;
use Payum\Server\Controller\PayumController;
use Silex\Application as SilexApplication;
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
    public function register(SilexApplication $app)
    {
        $app['controller.index'] = $app->share(function() use ($app) {
            return new IndexController($app['app.root_dir']);
        });

        $app['controller.api_health'] = $app->share(function() {
            return new ApiHealthController();
        });

        $app['controller.api_order'] = $app->share(function() use ($app) {
            return new ApiOrderController(
                $app['payum.security.token_factory'],
                $app['payum.security.http_request_verifier'],
                $app['payum'],
                $app['api.view.order_to_json_converter'],
                $app['form.factory'],
                $app['api.view.form_to_json_converter']
            );
        });

        $app['controller.api_payment_config'] = $app->share(function() use ($app) {
            return new ApiPaymentConfigController(
                $app['form.factory'],
                $app['url_generator'],
                $app['api.view.form_to_json_converter'],
                $app['payum.config'],
                $app['payum.config_file']
            );
        });

        $app['controller.api_payment_factory'] = $app->share(function() use ($app) {
            return new ApiPaymentMetaController(
                $app['form.factory'],
                $app['api.view.form_to_json_converter'],
                $app['payum.payment_factories']
            );
        });

        $app['controller.api_storage_factory'] = $app->share(function() use ($app) {
            return new ApiStorageMetaController(
                $app['form.factory'],
                $app['api.view.form_to_json_converter'],
                $app['payum.storage_factories']
            );
        });

        $app['controller.api_storage_config'] = $app->share(function() use ($app) {
            return new ApiStorageConfigController(
                $app['form.factory'],
                $app['url_generator'],
                $app['api.view.form_to_json_converter'],
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
        $app->post('/capture/{payum_token}', 'controller.payum:captureAction')->bind('capture_post');
        $app->get('/authorize/{payum_token}', 'controller.payum:authorizeAction')->bind('authorize');
        $app->post('/authorize/{payum_token}', 'controller.payum:authorizeAction')->bind('authorize_post');
        $app->get('/notify/{payum_token}', 'controller.payum:notifyAction')->bind('notify');
        $app->post('/notify/{payum_token}', 'controller.payum:notifyAction')->bind('notify_post');

        $app->get('/api/health', 'controller.api_health:checksAction')->bind('api_health_checks');
        $app->get('/api/orders/meta', 'controller.api_order:metaAction')->bind('order_meta');
        $app->get('/api/orders/{payum_token}', 'controller.api_order:getAction')->bind('order_get');
        $app->put('/api/orders/{payum_token}', 'controller.api_order:updateAction')->bind('order_update');
        $app->delete('/api/orders/{payum_token}', 'controller.api_order:deleteAction')->bind('order_delete');
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
            if (in_array($request->getMethod(), array('GET', 'OPTIONS', 'DELETE'))) {
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

        $app->after($app["cors"]);

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
    public function boot(SilexApplication $app)
    {
    }
}