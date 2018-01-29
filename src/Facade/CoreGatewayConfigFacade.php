<?php
declare(strict_types=1);

namespace App\Facade;

use App\Action\ExecuteSameRequestWithPaymentDetailsAction;
use App\Extension\UpdatePaymentStatusExtension;
use Payum\Core\Bridge\Spl\ArrayObject;
use App\Action\AuthorizePaymentAction;
use App\Action\CapturePaymentAction;
use App\Action\ObtainMissingDetailsAction;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Twig_Environment;

/**
 * Class CoreGatewayConfigFacade
 * @package App\Facade
 */
class CoreGatewayConfigFacade
{
    /**
     * @param ContainerInterface $container
     * @param FormFactoryInterface $formFactory
     * @param Twig_Environment $twig
     *
     * @return array
     */
    public static function get(
        ContainerInterface $container,
        FormFactoryInterface $formFactory,
        Twig_Environment $twig
    ) : array {
        return [
            'payum.template.obtain_credit_card' => '@PayumServer/obtainCreditCardWithJessepollakCard.html.twig',
            'payum.template.obtain_missing_details' => '@PayumServer/obtainMissingDetails.html.twig',
            'payum.extension.update_payment_status' => new UpdatePaymentStatusExtension(),
            'payum.prepend_extensions' => ['payum.extension.update_payment_status'],
            'payum.action.server.capture_payment' => new CapturePaymentAction(),
            'payum.action.server.authorize_payment' => new AuthorizePaymentAction(),
            'payum.action.server.execute_same_request_with_payment_details' => new ExecuteSameRequestWithPaymentDetailsAction(),
            'payum.action.server.obtain_missing_details' => function (ArrayObject $config) use ($container, $formFactory) {
                return new ObtainMissingDetailsAction(
                    $formFactory,
                    $container->getParameter('payum.template.obtain_missing_details')
                );
            },
            'twig.env' => $twig,
            'payum.paths' => $container->getParameter('payum.paths'),
        ];
    }
}
