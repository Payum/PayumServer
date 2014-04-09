<?php
namespace Payum\Server\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Server\Request\SecuredCaptureRequest;
use Payum\Core\Request\SecuredCaptureRequest as CoreSecuredCaptureRequest;

class PaypalExpressCheckoutCaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $coreRequest = new CoreSecuredCaptureRequest($request->getToken());
        $coreRequest->setModel($model);

        $this->payment->execute($coreRequest);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof SecuredCaptureRequest &&
            $request instanceof \ArrayAccess
        ;
    }
}
