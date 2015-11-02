<?php
namespace Payum\Server\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Server\Model\Payment;

class UpdatePaymentStatusExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
        if ($context->getPrevious()) {
            return;
        }

        /** @var Generic $request */
        $request = $context->getRequest();
        if (false == $request instanceof Generic) {
            return;
        }
        if (false == $request instanceof GetStatusInterface) {
            return;
        }

        /** @var Payment $payment */
        $payment = $request->getFirstModel();
        if (false == $payment instanceof Payment) {
            return;
        }

        $context->getGateway()->execute($status = new GetHumanStatus($payment));
        if ($payment->getStatus() != $status->getValue()) {
            $payment->setStatus($status->getValue());
        }
    }
}