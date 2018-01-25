<?php
declare(strict_types=1);

namespace App\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Generic;
use App\Model\Payment;

class ExecuteSameRequestWithPaymentDetailsAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param $request Generic
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Payment $payment */
        $payment = $request->getModel();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        try {
            $request->setModel($details);

            $this->gateway->execute($request);
        } finally {
            $payment->setDetails((array) $details);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) : bool
    {
        return
            $request instanceof Generic &&
            $request->getModel() instanceof Payment;
    }
}