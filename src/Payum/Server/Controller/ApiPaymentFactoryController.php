<?php
namespace Payum\Server\Controller;

use Payum\Server\Factory\Payment\PaypalExpressCheckoutFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiPaymentFactoryController
{
    /**
     * @return JsonResponse
     */
    public function getAction()
    {
        $paypalPaymentFactory = new PaypalExpressCheckoutFactory();

        return new JsonResponse(array('factories' => array(
            $paypalPaymentFactory->getName() => array(
                'options' => $paypalPaymentFactory->getOptions(),
                'required' => $paypalPaymentFactory->getRequiredOptions(),
            )
        )));
    }
}
