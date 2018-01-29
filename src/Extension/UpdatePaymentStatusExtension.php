<?php
declare(strict_types=1);

namespace App\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use App\Model\Payment;

/**
 * Class UpdatePaymentStatusExtension
 * @package App\Extension
 */
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

        if ($request instanceof GetStatusInterface) {
            return;
        }

        if ($request->getFirstModel() instanceof Payment) {
            /** @var Payment $payment */
            $payment = $request->getFirstModel();

            $context->getGateway()->execute($status = new GetHumanStatus($payment));
            $payment->setStatus($status->getValue());
        }
    }
}