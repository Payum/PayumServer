<?php
namespace Payum\Server;

use Doctrine\MongoDB\Connection;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Extension\UpdatePaymentStatusExtension;
use Payum\Server\Form\Type\CreatePaymentType;
use Payum\Server\Form\Type\UpdatePaymentType;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Model\SecurityToken;
use Payum\Server\Storage\MongoStorage;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

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

                ->addCoreGatewayFactoryConfig(['payum.extension.update_payment_status' => new UpdatePaymentStatusExtension()])
            ;


            return $builder;
        }));

        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new CreatePaymentType();
            $types[] = new UpdatePaymentType();

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

        $app->after($app["cors"], 1000);

        $app->before(function (Request $request, Application $app) {
            if (0 !== strpos($request->getPathInfo(), '/payment/capture')) {
                return;
            }

            /** @var Payum $payum */
            $payum = $app['payum'];

            $token = $payum->getHttpRequestVerifier()->verify($request);

            /** @var StorageInterface $paymentStorage */
            $paymentStorage = $payum->getStorage($token->getDetails()->getClass());

            /** @var Payment $payment */
            $payment = $paymentStorage->find($token->getDetails()->getId());

            if (false == $payment->getGatewayName()) {
                /** @var FormFactoryInterface $formFactory */
                $formFactory = $app['form.factory'];

                $form = $formFactory->createBuilder('form', $payment, ['method' => 'POST'])
                    ->add('gatewayName', 'payum_gateways_choice', ['constraints' => [new NotBlank()]])
                    ->add('choose', 'submit')

                    ->getForm()
                ;

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $paymentStorage->update($payment);
                } else {
                    /** @var \Twig_Environment $twig */
                    $twig = $app['twig'];

                    return new Response($twig->render('@PayumServer/chooseGateway.html.twig', [
                        'form' => $form->createView(),
                        'layout' => '@PayumCore/layout.html.twig',
                    ]));
                }
            } else {
                $token->setGatewayName($payment->getGatewayName());

                $payum->getTokenStorage()->update($token);
            }

            // do not verify it second time.
            $request->attributes->set('payum_token', $token);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
