<?php
namespace Payum\Server;

use Makasim\Yadm\Hydrator;
use Makasim\Yadm\Storage;
use MongoDB\Client;
use MongoDB\Database;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Action\AuthorizePaymentAction;
use Payum\Server\Action\CapturePaymentAction;
use Payum\Server\Action\ExecuteSameRequestWithPaymentDetailsAction;
use Payum\Server\Action\ObtainMissingDetailsAction;
use Payum\Server\Action\ObtainMissingDetailsForBe2BillAction;
use Payum\Server\Extension\UpdatePaymentStatusExtension;
use Payum\Server\Form\Extension\CreditCardExtension;
use Payum\Server\Form\Type\ChooseGatewayType;
use Payum\Server\Form\Type\CreatePaymentType;
use Payum\Server\Form\Type\CreateTokenType;
use Payum\Server\Form\Type\UpdatePaymentType;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Model\SecurityToken;
use Payum\Server\Storage\YadmStorage;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
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
        $app['debug'] = (boolean) getenv('PAYUM_DEBUG');
        $app['mongodb.uri'] = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017/payum_server';

        $app['payum.gateway_config_storage'] = $app->share(function ($app) {
            /** @var Database $db */
            $db = $app['mongodb.database'];

            return new YadmStorage(new Storage($db->selectCollection('gateway_configs'), new Hydrator(GatewayConfig::class)));
        });

        $app['payum.builder'] = $app->share($app->extend('payum.builder', function (PayumBuilder $builder) use ($app) {
            /** @var Database $db */
            $db = $app['mongodb.database'];

            $tokenStorage = new Storage($db->selectCollection('security_tokens'), new Hydrator(SecurityToken::class));
            $paymentStorage = new Storage($db->selectCollection('payments'), new Hydrator(Payment::class));

            $builder
                ->setTokenStorage(new YadmStorage($tokenStorage))
                ->setGatewayConfigStorage($app['payum.gateway_config_storage'])
                ->addStorage(Payment::class, new YadmStorage($paymentStorage))

                ->addCoreGatewayFactoryConfig([
                    'payum.template.obtain_credit_card' => '@PayumServer/obtainCreditCardWithJessepollakCard.html.twig',
                    'payum.template.obtain_missing_details' => '@PayumServer/obtainMissingDetails.html.twig',
                    'payum.extension.update_payment_status' => new UpdatePaymentStatusExtension(),
                    'payum.prepend_extensions' => ['payum.extension.update_payment_status'],
                    'payum.action.server.capture_payment' => new CapturePaymentAction(),
                    'payum.action.server.authorize_payment' => new AuthorizePaymentAction(),
                    'payum.action.server.execute_same_request_with_payment_details' => new ExecuteSameRequestWithPaymentDetailsAction(),
                    'payum.action.server.obtain_missing_details' => function(ArrayObject $config) use ($app) {
                        return new ObtainMissingDetailsAction(
                            $app['form.factory'],
                            $config['payum.template.obtain_missing_details']
                        );
                    },
                ])

                ->addGatewayFactoryConfig('be2bill_offsite', [
                    'payum.action.server.obtain_missing_details' => function(ArrayObject $config) use ($app) {
                        return new ObtainMissingDetailsForBe2BillAction(
                            $app['form.factory'],
                            $config['payum.template.obtain_missing_details']
                        );
                    },
                ])
                ->addGatewayFactoryConfig('be2bill_direct', [
                    'payum.action.server.obtain_missing_details' => function(ArrayObject $config) use ($app) {
                        return new ObtainMissingDetailsForBe2BillAction(
                            $app['form.factory'],
                            $config['payum.template.obtain_missing_details']
                        );
                    },
                ])
            ;

            return $builder;
        }));

        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new CreditCardExtension();

            return $extensions;
        }));

        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new CreatePaymentType();
            $types[] = new UpdatePaymentType();
            $types[] = new CreateTokenType();
            $types[] = new ChooseGatewayType();

            return $types;
        }));

        $app['payum.reply_to_json_response_converter'] = $app->share(function ($app) {
            return new ReplyToJsonResponseConverter();
        });

        $app['mongodb.client'] = $app->share(function ($app) {
            return new Client($app['mongodb.uri']);
        });

        $app['mongodb.database'] = $app->share(function ($app) {
            /** @var Client $client */
            $client = $app['mongodb.client'];

            if (false == $database = trim(parse_url($app['mongodb.uri'], PHP_URL_PATH), '/')) {
                throw new \LogicException('The mongodb.uri must have database specified. For example http://localhost:27017/payum_server');
            }

            return $client->selectDatabase($database);
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

        $app['payum.listener.choose_gateway'] = $app->share(function() use ($app) {
            return function(Request $request, Application $app) {
                /** @var Payum $payum */
                $payum = $app['payum'];

                /** @var SecurityToken $token */
                $token = $payum->getHttpRequestVerifier()->verify($request);

                /** @var Payment $payment */
                $payment = $token->getPayment();
                if ($payment && false == $payment->getGatewayName()) {
                    /** @var FormFactoryInterface $formFactory */
                    $formFactory = $app['form.factory'];

                    $form = $formFactory->createNamed('', 'choose_gateway', $payment, [
                        'action' => $token->getTargetUrl(),
                    ]);

                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $payum->getStorage($payment)->update($payment);
                    } else {
                        /** @var \Twig_Environment $twig */
                        $twig = $app['twig'];

                        return new Response($twig->render('@PayumServer/chooseGateway.html.twig', [
                            'form' => $form->createView(),
                            'payment' => $payment,
                            'layout' => '@PayumCore/layout.html.twig',
                        ]));
                    }
                }

                // do not verify it second time.
                $request->attributes->set('payum_token', $token);
            };
        });

        $app->before(function(Request $request, Application $app) {
            if (0 === strpos($request->getPathInfo(), '/payment/capture') || 0 === strpos($request->getPathInfo(), '/payment/authorize')) {
                return call_user_func($app['payum.listener.choose_gateway'], $request, $app);
            }
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
        SecurityToken::injectStorageRegistry($app['payum']);
    }
}
