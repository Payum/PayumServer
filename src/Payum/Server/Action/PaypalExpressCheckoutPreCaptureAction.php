<?php
namespace Payum\Server\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Server\Request\PrepareCaptureRequest;

class PaypalExpressCheckoutPreCaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model['RETURNURL'] = $request->getToken()->getTargetUrl();
        $model['CANCELURL'] = $request->getToken()->getTargetUrl();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof PrepareCaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
