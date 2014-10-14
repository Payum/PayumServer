<?php
namespace Payum\Server\Factory\Payment;

class PaypalExpressCheckoutFactory
{
    public function getOptions()
    {
        return array(
            'username' => '',
            'password' => '',
            'signature' => '',
            'sandbox' => true,
        );
    }

    public function getRequiredOptions()
    {
        return array(
            'username',
            'password',
            'signature',
        );
    }

    public function getName()
    {
        return 'paypal_express_checkout';
    }
}