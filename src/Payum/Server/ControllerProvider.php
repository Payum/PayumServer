<?php
namespace Payum\Server;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Payum\Server\Controller\ApiPaymentConfigController;
use Payum\Server\Controller\ApiOrderController;
use Payum\Server\Controller\ApiPaymentFactoryController;
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
            return new ApiPaymentFactoryController(
                $app['form.factory'],
                $app['payum.payment_factories']
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
        $app->get('/api/payments/configs', 'controller.api_payment_config:getAllAction')->bind('payment_config_get_all');
        $app->get('/api/payments/configs/{name}', 'controller.api_payment_config:getAction')->bind('payment_config_get');
        $app->post('/api/payments/configs', 'controller.api_payment_config:createAction')->bind('payment_config_create');
        $app->get('/api/payments/factories', 'controller.api_payment_factory:getAllAction')->bind('payment_factory_get_all');

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