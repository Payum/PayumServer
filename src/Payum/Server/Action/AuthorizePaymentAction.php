<?php
declare(strict_types=1);

namespace Payum\Server\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Server\Model\Payment;
use Payum\Server\Request\ObtainMissingDetailsRequest;

class AuthorizePaymentAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param $request Authorize
     */
    public function execute($request) : void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $payment Payment */
        $payment = $request->getModel();

        $this->gateway->execute($status = new GetHumanStatus($payment));
        if ($status->isNew()) {
            $this->gateway->execute(new ObtainMissingDetailsRequest($payment, $request->getToken()));

            try {
                $this->gateway->execute($convert = new Convert($payment, 'array', $request->getToken()));
                $payment->setDetails($convert->getResult());
            } catch (RequestNotSupportedException $e) {
                $payumPayment = new PayumPayment();
                $payumPayment->setNumber($payment->getNumber());
                $payumPayment->setTotalAmount($payment->getTotalAmount());
                $payumPayment->setCurrencyCode($payment->getCurrencyCode());
                $payumPayment->setClientEmail($payment->getPayer()->getEmail());
                $payumPayment->setClientId($payment->getPayer()->getId() ?: $payment->getPayer()->getEmail());
                $payumPayment->setDescription($payment->getDescription() ?: sprintf('Payment %s', $payment->getNumber()));
                $payumPayment->setCreditCard($payment->getCreditCard());
                $payumPayment->setDetails($payment->getDetails());

                $this->gateway->execute($convert = new Convert($payumPayment, 'array', $request->getToken()));
                $payment->setDetails($convert->getResult());
            }
        }

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        try {
            $request->setModel($details);
            $this->gateway->execute($request);
        } finally {
            $payment->setDetails((array) $details);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request) : bool
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof Payment;
    }
}