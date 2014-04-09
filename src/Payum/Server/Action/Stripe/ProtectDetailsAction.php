<?php
namespace Payum\Server\Action\Stripe;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Server\Request\GetSensitiveKeysRequest;
use Payum\Server\Request\PrepareCaptureRequest;
use Payum\Server\Request\ProtectedDetailsRequest;

class ProtectDetailsAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var ProtectedDetailsRequest$request */

        $request->protect('card');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof ProtectedDetailsRequest;
    }
}
