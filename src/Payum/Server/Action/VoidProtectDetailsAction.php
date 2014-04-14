<?php
namespace Payum\Server\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Server\Request\ProtectedDetailsRequest;

class VoidProtectDetailsAction extends PaymentAwareAction
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
        return $request instanceof ProtectedDetailsRequest;
    }
}
