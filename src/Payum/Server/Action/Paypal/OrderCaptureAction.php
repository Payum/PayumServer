<?php
namespace Payum\Server\Action\Paypal;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Model\Order;
use Payum\Server\Model\PaymentDetails;
use Payum\Server\Request\SecuredCaptureRequest;

class OrderCaptureAction extends PaymentAwareAction
{
    /**
     * @var \Payum\Core\Storage\StorageInterface
     */
    private $paymentDetailsStorage;

    /**
     * @param StorageInterface $paymentDetailsStorage
     */
    public function __construct(StorageInterface $paymentDetailsStorage)
    {
        $this->paymentDetailsStorage = $paymentDetailsStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var Order $order */
        $order = $request->getModel();

        $details = new PaymentDetails;
        $details['PAYMENTREQUEST_0_AMT'] = $order->getAmount();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = $order->getCurrency();
        $this->paymentDetailsStorage->updateModel($details);

        $order->setDetails($details);

        $this->payment->execute($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof SecuredCaptureRequest &&
            $request->getModel() instanceof Order &&
            false == $request->getModel()->getDetails()
        ;
    }
}
