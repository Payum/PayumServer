<?php
namespace Payum\Server;

use Doctrine\MongoDB\Connection;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\PayumBuilder;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Controller\CaptureController;
use Payum\Server\Extension\UpdatePaymentStatusExtension;
use Payum\Server\Form\Type\CreatePaymentType;
use Payum\Server\Form\Type\CreateTokenType;
use Payum\Server\Form\Type\UpdatePaymentType;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Model\SecurityToken;
use Payum\Server\Storage\MongoStorage;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['debug'] = (boolean) getenv('PAYUM_SERVER_DEBUG');

        $app['payum.gateway_config_storage'] = $app->share(function ($app) {
            /** @var Connection $connection */
            $connection = $app['doctrine.mongo.connection'];
            $db = $connection->selectDatabase('payum_server');

            return new MongoStorage(GatewayConfig::class, $db->selectCollection('gateway_configs'));
        });

        $app['payum.builder'] = $app->share($app->extend('payum.builder', function (PayumBuilder $builder) use ($app) {
            /** @var Connection $connection */
            $connection = $app['doctrine.mongo.connection'];
            $db = $connection->selectDatabase('payum_server');

            $builder
                ->setTokenStorage(new MongoStorage(SecurityToken::class, $db->selectCollection('security_tokens')))
                ->setGatewayConfigStorage($app['payum.gateway_config_storage'])
                ->addStorage(Payment::class, new MongoStorage(Payment::class, $db->selectCollection('payments')))

                ->addCoreGatewayFactoryConfig([
                    'payum.extension.update_payment_status' => new UpdatePaymentStatusExtension(),
                    'payum.prepend_extensions' => ['payum.extension.update_payment_status'],
                ])
            ;


            return $builder;
        }));

        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new CreatePaymentType();
            $types[] = new UpdatePaymentType();
            $types[] = new CreateTokenType();

            return $types;
        }));

        $app['payum.reply_to_json_response_converter'] = $app->share(function ($app) {
            return new ReplyToJsonResponseConverter();
        });

        $app['doctrine.mongo.connection'] = $app->share(function ($app) {
            return new Connection();
        });

        $app['payum.gateway_choices'] = $app->extend('payum.gateway_choices', function (array $choices) use ($app) {
            /** @var StorageInterface $gatewayConfigStorage */
            $gatewayConfigStorage = $app['payum.gateway_config_storage'];
            foreach ($gatewayConfigStorage->findBy([]) as $config) {
                /** @var GatewayConfigInterface $config */

                $choices[$config->getGatewayName()] = ucwords(str_replace(['_'], ' ', $config->getGatewayName()));
            }

            return $choices;
        });

        $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function(\Twig_Loader_Filesystem $loader, $app) {
            $loader->addPath(__DIR__.'/Resources/views', 'PayumServer');
            foreach (TwigFactory::createGenericPaths() as $path => $namespace) {
                $loader->addPath($path, $namespace);
            }

            return $loader;
        }));

        $app['payum.controller.capture'] = $app->share(function() use ($app) {
            return new CaptureController($app);
        });

        $app->after($app["cors"]);

        /** @var ControllerCollection $payment */
        $payment = $app['payum.payments_controller_collection'];
        $payment->after(function (Request $request, Response $response) use ($app) {
            if ('OPTIONS' == $request->getMethod()) {
                return;
            }

            if ('application/vnd.payum+json' == $response->headers->get('Content-Type')) {
                return;
            }
            if ('application/json' == $response->headers->get('Content-Type')) {
                return;
            }

            if ('application/vnd.payum+json' == $request->headers->get('Accept')) {
                throw new HttpResponse($response);
            }
        });

        $app->error(function (\Exception $e, $code) use ($app) {
            if ('OPTIONS' === $app['request']->getMethod()) {
                return;
            }
            if (false == $e instanceof ReplyInterface) {
                return;
            }

            if ('application/vnd.payum+json' == $app['request']->headers->get('Accept')) {
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
                'application/vnd.payum+json' == $app['request']->headers->get('Accept')
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
                        'Content-Type' => 'application/vnd.payum+json',
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
