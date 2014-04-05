<?php
namespace Payum\Server\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Server\Request\GetSensitiveKeysRequest;
use Payum\Server\Request\PrepareCaptureRequest;

class VoidGetSensitiveKeysAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        // just do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetSensitiveKeysRequest;
    }
}
