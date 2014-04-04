<?php
namespace Payum\Server\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\SecuredCaptureRequest;

class PaypalExpressCheckoutCaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model['RETURNURL'] = $request->getToken()->getTargetUrl();
        $model['CANCELURL'] = $request->getToken()->getTargetUrl();

        $this->payment->execute($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof SecuredCaptureRequest) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return false;
        }

        return false == isset($model['RETURNURL']);
    }
}
