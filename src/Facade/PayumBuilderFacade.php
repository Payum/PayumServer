<?php
declare(strict_types=1);

namespace App\Facade;

use App\Model\SecurityToken;
use Makasim\Yadm\Storage;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use App\Action\ExecuteSameRequestWithPaymentDetailsAction;
use App\Action\ObtainMissingDetailsForBe2BillAction;
use App\Extension\UpdatePaymentStatusExtension;
use App\Storage\PaymentStorage;
use App\Storage\YadmStorage;
use Payum\Core\Bridge\Spl\ArrayObject;
use App\Action\AuthorizePaymentAction;
use App\Action\CapturePaymentAction;
use App\Action\ObtainMissingDetailsAction;
use App\Model\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Twig_Environment;

/**
 * Class PayumBuilderFacade
 * @package App\Facade
 */
class PayumBuilderFacade
{
    /**
     * @param PayumBuilder $builder
     * @param Storage $tokenStorage
     * @param StorageInterface $gatewayConfigStorage
     * @param PaymentStorage $paymentStorage
     * @param FormFactoryInterface $formFactory
     * @param Twig_Environment $twig
     * @param ContainerInterface $container
     *
     * @return PayumBuilder
     */
    public static function get(
        PayumBuilder $builder,
        Storage $tokenStorage,
        StorageInterface $gatewayConfigStorage,
        PaymentStorage $paymentStorage,
        FormFactoryInterface $formFactory,
        Twig_Environment $twig,
        ContainerInterface $container
    ) : PayumBuilder {
        $builder
            ->setTokenStorage(new YadmStorage($tokenStorage, YadmStorage::DEFAULT_ID_PROPERTY, SecurityToken::class))
            ->setTokenFactory(function (
                StorageInterface $tokenStorage,
                StorageRegistryInterface $storageRegistry
            ) use ($container) {
                return new TokenFactory($tokenStorage, $storageRegistry, $container->get('router'));
            })
            ->setGatewayConfigStorage($gatewayConfigStorage)
            ->addStorage(Payment::class, new YadmStorage($paymentStorage, YadmStorage::DEFAULT_ID_PROPERTY, Payment::class))
            ->addCoreGatewayFactoryConfig([
                'payum.template.obtain_credit_card' => '@PayumServer/obtainCreditCardWithJessepollakCard.html.twig',
                'payum.template.obtain_missing_details' => '@PayumServer/obtainMissingDetails.html.twig',
                'payum.extension.update_payment_status' => new UpdatePaymentStatusExtension(),
                'payum.prepend_extensions' => ['payum.extension.update_payment_status'],
                'payum.action.server.capture_payment' => new CapturePaymentAction(),
                'payum.action.server.authorize_payment' => new AuthorizePaymentAction(),
                'payum.action.server.execute_same_request_with_payment_details' => new ExecuteSameRequestWithPaymentDetailsAction(),
                'payum.action.server.obtain_missing_details' => function (ArrayObject $config) use ($formFactory, $twig) {
                    return new ObtainMissingDetailsAction(
                        $formFactory,
                        $config['payum.template.obtain_missing_details']
                    );
                },

                'twig.env' => $twig,

                'payum.paths' => [
                    'PayumServer' => __DIR__ . '/../Resources/views',
                ],
            ])
            ->addGatewayFactoryConfig('be2bill_offsite', [
                'payum.action.server.obtain_missing_details' => function (ArrayObject $config) use ($formFactory) {
                    return new ObtainMissingDetailsForBe2BillAction(
                        $formFactory,
                        $config['payum.template.obtain_missing_details']
                    );
                },
            ])
            ->addGatewayFactoryConfig('be2bill_direct', [
                'payum.action.server.obtain_missing_details' => function (ArrayObject $config) use ($formFactory) {
                    return new ObtainMissingDetailsForBe2BillAction(
                        $formFactory,
                        $config['payum.template.obtain_missing_details']
                    );
                },
            ])
            ->setGenericTokenFactoryPaths([
                'capture' => $container->getParameter('payum.capture_path'),
                'notify' => $container->getParameter('payum.notify_path'),
                'authorize' => $container->getParameter('payum.authorize_path'),
                'refund' => $container->getParameter('payum.refund_path'),
                'cancel' => $container->getParameter('payum.cancel_path'),
                'payout' => $container->getParameter('payum.payout_path'),
            ])
            ->setHttpRequestVerifier(function ($tokenStorage) {
                return new HttpRequestVerifier($tokenStorage);
            })
            ->setCoreGatewayFactory($container->get('payum.core_gateway_factory_builder'));

        return $builder;
    }
}
